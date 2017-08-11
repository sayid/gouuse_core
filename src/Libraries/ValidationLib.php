<?php

namespace GouuseCore\Libraries;


/**
 * 字符串格式判断类
 */
class ValidationLib
{

    /**
     * [checkMobile 判断是否为手机]
     *
     * @param [string] $str            
     * @return [bool] [是否为手机]
     */
    function checkMobile($str)
    {
        return (bool) preg_match("/^1[3|4|5|6|7|8|5|9]\d{9}$|^[2|5|6|8|9]\d{7}$/is", $str);
    }

    function checkTel($str)
    {
        return (bool) preg_match("/^(0[0-9]{2,3}-)?([2-9][0-9]{6,7})+(-[0-9]{1,4})?$/", $str);
    }

    /**
     * [checkZip 检查邮编]
     *
     * @param [string] $str            
     * @return [bool]
     */
    function checkZip($str)
    {
        return (bool) preg_match("/^[1-9]\d{5}$/", $str);
    }

    function checkEmail($str)
    {
        return (bool) preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/is", $str);
    }

    function checkWebUrl($str)
    {
        return preg_match('/^http[s]?:\/\/' . '(([0-9]{1,3}\.){3}[0-9]{1,3}' . // IP形式的URL- 199.194.52.184
'|' . // 允许IP和DOMAIN（域名）
'([0-9a-z_!~*\'()-]+\.)*' . // 三级域验证- www.
'([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.' . // 二级域验证
'[a-z]{2,6})' . // 顶级域验证.com or .museum
'(:[0-9]{1,4})?' . // 端口- :80
'((\/\?)|' . // 如果含有文件对文件部分进行校验
'(\/[0-9a-zA-Z_!~\*\'\(\)\.;\?:@&=\+\$,%#-\/]*)?)$/', $str) == 1;
    }

    function checkDate($str)
    {
        return preg_match("/^(19|20)\d{2}-(0?\d|1[012])-(0?\d|[12]\d|3[01])$/", $str);
    }

    function checkDateTime($str)
    {
        return preg_match("/^(19|20)\d{2}-(0?\d|1[012])-(0?\d|[12]\d|3[01]) [0-9]{2}:[0-9]{2}$/", $str);
    }

    function checkTime($str)
    {
        return preg_match("/^[0-2][0-9]:[0-5][0-9]$/", $str);
    }

    function checkMoney($str)
    {
        return preg_match("/^[0-9]+(\.[0-9]{2}){0,1}$/is", $str);
    }

    function checkYear($str)
    {
        return preg_match("/^[1-9][0-9]{3}$/is", $str);
    }

    function checkMonth($str)
    {
        $str = $str * 1;
        return ($str >= 1 && $str <= 12) ? true : false;
    }

    function checkDomain($str)
    {
        return preg_match('/^[0-9a-z_\-][0-9a-z_\-]*\.([0-9a-z_\-]*\.)?[a-z]{2,6}$/', // 顶级域验证.com or .museum
$str) == 1;
    }

    function checkIp($str)
    {
        return filter_var($str, FILTER_VALIDATE_IP) === false ? false : true;
    }
    
    /**
     * 将error替换为自定义code
     * @param array $errors
     * @param array $messages
     * @return unknown
     */
    public function getErrorCode($errors = [], $messages = [])
    {
    	$error = current(current($errors));
    	$key = str_replace('validation.',key($errors).'.', $error);
    	
    	return isset($messages[$key]) ? $messages[$key] : $error;
    }
}