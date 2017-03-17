<?php

function ReplaceURL($content, $url)
{
	//把内容中相对路径的URL转换为绝对路径
	return $content;
}

$ch = curl_init();
curl_setopt($ch,CURLOPT_URL,"http://www.baidu.com");
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_HEADER,0);
$content = curl_exec($ch);
if($content === FALSE ){
	echo "CURL Error:".curl_error($ch);
}
// 4. 释放curl句柄
curl_close($ch);

echo htmlentities($content);

?>