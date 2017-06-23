<?php
namespace GouuseCore\Rpcs;

use GouuseCore\Rpcs\BaseRpc;

/**
 * 用户中心SDK
 * @author zhangyubo
 *
 */
class CompanyCenterRpc extends BaseRpc
{
	protected $host;
	
	function __construct() {
		$this->host = '';
		
	}
	
	function getById($company_id) {
		$url = $this->host . '/company_center/v3/info';
		$token = explode(' ', $token);
		$token = end($token);
		$header[] = 'Authorization: bearer '.$token;
		$result = $this->postOutside($url, $header, ['company_id' => $company_id]);
		return $result;
	}
	

}