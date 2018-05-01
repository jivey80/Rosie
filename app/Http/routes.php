<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {

	// Cron
	Route::match(array('POST', 'GET'), '/cron/booking_reminder', 	'CronController@booking_reminder');
    Route::match(array('POST', 'GET'), '/cron/review_mailer',       'CronController@review_mailer');
    Route::match(array('POST', 'GET'), '/cron/clear_logs',          'CronController@clear_logs');


	// Authentication
    Route::get('/admin/login', 	'AuthController@showLoginForm');
    Route::post('/admin/login', 	'AuthController@login');
    Route::get('/admin/logout', 	'AuthController@logout');


    // Registration
    // Route::get('register',   'Auth\AuthController@showRegistrationForm');
    // Route::post('register', 'Auth\AuthController@register');

    // Password Reset Routes
    // Route::get('password/reset/{token?}',    'Auth\PasswordController@showResetForm');
    // Route::post('password/email',            'Auth\PasswordController@sendResetLinkEmail');
    // Route::post('password/reset',            'Auth\PasswordController@reset');


    // Templates
    Route::get('/template/confirmation',    'TemplateController@confirmation');
    Route::get('/template/reminder',        'TemplateController@reminder');
    Route::get('/template/review',          'TemplateController@review');
    Route::get('/template/reset_password',  'TemplateController@reset_password');
    Route::get('/template/new_account',     'TemplateController@new_account');
});

