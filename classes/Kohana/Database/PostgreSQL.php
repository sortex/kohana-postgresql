<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * PostgreSQL database connection.
 *
 * @package     PostgreSQL
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Kohana_Database_PostgreSQL extends Database
{
	protected $_version;
	protected $_converters    = [];
	protected $_handled_types = [];

	/**
	 * CTOR
	 *
	 * @param  string  $name
	 * @param  array   $config
	 */
	public function __construct($name, array $config)
	{
		parent::__construct($name, $config);

		if (empty($this->_config['connection']['info']))
		{
			// Build connection string
			$this->_config['connection']['info'] = '';

			extract($this->_config['connection']);

			if ( ! empty($hostname))
			{
				$info .= "host='$hostname'";
			}

			if ( ! empty($port))
			{
				$info .= " port='$port'";
			}

			if ( ! empty($username))
			{
				$info .= " user='$username'";
			}

			if ( ! empty($password))
			{
				$info .= " password='$password'";
			}

			if ( ! empty($database))
			{
				$info .= " dbname='$database'";
			}

			if (isset($ssl))
			{
				if ($ssl === TRUE)
				{
					$info .= " sslmode='require'";
				}
				elseif ($ssl === FALSE)
				{
					$info .= " sslmode='disable'";
				}
				else
				{
					$info .= " sslmode='$ssl'";
				}
			}

			$this->_config['connection']['info'] = $info;
		}

		$this->register_base_converters();
	}

	/**
	 * @throws Database_Exception
	 */
	public function connect()
	{
		if ($this->_connection)
			return;

		try
		{
			$this->_connection = empty($this->_config['connection']['persistent'])
				? pg_connect($this->_config['connection']['info'], PGSQL_CONNECT_FORCE_NEW)
				: pg_pconnect($this->_config['connection']['info'], PGSQL_CONNECT_FORCE_NEW);
		}
		catch (ErrorException $e)
		{
			throw new Database_Exception(':error', array(':error' => $e->getMessage()));
		}

		if ( ! is_resource($this->_connection))
			throw new Database_Exception('Unable to connect to PostgreSQL ":name"', array(':name' => $this->_instance));

		$this->_version = pg_parameter_status($this->_connection, 'server_version');

		if ( ! empty($this->_config['charset']))
		{
			$this->set_charset($this->_config['charset']);
		}

		if (empty($this->_config['schema']))
		{
			// Assume the default schema without changing the search path
			$this->_config['schema'] = 'public';
		}
		else
		{
			if ( ! pg_send_query($this->_connection, 'SET search_path = '.$this->_config['schema'].', pg_catalog'))
				throw new Database_Exception(pg_last_error($this->_connection));

			if ( ! $result = pg_get_result($this->_connection))
				throw new Database_Exception(pg_last_error($this->_connection));

			if (pg_result_status($result) !== PGSQL_COMMAND_OK)
				throw new Database_Exception(pg_result_error($result));
		}
	}

	/**
	 * @return bool
	 */
	public function disconnect()
	{
		if ( ! $status = ! is_resource($this->_connection))
		{
			if ($status = pg_close($this->_connection))
			{
				$this->_connection = NULL;
			}
		}

		return $status;
	}

	/**
	 * @param  string  $charset
	 * @throws Database_Exception
	 */
	public function set_charset($charset)
	{
		$this->_connection OR $this->connect();

		if (pg_set_client_encoding($this->_connection, $charset) !== 0)
			throw new Database_Exception(pg_last_error($this->_connection));
	}

	/**
	 * Execute a PostgreSQL command
	 *
	 * @param   string  $sql    SQL command
	 * @return  boolean
	 * @throws  Database_Exception
	 */
	protected function _command($sql)
	{
		$this->_connection OR $this->connect();

		if ( ! pg_send_query($this->_connection, $sql))
			throw new Database_Exception(pg_last_error($this->_connection));

		if ( ! $result = pg_get_result($this->_connection))
			throw new Database_Exception(pg_last_error($this->_connection));

		return (pg_result_status($result) === PGSQL_COMMAND_OK);
	}

	/**
	 * @param  int     $type
	 * @param  string  $sql
	 * @param  bool    $as_object
	 * @param  array   $params
	 * @return array|Database_PostgreSQL_Result|int|object
	 * @throws Exception
	 */
	public function query($type, $sql, $as_object = FALSE, array $params = NULL)
	{
		$this->_connection OR $this->connect();

		if (Kohana::$profiling)
		{
			// Benchmark this query for the current instance
			$benchmark = Profiler::start("Database ({$this->_instance})", $sql);
		}

		try
		{
			if ($type === Database::INSERT AND $this->_config['primary_key'])
			{
				$sql .= ' RETURNING '.$this->quote_identifier($this->_config['primary_key']);
			}

			try
			{
				$result = pg_query($this->_connection, $sql);
			}
			catch (Exception $e)
			{
				throw new Database_Exception(':error [ :query ]',
					array(':error' => pg_last_error($this->_connection), ':query' => $sql));
			}

			if ( ! $result)
				throw new Database_Exception(':error [ :query ]',
					array(':error' => pg_last_error($this->_connection), ':query' => $sql));

			// Check the result for errors
			switch (pg_result_status($result))
			{
				case PGSQL_COMMAND_OK:
					$rows = pg_affected_rows($result);
				break;
				case PGSQL_TUPLES_OK:
					$rows = pg_num_rows($result);
				break;
				case PGSQL_BAD_RESPONSE:
				case PGSQL_NONFATAL_ERROR:
				case PGSQL_FATAL_ERROR:
					throw new Database_Exception(':error [ :query ]',
						array(':error' => pg_result_error($result), ':query' => $sql));
				case PGSQL_COPY_OUT:
				case PGSQL_COPY_IN:
					pg_end_copy($this->_connection);

					throw new Database_Exception('PostgreSQL COPY operations not supported [ :query ]',
						array(':query' => $sql));
				default:
					$rows = 0;
			}

			if (isset($benchmark))
			{
				Profiler::stop($benchmark);
			}

			$this->last_query = $sql;

			if ($type === Database::SELECT)
				return new Database_PostgreSQL_Result($result, $sql, $as_object, $params, $rows);

			if ($type === Database::INSERT)
			{
				if ($this->_config['primary_key'])
				{
					// Fetch the first column of the last row
					$insert_id = pg_fetch_result($result, $rows - 1, 0);
				}
				elseif ($insert_id = pg_send_query($this->_connection, 'SELECT LASTVAL()'))
				{
					if ($result = pg_get_result($this->_connection) AND pg_result_status($result) === PGSQL_TUPLES_OK)
					{
						$insert_id = pg_fetch_result($result, 0);
					}
				}

				return array($insert_id, $rows);
			}

			return $rows;
		}
		catch (Exception $e)
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw $e;
		}
	}

	/**
	 * Start a SQL transaction
	 *
	 * @link http://www.postgresql.org/docs/current/static/sql-set-transaction.html
	 *
	 * @param   string  $mode   Transaction mode
	 * @return  boolean
	 */
	public function begin($mode = NULL)
	{
		return $this->_command("BEGIN $mode");
	}

	/**
	 * @return bool
	 */
	public function commit()
	{
		return $this->_command('COMMIT');
	}

	/**
	 * Abort the current transaction or roll back to a savepoint
	 *
	 * @param   string  $savepoint  Savepoint name
	 * @return  boolean
	 */
	public function rollback($savepoint = NULL)
	{
		return $this->_command($savepoint ? "ROLLBACK TO $savepoint" : 'ROLLBACK');
	}

	/**
	 * Define a new savepoint in the current transaction
	 *
	 * @param   string  $name   Savepoint name
	 * @return  boolean
	 */
	public function savepoint($name)
	{
		return $this->_command("SAVEPOINT $name");
	}

	/**
	 * @link http://www.postgresql.org/docs/current/static/datatype.html#DATATYPE-TABLE
	 * @param  string  $type
	 * @return array
	 */
	public function datatype($type)
	{
		static $types = array
		(
			// PostgreSQL >= 7.4
			'box'       => array('type' => 'string'),
			'bytea'     => array('type' => 'string', 'binary' => TRUE),
			'cidr'      => array('type' => 'string'),
			'circle'    => array('type' => 'string'),
			'inet'      => array('type' => 'string'),
			'int2'      => array('type' => 'int', 'min' => '-32768', 'max' => '32767'),
			'int4'      => array('type' => 'int', 'min' => '-2147483648', 'max' => '2147483647'),
			'int8'      => array('type' => 'int', 'min' => '-9223372036854775808', 'max' => '9223372036854775807'),
			'line'      => array('type' => 'string'),
			'lseg'      => array('type' => 'string'),
			'macaddr'   => array('type' => 'string'),
			'money'     => array('type' => 'float', 'exact' => TRUE, 'min' => '-92233720368547758.08', 'max' => '92233720368547758.07'),
			'path'      => array('type' => 'string'),
			'point'     => array('type' => 'string'),
			'polygon'   => array('type' => 'string'),
			'text'      => array('type' => 'string'),

			// PostgreSQL >= 8.3
			'tsquery'   => array('type' => 'string'),
			'tsvector'  => array('type' => 'string'),
			'uuid'      => array('type' => 'string'),
			'xml'       => array('type' => 'string'),
		);

		if (isset($types[$type]))
			return $types[$type];

		return parent::datatype($type);
	}

	/**
	 * @param  string  $like
	 * @return array
	 */
	public function list_tables($like = NULL)
	{
		$this->_connection OR $this->connect();

		$sql = 'SELECT table_name FROM information_schema.tables WHERE table_schema = '.$this->quote($this->schema());

		if (is_string($like))
		{
			$sql .= ' AND table_name LIKE '.$this->quote($like);
		}

		return $this->query(Database::SELECT, $sql, FALSE)->as_array(NULL, 'table_name');
	}

	/**
	 * @param  string  $table
	 * @param  string  $like
	 * @param  bool    $add_prefix
	 * @return array
	 */
	public function list_columns($table, $like = NULL, $add_prefix = TRUE)
	{
		$this->_connection OR $this->connect();

		$sql = 'SELECT column_name, column_default, is_nullable, data_type, character_maximum_length, numeric_precision, numeric_scale, datetime_precision'
			.' FROM information_schema.columns'
			.' WHERE table_schema = '.$this->quote($this->schema())
			.' AND table_name = '.$this->quote($add_prefix ? ($this->table_prefix().$table) : $table);

		if (is_string($like))
		{
			$sql .= ' AND column_name LIKE '.$this->quote($like);
		}

		$sql .= ' ORDER BY ordinal_position';

		$result = array();

		foreach ($this->query(Database::SELECT, $sql, FALSE) as $column)
		{
			$column = array_merge($this->datatype($column['data_type']), $column);

			$column['is_nullable'] = ($column['is_nullable'] === 'YES');

			$result[$column['column_name']] = $column;
		}

		return $result;
	}

	/**
	 * @return string
	 */
	public function schema()
	{
		return $this->_config['schema'];
	}

	/**
	 * @param  string  $value
	 * @return string
	 */
	public function escape($value)
	{
		$this->_connection OR $this->connect();

		$value = pg_escape_string($this->_connection, $value);

		return "'$value'";
	}

	/**
	 * Register a new converter
	 *
	 * @param  string                                   $name       The name of the converter
	 * @param  Database_PostgreSQL_Converter_Interface  $converter  A converter instance
	 * @param  array                                    $pg_types   An array of the mapped postgresql's types
	 * @return Database_PostgreSQL
	 */
	public function register_converter(
		$name,
		Database_PostgreSQL_Converter_Interface $converter,
		array $pg_types
	)
	{
		$this->_converters[$name] = $converter;

		foreach ($pg_types as $type)
		{
			$this->_handled_types[$type] = $name;
		}

		return $this;
	}

	/**
	 * Returns a converter from its designation.
	 *
	 * @param  string  $name  Converter designation
	 * @return Database_PostgreSQL_Converter_Interface
	 */
	public function get_converter_for($name)
	{
		return $this->_converters[$name];
	}

	/**
	 * Returns the converter instance for a given a PostgreSQL's type
	 *
	 * @param   string  $pg_type Type name
	 * @return  Database_PostgreSQL_Converter_Interface
	 * @throws  Database_Exception
	 */
	public function get_converter_for_type($pg_type)
	{
		if (isset($this->_handled_types[$pg_type]))
		{
			$converter_name = $this->_handled_types[$pg_type];

			if (isset($this->_converters[$converter_name]))
			{
				return $this->_converters[$converter_name];
			}
			else
			{
				throw new Database_Exception(sprintf(
					"Pg type '%s' is associated with converter '%s' but converter is not registered.", $pg_type, $converter_name
				));
			}
		}

		throw new Database_Exception(sprintf("Could not find a converter for type '%s'.", $pg_type));
	}

	/**
	 * Associate an existing converter with a Pg type.
	 * This is useful for DOMAINs.
	 *
	 * @param  string  $type            Type name
	 * @param  string  $converter_name  Converter designation.
	 * @return Database_PostgreSQL
	 */
	public function register_type_for_converter($type, $converter_name)
	{
		$this->_handled_types[$type] = $converter_name;

		return $this;
	}

	/**
	 * Register the converters for PostgreSQL's built-in types
	 */
	protected function register_base_converters()
	{
		$this->register_converter('Array', new Database_PostgreSQL_Converter_Array($this), []);
		$this->register_converter('Boolean', new Database_PostgreSQL_Converter_Boolean, ['bool']);
		$this->register_converter('Number', new Database_PostgreSQL_Converter_Number, ['int2', 'int4', 'int8', 'numeric', 'float4', 'float8']);
		$this->register_converter('String', new Database_PostgreSQL_Converter_String, ['varchar', 'char', 'text', 'uuid', 'tsvector', 'xml', 'bpchar', 'json', 'name']);
		$this->register_converter('Timestamp', new Database_PostgreSQL_Converter_Timestamp, ['timestamp', 'date', 'time']);
		$this->register_converter('Interval', new Database_PostgreSQL_Converter_Interval, ['interval']);
		$this->register_converter('Binary', new Database_PostgreSQL_Converter_Bytea, ['bytea']);
		$this->register_converter('NumberRange', new Database_PostgreSQL_Converter_NumberRange, ['int4range', 'int8range', 'numrange']);
		$this->register_converter('TsRange', new Database_PostgreSQL_Converter_TsRange, ['tsrange', 'daterange']);
	}

} // End Kohana_Database_PostgreSQL
