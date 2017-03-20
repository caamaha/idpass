<?php

require_once('load.php');
require_once('show.php');
require_once('cutf8_py.php');

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
			
			//组合拼音字母
			$content .= CUtf8_PY::encode($record[$i*3-1], 'all') . ' ';
		}
	}
	
	//组合拼音字母
	$content .= CUtf8_PY::encode($record[0], 'all');
	
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
	$records = GetRecords($user_id);
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
				if(encrypted_set[i].getAttribute("href").indexOf("####") != 0)
				{
					encrypted_set[i].setAttribute("href", CryptoJS.AES.decrypt(encrypted_set[i].getAttribute("href"), key, { iv: iv, mode: CryptoJS.mode.CBC, padding: CryptoJS.pad.Pkcs7 }).toString(CryptoJS.enc.Utf8));
				}
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
	echo <<<STR
</div></ul>
<button id="cpbtn" hidden></button>
<script src="js/show.js"></script>
<script>
	DecryptFirst();
	$(document).ready(function(){
		$("#accordion").on("click", "a[name='aes']", function(){
			//点击解密文字并复制文字
			if($(this).attr("href").indexOf("####") != 0)
			{
				return;
			}
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

// if($_GET['s'])
// {
// 	$key_word = htmlentities(addslashes($_GET['s']));
// 	Search($_SESSION['user_id'], $key_word);
// }

?>