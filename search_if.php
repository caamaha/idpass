<?php

require_once('load.php');
require_once('search.php');

if($_GET['s'])
{
	//搜索
	$key_word = htmlentities(addslashes($_GET['s']));
	Search($_SESSION['user_id'], $key_word);
}
elseif($_GET['w'])
{
	//改变显示权重
	
}

?>