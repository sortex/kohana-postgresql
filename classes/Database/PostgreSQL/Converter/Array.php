<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Array converter
 *
 * @package   Pomm
 * @version   1.1.3
 * @copyright 2011 Grégoire HUBERT
 * @author    Grégoire HUBERT <hubert.greg@gmail.com>
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Database_PostgreSQL_Converter_Array implements Database_PostgreSQL_Converter_Interface {

	protected $database;

	/**
	 * CTOR
	 *
	 * @param  Database_PostgreSQL  $database
	 */
	public function __construct(Database_PostgreSQL $database)
	{
		$this->database = $database;
	}

	/**
	 * @see Database_PostgreSQL_Converter_Interface
	 */
	public function from_pg($data, $type = NULL)
	{
		if (is_null($type))
		{
			throw new Database_Exception(sprintf('Array converter must be given a type.'));
		}

		if ($data !== "{NULL}" and $data !== "{}")
		{
			$converter = $this->database->get_converter_for_type($type);

			return array_map(
				function ($val) use ($converter, $type)
				{
					return $val !== "NULL" ? $converter->from_pg(str_replace('\\"', '"', $val), $type) : NULL;
				}, str_getcsv(str_replace('\\\\', '\\', trim($data, "{}")))
			);
		}
		else
		{
			return array();
		}
	}

	/**
	 * @see Database_PostgreSQL_Converter_Interface
	 */
	public function to_pg($data, $type = NULL)
	{
		if (is_null($type))
		{
			throw new Database_Exception(sprintf('Array converter must be given a type.'));
		}
		if ( ! is_array($data))
		{
			if (is_null($data))
			{
				return 'NULL';
			}

			throw new Database_Exception(sprintf("Array converter toPg() data must be an array ('%s' given).", gettype($data)));
		}

		$converter = $this->database->get_converter_for_type($type);

		return sprintf(
			'ARRAY[%s]::%s[]', join(
				',', array_map(
					function ($val) use ($converter, $type)
					{
						return ! is_null($val) ? $converter->to_pg($val, $type) : 'NULL';
					}, $data
				)
			), $type
		);
	}

} // End Database_PostgreSQL_Converter_Array
