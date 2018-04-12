<?php

if (! function_exists('generate_salt')) {

	function generate_salt()
	{
		return sha1(uniqid(true) . date('Y-m-d H:i:s A'));
	}
}


if (! function_exists('generate_password')) {

	function generate_password($plain_password = '', $salt = '')
	{
		if ($plain_password and $salt) {

			return bcrypt($plain_password . $salt);
		}

		return false;
	}
}


if (! function_exists('authenticate')) {

	function authenticate($plain_password = '', $password = '', $salt = '')
	{
		if ($plain_password and $password and $salt) {

			$_password = generate_password($plain_password, $salt);

			return ($password === $_password) ? true : false;

		}

		return false;
	}
}


if (! function_exists('simple_auth')) {

	function simple_auth($custom_password = '')
	{

		$plain_password = $custom_password ? $custom_password : str_random(8);

		$salt = generate_salt();

		$auth_data = array(
			'plain_password' 	=> $plain_password,
			'password' 			=> generate_password($plain_password, $salt),
			'salt' 				=> $salt			
		);

		return $auth_data;
	}
}
