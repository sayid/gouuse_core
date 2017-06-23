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
	
	public $member_info = array();
	public $company_info = [];
	
	public function __construct()
	{
		$this->app_id =  env('GOUUSE_APP_ID');
		$this->memberInit();
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
				if (env('SERVICE_ID') == 1007) {
					//企业中心应用内
					$this->company_info = $this->CompanyModel->getById($this->member_info['company_id']);
				} else {
					//非企业中心应用内
					try {
						$gouuse_company_info= $this->CompanyCenterRpc->getInfo($this->member_info['company_id']);
						app()['gouuse_company_info'] = $this->company_info = $gouuse_company_info['data'];
					} catch (\Exception $e) {
						return null;
					}
				}
			}
			app()['gouuse_company_info'] = $this->company_info;
		}
	}
	
	
	public function __get($class)
	{
		
		/*if ($class == 'member_info') {
		 if (!defined('NEED_AUTH_CHECK')) {
		 return null;
		 }
		 if (isset($GLOBALS['gouuse_member_info'])) {
		 return $GLOBALS['gouuse_member_info'];
		 }
		 $GLOBALS['gouuse_member_info'] = Auth::user();
		 return $GLOBALS['gouuse_member_info'];
		 } */
		
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
