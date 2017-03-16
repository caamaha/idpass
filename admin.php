<?php
require_once('load.php');
if($_GET['type'] == "clear")
{
	//获取当前使用的数据库名
	$query = "SELECT DATABASE();";
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	
	//查询数据中的所有表
	$query = "SELECT CONCAT('drop table ',table_name,';') FROM information_schema.`TABLES` WHERE table_schema='$row[0]';";
	$result = mysql_query($query);
	
	//删除数据库中的所有表
	$row = mysql_fetch_array($result);
	do
	{
		mysql_query($row[0]);
		echo $row[0]. '<br>';
	} while($row = mysql_fetch_array($result));
	
	//新建idpass_users表
	$query = "create table idpass_users (id INT NOT NULL AUTO_INCREMENT,
						username VARCHAR(255),
						password mediumtext,
						salt VARCHAR(8),
						PRIMARY KEY(id)) DEFAULT CHARSET=utf8";
	echo $query. '<br>';
	$result = mysql_query($query);
	
	//新建idpass_secret表
	$query = "create table idpass_secret (id INT NOT NULL AUTO_INCREMENT,
						user_id INT,
						record VARCHAR(255),
						name VARCHAR(255),
						value VARCHAR(65535),
						encrypt BOOLEAN,
						PRIMARY KEY(id)) DEFAULT CHARSET=utf8";
	
	echo $query. '<br>';
	$result = mysql_query($query);
			
	
	echo 'Database rebuilt.<br>';
	
}


?>