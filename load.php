<?php
//包含phpseclib库
set_include_path(get_include_path() . PATH_SEPARATOR . 'lib/phpseclib');
//包含随机数生成库
set_include_path(get_include_path() . PATH_SEPARATOR . 'lib/random_compat-2.0.7/lib');

require_once('config.php');
require_once('Crypt/RSA.php');
require_once('Crypt/AES.php');
require_once('random.php');

header("Content-type: text/html; charset=utf-8");

//设置session保存路径
$savePath =  getcwd() . '/session';
if (!file_exists($savePath))
{
	mkdir($savePath, 0777, true);
	chmod($savePath, 0777);
}
session_save_path($savePath);

//RSA解密
function rsa_decrypt($rsa, $msg)
{
	$s = new Math_BigInteger($msg, 16);
	return $rsa->decrypt($s->toBytes());
}

//开启Session设置
session_start();

if(empty($_SESSION['privatekey']))
{
	//生成一对RSA钥匙
	$rsa = new Crypt_RSA();
	$rsa->setPublicKeyFormat(CRYPT_RSA_PUBLIC_FORMAT_RAW);
	$key = $rsa->createKey(512);
	$e = new Math_BigInteger($key['publickey']['e'], 10);
	$n = new Math_BigInteger($key['publickey']['n'], 10);
	
	//储存钥匙到session中
	$_SESSION['privatekey'] = $key['privatekey'];
	$_SESSION['publickey']['e'] = $e->toHex();
	$_SESSION['publickey']['n'] = $n->toHex();
	
	//生成AES钥匙
	$_SESSION['aeskey'] = md5((random_bytes(8)));
}

//生成rsa实例
$rsa_decrypt = new Crypt_RSA();
$rsa_decrypt->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
$rsa_decrypt->loadKey($_SESSION['privatekey'], CRYPT_RSA_PRIVATE_FORMAT_PKCS1);

//生成aes实例
$aes = new Crypt_AES(CRYPT_AES_MODE_CBC);
$aes->setKeyLength(256);
$aes->setKey($_SESSION['aeskey']);
$aes->setIV('1234567812345678');

//连接数据库
$conn = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
mysql_select_db(DB_NAME, $conn);
//更改数据库编码
mysql_query('SET character_set_client = utf8;');
mysql_query('SET character_set_results = utf8;');
mysql_query('SET character_set_connection = utf8;');
mysql_query('SET character_set_database = utf8;');

//用于判断用户是否登陆，以及其是否具有访问权限
function user_shell($user_id, $shell)
{
	if($user_id !== addslashes($user_id))
	{
		echo "非法注入<br>";
		echo '<meta http-equiv="refresh" content="1;URL=login.php">';
		exit();
	}
	$query = "select * from idpass_users where id = '$user_id'";
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	$us = is_array($row);
	$shell = $us ? $shell == hash('sha256', $row['username'] . $row['password'] . $row['salt']) : false;
	if($shell)
	{
		return $row;
	}
	else
	{
		//无权访问
		echo '<meta http-equiv="refresh" content="0;URL=login.php">';
		exit();
	}
}

//查看用户登陆是否超时
function user_mktime($online_time)
{
	$new_time = mktime();
	//TODO:更改超时时间
	if($new_time - $online_time > '30000')
	{
		session_destroy();
		echo '<meta http-equiv="refresh" content="0;URL=login.php">';
	}
	else
	{
		//更新当前时间
		$_SESSION['times'] = mktime();
	}
}


?>