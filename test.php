<?php

$key_word = "abc efg hij-k 鸟12 3  ";
$key_word = trim($key_word);

preg_match("/(.* )*(.*)/", $key_word, $matches);

var_dump($matches);

?>