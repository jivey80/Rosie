<?php

namespace Modules\Admin\Models;


use Modules\Booking\Models\GeneratorModel;
use Modules\Booking\Models\ValidatorModel;
use Modules\Booking\Models\ReviewModel;
use DB;
use Msg;
use Aes;


class ExpressBookModel
{
	public static function filter_data($data = array())
	{
		$record = array();


		if ($data and is_array($data)) {

			$match = array(
				'email'				=> 'email',
				'date'  			=> 'date_available',
				'cleaner'			=> 'employee_id',
				'time_start'		=> 'schedule_start',
				'time_end'			=> 'schedule_end',
				'price'				=> 'price',
				'supplies'			=> 'need_supplies',
				'instruction_area'	=> 'instructions'
			);

			foreach ($match as $param => $column) {

				if (isset($data[$param])) {

					$value = $data[$param];
				
					$record[$column] = $value;
				}
			}

			return $record;
		}

		return Msg::error('Invalid initial data format.');
	}

	public static function validate_timeslot($data = array())
	{
		if ($data and is_array($data)) {


			if (isset($data['email'], $data['date_available'], $data['employee_id'])) {

				$email = $data['email'];
				$date = $data['date_available'];
				$employee_id = $data['employee_id'];
				

				// Email check
				$get_client = ValidatorModel::email($email, true);

				if (! $get_client) {
					return Msg::error('Cannot find the email from the records. Please enter a valid client account email.');
				}

				// Timetable check
				$get_timetable = 	DB::table('timetable')
									->where('date_available', '=', $date)
									->where('employee_id', '=', $employee_id)
									->select('timetable_id')
									->first();

				if (! $get_timetable) {
					return Msg::error('Cleaner is not available on the selected time range.');
				}

				// Selected time range check
				$time_start = strtotime($data['schedule_start']);
				$time_end = strtotime($data['schedule_end']);
				$time_off = ($time_end - $time_start) / 60;

				if ($time_start === $time_end or $time_start > $time_end) {
					return Msg::error('Please enter a valid start and end time range.');
				}


				// Return final booking data
				if ($get_client and $get_timetable) {

					$client_id = $get_client['client_id'];
					$timetable_id = $get_timetable->timetable_id;


					// Generate group id identical to the actual 
					// booking process requirement.
					// t1_o1_1.5
					$interval = $time_off / 60;
					$group_id = "t{$timetable_id}_o0_{$interval}";


					// Generate email confirmation link
					$payload = Aes::payload(array(
						'c_id' => $client_id,
						's_id' => $group_id,
						'prc' => $data['price']
					));

					$data['client_id'] = $client_id;
					$data['timetable_id'] = $timetable_id;
					$data['payload'] = $payload;

					$data['tmp_group_id'] = $group_id;

					unset($data['email'], $data['date_available'], $data['employee_id']);

					return $data;
				}
			}
		}

		return Msg::error('Invalid filtered data format.');
	}

	public static function timetable()
	{
		return json_encode(GeneratorModel::get_active_timetable());
	}

	public static function check_timeslot($data = array())
	{
		if ($data) {

			unset($data['payload']);

			$check = 	DB::table('booking')
						->where($data)
						->count();

			if (! $check) {
				return true;
			}
		}

		return false; 
	}

	public static function save_booking($data = array())
	{
		if ($data) {

			$data['is_admin_generated'] = 1;

			return DB::table('booking')->insertGetId($data);
		}

		return false; 
	}

	public static function get_booking_summary($booking_id = 0)
	{
		$summary = array();

		if ($booking_id) {

			$booking = 	DB::table('booking as a')
						->join('client as b', 'b.client_id', '=', 'a.client_id')
						->join('timetable as c', 'c.timetable_id', '=', 'a.timetable_id')
						->where('a.booking_id', '=', $booking_id)
						->where('b.is_active', '=', 1)
						->select(
							'a.booking_id',
						    'b.client_id',
						    'c.timetable_id',
						    'c.employee_id',

						    'a.schedule_start',
						    'a.schedule_end',
						    'a.price',
						    'c.date_available',
						    
						    'b.fullname as client',
						    'b.email'
						)->first();

			$summary += typecast($booking, 'array');

			if ($booking) {

				$review = 	ReviewModel::get_review_record($booking->employee_id, true);

				$summary += typecast($review, 'array');
			}
		}

		return $summary ? typecast($summary, 'object') : false;
	}

	public static function list_client_email()
	{
		$rows = 	DB::table('client')
					->where('is_active', 1)
					->whereNotNull('stripe_id')
					->select(
						'client_id',
						'email'
					)->get();

		return array_pluck($rows, 'email');
	}
}
