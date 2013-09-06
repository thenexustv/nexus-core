<?php

class Nexus_Utility {

	public static function human_list($array, $sep = ', ', $join = ' and ') {
	  if ( 1 == sizeof($array) ) return $array[0];
	  $last  = array_slice($array, -1);
	  $first = join($sep, array_slice($array, 0, -1));
	  $both  = array_filter(array_merge(array($first), $last));
	  return join($join, $both);
	}

	public static function human_duration($length) {
		/* this works for standard powerpress times 01:23:45 | hour | minute | second */
		$parts = explode(':', $length);
		$times = array( array('hour', 'hours'), array('minute', 'minutes'), array('second, seconds') );
		$output = '';
		/* ignore seconds */
		for ($i = 0; $i < 2; $i++) {
			$value = (int)$parts[$i];
			if ( $value == 0 ) continue;
			$word = ( $value == 1 ? $times[$i][0] : $times[$i][1] );
			$output = $output . ($value . ' ' . $word . ' ');
		}

		return trim($output);
	}

	public static function human_filesize($size) {
		$base = 1024;
		$sizes = array('B', 'KB', 'MB', 'GB', 'TB');
		$place = 0;
		for (; $size > $base; $place++) { 
			$size /= $base;
		}
		return round($size, 2) . ' ' . $sizes[$place];
	}

	/**
	 * Creates a human readable time, relative to supplied data
	 * @param type $from 
	 * @param type $to 
	 * @return string
	 */
	public static function human_time_difference($from, $to = '') {
		if ( empty($to) ) {
			$to = time();
		}
		
		$periods = array(
			'minutes' => array('%s minute', '%s minutes'),
			'hours' => array('%s hour', '%s hours'),
			'days' => array('%s day', '%s days'),
			'weeks' => array('%s week', '%s weeks'),
			'months' => array('%s month', '%s months'),
			'years' => array('%s year', '%s years')
		);
		$ranges = array(
			'years' => 31556926,
			'months' => 2592000,
			'weeks' => 604800,
			'days' => 86400,
			'hours' => 3600,
			'minutes' => 60
		);


		$diff = (int) abs($to - $from);
		$since = '';

		foreach ($ranges as $unit => $value) {
			
			if ( $diff <= $value ) continue;

			$time = round($diff / $value);
			if ( $time < 1 ) {
				$time = 1;
			}

			$since = sprintf(_n($periods[$unit][0], $periods[$unit][1], $time), $time);
			break;
		}


		return $since;
	}




}