<?php

namespace Modules\Booking\Models;

use DB;


class ValidatorModel
{

	/**
	 * Checks if the email has already
	 * been saved to the database.
	 */
	public static function email($email = '', $get_id = false)
	{

		$result = array();

		if ($email) {

			$result = 	DB::table('client')
						->where('email', '=', typecast($email, 'string'))
						->where('is_active', '=', 1)
						->select(
							'client_id',
							'email as mail',
							'fullname as name',
							'latitude',
							'longitude'
						)->first();

			if (! $get_id and $result and isset($result->client_id)) {

				unset($result->client_id);
			}
		}

		return (array) $result;
	}

	/**
	 * Gets the client email based from
	 * the given client_id.
	 * 
	 * @param  integer $client_id
	 */
	public static function client_email($client_id = 0)
	{
		if ($client_id) {

			$data = DB::table('client')
					->where('client_id', '=', $client_id)
					->where('is_active', '=', 1)
					->first();

			return ($data and isset($data->email)) ? $data->email : false;
		}

		return false;
	}
}
