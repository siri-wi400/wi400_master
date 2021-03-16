<?php
/**
 *
 * @param string $varname a name for the value being recursed
 * @param mixed $value the value to recurse
 * @param string $path string representation of the object path
 * @param int $level number of levels deep into the recursion
 */
function recurse_var($varname, &$value, $path = '/', $level = 0, $myclass="")
{
	global $PARSED_OBJS;

	$classname = @get_class($value);
	$is_incomplete_class = $classname == '__PHP_Incomplete_Class';
	$is_object = is_object($value) || $is_incomplete_class;
	$is_array = is_array($value);
	$icon = 'value.png';

	$cache_key = md5(print_r($value,true));

	if (($is_object || $is_array) && $level > RECURSION_SANITY_LIMIT) {
		$icon = $is_object ? 'object.png' : 'array.png';
		$value = "!!! RECURSION LIMIT !!!";
		$is_array = false;
		$is_object = false;
	}

	if (($is_object) && array_key_exists($cache_key,$PARSED_OBJS)) {
		$icon = 'object.png';
		$value = "DUP OF ".$PARSED_OBJS[$cache_key]."";
		$is_array = false;
		$is_object = false;
	}

	// ensure var is encoded properly
	$varname = htmlentities($varname);

	if ($is_object) {

		$icon = 'object.png';

		// do this to make the property class type visible
		if ($is_incomplete_class) {
			// can't access "__PHP_Incomplete_Class_Name" directly so we have to enumerate properties
			$innerVars = get_object_vars($value);
			foreach ($innerVars as $innerVar => $innerVal) {
				if ($innerVar == '__PHP_Incomplete_Class_Name') {
					$classname = $innerVal;
					break;
				}
			}
		}

		$styleClass = $level == 0 ? "jstree-open" : "";
		echo "<li class='$styleClass' data-jstree='{\"icon\":\"".BASE_URL."assets/images/$icon\"}'><strong>$varname</strong> ($classname)\n";
		echo "<ul>\n";

		//$innerVars = get_object_vars($value);
		$innerVars = (array)$value;
		foreach ($innerVars as $innerVar => $innerVal) {
			recurse_var($innerVar, $innerVal, $path . $innerVar . '/', $level + 1, $classname);
		}

		$methods = get_class_methods($value);
		foreach ($methods as $method) {
			//echo "<li data-jstree='{\"icon\":\"".BASE_URL."assets/images/function.png\"}'><strong>$method</strong> (Function)\n";
		}

		echo "</ul></li>\n";

	}
	elseif ($is_array) {

		$icon = 'array.png';
		$styleClass = $level == 0 ? "jstree-open" : "";
		$varname = str_replace($myclass, "",$varname);
		//echo "<li class='$styleClass'><strong>$varname</strong> (Array)";
		echo "<li class='$styleClass' data-jstree='{\"icon\":\"".BASE_URL."assets/images/$icon\"}'><strong>$varname</strong> (Array)";
		echo "<ul>\n";
		foreach ($value as $innerVar => $innerVal) {
			recurse_var($innerVar, $innerVal, $path . $innerVar . '/', $level + 1);
		}
		echo "</ul></li>\n";
	}
	else {
		if ($varname != '__PHP_Incomplete_Class_Name')
			$varname = str_replace($myclass, "",$varname);
			echo "<li data-jstree='{\"icon\":\"".BASE_URL."assets/images/$icon\"}'><strong>$varname</strong> = <span class='aaa'>" . (is_string($value) ? '"' . htmlentities($value) . '"' : $value) . "</span></li>\n";
	}

	// if (!array_key_exists($cache_key,$PARSED_OBJS)) // this sometimes makes things even more weird
	$PARSED_OBJS[$cache_key] = $path;

}

/**
 * @param array $_SERVER
 * @param bool $use_forwarded_host may need to be set to true if behind a load balancer
 * @return string
 */
function url_origin($s, $use_forwarded_host=false)
{
	$ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true:false;
	$sp = strtolower($s['SERVER_PROTOCOL']);
	$protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
	$port = $s['SERVER_PORT'];
	$port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
	$host = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
	$host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
	return $protocol . '://' . $host;
}

/**
 * @param array $_SERVER
 * @param bool $use_forwarded_host may need to be set to true if behind a load balancer
 * @return string
 */
function full_url($s, $use_forwarded_host=false)
{
	return url_origin($s, $use_forwarded_host) . $s['REQUEST_URI'];
}
function unserialize_php($session_data) {
	$return_data = array();
	$offset = 0;
	while ($offset < strlen($session_data)) {
		if (!strstr(substr($session_data, $offset), "|")) {
			die("invalid data, remaining: " . substr($session_data, $offset));
		}
		$pos = strpos($session_data, "|", $offset);
		$num = $pos - $offset;
		$varname = substr($session_data, $offset, $num);
		$offset += $num + 1;
		$data = unserialize(substr($session_data, $offset));
		$return_data[$varname] = $data;
		$offset += strlen(serialize($data));
	}
	return $return_data;
}
function getHTMLObject($file, $tipo, $id="1", $sort=False) {
	global $appBase;
	$html='
	<!DOCTYPE html>
	<html lang="en">
	<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<meta name="description" content="">
	<meta name="author" content="">
	<link rel="icon" href="assets/images/favicon.ico">
		<title>PHP Object Browser</title>
	
		<link rel="stylesheet" href="modules/developer/assets/css/bootstrap.min.css">
		<link rel="stylesheet" href="modules/developer/assets/css/font-awesome.min.css">
		<link rel="stylesheet" href="modules/developer/assets/css/style.min.css" />
		<link rel="stylesheet" href="modules/developer/assets/css/object-browser.css" />
	
					<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
					<!--[if lt IE 9]>
			  <script src="//oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			  <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
			  <![endif]-->
			  </head>
	
			  <body>
			  <main class="container">';
	if (is_file('includes.php') && is_readable('includes.php')) {
	  require_once 'includes.php';
	  $html.='<div class="alert alert-info"><i class="fa fa-info-circle"></i> Class definitions from includes.php loaded.</div>';
	}
	require_once "developer_functions.php";
	define('RECURSION_SANITY_LIMIT',25);
	define('BASE_URL', "http://".getServerAddress().":".$_SERVER["SERVER_PORT"].$appBase."modules/developer/");
	error_reporting(E_ALL & ~E_NOTICE);
	$PARSED_OBJS = array();
	$SORT_ARRAY=False;
	global $PARSED_OBJS, $SORT_ARRAY;
	if ($sort==True) {
		$SORT_ARRAY=True;
	}
	//$file="/www/zendsvr/data/_SESSION/spg6ah8ieflhn6acchn5d5d98m/SESSION_LIST_FILE_LIST.list";
	if ($tipo!="Array") {
		// Se sono sotto windows devo chiuderla e riaprirla .. ma devo controllare che sia la mia ..
//		if ($tipo=="SESSION") session_write_close();   // Chiude la sessione per visualizzarla , se no non mi lascia su IIS
		$contents = file_get_contents($file);
		if ($tipo!="SESSION") {
			$obj = unserialize($contents);
		} else {
			$obj = unserialize_php($contents);
			// se devo visualizzare la sessione corrente utilisso $_SESSION
			if (strpos(strtoupper($file), strtoupper(session_id()))!==False) {
				$obj = $_SESSION;
			}
//			ini_set('session.use_cookies', 0);  // PHP non attacca altri ID cookies allo start della sessione - da PHP7
//			session_start(); // Riprendo la scrittura sul file sessione
		}
	} else {
		$obj = $file;
	}
	$html.='<div id="ajax-loader-'.$id.'"><i class="fa fa-cog fa-spin fa-5x"></i></div>';
	$html.="<div id='tree-$id' style='display: none;'>\n<ul>\n";
	$html.=recurse_var2("", $obj);
	$html.="<ul>\n</div>\n";
	$html.='<script src="modules/developer/assets/js/bootstrap.min.js"></script>
			<script src="modules/developer/assets/js/jstree.min.js"></script>
			<script>
				jQuery(document).ready(function(){
					jQuery(\'#tree-'.$id.'\').jstree();
					jQuery(\'#ajax-loader-'.$id.'\').hide();
					jQuery(\'#tree-'.$id.'\').show();
				});
			</script>
			<script>
			jQuery(document).ready(function () {
			jQuery(".aaa").click(function() {
			if (jQuery(this).is(\'input\')) {
				//var input = jQuery(this).text();
				//jQuery(this).replaceWith(input);
			} else {	
			    //var input = "<input value=\'"+ jQuery(this).text() + "\' type=\'text\'>";
			    //jQuery(this).replaceWith("<span class=\'aaa\'>"+input+"</span>");
			    //jQuery(this).select();
			}	
			});
			});
			</script>
		  </body>
		</html>';
		return $html;
}
function recurse_var2($varname, &$value, $path = '/', $level = 0, $myclass="", $sort=False)
{
	global $PARSED_OBJS, $SORT_ARRAY;
	$html="";
	$classname = null;
	if (is_object($value)) {
		$classname = @get_class($value);
	}
	$is_incomplete_class = $classname == '__PHP_Incomplete_Class';
	$is_object = is_object($value) || $is_incomplete_class;
	$is_array = is_array($value);
	$icon = 'value.png';

	$cache_key = md5(print_r($value,true));

	if (($is_object || $is_array) && $level > RECURSION_SANITY_LIMIT) {
		$icon = $is_object ? 'object.png' : 'array.png';
		$value = "!!! RECURSION LIMIT !!!";
		$is_array = false;
		$is_object = false;
	}

	if (($is_object) && isset($cache_key) && array_key_exists($cache_key,$PARSED_OBJS)) {
		$icon = 'object.png';
		$value = "DUP OF ".$PARSED_OBJS[$cache_key]."";
		$is_array = false;
		$is_object = false;
	}

	// ensure var is encoded properly
	$varname = htmlentities($varname);

	if ($is_object) {

		$icon = 'object.png';

		// do this to make the property class type visible
		if ($is_incomplete_class) {
			// can't access "__PHP_Incomplete_Class_Name" directly so we have to enumerate properties
			$innerVars = get_object_vars($value);
			foreach ($innerVars as $innerVar => $innerVal) {
				if ($innerVar == '__PHP_Incomplete_Class_Name') {
					$classname = $innerVal;
					break;
				}
			}
		}

		$styleClass = $level == 0 ? "jstree-open" : "";
		$html.="<li class='$styleClass' data-jstree='{\"icon\":\"".BASE_URL."assets/images/$icon\"}'><strong>$varname</strong> ($classname)\n";
		$html.="<ul>\n";

		//$innerVars = get_object_vars($value);
		$innerVars = (array)$value;
		// Ordino per nome
		if ($SORT_ARRAY==True) {
			ksort($innerVars,SORT_NATURAL | SORT_FLAG_CASE);
		}
		foreach ($innerVars as $innerVar => $innerVal) {
			$html.=recurse_var2($innerVar, $innerVal, $path . $innerVar . '/', $level + 1, $classname);
		}

		$methods = get_class_methods($value);
		foreach ($methods as $method) {
			//echo "<li data-jstree='{\"icon\":\"".BASE_URL."assets/images/function.png\"}'><strong>$method</strong> (Function)\n";
		}
		$html.="</ul></li>\n";
	}
	elseif ($is_array) {

		$icon = 'array.png';
		$styleClass = $level == 0 ? "jstree-open" : "";
		$varname = str_replace($myclass, "",$varname);
		//echo "<li class='$styleClass'><strong>$varname</strong> (Array)";
		$html.="<li class='$styleClass' data-jstree='{\"icon\":\"".BASE_URL."assets/images/$icon\"}'><strong>$varname</strong> (Array)";
		$html.="<ul>\n";
		if ($SORT_ARRAY==True) {
			ksort($value,SORT_NATURAL | SORT_FLAG_CASE);
		}
		foreach ($value as $innerVar => $innerVal) {
			$html.=recurse_var2($innerVar, $innerVal, $path . $innerVar . '/', $level + 1);
		}
		$html.="</ul></li>\n";
	}
	else {
		if ($varname != '__PHP_Incomplete_Class_Name')
			$varname = str_replace($myclass, "",$varname);
		$html.="<li data-jstree='{\"icon\":\"".BASE_URL."assets/images/$icon\"}'><strong>$varname</strong> = <span class='aaa'>" . (is_string($value) ? '"' . htmlentities($value) . '"' : $value) . "</span></li>\n";
	}

	// if (!array_key_exists($cache_key,$PARSED_OBJS)) // this sometimes makes things even more weird
	$PARSED_OBJS[$cache_key] = $path;
	return $html;
}
function everything_in_tags($string, $tagname)
{
	$pattern = "#<\s*?$tagname\b[^>]*>(.*?)</$tagname\b[^>]*>#s";
	preg_match_all($pattern, $string, $matches);
	return $matches[1];
}
function read_backward_line($filename, $lines, $revers = false)
{
	$offset = -1;
	$c = '';
	$read = '';
	$i = 0;
	$fp = @fopen($filename, "r");
	while( $lines && fseek($fp, $offset, SEEK_END) >= 0 ) {
		$c = fgetc($fp);
		if($c == "\n" || $c == "\r"){
			$lines--;
			if( $revers ){
				$read[$i] = strrev($read[$i]);
				$i++;
			}
		}
		if( $revers ) $read[$i] .= $c;
		else $read .= $c;
		$offset--;
	}
	fclose ($fp);
	if( $revers ){
		if($read[$i] == "\n" || $read[$i] == "\r")
			array_pop($read);
		else $read[$i] = strrev($read[$i]);
		return implode('',$read);
	}

	return strrev(rtrim($read,"\n\r"));
}

function getSessionPath() {
	
	$path_sess = session_save_path();
	$last_char = substr($path_sess, -1);
	if(!in_array($last_char, array('\\', '/'))) {
		$path_sess .= '/';
	}
	
	return $path_sess;
}

function getSessionUser($sessione) {
	/*global $moduli_path;
	
	require_once $moduli_path.'/analisi/job_log_commons.php';
	$dati = get_session_data($sessione);*/
	
	global $settings;
	
	$dati = array();
	$lines = "";
	
	if(session_id() == $sessione) {
		$dati = $_SESSION;
	}else {
		$directory = getSessionPath();
		//echo $directory."___directory__<br/>";
		$file_path = $directory.'sess_'.$sessione;
		
		//echo "file_path__".$file_path."__<br/>";
		
		if(file_exists($file_path)) {
			$lines = file_get_contents($file_path);
			
			$dati = unserialize_php($lines);
			
			/*echo 'dati_session_'.$sessione."_br/>";
			showArray($dati);*/
			
			/*echo 'dati_session_mia_'.session_id()."_br/>";
			showArray($_SESSION);*/
		}
	}
	
	return $dati;
}   