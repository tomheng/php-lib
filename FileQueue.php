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
	public static function push($data, $type)
	{
		$file_path = self::getFile($type);
		if(is_array($data))
		{
			$data = implode('||', $data);
		}	
		//$old_data = file_get_contents($file_path);
		$re = file_put_contents($file_path, $data.PHP_EOL, LOCK_EX|FILE_APPEND);
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

}
