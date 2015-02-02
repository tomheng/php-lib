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
	 * 异步POST请求
	 *
	 * @return void
	 * @author Heng Min Zhan
	 */
	public static function ($url, $params)
	{
	    foreach ($params as $key => &$val) {
	      if (is_array($val)) $val = implode(',', $val);
	        $post_params[] = $key.'='.urlencode($val);
	    }
	    $post_string = implode('&', $post_params);

	    $parts=parse_url($url);

	    $fp = fsockopen($parts['host'],
	        isset($parts['port'])?$parts['port']:80,
	        $errno, $errstr, 3);
	    if(!$fp)
	    {
	    	return false;
	    }
	    $out = "POST ".$parts['path']." HTTP/1.1\r\n";
	    $out.= "Host: ".$parts['host']."\r\n";
	    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
	    $out.= "Content-Length: ".strlen($post_string)."\r\n";
	    $out.= "Connection: Close\r\n\r\n";
	    if (isset($post_string)) $out.= $post_string;

	    fwrite($fp, $out);
	    fclose($fp);
	}
	
} // END class 