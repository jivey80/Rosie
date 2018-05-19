<?php

namespace Modules\Booking\Models;

use Input;
use DB;
use Modules\Booking\Models\ValidatorModel;
use App\Libraries\Calc;
use App\Libraries\Api;


class GeneratorModel
{
	/*!
	 * @param  objarr  $timetable GeneratorModel::get_active_timetable()
	 * @param  integer $interval  Slot generator time interval in hours
	 * @param  string  $_email
	 * @return array
	 */
	public static function generate_schedule($timetable = array(), $interval = 0, $_email = false)
	{

		$result = array();


		if (Input::has('mail')) {

			$client = ValidatorModel::email(Input::get('mail'));

		} else if ($_email) {

			$client = ValidatorModel::email($_email);
		
		} else {

			return $result;
		}


		if ($timetable and $client) {

			$pk = 0;

			foreach ($timetable as $i => $record) {

				$pk++;

				$slots = self::generate_availability($pk, $record, $interval, $client);

				$result = array_merge($result, $slots);
			}
		}

		return self::price_recalculate($result);
	}

	/**
	 * Basic price computation goes here.
	 * This function just compute for the base price
	 * or base + mark up price if there are any
	 * booked slots.
	 * 
	 * @param  array  $slots
	 */
	private static function price_recalculate($slots = array())
	{

		if ($slots) {

			$booked_per_timetable = array();

			foreach($slots as $slot) {

				$timetable_id = $slot['timetable_id'];

				if ($slot['is_booked'] == true) {
					
					// Total booked hours per
					// timetable
					if (isset($booked_per_timetable[$timetable_id])) {

						$booked_per_timetable[$timetable_id] += 1; 

					} else {

						$booked_per_timetable[$timetable_id] = 1;
					}
				}
			}


			$below_quota = false;
			if ($booked_per_timetable) {
				$below_quota = (min($booked_per_timetable) < QUOTA_HOUR_LIMIT) ? true : false;
			}


			$booked_timetable_id = array_keys($booked_per_timetable);
			foreach ($slots as $i => $slot) {

				$hours = typecast($slot['hours'], 'float');
				$tid = $slot['timetable_id'];


				if (in_array($tid, $booked_timetable_id) or (!in_array($tid, $booked_timetable_id) and $below_quota)) {

					// Default all of the slots to 
					// Regular Rate + Mark Up Rate
					// except from the already booked slot
					if (! $slot['is_booked']) {
						$slots[$i]['price'] = (PRICE_RATE_PER_HOUR + PRICE_RATE_INCREMENT) * $hours;
					}

				} else {

					// If all days doesn't have any booking
					// set all slots to the default price
					foreach ($slots as $i => $slot) {
						$slots[$i]['price'] = (PRICE_RATE_PER_HOUR * $hours);
					}
				}
			}


			// Per timetable adjacent price adjustment
			if ($booked_per_timetable) {

				$affected_slots = array();

				foreach ($booked_per_timetable as $booked_timetable_id => $booked_timetable_count) {

					foreach ($slots as $i => $slot) {

						$slot_timetable_id = $slot['timetable_id'];


						if ($slot_timetable_id == $booked_timetable_id) {

							$affected_slots[$booked_timetable_id][] = $slot;
						}
					}
				}

				$slots = Calc::adjacent($slots, $affected_slots);

			}
		}

		return $slots;
	}

	/**
	 * Generates the AJAX response for
	 * creating the list of cleaner
	 * schedule per day on `Choose Day Step`
	 * 
	 * @param  integer $timetable_id 
	 * @param  array   $slots        
	 * @param  string  $detail_id
	 */
	public static function list_schedule($timetable_id = 0, $slots = array(), $detail_id = '')
	{

		$result = array();

		if ($timetable_id and $slots) {

			$schedule = array();

			foreach ($slots as $slot) {

				if ($slot['timetable_id'] == $timetable_id) {

					$slot_data = array(
						'timetable_id' 	=> $slot['timetable_id'],
						'detail_id' 	=> $slot['group_id'],
						'start' 		=> date(TIME_NOSEC_FULL_FORMAT, strtotime($slot['schedule_start'])),
						'end' 			=> date(TIME_NOSEC_FULL_FORMAT, strtotime($slot['schedule_end'])),
						'price' 		=> $slot['price'],
						'hours' 		=> $slot['hours'],

						'is_booked' 	=> $slot['is_booked'],
						'is_today' 		=> $slot['is_today'],

						'travel'		=> $slot['travel']
					);

					$schedule[] = typecast($slot_data, 'object');
				}
			}

			$result = Calc::get_cheapest_price($schedule);
		}


		if ($result and $detail_id and is_string($detail_id)) {

			foreach ($result as $slotmatch) {

				if ($slotmatch->detail_id == $detail_id) {

					return $slotmatch;
				}
			}
		}

		return $result;
	}

	public static function get_active_timetable($get_ids_only = false)
	{	
		// Get all the active timetables
		// within the 7 days bracket from now
		$range = get_display_range(SCHEDULE_RANGE);

		$sql = 	DB::table('timetable as a')
				->join('employee as b', 'b.employee_id', '=', 'a.employee_id')
				->whereBetween('a.date_available', $range)
				->where('b.is_active', '=', 1)
				->orderBy('a.date_available', 'ASC')
				->orderBy('a.timetable_id', 'ASC');

		if ($get_ids_only) {
			
			return 	$sql->select(DB::raw('GROUP_CONCAT(a.timetable_id) as ids'))->first();

		} else {

			$records = 	$sql->select(
							'a.timetable_id',
							'b.employee_id',
							'b.firstname',
							'b.lastname',
							'b.avatar',
							'a.date_available',
							'a.time_in',
							'a.time_out',

							DB::raw("(CASE WHEN a.date_available = DATE(NOW()) THEN 1 ELSE 0 END) as is_today")
						)->get();

			$filter_time 	= self::filter_timeinout($records);

			$filter_avatar 	= self::set_avatar_url($filter_time);

			return $filter_avatar;
		}
	}

	/**
	 * Checks if the time of the timetable has already been passed.
	 * 
	 * @param  array  $records Timetable
	 * @return array  Filtered timetable records
	 */
	private static function filter_timeinout($records = array())
	{
		if ($records) {

			foreach ($records as $i => $record) {
				
				if ($record->is_today) {

					$time_out = strtotime($record->time_out);
					$time_now = strtotime(date('H:i:s'));
					
					if ($time_now > $time_out) {
						unset($records[$i]);
					}
				}
			}
		}

		return $records;
	}

	private static function set_avatar_url($records = array())
	{
		if ($records) {

			foreach ($records as $record) {

				if (isset($record->avatar)) {

					$avatar = $record->avatar ? $record->avatar : 'default.png';

					$record->avatar = ASSET_AVATAR_URL . "/$avatar";
				}
			}
		}

		return $records;
	}

	public static function get_employee_timetable($timetable_id = 0)
	{

		$result = array();

		if ($timetable_id) {

			$result = 	DB::table('timetable as a')
						->join('employee as b', 'a.employee_id', '=', 'b.employee_id')
						->where('a.timetable_id', '=', $timetable_id)
						->where('b.is_active', '=', 1)
						->first();
		}

		return $result;
	}

	/**
	 * Generates a list of schedule on-the-fly
	 * with the given start and end time
	 * together with the interval.
	 *
	 * This also checks for any schedule conflicts
	 * with the booked data on the database.
	 *
	 * Auto-generation happens first, before checking
	 * on the database.
	 * 
	 * @param  integer 	$id 
	 * @param  array 	$timetable
	 * @param  integer 	$interval
	 * @param  array 	$client
	 */
	private static function generate_availability($id = 0, $timetable = array(), $interval = 0, $client = array())
	{

		$result = array();

		if ($timetable and $interval and $client) {

			$timetable_id = $timetable->timetable_id;
			$timetable_start = strtotime($timetable->time_in);
			$timetable_end = strtotime($timetable->time_out);


			// Get all the related booked slots
			// as basis for the schedule auto-generator.
			$slots = self::get_booked_slots($timetable_id);
			$booked = array();
			foreach ($slots as $i => $slot) {
				$booked[$i] = strtotime($slot->schedule_start);
			}


			$_interval = typecast($interval, 'float');
			$hour = 60 * (60 * $_interval);
			$_hours = $timetable_end - $timetable_start;


			// There will be no initial padding
			// for timetables that hasn't received any
			// booking.
			$padding = 0;


			// If, there's already a booked slot. Compute
			// the padding time by the distance from the 
			// booked adjacent slot (origin) to the 
			// current client's location (destination).
			$origin = array();
			$destination = array(
				'latitude' => $client['latitude'],
				'longitude' => $client['longitude']
			);


			$time_start = $timetable_start;
			$time_end = 0;
			$pk = 0;

			while ($_hours > 0) {

				$slot_info = array();

				$is_booked = false;


				// group_id/detail_id
				// 
				// t = timetable
				// o = index in slot generated array
				$group_id = "t{$timetable_id}_o{$pk}_{$interval}";


				// Intended only to check autogenerated
				// time that is exactly same on what has 
				// already been saved on the database.
				if (in_array($time_start, $booked)) {

					$_k = array_search($time_start, $booked);
					$_s = $slots[$_k];


					// Cleaner origin
					$origin = array(
						'latitude' => $_s->latitude,
						'longitude' => $_s->longitude
					);


					$time_start = strtotime($_s->schedule_start);
					$time_end = strtotime($_s->schedule_end);

					$is_booked = true;

					unset($booked[$_k]);
					unset($slots[$_k]);

					$_hours -= $hour;
				
				} else {

					$time_end = $time_start + $hour;

					$slot_info = schedule_autocorrect($time_start, $time_end, $_interval, $slots);
					$_booked_flag = $slot_info['is_booked'];

					if ($_booked_flag) {

						$time_start = $slot_info['time_start'];
						$time_end = $slot_info['time_end'];
						$is_booked = $_booked_flag;


						$_ky = array_keys($slots);
						$_cn = count($_ky) - 1;
						$_k = $_ky[$_cn];
						$_s = $slots[$_k];

						// Cleaner origin
						$origin = array(
							'latitude' => $_s->latitude,
							'longitude' => $_s->longitude
						);

						$hour = $slot_info['hour_range'];

					} else {

						$time_start = $slot_info['time_start'];
						$time_end = $slot_info['time_end'];


						// Cleaner origin
						$origin = array();


						$hour = $slot_info['hour_range'];
					}

					$_hours -= $hour;
				}

				// Prevent the auto-generator
				// to exceed to the cleaner's
				// time out time.
				if (($timetable_end - $time_end) < 0) {
					break;
				}

				// Check for any correction basis if any.
				// The value indicated for this is the past/nearest
				// booked slot end time. 
				if (isset($slot_info['correction_time_end']) and $slot_info['correction_time_end'] > 0) {
					$time_end = $slot_info['correction_time_end'];
				}


				// Assign padding based on the travel
				// duration as specified between slots.
				if ($origin and $destination) {
					
					// Round the time to nearest multiple of 5 minutes.
					$api_padding = self::get_travel_distance($origin, $destination);
					$min_padding = ceil(($api_padding / 60) / 5);
					$new_padding = $min_padding * 5;
					$padding = $new_padding * 60;

					if ($padding == 0) {
						$padding = SCHEDULE_PADDING;
					}

				} else {

					$padding = 0;
				}


				$result[] = array(
					'index' 			=> $pk,
					'timetable_id' 		=> $timetable_id,
					'group_id' 			=> $group_id,
					
					'schedule_start' 	=> date(TIME_FORMAT, $time_start),
					'schedule_end' 		=> date(TIME_FORMAT, $time_end),
					
					'price'				=> (PRICE_RATE_PER_HOUR * $_interval),
					'hours' 			=> $_interval,
					'avatar' 			=> $timetable->avatar,

					'is_booked' 		=> $is_booked,
					'is_today' 			=> $timetable->is_today,

					'travel'			=> $padding
				);


				$time_end += $padding;
				$time_start = $time_end;
				$_hours -= $padding;


				$pk++;
			}

			return $result;
		}
	}

	private static function get_travel_distance($origin = array(), $destination = array())
	{
		$api_response = array();

		if ($origin and $destination) {

			// Generate request checksum
			$checksum_origin = md5(json_encode($origin));
			$checksum_destination = md5(json_encode($destination));


			// There's no need to compute distance if
			// the origin and destination is of same
			// place.
			if ($checksum_origin === $checksum_destination) {
				return 0;
			}


			// Check if the request has been
			// already recorded.
			$checksum = 	DB::table('distance_map')
							->where('origin', '=', $checksum_origin)
							->where('destination', '=', $checksum_destination)
							->select(
								'*',
								DB::raw("NOW() as date_now")
							)->first();


			// Reuse the recorded distance info
			// from the database if still valid within
			// the timeframe of MAP_CACHE_LIFETIME. 
			if ($checksum) {

				$time_old = strtotime($checksum->date_requested);
				$time_new = strtotime($checksum->date_now);
				
				// Convert time to minutes
				$time_offset = ($time_new - $time_old) / 60;

				if ($time_offset >= MAP_CACHE_LIFETIME) {

					$api_response = Api::get_distance($origin, $destination);

					DB::table('distance_map')
					->where('map_id', '=', $checksum->map_id)
					->update(array(
						'result' => $api_response
					));

				} else {

					$api_response = $checksum->result;
				}

			} else {

				$api_response = Api::get_distance($origin, $destination);

				// Insert the request record
				// so it can help on the next
				// same request if any.
				DB::table('distance_map')->insert(array(
					'origin' 		=> $checksum_origin,
					'destination' 	=> $checksum_destination,
					'result' 		=> $api_response
				));
			}
		}

		return $api_response;
	}

	private static function get_booked_slots($timetable_id = 0)
	{
		$result = array();

		if ($timetable_id) {

			$result = 	DB::table('booking as a')
						->join('client as b', 'b.client_id', '=', 'a.client_id')
						->where('a.timetable_id', '=', $timetable_id)
						->where('b.is_active', '=', 1)
						->orderBy('a.schedule_start')
						->select(
							'a.booking_id',
							'a.timetable_id',
							'a.schedule_start',
							'a.schedule_end',
							'b.latitude',
							'b.longitude'
						)->get();
		}

		return $result;
	}

	public static function get_slot_summary($booking_id)
	{	
		$result = array();

		if ($booking_id) {
			
			return 	DB::table('booking AS a')
					->join('timetable AS b', 'b.timetable_id', '=', 'a.timetable_id')
					->where('a.booking_id', '=', $booking_id)
					->select(
						'a.booking_id',
						'a.client_id',
						'b.timetable_id',
						'b.employee_id',
						
						'a.schedule_start',
						'a.schedule_end',
						'a.price',
						'b.date_available'
					)->first();
		}

		return $result;
	}
}
