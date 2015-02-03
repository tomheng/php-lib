<?php
/**
 *  
 * 通过浏览器更新SVN
 *
 * @author zhm20070928@gmail.com
 *
 */
header("Cache-Control:no-cache,must-revalidate");  
$handle = popen('export LC_CTYPE=en_US.UTF-8 && /usr/bin/svn up --username user_test --password pass_test /data1/svn_repo', 'r');  
$read = stream_get_contents($handle);//需要 PHP5 或更高版本  
echo "<pre>";  
printf($read);  
echo "</pre>";  
pclose($handle);
