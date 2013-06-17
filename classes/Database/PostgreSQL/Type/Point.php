<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Point type
 *
 * @package   Pomm
 * @version   1.1.3
 * @copyright 2011 Grégoire HUBERT
 * @author    Grégoire HUBERT <hubert.greg@gmail.com>
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Database_PostgreSQL_Type_Point {

	public $x;
	public $y;

	/**
	 * __construct
	 *
	 * @param  float  $x
	 * @param  float  $y
	 */
	public function __construct($x, $y)
	{
		$this->x = $x;
		$this->y = $y;
	}

} // End Database_PostgreSQL_Type_Point
