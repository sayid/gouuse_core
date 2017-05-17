<?php
use GouuseCore\Libraries\MessageCenterLib;

/**
 * 测试用例
 * @author lihonglin
 *
 */
class SendMessageTest extends TestCase
{
	protected $member_id;
	protected $company_id;

	/**
	 * 测试消息中心
	 * @return unknown
	 */
	function testSendMessageCenter()
	{	
		$this->sendMessageCenter = new MessageCenterLib();
		
		//组合消息内容
		$data = array();
		$data['company_id'] = '203';
		$data['member_id'] = '682';
		$data['service_id'] = '1000';
		$data['type_id'] = '1';
		$data['data_id'] = '1000000000000';
		$data['status'] = 1;
		$data['subject'] = 'message subject';
		$data['message'] = 'message content';
		$response_data = $this->sendMessageCenter->sendMessageCenter($data);
		print_r($response_data);		
		$this->assertTrue(TRUE);
	}

	/**
	 * 测试消息主题
	 * @return unknown
	 */
	function testSendMessageTopic()
	{
	    $this->sendMessageTopic = new MessageCenterLib();
	
	    //组合消息内容
	    $data = array();
	    $data['company_id'] = '203';
	    $data['member_id'] = '682';
	    $data['service_id'] = '1000';
	    $data['type_id'] = '1';
	    $data['data_id'] = '1000000000000';
	    $data['status'] = 1;
	    $data['subject'] = 'message subject';
	    $data['message'] = 'message content';
	    $msg_data = ['topic_name' => 'user-message-center', 'message_body' => json_encode($data)];
	    $response_data = $this->sendMessageTopic->sendMessageTopic($msg_data);
	    print_r($response_data);
	    $this->assertTrue(TRUE);
	}
}
