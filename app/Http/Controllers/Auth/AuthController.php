<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Input;
use Validator;
use Redirect;
use Session;
use Cache;
use Auth;
use App\Http\Models\AuthModel;
use App\Http\Controllers\Controller;
use Msg;


class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */
    use AuthenticatesAndRegistersUsers, ThrottlesLogins;


    // Where to redirect users after login / registration.
    protected $redirectTo = '/admin/console';

    // Where to redirect users after logout
    protected $redirectAfterLogout = '/admin/login';

    // Username and Password field equivalent from the database
    protected $username = 'username';
    protected $password = 'password';



    // Create a new authentication controller instance.
    public function __construct()
    {
        $this->middleware('guest', [
            'only' => 'login'
        ]);


        // Implement Auth only to the defined
        // valid modules.
        if (! defined('MODULE')) {
            
            return Msg::error('The URL you are trying to access does not exists.');
        }
    }


    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'username' => 'required|max:255',
            'password' => 'required|confirmed|min:6'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return AuthModel::create([
            'username' => $data['username'],
            'password' => bcrypt($data['password']),
        ]);
    }


    public function showLoginForm()
    {
        if (Auth::guest()) {

            return view('admin::pages.login');
        
        } else {

            return redirect($this->redirectTo);
        }
    }


    public function login()
    {
        $username = Input::get('username');
        $password = Input::get('password');


        // Get and append salt
        $password_data = AuthModel::get_password($username);

        if ($password_data) {
            $password .= $password_data->salt;
        }
        
        // Validate login form login
        $credentials = array(
            'username' => $username,
            'password' => $password
        );

        if (Auth::attempt($credentials)) {

            return Msg::success('Successful login.');

        } else {

            return Msg::error('Failed to perform the action. Please try again.');
        }
    }

    public function logout()
    {
        Auth::logout();
        Cache::flush();
        Session::flush();
        Redirect::back();

        return redirect($this->redirectAfterLogout);
    }
}
