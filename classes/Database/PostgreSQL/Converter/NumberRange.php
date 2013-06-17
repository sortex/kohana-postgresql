<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Number range converter
 *
 * @package   Pomm
 * @version   1.1.3
 * @copyright 2012 Grégoire HUBERT
 * @author    Grégoire HUBERT <hubert.greg@gmail.com>
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Database_PostgreSQL_Converter_NumberRange implements Database_PostgreSQL_Converter_Interface {

	protected $class_name;

	/**
	 * __construct()
	 *
	 * @param  string  $class_name  Optional fully qualified TsRange type class name.
	 */
	public function __construct($class_name = 'Database_PostgreSQL_Type_NumberRange')
	{
		$this->class_name = $class_name;
	}

	/**
	 * @see Database_PostgreSQL_Converter_Interface
	 */
	public function from_pg($data, $type = NULL)
	{
		if ( ! preg_match('/([\[\(])(-?[0-9\.]+),-?([0-9\.]+)([\]\)])/', $data, $matchs))
		{
			throw new Database_Exception(sprintf("Bad number range representation '%s' (asked type '%s').", $data, $type));
		}

		return new $this->class_name($matchs[2] + 0, $matchs[3] + 0, $matchs[1] === '[', $matchs[4] === ']');
	}

	/**
	 * @see Database_PostgreSQL_Converter_Interface
	 */
	public function to_pg($data, $type = NULL)
	{
		if ( ! $data instanceof Database_PostgreSQL_Type_NumberRange)
		{
			throw new Database_Exception(sprintf(
				"NumberRange converter expects 'NumberRange' data to convert. '%s' given.", gettype($data)
			));
		}

		return sprintf(
			"%s '%s%s, %s%s'", $type, $data->start_included ? '[' : '(', $data->start + 0, $data->end + 0,
			$data->end_included ? ']' : ')'
		);
	}

} // End Database_PostgreSQL_Converter_NumberRange
