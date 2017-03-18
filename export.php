<?php
namespace PHPArchive;

require_once("load.php");
require_once("show.php");
require_once("lib/php-archive/src/Zip.php");

function ExportSecret($user_id)
{
	$records = GetRecords($user_id, 0);
	if(!$records)
	{
		echo '<h1>无记录</h1>';
		return false;
	}
	
	$txt = <<<STR
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Secret Records</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style type="text/css">
STR;
	$fid = fopen("export/css/style.min.css", "r");
	$txt .= fread($fid, filesize("export/css/style.min.css"));
	fclose($fid);
	$txt .= <<<STR
</style>
<script>
STR;
	
	$fid = fopen("export/js/md5.js", "r");
	$txt .= fread($fid, filesize("export/js/md5.js"));
	fclose($fid);
	
	$fid = fopen("export/js/aes.js", "r");
	$txt .= fread($fid, filesize("export/js/aes.js"));
	fclose($fid);
	
	$fid = fopen("export/js/clipboard.min.js", "r");
	$txt .= fread($fid, filesize("export/js/clipboard.min.js"));
	fclose($fid);
	
	$txt .= <<<STR
</script>
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
</script>

</head>
<body>

	<div>
	<form id="slick-login">
		<label for="username">username</label><input type="text" id="username" name="username" class="placeholder" placeholder="user name">
		<label for="password">password</label><input type="password" id="password" name="password" class="placeholder" placeholder="password">
		<input type="button" id="decrypt" value="Decrypt" onclick="DecryptRecords()">
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
			$txt .= sprintf('<td><a href="####">%s</a></td><td><a href="####" encrypted="%d">%s</a></td>', $record[$i*3-2], $record[$i*3], $record[$i*3-1]);
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
	$query = "select * from idpass_users where id = '$user_id'";
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	$user_name = $row['username'];
	$filename = 'idpass_' . $user_name . '.html';
	
	
	//压缩文件
	$tar = new Zip();
	$tar->setCompression(9, Archive::COMPRESS_GZIP);
	$tar->create();
	$tar->addData($filename, $txt);
	$tar->close();
	
	$filename = 'idpass_' . $user_name . '.zip';
	$encoded_filename = urlencode($filename);
	$encoded_filename = str_replace("+", "%20", $encoded_filename);
	
	header("Content-Type: application/octet-stream");
	Header("Accept-Ranges: bytes");
	Header("Accept-Length:" . strlen($tar));
	if (preg_match("/MSIE/", $_SERVER['HTTP_USER_AGENT']) ) {
		header('Content-Disposition:  attachment; filename="' . $encoded_filename . '"');
	} elseif (preg_match("/Firefox/", $_SERVER['HTTP_USER_AGENT'])) {
		header('Content-Disposition: attachment; filename*="utf8' .  $filename . '"');
	} else {
		header('Content-Disposition: attachment; filename="' .  $filename . '"');
	}
	
	echo $tar->getArchive();
}


user_shell($_SESSION['user_id'] , $_SESSION['user_shell']);
ExportSecret($_SESSION['user_id']);

?>