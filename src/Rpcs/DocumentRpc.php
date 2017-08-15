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

	function __construct() {

	}


	/**
	 * 按文件id查询文件信息
	 * @param int $file_id
	 * @return \GouuseCore\Rpcs\number[]|\GouuseCore\Rpcs\string[]|mixed
	 */
	function getFileInfo($cond = [])
	{
		 
		$url = '/file_service/v3/file/info';
		$result = $this->post($url, [], $cond);
		return $result;
	}

	/**
	 * 删除文件
	 * @param int $file_id
	 * @return \GouuseCore\Rpcs\number[]|\GouuseCore\Rpcs\string[]|mixed
	 */
	function delFile($file_id = 0)
	{
		
		$url = '/file_service/v3/del_file_do';
		$result = $this->post($url, [], ['file_id' => $file_id]);
		return $result;
	}
	
}