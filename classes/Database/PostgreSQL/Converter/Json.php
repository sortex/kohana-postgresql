<?php defined('SYSPATH') or die('No direct script access.');
/**
 * JSON converter
 *
 * @package   xxx
 * @version   xxx
 * @copyright Sortex LTD
 * @author    Yakir Hanoch <yakir@sortex.com>
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Database_PostgreSQL_Converter_Json implements Database_PostgreSQL_Converter_Interface {

	/**
	 * @see Database_PostgreSQL_Converter_Interface
	 */
	public function to_pg($data, $type = NULL)
	{
		return json_encode($data);
	}

	/**
	 * @see Database_PostgreSQL_Converter_Interface
	 */
	public function from_pg($data, $type = NULL)
	{
		return json_decode($data);
	}

} // End Database_PostgreSQL_Converter_Json