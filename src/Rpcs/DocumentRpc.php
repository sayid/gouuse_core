<?php
namespace GouuseCore\Rpcs;

use GouuseCore\Rpcs\BaseRpc;

/**
 * 用户中心SDK
 * @author zhangyubo
 *
 */
class DocumentRpc extends BaseRpc
{
	protected $host;

	function __construct() {
		$this->host = '';

	}


	/**
	 * 按文件id查询文件信息
	 * @param int $file_id
	 * @return \GouuseCore\Rpcs\number[]|\GouuseCore\Rpcs\string[]|mixed
	 */
	function getFileInfo(int $file_id) {
		 
		$url = '/file_service/v3/file/info';
		$result = $this->post($url, [], [ 'file_id' => $file_id]);
		return $result;
	}

	
	/**
	 * 注册员工
	 * @param unknown $member_info
	 * @return \GouuseCore\Rpcs\number[]|\GouuseCore\Rpcs\string[]|mixed
	
	function register($member_info) {
			
		$url = $this->host . '/user_center/v3/member_add_do';
		$result = $this->post($url, [], $member_info);
		return $result;
	} */
}