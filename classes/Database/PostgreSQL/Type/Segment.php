<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Segment type
 *
 * @package   Pomm
 * @version   1.1.3
 * @copyright 2011 Grégoire HUBERT
 * @author    Grégoire HUBERT <hubert.greg@gmail.com>
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Database_PostgreSQL_Type_Segment {

	public $point_a;
	public $point_b;

	/**
	 * __construct
	 *
	 * @param  Database_PostgreSQL_Type_Point  $point_a
	 * @param  Database_PostgreSQL_Type_Point  $point_b
	 */
	public function __construct(Database_PostgreSQL_Type_Point $point_a, Database_PostgreSQL_Type_Point $point_b)
	{
		$this->point_a = $point_a;
		$this->point_b = $point_b;
	}

} // End Database_PostgreSQL_Type_Segment
