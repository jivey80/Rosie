<?php

if (! function_exists('get_display_range')) {

	function get_display_range($offset = 0, $show_text = false)
	{	
		$format = defined('DATE_FORMAT') ? DATE_FORMAT : 'Y-m-d';

		$now = time();
		$nxt = strtotime("+{$offset} days", $now);

		$days = array();
		$start	= (int) date('d', $now);
		for ($i = $start; $i <= $offset; $i++) {
			$nxt = strtotime("+{$i} days", $now);
			$days[] = date('l, F d', $nxt);
		}

		$result = array(
			'from'	=> date($format, $now),
			'to'	=> date($format, $nxt)
		);

        if ($show_text) {
            $result['days'] = $days;
        }

        return $result;
	}
}

if (! function_exists('strip_decimal')) {

	/**
	 * Strips decimal values with the custom
	 * precision. 
	 * 
	 * Defaults to 2 decimal places.
	 * 
	 * @param  integer $value
	 * @param  integer $precision
	 * @return float
	 */
	function strip_decimal($value = 0, $precision = 2)
	{
		return (float) sprintf("%.0{$precision}f", $value);
	}
}

if (! function_exists('timetosec')) {

	/**
	 * Gets all the number of seconds of
	 * any given time.
	 * 
	 * @param  string $time
	 * @return interger
	 */
	function timetosec($time)
	{
		return strtotime("1970-01-01 {$time} UTC");
	}
}

if (! function_exists('in_between')) {

	/**
	 * Checks if a given number is
	 * in between of the defined range value.
	 * 
	 * @param  integer $number Number to compare from range
	 * @param  integer $from   Start range
	 * @param  integer $to     End range
	 * @return boolean
	 */
    function in_between($number = 0, $from = 0, $to = 0)
    {

        $number = (float) $number;
        $from = (float) $from;
        $to = (float) $to;

        return ($number > $from && $number < $to) ? true : false;
    }
}

if (! function_exists('schedule_autocorrect')) {

	/**
	 
	 * 
	 * @param  [type] $sec_start [description]
	 * @param  [type] $sec_end   [description]
	 * @param  [type] $interval  [description]
	 * @param  [type] $booked    [description]
	 * @return [type]            [description]
	 */
	/**
	 * Generates the next slot schedule
	 * based from the given booked slots array (if any)
	 * as basis of optimization.
	 * 
	 * @param  integer $sec_start Pre-computed start of schedule
	 * @param  integer $sec_end   Pre-computed end of schedule
	 * @param  integer $interval  Hour interval for schedule generator
	 * @param  array   $booked    Records of booked slots from the database
	 * @return array
	 */
    function schedule_autocorrect($sec_start = 0, $sec_end = 0, $interval = 0, $booked = array())
    {
        if ($booked) {

            $_sec_start = date(TIME_FULL_FORMAT, $sec_start);
            $_sec_end = date(TIME_FULL_FORMAT, $sec_end);


            $is_booked = false;
            $_time_start = $sec_start;
            $_time_end = $sec_end;
            
            $correction_info = array();
            $correction_basis = array();

            foreach ($booked as $i => $booking) {

                $_schedule_start = date(TIME_FULL_FORMAT, strtotime($booking->schedule_start));
                $_schedule_end = date(TIME_FULL_FORMAT, strtotime($booking->schedule_end));

                $schedule_start = strtotime($_schedule_start);
                $schedule_end = strtotime($_schedule_end);

                // Check if one of the booked schedules
                // goes between the auto-generated start and end time.
                if (in_between($schedule_start, $sec_start, $sec_end)) {

                    $correction_info['info']['condition'] = "1|{$_schedule_start} [{$_sec_start} || {$_sec_end}]";

                    $is_booked = true;
                }

                if (in_between($schedule_end, $sec_start, $sec_end)) {
                    $correction_info['info']['condition'] = "2|{$_schedule_end} [{$_sec_start} || {$_sec_end}]";
                    $is_booked = true;

                    $_time_start = $sec_start;
                    $_time_end = $sec_end;
                }

                // Check if the auto-generated schedule goes
                // between the list of booked schedule.
                if (in_between($sec_start, $schedule_start, $schedule_end)) {

                    $correction_info['info']['condition'] = "3|{$_sec_start} [{$_schedule_start} || {$_schedule_end}]";
                    $is_booked = true;

                    $_time_start = $sec_start;
                    $_time_end = $schedule_end;
                }

                if (in_between($sec_end, $schedule_start, $schedule_end)) {

                    $correction_info['info']['condition'] = "4|{$_sec_end} [{$_schedule_start} || {$_schedule_end}]";
                    $is_booked = true;

                    $_time_start = $sec_start;
                    $_time_end = $sec_end;
                }


                if ($is_booked) {

                    $correction_info['index'] = $i;

                    $correction_info['info']['booked_start'] = $_schedule_start;
                    $correction_info['info']['booked_end'] = $_schedule_end;

                    $correction_info['info']['auto_start'] = $_sec_start;
                    $correction_info['info']['auto_end'] = $_sec_end;

                    $correction_info['info']['corrected_start'] = date(TIME_FULL_FORMAT, $_time_start);
                    $correction_info['info']['corrected_end'] = date(TIME_FULL_FORMAT, $_time_end);

                    $correction_basis = $booking;
                    $correction_info['info']['correction_basis'] = $correction_basis;

                    break;
                }
            }

            if (! $is_booked) {
                $_interval = 60 * (60 * $interval);
                $_time_end = $_time_start + $_interval;
            }

            $correction_info['is_booked'] = $is_booked;
            $correction_info['time_start'] = $_time_start;
            $correction_info['time_end'] = $_time_end;
            $correction_info['hour_range'] = $_time_end - $_time_start;

            // Contains the slot coming from the
            // database that has been used as the 
            // basis of generating the correction for this
            // given time start and end.
            if ($correction_basis and strtotime($correction_basis->schedule_end) > $_time_start) {

                $correction_info['correction_time_end'] = strtotime($correction_basis->schedule_end);

            } else {

                $correction_info['correction_time_end'] = 0;
            }


            return $correction_info;
            
        } else {

            return array(
                'is_booked' => false,
                
                'time_start' => $sec_start,
                'time_end' => $sec_end,

                'hour_range' => $sec_end - $sec_start
            );
        }
    }
}
