<?php

Route::group(array(
		'prefix' 		=> 'booking',
		'namespace' 	=> 'Modules\Booking\Controllers',
		'middleware' 	=> 'web'
	), function () {

		// Pages
		Route::get('/', 				'BookingController@index');
		Route::post('/details', 		'BookingController@details_page');
		Route::post('/schedule', 		'BookingController@schedule_page');
		Route::post('/confirmation', 	'BookingController@confirmation_page');
		Route::get('/confirm', 			'BookingController@confirmation');
		Route::get('/review', 			'BookingController@review_page');

		// Stripe Integration
		Route::get('/payment', 			'PaymentController@payment_page');
		Route::post('/payment', 		'PaymentController@payment');

		// API
		Route::post('/list_schedule', 	'GeneratorController@list_schedule');
		Route::post('/booking_data', 	'GeneratorController@booking_data');
		Route::post('/booking_save', 	'ValidatorController@booking_save');
		Route::post('/save_review', 	'ValidatorController@review_save');
		Route::post('/verify_email', 	'ValidatorController@email');
	}
);
