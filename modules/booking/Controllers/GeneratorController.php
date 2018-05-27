<?php

namespace Modules\Booking\Controllers;

use Input;
use Modules\Booking\Models\GeneratorModel;
use Modules\Booking\Models\BookingModel;
use Modules\Booking\Models\ReviewModel;
use Msg;


class GeneratorController extends Controller
{

	/**
	 * AJAX: List of available slots for the selected
	 * cleaner.
	 */
	public function list_schedule()
	{
		if (Input::has(['id', 'hours'])) {

			$response = array();

			$id = typecast(Input::get('id'), 'string');
			$interval = self::valid_hour(Input::get('hours'));
			$timetable_id = typecast(str_replace('schedule_', '', $id), 'int');

			if ($timetable_id > 0) {

				// Get the list of active timetables
				$timetable = GeneratorModel::get_active_timetable();

				// Generate list of schedule based
				// from the selected interval (hours)
				$slotlist = GeneratorModel::generate_schedule($timetable, $interval);


				// Remove slots that has already passed the 
				// current time.
				$slots = timetable_autocorrect(GeneratorModel::list_schedule($timetable_id, $slotlist));
				// io(GeneratorModel::list_schedule($timetable_id, $slotlist));


				// Get the duration of the timetable as base
				// of validation for correction.
				$duration = self::timetable_duration($timetable_id, $timetable);

				// Checks for backward time adjustment padding
				$response = self::filter_schedule_list($slots, $duration);
			}

			return $response;
		}
	}

	private static function timetable_duration($timetable_id = 0, $timetable = array())
	{
		if ($timetable_id and $timetable) {

			foreach ($timetable as $schedule) {

				if ($schedule->timetable_id == $timetable_id) {

					return $schedule;
				}
			}
		}

		return false;
	}


	private static function filter_schedule_list($slots = array(), $timetable = array())
	{
		$response = array();

		if ($slots and $timetable) {

			$base_start = strtotime($timetable->time_in);
			$base_end = strtotime($timetable->time_out);


			// First Gear
			// Get the very first booked slot and check
			// the preceding slots.
			$filtered = array();
			$filter_set = array();
			foreach ($slots as $slot_i => $slot) {

				$filter_set[$slot_i] = $slot;
				
				if ($slot->is_booked) {

					$filtered[] = $filter_set;

					$filter_set = array();
				}
			}


			// Second Gear
			// **For now**, just get and fix the first
			// instance of filtered set.
			if (isset($filtered[0]) and $filtered[0]) {

				$instances = $filtered[0];
				$instances_keys = array_keys($instances);
				$instances_min = $instances_keys[0];
				$instances_max = $instances_keys[count($instances) - 1];

				// Do the fixing from the last part
				// of the instances (booked slot)
				// then fix the preceding slots.
				$travel_time = 0;

				for ($ctr = $instances_max; $ctr >= $instances_min; $ctr--) {

					$now =  isset($instances[$ctr]) ? $instances[$ctr] : array();

					$prv_ctr = $ctr - 1;
					$prv =  isset($instances[$prv_ctr]) ? $instances[$prv_ctr] : array();


					if ($now and $prv) {

						// Current base slot info
						$now_start = strtotime($now->start);
						$now_end = strtotime($now->end);

						// Preceding slot info
						$prv_start = strtotime($prv->start);
						$prv_end = strtotime($prv->end);

						// Get the duration of slot
						$prv_off = ($prv_end - $prv_start);


						// Store the travel time as the base padding
						// of the preceding slots
						$travel_time = $now->travel;


						// Correction to slots with no padding.
						if ($prv_end === $now_start and $now->is_booked) {

							$prv_end -= $travel_time;
							$prv->end = date(TIME_NOSEC_FULL_FORMAT, $prv_end);
							
							$prv_start -= $travel_time;
							$prv->start = date(TIME_NOSEC_FULL_FORMAT, $prv_start);

							$prv->travel = $travel_time;
						}

						// Correction to slots with overlapping time.
						if ($prv_end > $now_start) {

							$prv_end = $now_start;
							$prv->end = date(TIME_NOSEC_FULL_FORMAT, $prv_end);


							$tmp_prv_start = $prv_end - $prv_off;

							if ($tmp_prv_start >= $base_start) {
								
								$prv_start = $tmp_prv_start;

							} else {

								$prv_start = $base_start;
								$prv->is_booked = true;
							}

							$prv->start = date(TIME_NOSEC_FULL_FORMAT, $prv_start);


							// There will be an instance where the start and end
							// time of the slot will be same due to the auto-correction.
							// 
							// Or the new total duration does not match the intended 
							// amount of duration as defined by the client.
							// 
							// Remove the slot from the list.
							$new_prv_off = ($prv_end - $prv_start);

							if ($prv_start === $prv_end or $new_prv_off < $prv_off) {
								$prv->for_removal = true;
							}
						}
					}
				}
			}


			// Third gear
			// Select slots that is not marked for removal.
			// Remove slots that exceeds on the timetable.
			foreach ($slots as $i => $slot) {

				if (isset($slot->travel)) {
					unset($slot->travel);
				}

				$slot_i = strtotime($slot->start);
				$slot_o = strtotime($slot->end);
				
				if (! isset($slot->for_removal) and ($slot_i >= $base_start)) {
					$response[] = $slot;
				}
			}

		}	

		return $response;
	}


	/**
	 * AJAX: Gives the breakdown of information
	 * for the selected slot.
	 */
	public function booking_data()
	{

		$result = array();

		if (Input::has(['book_id'])) {

			$book_id = typecast(Input::get('book_id'), 'string');

			$book_data = self::get_slot($book_id);

			if ($book_data) {

				return $book_data;
			
			} else {

				return Msg::error('The slot has already been booked. Please select another one.');
			}

		} else {

			return Msg::error('Invalid request. Please reload the page and try again.');
		}
	}
}
