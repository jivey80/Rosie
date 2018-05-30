<?php

namespace Modules\Admin\Controllers;


use App\Http\Models\AuthModel;
use Modules\Admin\Models\AdminModel;
use Modules\Admin\Models\GraphModel;
use Modules\Admin\Models\ExpressBookModel;
use Validator;
use Session;
use Input;
use Msg;
use Aes;
use Template;

class AdminController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function console_page()
	{
		$data = array();

		// For SMS listing
		$sms = AdminModel::active_booking();

		if ($sms) {
			$data['sms_all'] = $sms['active_booking'];
			$data['sms_count'] = ($sms['active_count'] > 0) ? $sms['active_count'] : '';
		}

		return view(MODULE . '::pages.console', $data);
	}

	/**
	 * Components
	 *
	 * Days to be displayed
	 * Price rate per hour
	 * Price increment per time
	 * Default padding per time slot
	 * Google Matrix API Key
	 */
	public function dashboard()
	{
		// Top Cleaners and review preview
		// Bookings per day Graph
		return view(MODULE . '::components.dashboard', array(
			'booking_stats' 	=> GraphModel::dashboard_booking_stats(),

			'top_cleaner' 		=> json_encode(AdminModel::top_cleaners()),
			'top_cleaner_title' => date('M Y') . ' Top Cleaners',

			'booking_summary' 	=> json_encode(AdminModel::booking_summary())
		));
	}

	public function list_timetables()
	{
		return AdminModel::list_timetables();
	}

	public function set_appointment()
	{
		// return AdminModel::set_appointment();
		return view(MODULE . '::components.set_appointment', array(
			'available_dates' => ExpressBookModel::timetable()
		));
	}

	public function administrators()
	{
		return AdminModel::administrator();
	}

	public function employees()
	{
		return AdminModel::employee();
	}

	public function clients()
	{
		return AdminModel::client();
	}
	
	public function settings()
	{
		return AdminModel::settings();
	}


	public function booking()
	{
		$data = Input::all();


		// Filter inputs if possible
		$filter = ExpressBookModel::filter_data($data);
		

		// Check if the selected date
		// matches up the availability of the cleaner
		$record = ExpressBookModel::validate_timeslot($filter);

		$group_id = isset($record['tmp_group_id']) ? $record['tmp_group_id'] : false;
		unset($record['tmp_group_id']);


		if (isset($record['client_id']) and $group_id) {

			// Check if the selected period of time
			// doesn't hit any available booked slots.
			$check_timeslot = ExpressBookModel::check_timeslot($record);

			if ($check_timeslot) {
				
				$booking_id = ExpressBookModel::save_booking($record);


				if ($booking_id) {

					$send_confirmation = self::booking_confirmation($record, $group_id, $booking_id);

					if ($send_confirmation !== true) {

						return $send_confirmation;
					
					} else {

						return Msg::success("An email containing booking confirmation was sent to <b>{$data['email']}</b>.");
					}

				} else {

					return Msg::error('Cannot save the process.');
				}

			} else {

				return Msg::error('Someone has already booked same time slot.');
			}
		}

		return $record;
	}

	private static function booking_confirmation($data = array(), $group_id = '', $booking_id = 0)
	{
		$payload = $data['payload'];

		// Add booking_id on the payload
		$decrypt = json_decode(Aes::decrypt(urldecode($payload)), true);
		$decrypt['b_id'] = $booking_id;
		$new_data = json_encode($decrypt);
		$new_payload = urlencode(Aes::encrypt($new_data));

		$base_url = str_replace('/admin', '/booking', MODULE_BASE_URL);
		$payload_url = "{$base_url}/confirm?pl={$new_payload}";
		$payload_name = 'get_client_first_name';


		$summary = ExpressBookModel::get_booking_summary($booking_id);

		if ($summary and $group_id) {

			$date_available = date(BOOKING_TIMESTAMP_FULL, strtotime($summary->date_available));
			$schedule_start = date(TIME_NOSEC_FULL_FORMAT, strtotime($summary->schedule_start));
			$schedule_end = date(TIME_NOSEC_FULL_FORMAT, strtotime($summary->schedule_end));

			$group_data = explode('_', $group_id);
			$hours = $group_data[2];
			$price = $summary->price;

			$hours_text = $hours > 1 ? "{$hours} hours" : "{$hours} hour";
			$hours_price = $price / $hours;
			$hours_price_clean = strip_decimal($hours_price, 2);

			$assets_url = ASSET_AVATAR_URL;
			$email = $summary->email;


			// Send email confirmation
			$template = Template::generate('confirmation', array(
				'logo' => ASSET_LOGO,
				'name' => ucwords($summary->client),
				'link' => $payload_url,

				'date' => $date_available,
				'time' => "{$schedule_start} to {$schedule_end}",
				'hourly' => $hours_text . ' at $' . $hours_price_clean . '/hour',
				'total' => $price,

				'avatar' => "{$assets_url}/{$summary->avatar}",
				'cleaner' => ucwords($summary->employee),
				'rating' => typecast($summary->rating, 'float')
			));

			if (ENVIRONMENT === 'production') {

				$bcc = defined('EMAIL_CONFIRM_BCC') ? EMAIL_CONFIRM_BCC : null;

				emailer($email, 'Rosie Services Confirm Booking', $template, $bcc);
			
			} else {

				log_write('email_conf', $template);
			}

			return true;
		}

		return Msg::error('Cannot find booking information.');
	}

	public function change_password()
	{
		$validator = Validator::make(Input::all(), array(
			'old_password' => 'bail|required|min:8',
			'new_password' => 'bail|required|min:8',
			'confirm_pass' => 'bail|required|min:8'
		));

		if ($validator->passes()) {

			$new_password = typecast(Input::get('new_password'), 'string');
			$old_password = typecast(Input::get('old_password'), 'string');
			$confirm_pass = typecast(Input::get('confirm_pass'), 'string');

			if ($old_password !== $new_password) {

				if ($new_password === $confirm_pass) {

					$account_info = AuthModel::get_info();

					if ($account_info) {

						if (AuthModel::check_password($old_password)) {

							$generate_new_hash = simple_auth($new_password);

							$update_hash = AuthModel::update_password($account_info['employee_id'], array(
								'password' 	=> $generate_new_hash['password'],
								'salt'		=> $generate_new_hash['salt']
							));

							if ($update_hash) {

								return Msg::success('Password has already been reset.');
							}

							return Msg::error('Failed to reset password. Please try again later.');
						}

						return Msg::error('Old password is invalid.');
					}

					return Msg::error('Account was not recognized. Please contact the main administrator.');
				}

				return Msg::error('Passwords didn\'t matched');
			}
			
			return Msg::error('Please get a new password.');
		}

		return Msg::error($validator->errors()->first());
	}

	public function list_client_email()
	{
		return ExpressBookModel::list_client_email();
	}
}
