<?php

namespace Modules\Admin\Controllers;

use HttpRequest;
use Validator;
use Input;
use Modules\Admin\Models\CrudModel;
use Aes;
use Msg;
use Template;


class CrudController extends Controller
{
	public function gateway(HttpRequest $request)
	{
		$path = $request->path();
		$path_info = explode('/' ,$path);


		$module_name = $path_info[1];
		$module_action = isset($path_info[2]) ? $path_info[2] : false;
		$module_data = Input::all();

		if (! $module_data) {

			return Msg::error('Request does not contain any required information.');
		}


		// Re-align customize columns to
		// the correct database counterpart.
		// 
		// The datatable library has been
		// modified to handle custom form
		// select data therefore modifying
		// the expected datatype value
		// of the affected column.

		// Lastly..Perform data validation
		// before going to database transactions.
		$validator = null;

		// Data needed for post validation process
		$post_process_data = null;

		switch ($module_name) {
			case 'timetables':
				$module_data = self::__filter_timetables($module_data);
				break;

			case 'administrators':
				// Add default type
				$module_data['employee_type_id'] = 1;

				// Account default password
				$post_process_data 			= simple_auth();
				$module_data['password'] 	= $post_process_data['password'];
				$module_data['salt'] 		= $post_process_data['salt'];

				$validator = Validator::make($module_data, array(
					'firstname' => 'bail|required|regex:/^[\pL\s\-]+$/u',
					'lastname' 	=> 'bail|required|regex:/^[\pL\s\-]+$/u',
					'username' 	=> 'bail|required|min:8|alphanum|unique:employee,username',
					'email' 	=> 'bail|required|email|unique:employee,email'
				));
				break;

			case 'employees':
				// Add default type
				$module_data['employee_type_id'] = 2;

				if ($request->hasFile('avatar')) {

					$avatar_file = $request->file('avatar');


					// Manually validate mime type
					// of uploaded avatar since I
					// cannot make the laravel validation
					// for image work.
					$avatar_ext = $avatar_file->extension();
					$avatar_hash = $avatar_file->hashName();
					
					if (in_array($avatar_ext, ['jpg', 'jpeg', 'png'])) {
						
						$post_process_data = array(
							'file' => $avatar_file,
							'hash' => $avatar_hash
						);

					} else {

						return Msg::error('Avatar must be a valid image file of filetype jpeg, jpg and png.');
					}

					// Rename the file
					$module_data['avatar'] = $avatar_hash;
				
				}


				$validator = Validator::make($module_data, array(
					'firstname' => 'bail|required|regex:/^[\pL\s\-]+$/u',
					'lastname' 	=> 'bail|required|regex:/^[\pL\s\-]+$/u'
				));
				break;

			case 'clients':
				$validator = Validator::make($module_data, array(
					'fullname' 	=> 'bail|required|regex:/^[\pL\s\-]+$/u',
					'username' 	=> 'bail|required|alphanum',
					'email' 	=> 'bail|required|email',
					'address' 	=> 'bail|required',
					'latitude' 	=> 'bail|required|numeric',
					'longitude' => 'bail|required|numeric',
					'contact_number' => 'bail|required|regex:/^[0-9-]+$/'
				));
				break;

			case 'settings':
				$validation_filter = array(
					// 'APIKEY_GMATRIX' 		=> 'bail',
					'BOOKING_REMINDER_TIME' => 'bail|numeric',
					'EMAIL_CONFIRM_BCC' 	=> 'bail|email',
					'EMAIL_RATING_BCC' 		=> 'bail|email',
					'EMAIL_REMINDER_BCC' 	=> 'bail|email',
					'PRICE_RATE_INCREMENT' 	=> 'bail|numeric',
					'PRICE_RATE_PER_HOUR' 	=> 'bail|numeric',
					'SCHEDULE_PADDING' 		=> 'bail|numeric',
					'SCHEDULE_PADDING_2KM' 	=> 'bail|numeric',
					'SCHEDULE_RANGE' 		=> 'bail|numeric',
					'TRAVEL_FEE' 			=> 'bail|numeric',
					'TRAVEL_FEE_TIME' 		=> 'bail|numeric',
				);

				if (isset($module_data['id'])) {

					$get_config = CrudModel::get_config($module_data['id']);
				
					if ($get_config and isset($validation_filter[$get_config->name])) {
						
						$validator = Validator::make($module_data, array(
							'value' => $validation_filter[$get_config->name]
						));
					}
				}
				break;
			
			default: break;
		}


		// Validate form data
		if ($validator and $module_action !== 'delete') {

			if (! $validator->passes()) {

				$error = $validator->errors()->first();

				return Msg::error($error);
			}
		}


		if ($module_action and in_array($module_action, ['add', 'edit', 'delete'])) {

			$process = CrudModel::gateway($module_name, $module_data, $module_action);

			$avatar = false;

			if ($process) {

				if ($post_process_data) {

					switch ($module_name) {

						case 'administrators':
							$template = Template::generate('new_account', array(
								'logo' 		=> ASSET_LOGO,
								'username' 	=> $module_data['username'],
								'password' 	=> $post_process_data['plain_password']
							));

							if (ENVIRONMENT === 'production') {

								$bcc = defined('EMAIL_CONFIRM_BCC') ? EMAIL_CONFIRM_BCC : null;

								emailer($module_data['email'], 'Rosie Services New Account', $template, $bcc);
							
							} else {

								log_write('email_conf', $template);
							}
							break;

						case 'employees':
							$a_file = $post_process_data['file'];
							$a_hash = $post_process_data['hash'];

							$avatar = $a_hash;

							// Save avatar to assets directory
							$a_file->move(ASSET_AVATAR_PATH, $a_hash);
							break;
						
						default: break;
					}
				}

				return Msg::success('Operation has been successfully executed.', array(
					'id' 		=> $process,
					'avatar' 	=> $avatar
				));

			} else {

				return Msg::error('Failed to perform the action. Please try again.');
			}
		}
	}

	public function get_form_data($module = '')
	{
		$data = array();

		switch ($module) {

			case 'cleaners':
				return CrudModel::get_cleaners();

			default:
				return $data;
		}

		return $data;
	}

	public function get_subs_link($fname = '', $email = '')
	{
		$as_callback = ($fname and $email) ? true : false;

		$_fname = Input::has('fname') ? Input::get('fname') : $fname;
		$_email = Input::has('email') ? Input::get('email') : $email;


		if ($_fname and $_email) {

			$baseurl = str_replace('/admin', '/booking/stripe', MODULE_BASE_URL);
			$payload = Aes::payload(array(
				'fname' => $_fname,
				'email' => $_email,
				'ftype' => 'subscription'
			));

			return array(
				'status' => true,
				'pload' => "{$baseurl}?payload={$payload}",
				'fname' => $_fname,
				'email' => $_email
			);

		} else {

			if ($as_callback) {

				return false;
			
			} else {

				return Msg::error('Fail to generate subscription link.');
			}
		}
	}

	public function snd_subs_link()
	{
		if (Input::has(array('fname', 'email'))) {

			$fname = Input::get('fname');
			$email = Input::get('email');

			$url_data = $this->get_subs_link($fname, $email);

			if ($url_data) {

				$payload 	= $url_data['pload'];
				$name 		= $url_data['fname'];


				$template = Template::generate('send_subs', array(
					'logo' 		=> ASSET_LOGO,
					'username' 	=> $name,
					'link' 		=> $payload
				));

				if (ENVIRONMENT === 'production') {

					$bcc = defined('EMAIL_CONFIRM_BCC') ? EMAIL_CONFIRM_BCC : null;

					emailer($email, 'Rosie Services Payment Subscription', $template, $bcc);

				} else {

					log_write('email_conf', $template);
				}

				return Msg::success("Subscription link has been sent to <b>{$name}</b>");
			}
		}


		return Msg::error('Failed to send subscription link.');
	}

	private static function __filter_timetables($data)
	{
		if ($data) {

			// Data filter
			if (isset($data['fullname'])) {

				$fullname = $data['fullname'];
				unset($data['fullname']);

				$data['id'] = $fullname;
				$data['employee_id'] = $fullname;

				if (! CrudModel::verify_cleaner($fullname)) {
					return Msg::error('Selected cleaner is invalid.');
				}
			}

			if (isset($data['date_available'])) {

				$date_available = strtotime($data['date_available']);

				$date_now = strtotime(DATE_NOW);


				if ($date_available >= $date_now) {

					$data['date_available'] = date(DATE_FORMAT, $date_available);
				
				} else {

					return Msg::error('Selected date is invalid.');
				}

			}

			if (isset($data['time_in'], $data['time_out'])) {

				$time_in = strtotime($data['time_in']);
				$time_out = strtotime($data['time_out']);

				if ($time_in < $time_out) {

					$data['time_in'] = date(TIME_FORMAT, $time_in);

					$data['time_out'] = date(TIME_FORMAT, $time_out);
				
				} else {

					return Msg::error('Selected time are invalid.');
				}
			}
		}

		return $data;
	}

	private static function __post_process($module = '', $module_info = array(), $update_info = array())
	{
		if ($module and $module_info and $update_info) {

			switch ($module) {
				case 'administrators':
					if (isset($module_info['email'], $update_info['plain_password'])) {

						$email 		= $module_info['email'];
						$password 	= $update_info['plain_password'];
					}
					break;
				
				default: break;
			}
		}
	}
}
