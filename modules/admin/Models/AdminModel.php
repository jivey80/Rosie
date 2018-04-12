<?php

namespace Modules\Admin\Models;

use DB;


class AdminModel
{
	public static function active_booking()
	{
		$rows = DB::table('booking as a')
				->join('timetable as b', 'b.timetable_id', '=', 'a.timetable_id')
				->join('client as c', 'c.client_id', '=', 'a.client_id')
				->join('employee as d', 'd.employee_id', '=', 'b.employee_id')
				->where('a.is_confirmed', '=', 1)
				->where('c.is_active', '=', 1)
				->where('d.is_active', '=', 1)
				->whereRaw('DATE_ADD(TIMESTAMP(b.date_available, a.schedule_end), INTERVAL 6 HOUR) >= DATE(NOW())')
				->orderBy('b.date_available', 'ASC')
				->orderBy('a.schedule_start', 'ASC')
				->select(
					'a.booking_id as bid',
					'b.employee_id as eid',
					'c.client_id as cid',
					
					'd.firstname as emp_fname',
					'd.lastname as emp_lname',
					'd.avatar as emp_avatar',
					
					'c.fullname as cl_fname',
					'c.address as cl_address',
					'c.contact_number as cl_contact',
					
					'a.schedule_start',
					'a.schedule_end',
					DB::raw("DATE_FORMAT(a.schedule_start, '%l:%i %p') as time_start"),
					DB::raw("DATE_FORMAT(a.schedule_end, '%l:%i %p') as time_end"),
					DB::raw("DATE_FORMAT(b.date_available, '%a, %b %e') as booking_date")
				)->get();


		$active_booking = array();
	
		if ($rows) {

			foreach ($rows as $row) {

				$date = $row->booking_date;
				$name = strtolower("{$row->emp_fname}_{$row->emp_lname}");

				$active_booking[$row->booking_date][$name][] = $row;
			}
		}

		return array(
			'active_booking' => $active_booking,
			'active_count' => count($rows)
		);
	}

	public static function settings($get_raw_data = false)
	{
		$rows = DB::table('config')
				->orderBy('name', 'ASC')
				->select(
					'config_id as id',
					'name as config_name',
					'value',
					'description as config_description'
				)->get();

		return $get_raw_data ? $rows : grid_json(
			$rows, 
			array(
				'id',
				'config_name',
				'value',
				'config_description'
			), 
			array(
				'value'
			)
		);
	}

	public static function set_config()
	{
		$settings = self::settings(true);

		if ($settings) {

			foreach ($settings as $config) {

				if (! defined($config->config_name) and $config->value) {
					define($config->config_name, $config->value);
				}
			}
			
			return true;
		} 

		return trigger_error('fuck this shit');
	}

	public static function top_clients()
	{
		$rows = DB::table('booking as a')
				->join('client as b', 'b.client_id', '=', 'a.client_id')
				->join('view_booking_stats as c', 'c.client_id', '=', 'a.client_id')
				->groupBy('a.client_id')
				->orderBy('total_bookings', 'DESC')
				->select(
					'a.client_id',
		    
				    'b.fullname as client_name',
				    'b.email',
				    
				    'c.confirmed',
				    'c.pending',
				    DB::raw("(c.confirmed + c.pending) as total_bookings")
				)->get();

		return grid_json(
			$rows, 
			null,
			array(
				'client_id',
				'client_name',
				'email',

				'confirmed',
				'pending',
				'total_bookings'
			)
		);
	}

	public static function top_cleaners()
	{
		$range = array(
			'from' 	=> date('Y-m-01'),
			'to' 	=> date('Y-m-t')
		);

		return 	DB::table('reviews as a')
				->join('employee as b', 'b.employee_id', '=', 'a.employee_id')
				->whereBetween('a.timestamp', $range)
				->groupBy('a.employee_id')
				->orderBy('rating', 'DESC')
				->orderBy('count', 'DESC')
				->select(
					'a.employee_id',
				    DB::raw("CONCAT(b.firstname, ' ', b.lastname) as employee"),
					DB::raw("TRUNCATE(AVG(a.rating), 1) as rating"),
				    DB::raw("COUNT(a.rating) as count")
				)->get();
	}

	public static function administrator()
	{
		$rows = DB::table('employee as a')
				->join('employee_type as b', 'b.employee_type_id', '=', 'a.employee_type_id')
				->where('a.is_active', '=', 1)
				->where('b.employee_type', '=', 'administrator')
				->groupBy('a.employee_id')
				->select(
					'a.employee_id as id',
					'a.firstname',
					'a.lastname',
					'a.username',
					'a.email',

					DB::raw("DATE_FORMAT(a.created_at, '%Y, %b %d (%h:%s %p)') as date_registered"),
					DB::raw("DATE_FORMAT(a.updated_at, '%Y, %b %d (%h:%s %p)') as last_updated")
				)->get();

		return grid_json(
			$rows, 
			array(
				'id',
				'firstname',
				'lastname',
				'username',
				'email',

				'date_registered',
				'last_updated'
			),
			array(
				'firstname',
				'lastname',
				'username',
				'email'
			)
		);
	}

	public static function employee()
	{
		$rows = DB::table('employee as a')
				->join('employee_type as b', 'b.employee_type_id', '=', 'a.employee_type_id')
				->leftJoin('reviews as c', 'c.employee_id', '=', 'a.employee_id')
				->where('a.is_active', '=', 1)
				->where('b.employee_type', '=', 'cleaner')
				->groupBy('a.employee_id')
				->select(
					'a.employee_id as id',
					'a.avatar',
					'a.firstname',
					'a.lastname',
					'a.username',
					'a.email',
					
					DB::raw("IFNULL(TRUNCATE(AVG(c.rating), 1), 0) as ratings"),
					DB::raw("COUNT(c.rating) as reviews"),

					DB::raw("DATE_FORMAT(a.created_at, '%Y, %b %d (%h:%s %p)') as date_registered"),
					DB::raw("DATE_FORMAT(a.updated_at, '%Y, %b %d (%h:%s %p)') as last_updated")
				)->get();

		return grid_json(
			$rows, 
			array(
				'id',
				'firstname',
				'lastname',
				'username',
				'email',
				
				'ratings',
				'reviews',

				'date_registered',
				'last_updated'
			),
			array(
				'firstname',
				'lastname',

				'avatar'
			)
		);		
	}

	public static function client()
	{
		$rows = DB::table('client')
				->where('is_active', '=', 1)
				->select(
					'client_id as id',
					'fullname',
					'username',
					'email',
					'address',
					'latitude',
					'longitude',
					'contact_number',
					DB::raw("DATE_FORMAT(date_registered, '%Y, %b %d (%h:%s %p)') as date_registered")
				)->get();

		return grid_json(
			$rows, 
			array(
				'id',
				'fullname',
				'username',
				'email',
				'address',
				'latitude',
				'longitude',
				'contact_number',
				'date_registered'
			),
			array(
				'fullname',
				'username',
				'email',
				'address',
				'latitude',
				'longitude',
				'contact_number'
			)
		);
	}

	public static function list_timetables()
	{
		$range = get_display_range(SCHEDULE_RANGE);

		$rows = 	DB::table('timetable as a')
					->join('employee as b', 'b.employee_id', '=', 'a.employee_id')
					->whereBetween('a.date_available', $range)
					->where('b.is_active', '=', 1)
					->orderBy('a.employee_id', 'ASC')
					->orderBy('a.date_available', 'ASC')
					->select(
						'a.timetable_id',

						DB::raw("CONCAT(b.firstname, ' ', b.lastname) as fullname"),

						DB::raw("DATE_FORMAT(a.date_available, '%m/%d/%Y') as date_available"),
						DB::raw("TIME_FORMAT(a.time_in, '%H:%i') as time_in"),
						DB::raw("TIME_FORMAT(a.time_out, '%H:%i') as time_out")
					)->get();

		return grid_json(
			$rows, 
			array(
				'timetable_id',

				'fullname',

				'date_available',
				'time_in',
				'time_out'
			),
			array(
				'fullname',

				'date_available',
				'time_in',
				'time_out'
			)
		);
	}
}
