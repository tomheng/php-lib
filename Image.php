<?php
/**
*
* 图像处理
* @author zhm20070928@gmail.cn
* @date 2015-11-26
*
*/
class Image {
	
	private $src_path = '';
	private $src_img = null;
	private $width = 0;
	private $height = 0;
	private $type = '';
	
	//初始化
	public function __construct($image_path = ''){
		$image_path = trim($image_path);
		if(!$image_path){
			return;
		}
		$this->src_path = $image_path;
		$info = getimagesize($image_path);
		if(is_array($info)){
			list($this->width, $this->height, $this->type, $attr) = $info;
		}
	}
	
	//获取mine type
	public function getMimeType(){
		return image_type_to_mime_type($this->type);
	}
	
	//
	public function getImageTypeName(){
		switch($this->type){
			case IMAGETYPE_GIF:
				return 'gif';
			break;
			case IMAGETYPE_PNG:
				return 'png';
			break;
			case IMAGETYPE_JPEG:
				return 'jpeg';
			break;
		}
	}
	
	//等比例压缩放大图片
	public function resize($max_w = 100, $max_h = 0, $dst_path = ''){
		if($max_w > 0 && $max_h > 0){
			$f = min($max_w/$this->width, $max_h/$this->height, 1); 
	        $max_w = round($f * $this->width); 
	        $max_h = round($f * $this->height); 
		}elseif($max_w == 0 && $max_h > 0){
			$max_w = round(($max_h/$this->height)*$this->width);
		}elseif($max_h == 0 && $max_w > 0){
			$max_h = round(($max_w/$this->width)*$this->height);
		}else{
			throw new Exception("宽和高不能同时为零");
		}
		if(function_exists('imagecreatetruecolor'))
		{
			$create	= 'imagecreatetruecolor';
			$copy	= 'imagecopyresampled';
		}
		else
		{
			$create	= 'imagecreate';
			$copy	= 'imagecopyresized';
		}
		
		$dst_img = $create($max_w, $max_h);
		
		if ($this->type === IMAGETYPE_PNG) // png we can actually preserve transparency
		{
			imagealphablending($dst_img, FALSE);
			imagesavealpha($dst_img, TRUE);
		}
		$type_name = $this->getImageTypeName();
		if(!$type_name){
			throw new Exception("不支持的图片类型{$this->type}");
		}
		$src_img = call_user_func("imagecreatefrom{$type_name}", $this->src_path);
		$copy($dst_img, $src_img, 0, 0, 0, 0, $max_w, $max_h, $this->width, $this->height);

		$args = array($dst_img);
		//var_dump($args);exit;
		
		if($dst_path){
			$dst_path && $args[] = $dst_path;
		}else{
			header('Content-Disposition: filename='.$this->src_path.';');
			header('Content-Type: '.$this->getMimeType());
			header('Content-Transfer-Encoding: binary');
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', time()).' GMT');
		}
		$re = call_user_func_array("image{$type_name}", $args);
		// Kill the file handles
		imagedestroy($dst_img);
		imagedestroy($src_img);

		//chmod($this->full_dst_path, $this->file_permissions);

		return $re;
	}
	
}