<?php

namespace App\Http\Models;

use DB;
use Aes;
use Template;


class PasswordModel
{
	public static function generate()
	{
		$plain = str_random(6);


		// This is the actual password used
		// by laravel on hashing.
		$password = bcrypt($plain);


		// This is not actually salt. But
		// to store only the actual value
		// of the hashed password for retrieval
		// purposes in case forgotten password
		// is requested instead of reset.
		$salt = Aes::encrypt($plain);


		// New password data in 8 alpha-numeric
		return array(
			'salt' => $salt,
			'plain' => $plain,
			'password' => $password
		);
	}

	public static function reset_email($id = 0)
	{
		$account = self::get_account($id);

		if ($account) {

			// Check if email is valid
			if (isset($account->email) and filter_email($account->email)) {

				$email = $account->email;

				// Send reset email notification
				$template = Template::generate('reset_password', array(
					'name' => $account->firstname,
					'logo' => ASSET_LOGO,
					'link' => 'http://www.google.com',
				));

				// Send email reminder
				if (ENVIRONMENT === 'production') {

					$bcc = defined('EMAIL_REMINDER_BCC') ? EMAIL_REMINDER_BCC : null;

					emailer($email, "Rosie Admin Reset Password", $template, $bcc);
				}
			}
		}

		return false;
	}

	public function reset_confirm()
	{

		// @temp
		// Not yet finalized, yet to be continued
		// 
		// Generate password
		$new_password = self::generate();
		unset($new_password['plain']);
		$new_password['updated_at'] = date(TIMESTAMP_FORMAT);


		// Update the account's password
		self::update_account($id, $new_password);
	}

	/**
	 * Notify the user about the current password.
	 */
	public static function notify_user($id = 0)
	{

	}


	/**
	 * Gets all the information related
	 * to account password setup.
	 * 
	 * @param  integer $id employee_id
	 */
	private static function get_account($id = 0)
	{
		return 	DB::table('employee')
				->where('employee_id', '=', typecast($id, 'integer'))
				->where('is_active', '=', 1)
				->select(
					'employee_id',
					'employee_type_id',
					'email',
					'firstname',
					'lastname',
					'username',
					'password',
					'salt'
				)->first();
	}

	private static function update_account($id = 0, $params = array())
	{
		if ($id and $params) {

			return 	DB::table('employee')
					->where('employee_id', '=', typecast($id, 'integer'))
					->update(typecast($params, 'array'));
		}

		return false;
	}
}
