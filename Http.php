<?php
/**
 * HTTP 常用操作
 *
 * @package default
 * @author Heng Min Zhan
 */
class Http
{
	public static $UA = "HTTP CLIENT(PHP)";

	/**
	 *
	 * ASYNC REQUEST
	 *
	 */
	public static function asyncRequest($url, $params = array()){
		if(function_exists('exec')){
			if($params){
				$params_string = http_build_query($params, '', '&');
				$params = " -d '$params_string' ";	
			}else{
				$params = '';	
			}
			$curl_cmd = "CURL -s '$url' $params > /dev/null 2>&1 &";
			exec($curl_cmd);
		}
		return self::socketRequest($url, $params, false);	
	}

	/**
	 * SOCKET 请求
	 *
	 * @return void
	 * @author Heng Min Zhan
	 */
	public static function socketRequest($url, $params = array(), $headers = array(), $wait_result = true, $connect_timeout = 3)
	{
		$method = 'GET';
		if($params){
			$method = 'POST';
			if(is_array($params)){
				$post_string = http_build_query($params, '', '&');
			}else{
				$post_string = trim($params);
			}
		}
		$parts=parse_url($url);
		if(!isset($parts['path'])){
			$parts['path'] = '/';
		}
		if(!isset($parts['port'])){
			$parts['port'] = 80;
		}
		$fp = fsockopen($parts['host'], $parts['port'], $errno, $errstr, $connect_timeout);
		if(!$fp)
		{
			throw new Exception($errstr, $errno);
		}
		if(!isset($headers['User-Agent'])){
			$headers['User-Agent'] = self::$UA;
		}
		$out  = "$method {$parts['path']} HTTP/1.1\r\n";
		$out .= "Host: {$parts['host']}\r\n";
		$out .= "Connection: Close\r\n";
		foreach($headers as $key => $val){
			$out .= "{$key}: {$val}\r\n";
		}
		if(function_exists('gzinflate')){
			$out .= "Accept-Encoding: gzip,deflate\r\n";
		}
		$out .= "\r\n";
		if (isset($post_string)) {
			$out .= $post_string;
			$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$out .= "Content-Length: ".strlen($post_string)."\r\n";
		}
		fwrite($fp, $out);
		$headers = array();
		$body = '';
		$http_status = "";
		if($wait_result){
			//read and parse header
			$http_status = trim(fgets($fp, 256));
			while (!feof($fp)) {
				$line = trim(fgets($fp, 256));
				if(empty($line)){
					break;
				}
				list($key, $val) = explode(':', $line, 2);	
				$key = trim($key);
				$val = trim($val);
				$headers[$key] = $val;
			}
			//read body
			//分块传输编码只在HTTP协议1.1版本（HTTP/1.1）中提供
			if(isset($headers['Transfer-Encoding']) && $headers['Transfer-Encoding'] == 'chunked'){
				while (!feof($fp)) {
					$line = fgets($fp, 256);
					if($line && preg_match('/^([0-9a-f]+)/i', $line, $matches)){
						$len = hexdec($matches[1]);
						if($len == 0){
							break;//maybe have some other header
						}
						$body .= fread($fp, $len);
					}
				}
			}else if(isset($headers['Content-Length']) && $len = $headers['Content-Length']){
				$body .= fread($fp, $len);		
			}else{
				while(!feof($fp)){
					$body .= fread($fp, 1024 * 8);
				}	
			}
			if($body && $headers['Content-Encoding'] == 'gzip'){
				$body = gzinflate(substr($body, 10));
			}
		}
		fclose($fp);
		$result = array('header' => $headers, 'body' => $body, 'http_status' => $http_status);
		return $result;
	}

	/**
	 * 
	 * CURL REQUEST
	 *
	 */
	public static function curlRequest($url, $params, $headers = array(), $wait_result = true, $connect_timeout = 3, $max_redirect = 5){
		$ch = curl_init($url);
		if(!is_resource($ch)){
			throw new Exception('curl init failed');
		}
		//bool
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);	
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		//integer
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connect_timeout);
		curl_setopt($ch, CURLOPT_MAXREDIRS, $max_redirect);
		//array
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}	
} 
