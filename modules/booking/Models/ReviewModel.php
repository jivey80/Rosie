<?php

namespace Modules\Booking\Models;

use DB;


class ReviewModel
{
	/**
	 * Fetches review information for the 
	 * frontend information display purpose.
	 */
	public static function get_review_summary($employee_id = 0)
	{
		$employee_id = typecast($employee_id, 'int');

		$records = self::get_review_record($employee_id, true);

		if ($records) {

			return array(
				'rate' => $records->rating,
				'reviews' => $records->count
			);

		} else {

			return array(
				'rate' => 0,
				'reviews' => 0
			);
		}
	}

	/**
	 * Gets the summary of review records
	 * of a particular cleaner.
	 */
	public static function get_review_record($employee_id = 0, $get_summary = false)
	{
		if ($get_summary) {

			return 	DB::table('employee as a')
					->join('reviews as b', 'b.employee_id', '=', 'a.employee_id')
					->where('a.employee_id', '=', $employee_id)
					->where('a.is_active', '=', 1)
					->select(
						DB::raw("CONCAT(a.firstname, ' ', a.lastname) as employee"),
						'a.firstname',
						'a.lastname',
						'a.avatar',
						DB::raw("TRUNCATE(AVG(b.rating), 1) as rating"),
						DB::raw('COUNT(b.rating) as count')
					)->first();

		} else {

			return 	DB::table('reviews as a')
					->join('client as b', 'b.client_id', '=', 'a.client_id')
					->join('employee as c', 'c.employee_id', '=', 'a.employee_id')
					->where('a.employee_id', '=', $employee_id)
					->where('b.is_active', '=', 1)
					->where('c.is_active', '=', 1)
					->select(
						'b.client_id',
						'b.fullname',
					    'a.*',
					    DB::raw("CONCAT(c.firstname, ' ', c.lastname) as employee"),
					    'c.avatar'
					)->get();
		}
	}


	/**
	 * Checks the status of a booked slot
	 * if already reviewed by the client or not.
	 */
	public static function is_reviewed($booking_id = 0, $client_id)
	{
		if ($booking_id and $client_id) {

			$check =	DB::table('reviews')
						->where('booking_id', '=', $booking_id)
						->where('client_id', '=', $client_id)
						->count();

			return $check ? true : false;
		}

		return false;
	}

	public static function save_review($fields = array())
	{

		if ($fields and isset($fields['booking_id']) and isset($fields['client_id'])) {

			$is_reviewed = self::is_reviewed($fields['booking_id'], $fields['client_id']);

			if (! $is_reviewed) {

				DB::table('reviews')->insert($fields);
			}
		}
	}
}
