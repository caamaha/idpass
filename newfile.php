<html>
<head>
<meta charset="utf-8">
<script src="js/jsbn/jsbn.js"></script>
<script src="js/jsbn/jsbn2.js"></script>
<script src="js/jsbn/prng4.js"></script>
<script src="js/jsbn/rng.js"></script>
<script src="js/jsbn/rsa.js"></script>
<script src="js/jsbn/rsa2.js"></script>

<script>
function getCookie(c_name)
{
	return sessionStorage.getItem(c_name);
}

function setCookie(c_name, value)
{
	sessionStorage.setItem(c_name, value);
}

function load()
{
	var rsa = new RSAKey();
	
	rsa_n = getCookie('rsa_n')
	if(rsa_n == null)
	{
		rsa.generate(1024, '10001');
		setCookie('rsa_n', rsa.n.toString(16));
		setCookie('rsa_e', rsa.e.toString(16));
		setCookie('rsa_d', rsa.d.toString(16));
		setCookie('rsa_p', rsa.p.toString(16));
		setCookie('rsa_q', rsa.q.toString(16));
		setCookie('rsa_dmp1', rsa.dmp1.toString(16));
		setCookie('rsa_dmq1', rsa.dmq1.toString(16));
		setCookie('rsa_coeff', rsa.coeff.toString(16));
	}
	else
	{
		rsa.setPublic(rsa_n, getCookie('rsa_e'));
	}
	
	

	// document.write('<br>JS public key(HEX):<br>');
// 	rsa.setPublic(rsa.n.toString(16), rsa.e.toString(16));
	document.getElementById("client_public_n").value = rsa.n.toString(16);
	document.getElementById("client_public_e").value = rsa.e.toString(16);



	// document.write('<br>JS encrypt result:<br>');
// 	var res = rsa.encrypt('Matt');
	// document.write(res + '<br>');

	// res = rsa.decrypt('5c5a4bed92460e5b4460724129b19abe20566736caa5f370161a6563bdf9a8f3dac807a51f5830c29e35ce24408b965b85955b5dc89828b6e52c1ba491c6fb87e90b024361273d7b0cbf8c7a8720a537bf8e93851cdd4f0197fb9ece648e5714dcce3ffa69934fcabb61834d1860e70064989b05ecda1129ad902ce818f9198e');
	// document.write(res + '<br>');
}


function js_decrypt()
{
	var rsa = new RSAKey();
	rsa.setPrivateEx(getCookie('rsa_n'), getCookie('rsa_e'), getCookie('rsa_d'), getCookie('rsa_p'), getCookie('rsa_q'), getCookie('rsa_dmp1'), getCookie('rsa_dmq1'), getCookie('rsa_coeff'));
	var res = rsa.decrypt(document.getElementById("cip").value);
	document.getElementById("js_out").value = res;
}

function js_clear()
{
	sessionStorage.clear();
}

</script>
</head>

<body onload="load()">
	<form id="form" action="" method="post">
		<input type="hidden" id="client_public_n" name="client_public_n" value=""><br>
		<input type="hidden" id="client_public_e" name="client_public_e" value=""><br>
		原文：<input type="text" name="content" value="Matt"/><br>
		js解密：<input type="text" id="js_out" value=""/><br>
		<input type="submit" name="Submit" value="php加密"/><br>
		<input type="button" onclick="js_decrypt()" value="js解密"/><br>
		<input type="button" onclick="js_clear()" value="清除cookie"/><br>
	</form>
	
	<?php
//包含phpseclib库
set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');

require_once('Crypt/RSA.php');

//生成一对RSA钥匙
define('CRYPT_RSA_MODE', CRYPT_RSA_MODE_INTERNAL);

if($_POST['Submit'])
{
	$rsa = new Crypt_RSA();
// 	$mykey['n'] = new Math_BigInteger('727b94a207d83d183e3cb1d5125b1d6286df599d8f9329482132a3f57b214a5aa9533f87615bbd802879915e823f6a9d61a5034ce3d5d0b8d22e08ee72adc6053a780bd31e35b6a18d8a64adcbf6983e79a5d3a19f8ff54097bf93d4a4271f1be65e0712e57ac38a62c19ff21d817ec1dc149862b0e02177b8f0dd84bd3c8c73', 16);
// 	$mykey['e'] = new Math_BigInteger('10001', 16);
	$mykey['n'] = new Math_BigInteger($_POST['client_public_n'], 16);
	$mykey['e'] = new Math_BigInteger($_POST['client_public_e'], 16);
	
	// var_dump($mykey);
	
	$rsa->loadKey($mykey, CRYPT_RSA_PUBLIC_FORMAT_RAW);
	$publickey = $rsa->getPublicKey(CRYPT_RSA_ENCRYPTION_PKCS1);
// 	echo $publickey . '<br>';
	// $rsa->loadKey('-----BEGIN PUBLIC KEY-----
	// MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCqGKukO1De7zhZj6+H0qtjTkVxwTCpvKe4eCZ0
	// FPqri0cb2JZfXJ/DgYSF6vUpwmJG8wVQZKjeGcjDOL5UlsuusFncCzWBQ7RKNUSesmQRMSGkVb1/
	// 3j+skZ6UtW+5u09lHNsj6tQ51s1SPrCBkedbNf0Tp0GbMJDyR4e9T04ZZwIDAQAB
	// -----END PUBLIC KEY-----');
	// $rsa->setPublicKey();
	// $publickey = $rsa->getPublicKey(CRYPT_RSA_ENCRYPTION_PKCS1);
	// echo $publickey . '<br>';
	// var_dump($publickey);
	$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
	$res = $rsa->encrypt($_POST['content']);
// 	echo $res .'<br>';
	$t = new Math_BigInteger(bin2hex($res), 16);
// 	echo 'PHP encrypt result:<br>';
// 	echo $t->toHex() . '<br>';
	echo 'php密文：<input type="text" id="cip" name="cip" value="'. $t->toHex(). '"/><br>';
}
	

// var_dump($res);
?>
	
	
	
</body>
</html>