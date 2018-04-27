<?php

namespace Modules\Admin\Models;

use DB;


class CrudModel
{
	public static function gateway($module = '', $data = array(), $action = '')
	{
		$table = self::get_table($module);

		if ($table) {

			if ((in_array($action, ['edit', 'delete']) and isset($data['id'])) or $action === 'add') {

				$id = isset($data['id']) ? $data['id'] : 0;
				$record = self::filter_params($data, $module);

				switch ($action) {
					case 'add': 	return self::add_record($table, $record);
					case 'edit':	return self::edit_record($table, $id, $record);
					case 'delete': 	return self::delete_record($table, $id);
				}

			} else {

				trigger_error('Request is invalid. Please double-check the submitted data.');
			}

		} else {

			trigger_error('Request is invalid. Cannot recognize the requested module.');
		}
	}

	private static function filter_params($data = array(), $module = '')
	{
		if ($data) {

			// Remove unnecessary columns
			$blacklisted = array(
				// General
				'id',
				'date_registered',

				// client table
				// ...

				// employee table
				'ratings',
				'reviews',

				// config table
				'config_name',
				'config_description'
			);


			// Needs capitalization
			$for_capitalization = array(
				'firstname',
				'lastname',
				'fullname'
			);

			foreach ($data as $column_name => $value) {

				if (in_array($column_name, $for_capitalization)) {
					$data[$column_name] = ucwords(strtolower($value));
				}

				if (in_array($column_name, $blacklisted)) {
					unset($data[$column_name]);
				}
			}
		}

		return $data;
	}

	private static function get_table($module)
	{
		$_module = strtolower($module);

		$table = null;
		$pk_id = 0;


		switch ($_module) {
			case 'clients':	
				$table = 'client';
				$pk_id = 'client_id';
				break;

			case 'administrators':
			case 'employees':
				$table = 'employee';
				$pk_id = 'employee_id';
				break;

			case 'settings':
				$table = 'config';
				$pk_id = 'config_id';
				break;

			case 'cleaner_availability':
				$table = 'timetable';
				$pk_id = 'timetable_id';
				break;

			case 'timetables':
				$table = 'timetable';
				$pk_id = 'timetable_id';
				break;

			default: return false;
		}


		return typecast(array(

			'name' => $table,

			'pkid' => $pk_id

		), 'object');
	}

	private static function add_record($table = '', $data = array())
	{
		return DB::table($table->name)->insertGetId($data);
	}

	private static function edit_record($table = '', $id = 0, $data = array())
	{
		return 	DB::table($table->name)
				->where($table->pkid, '=', $id)
				->update($data);
	}

	private static function delete_record($table = '', $id = 0)
	{
		if ($id) {

			// Check if the record to be
			// deleted is related to any active
			// booking. Including non-confirmed
			// booking records.
			$whitelist = array(
				'client',
				'employee'
			);


			$has_booking = in_array($table->name, $whitelist) ? self::validate_for_booking($table, $id) : false;

			if (! $has_booking) {

				return 	DB::table($table->name)
						->where($table->pkid, $id)
						->update(array(
							'is_active' => 0
						));
			}
		} 

		return false;
	}

	private static function validate_for_booking($table = '', $id = 0)
	{
		switch ($table->name) {
			case 'client':
				$check = 	DB::table('booking')
							->where('client_id', '=', $id)
							->count();

				return $check ? true : false;


			case 'employee':
				$check = 	DB::table('booking as a')
							->join('timetable as b', 'b.timetable_id', '=', 'a.timetable_id')
							->where('b.employee_id', '=', $id)
							->count();

				return $check ? true : false;

			default: return true;
		}
	}

	public static function get_config($id = 0)
	{
		return 	DB::table('config')
				->where('config_id', '=', $id)
				->select('name')
				->first();
	}


	public static function get_cleaners()
	{
		return 	DB::table('employee as a')
				->join('employee_type as b', 'b.employee_type_id', '=', 'a.employee_type_id')
				->where('b.employee_type', 'cleaner')
				->where('a.is_active', 1)
				->select(
					'a.employee_id',
					DB::raw("CONCAT(a.firstname, ' ', a.lastname) as fullname")
				)->get();
	}

	public static function verify_cleaner($employee_id = 0)
	{
		return 	DB::table('employee')
				->where('employee_id', $employee_id)
				->where('employee_type_id', 2)
				->where('is_active', 1)
				->count();
	}
}
