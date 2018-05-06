<?php

namespace Modules\Booking\Models;

use DB;


class PaymentModel
{
	public static function get_profile($email = '')
	{
		return 	DB::table('client')
				->where('email', '=', $email)
				->where('is_active', '=', 1)
				->select(
					'client_id',
					'stripe_id',
					'fullname',
					'username',
					'email',
					'contact_number'
				)->first();
	}

	public static function update_profile($client_id = '', $params = array())
	{
		return 	DB::table('client')
				->where('client_id', '=', $client_id)
				->update($params);
	}
}
