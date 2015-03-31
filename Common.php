<?php
/**
 * 常用函数
 *
 * @package default
 * @author Heng Min Zhan
 */
class  Common
{

	//验证码生成
	static public function showCaptcha($width = 100,  $height = 30,  $len){
		$image = imagecreatetruecolor($width, $height);//创建一个宽100，高度30的图片
		$bgcolor = imagecolorallocate($image, 255, 255, 255);//图片背景是白色
		imagefill($image, 0, 0, $bgcolor);//图片填充白色
		//随机数据，下面的例子是随机数据，包括字母和数字
		$captch_code = '';
		$font  = 5;
		$data = '23456789abcdefghijkmnpqrstuvwxyz';
		$data_len = strlen($data);
		for($i = 0; $i < $len; $i++){
			$fontcolor = imagecolorallocate($image, rand(0, 120), rand(0, 120), rand(0, 120));
			$fontcontent = substr($data, rand(0, $data_len - 1), 1);
			$captch_code .= $fontcontent;
			$x = ($i * $width / $len) + rand(5, 8);
			$y = rand(0, $height/3);
			imagestring($image, $font, $x, $y, $fontcontent, $fontcolor);
		}
		//随机点，生成干扰点
		$pn = ($width * $height) * 0.1;
		for($i = 0; $i < $pn; $i++){
			$pointcolor = imagecolorallocate($image, rand(50, 120), rand(50, 120), rand(50, 120));
			imagesetpixel($image, rand(1, $width), rand(1, $width), $pointcolor);
		}
		//随机线，生成干扰线
		for($i = 0;$i<3;$i++){
			$linecolor = imagecolorallocate($image, rand(80, 220), rand(80, 220), rand(80, 220));
			imageline($image, rand(1, $width), rand(1, $height), rand(1, $width), rand(1, $height), $linecolor);
		}
		header("content-type:image/png");
		imagepng($image);
		return $captch_code;
	}


	/**
	 * 转码函数
	 * @param Mixed $data 需要转码的数组
	 * @param String $dstEncoding 输出编码
	 * @param String $srcEncoding 传入编码
	 * @param bool $toArray 是否将stdObject转为数组输出
	 * @return Mixed
	 */
	static public function convertEncoding($data, $dstEncoding, $srcEncoding, $toArray = false) {
		if ($toArray && is_object($data)) {
			$data = (array)$data;
		}
		if (!is_array($data) && !is_object($data)) {
			$data = mb_convert_encoding($data, $dstEncoding, $srcEncoding);
		} else {
			if (is_array($data)) {
				foreach($data as $key=>$value) {
					if (is_numeric($value)) {
						continue;
					}
					$keyDstEncoding = self::convertEncoding($key, $dstEncoding, $srcEncoding, $toArray);
					$valueDstEncoding = self::convertEncoding($value, $dstEncoding, $srcEncoding, $toArray);
					unset($data[$key]);
					$data[$keyDstEncoding] = $valueDstEncoding;
				}
			} else if(is_object($data)) {
				$dataVars = get_object_vars($data);
				foreach($dataVars as $key=>$value) {
					if (is_numeric($value)) {
						continue;
					}
					$keyDstEncoding = self::convertEncoding($key, $dstEncoding, $srcEncoding, $toArray);
					$valueDstEncoding = self::convertEncoding($value, $dstEncoding, $srcEncoding, $toArray);
					unset($data->$key);
					$data->$keyDstEncoding = $valueDstEncoding;
				}
			}
		}
		return $data;
	}

} // END class 
