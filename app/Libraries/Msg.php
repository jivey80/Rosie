<?php

namespace App\Libraries;


class Msg
{
	public static function error($message = 'Something went wrong. Please refresh the page and try again.', $code = 200, $to_mail = true)
	{

		if (is_array($message)) {

			$_message = $message;

			$message = $message['Message'];

		} else {

			$_message = array(
				'Message' => $message,
				'Code' => $code
			);
		}


		if (ENVIRONMENT === 'production') {

			if ($to_mail) {

				error_mailer('Error', json_to_htmail($_message), array(DEV_EMAIL));
			}


			// Set to common error when the debug 
			// mode is not set for security purpose
			if (config('app.debug') == false) {
				$message = 'Something went wrong. Please refresh the page and try again.';
			}
		}


		$_error_data = array(
			'status' => 'Error',
			'message' => $message,
			'code' => $code
		);

		if (request()->ajax()) {

			return $_error_data;

		} else {
	
			return view('errors.page', $_error_data);
		}
	}

	public static function success($message = 'Success!', $response_data = array())
	{
		$response = array(
			'status' => 'Success',
			'code' => 200,
			'message' => $message ?: 'Success!'
		);

		if ($response_data and is_array($response_data)) {
			$response += $response_data;
		}

		return $response;
	}
}
