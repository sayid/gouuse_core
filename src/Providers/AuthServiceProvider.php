<?php

namespace GouuseCore\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Namshi\JOSE\SimpleJWS;

class AuthServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
	}
	
	/**
	 * Boot the authentication services for the application.
	 *
	 * @return void
	 */
	public function boot()
	{
		
		if (isset($_SERVER['HTTP_GOUUSE_INSIDE'])) {
			//内部调用
			if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || preg_match('/192\.168\.(\d+).(\d+)/', $_SERVER['REMOTE_ADDR'])) {
				define('REQUEST_IS_LOCAL', true);
			}
		}
		
		// Here you may define how you wish users to be authenticated for your Lumen
		// application. The callback which receives the incoming request instance
		// should return either a User instance or null. You're free to obtain
		// the User instance via an API token or any other method necessary.
		
		$this->app['auth']->viaRequest('api', function ($request) {
			
			if (!defined('NEED_AUTH_CHECK')) {
				return;
			}
			
			/**
			 * 验证，权限判断
			 */
			if (defined('REQUEST_IS_LOCAL')) {
				
				if (isset($_SERVER['HTTP_CURRENT_MEMBER_INFO'])) {
					//当前用户id 不用再查询数据库
					$member_info = json_decode(urldecode($_SERVER['HTTP_CURRENT_MEMBER_INFO']), true);
					//返回数据给auth控件
					return $member_info;
				}
			}
			
			$token = $request->header('Authorization');
			if (empty($token)) {
				$token = $request->input('_gouuse_token');
			} else {
				$token = explode(' ', $token);
				$token = end($token);
			}
			
			/**
			 * 登录验证，权限判断
			 */
			if ($token) {
				if (env('SERVICE_ID') == 1005) {
					//用户中心
					$member_id = 0;
					$supper_admin = 0;
					try {
						$jwt = SimpleJWS::load($token, true);
						$public_key = openssl_pkey_get_public(file_get_contents(ROOT_PATH . env('GATEWAY_APP_PUB_CERT')));
						
						if ($jwt->isValid($public_key, 'RS256')) {
							$payload = $jwt->getPayload();
							$member_id = $payload['member_id'];
							$supper_admin= $payload['supper_admin'] ?? 0;
						}
					} catch (DecryptException $e) {
						//
						return;
					}
					
					
					if ($supper_admin== 0) {
						$class_load = 'App\Libraries\MemberLib';
						App::bindIf($class_load, null, true);
						$memberLib = App::make($class_load);
						$member_info = $memberLib->memberInfo(['member_id' => $member_id]);
					} else {
						$class_load = 'App\Models\SystemMemberModel';
						App::bindIf($class_load, null, true);
						$systemMemberModel = App::make($class_load);
						$member_info = $systemMemberModel->getById($member_id);
					}
					
					if (!empty($member_info)) {
						if ($supper_admin) {
							$member_info['supper_admin'] = $supper_admin;
						}
						return $member_info;
					}
				} else {
					
					App::bindIf('GouuseCore\Rpcs\AuthCenterRpc', null, true);
					$member_api = App::make('GouuseCore\Rpcs\AuthCenterRpc');
					
					$result = $member_api->check($token);
					
					if (isset($result['code']) && $result['code']==0) {
						return $result['data'];
					}
				}
			}
			
		});
			
			/**********定义权限*********/
			Gate::define('admin-super-auth', function ($user) {
				//A后台 超级管理员
				return isset($user['supper_admin']) && $user['type'] == 0 ? true : false;
			});
				
				Gate::define('admin-company-auth', function ($user, $company) {
					//B后台 企业管理员
					return $user['member_id'] == $company['admin_id'];
				});
	}
}
