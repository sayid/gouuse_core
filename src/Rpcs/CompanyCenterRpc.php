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
	
	function __construct()
	{
		$this->host = '';
		
	}
	
	function getById($company_id = 0)
	{
		$url = $this->host . '/company_center/v3/view';
		$result = $this->post($url, [], ['company_id' => $company_id]);
		return $result;
	}
	
	/**
	 * 添加公司默认职位
	 * @param integer $company_id
	 */
	public function addCompanyDefaultPosition($company_id)
	{
	    $url = $this->host . '/company_center/v3/auto_add_positions';
	    $result = $this->post($url, [], ['company_id' => $company_id]);
	    return $result;
	}
	

}