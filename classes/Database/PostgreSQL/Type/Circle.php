<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Circle type
 *
 * @package   Pomm
 * @version   1.1.3
 * @copyright 2011 Grégoire HUBERT
 * @author    Grégoire HUBERT <hubert.greg@gmail.com>
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Database_PostgreSQL_Type_Circle {

	public $center;
	public $radius;

	/**
	 * __construct
	 *
	 * @param  Database_PostgreSQL_Type_Point  $center
	 * @param  integer                         $radius
	 */
	public function __construct(Database_PostgreSQL_Type_Point $center, $radius)
	{
		$this->center = $center;
		$this->radius = $radius;
	}

} // End Database_PostgreSQL_Type_Circle
