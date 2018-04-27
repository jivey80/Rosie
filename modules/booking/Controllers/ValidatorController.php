<?php

namespace Modules\Booking\Controllers;

use Input;
use HttpRequest;
use Validator;
use Modules\Booking\Controllers\Controller;
use Modules\Booking\Models\BookingModel;
use Modules\Booking\Models\GeneratorModel;
use Modules\Booking\Models\ValidatorModel;
use Modules\Booking\Models\ReviewModel;
use Aes;
use Msg;
use Template;


class ValidatorController extends Controller
{
	/**
	 * Checks if the email is valid and
	 * is already existing on the database.
	 */
	public function email(HttpRequest $request)
	{

		// Form validator
		$validator = Validator::make(
			Input::all(), 
			array(
				'mail' => 'bail|required|email'
			)
		);

		if ($validator->passes()) {

			$is_valid_email = ValidatorModel::email(Input::get('mail'));

			if ($is_valid_email) {

				return $is_valid_email;
			
			} else {

				return Msg::error('Email is not available.');
			}
        
        } else {

        	return Msg::error('Invalid email.');
        }
	}

	/**
	 * AJAX: Verifies all of the selected booking data
	 * for the last time before saving to the database.
	 *
	 * If the saving is successful, an email notification
	 * for Booking Confirmation will be send on the
	 * client.
	 */
	public function booking_save()
	{

		$validator = Validator::make(
			Input::all(),
			array(
				'book_id' => 'bail|required',

				'name' => 'bail|string',
				'mail' => 'bail|required|email',

				'hours' => 'bail|numeric',
				'supplies' => 'bail|boolean',
				'instruction' => 'bail|string'
			)
		);

		if ($validator->passes()) {

			$client_id = 0;
			$book_id = typecast(Input::get('book_id'), 'string');
			
			// Timetable data
			$id_data = explode('_', $book_id);
			$timetable_id = typecast(substr($id_data[0], 1), 'int');

			// Validate slots
			$interval = self::valid_hour(Input::get('hours'));
			$timetable = GeneratorModel::get_active_timetable();
			$slotlist = GeneratorModel::generate_schedule($timetable, $interval);
			$summary = self::get_slot($book_id);


			$slotdata = array();
			foreach ($slotlist as $slot) {

				if ($slot['group_id'] == $book_id) {

					$slotdata = $slot;
				}
			}


			if ($slotdata and $summary) {

				// Get client_id based from given email
				$email = typecast(Input::get('mail'), 'string');
				$is_valid_email = ValidatorModel::email($email, true);

				if (count($is_valid_email) > 1) {

					$client_id = $is_valid_email['client_id'];

				} else {

					return Msg::error('Email is not available.');
				}


				// Generate encrypted payload
				$payload = Aes::payload(array(
					'c_id' => $client_id,
					's_id' => $book_id,
					'prc' => $slotdata['price']
				));


				// Save booking information
				$save = BookingModel::save_booking(array(

					'timetable_id' => $timetable_id,

					'client_id' => $client_id,


					'need_supplies' => typecast(Input::get('supplies'), 'integer'),

					'instructions' => typecast(Input::get('instruction'), 'string'),

					'schedule_start' => $slotdata['schedule_start'],

					'schedule_end' => $slotdata['schedule_end'],

					'price' => $slotdata['price'],

					'payload' => $payload,


					'tmp_book_id' => typecast(Input::get('book_id'), 'string'),
					'tmp_interval' => self::valid_hour(Input::get('hours'))
				));

				if (is_array($save) and isset($save['booking_id'])) {
					
					// Add booking_id on the payload
					$booking_id = $save['booking_id'];
					$decrypt = json_decode(Aes::decrypt(urldecode($payload)), true);
					$decrypt['b_id'] = $booking_id;
					$new_data = json_encode($decrypt);
					$new_payload = urlencode(Aes::encrypt($new_data));


					$base_url = MODULE_BASE_URL;
					$payload_url = "{$base_url}/confirm?pl={$new_payload}";
					$payload_name = typecast(Input::get('name'), 'string');


					$template = Template::generate('confirmation', array(
						'logo' => ASSET_LOGO,
						'name' => ucwords($payload_name),
						'link' => $payload_url,

						'date' => $summary['date_available'],
						'time' => "{$summary['schedule_start']} to {$summary['schedule_end']}",
						'hourly' => $summary['hours_text'],
						'total' => $summary['price'],

						'avatar' => $summary['avatar'],
						'cleaner' => "{$summary['firstname']} {$summary['lastname']}",
						'rating' => $summary['rate']
					));


					// Send email confirmation
					if (ENVIRONMENT === 'production') {

						$bcc = defined('EMAIL_CONFIRM_BCC') ? EMAIL_CONFIRM_BCC : null;

						emailer($email, 'Rosie Services Confirm Booking', $template, $bcc);
					
					} else {

						log_write('email_conf', $template);

						return self::success($payload_url);
					}

					return self::success("Thank your for booking. An email confirmation has been sent to <b>{$email}</b>. The slot confirmation will be <b>valid within 24 hours</b>.");
				
				} else if ($save === false) {

					return Msg::error('Booking was not saved. Please review your booking information.');

				} else {

					return Msg::error('This slot has already been booked. Please try other time slot.');
				}

			} else {

				return Msg::error('Invalid booking request. Slot is not available.');

			}

		} else {

			return Msg::error('Invalid booking request. Please verify the booking information before saving.');
		}
	}

	/**
	 * AJAX: Saves review page
	 */
	public function review_save()
	{
		$validator = Validator::make(
			Input::all(),
			array(
				'str' => 'bail|required|numeric',
				'rvw' => 'string',
				'bid' => 'required|numeric',
				'eid' => 'required|numeric',
				'cid' => 'required|numeric'
			)
		);

		if ($validator->passes()) {

			// Save to database
			ReviewModel::save_review(array(
				'booking_id' => typecast(Input::get('bid'), 'int'),
				'client_id' => typecast(Input::get('cid'), 'int'),
				'employee_id' => typecast(Input::get('eid'), 'int'),
				'comment' => typecast(Input::get('rvw'), 'string'),
				'rating' => typecast(Input::get('str'), 'int')
			));

			return self::success('<h3>Thank you very much for your feedback.<h3>');

		} else {

			log_write('review_fail', array(
				'inputs' 	=> Input::all(), 
				'validator' => $validator->failed(),
			));

			return Msg::error('Invalid review information. Please reload the review page and check your review.', 200, false);
		}
	}
}
