<?php
//包含phpseclib库
set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
//包含随机数生成库
set_include_path(get_include_path() . PATH_SEPARATOR . 'random_compat-2.0.7');

require_once('config.php');
require_once('Crypt/RSA.php');
require_once('lib/random.php');

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

//AES加密函数
function aes_encrypt($data, $key, $iv = null){
	return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_CBC, $iv));
}

//AES解密函数
function aes_decrypt($data, $key, $iv = null){
	return mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($data), MCRYPT_MODE_CBC, $iv);
}

//开启Session设置
session_start();

if(empty($_SESSION['privatekey']))
{
	echo 'Generating RSA Key pair<br>';
	//生成一对RSA钥匙
	define('CRYPT_RSA_MODE', CRYPT_RSA_MODE_INTERNAL);
	$rsa = new Crypt_RSA();
	$rsa->setPublicKeyFormat(CRYPT_RSA_PUBLIC_FORMAT_RAW);
	$key = $rsa->createKey(512);
	$e = new Math_BigInteger($key['publickey']['e'], 10);
	$n = new Math_BigInteger($key['publickey']['n'], 10);
	
	//储存钥匙到session中
	$_SESSION['privatekey'] = $key['privatekey'];
	$_SESSION['publickey']['e'] = $e->toHex();
	$_SESSION['publickey']['n'] = $n->toHex();
}

//生成rsa实例
$rsa = new Crypt_RSA();
$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
$rsa->loadKey($_SESSION['privatekey'], CRYPT_RSA_PRIVATE_FORMAT_PKCS1);

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
		echo "无权访问，正跳转到登陆页面<br>";
		echo '<meta http-equiv="refresh" content="1;URL=login.php">';
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
		echo "登录超时，正跳转到登陆页面";
		echo '<meta http-equiv="refresh" content="1;URL=login.php">';
	}
	else
	{
		//更新当前时间
		$_SESSION['times'] = mktime();
	}
}


?>