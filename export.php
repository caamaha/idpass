<?php
require_once("load.php");

function ExportSecret($user_id)
{
	$query = "select * from idpass_users where id = '$user_id'";
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	if(!is_array($row))
	{
		return;
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
	
	$txt = <<<STR
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Secret Records</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/style.css" media="screen" type="text/css" />
<script src="js/jquery-1.8.0.js"></script>
<script src="js/index.js"></script>
<script src="js/md5.js"></script>
<script src="js/aes.js"></script>
<script src="js/clipboard.min.js"></script>
<script>
function DecryptRecords()
{
	var aes_key = CryptoJS.MD5(document.getElementById("username").value + document.getElementById("password").value + "3.141592653589793238462643383");
	var key = CryptoJS.enc.Utf8.parse(aes_key); 
	var iv  = CryptoJS.enc.Utf8.parse('1234567812345678');

	var val_set = document.getElementsByTagName("a");

	for(var i = 0; i < val_set.length; i++)
	{
		if(val_set[i].getAttribute('encrypted') == "1")
		{
			val_set[i].innerHTML = CryptoJS.AES.decrypt(val_set[i].innerHTML.toString(), key, { iv: iv, mode: CryptoJS.mode.CBC, padding: CryptoJS.pad.Pkcs7 }).toString(CryptoJS.enc.Utf8);;
		}
		val_set[i].setAttribute('encrypted', '0');
		val_set[i].setAttribute('data-clipboard-text', val_set[i].innerHTML.toString());
	}
}
$(document).ready(function(){
	$("#decrypt").click(function(){
				DecryptRecords();
			});
});
</script>

</head>
<body>

	<div>
	<form id="slick-login">
		<label for="username">username</label><input type="text" id="username" name="username" class="placeholder" placeholder="user name">
		<label for="password">password</label><input type="password" id="password" name="password" class="placeholder" placeholder="password">
		<input type="button" id="decrypt" value="Decrypt">
	</form>
	</div>
	<div>
	<ul id="accordion" class="accordion">
STR;
	foreach($records as $record)
	{
		$txt .= '<li><div class="link">' . $record[0] . '</div>';
		$txt .= '<ul class="submenu"><table>';
		$lines = (count($record) - 1) / 3;
		for($i = 1; $i <= $lines; $i++)
		{
			$txt .= '<tr>';
			$txt .= sprintf('<td width="200 px"><a href="" onclick="return false;">%s</a></td><td><a href="" encrypted="%d" onclick="return false;">%s</a></td>', $record[$i*3-2], $record[$i*3], $record[$i*3-1]);
			$txt .= '</tr>';
		}
		$txt .= '</table></ul></li>';
	}
	$txt .= <<<STR
</ul>
	</div>
<script>
	var btns = document.querySelectorAll('a');
	var clipboard = new Clipboard(btns);
</script>
</body>
</html>
STR;
	
	//下载数据文件到客户端
	$filename = 'idpass_' . $user_name . '.html';
	$encoded_filename = urlencode($filename);
	$encoded_filename = str_replace("+", "%20", $encoded_filename);
	
	header("Content-Type: application/octet-stream");
	Header("Accept-Ranges: bytes");
	Header("Accept-Length:" . strlen($txt));
	if (preg_match("/MSIE/", $_SERVER['HTTP_USER_AGENT']) ) {
		header('Content-Disposition:  attachment; filename="' . $encoded_filename . '"');
	} elseif (preg_match("/Firefox/", $_SERVER['HTTP_USER_AGENT'])) {
		header('Content-Disposition: attachment; filename*="utf8' .  $filename . '"');
	} else {
		header('Content-Disposition: attachment; filename="' .  $filename . '"');
	}
	
	echo $txt;
	
	exit();
}

ExportSecret($_SESSION['user_id']);

?>