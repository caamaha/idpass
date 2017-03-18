<?php

require_once('load.php');

function CheckInput($value)
{
	$value = htmlentities(mysql_real_escape_string($value), ENT_QUOTES);
	return $value;
}

function ParseFormData($post, $rsa)
{
	$form_data['recordname'] = CheckInput(rsa_decrypt($rsa, $post['recordname']));
	
	foreach($post as $name => $value)
	{
		if(preg_match("/^name(\d+)$/", $name, $matches))
		{
			if($post["value".$matches[1]])
			{
				$_name  = $post["name".$matches[1]];
				$_value = $post["value".$matches[1]];
				if(empty($_name) || empty($_value))
				{
					continue;
				}
				$_name  = CheckInput(rsa_decrypt($rsa, $post["name" .$matches[1]]));
				$_value = CheckInput(rsa_decrypt($rsa, $post["value".$matches[1]]));
				
				if(strlen($_name) == 0 || strlen($_value) == 0)
				{
					continue;
				}
				if($post["encrypt".$matches[1]] == "1")
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
			echo '<h1>' . $name . '内容不能为空</h1>';
			$check = 0;
			return false;
		}
	}
	
	return $form_data;
}

function EditRecord($user_id, $record_name)
{
	$query = sprintf("select name, value, encrypt from idpass_secret where user_id = %d and record = '%s'", $_SESSION['user_id'], CheckInput($record_name));
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	if(is_array($row))
	{
		$template =  <<<STR
<script>
function DecryptRecord()
{
	if(sessionStorage.getItem('aes_key_valid') != 1)
	{
		location.href='login.php';
		return;
	}
	var input_set = document.getElementsByTagName("input");
	var key = CryptoJS.enc.Utf8.parse(sessionStorage.getItem('aes_key')); 
	var iv  = CryptoJS.enc.Utf8.parse('1234567812345678'); 
	//遍历得到要解密的表单内容
	for(var i = 0; i < input_set.length; i++)
	{
		if(input_set[i].id.indexOf("input-") == 0)
		{
			if(input_set[i].type == "password")
			{
				//对加密存储的内容使用客户端根据用户信息生成的密钥进行AES解密
				input_set[i].value = CryptoJS.AES.decrypt(input_set[i].value, key, { iv: iv, mode: CryptoJS.mode.CBC, padding: CryptoJS.pad.Pkcs7 }).toString(CryptoJS.enc.Utf8);;
			}
		}
	}
}
</script>
<form id="new_record" class="slick-form" method="post" action="">
	<input type="hidden" name="_post_type" value="edit_record">
	<input type="hidden" name="old_name" value="%s">
	<div class="input-fields">
		<label>username</label><input type="text" id="input-formname" name="recordname" class="placeholder" placeholder="表名" autocomplete="off" value="%s">
        <a href="####" class="plus-button">+</a>
	</div>
STR;
		echo sprintf($template, $record_name, $record_name);
		$items = 1;
		do
		{
			$template = <<<STR
	<div class="input-fields">
		<input type="text" id="input-name%d" name="name%d" class="placeholder" placeholder="段名" autocomplete="off" value="%s">
		<input style="display:none">
		<input type="%s" id="input-value%d" name="value%d" class="placeholder" placeholder="内容" autocomplete="off" value="%s">
		<div class="checkbox-holder">
			<input type="checkbox" %s id="checkbox-encrypt%d" name="encrypt%d" value="%d"><label for="checkbox-encrypt%d"></label>
		</div>
        <a href="####" class="plus-button">+</a>
	</div>
STR;
			echo sprintf($template, $items, $items, $row[0], ($row[2] == 1) ? 'password' : 'text', $items, $items, $row[1], ($row[2] == 1) ? 'checked=1' : '', $items, $items, $row[2], $items);
			$items++;
		} while($row = mysql_fetch_array($result));
		echo '<br><input type="button" onclick="FormSubmit()" value="更新"/></form>';
		echo '<script>DecryptRecord();</script>';
	}
}

?>