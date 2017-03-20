<?php

require_once('load.php');
require_once('search.php');

if($_GET['s'])
{
	$key_word = htmlentities(addslashes($_GET['s']));
	Search($_SESSION['user_id'], $key_word);
}

?>