<?php
/**
 *
 * 文件模拟队列
 *
 * @author zhm20070928@gmail.com
 * @date 2014-10-14
 *
 */
class FileQueue
{
	//存储路径
	private static $file_path = '/var/data/www';

	//加入队列
	public static function push($data, $type, $repeatable = false)
	{
		$file_path = self::getFile($type);
		if(is_array($data))
		{
			$data = implode('||', $data);
		}	
		$mcd = new MyMemcached();
		$key = 'filequeue_key_'.md5($data);
		if(!$repeatable && $mcd->get($key))
		{
			return true;
		}
		//$old_data = file_get_contents($file_path);
		$re = file_put_contents($file_path, $data.PHP_EOL, LOCK_EX|FILE_APPEND);
		$mcd->set($key, true, 60);
		return $re;
	}	

	//获取文件
	private static function getFile($type)
	{
		$type = trim($type);
		$type = preg_replace('/[^\w\d]/', '', $type);	
		$file_path = self::$file_path."/$type";
		return $file_path;
	}

	//取队列
	public static function pop($type, $num = 1, $unique = true)
	{
		$file_path = self::getFile($type);
		if(!file_exists($file_path)){
			throw new Exception("file not exits $file_path");
		}	
		$fh = fopen($file_path, 'r+');
		if(!$fh || !flock($fh, LOCK_EX | LOCK_NB))
		{
			throw new Exception("can not lock file: $file_path");
		}
		$result = array();
		$left = '';
		while($line = fgets($fh))
		{
			if($num)
			{
				$result[] = $line;
				$num--;
			}	
			else
			{
				$left .= $line;	
			}
		}
		rewind($fh);
		ftruncate($fh, 0);
		fwrite($fh, $left);
		flock($fh, LOCK_UN);
		fclose($fh);
		return $unique ? array_unique($result) : $result;
	}	

	//获取文件的最后一行
	private static function getLastLine($file_name, $truncate = true)
	{
		$line = '';

		$f = fopen($file_name, 'r');
		if(!flock($f, LOCK_EX|LOCK_NB))
		{
			throw new Exception("文件($file_name)正在被占用");
		}
		$cursor = -1;

		fseek($f, $cursor, SEEK_END);
		$char = fgetc($f);

		/**
		 * Trim trailing newline chars of the file
		 */
		while ($char === "\n" || $char === "\r") {
			fseek($f, $cursor--, SEEK_END);
			$char = fgetc($f);
		}

		/**
		 * Read until the start of file or first newline char
		 */
		while ($char !== false && $char !== "\n" && $char !== "\r") {
			/**
			 * Prepend the new char
			 */
			$line = $char . $line;
			fseek($f, $cursor--, SEEK_END);
			$char = fgetc($f);
		}
		$truncate && ftruncate($f, filesize($file_name) - strlen($line));
		flock($f, LOCK_UN);
		fclose($f);
		return $line;	
	}
}
