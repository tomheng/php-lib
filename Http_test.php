<?php
/**
 *
 * HTTP TEST
 *
 * @author zhm20070928@gmail.com
 * @date 2015-03-12
 *
 */
require 'Http.php';
$url = "http://www.baidu.com";
//$re = Http::socketRequest($url);
$re = Http::get($url);
//var_dump($re);
print_r($re);
