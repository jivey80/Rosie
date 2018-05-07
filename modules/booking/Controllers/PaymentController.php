<?php

namespace Modules\Booking\Controllers;

use Modules\Booking\Models\PaymentModel;
use Stripe\Stripe as Stripe;
use Stripe\Customer as StripeCustomer;
use Stripe\Charge as StripeCharge;
use Input;
use Aes;
use Msg;


class PaymentController extends Controller
{
	public function index()
	{
		if (Input::has('payload')) {

			$get_payload = Input::get('payload');

			$chk_payload = Aes::verify_payload($get_payload, EXPIRE_SUBS);

			if ($chk_payload) {

				$titles = array(
					'payment' 		=> 'Booking will be confirmed once the payment was successfully made.',
					'subscription'	=> 'Connect to Stripe for faster booking!'
				);


				$arr_payload = json_decode($chk_payload, true);
				if ($arr_payload) {

					$type = $arr_payload['ftype'];

					$arr_payload['panel'] = 'stripe';
					$arr_payload['title'] = $titles[$type];


					// Check if the client has already been subscribed
					$email = $arr_payload['email'];
					$user_data = PaymentModel::get_profile($email);

					if ($user_data) {

						if (! $user_data->stripe_id) {

							// Supply the form with the given parameters.
							return view(MODULE . '::booking.stripe_form', $arr_payload);
						
						} else {

							return self::redirect(
								'Session Invalid.', 
								"Your account has with email <i>{$email}</i> has already been subscribed.",
								true
							);
						}
					
					} else {

						return self::redirect(
							'Session Invalid.', 
							'You are trying to visit an invalid link.',
							true
						);
					}
				}
			}

			return self::redirect(
				'Session Expired.', 
				'The subscription link has already been expired. Please contact the administrator and request a new one.',
				true
			);
		}
	}

	public function process()
	{
		if (Input::has(array('stripeToken', 'stripeEmail', 'stripeType'))) {

			$stripeToken 	= Input::get('stripeToken');
			$stripeEmail 	= Input::get('stripeEmail');
			$stripeType 	= Input::get('stripeType');


			// Check customer profile
			$user_data = PaymentModel::get_profile($stripeEmail);

			if ($user_data) {

				switch ($stripeType) {
					case 'payment':
						return self::payment($stripeToken, $user_data);
					
					case 'subscription':
					default:
						return self::subscription($stripeToken, $user_data);
				}

			} else {

				return self::redirect(
					'Session Invalid.', 
					"Your account has with email <i>{$email}</i> has already been subscribed.",
					true
				);
			}

		} else {

			return self::redirect(
				'Session Invalid.', 
				'You are trying to submit an incomplete form data.',
				true
			);
		}
	}

	private static function payment($token = '', $user_data = array())
	{
		// Check the amount that needs
		// to be paid based from the email.
		// ...
		

		// Check if the user has Stripe Customer ID.
		// Only accounts that has already been
		// subscribed can do the payment.
		// NOTE: Confirm to PM
		// ...
		
		// // Proceed to payment.
		// Stripe::setApiKey(APIKEY_STRIPE_SKEY);
		
		// $charge = StripeCharge::create(array(
		// 	'customer' => $customer->id,
		// 	'amount'   => $payment,
		// 	'currency' => 'usd'
		// ));
	}

	private function subscription($token = '', $user_data = array()) 
	{
		// Verify if the email has the 
		// Stripe Customer ID.
		if ($user_data and !$user_data->stripe_id) {

			Stripe::setApiKey(APIKEY_STRIPE_SKEY);

			$customer = StripeCustomer::create(array(
				'email' 	=> $user_data->email,
				'source'  	=> $token
			));

			if ($customer and isset($customer->id)) {

				PaymentModel::update_profile($user_data->client_id, array(
					'stripe_id' => $customer->id
				));

				return self::redirect(
					'Payment details confirmed!',
					'You are now integrated to Stripe.'
				);
			}

		} else {

			return self::redirect(
				'Session Invalid.', 
				"Your account has with email <i>{$email}</i> has already been subscribed.",
				true
			);
		}
	}

	public static function redirect($title = '', $message = '', $is_error = false)
	{
		return view(MODULE . '::booking.stripe_redirect', array(
			'title' 	=> $title,
			'message'	=> $message,
			'link' 		=> MODULE_BASE_URL,
			'is_error' 	=> $is_error
		));
	}
}
