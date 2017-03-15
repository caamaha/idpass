<?php
//包含phpseclib库
set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');

require_once('Crypt/AES.php');
include('Crypt/Random.php');

$text = "Message";
$key = md5($text);  //CuPlayer.com提示key的长度必须16，32位,这里直接MD5一个长度为32位的key

$iv='1234567812345678';

$cipher = new Crypt_AES(CRYPT_AES_MODE_CBC); // could use CRYPT_AES_MODE_CBC
// keys are null-padded to the closest valid size
// longer than the longest key and it's truncated
$cipher->setKeyLength(256);
$cipher->setKey($key);
// the IV defaults to all-NULLs if not explicitly defined
$cipher->setIV($iv);

// $plaintext = 'Message';

echo base64_encode($cipher->encrypt($text)) .'<br>';
// echo $cipher->decrypt($cipher->encrypt($plaintext));
// echo $cipher->decrypt(base64_decode('RJ2XtsHfnWs/79MGnVcEWg==')). '<br>';

// $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $text, MCRYPT_MODE_CBC, $iv);
// $decode = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $crypttext, MCRYPT_MODE_CBC, $iv);
// echo base64_encode($crypttext);
// echo "<br/>";
// echo $decode;
// echo "<br/>";


?>

<script src="js/crypto/rollups/aes.js"></script>
<script src="js/crypto/components/pad-zeropadding.js"></script>
<script>

var key_hash = CryptoJS.MD5("Message");
var key = CryptoJS.enc.Utf8.parse(key_hash); 
var iv  = CryptoJS.enc.Utf8.parse('1234567812345678'); 
var encrypted = CryptoJS.AES.encrypt("Message", key, { iv: iv, mode: CryptoJS.mode.CBC, padding: CryptoJS.pad.ZeroPadding }); 
var decrypted = CryptoJS.AES.decrypt(encrypted.toString(), key, { iv: iv, mode: CryptoJS.mode.CBC, padding: CryptoJS.pad.ZeroPadding });
document.write(decrypted.toString(CryptoJS.enc.Utf8)); 

</script>