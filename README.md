# gouuse_core

使用方法：

1、第一步在composer文件中引入该包

"gouuse/core":"1.1.*"


2、在自己项目框架中，将controller继承为

use GouuseCore\Controllers\Controller

3、将model继承为

use GouuseCore\Models\BaseModel;

4、将lib继承为

use GouuseCore\Libraries\Lib;
如果将自己library放入到公共包并且希望使用$this->方式调用，需要将公共包的BaseGouuse.php中的33行加上自己的library。公共类可以自定加入到lib文件夹下

5、使用helper时 使用

GouuseCore\Helpers\ArrayHelper

6、使用数据库日志

首先在app\prividers\EventServiceProvider.php中配置监听

 	protected $listen = [
        'App\Events\SomeEvent' => [
            'App\Listeners\EventListener',
        ],  
        'Illuminate\Database\Events\QueryExecuted' => [  
            'GouuseCore\Listeners\QueryListener'  
        ]
    ];
    
然后在app.php中注册EventServiceProvider

$app->register(App\Providers\EventServiceProvider::class);

7、swoole服务管理
端口信息配置在.env文件中
 vendor/bin/lumoon start | stop | reload | restart | quit

