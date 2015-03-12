<?php
/**
 * HTTP 常用操作
 *
 * @package default
 * @author Heng Min Zhan
 */
class Http
{
	/**
	 * SOCKET 请求
	 *
	 * @return void
	 * @author Heng Min Zhan
	 */
	public static function socketRequest($url, $params, $wait_result = true, $connect_timeout = 3)
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
		if(isset($parts['port'])){
			$port =	$parts['port'];
		}else{
			$port = 80;
		}
	    $fp = fsockopen($parts['host'], $port, $errno, $errstr, $connect_timeout);
	    if(!$fp)
	    {
			throw new Exception($errstr, $errno);
	    }
	    $out = "$method {$parts['path']} HTTP/1.1\r\n";
	    $out.= "Host: {$parts['host']}\r\n";
	    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
	    $out.= "Connection: Close\r\n\r\n";
		if (isset($post_string)) {
			$out.= $post_string;
			$out.= "Content-Length: ".strlen($post_string)."\r\n";
		}
	    fwrite($fp, $out);
		$result = '';
		if($wait_result){
			while (!feof($fp)) {
				    $result .= fgets($fp, 1024);
			}
		}
		fclose($fp);
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
