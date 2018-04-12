<?php

namespace App\Http\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Auth;
use Hash;
use DB;


class AuthModel extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'password',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $table = 'employee';

    protected $primaryKey = 'employee_id';


    // Global authentication utility
    // to fetch login information
    public static function get_info($data = 'all')
    {
        if (Auth::check() and is_string($data)) {

            $login_info = Auth::user()->original;


            switch ($data) {
                case 'id':
                    return Auth::id();

                case 'role':
                    return $login_info['employee_type_id'];

                case 'email':
                    return $login_info['email'];

                case 'all':
                default:
                    return $login_info;
            }            
        }

        return false;
    }

    public static function get_password($username = '')
    {
        if ($username) {

            return  DB::table('employee')
                    ->where('username', '=', $username)
                    ->where('is_active', '=', 1)
                    ->select('password', 'salt')
                    ->first();
        }

        return false;
    }

    public static function check_password($old_password = '')
    {
        $account_info = self::get_info();

        return Hash::check($old_password . $account_info['salt'], $account_info['password'], []);
    }

    public static function update_password($employee_id = 0, $update_data = array())
    {
        if ($employee_id and $update_data) {

            return  DB::table('employee')
                    ->where('employee_id', '=', $employee_id)
                    ->update($update_data);
        }

        return false;
    }
}
