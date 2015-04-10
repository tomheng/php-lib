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
$url = "http://221.179.190.191/admin/test.php";
$re = Http::socketRequest($url);
//var_dump($re);
print_r($re);
