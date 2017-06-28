<?php
namespace GouuseCore\Helpers;

class StringHelper
{
	/**
	 * 校验字符串是否json格式
	 * @param unknown $string
	 * @return boolean
	 */
	function isJson($string)
	{
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}
	
}
