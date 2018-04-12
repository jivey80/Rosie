<?php

namespace Modules\Booking\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Modules\Booking\Models\GeneratorModel;
use Modules\Booking\Models\ValidatorModel;
use Modules\Booking\Models\ReviewModel;
use Aes;
use Msg;

class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    
    /**
     * Statically declared time interval
     * for the schedule selection dropdown.
     */
    public static $valid_hours = array(
    	1, 
    	1.5, 
    	2, 
    	2.5, 
    	3, 
    	3.5, 
    	4, 
    	4.5, 
    	5
   	);

    /**
     * Checks if the hour has a valid 
     * value based from the pre-defined
     * time interval self::$valid_hours
     */
    public static function valid_hour($hour)
    {
    	$type_hour = typecast($hour, 'float');

    	return in_array($type_hour, self::$valid_hours) ? $type_hour : 0;
    }


    /**
     * General function to handle success responses.
     */
    public static function success($message = '')
    {
    	return Msg::success(typecast($message, 'string'));
    }

    /**
     * 
     * 
     */
    /**
     * Centralized function to get and return
     * the specific slot information.
     * 
     * @param  integer $group_id
     * @param  integer $price
     * @param  integer $client_id Optional
     */
    protected static function get_slot($group_id = 0, $price = 0, $client_id = 0)
	{

		$result = array();

		if ($group_id and is_string($group_id)) {

			$data = explode('_', $group_id);

			if (count($data) === 3) {

				$timetable_id = typecast(substr($data[0], 1), 'int');
				$interval = $data[2];

				$mail = $client_id ? ValidatorModel::client_email($client_id) : false;

				$timetable = GeneratorModel::get_active_timetable();
				$slotlist = GeneratorModel::generate_schedule($timetable, $interval, $mail);

				if ($slotlist) {

					$chosen_slot = array();
					foreach ($slotlist as $slot) {
						if ($slot['group_id'] == $group_id) {
							$chosen_slot = $slot;
							break;
						}
					}

					$chosen_timetable = array();
					foreach ($timetable as $schedule) {
						if ($schedule->timetable_id == $timetable_id) {
							$chosen_timetable = typecast($schedule, 'array');
							break;
						}
					}

					$review = isset($chosen_timetable['employee_id']) ? ReviewModel::get_review_summary($chosen_timetable['employee_id']) : array();

					if ($chosen_slot and $chosen_timetable and $review) {

						unset($chosen_slot['index']);
						unset($chosen_slot['group_id']);
						unset($chosen_slot['timetable_id']);

						unset($chosen_timetable['timetable_id']);
						unset($chosen_timetable['employee_id']);
						unset($chosen_timetable['time_in']);
						unset($chosen_timetable['time_out']);

						$_hours = typecast($chosen_slot['hours'], 'float');

						if (isset($price) and $price > 0) {

							$_price = typecast($price, 'float');
						
						} else {

							$_price = typecast($chosen_slot['price'], 'float');
						}

						$hours_text = $_hours > 1 ? "{$_hours} hours" : "{$_hours} hour";
						$hours_price = $_price / $_hours;
						$hours_price_clean = strip_decimal($hours_price, 2);
						
						$chosen_slot['hours_text'] = $hours_text . ' at $' . $hours_price_clean . '/hour';
						$chosen_slot['schedule_start'] = date(TIME_NOSEC_FULL_FORMAT, strtotime($chosen_slot['schedule_start']));
						$chosen_slot['schedule_end'] = date(TIME_NOSEC_FULL_FORMAT, strtotime($chosen_slot['schedule_end']));
						$chosen_slot['price'] = $_price;

						$chosen_timetable['firstname'] = ucwords($chosen_timetable['firstname']);
						$chosen_timetable['lastname'] = ucwords($chosen_timetable['lastname']);
						$chosen_timetable['date_available'] = date(BOOKING_TIMESTAMP_FULL, strtotime($chosen_timetable['date_available']));

						$reviews = typecast($review['reviews'], 'integer');
						$review['reviews'] = ($reviews > 1) ? "{$reviews} Reviews" : "{$reviews} Review";
						$review['rate'] = typecast($review['rate'], 'float');

					} else {

						log_write('err_get_slot', array(
							'chosen_slot' => $chosen_slot,
							'chosen_timetable' => $chosen_timetable,
							'review' => $review
						));

						return Msg::error('An unexpected error occured. Please try again later.');
					}

					$result = array_merge($chosen_slot, $chosen_timetable, $review);
				}
			}
		}

		return $result;
	}

	public static function slot_summary($args = array())
	{
		$response = array();

		if ($args and isset($args->s_id, $args->b_id, $args->prc)) {

			$schedule_id = explode('_', $args->s_id);

			$price = typecast($args->prc, 'float');
			$hours = ($schedule_id[2] == 'na') ? $schedule_id[2] : typecast($schedule_id[2], 'float');
			$booking_id = typecast($args->b_id, 'integer');


			$slot = GeneratorModel::get_slot_summary($booking_id);

			if ($slot) {

				$review = ReviewModel::get_review_record($slot->employee_id, true);

				if ($review) {

					$response = typecast($slot, 'array') + typecast($review, 'array');


					// Reformat data
					$response['schedule_start'] = date(TIME_NOSEC_FULL_FORMAT, strtotime($response['schedule_start']));
					$response['schedule_end'] = date(TIME_NOSEC_FULL_FORMAT, strtotime($response['schedule_end']));
					$response['price'] = $price;

					if ($hours !== 'na') {

						$hours_text	 = ($hours > 1) ? "{$hours} hours" : "{$hours} hour";
						$hours_price = strip_decimal(($price / $hours), 2);

						$response['hours'] = $hours;
						$response['hours_text'] = $hours_text . ' at $' . $hours_price . '/hour';
					}

					$baseurl = ASSET_AVATAR_URL;

					$avatar_img = $response['avatar'] ? $response['avatar'] : 'default.png';
					$response['avatar'] = "{$baseurl}/{$avatar_img}";

					$count = typecast($response['count'], 'integer');
					$response['reviews'] = $count > 1 ? "{$count} Reviews" : "{$count} Review";
					$response['rate'] = $response['rating'] ?: 0;

					$response['date_available'] = date(BOOKING_TIMESTAMP, strtotime($response['date_available']));

					unset(
						$response['booking_id'],
						$response['client_id'],
						$response['timetable_id'],
						$response['employee_id'],

						$response['employee'],
						$response['rating'],
						$response['count']
					);
				}
			}
		}

		return $response;
	}

	public static function review_link($booking_id = 0, $client_id = 0, $employee_id = 0)
	{

		// Check if the booking_id has already
		// been reviewed
		$is_reviewed = ReviewModel::is_reviewed($booking_id, $client_id);

		$star_url = ASSET_IMG . '/icons/star32.png';


		$stars = array();
		for ($i = 0; $i < 5; $i++) {
			
			$star_value = $i + 1;

			// Create payload for stars
			$payload = ($is_reviewed) ? 'nein' : Aes::payload(array(
				'bid' => $booking_id,
				'cid' => $booking_id,
				'eid' => $employee_id,
				'str' => $star_value
			));


			// @note 
			// use of baseurl() helper instead of constant because the method
			// is being used on CronController(outsite the module) as well.
			$url = baseurl() . "booking/review?pl={$payload}";
			

			$html = "<a href=\"{$url}\" target=\"_new\" style=\"width: 110px; height: 25px; display: inline-block; padding: 10px 0 0 0; text-decoration: none; color: #000; background: #FFCDD2; border-radius: 3px; cursor: pointer;\">";
			for ($s_i = 0; $s_i < $star_value; $s_i++) {
				$html .= "<img style=\"max-width: 45%; max-height: 45%; padding: 0; margin: 2px;\" src=\"{$star_url}\" alt=\"{$star_value} Stars\" title=\"{$star_value} Stars\"/>";
			}
			$html .= '</a>';


			$stars[$star_value] = $html;

		}

		return implode("\n", $stars);
	}
}
