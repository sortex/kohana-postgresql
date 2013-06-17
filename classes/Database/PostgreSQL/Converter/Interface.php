<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Interface for converters
 *
 * @package   Pomm
 * @version   1.1.3
 * @copyright 2011 Grégoire HUBERT
 * @author    Grégoire HUBERT <hubert.greg@gmail.com>
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
interface Database_PostgreSQL_Converter_Interface {

	/**
	 * Parse the output string from PostgreSQL and returns the converted value
	 * into an according PHP representation.
	 *
	 * @param  string  $data Input string from Pg row result.
	 * @param  string  $type Optional type.
	 * @return mixed   PHP representation of the data.
	 */
	public function from_pg($data, $type = NULL);

	/**
	 * Convert a PHP representation into the according Pg formatted string.
	 *
	 * @param  mixed   $data  PHP representation.
	 * @param  string  $type  Optional type.
	 * @return string  Pg converted string for input.
	 */
	public function to_pg($data, $type = NULL);

} // End Database_PostgreSQL_Converter_Interface
