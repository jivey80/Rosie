<?php

Route::group(array(
		'prefix' 		=> 'admin',
		'namespace' 	=> 'Modules\Admin\Controllers',
		'middleware' 	=> ['web']
	), function () {		

	    // AJAX Pages
		Route::get('/console',		'AdminController@console_page');
		Route::get('/dashboard', 	'AdminController@dashboard');

		Route::post('/dashboard', 		'AdminController@dashboard');
		
		Route::post('/list_timetables', 'AdminController@list_timetables');
		Route::post('/set_appointment', 'AdminController@set_appointment');
		Route::post('/xpressbook', 		'AdminController@booking');
		
		Route::post('/administrators', 	'AdminController@administrators');
		Route::post('/employees', 		'AdminController@employees');
		Route::post('/clients', 		'AdminController@clients');
		Route::post('/settings', 		'AdminController@settings');

		Route::post('/change_password', 'AdminController@change_password');


		// Autocomplete
		Route::post('/get_client_email', 	'AdminController@list_client_email');


		// CRUD REST
		Route::post('/{module}/add', 		'CrudController@gateway');
		Route::put('/{module}/edit', 		'CrudController@gateway');
		Route::post('/employees/edit', 		'CrudController@gateway');
		Route::delete('/{module}/delete', 	'CrudController@gateway');


		// General
		Route::post('/get_form_data/{method}', 	'CrudController@get_form_data');
		Route::post('/get_subscription_link', 	'CrudController@get_subs_link');
		Route::post('/send_subscription_link', 	'CrudController@snd_subs_link');
	}
);
