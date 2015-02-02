<?php
/**
 * 常用函数
 *
 * @package default
 * @author Heng Min Zhan
 */
class 
{
    /**
     * 转码函数
     * @param Mixed $data 需要转码的数组
     * @param String $dstEncoding 输出编码
     * @param String $srcEncoding 传入编码
     * @param bool $toArray 是否将stdObject转为数组输出
     * @return Mixed
     */
    static public function convertEncoding($data, $dstEncoding, $srcEncoding, $toArray=false) {
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