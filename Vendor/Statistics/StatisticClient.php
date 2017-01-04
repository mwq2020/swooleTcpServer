<?php

namespace Statistics;
/**
 * 统计客户端
 * @author workerman
 * @author xmc 2015.06.06
 */
class StatisticClient
{
	/**
	 * [module=>[interface=>time_start, interface=>time_start .
	 * ..], module=>[interface=>time_start ..], ... ]
	 * @var array
	 */
	protected static $timeMap = array();
	
	protected static $client;

	/**
	 * 模块接口上报消耗时间记时
	 * @param string $module        	
	 * @param string $interface        	
	 * @return void
	 */
	public static function tick($module = '', $interface = '')
	{
		return self::$timeMap[$module][$interface] = microtime(true);
	}


	/**
	 * service上报统计数据（在服务器端上报服务的数据，用来统计接口的数据）
	 * @param $project_name   // 项目名称
	 * @param $class_name     // 调用的类名
	 * @param $function_name  // 调用的函数名
	 * @param $args          // 传参
	 * @param $cost_time      // service执行花费的时间
	 * @param bool|true $is_success  //是否成功
	 * @param int $code       // 服务的状态码（错误的状态码统计使用）
	 * @param string $msg     // 报错服务堆栈信息（方便根据错误信息调试代码）
	 * @return bool
	 */
	public static function serviceApiReport($project_name, $class_name, $function_name, $args,$cost_time, $is_success=true, $code=200, $msg='')
	{
		$report_address = '127.0.0.1:55656';

		//加密数据
		$data = array(
			'project_name' => $project_name,
			'class_name' => $class_name,
			'function_name' => $function_name,
			'args' => $args,
			'cost_time' => $cost_time,
			'is_success' => $is_success,
			'code' => $code,
			'msg' => $msg,
		);
		$bin_data = Protocol::apiEncode($data);
		if (extension_loaded('swoole')) {
			if (!self::$client || (self::$client && !self::$client->isConnected())) {
				self::$client = new \swoole_client(SWOOLE_TCP | SWOOLE_KEEP, SWOOLE_SOCK_SYNC);
				list($ip, $port) = explode(':', $report_address);
				self::$client->connect($ip, $port);
			}
			self::$client->send($bin_data);
			self::$client->close();
			self::$client = null;
		} else {
			return self::sendData($report_address, $bin_data);
		}
	}


	/**
	 * 上报http的统计结果（暂时不启用，用来统计前端页面的数据）
	 * @param $url  当前页面的url
	 * @param $cost_time  页面逻辑耗费的时间
	 * @param bool|true $is_success 页面服务是否成功
	 * @param int $http_code  http的服务状态码
	 * @param string $msg 报错详情（有报错信息时，上传报错信息堆栈）
	 * @return bool
	 */
	public static function httpReport($url, $cost_time, $is_success=true, $http_code=200, $msg='')
	{
		$report_address = '127.0.0.1:55656';
		$bin_data = Protocol::encode($url, $cost_time, $is_success, $cost_time,$http_code, $msg);
		if (extension_loaded('swoole')) {
			if (!self::$client || (self::$client && !self::$client->isConnected())) {
				self::$client = new \swoole_client(SWOOLE_TCP | SWOOLE_KEEP, SWOOLE_SOCK_SYNC);
				list($ip, $port) = explode(':', $report_address);
				self::$client->connect($ip, $port);
			}
			self::$client->send($bin_data);
			self::$client->close();
			self::$client = null;
		} else {
			return self::sendData($report_address, $bin_data);
		}
	}


	/**
	 * 上报统计数据
	 * 
	 * @param string $module        	
	 * @param string $interface        	
	 * @param bool $success        	
	 * @param int $code        	
	 * @param string $msg        	
	 * @param string $report_address        	
	 * @return boolean
	 */
	public static function report($module, $interface, $success, $code, $msg, $report_address = '',$process_time=0)
	{
		$report_address = $report_address ? $report_address : '127.0.0.1:55656';
		if (isset(self::$timeMap[$module][$interface]) && self::$timeMap[$module][$interface] > 0) {
			$time_start = self::$timeMap[$module][$interface];
			self::$timeMap[$module][$interface] = 0;
		} else 
			if (isset(self::$timeMap['']['']) && self::$timeMap[''][''] > 0) {
				$time_start = self::$timeMap[''][''];
				self::$timeMap[''][''] = 0;
			} else {
				$time_start = microtime(true);
			}
		
		$cost_time = microtime(true) - $time_start;
		$bin_data = Protocol::encode($module, $interface, $cost_time, $success, $code, $msg);
		if (extension_loaded('swoole')) {
		    if (!self::$client || (self::$client && !self::$client->isConnected())) {
    		    self::$client = new \swoole_client(SWOOLE_TCP | SWOOLE_KEEP, SWOOLE_SOCK_SYNC);
    		    list($ip, $port) = explode(':', $report_address);
    		    self::$client->connect($ip, $port);
		    }
	        self::$client->send($bin_data);
	        self::$client->close();
	        self::$client = null;
		} else {
    		return self::sendData($report_address, $bin_data);
		}
	}

	/**
	 * 发送数据给统计系统
	 * @param string $address        	
	 * @param string $buffer        	
	 * @return boolean
	 */
	public static function sendData($address, $buffer, $timeout = 10)
	{
		$socket = stream_socket_client('tcp://'.$address, $errno, $errmsg, $timeout);
		if (! $socket) {
			return false;
		}
		stream_set_timeout($socket, $timeout);
		return stream_socket_sendto($socket, $buffer) == strlen($buffer);
	}
}

/**
 * 协议swoole
 * @author xmc
 */
class Protocol
{
	/**
	 * 编码
	 * @param string $module        	
	 * @param string $interface        	
	 * @param float $cost_time        	
	 * @param int $success        	
	 * @param int $code        	
	 * @param string $msg        	
	 * @return string
	 */
	public static function encode($module, $interface, $cost_time, $success, $code = 0, $msg = '')
	{
		$data = array(
			'module' => $module,
			'interface' => $interface,
			'cost_time' => $cost_time,
			'success' => $success,
			'time' => time(),
			'code' => $code,
			'msg' => $msg
		);
		$string = json_encode($data);
		$packData = pack('N', strlen($string)).$string;
 		//echo strlen($string).$string.PHP_EOL;//log
		return $packData;
	}


	/**
	 * api参数加密
	 * @param $data
	 * @return string
	 */
	public static function apiEncode($data)
	{
		$data = empty($data) ? array() : $data;
		$string = json_encode($data);
		$packData = pack('N', strlen($string)).$string;
		//echo strlen($string).$string.PHP_EOL;//log
		return $packData;
	}
	
	/**
	 * 解码
	 */
	public static function decode($buffer)
	{
		$length = unpack('N', $buffer)[1];
		$string = substr($buffer, -$length);
		$data = json_decode($string, true);
		return $data;
	}
}
