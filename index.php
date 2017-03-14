<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>IDPass</title>
<link rel="stylesheet" type="text/css" href="css/layout.css"/>
<link rel="stylesheet" type="text/css" href="css/input_field.css" />
<script src="js/jquery-1.8.0.js"></script>
<script src="js/string_format.js"></script>
<script src="js/clipboard.min.js"></script>
<script src="js/jsbn/jsbn.js"></script>
<script src="js/jsbn/prng4.js"></script>
<script src="js/jsbn/rng.js"></script>
<script src="js/jsbn/rsa.js"></script>
<script src="js/crypto/rollups/aes.js"></script>
<script src="js/crypto/components/pad-zeropadding.js"></script>
<script>
//提交表单时加密内容
function FormSubmit()
{
	var rsa = new RSAKey();
	var input_set = document.getElementsByTagName("input");

	rsa.setPublic(document.getElementById('publickey_n').value, document.getElementById('publickey_e').value);
	
	//遍历得到要提交的表单内容
	for(var i = 0; i < input_set.length; i++)
	{
		if(input_set[i].id.indexOf("input-") == 0)
		{
			//对要提交的内容使用服务器的公钥进行RSA加密
			if(input_set[i].type == "password")
			{
				//对要加密存储的内容使用客户端根据用户信息生成的密钥进行AES加密
				//input_set[i].value = rsa.encrypt(input_set[i].value);
			}
			input_set[i].value = rsa.encrypt(input_set[i].value);
			
			input_set[i].style.display="none";
		}
	}

	document.getElementById('new_record').submit();
}
</script>
<script>
	var form_items = 4;
	$(document).ready(function(){
		//在新建表单时动态增加或减少表单项
		$("#new_record").on("click", ".new_item", function(){
			var new_item = '<section class="input_content input_session_new">\
								<span class="input input--juro">\
									<input class="input__field input__field--juro" type="text" id="input-name{0}" name="name{1}"/>\
									<label class="input__label input__label--juro" >\
										<span class="input__label-content input__label-content--juro">段名</span>\
									</label>\
								</span>\
								<span class="input input--juro">\
									<input class="input__field input__field--juro" type="text" id="input-value{2}" name="value{3}"/>\
									<label class="input__label input__label--juro" >\
										<span class="input__label-content input__label-content--juro">内容</span>\
									</label>\
								</span>\
								<span class="input"><input class="check_encrypt" name="encrypt{4}" type="checkbox" value="0" /></span>\
								<span class="input"><label class="new_item"><span class="input__label-content">+添加</span></label></span>\
							</section>';
			$(this).parent().parent().after(new_item.format(form_items, form_items, form_items, form_items, form_items++));
			$(this).parent().parent().next().fadeIn(300);
		});
		
		//输入框动态效果
		$("#new_record").on("focus", ".input__field", function(){
			$(this).parent().addClass("input--filled");
		});
		$("#new_record").on("blur", ".input__field", function(){
			if($(this).val() == '')
			{
				$(this).parent().removeClass("input--filled");
			}
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
			window._clipboard_text = $(this).attr("data-clipboard-text");
			$("#cpbtn").click();
		});

		//点击加密复选框时动态改变输入框类型
		$("#new_record").on("click", ".check_encrypt", function(){
			$(this).val($(this).attr("checked") == "checked" ? 1 : 0);
			document.getElementById($(this).parent().parent().find("[name^=value]").attr("id")).type = $(this).val() == 1 ? "password" : "text";
		});
	});
</script>
</head>


<body>

<?php
require_once("config.php");
$arr = user_shell($_SESSION['uid'] , $_SESSION['user_shell'], 4);
user_mktime($_SESSION['times']);
?>


<div id="outer">
	<div id="header"><h1>欢迎
	<?php echo $arr['username'];?>
	</h1></div>
	<div id="menu">
		<ul>
			<li class="first"><a href="index.php" accesskey="1" title="">IDPass</a></li>
			<li><a href="index.php" accesskey="2" title="">Import</a></li>
			<li><a href="index.php" accesskey="3" title="">Export</a></li>
			<li><a href="index.php" accesskey="4" title="">Search</a></li>
			<li><a href="index.php" accesskey="5" title="">About Us</a></li>
		</ul>
	</div>
	<div id="content">
		<div id="side_right_container">
			<div id="side_right">
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
	$query = sprintf("select * from info_%s where record = '%s'", $arr['username'], $form_data['recordname']);
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
				if($_encrypt == 1)
				{
					//需要根据用户shell加密数据再存储
					//$_value = aes_encrypt($_value, $_SESSION['user_shell'], ' ');
				}
				$query = sprintf("insert into info_%s(record, name, value, encrypt) values('%s', '%s', '%s', %d)",
									$arr['username'], $form_data['recordname'], $_name, $_value, $_encrypt);
				$result = mysql_query($query);
				$row = mysql_fetch_array($result);
				if($result == true)
				{
					echo "记录成功<br>";
				}
// 				else
// 				{
// 					echo $query.'<br>';
// 				}
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
}
elseif($_GET['type'] == "show")
{
	//显示所有记录
	$query = sprintf("select distinct record from info_%s", $arr['username']);
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
	$query = sprintf("select name, value from info_%s where record = '%s'", $arr['username'], urldecode($_GET['name']));
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	if(is_array($row))
	{
		echo '<ul>';
		do
		{
			$output = sprintf('<li><label>%s %s</label><button class="cpbtn btn" data-clipboard-text="%s"><img src="assets/images/clippy.svg" width="13"></button></li>', $row[0], $row[1], $row[1]);
			echo $output;
		} while($row = mysql_fetch_array($result));
		echo '</ul>';
	}
}
elseif($_GET['type'] == "deleterecord")
{
	$query = sprintf("delete from info_%s where record = '%s'", $arr['username'], urldecode($_GET['name']));
	mysql_query($query);
	echo '<script>self.location="?type=show";</script>';
	exit;
}
?>

			</div>
		</div>
		<div id="side_left">
		<h2><a href="?type=new">新建记录</a></h2>
		<h2><a href="?type=show">记录列表</a></h2>
		</div>
		<div class="clear"></div>
	</div>
	<div id="footer"><h3>Copyright © 2017 Soe</h3><h3><a href="http://www.miitbeian.gov.cn/">鄂ICP备17003963</a></h3></h3></div>
	
	<!-- 辅助复制到粘贴板 -->
	<button id="cpbtn" hidden></button>
</div>

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