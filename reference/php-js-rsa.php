<?php
//php和javascript兼容的RSA加解密
//引用自http://bestmike007.com/2011/08/secure-data-transmission-between-pure-php-and-javascript-using-rsa/

//包含phpseclib库
set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');

include('Crypt/RSA.php');


// require_once('Crypt/RSA.php');
// define('CRYPT_RSA_MODE', CRYPT_RSA_MODE_INTERNAL);
// $rsa = new Crypt_RSA();
// $rsa->setPublicKeyFormat(CRYPT_RSA_PUBLIC_FORMAT_RAW);
// $key = $rsa->createKey(512);
// echo $key['privatekey'];
// echo "\n";
// $e = new Math_BigInteger($key['publickey']['e'], 10);
// $n = new Math_BigInteger($key['publickey']['n'], 10);
// echo "Public Key:\n";
// echo $e->toHex();
// echo "\n";
// echo $n->toHex();


define("KEY_PRIVATE", "-----BEGIN RSA PRIVATE KEY-----
MIIBOQIBAAJBAIpfTU+n3XjKhTm6i5WBswyc4E4ZmM2IHVJ5IhmEvGBuLH0zaNwYSzV1B5ZqDyCT
C6ZlzZ6RTWsLZ8hjb/6MrP0CAwEAAQJAAlK9TTln9No5nbwtvHHesWHaO5V0b6b5ubkXmHlrtuwR
nnNLGT9wqtIyP830/njo3qMFSIFKYGIErt+bSxEgBQIhAK5LTM2u2AudTUb6l1pi8qypXf7UHGUQ
bTxqPZaeh4gHAiEAyz0Wt0emBEieUDw7D4g3IXCb36cJcqDJ0OOz9rAwedsCIEV7QzzjrMDEjp/z
Gg8wTunCAvSpfkBT0hg5ih/XRtRVAiAQXVnf5iADBknhEgh7Zq9xvNyANLX5CeNWM4+BFIzCswIg
dGr1KW1fmIGJXoJ8qbFUbY7Bgk+cEc0kf2GvudfGQ5k=
-----END RSA PRIVATE KEY-----");

function decrypt($msg) {
	$rsa = new Crypt_RSA();
	$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
	$rsa->loadKey(KEY_PRIVATE, CRYPT_RSA_PRIVATE_FORMAT_PKCS1);
	$s = new Math_BigInteger($msg, 16);
	return $rsa->decrypt($s->toBytes());
}
	if(isset($_POST['submit'])) {
		echo decrypt($_POST['enc_text']);
	}
?>

<script type="text/javascript" src="js/jsbn/jsbn.js"></script>
<script type="text/javascript" src="js/jsbn/prng4.js"></script>
<script type="text/javascript" src="js/jsbn/rng.js"></script>
<script type="text/javascript" src="js/jsbn/rsa.js"></script>
<script>
function encrypt() {
	var rsa = new RSAKey();
	rsa.setPublic('8a5f4d4fa7dd78ca8539ba8b9581b30c9ce04e1998cd881d5279221984bc606e2c7d3368dc184b357507966a0f20930ba665cd9e914d6b0b67c8636ffe8cacfd', '10001');
	document.getElementById('enc_text').value = rsa.encrypt(document.getElementById('plaintext').value);
}
</script>
<form action="" method="post">
Plain Text:<br/>
<input id='plaintext' type="text" size="40" value="test"/><br/>
<input type="button" onclick="encrypt()" value="Encrypt"/><br/>
Encrypted Text:<br/>
<input id="enc_text" name='enc_text' type="text" size="40"/><br/>
<input name="submit" type="submit" value="Submit" size="10"/>
</form>