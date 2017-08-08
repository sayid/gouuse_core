<?php

namespace GouuseCore\Libraries;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use GouuseCore\Helpers\RpcHelper;

/**
 * 分布式事务类
 */
class TcLib extends Lib
{
	//通知时调用的方法
	private $notify;
	
	private $tc_id; //事务id
	
	private $service_id;//服务id
	
	private $child_tcs = [];//子事务数据
	
	private $rollback_data = []; //本地事务备份数据
	
	private $topic_name = 'v3-transaction-center';
	
	const STEP_START = 0;//开启事务
	const STEP_CANCEL = 1;//撤回事务
	const STEP_CONFIRM =2;//确认事务
	//const STEP_CANCEL_FAILD =3;//撤回事务失败
	
	public $client;
	
	public function __construct()
	{
		$this->service_id = $service_id = env('SERVICE_ID');//服务id
		parent::__construct();
	}
	
	/**
	 * 开启事务中心事务
	 * 需要本地事务记录本地事务提交的信息，以便做数据回滚
	 * @param $notify_url 设置回调通知地址,通知回滚、撤销等等
	 * @param $notify_level 事务等级 0=发出去后不管，不用回调不用通知，1=需要通知需要回调，2=失败时需要回滚
	 */
	public function tcStart($notify)
	{
		//本地事务开启
		DB::beginTransaction();
		
		$this->notify = $notify;
	}
	
	/**
	 * 撤回事务
	 */
	public function tcRollback()
	{
		DB::rollback();
		
	}
	
	/**
	 * 添加本地事务需要备份数据
	 * @param array $child_log_data
	 */
	public function tcAddRollback($rollback_data= [])
	{
		$this->rollback_data[] = $rollback_data;
	}
	
	/**
	 * 添加子事务数据
	 * @param array $child_log_data
	 */
	public function tcAddChildTc($child_log_data = [])
	{
		$this->child_tcs[] = $child_log_data;
	}
	
	/**
	 * 提交事务 往阿里消息服务发送订阅
	 * @param $rollback_data 本地事务备份数据
	 * @param $child_log_data 子事务数据和条件
	 */
	public function tcCommit($rollback_data = [], $child_log_data = [])
	{
		$request = app('Illuminate\Http\Request');
		$from_url = $request->url();
	
		$tc_log_data= [
				'service_id' => $this->service_id,
				'from_url' => $from_url,
				'notify' => $this->notify,
				'step' => self::STEP_START
		];
		
		$tcLibServer = RpcHelper::load('TcCenter', 'TcLib');
		$tc_server_id = $tcLibServer->addTcLog($tc_log_data);
		
		if (!$tc_server_id) {
			//创建事务失败 回滚本地事务
			$this->tcRollback();
			return false;
		}
		$this->tc_id = $tc_server_id;
		
		$this->child_tcs = array_merge($child_log_data, $this->child_tcs);
		$this->rollback_data = array_merge($rollback_data, $this->rollback_data);
		
		$field = [
				'member_id' => isset ( $this->member_info ['member_id'] ) ? $this->member_info ['member_id'] : 0,
				'company_id' => isset ( $this->member_info ['company_id'] ) ? $this->member_info ['company_id'] : 0 ,
				'create_time' => time(),
				'transaction_id' => $this->tc_id,
				'service_id' => $this->service_id,
				'rolleback_data' => json_encode($rollback_data),
				'child_log_data' => json_encode($child_log_data)
		];
		App::bindIf('GouuseCore\Models\TcBaseModel', null, true);
		$this->TcBaseModel= App::make('GouuseCore\Models\TcBaseModel');
		
		$tc_id = $this->TcBaseModel->add($field);
		if (!$tc_id) {
			//回滚
			$this->tcRollback();
			return false;
		}
		
		DB::commit();
		return true;
	}
	
	/**
	 * 确认事务提交 该方法一定要放在本地事务提交后执行，(一定要执行！！否则事务不会最终提交)
	 */
	public function tcConfirm()
	{
		$push_message= [
				'service_id' => $this->service_id,
				'step' => self::STEP_CONFIRM,
				'trunsaction_id' => $this->tc_id
		];
		
		$msg_data = ['topic_name' => $this->topic_name, 'message_body' => json_encode($push_message)];
		$re_push_data = $this->MqLib->sendTopic($msg_data);
		if ($re_push_data[0] != 0) {
			//消息发送失败
			return $re_push_data;
		}
	}	
}
