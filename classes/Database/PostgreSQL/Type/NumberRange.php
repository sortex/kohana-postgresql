<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Number range type
 *
 * @package   Pomm
 * @version   1.1.3
 * @copyright 2012 Grégoire HUBERT
 * @author    Grégoire HUBERT <hubert.greg@gmail.com>
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Database_PostgreSQL_Type_NumberRange {

	public $start;
	public $end;
	public $start_included;
	public $end_included;

	public function __construct($start, $end, $start_included = FALSE, $end_included = FALSE)
	{
		$this->start          = $start;
		$this->end            = $end;
		$this->start_included = (bool) $start_included;
		$this->end_included   = (bool) $end_included;
	}

} // End Database_PostgreSQL_Type_NumberRange
