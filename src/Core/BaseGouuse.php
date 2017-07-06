<?php

namespace GouuseCore\Core;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;

/**
 * @author zhangyubo
 *
 */
class BaseGouuse
{
	
	protected $app_id;
	
	//public $member_info = [];
	//public $company_info = [];
	
	public function __construct($params = [])
	{
		$this->app_id =  env('SERVICE_ID');
	}
	
	public function memberInit()
	{
		if (!defined('NEED_AUTH_CHECK')) {
			return null;
		}
		if (isset(app()['gouuse_member_info'])) {
			$this->member_info = app()['gouuse_member_info'];
			if (isset(app()['gouuse_company_info'])) {
				$this->company_info = app()['gouuse_company_info'];
			}
		} else {
			$this->member_info = Auth::user();
			app()['gouuse_member_info'] = $this->member_info;
			
			if (!empty($this->member_info)) {
				if (env('SERVICE_ID') == 1005) {
					$class_load = "App\Models\CompanyModel";
					App::bindIf($class_load, null, true);
					$companyModel= App::make($class_load);
					app()['gouuse_company_info'] = $companyModel->getById($company_id);
					return app()['gouuse_company_info'];
				} else {
					App::bindIf('GouuseCore\Rpcs\CompanyCenterRpc', null, true);
					$company_api = App::make('GouuseCore\Rpcs\CompanyCenterRpc');
					$company_info = $company_api->getById($company_id);
					app()['gouuse_company_info'] = [];
					if ($company_info['code'] != 0) {
						return response($company_info)->send();exit();
					}
					return app()['gouuse_company_info'];
				}
			}
			app()['gouuse_company_info'] = $this->company_info;
		}
	}
	
	
	public function __get($class)
	{
		
		if ($class == 'member_info') {
			if (!defined('NEED_AUTH_CHECK')) {
				return null;
			}
			if (isset(app()['gouuse_member_info'])) {
				return app()['gouuse_member_info'];
			}
			app()['gouuse_member_info']= Auth::user();
			return app()['gouuse_member_info'];
		} else if ($class == 'company_info') {
			if (isset(app()['gouuse_company_info'])) {
				return app()['gouuse_company_info'];
			}
		}
		
		if (substr($class, strlen($class) - 3)=='Lib') {
			if (class_exists("GouuseCore\Libraries\\".$class)) {
				$class_load = "GouuseCore\Libraries\\".$class;
				$class = strtolower($class);
			} else {
				$class_load = "App\Libraries\\".$class;
				$class = strtolower($class);
			}
			
		} elseif (substr($class, strlen($class) - 5)=='Model') {
			$class = str_replace('_', '', $class);
			$class_load = "App\Models\\".$class;
			$class = strtolower($class);
		} elseif (substr($class, strlen($class) - 3)=='Rpc') {
			$class_load = "GouuseCore\Rpcs\\".$class;
			$class = strtolower($class);
		} else {
			return;
		}
		App::bindIf($class_load, null, true);
		$obj = App::make($class_load);
		return $obj;
		/**
		 * 优化单利模式，使用lumen自带对
		 if (isset(app()['gouuse_'.$class_load])) {
		 return app()['gouuse_'.$class_load];
		 }
		 app()['gouuse_'.$class_load] = new $class_load;
		 return app()['gouuse_'.$class_load];
		 */
	}
}
