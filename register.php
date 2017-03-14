<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>注册</title>
<script src="js/jsbn/jsbn.js"></script>
<script src="js/jsbn/prng4.js"></script>
<script src="js/jsbn/rng.js"></script>
<script src="js/jsbn/rsa.js"></script>
<script>
function FormSubmit()
{
	var rsa = new RSAKey();
	rsa.setPublic(document.getElementById('publickey_n').value, document.getElementById('publickey_e').value);
	document.getElementById('password').value = rsa.encrypt(document.getElementById('password').value);
	document.getElementById('form').submit();
}
</script>
</head>

<body>

<?php
require_once("config.php");

//输出公钥到浏览器
echo '<input type="hidden" id="publickey_e" value="' . $_SESSION['publickey']['e']. '">';
echo '<input type="hidden" id="publickey_n" value="' . $_SESSION['publickey']['n']. '">';

if($_POST['username'])
{
	$user_name = str_replace(" ", "", $_POST['username']);
	echo $user_name . "<br>";
	$_POST['password'] = rsa_decrypt($rsa, $_POST['password']);
	$salt = bin2hex(random_bytes(4));
	$password = hash('sha256', $_POST['password'] . $salt);
	echo $password . "<br>";
	
	//查询用户是否已存在
	$query = "select * from user_list where username = '$user_name'";
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	if(is_array($row))
	{
		echo "用户已存在<br>";
	}
	else
	{
		$query = "insert into user_list(uid, username, password, salt) values(null, '$user_name', '$password', '$salt')";
		$result = mysql_query($query);
		
		//获得受影响的行数
		$row = mysql_affected_rows($conn);
		if($row > 0)
		{
			//为新用户新建一个信息table
			$query = "create table info_$user_name (id INT NOT NULL AUTO_INCREMENT,
						record VARCHAR(255),
						name VARCHAR(255),
						value VARCHAR(65535),
						encrypt BOOLEAN,
						PRIMARY KEY(id))";
			$result = mysql_query($query);
			if($result == true)
			{
				//注册成功后自动登陆
				$result = mysql_query("select uid from user_list where username = '$user_name'");
				$row = mysql_fetch_array($result);
				
				$_SESSION['uid'] = $row['uid'];
				$_SESSION['user_shell'] = hash('sha256', $user_name . $password . $salt);
				$_SESSION['times'] = mktime();  //登录的时间
				echo "注册成功<br>";
				echo '<meta http-equiv="refresh" content="1;URL=index.php">';
			}
			else
			{
				$query = "delete from user_list where username = '$user_name'";
				mysql_query($query);
				echo "注册失败，用户名非法<br>";
			}
		}
		else
		{
			echo "注册失败<br>";
		}
	}
}
?>

<form action="" id="form" method="post">
用户名:<input type="text" name="username" /><br>
密　码:<input type="password" id="password" name="password" /><br>
<input type="button" onclick="FormSubmit()" value="注册" /><br>
</form>

</body>
</html>
