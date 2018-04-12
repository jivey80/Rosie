<?php

namespace Modules\Booking\Models;

use Modules\Booking\Models\GeneratorModel;
use Modules\Booking\Models\ValidatorModel;
use Modules\Booking\Models\ReviewModel;
use App\Libraries\Calc;
use DB;


class BookingModel
{
	/*!
	 * @param  objarr $timetable GeneratorModel::get_active_timetable()
	 * @param  array  $slots     Generated schedule as per given hour interval
	 * @return array
	 */
	public static function get_schedule($timetable = array(), $slots = array())
	{
		$result = array();

		if ($timetable) {

			// Groups slots by timetable
			$groups = array();
			foreach ($slots as $slot) {
				$tid = $slot['timetable_id'];
				$groups[$tid][] = $slot;
			}


			foreach ($timetable as $i => $record) {

				$timetable_id = $record->timetable_id;

				$review = ReviewModel::get_review_summary($record->employee_id);

				// @temp
				if (isset($groups[$timetable_id])) {
					
					$slotlist = $groups[$timetable_id];

					$result[$i] = array(
						'timetable_id' 	=> $timetable_id, 

						'firstname' => ucfirst($record->firstname),
						'lastname' 	=> ucfirst($record->lastname),
						'avatar' 	=> $record->avatar,
						'rating' 	=> $review['rate'],
						'reviews' 	=> $review['reviews'],

						'date_available' => date(BOOKING_TIMESTAMP, strtotime($record->date_available)),

						'cheapest' => Calc::get_cheapest_price($slotlist, true)
					);
				}

			}
		}

		return $result;
	}

	public static function save_booking($data = array())
	{

		if ($data and is_array($data)) {

			$book_id = $data['tmp_book_id'];
			$interval = $data['tmp_interval'];

			$timetable_id = $data['timetable_id'];
			$schedule_start = $data['schedule_start'];
			$schedule_end = $data['schedule_end'];

			// Check if the data is already 
			// existing and prevent if it does.
			$check = 	DB::table('booking as a')
						->join('timetable as b', 'a.timetable_id', '=', 'b.timetable_id')
						->join('employee as c', 'b.employee_id', '=', 'c.employee_id')
						->join('client as d', 'a.client_id', '=', 'd.client_id')
						
						->where('a.timetable_id', '=', $timetable_id)
						->where('a.schedule_start', '=', $schedule_start)
						->where('a.schedule_end', '=', $schedule_end)
						->where('c.is_active', '=', 1)
						->where('d.is_active', '=', 1)

						->orderBy('a.created_at', 'DESC')
						->select(
							'a.booking_id',
							'a.need_supplies',
							'a.instructions',
							'a.schedule_start',
							'a.schedule_end',
							'a.price',
							'a.payload',
							
							'a.timetable_id',
							'b.employee_id',
							DB::raw("CONCAT(c.firstname, ' ', c.lastname) as cleaner_name"),
							
							'd.client_id',
							'd.fullname AS client_name',
							
							'a.created_at'
						)->get();


			$timetable = GeneratorModel::get_active_timetable();
			$slotlist = GeneratorModel::generate_schedule($timetable, $interval);
			$find = GeneratorModel::list_schedule($timetable_id, $slotlist, $book_id);


			if (!count($check) and !$find->is_booked) {

				unset($data['tmp_book_id']);
				unset($data['tmp_interval']);

				// Save booking data
				return array(
					'booking_id' => DB::table('booking')->insertGetId($data)
				);
			
			} else if (count($check)) {

				return 'booked';
			}

		}

		return false;

	}

	public static function confirm_booking($booking_id = 0, $client_id = 0, $group_id = 0)
	{

		if ($booking_id and $client_id and $group_id) {

			$data = explode('_', $group_id);

			if (count($data) === 3) {

				$timetable_id = typecast(substr($data[0], 1), 'int');

				$check = 	DB::table('booking')
							->where('booking_id', '=', $booking_id)
							->where('client_id', '=', $client_id)
							->where('timetable_id', '=', $timetable_id)
							->where('is_confirmed', '=', 0)
							->first();

				if ($check) {

					date_default_timezone_set('Asia/Manila');

					DB::table('booking')
					->where('booking_id', '=', $booking_id)
					->where('client_id', '=', $client_id)
					->where('timetable_id', '=', $timetable_id)
					->update(array(
						'is_confirmed' => 1,
						'confirmed_at' => date(TIMESTAMP_FORMAT)
					));

					return true;
				}

			}

			return false;
		}
	}
}
