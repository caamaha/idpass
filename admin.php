<?php
require_once('load.php');
if($_GET['type'] == "clear")
{
	//删除相关表
	$query = 'drop table idpass_users;';
	$result = mysql_query($query);
	echo $query . '   ' .$result . '<br>';
	
	$query = 'drop table idpass_secret;';
	$result = mysql_query($query);
	echo $query . '   ' .$result . '<br>';
	
	//重建idpass_users表
	$query = "create table idpass_users (id INT NOT NULL AUTO_INCREMENT,
						username VARCHAR(255),
						password mediumtext,
						salt VARCHAR(8),
						PRIMARY KEY(id)) DEFAULT CHARSET=utf8";
	$result = mysql_query($query);
	echo $query . '   ' .$result . '<br>';
	
	//重建idpass_secret表
	$query = "create table idpass_secret (id INT NOT NULL AUTO_INCREMENT,
						user_id INT,
						record VARCHAR(255),
						name VARCHAR(255),
						value VARCHAR(65535),
						encrypt BOOLEAN,
						PRIMARY KEY(id)) DEFAULT CHARSET=utf8";
	$result = mysql_query($query);
	echo $query . '   ' .$result . '<br>';
	
	echo 'Database rebuilt.<br>';
	
}


?>