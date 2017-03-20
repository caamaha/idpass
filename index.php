<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>IDPass</title>

<link rel="stylesheet" type="text/css" href="css/index.css" />
<link rel="stylesheet" type="text/css" href="css/input-field.css" />
<!-- <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"> -->
<!-- <meta http-equiv="X-UA-Compatible" content="IE=8"> -->
<!-- <meta http-equiv="Expires" content="0"> -->
<!-- <meta http-equiv="Pragma" content="no-cache"> -->
<!-- <meta http-equiv="Cache-control" content="no-cache"> -->
<!-- <meta http-equiv="Cache" content="no-cache"> -->
<script src="js/jquery-1.8.0.js"></script>
<script src="js/string_format.js"></script>
<script src="js/clipboard.min.js"></script>
<script src="js/jsbn/jsbn.js"></script>
<script src="js/jsbn/jsbn2.js"></script>
<script src="js/jsbn/prng4.js"></script>
<script src="js/jsbn/rng.js"></script>
<script src="js/jsbn/rsa.js"></script>
<script src="js/jsbn/rsa2.js"></script>
<script src="js/crypto/rollups/aes.js"></script>
<script>
//判断是否需要重新登陆
if(sessionStorage.getItem('aes_key_valid') != 1)
{
	location.href='login.php';
}
//提交表单时加密内容
function FormSubmit()
{
	if(sessionStorage.getItem('aes_key_valid') != 1)
	{
		location.href='login.php';
		return;
	}
	
	var input_set = document.getElementsByTagName("input");

	var rsa = new RSAKey();
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
			if(input_set[i].type == "password" && input_set[i].value != "")
			{
				//对要加密存储的内容使用客户端根据用户信息生成的密钥进行AES加密
				input_set[i].value = CryptoJS.AES.encrypt(input_set[i].value, key, { iv: iv, mode: CryptoJS.mode.CBC, padding: CryptoJS.pad.Pkcs7 });
			}
			input_set[i].value = rsa.encrypt(input_set[i].value);
		}
	}
	document.getElementById('new_record').submit();
}
//注销
function LogOut()
{
	sessionStorage.clear();
    var keys = document.cookie.match(/[^ =;]+(?=\=)/g);  
    if(keys) {  
        for(var i = keys.length; i--;)  
            document.cookie = keys[i] + '=0;expires=' + new Date(0).toUTCString()  
    }
    location.href='login.php';
}
function getStore(c_name)
{
	return sessionStorage.getItem(c_name);
}
//解密双方使用的AES密钥
function DescryptAESKey()
{
	var rsa = new RSAKey();
	rsa.setPrivateEx(getStore('rsa_n'), getStore('rsa_e'), getStore('rsa_d'), getStore('rsa_p'), getStore('rsa_q'), getStore('rsa_dmp1'), getStore('rsa_dmq1'), getStore('rsa_coeff'));
	sessionStorage.setItem('public_aes_key', rsa.decrypt(document.getElementById('public_aes_key').value));
	if(getStore('public_aes_key').length != 32)
	{
		location.href='login.php';
	}
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

		//提交创建表单后删除创建表单页面
		$("#new_record").on("click", "[name='submit']", function(){
			$("#new_record").remove();
		});

		//点击加密复选框时动态改变输入框类型
		$("#new_record").on("click", "[type=checkbox]", function(){
			
			$(this).val($(this).attr("checked") == "checked" ? 1 : 0);
			document.getElementById($(this).parent().parent().find("[name^=value]").attr("id")).type = $(this).val() == 1 ? "password" : "text";
		});

		//搜索文本框按键事件
		$("#key_word").keyup(function(){
			if($(this).val().length >= 1)
			{
				AJAXSearch($(this).val());
			}
		});
	});

	//AJAX展示搜索结果
	function AJAXSearch(key_word)
	{
		var xmlhttp;
		if(window.XMLHttpRequest)	// code for IE7+, Firefox, Chrome, Opera, Safari
		{
			xmlhttp = new XMLHttpRequest();
		}
		else	// code for IE6, IE5
		{
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange = function()
		{
			if(xmlhttp.readyState == 4 && xmlhttp.status == 200)
			{
				document.getElementById("post_holder").innerHTML = xmlhttp.responseText;
				if(document.getElementById("accordion"))
				{
					DynamicBind();
					DecryptFirst();
				}
		  	}
		}
		xmlhttp.open("GET", "search_if.php?s=" + key_word, true);
		xmlhttp.send();
	}
</script>
<script>
//展示记录时解密的相关函数
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
	//在解密前隐藏表单数据
	document.getElementById("accordion").style.visibility = "hidden";
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
	//在解密后再显示表单数据，否则会把解密前数据显示出来，影响美观．利用setTimeout隐藏撕裂效果
	setTimeout('document.getElementById("accordion").style.visibility = "visible"', 100);
}
$(document).ready(function(){
	$("#decrypt").click(function(){
				DecryptRecords();
			});
});
</script>
</head>


<body class="home blog">
<?php
require_once("load.php");
$arr = user_shell($_SESSION['user_id'] , $_SESSION['user_shell']);
user_mktime($_SESSION['times']);

//输出公钥到浏览器
echo '<input type="hidden" id="publickey_e" value="' . $_SESSION['publickey']['e']. '">';
echo '<input type="hidden" id="publickey_n" value="' . $_SESSION['publickey']['n']. '">';

//输出使用浏览器生成的公钥加密后的AES钥匙
$rsa_encrypt = new Crypt_RSA();
$key['n'] = new Math_BigInteger($_SESSION['client_public_n'], 16);
$key['e'] = new Math_BigInteger($_SESSION['client_public_e'], 16);
$rsa_encrypt->loadKey($key, CRYPT_RSA_PUBLIC_FORMAT_RAW);
$rsa_encrypt->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
$encrypted_key = bin2hex($rsa_encrypt->encrypt($_SESSION['aeskey']));
echo '<input type="hidden" id="public_aes_key" value="' . $encrypted_key. '">';

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
			<ul class="menu"><li><a href="index.php">首页</a></li>
				<li><a href="index.php?type=import">导入</a></li>
				<li><a href="export.php" target="_blank">导出</a></li>
				<li><a href="javascript:LogOut();">注销</a></li>
				<li><a href="index.php?about">关于</a></li>
			</ul></div> <!--/menu-->

		<div id="searchform">
			<form role="search" method="get" action="">
				<input type="text" class="searchtext" value="" id="key_word" name="s">
				<input type="submit" class="searchbutton" value=" ">
			</form>
		</div> <!--/searchform-->
		<div class="clearfix"></div>
	</div>
	

	<div id="content" style="min-height:917px">
		<div class="post" id="post_holder">
<?php
if($_POST['_post_type'] == "new_record")
{
	require_once('edit.php');
	
	$form_data = ParseFormData($_POST, $rsa_decrypt);
	
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
		
		//刷新已缓存的记录
		CacheRecords($_SESSION['user_id']);
	}
}
else if($_POST['_post_type'] == "edit_record")
{
	require_once('edit.php');
	
	$form_data = ParseFormData($_POST, $rsa_decrypt);
	
	if(!$form_data)
	{
		return;
	}
	
	//检查原有表单是否存在
	$_POST['old_name'] = $aes->decrypt(base64_decode($_POST['old_name'])); 
	
	$query = sprintf("select * from idpass_secret where user_id = %d and record = '%s'", $_SESSION['user_id'], $_POST['old_name']);
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	if(!is_array($row))
	{
		echo "<h1>要编辑的记录不存在</h1>";
		return;
	}
	
	//删除原有数据
	$query = sprintf("delete from idpass_secret where user_id = %d and record = '%s'", $_SESSION['user_id'], $_POST['old_name']);
	$result = mysql_query($query);
	
	if($result != true)
	{
		echo $query;
		echo "<h1>删除记录失败</h1>";
		return;
	}
	
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
	
	CacheRecords($_SESSION['user_id']);
}
else if($_GET['type'] == "new")
{
	//显示新建记录页面
	echo "<script>form_items=4;</script>";
	include("assets/components/new_record.html");
}
elseif($_GET['type'] == "show")
{
	require_once("show.php");
	ShowRecords($_SESSION['user_id']);
}
elseif($_GET['type'] == "edit")
{
	//编辑记录
	require_once("edit.php");
	EditRecord($_SESSION['user_id'], rsa_decrypt($rsa_decrypt, urldecode($_GET['name'])));
}
elseif($_GET['type'] == "deleterecord")
{
	//删除记录
	$record_name = htmlentities(addslashes(rsa_decrypt($rsa_decrypt, urldecode($_GET['name']))));
	$query = sprintf("delete from idpass_secret where user_id = %d and record = '%s'", $_SESSION['user_id'], $record_name);
	mysql_query($query);
	echo '<script>self.location="?type=show";</script>';
	exit;
}
elseif($_GET['type'] == "import")
{
	//导入记录
	include('assets/components/import.html');
	echo <<<STR
<script>
function SubmitByEnter()
{
    if(event.keyCode == 13)
    {
       	//根据用户信息生成AES密钥
		document.getElementById('password').value = CryptoJS.MD5(document.getElementById("username").value + document.getElementById("password").value + "3.141592653589793238462643383");
		var rsa = new RSAKey();
		rsa.setPublic(document.getElementById('publickey_n').value, document.getElementById('publickey_e').value);
		document.getElementById('username').value = "";
		document.getElementById('password').value = rsa.encrypt(document.getElementById('password').value);
		document.getElementById('import_record').submit();
    }
}
</script>
STR;
}
elseif($_GET['s'])
{
	//搜索
	require_once('search.php');
	$key_word = htmlentities(addslashes($_GET['s']));
	Search($_SESSION['user_id'], $key_word);
	echo '<script>DecryptFirst();</script>';
}
elseif(isset($_GET['about']))
{
	include('assets/components/introduction.html');
}
else 
{
	
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
DescryptAESKey();
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

$(document).ready(function() {
	DynamicBind();
});

function DynamicBind() {
	//---- 动态菜单 ----
	var Accordion = function(el, multiple) {
		this.el = el || {};
		this.multiple = multiple || false;

		// Variables privadas
		var links = this.el.find('.link');
		// Evento
		links.on('click', {el: this.el, multiple: this.multiple}, this.dropdown)
	}

	Accordion.prototype.dropdown = function(e) {
		var $el = e.data.el;
			$this = $(this),
			$next = $this.next();

		$next.slideToggle();
		$this.parent().toggleClass('open');

		if (!e.data.multiple) {
			$el.find('.submenu').not($next).slideUp().parent().removeClass('open');
		};
	}	

	var accordion = new Accordion($('#accordion'), false);
	//----
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
}
</script>
</body>
</html>