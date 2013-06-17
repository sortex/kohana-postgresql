<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Geometric Point converter
 *
 * @package   Pomm
 * @version   1.1.3
 * @copyright 2011 Grégoire HUBERT
 * @author    Grégoire HUBERT <hubert.greg@gmail.com>
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Database_PostgreSQL_Converter_Point implements Database_PostgreSQL_Converter_Interface {

	protected $class_name;

	/**
	 * __construct()
	 *
	 * @param  string $class_name  Optional fully qualified Point type class name.
	 */
	public function __construct($class_name = 'Database_PostgreSQL_Type_Point')
	{
		$this->class_name = $class_name;
	}

	/**
	 * @see Database_PostgreSQL_Converter_Interface
	 */
	public function from_pg($data, $type = NULL)
	{
		if ( ! preg_match('/([0-9e\-+\.]+,[0-9e\-+\.]+)/', $data))
		{
			throw new Database_Exception(sprintf("Bad point representation '%s' (asked type '%s').", $data, $type));
		}

		list($x, $y) = preg_split("/,/", trim($data, "()"));

		return new $this->class_name($x, $y);
	}

	/**
	 * @see Database_PostgreSQL_Converter_Interface
	 */
	public function to_pg($data, $type = NULL)
	{
		if ( ! $data instanceof $this->class_name)
		{
			if ( ! is_object($data))
			{
				$type = gettype($data);
			}
			else
			{
				$type = get_class($data);
			}

			throw new Database_Exception(sprintf(
				"Converter Point needs data to be an instance of Database_PostgreSQL_Type_Point ('%s' given).", $type
			));
		}

		return sprintf("point(%.9e, %.9e)", $data->x, $data->y);
	}

} // End Database_PostgreSQL_Converter_Point
