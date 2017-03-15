<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>登陆</title>
<script src="js/jsbn/jsbn.js"></script>
<script src="js/jsbn/jsbn2.js"></script>
<script src="js/jsbn/prng4.js"></script>
<script src="js/jsbn/rng.js"></script>
<script src="js/jsbn/rsa.js"></script>
<script src="js/jsbn/rsa2.js"></script>
<script src="js/crypto/rollups/md5.js"></script>
<script>
function FormSubmit()
{
	//根据用户信息生成AES密钥
	var aes_key = CryptoJS.MD5(document.getElementById("username").value + document.getElementById("password").value + "3.141592653589793238462643383");
	sessionStorage.setItem('aes_key', aes_key);
	var rsa = new RSAKey();
	rsa.setPublic(document.getElementById('server_public_n').value, document.getElementById('server_public_e').value);
	document.getElementById('password').value = rsa.encrypt(document.getElementById('password').value);
	document.getElementById('form').submit();
}

function PageLoad()
{
	//页面加载时清理sessionStorage并生成客户端RSA钥匙
	var rsa = new RSAKey();
	rsa.generate(1024, '10001');

	sessionStorage.setItem('rsa_n', rsa.n.toString(16));
	sessionStorage.setItem('rsa_e', rsa.e.toString(16));
	sessionStorage.setItem('rsa_d', rsa.d.toString(16));
	sessionStorage.setItem('rsa_p', rsa.p.toString(16));
	sessionStorage.setItem('rsa_q', rsa.q.toString(16));
	sessionStorage.setItem('rsa_dmp1', rsa.dmp1.toString(16));
	sessionStorage.setItem('rsa_dmq1', rsa.dmq1.toString(16));
	sessionStorage.setItem('rsa_coeff', rsa.coeff.toString(16));
	
	document.getElementById("client_public_n").value = rsa.n.toString(16);
	document.getElementById("client_public_e").value = rsa.e.toString(16);
}
</script>
</head>

<body onload="PageLoad()">
<?php
require_once("load.php");

//输出公钥到浏览器
echo '<input type="hidden" id="server_public_n" value="' . $_SESSION['publickey']['n']. '">';
echo '<input type="hidden" id="server_public_e" value="' . $_SESSION['publickey']['e']. '">';

if($_POST['username'])
{
	echo $_POST['password'] . '<br>';
	
	$user_name = $_POST['username'];
	$query = "select * from idpass_users where username = '$user_name'";
	$result = mysql_query($query);
	$us = is_array($row = mysql_fetch_array($result));
	
	$_POST['password'] = rsa_decrypt($rsa, $_POST['password']);
	
	$ps = $us ? hash('sha256', $_POST['password'] . $row['salt']) == $row['password'] : false;
	if($ps){
		$_SESSION['user_id'] = $row['id'];
		$_SESSION['user_shell'] = hash('sha256', $row['username'].$row['password'].$row['salt']);
		$_SESSION['times'] = mktime();  //登录的时间
		//$_SESSION['salt'] = 
		echo "登录成功";
		echo '<meta http-equiv="refresh" content="1;URL=index.php">';
	}else{
		echo "用户名或密码错误";
		session_destroy();
	}
}
?>

<form id="form" action="" method="post">
<input type="hidden" id="client_public_n" name="client_public_n" value="">
<input type="hidden" id="client_public_e" name="client_public_e" value="">
用户名:<input type="text" id="username" name="username" /><br>
密　码:<input type="password" id="password" name="password" /><br>
<input type="button" onclick="FormSubmit()" value="登录" /><br>
<a href="register.php"><input type="button" value="注册" /></a><br>
</form>


</body>
</html>