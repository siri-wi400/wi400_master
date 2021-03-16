<?php
define ("ON", "on");
define ("OFF", "off");
define ("ERROR", -1);


/* Return values for all functions
 1 - User passed filter.
 0 - User did not pass filter */
class wi400Control
{
	public $settings;

	public function __construct($config_file = "conf/wi400Control.conf")
	{
		$this->settings = @parse_ini_file($config_file, TRUE) or exit("// [WI400] ERROR: Impossibile leggere il file di configurazione($config_file) */");
	}

	public function rec_repeat()
	{
		if (isset($this->settings['rec_repeat_always']) &&  $this->settings['rec_repeat_always'] == ON) return 1;
		return 0;
	}

	public function day_filter()
	{
		if (isset($this->settings['day']) && $this->settings['day'] != OFF)
		{
			$this->settings['day'] = str_replace(" ","",$this->settings['day']);
			$dayArr = explode(",", $this->settings['day']);
			if (!in_array(date("N"), $dayArr)) return 0;
		}
		return 1;
	}

	public function time_filter()
	{
		if (isset($this->settings['time']) && $this->settings['time'] != OFF)
		{
			$time_filter = strtolower($this->settings['time']);
			if ($time_filter != "record" && $time_filter != "norecord") exit("// [ClickTale] ERROR: Invalid setting for [time]. */");
			if (isset($this->settings['time_start']) && isset($this->settings['time_stop']))
			{
				if (isset($this->settings['time_zone'])) date_default_timezone_set($this->settings['time_zone']);
				$time_stop = strtotime($this->settings['time_stop']);
				$time_start = strtotime($this->settings['time_start']);
				/* if ($this->settings['time_start'] > $this->settings['time_stop']) {
				 $time_stop = mktime(date("H",$time_stop),date("i",$time_stop),0,date("n"),date("j")+1,date("Y"));
					} */
				$result  = (time()>$time_start && time()<$time_stop);
				if ($time_filter == "record" && !$result) return 0;
				if ($time_filter == "norecord" && $result) return 0;
			} else exit("// [ClickTale] ERROR: time_start/stop not set. */");
		}
		return 1;
	}

	public function country_filter()
	{

		if (isset($this->settings['country']['filter']) && $this->settings['country']['filter'] != OFF)
		{
			if ($this->bad_mode_setting($this->settings['country']['filter'])) exit("// [ClickTale] ERROR: Invalid setting for [country] \"filter\". */");
			if (!isset($_COOKIE[COUNTRY_COOKIE]))
			{
				if (isset($this->settings['country']['mode']) && strtolower($this->settings['country']['mode'])=="http")
				$country = getCountryIP(getIP());
				else if (isset($this->settings['country']['mode']) && strtolower($this->settings['country']['mode'])=="db")
				$country = getCountryDB(getIP(),$this->settings['country']['db_path']);
				else exit("// [ClickTale] ERROR: Invalid setting for [country] \"mode\". */");
				if ($country == "") $country = "xx";
				$country = strtolower($country);
				setcookie(COUNTRY_COOKIE, $country, time() + 3600*24*30, '/');
			} else $country = $_COOKIE[COUNTRY_COOKIE];
			$country_list = $this->settings['country_list'] or exit("// [ClickTale] ERROR: Countries not defined. */");
			if (strtolower($this->settings['country']['filter']) == "blacklist" && in_array($country, $country_list))
			return 0;
			if (strtolower($this->settings['country']['filter']) == "whitelist" && !in_array($country, $country_list))
			return 0;
		}
		return 1;
	}

	public function ip_filter($user)
	{
		$ip="ip";
		$filter="filter";
		$ip_list = "ip_list";
		if (isset($this->settings["ip_$user"])){
			$ip="ip_$user";
			$ip_list = "ip_list_$user";
		}

		if (isset($this->settings[$ip][$filter]) && $this->settings[$ip][$filter] != OFF)
		{
			if ($this->bad_mode_setting($this->settings[$ip][$filter])) exit("// [ClickTale] ERROR: Invalid setting for [ip] \"filter\". */");
			$list = $this->settings[$ip_list] or exit("// [ClickTale] ERROR: [ip_list] does not exist. */");
			foreach($list as $filterIP)
			{
				if  (($mask=substr_count($filterIP, "*")) == 0)
				{
					$filterIP = explode("/", $filterIP);
					$mask = $filterIP[1];
					$filterIP = $filterIP[0];
					if (substr_count($mask, ".") == 0) $mask = bytesToMask($mask);
					else $mask = ipToInt($mask);
				} else {
					if ($mask == 4) $mask = 0;
					else $mask = bytesToMask(32-8*$mask);
					$filterIP = str_replace("*","0",$filterIP);
				}
				$userIP = ipToInt(getIP());
				$filterIP = ipToInt($filterIP);
				if ($mask == 0) // No mask supplied
				$match = (intval($filterIP) == intval($userIP));
				else
				$match = (intval($filterIP&$mask) == intval($userIP&$mask));
				if ($match != 0)
				{
					break;
				}
			}
			if (strtolower($this->settings[$ip][$filter]) == "blacklist" && $match) return 0;
			if (strtolower($this->settings[$ip][$filter]) == "whitelist" && empty($match)) return 0;
		}
		return 1;
	}

	public function ref_filter()
	{
		if (isset($this->settings['referer']['filter']) && $this->settings['referer']['filter'] != OFF)
		{
			$result = $this->refURL_filter($_GET['ref'],$this->settings['referer'],$this->settings['ref_list']);
			if ($result == ERROR) exit("// [ClickTale] ERROR: Referer rule not configured correctly. */");
			else return $result;
		}
		return 1;
	}

	public function url_filter()
	{
		if (isset($this->settings['url']['filter']) && $this->settings['url']['filter'] != OFF)
		{
			$callpage = (!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] :
			((!empty($_ENV['HTTP_REFERER'])) ? $_ENV['HTTP_REFERER'] : @$HTTP_REFERER);
			$result = $this->refURL_filter($callpage,$this->settings['url'],$this->settings['url_list']);
			if ($result == ERROR) exit ("// [ClickTale] ERROR: URL rule not configured correctly. */");
			else return $result;
		}
		return 1;
	}
		
	private function refURL_filter($user_ref, $settings, $list)
	{
		$filter_type = strtolower($settings['filter']);
		$mode = strtolower($settings['mode']);
		if ($this->bad_mode_setting($filter_type)) return ERROR;
		if (empty($list)) return ERROR;
		if (!empty($mode) && $mode == "simple")
		{
			foreach ($list as $r)
			{
				if ($r != "") $matches = substr_count($user_ref, trim($r));
				if ($matches > 0) break;
			}
		} else if (!empty($mode) && $mode == "regex")
		{
			foreach ($list as $pattern)
			{
				if ($pattern != "") $matches = preg_match($pattern, $user_ref);
				if ($matches > 0) break;
			}
				
		} else return ERROR;
		if ($filter_type == "whitelist" && $matches == 0) return 0; // Not in the Whitelist.
		if ($filter_type == "blacklist" && $matches >  0) return 0; // the Ref-URL is Blacklisted.
		return 1;
	}

	public function bad_mode_setting($setting)
	{
		if (strtolower($setting) != "blacklist" && strtolower($setting) != "whitelist") return 1;
		return 0;
	}

	public function filter_all()
	{
		if (!$this->day_filter()) return 0;
		if (!$this->time_filter()) return 0;
		if (!$this->url_filter()) return 0;
		if (!$this->ref_filter()) return 0;
		if (!$this->ip_filter()) return 0;
		if (!$this->country_filter()) return 0;
		return 1;
	}

	// Debug function.
	public function print_status()
	{
		echo "/* ClickTale Control Script Debug\n";
		echo " * Version: ".CT_VERSION." ".CT_VERSIION_DATE."\n";
		// 1 - passed filter, 0 - did not pass filter.
		echo " * Day: ".$this->day_filter()."\n";
		echo " * Time: ".$this->time_filter()."\n";
		echo " * Country: ".$this->country_filter()."\n";
		echo " * IP: ".$this->ip_filter()."\n";
		echo " * Referer: ".$this->ref_filter();
		if (isset($_GET['ref'])) echo " [".$_GET['ref']."]";
		echo "\n";
		echo " * URL: ".$this->url_filter()." [".$_SERVER['HTTP_REFERER']."]\n";
		echo " */\n";
	}
}

function getIP()
{
	//$ip = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] :
	//	 ((!empty($_ENV['REMOTE_ADDR'])) ? $_ENV['REMOTE_ADDR'] : @$REMOTE_ADDR);
	if (getenv("HTTP_CLIENT_IP")) $ip = getenv("HTTP_CLIENT_IP");
	else if(getenv("HTTP_X_FORWARDED_FOR")) $ip = getenv("HTTP_X_FORWARDED_FOR");
	else if(getenv("REMOTE_ADDR")) $ip = getenv("REMOTE_ADDR");
	else $ip = "0.0.0.0";

	return trim($ip);
}

function ipToInt($ip)
{
	$ip = explode(".", $ip);
	return ($ip[3] + 256*$ip[2] + 256*256*$ip[1] + 256*256*256*$ip[0]);
}

function bytesToMask($bytes)
{
	$mask = str_pad("", $bytes, "1");
	$mask = str_pad($mask, 32, "0");
	$mask = str_split($mask, 8);
	/*$mask = rtrim(chunk_split($mask, 8, "."),".");
		$mask = explode(".", $mask); // PHP4 */

	for ($i=0; $i<4; $i++)
	$mask[$i]=base_convert($mask[$i], 2, 10);
	return ipToInt(implode(".",$mask));
}

function getCountryIP($ip)
{
	ob_start();
	readfile("http://api.hostip.info/country.php?ip=$ip");
	$country = ob_get_contents();
	ob_end_clean();
	$country = strtolower($country);
	return trim($country);
}

/* This product includes GeoLite data created by MaxMind, available from http://www.maxmind.com/ */
function getCountryDB($ip, $path)
{
	require_once($path."geoip.inc");
	$gi = geoip_open($path."GeoIP.dat",GEOIP_STANDARD);
	$country = geoip_country_code_by_addr($gi, $ip);
	geoip_close($gi);
	return strtolower(trim($country));
}
function exec_script($url)
{
	$path = pathinfo($url);
	if (preg_match("/^http:/",$path[dirname])) {
		$script = @file_get_contents($url);
		if ($script === false) exit("// [ClickTale] ERROR: Unable to read $url.\n");
		else echo $script;
	} else exit("// [ClickTale] ERROR: Only HTTP requests allowed.");
}
?>
