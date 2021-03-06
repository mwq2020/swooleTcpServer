<?php

namespace Statistics;
/**
 * 统计客户端
 * @author workerman
 * @author xmc 2015.06.06
 */
class StatisticClient
{
	protected static $client;//统计客户端
	protected static $config;//统计服务地址配置

	public static function config($config='')
	{
		if(!empty($config)){
			self::$config = $config;
		} else {
			$config = (array) new \Config\Statistics();
			if(empty($config) || empty($config['default']['uri'])){
				throw new \Exception('统计服务地址未配置！');
			}
			self::$config = $config['default']['uri'];
		}
	}

	/**
	 * service上报统计数据（在服务器端上报服务的数据，用来统计接口的数据）
	 * @param $user                // 使用者
	 * @param $project_name        // 项目名称
	 * @param $class_name          // 调用的类名
	 * @param $function_name       // 调用的函数名
	 * @param $args                // 传参
	 * @param $cost_time           // service执行花费的时间
	 * @param bool|true $is_success  //是否成功
	 * @param int $code            // 服务的状态码（错误的状态码统计使用）
	 * @param string $msg          // 报错服务堆栈信息（方便根据错误信息调试代码）
	 * @return bool
	 */
	public static function serviceApiReport($user,$project_name, $class_name, $function_name, $args,$cost_time, $is_success=true, $code=200, $msg='',$request_ip=0,$service_ip=0)
	{
		if(empty(self::$config)){
			self::config();
		}
		$report_address = self::$config;

		//加密数据
		$data = array(
			'user' => $user,
			'project_name' => $project_name,
			'class_name' => $class_name,
			'function_name' => $function_name,
			'args' => $args,
			'cost_time' => $cost_time,
			'is_success' => $is_success,
			'code' => $code,
			'msg' => $msg,
			'request_ip' => $request_ip,
			'service_ip' => $service_ip
		);
		$bin_data = Protocol::apiEncode($data);
		if (extension_loaded('swoole')) {
			if (is_null(self::$client) || empty(self::$client) || !is_object(self::$client)) {
				if(PHP_SAPI == 'cli'){
					self::$client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
				} else {
					self::$client = new \swoole_client(SWOOLE_SOCK_TCP | SWOOLE_KEEP, SWOOLE_SOCK_SYNC);
				}
				list($ip, $port) = explode(':', $report_address);
				self::$client->connect($ip, $port);
			} else {
				if(!self::$client->isConnected()){
					if(PHP_SAPI == 'cli'){
						self::$client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
					} else {
						self::$client = new \swoole_client(SWOOLE_SOCK_TCP | SWOOLE_KEEP, SWOOLE_SOCK_SYNC);
					}
					list($ip, $port) = explode(':', $report_address);
					self::$client->connect($ip, $port);
				}
			}
			self::$client->send($bin_data);
			self::$client->close();
			if(PHP_SAPI == 'cli'){
				self::$client = null;
			}
		} else {
			return self::sendData($report_address, $bin_data);
		}
		return true;
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
