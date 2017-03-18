<?php

require_once('show.php');

function CheckKeyword($key_word, $record)
{
	//组合记录为单条字符串
	$content = $record[0] . ' ';
	
	$lines = (count($record) - 1) / 3;
	for($i = 1; $i <= $lines; $i++)
	{
		if($record[$i*3] == 0)
		{
			$content .= $record[$i*3-2] . ' ' . $record[$i*3-1] . ' ';
		}
	}
	
	//检查记录是否包含关键字
	if(preg_match("/" . $key_word . "/i", $content))
		return true;
	else
		return false;
	if((strstr($record[0], $key_word)))
		return true;
}

function Search($user_id, $key_word)
{
	$records = GetRecords($user_id, 0);
	if(!$records)
	{
		echo '<h1>无记录</h1>';
		return false;
	}
	
	echo '<h1>' . htmlentities($key_word) . '的搜索结果：</h1><br>';
	echo <<<STR
<script>
function DecryptValue(val)
{
	if(sessionStorage.getItem('aes_key_valid') != 1)
	{
		location.href='login.php';
		return;
	}
	var key = CryptoJS.enc.Utf8.parse(sessionStorage.getItem('aes_key'));
	var iv  = CryptoJS.enc.Utf8.parse('1234567812345678');
	return CryptoJS.AES.decrypt(val.toString(), key, { iv: iv, mode: CryptoJS.mode.CBC, padding: CryptoJS.pad.Pkcs7 }).toString(CryptoJS.enc.Utf8);;
}
$(document).ready(function(){
	$("#decrypt").click(function(){
				DecryptRecords();
			});
});
</script>
<link rel="stylesheet" href="css/show.css" type="text/css" />
<div class="show-holder"><ul id="accordion" class="accordion">
STR;
	
	foreach($records as $record)
	{
		if(!CheckKeyword($key_word, $record))
			continue;
		$txt .= '<li><div class="link"><label>' . $record[0] . '</label><a href="?type=deleterecord&name=' . $record[0] . '" name="delete_record">删除</a><a href="index.php?type=edit&name=' . urlencode($record[0]) . '">编辑</a></div>';
		$txt .= '<ul class="submenu"><table>';
		$lines = (count($record) - 1) / 3;
		for($i = 1; $i <= $lines; $i++)
		{
			$txt .= '<tr>';
			$txt .= sprintf('<td ><a href="####">%s</a></td><td><a href="####" encrypted="%d">%s</a></td>', $record[$i*3-2], $record[$i*3], $record[$i*3-1]);
			$txt .= '</tr>';
		}
		$txt .= '</table></ul></li>';
	}
	echo $txt;
	echo <<<STR
</div></ul>
<button id="cpbtn" hidden></button>
<script src="js/show.js"></script>
<script>
	$(document).ready(function(){
		$("#accordion").on("click", "a", function(){
			if($(this).attr("encrypted") == "1")
			{
				$(this).text(DecryptValue($(this).text()));
				$(this).attr("encrypted", "0");
			}
			window._clipboard_text = $(this).text();
			$("#cpbtn").click();
		});
	});
	
</script>
STR;
}

?>