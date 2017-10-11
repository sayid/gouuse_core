<?php

namespace GouuseCore\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Namshi\JOSE\SimpleJWS;
use App\Libraries\CodeLib;
use GouuseCore\Helpers\RpcHelper;
use GouuseCore\Libraries\AuthLib;

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
                    $member_info = json_decode(urldecode($request->input('GOUUSE_XX_V3_MEMBER_INFO')), true);
                    if (!defined('GOUUSE_MEMBER_INFO')) {
                        define('GOUUSE_MEMBER_INFO', $member_info);
                    }
                    $gouuse_company_info = json_decode(urldecode($request->input('GOUUSE_XX_V3_COMPANY_INFO')), true);
                    if (!defined('GOUUSE_COMPANY_INFO')) {
                        define('GOUUSE_COMPANY_INFO', $gouuse_company_info);
                    }
                    $request->gouuse_member_info = $member_info;
                    $request->gouuse_company_info= $gouuse_company_info;
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
                    $class_load = 'App\Libraries\AccountLib';
                    App::bindIf($class_load, null, true);
                    $accountLib= App::make($class_load);
                    $check_result = $accountLib->check($token);
                    if ($check_result['code'] > 0) {
                        return $check_result['code'];
                    } else {
                        return $check_result['data']['member_info'];
                    }
                } elseif(env('SERVICE_ID') == 1010) {   //运营中心
                    $app = RpcHelper::load('UserCenter', 'Rpc');
                    $result = $app->do('AccountLib', 'check', [$token]);

                    if (isset($result['code']) && $result['code']==0) {
                        //超管判断
                        if (empty($result['data']['member_info'])) {
                            return CodeLib::AUTH_DENY;
                        }
                        if (!isset($result['data']['member_info']['super_admin']) || $result['data']['member_info']['type'] != 1) {
                            return CodeLib::AUTH_DENY;
                        }

                        $result['data']['member_info']['_gouuse_token'] = $token;
                        $gouuse_member_info = $result['data']['member_info'];
                        if (!defined('GOUUSE_MEMBER_INFO')) {
                            define('GOUUSE_MEMBER_INFO', $gouuse_member_info);
                        }
                        $gouuse_company_info = $result['data']['company_info'] ?? [];
                        if (!defined('GOUUSE_COMPANY_INFO')) {
                            define('GOUUSE_COMPANY_INFO', $gouuse_company_info);
                        }
                        $request->gouuse_member_info = $gouuse_member_info;
                        $request->gouuse_company_info= $gouuse_company_info;
                        return $result['data']['member_info'];
                    }
                    return $result['code'];
                } else {

                    $app = RpcHelper::load('UserCenter', 'Rpc');
                    $result = $app->do('AccountLib', 'check', [$token]);

                    if (isset($result['code']) && $result['code']==0) {
                        $result['data']['member_info']['_gouuse_token'] = $token;
                        $gouuse_member_info = $result['data']['member_info'];
                        if (!defined('GOUUSE_MEMBER_INFO')) {
                            define('GOUUSE_MEMBER_INFO', $gouuse_member_info);
                        }
                        $gouuse_company_info = $result['data']['company_info'] ?? [];
                        if (!defined('GOUUSE_COMPANY_INFO')) {
                            define('GOUUSE_COMPANY_INFO', $gouuse_company_info);
                        }
                        $request->gouuse_member_info = $gouuse_member_info;
                        $request->gouuse_company_info= $gouuse_company_info;
                        return $result['data']['member_info'];
                    }
                    return $result['code'];
                }
            }

        });
    }
}
