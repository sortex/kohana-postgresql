<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Date and timestamp converter
 *
 * @package   Pomm
 * @version   1.1.3
 * @copyright 2011 Grégoire HUBERT
 * @author    Grégoire HUBERT <hubert.greg@gmail.com>
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Database_PostgreSQL_Converter_Timestamp implements Database_PostgreSQL_Converter_Interface {

	/**
	 * @see Database_PostgreSQL_Converter_Interface
	 */
	public function from_pg($data, $type = NULL)
	{
		return new DateTime($data);
	}

	/**
	 * @see Database_PostgreSQL_Converter_Interface
	 */
	public function to_pg($data, $type = NULL)
	{
		if ( ! $data instanceof DateTime)
		{
			$data = new DateTime($data);
		}

		return sprintf("%s '%s'", $type, $data->format('Y-m-d H:i:s.u'));
	}

} // End Database_PostgreSQL_Converter_Timestamp
