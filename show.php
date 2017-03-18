<?php

require_once('Crypt/RSA.php');

function GetRecords($user_id, $is_encrypt = 1)
{
	//生成一个RSA实例
	$rsa = new Crypt_RSA();
	$key['n'] = new Math_BigInteger($_SESSION['client_public_n'], 16);
	$key['e'] = new Math_BigInteger($_SESSION['client_public_e'], 16);
	
	$rsa->loadKey($key, CRYPT_RSA_PUBLIC_FORMAT_RAW);
	$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
	
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
			if($is_encrypt)
				$one_record = array(bin2hex($rsa->encrypt($record_name)));
			else
				$one_record = array($record_name);
			
			$query = sprintf("select name, value, encrypt from idpass_secret where user_id = %d and record = '%s'", $user_id, $record_name);
			$result = mysql_query($query);
			$row = mysql_fetch_array($result);
			if(is_array($row))
			{
				do
				{
					if($is_encrypt)
					{
						$row[0] = bin2hex($rsa->encrypt($row[0]));
						$row[1] = bin2hex($rsa->encrypt($row[1]));
					}
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
	//显示所有记录
	$records = GetRecords($user_id);
	if(!$records)
	{
		echo '<h1>无记录</h1>';
		return false;
	}
	
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

function getCookie(c_name)
{
	return sessionStorage.getItem(c_name);
}

function RsaDecrypt()
{
	var rsa_set = document.getElementsByTagName("label");
	var rsa = new RSAKey();
	rsa.setPrivateEx(getCookie('rsa_n'), getCookie('rsa_e'), getCookie('rsa_d'), getCookie('rsa_p'), getCookie('rsa_q'), getCookie('rsa_dmp1'), getCookie('rsa_dmq1'), getCookie('rsa_coeff'));
	for(var i = 0; i < rsa_set.length; i++)
	{
		if(rsa_set[i].getAttribute("name").indexOf("rsa") == 0)
		{
			rsa_set[i].innerHTML = rsa.decrypt(rsa_set[i].innerHTML);
		}
	}
	rsa_set = document.getElementsByTagName("a");
	for(var i = 0; i < rsa_set.length; i++)
	{
		if(rsa_set[i].getAttribute("name") != null)
		{
			if(rsa_set[i].getAttribute("name").indexOf("rsa") == 0)
			{
				rsa_set[i].innerHTML = rsa.decrypt(rsa_set[i].innerHTML);
			}
		}
	}
	//在解密后再显示表单数据，否则会把解密前数据显示出来，影响美观
	document.getElementById("accordion").style.display = "block";
}
</script>
<link rel="stylesheet" href="css/show.css" type="text/css" />
<div class="show-holder"><ul id="accordion" class="accordion">
STR;
	
	foreach($records as $record)
	{
		$txt .= '<li><div class="link"><label name="rsa">' . $record[0] . '</label><a href="####" name="delete_record">删除</a><a href="####" name="edit">编辑</a></div>';
		$txt .= '<ul class="submenu"><table>';
		$lines = (count($record) - 1) / 3;
		for($i = 1; $i <= $lines; $i++)
		{
			$txt .= '<tr>';
			$txt .= sprintf('<td ><a href="####" name="rsa">%s</a></td><td><a href="####" encrypted="%d" name="rsa">%s</a></td>', $record[$i*3-2], $record[$i*3], $record[$i*3-1]);
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
	RsaDecrypt();
	$(document).ready(function(){
		$("#accordion").on("click", "a[name='rsa']", function(){
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