<?php
/**
 * 消息处理
 * 用于向消息阅服务器和消息中心提交数据
 * @author  李洪林
 * @version 2017-5-17
 */
namespace GouuseCore\Libraries;

use GouuseCore\Helpers\FormHelper;

class LogLib extends Lib
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 通过消息订阅方式推送消息
     * @param array $msg_data 消息内容，以下为数组需要的属性
     * @param string topic_name 消息主题
     * @param string message_body 消息内容
     * @return array 返回数据;
     */
    public function sendMessageTopic($msg_data)
    {
        $topic_name = trim(FormHelper::__getData($msg_data, 'topic_name'));
        $message_body = trim(FormHelper::__getData($msg_data, 'message_body'));    
        $queue = $this->client->getTopicRef(env('QUEUE_PREFIX').$topic_name);
        $push_message_obj = new PublishMessageRequest($message_body);
        $re_push_data = $queue->publishMessage($push_message_obj);
        return $re_push_data;
    }
    
    /**
     * 给消息中心发消息
     * @param array $msg_data 消息内容，以下为数组需要的属性
     * @param string company_id 公司id
     * @param string member_id 用户id，多个用户id用逗号隔开
     * @param string messasge 消息内容，为json
     * @param string service_id 服务id
     * @param string type_id 服务操作类型id
     * @param string data_id 服务数据id
     * @param string status 消息状态：0未读，1已读
     * @param string subject 消息提示语
     * @return array 返回数据;
     */
    public function sendMessageCenter($msg_data)
    {
        //消息来源
        $message_source = intval(FormHelper::__getData($msg_data, 'message_source', 0));
        
        //获取公司id
        $company_id = intval(FormHelper::__getData($msg_data, 'company_id', 0));
        
        //获取用户id
        $member_id = FormHelper::__getData($msg_data, 'member_id');
        
        //获取服务id
        $service_id = intval(FormHelper::__getData($msg_data, 'service_id'));
        
        //获取消息状态
        $status = intval(FormHelper::__getData($msg_data, 'status', 1));
        
        //获取操作动作id
        $type_id = intval(FormHelper::__getData($msg_data, 'type_id'));
        
        //获取数据id
        $data_id = intval(FormHelper::__getData($msg_data, 'data_id'));
        
        //获取消息内容
        $message = trim(FormHelper::__getData($msg_data, 'message'));
        
        //消息提示语
        $subject = trim(FormHelper::__getData($msg_data, 'subject', '你有新的消息请注意查收'));
        
        //验证数据是否完整
        if ($company_id==0 || $service_id==0 || $status>1) {
            return array('code' => '1001000001', 'data' => array());
        }
        
        //组合消息内容
        $push_message = new \stdClass();
        $push_message->message_source = $message_source;
        $push_message->company_id = $company_id;
        $push_message->member_id = $member_id;
        $push_message->service_id = $service_id;
        $push_message->type_id = $type_id;
        $push_message->subject = $subject;
        $push_message->message = $message;
        $push_message->status = $status;
        $push_message->send_time = time();
        $msg_data = ['topic_name' => 'user-message-center', 'message_body' => json_encode($push_message)];
        $re_push_data = $this->sendMessageTopic($msg_data);
        return $re_push_data;
    }
    
    /**
     * 获取消息订阅服务器提交过来的数据
     * @return array 返回数据;
     */
    public function getMessageData(){
        $data = file_get_contents('php://input');
        $msg_base64_json = json_decode($data, true);
        $msg_data_base64_json = $msg_base64_json["Message"];
        $msg_data_json = base64_decode($msg_data_base64_json);
        return json_decode($msg_data_json, true);
    }
}
