<?php defined('SYSPATH') or die('No direct script access.');
/**
 * HStore converter
 *
 * @package   Pomm
 * @version   1.1.3
 * @copyright 2011 Grégoire HUBERT
 * @author    Grégoire HUBERT <hubert.greg@gmail.com>
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Database_PostgreSQL_Converter_HStore implements Database_PostgreSQL_Converter_Interface {

	/**
	 * @see Database_PostgreSQL_Converter_Interface
	 */
	public function from_pg($data, $type = NULL)
	{
		if ($data === 'NULL')
		{
			return NULL;
		}

		@eval(sprintf("\$hstore = array(%s);", $data));

		if ( ! (isset($hstore) AND is_array($hstore)))
		{
			throw new Database_Exception(sprintf("Could not parse hstore string '%s' to array.", $data));
		}

		return $hstore;
	}

	/**
	 * @see Database_PostgreSQL_Converter_Interface
	 */
	public function to_pg($data, $type = NULL)
	{
		if ( ! is_array($data))
		{
			throw new Database_Exception(sprintf(
				"HStore::toPg takes an associative array as parameter ('%s' given).", gettype($data)
			));
		}

		$insert_values = array();

		foreach ($data as $key => $value)
		{
			if (is_null($value))
			{
				$insert_values[] = sprintf('"%s" => NULL', $key);
			}
			else
			{
				$insert_values[] = sprintf('"%s" => "%s"', addcslashes($key, '\"'), addcslashes($value, '\"'));
			}
		}

		return sprintf("%s(\$hst\$%s\$hst\$)", $type, join(', ', $insert_values));
	}

} // End Database_PostgreSQL_Converter_HStore
