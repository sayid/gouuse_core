<?php

namespace GouuseCore\Libraries;
use Ixudra\Curl\Facades\Curl;

class GatewayLib
{
	
	public function get_upstream($uri)
	{
		$host = env('API_GATEWAY_ADMIN_HOST');
		$host = $host . '/apis/';
		$result = Curl::to($host)
		//->withData($data)
		->get();
		$data = json_decode($result, true);
		if ($data['total'] > 0) {
			foreach ($data['data'] as $row) {
				if (in_array($uri, $row['uris'])) {
					return $row['upstream_url'];
				}
				foreach ($row['uris'] as $uri_api) {
					if (strpos($uri, $uri_api)===0) {
						return  $row['upstream_url'];
					}
				}
			}
		}
		return false;
	}
	
	public function get_targets_by_upstream($upstream) 
	{
	    $upstream = str_replace(array('http://', 'https://'), '', $upstream);
	    $host = env('API_GATEWAY_ADMIN_HOST');
	    $host = $host . '/upstreams/'.$upstream.'/targets/active';
	    $result = Curl::to($host)
	    ->get();
	    $data = json_decode($result, true);
	    if ($data['total'] > 0) {
	        if ($data['total'] > 1) {
	           return 'http://'.$data['data'][mt_rand(0, $data['total'])]['target'];
	        } else {
	           return 'http://'.$data['data'][0]['target'];
	        }
	    }
	    return false;
	}
	
	public function getHost($uri)
	{	//return env('API_GATEWAY_HOST');
	    $upstream = $this->get_upstream($uri);
	    //解析dns记录
	    $dns = @dns_get_record(str_replace(['http://', 'https://'], '', $upstream), DNS_SRV);
	    if (!empty($dns[0])) {
	    	return 'http://'.$dns[0]['target'].':'.$dns[0]['port'];
	    } else {
	    	throw new Exception('微服务dns获取失败');
	    }
	    
	    return $upstream;
	    if ($upstream) {
	        $host = $this->get_targets_by_upstream($upstream);
	        return $host;
	    }	    
	}
}
