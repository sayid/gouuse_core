<?php
namespace GouuseCore\Controllers;

use GouuseCore\Core\BaseGouuse;
use GouuseCore\Helpers\OptionHelper;//配置文件

/**
 * 基类 复写了lumen基类
 * @author zhangyubo
 *
 */
class Controller extends BaseGouuse
{
    use \Laravel\Lumen\Routing\ProvidesConvenienceMethods;

    protected $output;
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * 后续扩展返回数据 加密等等
     * @param array $data
     * @return unknown
     */
    public function display(array $data)
    {   
        $msg = 'ok';
        $code = isset($data['code']) ? $data['code'] : 0;
        if (!empty($code)) {
            $lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'zh_cn';
            $lang = $lang ? $lang : 'zh_cn';
            $error_code = OptionHelper::getOption('error_code','options',$lang);
            $msg = isset($error_code[$code]) ? $error_code[$code] : '未知错误';
            
            if(isset($data['data'])){
                foreach ($data['data'] as $key => $value) {
                    $msg=str_replace("{".$key."}", $value, $msg);
                }
            }
        }        
        $data['msg'] = $msg;

        return $data;
    }
    
    /**
     * The middleware defined on the controller.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * Define a middleware on the controller.
     *
     * @param  string  $middleware
     * @param  array  $options
     * @return void
     */
    public function middleware($middleware, array $options = [])
    {
        $this->middleware[$middleware] = $options;
    }

    /**
     * Get the middleware for a given method.
     *
     * @param  string  $method
     * @return array
     */
    public function getMiddlewareForMethod($method)
    {
        $middleware = [];

        foreach ($this->middleware as $name => $options) {
            if (isset($options['only']) && ! in_array($method, (array) $options['only'])) {
                continue;
            }

            if (isset($options['except']) && in_array($method, (array) $options['except'])) {
                continue;
            }

            $middleware[] = $name;
        }

        return $middleware;
    }
}
