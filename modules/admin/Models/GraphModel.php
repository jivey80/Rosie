<?php

namespace Modules\Admin\Models;

use DB;


class GraphModel
{
	public static function dashboard_booking_stats()
	{
		$rows = 	DB::table('booking as a')
					->join('timetable as b', 'b.timetable_id', '=', 'a.timetable_id')
					->whereRaw("b.date_available BETWEEN DATE_FORMAT(NOW(), '%Y-%m-01') AND LAST_DAY(CURDATE())")
					->groupBy('b.date_available')
					->orderBy('b.date_available')
					->select(
						DB::raw("DATE_FORMAT(b.date_available, '%e') as x_axis"),
						DB::raw("COUNT(a.booking_id) as y_axis")
					)->get();

		if ($rows) {

			$month = date('F');

			return grid_data($rows, array(
				'title' 	=> "Bookings for the Month of {$month}",
				'tooltip' 	=> 'Total Bookings',
				'x_axis'	=> 'Days',
				'y_axis' 	=> 'Bookings',
				'days' 		=> date('t')
			));

		} else {

			return false;
		}
	}
}
