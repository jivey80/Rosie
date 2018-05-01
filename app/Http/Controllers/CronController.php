<?php

namespace App\Http\Controllers;

use Modules\Booking\Controllers\Controller as BookingMainController;
use App\Http\Models\CronModel;
use Template;


class CronController extends Controller
{
	/**
	 * Checks for all of the slots already
	 * expired/done after 5 minutes from the
	 * specified end date and time.
	 *
	 * All of the qualified from this criteria
	 * will receive an email of the app's
	 * Cleaner Review feature.
	 *
	 * Must be running in background/crontab at
	 * least every 5 minutes.
	 */
	public function review_mailer()
	{

		$list = CronModel::get_review_recipients();

		if ($list) {

			$base_url = baseurl() . 'modules/booking';
			$star_url = "{$base_url}/images/icons/star32.png";

			$review_urls = array();

			foreach ($list as $for_review) {

				$booking_id = $for_review->booking_id;
				$client_id = $for_review->client_id;
				$employee_id = $for_review->employee_id;

				$name_employee = ucwords($for_review->employee_name);
				$name_client = ucwords($for_review->client_name);

				// Generate the HTML template
				$review_url = BookingMainController::review_link($booking_id, $client_id, $employee_id);
				$review_urls[] = $review_url;

				$template = Template::generate('review', array(
					'name' => $name_client,
					'logo' => ASSET_LOGO,
					'star' => $star_url,

					'cleaner' => $name_employee,

					'rating_urls' => $review_url
				));


				// Send email confirmation
				if (ENVIRONMENT === 'production') {

					$bcc = defined('EMAIL_RATING_BCC') ? EMAIL_RATING_BCC : null;

					emailer($for_review->email, "Review for {$name_employee}", $template, $bcc);
				}


				// Set flag to prevent the booking
				// being included to the next run
				// of automation
				CronModel::update_review_status($booking_id);


				// Backup debugging
				log_write('recipients_review', $for_review);
			}

			return (ENVIRONMENT === 'local') ? $review_urls : array(
				'status' => count($list) . ' booking(s) recipients found. Email sent.'
			);

		} else {

			return array(
				'status' => 'Unauthorized'
			);
		}
	}


	/**
	 * Checks all of the running booking
	 * and sends email notification if the 
	 * booking is about to start as defined
	 * from config BOOKING_REMINDER_TIME.
	 */
	public function booking_reminder()
	{
		$sent = array();
		$for_update = array();

		$slots = CronModel::get_running_slots();


		if ($slots and defined('BOOKING_REMINDER_TIME')) {

			$effectivity = BOOKING_REMINDER_TIME / 24;
			$now_timestamp = strtotime(date(TIMESTAMP_FORMAT));


			foreach ($slots as $key => $slot) {

				if ($slot->countdown > $effectivity) {

					$payload = typecast(array(
						's_id' => "t{$slot->timetable_id}_o0_na",
						'b_id' => $slot->booking_id,
						'prc' => typecast($slot->price, 'float')
					), 'object');

					$summary = BookingMainController::slot_summary($payload);

					$template = Template::generate('reminder', array(
						'logo' 		=> ASSET_LOGO,
						'name' 		=> ucwords($slot->client_name),
						'cleaner' 	=> "{$summary['firstname']} {$summary['lastname']}",

						'total' 	=> $summary['price'],
						'date' 		=> $summary['date_available'],
						'time' 		=> "{$summary['schedule_start']} to {$summary['schedule_end']}",

						'avatar' 	=> $summary['avatar'],
						'rating' 	=> $summary['rate'],

						'link' 		=> 'https://www.google.com'
					));

					// Send email reminder
					if (ENVIRONMENT === 'production') {

						$bcc = defined('EMAIL_REMINDER_BCC') ? EMAIL_REMINDER_BCC : null;

						emailer($slot->email, "Rosie Booking Reminder", $template, $bcc);
					}

					$sent[] = $slot;

					$for_update[] = $slot->booking_id;
				}
			}


			if ($sent) {

				CronModel::update_booking_reminder($for_update);

				log_write('recipients_reminder', $sent);
			}
		}

		return array(
			'status' => count($sent) . ' booking(s) has been reminded.'
		);
	}


	/**
	 * Clearing logs is very important 
	 * to prevent future issues regarding
	 * not enough disk/storage capacity.
	 *
	 * Must be run every 3 AM (low traffic hour)
	 *
	 * The script will check for logs
	 * last modified date that is already 1 week old.
	 *
	 * 		* 3 * * * 	to run the script every 3 AM
	 */
	public function clear_logs()
	{

		// Change this value to your
		// prefered days of checking.
		// Defaults to 7 Days (1 week) old file
		// to be deleted.
		$_DAYS = 7;
		$expiry = ((60 * 60) * 24) * $_DAYS;


		$storage_path = storage_path();
		

		// Log folders to scan through
		$log_folders = array(
			'framework/views',
			'logs'
		);


		// Log files to skip
		$log_except = array(
			'.',
			'..',
			'.gitignore'
		);


		// Total files deleted
		$total = 0;
		

		foreach ($log_folders as $folder) {

			$log_folder = "{$storage_path}/{$folder}";

			if (is_dir($log_folder)) {

				$scan_dir = scandir($log_folder);
				$scan_cls = array_diff($scan_dir, $log_except);

				if ($scan_cls) {

					foreach ($scan_cls as $filename) {

						$file = "{$log_folder}/{$filename}";

						$date_modified = filemtime($file);
						$date_today = strtotime(date(TIMESTAMP_FORMAT));
						$date_offset = $date_today - $date_modified;


						if ($date_offset >= $expiry) {
							unlink($file);

							$total++;
						}
					}
				}
			}
		}

		return array('total_files_cleared' => $total);
	}
}
