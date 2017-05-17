<?php
namespace GouuseCore\Controllers;

use GouuseCore\Core\BaseGouuse;

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
