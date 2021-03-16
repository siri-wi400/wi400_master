<?php
$siteId = $settings['matomo_site_id'];
//$apiUrl = 'http://10.0.40.2/piwik/piwik.php';
$apiUrl = $settings['matomo_api_url'];
$sql = "SELECT * FROM ZSLOGSTS WHERE STATEXT=''";
require_once $routine_path.'/misc/PiwikTracker.php';
$result = $db->query($sql);
while ($row =$db->fetch_array($result)) {
	//if ($row['ZURI']!="") {
	$userId = $row['ZSUTE'];
	$piwik = new PiwikTracker($siteId, $apiUrl);
	$piwik->setIp($row['ZIPAD']);
	$piwik->setUserId($userId);
	$piwik->setCustomVariable(1, "session_id", $row['ZSESID']);
	// Date with the format 'Y-m-d H:i:s', or a UNIX timestamp.
	$d = $row['ZTIME'];
	$data =substr($d,0,4).'-'.substr($d,5,2).'-'.substr($d, 8, 2)." ".substr($d, 11, 2).":".substr($d,14,2).":".substr($d,17,2);
	$date = date_create($data);
	$piwik->setForceVisitDateTime(date_format($date, "U"));
	$piwik->setUserAgent($row['ZAGEN']);
	$piwik->setUrlReferrer($row['ZREFE']);
	$piwik->setBrowserLanguage($row['ZLANG']);
	$piwik->setResolution($row['ZBAS'], $row['ZALT']);
	$url =$settings['matomo_site_url'].$row['ZURI'];
	$piwik->setUrl($url);
	$piwik->setTokenAuth($settings['matomo_auth_token']);
	// Sends Tracker request via http
	$action = "ACTION";
	$parse_url = parse_url($url);
	if (isset($parse_url['query']) && $parse_url['query']!="") {
		parse_str($parse_url['query'], $output);
		if (isset($output['t'])) {
			$action = $output['t'];
		}
	}
	$piwik->doTrackPageView($action);
	if (!isset($stmt)) {
		$sql = "UPDATE ZSLOGSTS SET STATEXT='1' WHERE ZSUTE=? AND ZIPAD=? AND ZTIME=?";
		$stmt = $db->prepareStatement($sql);
	}
	$db->execute($stmt, array($row['ZSUTE'], $row['ZIPAD'], $row['ZTIME']));
	//}
}