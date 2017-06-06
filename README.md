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
如果将自己library放入到公共包并且希望使用$this->方式调用，需要将公共包的BaseGouuse.php中的33行加上自己的library。公共类可以自定加入到lib文件夹下。
注意：自己模块的私有类库请放在自己项目的library下

5、使用helper时 使用

GouuseCore\Helpers\ArrayHelper

6、使用数据库日志、返回数据使用数组

在app.php中注册EventServiceProvider
$app->register(GouuseCore\Providers\EventServiceProvider::class);

7、swoole服务管理
端口信息配置在.env文件中
vendor/silentred/lumoon/lumoon start | stop | reload | restart | quit
 
8、自己开发的服务如果要对其他应用提供内部调用，请在Rpcs文件夹下编写自己的rpc客户端，参照memberrpc.php

9、阿里消息订阅服务
在.env中配置：
QUEUE_PREFIX=dev-
QUEUE_MNS_ACCESS_KEY=dZXh0G2laatxxxx
QUEUE_MNS_SECRET_KEY=hdEcO9HAIXtOxpMKjKppVBeixxxx
QUEUE_MNS_ENDPOINT=http://xxxxxxxxxxxxxxxxx.mns.cn-beijing.aliyuncs.com/
发送消息到消息中心：
$messge_data = [消息体组合数组];
$this->sendMessageCenter->sendMessageCenter($messge_data);

发送通用订阅消息：
$messge_data = ['topic_name' => 'user-message-center', 'message_body' => json_encode($messge_data)];
$this->sendMessageCenter->sendMessageTopic($messge_data);


获取订阅消息：
$this->MessageCenterLib->getMessageData($request);

10、通用日志方法
成功日志：$this->LogLib->log_info(['param' => '提交数据', 'result' => '返回数据', 'startTime' => '开始时间戳']);
错语日志：$this->LogLib->log_err(['param' => '提交数据', 'result' => '返回数据', 'startTime' => '开始时间戳']);

11、CodeTool工具是自动将CodeLib.php文件的注释自动转换成数组配置文件
执行方式vendor/bin/CodeTool.php error_code.inc.php
error_code.php为转换后的文件名称，生成的文件在resources/options/zh_cn/目录下

12、加密解密
使用$this->EncryptLib->encrypt() / $this->EncryptLib->decrypt()
