<?php
//	Radius of earth.  3959 miles or 6371 kilometers.  Must set radius to units you are using, in my case, miles.
//  A bearing of 0 is due north, 90 is due east, 180 due south, 270 due west, and anything in between.
//  Pass in coordinates in Decimal form.  Example: -41.5786214
function new_coords($latitude, $longitude, $bearing, $distance, $unit = 'm')
{

	if ($unit == 'm')
	{
		$radius = 3959;
	}
	elseif ($unit == 'km')
	{
		$radius = 6371;
	}

	//	New latitude in degrees.
	$new_latitude = rad2deg(asin(sin(deg2rad($latitude)) * cos($distance / $radius) + cos(deg2rad($latitude)) * sin($distance / $radius) * cos(deg2rad($bearing))));

	//	New longitude in degrees.
	$new_longitude = rad2deg(deg2rad($longitude) + atan2(sin(deg2rad($bearing)) * sin($distance / $radius) * cos(deg2rad($latitude)), cos($distance / $radius) - sin(deg2rad($latitude)) * sin(deg2rad($new_latitude))));

	//  Assign new latitude and longitude to an array to be returned to the caller.
	$coord['latitude'] = $new_latitude;
	$coord['longitude'] = $new_longitude;

	return $coord;

}
function getCoordsByIp($ip) {
	$ip_addr = $ip; //$_SERVER['REMOTE_ADDR'];
	$geoplugin = unserialize( file_get_contents('http://www.geoplugin.net/php.gp?ip='.$ip_addr) );
	
	if ( is_numeric($geoplugin['geoplugin_latitude']) && is_numeric($geoplugin['geoplugin_longitude']) ) {
	
		$lat = $geoplugin['geoplugin_latitude'];
		$long = $geoplugin['geoplugin_longitude'];
	}
	return $lat.','.$long;
}