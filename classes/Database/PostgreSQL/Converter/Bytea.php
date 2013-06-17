<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Bytea converter
 *
 * @package   Pomm
 * @version   1.1.3
 * @copyright 2011 Grégoire HUBERT
 * @author    Grégoire HUBERT <hubert.greg@gmail.com>
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Database_PostgreSQL_Converter_Bytea implements Database_PostgreSQL_Converter_Interface {

	/**
	 * Does the job of pg_escape_bytea in PHP.
	 *
	 * @link http://php.net/manual/fr/function.pg-escape-bytea.php
	 *
	 * @param String $data Binary string to be escaped.
	 * @return String
	 */
	protected function escByteA($data)
	{
		$search  = array(chr(92), chr(0), chr(39));
		$replace = array('\\\134', '\\\000', '\\\047');
		$data    = str_replace($search, $replace, $data);

		return $data;
	}

	protected function unescByteA($data)
	{
		$search  = array('\\000', '\\\'', '\\');
		$replace = array(chr(0), chr(39), chr(92));
		$data    = str_replace($search, $replace, $data);

		$data = preg_replace_callback(
			'/\\\\([0-9]{3})/',
			function ($byte)
			{
				return chr((int) base_convert((int) $byte[1], 8, 10));
			}, $data
		);

		return $data;
	}

	/**
	 * @see Database_PostgreSQL_Converter_Interface
	 */
	public function to_pg($data, $type = NULL)
	{
		if (function_exists('pg_escape_bytea'))
		{
			return sprintf("bytea E'%s'", addcslashes(pg_escape_bytea($data), '\\'));
		}
		else
		{
			return sprintf("bytea E'%s'", addcslashes($this->escByteA($data), '\\'));
		}
	}

	/**
	 * @see Database_PostgreSQL_Converter_Interface
	 */
	public function from_pg($data, $type = NULL)
	{
		if (is_resource($data))
		{
			return stripcslashes(@stream_get_contents($data));
		}

		return $this->unescByteA(stripcslashes($data));
	}

} // End Database_PostgreSQL_Converter_Bytea
