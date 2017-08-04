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
	
	public function info($msg, $log_data = [], $more_info = false)
	{
		if ($more_info) {
			$log_data['remoteAddress'] = $this->getIp();//请求客户端地址
			$log_data['localAddress'] = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ?
			$_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ?
					$_SERVER['HTTP_HOST'] : '');//服务器地址
			$log_data['serviceID'] = env('SERVICE_ID');//服务id
			$log_data['startTime'] = TIME_START;//开始时间
			$log_data['endTime'] = $this->logMicrotimeFloat();//结束时间
		}
		$this->file_driver->info($msg, $log_data);
	}
	
	public function debug($msg, $log_data= [], $more_info = false)
	{
		if ($more_info) {
			$log_data['remoteAddress'] = $this->getIp();//请求客户端地址
			$log_data['localAddress'] = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ?
			$_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ?
					$_SERVER['HTTP_HOST'] : '');//服务器地址
			$log_data['serviceID'] = env('SERVICE_ID');//服务id
			$log_data['startTime'] = TIME_START;//开始时间
			$log_data['endTime'] = $this->logMicrotimeFloat();//结束时间
		}
		$this->file_driver->debug($msg, $log_data);
	}
	
	public function error($msg, $log_data= [], $more_info = false)
	{
		if ($more_info) {
			$log_data['remoteAddress'] = $this->getIp();//请求客户端地址
			$log_data['localAddress'] = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ?
			$_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ?
					$_SERVER['HTTP_HOST'] : '');//服务器地址
			$log_data['serviceID'] = env('SERVICE_ID');//服务id
			$log_data['startTime'] = TIME_START;//开始时间
			$log_data['endTime'] = $this->logMicrotimeFloat();//结束时间
		}
		$this->file_driver->error($msg, $log_data);
	}
	
	public function warning($msg, $log_data= [], $more_info = false)
	{
		if ($more_info) {
			$log_data['remoteAddress'] = $this->getIp();//请求客户端地址
			$log_data['localAddress'] = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ?
			$_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ?
					$_SERVER['HTTP_HOST'] : '');//服务器地址
			$log_data['serviceID'] = env('SERVICE_ID');//服务id
			$log_data['startTime'] = TIME_START;//开始时间
			$log_data['endTime'] = $this->logMicrotimeFloat();//结束时间
		}
		$this->file_driver->warning($msg, $log_data);
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
