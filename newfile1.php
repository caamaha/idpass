<?php
//包含phpseclib库
set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');

require_once('Crypt/RSA.php');

$rsa = new Crypt_RSA();
$rsa->privateKeyFormat = CRYPT_RSA_PRIVATE_FORMAT_PKCS1;
$rsa->publicKeyFormat = CRYPT_RSA_PUBLIC_FORMAT_PKCS1;

$keys = $rsa->createKey();
var_export($keys["privatekey"], $keys["publickey"]);

var_dump($keys["privatekey"]);

echo '<br>test<br>';

$rsa->loadKey($keys["privatekey"], CRYPT_RSA_PRIVATE_FORMAT_PKCS1);
var_export($rsa->getPrivateKey(CRYPT_RSA_PRIVATE_FORMAT_PKCS1));

$rsa->loadKey($keys["publickey"],CRYPT_RSA_PUBLIC_FORMAT_PKCS1);
$public_xml = $rsa->getPublicKey(CRYPT_RSA_PUBLIC_FORMAT_PKCS1);
?>