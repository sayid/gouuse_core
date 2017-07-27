<?php
namespace GouuseCore\Libraries;

use AliyunMNS\Client;
use AliyunMNS\Topic;

use AliyunMNS\Model\BatchSmsAttributes;
use AliyunMNS\Model\MessageAttributes;
use AliyunMNS\Exception\MnsException;
use AliyunMNS\Requests\PublishMessageRequest;

/**
 * @author  chensheng
 ***/

class SmsAliLib {
		
	
	/**
	 * 发送短信逻辑
	 * @param unknown $template 模板id
	 * @param $sign_id 签名，如 "够用云工作平台"
	 * @param unknown $mobile
	 * @param array $data
	 */
	public function sendSms($template_id, $sign, $mobile, $data = [])
	{
 		$this->endPoint = env('SMS_MNS_ENDPOINT');
		$this->accessId = env('SMS_MNS_ACCESS_KEY');
		$this->accessKey = env('SMS_MNS_SECRET_KEY');
		$this->client = new Client($this->endPoint, $this->accessId, $this->accessKey);
		
		/**
		 * Step 2. 获取主题引用
		 */
		$topicName = env('SMS_TOPIC');
		
		$topic = $this->client->getTopicRef($topicName);
		/**
		 * Step 3. 生成SMS消息属性
		 */
		// 3.1 设置发送短信的签名（SMSSignName）和模板（SMSTemplateCode）
		$batchSmsAttributes = new BatchSmsAttributes($sign, $template_id);
		// 3.2 （如果在短信模板中定义了参数）指定短信模板中对应参数的值
		$batchSmsAttributes->addReceiver($mobile, $data);
		$messageAttributes = new MessageAttributes(array($batchSmsAttributes));
		/**
		 * Step 4. 设置SMS消息体（必须）
		 *
		 * 注：目前暂时不支持消息内容为空，需要指定消息内容，不为空即可。
		 */
		$messageBody = "smsmessage";
		/**
		 * Step 5. 发布SMS消息
		 */
		$request = new PublishMessageRequest($messageBody, $messageAttributes);
		try
		{
			$res = $topic->publishMessage($request);
			return ['status' => $res->isSucceed(),'message_id' => $res->getMessageId()];
		}
		catch (MnsException $e)
		{
			return ['status' => false, 'exception' => $e];
		}
		
	}
}
