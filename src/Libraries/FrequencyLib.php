<?php
namespace GouuseCore\Libraries;

/**
 * 访问频率限制
 * @author zhangyubo
 *
 */
class FrequencyLib extends Lib
{
	/**
	 * 
	 * @param unknown $url 频率限制的URL
	 * @param unknown $key 限制的key值
	 * @param number $life_time 有效时间
	 */
    public function set($url, $key, $life_time = 600)
    {
    	$value = $this->CacheLib->get(md5($url.'_'.$key));
    	$value = intval($value) + 1;
    	$this->CacheLib->set(md5($url.'_'.$key), $value, $life_time);
    }
    
    /**
     * 检测频率，生命周期内只能请求多少次
     * @param unknown $url
     * @param unknown $key
     * @param number $times
     * @return boolean
     */
    public function check($url, $key, $times = 5)
    {
    	$value = $this->CacheLib->get(md5($url.'_'.$key));
    	$value = intval($value) + 1;
    	if ($value > $times) {
    		return false;
    	}
    	return true;
    }
    
    
}
