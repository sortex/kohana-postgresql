<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Geometric Circle converter
 *
 * @package   Pomm
 * @version   1.1.3
 * @copyright 2011 Grégoire HUBERT
 * @author    Grégoire HUBERT <hubert.greg@gmail.com>
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Database_PostgreSQL_Converter_Circle implements Database_PostgreSQL_Converter_Interface {

	protected $class_name;
	protected $point_converter;

	/**
	 * __construct()
	 *
	 * @param  string                               $class_name       Optional fully qualified Circle type class name
	 * @param  Database_PostgreSQL_Converter_Point  $point_converter  Point converter to be used
	 */
	public function __construct($class_name = 'Database_PostgreSQL_Type_Circle', Database_PostgreSQL_Converter_Point $point_converter = NULL)
	{
		$this->class_name      = $class_name;
		$this->point_converter = is_null($point_converter) ? new Database_PostgreSQL_Converter_Point : $point_converter;
	}

	/**
	 * @see Database_PostgreSQL_Converter_Interface
	 */
	public function from_pg($data, $type = NULL)
	{
		$data = trim($data, '<>');

		$elts = preg_split('/[,\s]*(\([^\)]+\))[,\s]*|[,\s]+/', $data, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

		if (count($elts) !== 2)
		{
			throw new Database_Exception(sprintf("Cannot parse circle data '%s'.", $data));
		}

		return new Database_PostgreSQL_Type_Circle($this->point_converter->from_pg($elts[0]), $elts[1]);
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
				"Converter Circle needs data to be an instance of '%s' ('%s' given).", $this->class_name, $type
			));
		}

		return sprintf(
			"circle(%s, %s)", $this->point_converter->to_pg($data->center), $data->radius
		);
	}

} // End Database_PostgreSQL_Converter_Circle
