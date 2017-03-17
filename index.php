<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>IDPass</title>
<meta http-equiv="X-UA-Compatible" content="IE=8">
<meta http-equiv="Expires" content="0">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Cache-control" content="no-cache">
<meta http-equiv="Cache" content="no-cache">

<link rel="stylesheet" type="text/css" href="css/index.css" />
<link rel="stylesheet" type="text/css" href="css/input-field.css" />

<script src="js/jquery-1.8.0.js"></script>
<script src="js/string_format.js"></script>
<script src="js/clipboard.min.js"></script>
<script src="js/jsbn/jsbn.js"></script>
<script src="js/jsbn/prng4.js"></script>
<script src="js/jsbn/rng.js"></script>
<script src="js/jsbn/rsa.js"></script>
<script src="js/crypto/rollups/aes.js"></script>
<script>
//提交表单时加密内容
function FormSubmit()
{
	var rsa = new RSAKey();
	var input_set = document.getElementsByTagName("input");

	rsa.setPublic(document.getElementById('publickey_n').value, document.getElementById('publickey_e').value);

	var key = CryptoJS.enc.Utf8.parse(sessionStorage.getItem('aes_key')); 
	var iv  = CryptoJS.enc.Utf8.parse('1234567812345678'); 

	document.getElementById('new_record').style.display="none";
	
	//遍历得到要提交的表单内容
	for(var i = 0; i < input_set.length; i++)
	{
		if(input_set[i].id.indexOf("input-") == 0)
		{
			//对要提交的内容使用服务器的公钥进行RSA加密
			if(input_set[i].type == "password")
			{
				//对要加密存储的内容使用客户端根据用户信息生成的密钥进行AES加密
				input_set[i].value = CryptoJS.AES.encrypt(input_set[i].value, key, { iv: iv, mode: CryptoJS.mode.CBC, padding: CryptoJS.pad.Pkcs7 });
			}
			input_set[i].value = rsa.encrypt(input_set[i].value);
		}
	}
	document.getElementById('new_record').submit();
}
</script>
<script>
	var form_items = 4;
	$(document).ready(function(){
		//在新建表单时动态增加或减少表单项
		$("#new_record").on("click", ".plus-button", function(){
			var new_item = '<div class="input-fields">\
								<input type="text" id="input-name{0}" name="name{1}" class="placeholder" placeholder="段名" autocomplete="off"><input style="display:none">\
								<input type="text" id="input-value{2}" name="value{3}" class="placeholder" placeholder="内容" autocomplete="off">\
								<div class="checkbox-holder"><input type="checkbox" id="checkbox-encrypt{4}" name="encrypt{5}" value="0"><label for="checkbox-encrypt{6}"></label></div>\
								<a href="####" class="plus-button">+</a>\
							</div>';
			$(this).parent().after(new_item.format(form_items, form_items, form_items, form_items, form_items, form_items, form_items++));
			$(this).parent().next().fadeIn(300);
		});

		//删除记录
		$("a[name=delete_record").on("click", function(){
			if(!confirm("确定删除记录？"))
				event.preventDefault();
		});

		//提交创建表单后删除创建表单页面
		$("#new_record").on("click", "[name='submit']", function(){
			$("#new_record").remove();
		});

		//点击超链接时编码URL
		$("a[name!=delete_record]").on("click", function(){
			event.preventDefault();
			location.href = encodeURI($(this).attr("href"));
		});

		//点击复制文字时
		$(".cpbtn").on("click", function(){
			if($(this).attr("encrypted") == "1")
			{
				var key = CryptoJS.enc.Utf8.parse(sessionStorage.getItem('aes_key')); 
				var iv  = CryptoJS.enc.Utf8.parse('1234567812345678'); 
				window._clipboard_text = CryptoJS.AES.decrypt($(this).attr("data-clipboard-text").toString(), key, { iv: iv, mode: CryptoJS.mode.CBC, padding: CryptoJS.pad.Pkcs7 }).toString(CryptoJS.enc.Utf8);
			}
			else
			{
				window._clipboard_text = $(this).attr("data-clipboard-text");
			}
			$("#cpbtn").click();
		});

		//点击加密复选框时动态改变输入框类型
		$("#new_record").on("click", "[type=checkbox]", function(){
			
			$(this).val($(this).attr("checked") == "checked" ? 1 : 0);
			document.getElementById($(this).parent().parent().find("[name^=value]").attr("id")).type = $(this).val() == 1 ? "password" : "text";
		});
	});
</script>
</head>


<body class="home blog">

<?php
require_once("load.php");
$arr = user_shell($_SESSION['user_id'] , $_SESSION['user_shell']);
user_mktime($_SESSION['times']);
?>


<header class="header" style="min-height: 611px">
	<div id="logo"><div class="header-logo-wrap">
		<h1 class="site-title"><a href="index.php" title="IDPass" rel="home">IDPass</a></h1>
		<h2 class="site-description">欢迎<?php echo $arr['username'];?></h2>
	</div></div>
	<div class="left-sidebar sidebar-desktop">
	<aside><h3 class="widget-title">操作</h3><ul>
		<li><a href="?type=new">新建记录</a></li>
		<li><a href="?type=show">记录列表</a></li>
	</ul></aside></div>
</header>
	

	<div id="topside">
		<div class="pages">
			<div class="menu-menu-1-container">
			<ul id="menu-menu-1" class="menu"><li id="menu-item-29" class="menu-item menu-item-type-custom menu-item-object-custom current-menu-item menu-item-29"><a href="index.php">首页</a></li>
				<li id="menu-item-41" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-41"><a href="index.php?type=export">导出</a></li>
				<li id="menu-item-42" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-42"><a href="login.php">登陆</a></li>
				<li id="menu-item-28" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-28"><a href="index.php">关于</a></li>
			</ul></div></div> <!--/menu-->

		<div id="searchform">
			<form role="search" method="get" action="">
				<input type="text" class="searchtext" value="" name="s" title="搜索：">
				<input type="submit" class="searchbutton" value=" ">
			</form>
		</div> <!--/searchform-->
		<div class="clearfix"></div>
	</div>
	

	<div id="content" style="min-height:917px">
		<div class="post">
<?php
//输出公钥到浏览器
echo '<input type="hidden" id="publickey_e" value="' . $_SESSION['publickey']['e']. '">';
echo '<input type="hidden" id="publickey_n" value="' . $_SESSION['publickey']['n']. '">';
if($_POST['_post_type'] == "new_record")
{
	$form_data['recordname'] = rsa_decrypt($rsa, $_POST['recordname']);
	foreach($_POST as $name => $value)
	{
 		if(preg_match("/^name(\d+)$/", $name, $matches))
 		{
 			if($_POST["value".$matches[1]])
 			{
				$_name  = $_POST["name".$matches[1]];
				$_value = $_POST["value".$matches[1]];
				if(empty($_name) || empty($_value))
				{
					continue;
				}
				$_name  = rsa_decrypt($rsa, $_POST["name" .$matches[1]]);
				$_value = rsa_decrypt($rsa, $_POST["value".$matches[1]]);
				if(strlen($_name) == 0 || strlen($_value) == 0)
				{
					continue;
				}
				if($_POST["encrypt".$matches[1]] == "1")
					$form_data["encrypt".$matches[1]] = 1;
				else
					$form_data["encrypt".$matches[1]] = 0;
				$form_data["name" .$matches[1]] = $_name;
				$form_data["value".$matches[1]] = $_value;
			}
		}
	}
	
	//检查创建的表单内容合法性
	$check = 1;
	foreach($form_data as $name => $value)
	{
		if(strlen($value) == 0)
		{
			echo $name . '内容不能为空<br>';
			$check = 0;
			break;
		}
	}
	//把表单内容存入数据库中;
	//查询表单是否已存在
	$query = sprintf("select * from idpass_secret where user_id = %d and record = '%s'", $_SESSION['user_id'], $form_data['recordname']);
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	if(is_array($row))
	{
		echo "记录已存在<br>";
	}
	else
	{
		//生成新的记录
		foreach($form_data as $name => $value)
		{
			if(preg_match("/^name(\d+)$/", $name, $matches))
			{
				$_name = $form_data["name".$matches[1]];
				$_value = $form_data["value".$matches[1]];
				$_encrypt = $form_data["encrypt".$matches[1]];
				$query = sprintf("insert into idpass_secret(user_id, record, name, value, encrypt) values(%d, '%s', '%s', '%s', %d)",
									$_SESSION['user_id'], $form_data['recordname'], $_name, $_value, $_encrypt);
				$result = mysql_query($query);
				$row = mysql_fetch_array($result);
				if($result == true)
				{
					echo "记录成功<br>";
				}
				echo $query.'<br>';
			}
		}
	}
}
else if($_GET['type'] == "new")
{
	//显示新建记录页面
	echo "<script>form_items=4;</script>";
	include("new_record.html");
	//echo <<<STR
	
STR;
}
elseif($_GET['type'] == "show")
{
	//显示所有记录
	$query = sprintf("select distinct record from idpass_secret where user_id = %d", $_SESSION['user_id']);
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	if(is_array($row))
	{
		echo '<h2>记录列表</h2>';
		echo '<ul>';
		do
		{
			$output = sprintf('<li><a href="?type=showrecord&name=%s">%s</a><label></label>
							       <a href="?type=deleterecord&name=%s" name="delete_record">删除</a></li>', $row[0], $row[0], $row[0]);
			echo $output;
		} while($row = mysql_fetch_array($result));
		echo '</ul>';
	}
	else
	{
		echo '<h2>无记录</h2>';
	}
}
elseif($_GET['type'] == "showrecord")
{
	echo '<h2>' . urldecode($_GET['name']) .'</h2>';
	$query = sprintf("select name, value, encrypt from idpass_secret where user_id = %d and record = '%s'", $_SESSION['user_id'], urldecode($_GET['name']));
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	if(is_array($row))
	{
		echo '<ul>';
		do
		{
			$output = sprintf('<li><label>%s %s</label><button class="cpbtn btn" data-clipboard-text="%s" encrypted="%d"><img src="assets/images/clippy.svg" width="13"></button></li>', $row[0], $row[1], $row[1], $row[2]);
			echo $output;
		} while($row = mysql_fetch_array($result));
		echo '</ul>';
	}
}
elseif($_GET['type'] == "deleterecord")
{
	$query = sprintf("delete from idpass_secret where user_id = %d and record = '%s'", $_SESSION['user_id'], urldecode($_GET['name']));
	mysql_query($query);
	echo '<script>self.location="?type=show";</script>';
	exit;
}
else if($_GET['type'] == "export")
{
	//导出数据
	echo '<h2>请下载下面的组件并解压缩，把导出的html文件放置在解压缩后的文件夹内，然后打开html文件查看。</h2>';
	echo '<h2><a href="http://o7sk7ggui.bkt.clouddn.com/idpass/export_components.rar?attname=" download>export components.rar</a></h2>';
	echo '<h2><a href="export.php" target="_blank">请点此导出数据html</a></h2>';
}
?>


		</div>
		<div class="clearfix"></div>
	</div>

	<footer>
	<span class="alignleft">Copyright © 2017 Soe</span><br>
	<span class="alignright"><a href="http://www.miitbeian.gov.cn/" rel="external nofollow" target="_blank">鄂ICP备17003963号</a></span>
	<br>
	</footer>
	
	<!-- 辅助复制到粘贴板 -->
	<button id="cpbtn" hidden></button>


<script>
//支持复制到粘贴板
var clipboard = new Clipboard('#cpbtn', {
	text: function() {
		return window._clipboard_text;
	}
});
clipboard.on('success', function(e) {
	console.log(e);
});
clipboard.on('error', function(e) {
	console.log(e);
});
</script>
</body>
</html>