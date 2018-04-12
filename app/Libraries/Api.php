<?php

namespace App\Libraries;


use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;


class Api
{

	private static $url_gmatrix = 'https://maps.googleapis.com/maps/api/distancematrix/json';


	private static function api_call($uri, $method = 'POST', $params = array())
	{

		$_method = strtoupper($method);
		$_params = urldecode(http_build_query($params));

		$allowed_methods = array(
			'GET',
			'POST'
		);

		if (in_array($_method, $allowed_methods)) {

			if (! is_array($params)) {
				trigger_error('Parameters must be in the form of array.');
			}

			$client = new Client();

			$result = $client->request($_method, $uri, array(
				'verify' => false,
				'query' => $_params
			));


			$body = (string) $result->getBody();

			$data = json_decode($body, true);

			if ($data) {

				log_write('api_call', array(
					'params' 	=> $_params,
					'response' 	=> $data
				));

				return $body;

			} else {
				
				log_write('err_api_call', $_params);

				return array();
			}

		} else {

			trigger_error('Required HTTP Method is not allowed.');
		}
	}

	/**
	 * Gets distance between a defined origin and destination
	 * using Google Distance Matrix API.
	 *
	 * https://developers.google.com/maps/documentation/distance-matrix/start
	 *  
	 * @param  array  $origin      Expects 2 keys, latitude and longitude. Should be in order.
	 * @param  array  $destination Expects 2 keys, latitude and longitude. Should be in order.
	 * @param  bool   $is_verbose  Return full api response or just the time of travel in seconds.
	 * @return array.
	 */
	public static function get_distance($origin = array(), $destination = array(), $is_verbose = false)
	{
		$api_response = array();


		// Auto padding
		$padding = SCHEDULE_PADDING;


		$_response = array();
		$_response['rows'][0]['elements'][0]['duration']['value'] = 0;
		$_response['rows'][0]['elements'][0]['distance']['value'] = 0;
		$api_response = json_encode($_response);


		if ($origin and is_array($origin) and $destination and is_array($destination)) {

			$params = array(
				'origins' 		=> implode(',', $origin),
				'destinations' 	=> implode(',', $destination),

				/**
				 * driving		
				 * walking
				 * bicycling
				 * transit 		(default)
				 */
				'mode'		=> 'transit',

				'transit_mode' => 'bus|rail',

				/**
				 * metric 	(default) returns distances in kilometers and meters.
				 * imperial returns distances in miles and feet.
				 */
				'units'		=> 'metric',

				'key'		=> APIKEY_GMATRIX
			);


			if ($params['origins'] != $params['destinations']) {

				$api_response = self::api_call(self::$url_gmatrix, 'POST', $params);
			}


			if (! $is_verbose) {

				if ($api_response) {

					$api_data = json_decode($api_response, true);

					if ($api_data and isset($api_data['rows'][0]['elements'][0]['duration']['value'])) {

						// Get the distance and check if
						// it has already reached 2 kilometers
						// and above.
						// 
						// There will be specific default 
						// padding for this condition.
						$distance = typecast($api_data['rows'][0]['elements'][0]['distance']['value'], 'integer') / 1000;

						if ($distance >= 2 and defined('SCHEDULE_PADDING_2KM')) {
							$padding += SCHEDULE_PADDING_2KM;
						}

						$duration = $api_data['rows'][0]['elements'][0]['duration']['value'] + $padding;

						return typecast($duration, 'integer');
					}
				}
			}

			return $api_response;
		}
		
		return $api_response;
	}
}
