<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>登陆IDPass</title>
<link rel="stylesheet" href="css/login.css" media="screen" type="text/css" />
<script src="js/jsbn/jsbn.js"></script>
<script src="js/jsbn/jsbn2.js"></script>
<script src="js/jsbn/prng4.js"></script>
<script src="js/jsbn/rng.js"></script>
<script src="js/jsbn/rsa.js"></script>
<script src="js/jsbn/rsa2.js"></script>
<script src="js/crypto/rollups/md5.js"></script>
<script src="js/crypto/rollups/sha256.js"></script>
<script>
function FormSubmit(type)
{
	//隐藏表单
	document.getElementById("slick-login").style.visibility = "hidden";
	//根据用户信息生成AES密钥
	var aes_key = CryptoJS.MD5(document.getElementById("username").value + document.getElementById("password").value + "3.141592653589793238462643383");
	sessionStorage.setItem('aes_key', aes_key);
	sessionStorage.setItem('aes_key_valid', 0);
	var rsa = new RSAKey();
	rsa.setPublic(document.getElementById('server_public_n').value, document.getElementById('server_public_e').value);
	document.getElementById('password').value = CryptoJS.SHA256(document.getElementById('password').value);
	document.getElementById('username').value = rsa.encrypt(document.getElementById('username').value);
	document.getElementById('password').value = rsa.encrypt(document.getElementById('password').value);
	if(type == 1)
	{
		document.getElementById('type').value = 'login';
	}
	else
	{
		document.getElementById('type').value = 'register';
	}
	document.getElementById('slick-login').submit();
}

function PageLoad()
{
	//页面加载时生成客户端RSA钥匙
	var rsa = new RSAKey();

	rsa_n = sessionStorage.getItem('rsa_n');
	if(rsa_n == null)
	{
		rsa.generate(1024, '10001');
		sessionStorage.setItem('rsa_n', rsa.n.toString(16));
		sessionStorage.setItem('rsa_e', rsa.e.toString(16));
		sessionStorage.setItem('rsa_d', rsa.d.toString(16));
		sessionStorage.setItem('rsa_p', rsa.p.toString(16));
		sessionStorage.setItem('rsa_q', rsa.q.toString(16));
		sessionStorage.setItem('rsa_dmp1', rsa.dmp1.toString(16));
		sessionStorage.setItem('rsa_dmq1', rsa.dmq1.toString(16));
		sessionStorage.setItem('rsa_coeff', rsa.coeff.toString(16));
	}
	else
	{
		rsa.setPublic(rsa_n, sessionStorage.getItem('rsa_e'));
	}
	
	document.getElementById("client_public_n").value = rsa.n.toString(16);
	document.getElementById("client_public_e").value = rsa.e.toString(16);
}
</script>
</head>

<body onload="PageLoad()">
<div>
	<h1> IDPass</h1>
<?php
require_once("load.php");

//输出公钥到浏览器
echo '<input type="hidden" id="server_public_n" value="' . $_SESSION['publickey']['n']. '">';
echo '<input type="hidden" id="server_public_e" value="' . $_SESSION['publickey']['e']. '">';

function SessionDestroy()
{
	session_destroy();
	echo '<meta http-equiv="refresh" content="0;URL=login.php">';
}

function Login($rsa)
{
	$user_name = addslashes(stripslashes(rsa_decrypt($rsa, $_POST['username'])));
	if($user_name == '')
	{
		echo "<p>用户名不能为空<p>";
		SessionDestroy();
		return;
	}
	$query = "select * from idpass_users where username = '$user_name'";
	$result = mysql_query($query);
	$us = is_array($row = mysql_fetch_array($result));
	
	$_POST['password'] = addslashes(stripslashes(rsa_decrypt($rsa, $_POST['password'])));
	
	if($_POST['password'] == false)
	{
		echo "<p>数据校验失败<p>";
		SessionDestroy();
		return;
	}
	
	//强制修改密码
// 	$update = hash('sha256', $_POST['password'] . $row['salt']);
// 	$query = "update idpass_users set password = '$update' where username = '$user_name'";
// 	mysql_query($query);
	
	$ps = $us ? hash('sha256', $_POST['password'] . $row['salt']) == $row['password'] : false;
	if($ps){
		$_SESSION['user_id'] = $row['id'];
		$_SESSION['user_shell'] = hash('sha256', $row['username'].$row['password'].$row['salt']);
		$_SESSION['times'] = mktime();  //登录的时间
		$_SESSION['client_public_n'] = $_POST['client_public_n'];	//记录浏览器生成的RSA公钥
		$_SESSION['client_public_e'] = $_POST['client_public_e'];
		echo "<p>登录成功<p>";
		echo '<script>sessionStorage.setItem("aes_key_valid", 1);</script>';
		echo '<meta http-equiv="refresh" content="0;URL=index.php">';
	}else{
		echo "<p>用户名或密码错误</p>";
		SessionDestroy();
		echo '<meta http-equiv="refresh" content="0;URL=login.php">';
	}
}

function Register($rsa)
{
	$user_name = addslashes(stripslashes(str_replace(" ", "", rsa_decrypt($rsa, $_POST['username']))));
	if($user_name == '')
	{
		echo "<p>用户名不能为空<p>";
		SessionDestroy();
		return;
	}
	$_POST['password'] = addslashes(stripslashes(rsa_decrypt($rsa, $_POST['password'])));
	if($_POST['password'] == false)
	{
		echo "<p>数据校验失败<p>";
		SessionDestroy();
		return;
	}
	$salt = bin2hex(random_bytes(4));
	$password = hash('sha256', $_POST['password'] . $salt);
	
	//查询用户是否已存在
	$query = "select * from idpass_users where username = '$user_name'";
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	if(is_array($row))
	{
		echo "<p>用户已存在<p>";
		SessionDestroy();
	}
	else
	{
		$query = "insert into idpass_users(id, username, password, salt, prio) values(null, '$user_name', '$password', '$salt', 0)";
		$result = mysql_query($query);
		
		if($result)
		{
			$query = "select * from idpass_users where username = '$user_name'";
			$result = mysql_query($query);
			$row = mysql_fetch_array($result);
			$_SESSION['user_id'] = $row['id'];
			$_SESSION['user_shell'] = hash('sha256', $row['username'].$row['password'].$row['salt']);
			$_SESSION['times'] = mktime();  //登录的时间
			$_SESSION['client_public_n'] = $_POST['client_public_n'];	//记录浏览器生成的RSA公钥
			$_SESSION['client_public_e'] = $_POST['client_public_e'];
			//注册成功后转向主页
			echo "<p>注册成功<p>";
			echo '<script>sessionStorage.setItem("aes_key_valid", 1);</script>';
			echo '<meta http-equiv="refresh" content="0;URL=index.php">';
		}
		else
		{
			echo "<p>注册失败<p>";
			SessionDestroy();
		}
	}
}

if($_POST['type'] == 'login')
{	
	Login($rsa_decrypt);
}
elseif($_POST['type'] == 'register')
{
	Register($rsa_decrypt);
}
else
{
	echo '<p>请输入登陆或注册信息</p>';
	echo <<<STR
	<form id="slick-login" action="" method="post" onkeydown="SubmitByEnter();">
		<input type="hidden" id="client_public_n" name="client_public_n" value="">
		<input type="hidden" id="client_public_e" name="client_public_e" value="">
		<input type="hidden" id="type" name="type" value="">
		<input type="text" id="username" name="username" class="placeholder" placeholder="用户名" autocomplete="off" autofocus="autofocus" tabindex="1"/>
		<input style="display:none">
		<input type="password" id="password" name="password" class="placeholder" placeholder="密　码" autocomplete="off" tabindex="2"/>
		<input type="button" id="btn1" onclick="FormSubmit(1)" value="登录" tabindex="3"/><br>
		<input type="button" id="btn2" onclick="FormSubmit(2)" value="注册" tabindex="4"/><br>
	</form>
	<script>
    function SubmitByEnter()
    {
        if(event.keyCode == 13)
        {
        	FormSubmit(1);
        }
    }
	</script>
STR;
}
?>
</div>
</body>
</html>