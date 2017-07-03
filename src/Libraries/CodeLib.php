<?php
namespace GouuseCore\Libraries;

class CodeLib
{
	const REQUEST_NOT_FOUND = 404;//访问地址不存在
	const REQUEST_METHOD_ERROR = 405;//请使用接口文档定义的请求方式调用
	const HTTP_ERROR = 500;//服务器内部错误
	
	const AUTH_FAILD = 1005104100;//登录失效，请重新登录
	const AUTH_TIMEOUT = 1005104101;//登录已过期，请重新登录
	const AUTH_REQUIRD = 1005104102;//请登录
	const AUTH_DENY = 1005104103;//无权访问
	
}