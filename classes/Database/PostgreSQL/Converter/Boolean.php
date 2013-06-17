<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Boolean converter
 *
 * @package   Pomm
 * @version   1.1.3
 * @copyright 2011 Grégoire HUBERT
 * @author    Grégoire HUBERT <hubert.greg@gmail.com>
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Database_PostgreSQL_Converter_Boolean implements Database_PostgreSQL_Converter_Interface {

	/**
	 * @see Database_PostgreSQL_Converter_Interface
	 */
	public function from_pg($data, $type = NULL)
	{
		return ($data == 't');
	}

	/**
	 * @see Database_PostgreSQL_Converter_Interface
	 */
	public function to_pg($data, $type = NULL)
	{
		return $data ? 'true' : 'false';
	}

} // End Database_PostgreSQL_Converter_Boolean
