<?php
/*!
 * @author jdia07
 * @copyright (c) 2015, Vainglory07 02.07
 */

/*! 
 | ------------------------------------------------------
 | Strings
 | ------------------------------------------------------
 */
if (! function_exists('cast')) {

    /**
     * Cast data to specific data type.
     * If data type is not provided, raw data is returned.
     * If array|object is converted to string, returned data is json_encoded 
     * instead.
     * 
     * @param  mixed   $data Any data value
     * @param  string  $cast Data type [string|int|float|double|array|object|bool]
     * @return [mixed]
     * 
     * Defaults the datatype to string if not defined.
     * Sensitive to urlencoded string.
     */
    function typecast($data = null, $cast = 'string')
    {

        switch (strtolower($cast)) {
            case 'str':
            case 'string':
            default:
                $string = (is_array($data) or is_object($data)) ? json_encode($data) : (string) $data;
                return $string;

            case 'int': 
            case 'integer':
                return is_object($data) ? 0 : (int) $data;

            case 'float': 
                return is_object($data) ? 0 : (float) $data;

            case 'double': 
                return is_object($data) ? 0 : (double) $data;

            case 'arr': 
            case 'array':
                return (array) $data;

            case 'obj': 
            case 'object':
                return (object) $data;

            case 'bool': 
                return $data ? true : false;
        }
    }
}

if (! function_exists('is_json')) {

    /**
     * Checks if valid json. 
     * Returns decoded json if valid.
     * 
     * @param  mixed  $json
     * 
     * @return boolean
     * 
     */
    function is_json($json = null)
    {

        if ($json and is_string($json)) {

            if (is_object(json_decode($json)))  { 

                return true;
            }

        } 

        return false;
    }
}

if (! function_exists('suggest_username')) {

	/*!
	 * Username auto-suggestion
	 *
	 * 
	 * @param  string  $firstname
	 * @param  string  $lastname
	 * @param  integer $max_firstname_char Max no. of characters to use in firstname. Default 0
	 * @param  integer $max_lastname_char  Max no. of characters to use in lastname. Default 3
	 * 
	 * @return array List of possible usernames
	 */
	function suggest_username($firstname = '', $lastname = '', $max_firstname_char = 0, $max_lastname_char = 3)
    {
	
    	function lastname_min ($lastname = '', $max_lastname_char = 0)
        {
			$vowels = array('a', 'e', 'i', 'o', 'u');

			$trim = str_replace(' ', '', $lastname);
			$char = str_split($trim);
			$cnts = count($char);

			if ($cnts <= $max_lastname_char) {

				return "{$lastname}";

			} else {

				for ($i = $max_lastname_char - 2; $i < $cnts; $i++) {

					if (isset($char[$i]) and in_array($char[$i], $vowels)) {
						$max_lastname_char = $i + 2;
						break;
					}
				}

				$result = substr($trim, 0, $max_lastname_char);

				return "{$result}";
			}
		}

		if ($firstname and $lastname) {

			$result = [];
			
			$arr_firstname = explode(' ', $firstname);
			$str_lastname = lastname_min($lastname, $max_lastname_char);
			
			foreach ($arr_firstname as $names) {

				$str_firstname = ($max_firstname_char > 0) ? substr($names, 0, 1) : $names;
				$str_separator = ($max_firstname_char > 0) ? '' : '.';

				$result[] = "{$str_firstname}{$str_separator}{$str_lastname}";
			}

			return $result;

		}

        return trigger_error('Incomplete parameters.');
	}
}



/*! 
 | ------------------------------------------------------
 | Numbers
 | ------------------------------------------------------
 */
if (! function_exists('minify_number')) {

    /**
     * Converts any given number into shorthand value.
     * 
     * @param integer|float $number
     * @param boolean $as_word Use word or letter for big numbers
     * 
     * @return string
     * 
     */
    function minify_number($number = 0, $as_word = false)
    {

        $float = number_format(cast($number, 'float'));
        $suffix = array(
            'Thousand' => 'K',
            'Million' => 'M',
            'Billion' => 'B',
            'Trillion' => 'T',
            'Quadrillion' => 'Quad',
            'Quintillion' => 'Quin'
        );
        
        $extract = explode(',', $float);
        $count = count($extract) - 2;
        $i = 0;
        
        if ($count >= 0 and $number > 0) {

            foreach ($suffix as $word => $shorthand) {

                if ($count === $i) {

                    $addfix = $as_word ? " $word" : $shorthand;
                    $get_dec = substr($extract[1], 0, 1);
                    $decimal = $get_dec ? ".{$get_dec}" : '';
           
                    return "{$extract[0]}{$decimal}" . $addfix;
                }

                $i++;
            }
        }

        return $number;
    }
}

if (! function_exists('get_probability')) {

    /**
     * Get random data based from given probability
     * 
     * @param array|object $weightedValues array({value} => {percentage of probability})
     * 
     * @return string|integer Key from $weightedValues
     * 
     */
    function get_probability($weightedValues = array())
    {

    	$values = array();
        $weights = array();
        foreach ($weightedValues as $k => $v) {
            $values[] = $k;
            $weights[] = $v;
        }
        
        $count = count($values); 
        $i = 0; 
        $n = 0; 
        $num = mt_rand(0, array_sum($weights)); 
        while ($i < $count) {

            $n += $weights[$i];

            if($n >= $num){
                break; 
            }

            $i++; 
        }

        return $values[$i];
    }
}



/*!
 | ------------------------------------------------------
 | Date and Time
 | ------------------------------------------------------
 */
if (! function_exists('list_timezone')) {

    /**
     * Returns an array|list of valid PHP supported timezones
     * 
     * @param  boolean $as_select_option Return result as <select> option
     * 
     * @return array|string
     * 
     */
    function list_timezone($as_select_option = false)
    {

    	// Get complete list of php supported gmt values
    	$timezone = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $result = array();

    	foreach($timezone as $zones) {

    		$now = new DateTime("now");
    		$now->setTimezone(new DateTimeZone($zones));

            if ($as_select_option) {

                $result[] = "<option value=\"{$zones}\"> [{$now->format('P')}] {$now->format('e')} </option>";

            } else {

                $result[$zones] = "({$now->format('P')})|{$now->format('e')}";
            }
    	}

    	return $as_select_option ? implode("\n", $result) : $result;
    }
}

if (! function_exists('date_convert')) {

    /**
     * Convert time from one timezone to another
     * 
     * @param string $date
     * @param string $timezone Defaults to UTC
     * @param string $format Defaults to Y-m-d H:i:s Timestamp Standard
     * 
     * @return string
     * 
     */
    function date_convert($date = '', $timezone = 'UTC', $format = 'Y-m-d H:i:s')
    {
        if ($date) {

            $valid_timezone = list_timezone();
            if (isset($valid_timezone[$timezone])) {

                $obj_date = new DateTime($date);
                $obj_date->setTimezone(new DateTimeZone($timezone));

                return $obj_date->format($format);

            }

            return defined('DATETIME_EMPTY') ? DATETIME_EMPTY : '0000-00-00 00:00:00';
        }

        return defined('DATETIME_EMPTY') ? DATETIME_EMPTY : '0000-00-00 00:00:00';
    }
}

if (! function_exists('timeline')) {

    /**
     * Timeline tracker.
     * 
     * @param string $datetime Datetime to track with.
     * 
     * @return boolean|string
     * 
     */
    function timeline($datetime = null)
    {

        if ($datetime and strtotime($datetime)) {

            $ref_dt = date(defined('TIMESTAMP_FORMAT') ? TIMESTAMP_FORMAT : 'Y-m-d H:i:s');
            $off_dt = strtotime($ref_dt) - strtotime($datetime);
            

            if ($off_dt > 0) {

                $stats = date_diff(new DateTime($datetime), new DateTime($ref_dt));

                $params = array(
                    'y' => 'year', 
                    'm' => 'month', 
                    'd' => 'day', 
                    'h' => 'hour', 
                    'i' => 'minute', 
                    's' => 'second'
                );
                
                $result = array();

                foreach ($params as $param => $legend) {

                    $value = $stats->$param;

                    if ($value > 0) {

                        $result[$param] = "{$value} {$legend}";
                        $result[$param] .= $value > 0 ? 's' : '';
                    }
                }

                $chunk = array_chunk($result, 2);
                $final = reset($chunk);

                return implode(', ', $final);

            }

            return 'Now';
        }

        return false;
    }
}


/*! 
 | ------------------------------------------------------
 | Misc
 | ------------------------------------------------------
 */
if (! function_exists('io')) {

    /**
     * Outputs any number of passed values in <pre> tag
     * Accepts any datatype
     */
    function io()
    {

        $args = func_get_args();

        foreach ($args as $arg) {

            echo '<pre>'; print_r($arg); echo '</pre>';
            
        } die;
    }
}


if (! function_exists('baseurl')) {

    /**
     * Gets the base url of the application.
     * With HTTP injection filter.
     * 
     * @return string
     */
    function baseurl($get_host = false)
    {
     
        if (isset($_SERVER['HTTP_HOST'])) {

            $server_host = $_SERVER['HTTP_HOST'];

        } else if (isset($_SERVER['SERVER_NAME'])) {

            $server_host = $_SERVER['SERVER_NAME'];

        } else {

            $server_host = null;
        }


        if ($server_host) {

            $host = trim(preg_replace('/[^a-z \d \. _]/i ', '', strip_tags($server_host)));
            $protocol = (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
            $location = substr(str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']), 0, -1);
            $base_url = "{$protocol}://{$host}{$location}/";
            
            return $get_host ? $host : $base_url;

        }
    }
}

if (! function_exists('log_write')) {

    function log_write($filename = 'file', $content = 'content')
    {

        $file_info = pathinfo($filename);
        $file_name = isset($file_info['extension']) ? $file_info['basename'] : "{$file_info['filename']}.log";
        $file_content = typecast($content, 'string');
        $file_storage = storage_path('logs');

        if (! file_exists($file_storage)) {
            $old = umask(0);
            mkdir($file_storage, 0777);
            umask($old);
        }

        $date = defined(DATE_FORMAT) ? date(DATE_FORMAT) : date('Y-m-d');

        $file = "{$file_storage}/{$date}_{$file_name}";

        file_put_contents($file, "{$file_content} \n----\n", FILE_APPEND);
    }
}

// Load backend assets CONSTANTS.
// Instead of manually declaring same
// values all over the app.
if (! function_exists('asset_constants')) {

    function asset_constants()
    {
        $baseurl = baseurl();

        $constants = array(
            'ASSET_IMG'         => "{$baseurl}assets/images",
            'ASSET_LOGO'        => "{$baseurl}assets/images/logo.png",

            'ASSET_AVATAR_URL'  => "{$baseurl}assets/images/avatar",
            'ASSET_AVATAR_TMP'  => "{$baseurl}assets/images/avatar/default.png",
            'ASSET_AVATAR_PATH' => dirname(__DIR__) . '/../public/assets/images/avatar',
        );


        foreach ($constants as $constant => $value) {

            if (! defined($constant)) {
                define(strtoupper($constant), $value);
            }
        }
    }

    asset_constants();
}
