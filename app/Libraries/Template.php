<?php

namespace App\Libraries;


class Template
{
	public static function generate($type, $placeholders)
	{	
		$types = array(
			'confirmation' 		=> 'booking/confirmation.blade.php',
			'reminder' 			=> 'booking/reminder.blade.php',
			'review' 			=> 'booking/review.blade.php',

			'reset_password' 	=> 'reset_password.blade.php',
			'new_account' 		=> 'new_account.blade.php',
		);

		if (isset($types[$type])) {

			$file = $types[$type];
			$dir = __DIR__;


			$loc = "{$dir}/../Http/Views/mailer/{$file}";
			
			if (file_exists($loc)) {

				$template = file_get_contents($loc);

				if ($placeholders and is_array($placeholders)) {

					foreach ($placeholders as $find => $replace) {

						$template = str_replace('{{$'.$find .'}}', $replace, $template);
						$template = str_replace('{!!$'.$find .'!!}', $replace, $template);
					}
				}

				return $template;
			}
		}
	}
}
