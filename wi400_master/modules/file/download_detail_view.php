<?php

	$azione = $actionContext->getAction();

	if (!isset($_SESSION['user'])) {
		die("Permission denied!!");
	}
	
//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
	
	$fileType = $_REQUEST["FILE_TYPE"];
//	$file = $_REQUEST["FILE"];
	
	$id= $_REQUEST["ID"];
	
	$contest = $_REQUEST["CONTEST"];
	
	$msg = $_REQUEST["MSG"];
	
	$common = $_REQUEST["COMMON"];
	
	$email = $_REQUEST["EMAIL"];
	
	$campi = unserialize(urldecode($_REQUEST["CAMPI"]));
	
//	echo "POST FROM:"; var_dump($_POST['FROM']); echo "<br>";
	if(isset($_POST['FROM']) && $_POST['FROM']!="")
		$campi['FROM'] = $_POST['FROM'];
	
//	echo "POST TO:"; var_dump($_POST['TO']); echo "<br>";
	if(isset($_POST['TO']) && $_POST['TO']!="")
		$campi['TO'] = $_POST['TO'];
			
//	echo "POST CC:"; var_dump($_POST['CC']); echo "<br>";
	if(isset($_POST['CC']) && $_POST['CC']!="")
		$campi['CC']  = $_POST['CC'];
			
//	echo "POST BCC:"; var_dump($_POST['BCC']); echo "<br>";
	if(isset($_POST['BCC']) && $_POST['BCC']!="")
		$campi['BCC']  = $_POST['BCC'];
		
	if(isset($_POST['BODY']) && $_POST['BODY']!="")
		$campi['BODY']  = $_POST['BODY'];
		
	if(isset($_POST['SUBJECT']) && $_POST['SUBJECT']!="")
		$campi['SUBJECT']  = $_POST['SUBJECT'];
	
	// ALLEGATI EXTRA
//	echo "REQUEST - ALLEGATI_PATH_0:<pre>"; var_dump($_REQUEST["ALLEGATI_PATH_0"]); echo "</pre>";
		
	$atc_path_array = array();
	if(isset($_REQUEST["ALLEGATI_PATH_0"])) {
		for($i=0; ; $i++) {
			$key = "ALLEGATI_PATH_".$i;
			
			if(!isset($_REQUEST[$key]))
				break;
			
			if(isset($_REQUEST["REMOVE_ATC"]) && $_REQUEST["REMOVE_ATC"]==$key)
				continue;
			
			$atc_path_array[] = $_REQUEST[$key];
		}
	}
//	echo "1 - ATC_PATH_ARRAY:<pre>"; print_r($atc_path_array); echo "</pre>";
		
	$loaded_file = check_load_file("IMPORT_FILE", array(), false);
		
	if($loaded_file!==false) {
		$load_file_name = $loaded_file['tmp_name'];
	
		$file_name = $loaded_file['name'];
		$file_parts = pathinfo($file_name);
//		echo "FILE PARTS:<pre>"; print_r($file_parts); echo "</pre>";
		
		$temp = "tmp";
		$file_path = wi400File::getUserFile($temp, $file_name);
//		echo "NEW FILE PATH: $file_path<br>";
				
		$rinomina = copy($load_file_name, $file_path);
		chmod($file_path, 777);

		$atc_path_array[] = $file_path;
	}
		
//	echo "2 - ATC_PATH_ARRAY:<pre>"; print_r($atc_path_array); echo "</pre>";

	$atc_array = array();
	if(!empty($atc_path_array)) {
		$n = 0;
		foreach($atc_path_array as $key => $file_path) {
			$n++;
	
			$atc = $n.") File: $file_path";
	
//			$atc_array[] = $atc;
	
			$htmlOutput = "";
			if($key>0) {
				$imgHtml = get_image_url("REMOVE");
				$htmlOutput = " <img id=\"ATC_REMOVE_TOOL\" class=\"wi400-pointer\" hspace=\"5\" style=\"cursor:pointer\" title=\""._t('REMOVE')."\" onClick=\"doSubmit('".$azione."', 'DEFAULT&REMOVE_ATC=ALLEGATI_PATH_".$key."')\" src=\"".$imgHtml."\">";
			}
		
			$atc_array[] = $atc.$htmlOutput;
		}
	}
	
	$campi['ALLEGATI'] = $atc_array;
	$campi['ALLEGATI_PATH'] = $atc_path_array;
	
	$disable = unserialize(urldecode($_REQUEST["DISABLE"]));
	
//	showDownloadDetail($fileType, $file, $contest, $msg, $common, $email, $campi, $disable);
	showDownloadDetail($fileType, $id, $contest, $msg, $common, $email, $campi, $disable);
