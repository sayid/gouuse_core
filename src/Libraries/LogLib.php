<?php
/**
 * 统一日志处理
 * @author  李洪林
 * @version 2017-5-18
 */
namespace GouuseCore\Libraries;

use GouuseCore\Helpers\FormHelper;
use GouuseCore\Libraries\Lib;
use Illuminate\Support\Facades\Log;

class LogLib extends Lib
{
	private $file_drivers = [];
	
	private $file_driver;
	
	public function __construct()
	{
		parent::__construct();
		$this->setDriver('log');
	}
	
	/**
	 * 切换日志文件
	 * @param unknown $file_name
	 */
	public function setDriver($file_name)
	{
		if (!isset($this->file_drivers[$file_name])) {
			$monolog = new \Monolog\Logger($file_name);
			$monolog->pushHandler(
					new \Monolog\Handler\RotatingFileHandler(ROOT_PATH.'/storage/logs/'.$file_name.'.log', 0, \Monolog\Logger::DEBUG, true, 0777)
					);
			$this->file_drivers[$file_name] = & $monolog;
		} else {
			$monolog = $this->file_drivers[$file_name];
		}
		$this->file_driver = $monolog;
	}
	
	public function info($msg, $log_data = [], $more_info = false) {
		if ($more_info) {
			$log_data['time'] = date('Y-m-d H:i:s');
			$log_data['remoteAddress'] = $this->getIp();//请求客户端地址
			$log_data['localAddress'] = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ?
			$_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ?
					$_SERVER['HTTP_HOST'] : '');//服务器地址
					$log_data['uri'] = @$_SERVER["REQUEST_URI"];//调用方法或接口
					$log_data['serviceID'] = env('SERVICE_ID');//服务id
					$log_data['startTime'] = TIME_START;//开始时间
					$log_data['endTime'] = $this->logMicrotimeFloat();//结束时间
		}
		$this->file_driver->info($msg, $log_data);
	}
	
	public function debug($msg, $log_data= [], $more_info = false) {
		if ($more_info) {
			$log_data['time'] = date('Y-m-d H:i:s');
			$log_data['remoteAddress'] = $this->getIp();//请求客户端地址
			$log_data['localAddress'] = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ?
			$_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ?
					$_SERVER['HTTP_HOST'] : '');//服务器地址
					$log_data['uri'] = @$_SERVER["REQUEST_URI"];//调用方法或接口
					$log_data['serviceID'] = env('SERVICE_ID');//服务id
					$log_data['startTime'] = TIME_START;//开始时间
					$log_data['endTime'] = $this->logMicrotimeFloat();//结束时间
		}
		$this->file_driver->debug($msg, $log_data);
	}
	
	public function error($msg, $log_data= [], $more_info = false) {
		if ($more_info) {
			$log_data['time'] = date('Y-m-d H:i:s');
			$log_data['remoteAddress'] = $this->getIp();//请求客户端地址
			$log_data['localAddress'] = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ?
			$_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ?
					$_SERVER['HTTP_HOST'] : '');//服务器地址
					$log_data['uri'] = @$_SERVER["REQUEST_URI"];//调用方法或接口
					$log_data['serviceID'] = env('SERVICE_ID');//服务id
					$log_data['startTime'] = TIME_START;//开始时间
					$log_data['endTime'] = $this->logMicrotimeFloat();//结束时间
		}
		$this->file_driver->error($msg, $log_data);
	}
	
	public function warning($msg, $log_data= [], $more_info = false) {
		if ($more_info) {
			$log_data['time'] = date('Y-m-d H:i:s');
			$log_data['remoteAddress'] = $this->getIp();//请求客户端地址
			$log_data['localAddress'] = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ?
			$_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ?
					$_SERVER['HTTP_HOST'] : '');//服务器地址
					$log_data['uri'] = @$_SERVER["REQUEST_URI"];//调用方法或接口
					$log_data['serviceID'] = env('SERVICE_ID');//服务id
					$log_data['startTime'] = TIME_START;//开始时间
					$log_data['endTime'] = $this->logMicrotimeFloat();//结束时间
		}
		$this->file_driver->warning($msg, $log_data);
	}
	
	/**
	 * 写入日志
	 * @param array $log_data 日志数据
	 * @param string $file_name 日志文件名
	 * @return array
	 */
	public function log_info($log_data, $file_name = 'log')
	{
		if (!defined('TIME_START'))
			define('TIME_START', $this->logMicrotimeFloat());
			$param = FormHelper::__getData($log_data, 'param');
			$result = FormHelper::__getData($log_data, 'result');
			$log_data = array();
			$log_data['time'] = date('Y-m-d H:i:s');
			$log_data['remoteAddress'] = $this->getIp();//请求客户端地址
			$log_data['localAddress'] = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ?
			$_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ?
					$_SERVER['HTTP_HOST'] : '');//服务器地址
					$log_data['uri'] = @$_SERVER["REQUEST_URI"];//调用方法或接口
					$log_data['param'] = $param;//提交参数
					$log_data['result'] = $result;//返回数据
					$log_data['success'] = true;//处理结果
					$log_data['serviceID'] = env('SERVICE_ID','1000');//服务id
					$log_data['startTime'] = TIME_START;//开始时间
					$log_data['endTime'] = $this->logMicrotimeFloat();//结束时间
					$log_data['sqlNumber'] = 1;//sql查询次数
					file_put_contents(storage_path().'/logs/'.$file_name.'-'.date('Y-m-d').'.txt',
							json_encode($log_data)."\r\n",
							FILE_APPEND);
					return $log_data;
	}
	
	/**
	 * 写入错误日志
	 * @param array $log_data 日志数据
	 * @param string $file_name 日志文件名
	 * @return array
	 */
	public function log_error($log_data, $file_name = 'err')
	{
		if (!defined('TIME_START'))
			define('TIME_START', $this->logMicrotimeFloat());
			$param = FormHelper::__getData($log_data, 'param');
			$result = FormHelper::__getData($log_data, 'result');
			$log_data = array();
			$log_data['time'] = date('Y-m-d H:i:s');
			$log_data['remoteAddress'] = $this->getIp();//请求客户端地址
			$log_data['localAddress'] = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ?
			$_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ?
					$_SERVER['HTTP_HOST'] : '');//服务器地址
					$log_data['uri'] = @$_SERVER["REQUEST_URI"];//调用方法或接口
					$log_data['param'] = $param;//提交参数
					$log_data['result'] = $result;//返回数据
					$log_data['success'] = false;//处理结果
					$log_data['serviceID'] = env('SERVICE_ID', '1000');//服务id
					$log_data['startTime'] = TIME_START;//开始时间
					$log_data['endTime'] = $this->logMicrotimeFloat();//结束时间
					$log_data['sqlNumber'] = 1;//sql查询次数
					file_put_contents(storage_path().'/logs/'.$file_name.'-'.date('Y-m-d').'.txt',
							json_encode($log_data)."\r\n",
							FILE_APPEND);
					return $log_data;
	}
	
	/**
	 * 获取ip地址
	 * @return string
	 */
	public function getIp()
	{
		$onlineip = '';
		if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$onlineip = getenv('HTTP_CLIENT_IP');
		} elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$onlineip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'),'unknown')) {
			$onlineip = getenv('REMOTE_ADDR');
		} elseif (isset($_SERVER['REMOTE_ADDR']) &&
				$_SERVER['REMOTE_ADDR'] &&
				strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
					$onlineip = $_SERVER['REMOTE_ADDR'];
		}
		return $onlineip;
	}
	
	public function logMicrotimeFloat()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
}
