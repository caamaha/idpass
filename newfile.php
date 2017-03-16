<?php 

$ua = $_SERVER["HTTP_USER_AGENT"];  
$filename = "中文文件名.txt";  
$encoded_filename = urlencode($filename);  
$encoded_filename = str_replace("+", "%20", $encoded_filename);  

header("Content-Type: application/octet-stream");
if (preg_match("/MSIE/", $_SERVER['HTTP_USER_AGENT']) ) {
	header('Content-Disposition:  attachment; filename="' . $encoded_filename . '"');
} elseif (preg_match("/Firefox/", $_SERVER['HTTP_USER_AGENT'])) {
	header('Content-Disposition: attachment; filename*="utf8' .  $filename . '"');
} else {
	header('Content-Disposition: attachment; filename="' .  $filename . '"');
}

// echo fread($file,filesize('文件地址'));
echo 'hello this is auto generated.'

?>