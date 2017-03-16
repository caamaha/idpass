<?php
//包含phpseclib库
set_include_path(get_include_path() . PATH_SEPARATOR . 'php-archive');

require_once('php-archive/src/Tar.php');

$tar = new Tar();
$tar->create();
$tar->addFile('export/root.html');
$tar->save('export/myfile.tgz'); // compresses and saves it
echo $tar->getArchive(Archive::COMPRESS_GZIP); // compresses and returns it

?>