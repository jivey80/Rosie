<?php

namespace Modules\Booking\Controllers;

use Input;
use Modules\Booking\Models\BookingModel;
use Modules\Booking\Models\GeneratorModel;
use Modules\Booking\Models\ReviewModel;
use Aes;
use Msg;


class BookingController extends Controller
{

	/**
	 * Initial page loader
	 */
	public function index()
	{
		return view(MODULE . '::booking.index');
	}

	/**
	 * AJAX: Fill-up details step
	 */
	public function details_page()
	{
		return view(MODULE . '::booking.details', array(
			'hours' => self::$valid_hours
		));
	}

	/**
	 * AJAX: Schedule selection step
	 */
	public function schedule_page()
	{
		$interval = self::valid_hour(Input::get('hours'));


		// Generate list of schedule based
		// from the selected interval (hours)
		$timetable = GeneratorModel::get_active_timetable();
		$slotlist = GeneratorModel::generate_schedule($timetable, $interval);
		$schedule = BookingModel::get_schedule($timetable, $slotlist);

		return view(MODULE . '::booking.schedule', array(
			'schedules' => $schedule
		));
	}

	/**
	 * AJAX: Selected booking confirmation step
	 */
	public function confirmation_page()
	{	
		return view(MODULE . '::booking.confirmation');
	}

	/**
	 * AJAX: `Proceed Booking` Button Callback
	 */
	public function confirmation()
	{

		if (Input::has('pl')) {

			$payload = typecast(Input::get('pl'), 'string');
			$payload_decrypt = Aes::decrypt(htmlspecialchars($payload));
			$payload_data = json_decode($payload_decrypt);


			if (isset($payload_data->ts)) {

				$ts_now = strtotime(date(TIMESTAMP_FORMAT));
				$ts_payload = $payload_data->ts;
				$ts_offset = $ts_now - $ts_payload;


				$expiry = 60 * 60 * 24;
				
				if ($ts_offset <= $expiry) {

					$booking_id = $payload_data->b_id;
					$client_id = $payload_data->c_id;
					$schedule_id = $payload_data->s_id;


					// Save booking status
					// $save = BookingModel::confirm_booking($booking_id, $client_id, $schedule_id);
					$save = true;

					if ($save) {

						// Success message
						$message = Msg::success('<b>Congratulations!</b> The booking for this slot has been successfully verified.', array(
							'link' => MODULE_BASE_URL
						));
						
						// Fetch the booking data for display
						$slot = self::slot_summary($payload_data);
						$response = array_merge($message, $slot);

						return view(MODULE . '::booking.verified', $response);

					} else {

						return view(MODULE . '::booking.verified', array(
							'status' => 'error',
							'message' => 'The slot has already been confirmed for booking.',
							'link' => MODULE_BASE_URL
						));
					}
				
				} else {

					return view(MODULE . '::booking.verified', array(
						'status' => 'error',
						'message' => 'Sorry. The URL has already been expired.',
						'link' => MODULE_BASE_URL
					));
				}
				
			}
		}

		return view(MODULE . '::booking.verified', array(
			'status' => 'error',
			'message' => 'Sorry, this is an invalid URL. <br /> Please contact our customer support for further assistance.',
			'link' => MODULE_BASE_URL
		));
	}


	/**
	 * Review page
	 */
	public function review_page()
	{
		if (Input::has('pl')) {

			$pl = typecast(Input::get('pl'));
			$payload = Aes::decrypt($pl);
			$payload_data = json_decode($payload);


			// Check if the payload has already been
			// reviewed.
			$booking_id = $payload_data->bid;
			$client_id = $payload_data->cid;
			$employee_id = $payload_data->eid;
			$check = ReviewModel::is_reviewed($booking_id, $client_id);

			if ($check) {

				return view(MODULE . '::booking.review', array(
					'status' => 'error',
					'message' => 'You already visited this link for review.',
					'link' => MODULE_BASE_URL
				));

			} else {

				$success_data = Msg::success('How satisfied were you with the service we provided today?', array(
					'link' 			=> MODULE_BASE_URL,
					'stars' 		=> $payload_data->str,

					'booking_id' 	=> $booking_id,
					'client_id' 	=> $client_id,
					'employee_id' 	=> $employee_id
				));

				return view(MODULE . '::booking.review', $success_data);
			}

		}

		return view(MODULE . '::booking.review', array(
			'status' => 'error',
			'message' => 'Sorry, this is an invalid URL. <br /> Please contact our customer support for further assistance.',
			'link' => MODULE_BASE_URL
		));
	}
}
