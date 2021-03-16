<?php
error_reporting(E_ALL);
ini_set("display_errors", true);
echo "<br>Starting Test.";
include('Net/SFTP.php');

$sftp = new Net_SFTP('10.0.40.25', 22);
if (!$sftp->login('MaxiDi', 'hYjR7xlmOFRe')) {
    exit('Login Failed');
}
echo "<pre>";
//print_r($sftp->nlist());
echo "</pre>";
$sftp->chdir("OUT");
echo "<pre>";
//print_r($sftp->nlist());
echo "</pre>";
$do = $sftp->get('10_PFS_TRANSATO_20141114.OK', "/www/zendsvr/test.ok");
echo $sftp->getLastSFTPError();
//echo $sftp->size('VENDFIDE_20161025_10300-test0.ZIP');
//print_r($sftp->stat('VENDFIDE_20161025_10300-test0.ZIP'));
//print_r($sftp->lstat('VENDFIDE_20161025_10300-test0.ZIP'));
if ($do==False) {
	exit('Download non effettuato');
}
// puts a three-byte file named filename.remote on the SFTP server
//$sftp->put('filename.remote', 'xxx');
// puts an x-byte file named filename.remote on the SFTP server,
// where x is the size of filename.local
//$sftp->put('filename.remote', 'filename.local', NET_SFTP_LOCAL_FILE);
?>
