<?php

namespace App\Http\Controllers;

use Modules\Booking\Controllers\Controller as BookingMainController;


class TemplateController extends Controller
{
	public function confirmation()
	{
		return view('mailer.booking.confirmation', array(
			'logo' => ASSET_LOGO,
			'name' => 'John Doe',

			'date' => 'Wednesday, September 30',
			'time' => '09:00 AM to 10:00 AM',
			'hourly' => '1 hour at $25.25/hour',
			'total' => 25,

			'avatar' => ASSET_AVATAR_TMP,
			'cleaner' => 'Emma Watson',
			'rating' => 3.5,

			'link' => 'http://www.google.com',
		));
	}

	public function reminder()
	{
		return view('mailer.booking.reminder', array(
			'logo' => ASSET_LOGO,
			'name' => 'John Doe',
			'cleaner' => 'Emma Watson',

			'total' => 25,
			'date' => 'Wednesday, September 30',
			'time' => '09:00 AM to 10:00 AM',

			'avatar' => ASSET_AVATAR_TMP,
			'rating' => 3.5,

			'link' => 'http://www.google.com',
		));
	}

	public function review()
	{
		return view('mailer.booking.review', array(
			'logo' => ASSET_LOGO,
			'name' => 'John Doe',

			'cleaner' => 'Emma Watson',

			'rating_urls' => BookingMainController::review_link(1, 1, 1)
		));
	}

	public function reset_password()
	{
		return view('mailer.reset_password', array(
			'logo' => ASSET_LOGO,
			'name' => 'John Doe',
			'link' => 'http://www.google.com',
		));
	}

	public function new_account()
	{
		return view('mailer.new_account', array(
			'logo' 		=> ASSET_LOGO,
			'username' => 'Vainglory07',
			'password' 	=> 'xxxx'
		));
	}
}
