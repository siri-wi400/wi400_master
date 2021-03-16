<?php

	/************************************************************************
	 * CSS and Javascript Combinator 0.5
	 * Copyright 2006 by Niels Leenheer
	 *
	 * Permission is hereby granted, free of charge, to any person obtaining
	 * a copy of this software and associated documentation files (the
	 * "Software"), to deal in the Software without restriction, including
	 * without limitation the rights to use, copy, modify, merge, publish,
	 * distribute, sublicense, and/or sell copies of the Software, and to
	 * permit persons to whom the Software is furnished to do so, subject to
	 * the following conditions:
	 * 
	 * The above copyright notice and this permission notice shall be
	 * included in all copies or substantial portions of the Software.
	 *
	 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
	 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
	 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
	 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
	 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
	 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
	 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
	 */


	$cache 	  = true;
	//$cachedir = realpath('../') . '/cache';
	require_once realpath('../')."/base/includes/getconfiguration.php";
	$settings = wi400GetSettings("");
	//require_once realpath('../')."/conf/wi400.conf.php";
	$cachedir = $settings['data_path']. '/cache';
	// @todo Prendere anche gli altri settings ...
	// Rimozion barre in eccesso
	$cachedir = preg_replace('/(\/+)/','/',$cachedir);

	// Determine the directory and type we should use
	switch ($_GET['type']) {
		case 'css':
			break;
		case 'javascript':
			break;
		default:
			header ("HTTP/1.0 503 Not Implemented");
			exit;
	};
	
	$type = $_GET['type'];
	$elements = explode(',', $_GET['files']);
	// Determine last modification date of the files
	$lastmodified = 0;
	//while (list(,$element) = each($elements)) {
	foreach ($elements as $key => $element) {
		//$path = realpath($base . '/' . $element);
		$path = realpath("../").$element;

		if (($type == 'javascript' && substr($path, -3) != '.js') || 
			($type == 'css' && substr($path, -4) != '.css')) {
			header ("HTTP/1.0 403 Forbidden");
			exit;	
		}
	
		if (!file_exists($path)) {
			die($path);
			header ("HTTP/1.0 404 Not Found");
			exit;
		}
		
		$lastmodified = max($lastmodified, filemtime($path));
	}
		
	// Send Etag hash
	$hash = $lastmodified . '-' . md5($_GET['files']);
	header ("Etag: \"" . $hash . "\"");
	
	if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && 
		stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) == '"' . $hash . '"') 
	{
		// Return visit and no modifications, so do not send anything
		header ("HTTP/1.0 304 Not Modified");
		header ('Content-Length: 0');
	} 
	else 
	{
		// First time visit or files were modified
		if ($cache) 
		{
			// Determine supported compression method
			$gzip = "";
			$deflate = "";
			if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
				$gzip = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip');
				$deflate = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate');
			}
	
			// Determine used compression method
			$encoding = $gzip ? 'gzip' : ($deflate ? 'deflate' : 'none');
	
			// Check for buggy versions of Internet Explorer
			if (!strstr($_SERVER['HTTP_USER_AGENT'], 'Opera') && 
				preg_match('/^Mozilla\/4\.0 \(compatible; MSIE ([0-9]\.[0-9])/i', $_SERVER['HTTP_USER_AGENT'], $matches)) {
				$version = floatval($matches[1]);
				
				if ($version < 6)
					$encoding = 'none';
					
				if ($version == 6 && !strstr($_SERVER['HTTP_USER_AGENT'], 'EV1')) 
					$encoding = 'none';
			}
			if (isset($settings['cache_encoding']) && $settings['cache_encoding']!="") {
				$encoding = $settings['cache_encoding'];
			}
			// Try the cache first to see if the combined files were already generated
			$cachefile = 'cache-' . $hash . '.' . $type . ($encoding != 'none' ? '.' . $encoding : '');
			
			if (!file_exists($cachedir)){
				mkdir($cachedir);
				
			}
			if (file_exists($cachedir . '/' . $cachefile)) {
				if ($fp = fopen($cachedir . '/' . $cachefile, 'rb')) {

					if ($encoding != 'none') {
						header ("Content-Encoding: " . $encoding);
					}
				
					header ("Content-Type: text/" . $type);
					header ("Content-Length: " . filesize($cachedir . '/' . $cachefile));
		
					fpassthru($fp);
					fclose($fp);
					exit;
				}
			}
		}
	
		// Get contents of the files
		$contents = '';
		reset($elements);
		foreach ($elements as $key => $element) {
		//while (list(,$element) = each($elements)) {
//			$path = realpath($base . '/' . $element);
			$path = realpath("../").$element;
			$contents .= "\n\n" . file_get_contents($path);
			
			
		}
	// 	Fix appbase path
		if ($type == "css" && isset($_GET["appBase"])){
			$contents = str_replace('url("','url("'.$_GET["appBase"],$contents);
		}
		
		// Send Content-Type
		header ("Content-Type: text/" . $type);
		
		if (isset($encoding) && $encoding != 'none') 
		{
			// Send compressed contents
			$contents = gzencode($contents, 9, $gzip ? FORCE_GZIP : FORCE_DEFLATE);
			header ("Content-Encoding: " . $encoding);
			header ('Content-Length: ' . strlen($contents));
			echo $contents;
		} 
		else 
		{
			// Send regular contents
			header ('Content-Length: ' . strlen($contents));
			echo $contents;
		}

		// Store cache
		if ($cache) {
			if ($fp = fopen($cachedir . '/' . $cachefile, 'wb')) {
				fwrite($fp, $contents);
				fclose($fp);
			}
		}
	}	
	
