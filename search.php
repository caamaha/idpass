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
	//生成aes实例
	$aes = new Crypt_AES(CRYPT_AES_MODE_CBC);
	$aes->setKeyLength(256);
	$aes->setKey($_SESSION['aeskey']);
	$aes->setIV('1234567812345678');
	
	//获取所有记录
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
function DecryptFirst()
{
	var encrypted_set = document.getElementsByTagName("label");
	var key = CryptoJS.enc.Utf8.parse(sessionStorage.getItem('public_aes_key')); 
	var iv  = CryptoJS.enc.Utf8.parse('1234567812345678'); 
	for(var i = 0; i < encrypted_set.length; i++)
	{
		if(encrypted_set[i].getAttribute("name").indexOf("aes") == 0)
		{
			encrypted_set[i].innerHTML = CryptoJS.AES.decrypt(encrypted_set[i].innerHTML, key, { iv: iv, mode: CryptoJS.mode.CBC, padding: CryptoJS.pad.Pkcs7 }).toString(CryptoJS.enc.Utf8);
		}
	}
	encrypted_set = document.getElementsByTagName("a");
	for(var i = 0; i < encrypted_set.length; i++)
	{
		if(encrypted_set[i].getAttribute("name") != null)
		{
			if(encrypted_set[i].getAttribute("name").indexOf("aes") == 0)
			{
				encrypted_set[i].innerHTML = CryptoJS.AES.decrypt(encrypted_set[i].innerHTML, key, { iv: iv, mode: CryptoJS.mode.CBC, padding: CryptoJS.pad.Pkcs7 }).toString(CryptoJS.enc.Utf8);
			}
		}
	}
	//在解密后再显示表单数据，否则会把解密前数据显示出来，影响美观
	document.getElementById("accordion").style.display = "block";
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
		for($i = 0; $i < count($record); $i++)
		{
			if(($i % 3) == 0 && $i > 0)
				continue;
			$record[$i] = base64_encode($aes->encrypt($record[$i]));
		}
		$txt .= '<li><div class="link"><label name="aes">' . $record[0] . '</label><a href="####" name="delete_record">删除</a><a href="####" name="edit">编辑</a></div>';
		$txt .= '<ul class="submenu"><table>';
		$lines = (count($record) - 1) / 3;
		for($i = 1; $i <= $lines; $i++)
		{
			$txt .= '<tr>';
			$txt .= sprintf('<td ><a href="####" name="aes">%s</a></td><td><a href="####" name="aes" encrypted="%d">%s</a></td>', $record[$i*3-2], $record[$i*3], $record[$i*3-1]);
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
	DecryptFirst();
	$(document).ready(function(){
		$("#accordion").on("click", "a[name='aes']", function(){
			if($(this).attr("encrypted") == "1")
			{
				$(this).text(DecryptValue($(this).text()));
				$(this).attr("encrypted", "0");
			}
			window._clipboard_text = $(this).text();
			$("#cpbtn").click();
		});
		$("#accordion").on("click", "a[name='edit']", function(){
			//编辑记录
			name = $(this).parent().children("label").text();
			var rsa = new RSAKey();
			rsa.setPublic(document.getElementById('publickey_n').value, document.getElementById('publickey_e').value);
			location.href='index.php?type=edit&name=' + encodeURI(rsa.encrypt(name));
		});
		$("#accordion").on("click", "a[name='delete_record']", function(){
			//删除记录
			name = $(this).parent().children("label").text();
			if(confirm("确定删除记录" + name + "?"))
			{
				var rsa = new RSAKey();
				rsa.setPublic(document.getElementById('publickey_n').value, document.getElementById('publickey_e').value);
				location.href='index.php?type=deleterecord&name=' + encodeURI(rsa.encrypt(name));
			}
		});
	});
	
</script>
STR;
}

?>