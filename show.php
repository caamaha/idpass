<?php

function GetRecords($user_id)
{
	$query = "select * from idpass_users where id = '$user_id'";
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	if(!is_array($row))
	{
		return false;
	}
	$user_name = $row['username'];
	$records = array();
	
	//查询所有记录
	$query = sprintf("select distinct record from idpass_secret where user_id = %d", $user_id);
	$records_result = mysql_query($query);
	$records_row = mysql_fetch_array($records_result);
	if(is_array($records_row))
	{
		do
		{
			$record_name = $records_row[0];
			$one_record = array($record_name);
				
			$query = sprintf("select name, value, encrypt from idpass_secret where user_id = %d and record = '%s'", $user_id, $record_name);
			$result = mysql_query($query);
			$row = mysql_fetch_array($result);
			if(is_array($row))
			{
				do
				{
					array_push($one_record, $row[0], $row[1], $row[2]);
				} while($row = mysql_fetch_array($result));
			}
			array_push($records, $one_record);
		} while($records_row = mysql_fetch_array($records_result));
	}
	
	return $records;
}

function ShowRecords($user_id)
{
	//显示所有记录
	$records = GetRecords($user_id);
	if(!$records)
	{
		echo '<h1>无记录</h1>';
		return false;
	}
	
	echo <<<STR
<script>
function DecryptValue(val)
{
	var key = CryptoJS.enc.Utf8.parse(sessionStorage.getItem('aes_key')); 
	var iv  = CryptoJS.enc.Utf8.parse('1234567812345678'); 
	return CryptoJS.AES.decrypt(val.toString(), key, { iv: iv, mode: CryptoJS.mode.CBC, padding: CryptoJS.pad.Pkcs7 }).toString(CryptoJS.enc.Utf8);;
}
$(document).ready(function(){
	$("#decrypt").click(function(){
				DecryptRecords();
			});
});
</script>
<link rel="stylesheet" href="css/show.css" type="text/css" />
<div class="show-holder"><ul id="accordion" class="accordion">
STR;
	
	foreach($records as $record)
	{
		$txt .= '<li><div class="link">' . $record[0] . '<a href="?type=deleterecord&name=' . $record[0] . '" name="delete_record">删除</a><a href="####">编辑</a></div>';
		$txt .= '<ul class="submenu"><table>';
		$lines = (count($record) - 1) / 3;
		for($i = 1; $i <= $lines; $i++)
		{
			$txt .= '<tr>';
			$txt .= sprintf('<td width="200 px"><a href="####">%s</a></td><td><a href="####" encrypted="%d">%s</a></td>', $record[$i*3-2], $record[$i*3], $record[$i*3-1]);
			$txt .= '</tr>';
		}
		$txt .= '</table></ul></li>';
	}
	echo $txt;
	echo <<<STR
</div></ul>
<button id="cpbtn" hidden></button>
<script src="js/show.js"></script>
<script>
	$(document).ready(function(){
		$("#accordion").on("click", "a", function(){
			if($(this).attr("encrypted") == "1")
			{
				$(this).text(DecryptValue($(this).text()));
				$(this).attr("encrypted", "0");
			}
			window._clipboard_text = $(this).text();
			$("#cpbtn").click();
		});
	});
	
</script>
STR;

}

?>