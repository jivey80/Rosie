<?php

/**
 * Project Information
 */
define('PROJECT_NAME', 		'Rosie Booking');


/**
 * Common Settings
 */
// Valid modules
define('VALID_MODULES', serialize(array(
		'admin',
		'booking'
	))
);

// Total hour quota per timetable
define('QUOTA_HOUR_LIMIT', 3);

// Map API cache validity
define('MAP_CACHE_LIFETIME', 3);


// Date and time formats
define('DATE_NOW', 					date('Y-m-d'));
define('DATE_FORMAT', 				'Y-m-d');
define('TIMESTAMP_FORMAT', 			'Y-m-d H:i:s');
define('TIMESTAMP_FULL_FORMAT', 	'Y-m-d H:i:s A');

define('TIME_FORMAT', 				'H:i:s');
define('TIME_FULL_FORMAT', 			'g:i:s A');
define('TIME_NOSEC_FULL_FORMAT',	'g:i A');

define('BOOKING_TIMESTAMP', 		'l, F j');
define('BOOKING_TIMESTAMP_FULL', 	'l, F j, Y');


// Dev tools
// -----
// Force the use of minified assets
// on local
define('FORCE_MIN_ASSETS', false);
define('DEV_EMAIL', 'jedianela14@gmail.com');