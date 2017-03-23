<?php

function ValidateURL($url)
{
	//验证URL是否合法
	$patern = '/^(http[s]?:\/\/)?'.
			'(([0-9]{1,3}\.){3}[0-9]{1,3}'. // IP形式的URL- 199.194.52.184
			'|'. // 允许IP和DOMAIN（域名）
			'([0-9a-z_!~*\'()-]+\.)*'. // 三级域验证- www.
			'([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.'. // 二级域验证
			'[a-z]{2,6})'. // 顶级域验证.com or .museum
			'(:[0-9]{1,4})?'. // 端口- :80
			'((\/\?)|'. // 如果含有文件对文件部分进行校验
			'(\/[0-9a-zA-Z_!~\*\'\(\)\.;\?:@&=\+\$,%#-\/]*)?)$' .
			'/';
	if(!preg_match($patern, $url)) {
		return false;
	}
	else
	{
		return true;
	}
}

function AESEncrypt($aes, $msg)
{
	return base64_encode($aes->encrypt($msg));
}

function GetRecords($user_id)
{	
	//获取用户名
	$query = "select * from idpass_users where id = '$user_id'";
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	if(!is_array($row))
	{
		return false;
	}
	$user_name = $row['username'];
	$records = array();
	
	//查询所有记录
	$query = sprintf("select distinct record from idpass_secret where user_id = %d", $user_id);
	$records_result = mysql_query($query);
	$records_row = mysql_fetch_array($records_result);
	if(is_array($records_row))
	{
		do
		{
			$record_name = $records_row[0];
			$one_record = array($record_name);
			
			$query = sprintf("select name, value, encrypt from idpass_secret where user_id = %d and record = '%s'", $user_id, $record_name);
			$result = mysql_query($query);
			$row = mysql_fetch_array($result);
			if(is_array($row))
			{
				do
				{
					array_push($one_record, $row[0], $row[1], $row[2]);
				} while($row = mysql_fetch_array($result));
			}
			array_push($records, $one_record);
		} while($records_row = mysql_fetch_array($records_result));
	}
	
	return $records;
}

function ShowRecords($user_id)
{
	//生成aes实例
	$aes = new Crypt_AES(CRYPT_AES_MODE_CBC);
	$aes->setKeyLength(256);
	$aes->setKey($_SESSION['aeskey']);
	$aes->setIV('1234567812345678');
	
	//显示所有记录
	$records = GetRecords($user_id);
	if(!$records)
	{
		echo '<h1>无记录</h1>';
		return false;
	}
	
	echo <<<STR
<link rel="stylesheet" href="css/show.css" type="text/css" />
<div class="show-holder"><ul id="accordion" class="accordion">
STR;
	
	foreach($records as $record)
	{
		$txt .= '<li><div class="link"><label name="aes">' . AESEncrypt($aes, $record[0]) . '</label><a href="####" name="delete_record">删除</a><a href="####" name="edit">编辑</a></div>';
		$txt .= '<ul class="submenu"><table>';
		$lines = (count($record) - 1) / 3;
		for($i = 1; $i <= $lines; $i++)
		{
			$txt .= '<tr>';
			if(ValidateURL($record[$i*3-1]))
			{
				//判断值是否为合法URL形式
				$txt .= sprintf('<td ><a href="####" name="aes">%s</a></td><td><a href="%s" encrypted="%d" name="aes" target="_blank">%s</a></td>', AESEncrypt($aes, $record[$i*3-2]), AESEncrypt($aes, $record[$i*3-1]), $record[$i*3], AESEncrypt($aes, $record[$i*3-1]));
			}
			else
			{
				$txt .= sprintf('<td ><a href="####" name="aes">%s</a></td><td><a href="####" encrypted="%d" name="aes">%s</a></td>', AESEncrypt($aes, $record[$i*3-2]), $record[$i*3], AESEncrypt($aes, $record[$i*3-1]));
			}
			
			$txt .= '</tr>';
		}
		$txt .= '</table></ul></li>';
	}
	echo $txt;
	echo '</div></ul><script>DecryptFirst();</script>';
}

?>