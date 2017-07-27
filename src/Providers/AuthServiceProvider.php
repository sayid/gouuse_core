<?php

namespace GouuseCore\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Namshi\JOSE\SimpleJWS;
use App\Libraries\CodeLib;

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
				
				if (isset($_SERVER['HTTP_CURRENT_MEMBER_ID'])) {
					//当前用户id 不用再查询数据库
					app()['gouuse_member_info'] = $member_info = json_decode(urldecode($request->input('GOUUSE_XX_V3_MEMBER_INFO')), true);
					app()['gouuse_company_info'] = json_decode(urldecode($request->input('GOUUSE_XX_V3_COMPANY_INFO')), true);
					$request->gouuse_member_info = app()['gouuse_member_info'];
					$request->gouuse_company_info= app()['gouuse_company_info'];
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
			if (empty($token)) {
				//需要token登录
				return CodeLib::AUTH_REQUIRD;
			}
			/**
			 * 登录验证，权限判断
			 */
			if ($token) {
				if (env('SERVICE_ID') == 1005) {
					//用户中心
					$member_id = 0;
					$super_admin= 0;
					$first_login = 0;
					try {
						$jwt = SimpleJWS::load($token, true);
						$public_key = openssl_pkey_get_public(file_get_contents(ROOT_PATH . env('GATEWAY_APP_PUB_CERT')));
						
						if ($jwt->isValid($public_key, 'RS256')) {
							$payload = $jwt->getPayload();
							
							$member_id = $payload['member_id'];
							$super_admin= $payload['super_admin'] ?? 0;
							$first_login= $payload['first_login'] ?? 0;
						}
					} catch (\InvalidArgumentException $e) {
						return CodeLib::AUTH_FAILD;
					} catch (DecryptException $e) {
						//
						return CodeLib::AUTH_FAILD;
					}
					
					//移动端或web端或pc端 app端需要单点登录
					$client_type = $request->input('source') == 2 || $request->input('source') == 3 ? 1 : 0;
					
					$where = [];
					$where['member_id'] = array(
							"sign" => "=",
							"value" => $member_id
					);
					$where['client_type'] = array(
							"sign" => "=",
							"value" => $client_type //单点登录
					);
					$where['member_type'] = array(
							"sign" => "=",
							"value" => $super_admin ? 1 : 0 //区分管理员token
					);
					$class_load = 'App\Models\AccessTokenModel';
					App::bindIf($class_load, null, true);
					$accessTokenModel= App::make($class_load);
					
					$token_row = $accessTokenModel->getOne("", $where);
					if (empty($token_row) || $token_row['token'] != $token) {
						//已在其他客户端登录
						return CodeLib::AUTH_ON_OTHER_CLIENT;
					}
					
					if ($super_admin== 0) {
						$class_load = 'App\Models\MemberModel';
						App::bindIf($class_load, null, true);
						$memberLib = App::make($class_load);
						$member_info = $memberLib->getMemberData($member_id);
					} else {
						$class_load = 'App\Models\SystemMemberModel';
						App::bindIf($class_load, null, true);
						$systemMemberModel = App::make($class_load);
						$member_info = $systemMemberModel->getById($member_id);
					}
					if (!empty($member_info)) {
						if ($super_admin) {
							$member_info['super_admin'] = $super_admin;
						}
						$member_info['first_login'] = $first_login || empty($member_info['last_login_time']) ? 1 : 0;
						app()['gouuse_member_info'] = $member_info;
						$company_info = [];
						if (isset($member_info['company_id']) && $member_info['company_id'] > 0) {
							$class_load = 'App\Models\CompanyModel';
							App::bindIf($class_load, null, true);
							$companyModel= App::make($class_load);
							$company_info = $companyModel->getById($member_info['company_id']);
						}
						app()['gouuse_company_info'] = $company_info;
						$request->gouuse_member_info = app()['gouuse_member_info'];
						$request->gouuse_company_info= app()['gouuse_company_info'];
						return $member_info;
					} else {
						return CodeLib::AUTH_FAILD;
					}
				} else {
					App::bindIf('GouuseCore\Rpcs\AuthCenterRpc', null, true);
					$member_api = App::make('GouuseCore\Rpcs\AuthCenterRpc');
					$result = $member_api->check($token);
					if (isset($result['code']) && $result['code']==0) {
						$result['data']['member_info']['_gouuse_token'] = $token;
						app()['gouuse_member_info'] = $result['data']['member_info'];
						app()['gouuse_company_info'] = $result['data']['company_info'];
						$request->gouuse_member_info = app()['gouuse_member_info'];
						$request->gouuse_company_info= app()['gouuse_company_info'];
						return $result['data']['member_info'];
					}
					return $result['code'];
				}
			}
			
		});
			
			/**********定义权限*********/
			Gate::define('admin-super-auth', function ($user) {
				//A后台 超级管理员
				return isset($user['super_admin']) && $user['type'] == 1 ? true : false;
			});
				
				Gate::define('admin-company-auth', function ($user, $company) {
					//B后台 企业管理员
					return $user['member_id'] == $company['admin_id'];
				});
	}
}
