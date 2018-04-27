<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Admin\Models\AdminModel;

class ModuleServiceProvider extends ServiceProvider
{
	private static $modules = array(
		'booking',
		'admin'
	);

	public function register()
	{
		// ...
	}

	public function boot()
	{

		$uri = isset($_SERVER['REQUEST_URI']) ? strtolower($_SERVER['REQUEST_URI']) : '';
		
		if (self::_is_valid_module($uri)) {

			$_module = MODULE;

			$_locate_routes = base_path() . "/modules/{$_module}/routes.php";
			if (file_exists($_locate_routes)) {
			
				require $_locate_routes;
			
			} else {

				trigger_error("Routes was not found for this module. Expected location was '{$_locate_routes}'.");
			}


			$_locate_views = base_path() . "/modules/{$_module}/Views";
			if (file_exists($_locate_views)) {

				$this->loadViewsFrom($_locate_views, $_module);
			
			} else {

				trigger_error("Assets were not found for this module. Expected location was '{$_locate_views}'.");
			}
		}
	}

	private static function _is_valid_module($uri)
	{
		$modules = defined('VALID_MODULES') ? unserialize(VALID_MODULES) : self::$modules;

		if ($uri) {

			foreach ($modules as $module) {

				if (strpos($uri, $module) !== false) {

					// Set module constants
					define('MODULE', $module);
					define('MODULE_BASE_URL', 	baseurl() . $module);
					define('MODULE_ASSETS_URL', baseurl() . "modules/{$module}");

					// Set other config constants from database
					AdminModel::set_config();

					return true;
				}
			}
		}

		return false;
	}
}
