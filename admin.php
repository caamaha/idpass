<?php
require_once('config.php');
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
	
	//新建user_list表
	$query = "create table user_list (uid INT NOT NULL AUTO_INCREMENT,
						username VARCHAR(255),
						password mediumtext,
						salt VARCHAR(8),
						PRIMARY KEY(uid))";
	echo $query. '<br>';
	$result = mysql_query($query);
	
	echo 'Database rebuilt.<br>';
	
}


?>