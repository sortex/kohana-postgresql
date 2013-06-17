<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Date interval converter
 *
 * @package   Pomm
 * @version   1.1.3
 * @copyright 2011 Grégoire HUBERT
 * @author    Grégoire HUBERT <hubert.greg@gmail.com>
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Database_PostgreSQL_Converter_Interval implements Database_PostgreSQL_Converter_Interface {

	/**
	 * @see Database_PostgreSQL_Converter_Interface
	 */
	public function from_pg($data, $type = NULL)
	{
		// if IntervalStyle is 'iso_8601'
		if (preg_match("/^P/", $data))
		{
			return new DateInterval($data);
		}

		// if IntervalStyle is 'postgres'
		if (
			preg_match(
				"/(?:([0-9]+) years ?)?(?:([0-9]+) mons ?)?(?:([0-9]+) days ?)?(?:([0-9]{1,2}):([0-9]{1,2}):([0-9]+))?/",
				$data,
				$matchs
			)
		)
		{
			return DateInterval::createFromDateString(
				sprintf(
					"%d years %d months %d days %d hours %d minutes %d seconds",
					array_key_exists(1, $matchs) ? (is_null($matchs[1]) ? 0 : (int) $matchs[1]) : 0,
					array_key_exists(2, $matchs) ? (is_null($matchs[2]) ? 0 : (int) $matchs[2]) : 0,
					array_key_exists(3, $matchs) ? (is_null($matchs[3]) ? 0 : (int) $matchs[3]) : 0,
					array_key_exists(4, $matchs) ? (is_null($matchs[4]) ? 0 : (int) $matchs[4]) : 0,
					array_key_exists(5, $matchs) ? (is_null($matchs[5]) ? 0 : (int) $matchs[5]) : 0,
					array_key_exists(6, $matchs) ? (is_null($matchs[6]) ? 0 : (int) $matchs[6]) : 0
				)
			);
		}

		throw new Database_Exception(sprintf("Data '%s' is not a supported pg interval representation.", $data));
	}

	/**
	 * @see Database_PostgreSQL_Converter_Interface
	 */
	public function to_pg($data, $type = NULL)
	{
		if ( ! $data instanceof DateInterval)
		{
			$data = DateInterval::createFromDateString($data);
		}

		return sprintf("interval '%s'", $data->format('%Y years %M months %D days %H:%i:%S'));
	}

} // End Database_PostgreSQL_Converter_Interval
