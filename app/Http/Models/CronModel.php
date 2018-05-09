<?php

namespace App\Http\Models;

use DB;


class CronModel
{
	public static function get_review_recipients()
	{
		// Get all the non-reviewed bookings
		// 
		// @temp 
		// is_confirmed functionality is still 
		// subject for final approval.
		$bookings = 	DB::table('booking as a')
						->join('timetable as b', 'b.timetable_id', '=', 'a.timetable_id')
						->join('employee as c', 'c.employee_id', '=', 'b.employee_id')
						->join('client as d', 'd.client_id', '=', 'a.client_id')
						->leftJoin('reviews as e', 'e.booking_id', '=', 'a.booking_id')
						->where('c.is_active', '=', 1)
						->where('d.is_active', '=', 1)
						->where('a.is_confirmed', '=', 1)
						->where('a.for_review', '=', 0)
						->whereNull('e.review_id')
						->select(
							'a.booking_id',
							'b.timetable_id',
							'b.employee_id',
							'e.review_id',
							'a.client_id',
							DB::raw("CONCAT(c.firstname, ' ', c.lastname) as employee_name"),
							'd.fullname as client_name',
							'd.email',
							'a.schedule_start',
							'a.schedule_end',
							'b.date_available',
							'a.for_review'
						)->get();


		// Filter bookings that has already been
		// finished (past 5 minutes or more after 
		// the booking schedule end)
		if ($bookings) {

			// For local
			if (ENVIRONMENT === 'local') {
				return $bookings;
			}

			// For production (filtered)
			foreach ($bookings as $i => $booking) {

				$date = $booking->date_available;
				$time = $booking->schedule_end;

				$datetime = strtotime("{$date} {$time}");
				$now = strtotime(date(TIMESTAMP_FORMAT));


				// Get time offset in minutes
				$offset = ($now - $datetime) / 60;

				// 5 Minutes (300 secs)
				$limit = 300;


				$booking->offset = $offset;

				// Disregard bookings that has not 
				// yet been done.
				if ($offset < $limit) {

					unset($bookings[$i]);
				}
			}
		}

		return $bookings;
	}

	public static function update_review_status($booking_id = 0)
	{

		if ($booking_id) {

			DB::table('booking')
			->where('booking_id', '=', $booking_id)
			->update(array(
				'for_review' => 1
			));
		}
	}

	public static function get_running_slots()
	{
		return	DB::table('booking as a')
				->join('timetable as b', 'b.timetable_id', '=', 'a.timetable_id')
				->join('client as c', 'c.client_id', '=', 'a.client_id')
				->where('a.is_confirmed', '=', 1)
				->where('a.for_reminder', '=', 0)
				->select(
					'a.booking_id',
					'a.timetable_id',
					'a.client_id',
					'a.price',

					'c.fullname as client_name',
					'c.email',

					'b.date_available as date_start',
					'a.schedule_start',
					'a.schedule_end',

					'a.confirmed_at',

					DB::raw("DATE(NOW()) as date_now"),
					DB::raw("DATEDIFF(DATE(b.date_available), DATE(a.confirmed_at)) as countdown")
				)->get();
	}

	public static function update_booking_reminder($booking_id = array())
	{
		$booking_id = typecast($booking_id, 'array');

		if ($booking_id) {

			DB::table('booking')
			->whereIn('booking_id', $booking_id)
			->update(array(
				'for_reminder' => 1
			));
		}
	}
}
