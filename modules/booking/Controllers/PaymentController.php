<?php

namespace Modules\Booking\Controllers;

use Stripe\Stripe as Stripe;
use Stripe\Customer as StripeCustomer;
use Stripe\Charge as StripeCharge;
use Input;
use Msg;


class PaymentController extends Controller
{
	public function payment_page()
	{
		return view(MODULE . '::booking.payment');
	}

	public function payment()
	{
		Stripe::setApiKey(APIKEY_STRIPE_SKEY);

		$token  = Input::get('stripeToken');
		$email  = Input::get('stripeEmail');
		$payment = 2500;

		$customer = StripeCustomer::create(array(
			'email' 	=> $email,
			'source'  	=> $token
		));

		$charge = \Stripe\Charge::create(array(
			'customer' => $customer->id,
			'amount'   => $payment,
			'currency' => 'usd'
		));

		echo '<h3 style="font-family: tahoma, consolas;">Successfully charged $50.00!</h3>';
	}
}
