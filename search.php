<?php

require_once('load.php');
require_once('show.php');
require_once('cutf8_py.php');

function CacheRecords($user_id)
{
	//获取所有记录
	$records = GetRecords($user_id);
	
	//扁平化记录内容
	$content = array();
	
	foreach($records as $record)
	{
		//组合记录为单条字符串
		$text = $record[0] . ' ';
		
		$lines = (count($record) - 1) / 3;
		for($i = 1; $i <= $lines; $i++)
		{
			if($record[$i*3] == 0)
			{
				$text .= $record[$i*3-2] . ' ' . $record[$i*3-1] . ' ';
				//组合拼音字母
				$text .= CUtf8_PY::encode($record[$i*3-2], 'all') . ' ' . CUtf8_PY::encode($record[$i*3-1], 'all') . ' ';
			}
		}
		
		//组合拼音字母
		$text .= CUtf8_PY::encode($record[0], 'all');
		
		$item = array($record, $text);
		array_push($content, $item);
	}
	
	//缓存记录到文件中
	file_put_contents('cache/' . $user_id . '_srh', serialize($content));
	
	return $content;
}

function CheckKeyword($key_words, $text)
{
	//检查记录是否包含关键字
	foreach($key_words as $key_word)
	{
		if(!preg_match("/" . $key_word . "/i", $text))
			return false;
	}
	return true;
}

function Search($user_id, $key_word)
{
	//生成aes实例
	$aes = new Crypt_AES(CRYPT_AES_MODE_CBC);
	$aes->setKeyLength(256);
	$aes->setKey($_SESSION['aeskey']);
	$aes->setIV('1234567812345678');
	
	//从缓存文件中读取记录
	if(!file_exists('cache/' . $user_id . '_srh'))
	{
		$content = CacheRecords($user_id, $record);
	}
	else
	{
		$content = unserialize(file_get_contents('cache/' . $user_id . '_srh'));
	}
	
	if(count($content) == 0)
	{
		echo '<h1>无记录</h1>';
		return false;
	}
	
	echo '<h1>' . htmlentities($key_word) . '的搜索结果：</h1><br>';
	echo <<<STR
<link rel="stylesheet" href="css/show.css" type="text/css" />
<div class="show-holder"><ul id="accordion" class="accordion" display="none">
STR;
	
	//解析$key_word
	$key_word = trim($key_word);
	$key_words = array();
	$start = 0;
	$length = 0;
	for($i = 0; $i < strlen($key_word); $i++)
	{
		if($key_word[$i] == ' ' && $length > 0 )
		{
			array_push($key_words, substr($key_word, $start, $length));
			$start = $i+1;
			$length = 0;
		}
		else if($key_word[$i] != ' ' && $i == strlen($key_word) - 1)
		{
			array_push($key_words, substr($key_word, $start, $length+1));
		}
		else if($key_word[$i] != ' ')
		{
			$length++;
		}
		else
		{
			$start = $i+1;
		}
	}
	
	foreach($content as $item)
	{
		if(!CheckKeyword($key_words, $item[1]))
			continue;
		$txt .= '<li><div class="link"><label name="aes">' . AESEncrypt($aes, $item[0][0]) . '</label><a href="####" name="delete_record">删除</a><a href="####" name="edit">编辑</a></div>';
		$txt .= '<ul class="submenu"><table>';
		$lines = (count($item[0]) - 1) / 3;
		for($i = 1; $i <= $lines; $i++)
		{
			$txt .= '<tr>';
			if(ValidateURL($item[0][$i*3-1]))
			{
				//判断值是否为合法URL形式
				$txt .= sprintf('<td ><a href="####" name="aes">%s</a></td><td><a href="%s" encrypted="%d" name="aes" target="_blank">%s</a></td>', AESEncrypt($aes, $item[0][$i*3-2]), AESEncrypt($aes, $item[0][$i*3-1]), $item[0][$i*3], AESEncrypt($aes, $item[0][$i*3-1]));
			}
			else
			{
				$txt .= sprintf('<td ><a href="####" name="aes">%s</a></td><td><a href="####" encrypted="%d" name="aes">%s</a></td>', AESEncrypt($aes, $item[0][$i*3-2]), $item[0][$i*3], AESEncrypt($aes, $item[0][$i*3-1]));
			}
			$txt .= '</tr>';
		}
		$txt .= '</table></ul></li>';
	}
	echo $txt;
	echo '</div></ul>';
}



?>