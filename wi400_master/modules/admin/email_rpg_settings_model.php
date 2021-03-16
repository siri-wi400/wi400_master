<?php

	$azione = $actionContext->getAction();
	
	if($actionContext->getForm()=="DEFAULT") {
/*		
		$string = data_area_read("DTAZEMA");
//		echo "STRING: $string<br>";
//		$string_prova = str_replace(' ','*',$string);
//		echo "STRING: $string_prova<br>";
		$string = sprintf("%-263s", $string);
//		echo "STRING: $string<br>";
//		$string_prova = str_replace(' ','*',$string);
//		echo "STRING: $string_prova<br>";
		$ambiente = substr($string, 0, 1);
		$to = substr($string, 1, 64);
		$from = substr($string, 65, 64);
		$subject = substr($string, 129, 64);
		$country = substr($string, 193, 3);
		$exit = substr($string, 196, 1);
		$path_email = substr($string, 197, 64);
		$invoke_method = substr($string, 261, 1);
		$invoke_batch = substr($string,262, 1);
		$wi400_url = substr($string,263, 64);
*/
		$ambiente = data_area_read("DTAZEMA", 1, 1);
		$to = data_area_read("DTAZEMA", 2, 64);
		$from = data_area_read("DTAZEMA", 66, 64);
		$subject = data_area_read("DTAZEMA", 130, 64);
		$country = data_area_read("DTAZEMA", 194, 3);
		$exit = data_area_read("DTAZEMA", 197, 1);
		$path_email = data_area_read("DTAZEMA", 198, 64);
		$invoke_method = data_area_read("DTAZEMA", 262, 1);
		$invoke_batch = data_area_read("DTAZEMA", 263, 1);
		$wi400_url = data_area_read("DTAZEMA", 264, 64);
		$multiasp = data_area_read("DTAZEMA", 328, 1);
		$default_body = data_area_read("DTAZEMA", 329, 64);
		$multisys = data_area_read("DTAZEMA", 393, 1);
		$findmod = data_area_read("DTAZEMA", 394, 1);
		$ftp_user = data_area_read("DTAZEMA", 395, 10);
		$ftp_pass = data_area_read("DTAZEMA", 405, 10);
		$jobq = data_area_read("DTAZEMA", 415, 10);
		$default_alias = data_area_read("DTAZEMA", 425, 50);
		$wrtxmlcont = data_area_read("DTAZEMA", 475, 1);
		$wrtbodcont = data_area_read("DTAZEMA", 476, 1);
		$wrtsplcont = data_area_read("DTAZEMA", 477, 1);
		$jobq_batch = data_area_read("DTAZEMA", 478, 10);
		$jobq_lib_batch = data_area_read("DTAZEMA", 488, 10);
		$jobq_mail = data_area_read("DTAZEMA", 498, 10);
		$jobq_lib_mail = data_area_read("DTAZEMA", 508, 10);
		$super_user = data_area_read("DTAZEMA", 518, 10);
		$super_pwd  = data_area_read("DTAZEMA", 528, 10);
		$port  = data_area_read("DTAZEMA", 538, 5);
		$batch_data_path  = data_area_read("DTAZEMA", 543, 60);
		$path_server  = data_area_read("DTAZEMA", 603, 60);
	}
	else if($actionContext->getForm()=="SAVE") {
//		echo "POST:<pre>"; print_r($_POST); echo "</pre>";

		$ambiente = "";
		if(isset($_POST['AMBI']))
			$ambiente = $_POST['AMBI'];
			
		$to = "";
		if(isset($_POST['DFTTO']) && $_POST['DFTTO']!="")
			$to = $_POST['DFTTO'];
			
		$from = "";
		if(isset($_POST['DFTFRM']) && $_POST['DFTFRM']!="")			
			$from = $_POST['DFTFRM'];
			
		$subject = "";
		if(isset($_POST['SUBJECT']) && $_POST['SUBJECT']!="")
			$subject = $_POST['SUBJECT'];
			
		$country = "";
		if(isset($_POST['COUNTRY']) && $_POST['COUNTRY']!="")
			$country = $_POST['COUNTRY'];
			
		$exit = "N";
		if(isset($_POST['EXIT']))
			$exit = "E";
			
		$path_email = "";
		if(isset($_POST['PATH_EMAIL']))
			$path_email = $_POST['PATH_EMAIL'];
			
		$invoke_method = "";
		if(isset($_POST['INVOKE_METHOD']) && $_POST['INVOKE_METHOD']!="")
			$invoke_method = $_POST['INVOKE_METHOD'];
			
		$invoke_batch = "N";
		if(isset($_POST['INVOKE_BATCH']))
			$invoke_batch = "S";
			
		$wi400_url = "";
		if(isset($_POST['WI400_URL']) && $_POST['WI400_URL']!="")
			$wi400_url = $_POST['WI400_URL'];
		
		$multiasp = "N";
		if(isset($_POST['MULTIASP']))
			$multiasp = "S";
		
		$default_body = "";
		if(isset($_POST['DEFAULT_BODY']) && $_POST['DEFAULT_BODY']!="")
			$default_body = $_POST['DEFAULT_BODY'];

		$multisys = "N";
		if(isset($_POST['MULTISYS']))
			$multisys = "S";

		$findmod = "N";
		if(isset($_POST['FINDMOD']))
			$findmod = "S";
				
		$ftp_user = "";
		if(isset($_POST['FTP_USER']) && $_POST['FTP_USER']!="")
			$ftp_user = $_POST['FTP_USER'];		
		
		$ftp_pass = "";
		if(isset($_POST['FTP_PASS']) && $_POST['FTP_PASS']!="")
			$ftp_pass = $_POST['FTP_PASS'];
		
		$jobq = "";
		if(isset($_POST['JOBQ']) && $_POST['JOBQ']!="")
			$jobq = $_POST['JOBQ'];
		
		$default_alias = "";
		if(isset($_POST['DEFAULT_ALIAS']) && $_POST['DEFAULT_ALIAS']!="")
			$default_alias = $_POST['DEFAULT_ALIAS'];
		$wrtxmlcont = "";
		if(isset($_POST['WRTXMLCONT']))
			$wrtxmlcont = "S";
		$wrtbodcont = "";
		if(isset($_POST['WRTBODCONT']))
			$wrtbodcont = "S";
		$wrtsplcont = "";
		if(isset($_POST['WRTSPLCONT']))
			$wrtsplcont = "S";
		
		$jobq_bath = "";
		if(isset($_POST['JOBQ_BATCH']) && $_POST['JOBQ_BATCH']!="")
			$jobq_batch = $_POST['JOBQ_BATCH'];
		$jobq_lib_batch = "";
			if(isset($_POST['JOBQ_LIB_BATCH']) && $_POST['JOBQ_LIB_BATCH']!="")
				$jobq_lib_batch = $_POST['JOBQ_LIB_BATCH'];
		$jobq_mail = "";
		if(isset($_POST['JOBQ_MAIL']) && $_POST['JOBQ_MAIL']!="")
			$jobq_mail = $_POST['JOBQ_MAIL'];
		$jobq_lib_mail = "";
		if(isset($_POST['JOBQ_LIB_MAIL']) && $_POST['JOBQ_LIB_MAIL']!="")
			$jobq_lib_mail = $_POST['JOBQ_LIB_MAIL'];
		$super_user = "";
		if(isset($_POST['SUPER_USER']) && $_POST['SUPER_USER']!="")
			$super_user = $_POST['SUPER_USER'];		

		$super_pwd = "";
		if(isset($_POST['SUPER_PWD']) && $_POST['SUPER_PWD']!="")
			$super_pwd = $_POST['SUPER_PWD'];
		$port = "";
		if(isset($_POST['PORT']) && $_POST['PORT']!="")
			$port = $_POST['PORT'];
		$batch_data_path = "";
		if(isset($_POST['DATA_PATH']) && $_POST['DATA_PATH']!="")
			$batch_data_path = $_POST['DATA_PATH'];
			$path_server = "";
		if(isset($_POST['PATH_SERVER']) && $_POST['PATH_SERVER']!="")
			$path_server = $_POST['PATH_SERVER'];
		
		$string = sprintf("%-1s", $ambiente);
		$string .= sprintf("%-64s", $to);
		$string .= sprintf("%-64s", $from);
		$string .= sprintf("%-64s", $subject);
		$string .= sprintf("%-3s", $country);
		$string .= sprintf("%-1s", $exit);
		$string .= sprintf("%-64s", $path_email);
		$string .= sprintf("%-1s", $invoke_method);
		$string .= sprintf("%-1s", $invoke_batch);
		$string .= sprintf("%-64s", $wi400_url);
		$string .= sprintf("%-1s", $multiasp);
		$string .= sprintf("%-64s", $default_body);
		$string .= sprintf("%-1s", $multisys);
		$string .= sprintf("%-1s", $findmod);
		$string .= sprintf("%-10s", $ftp_user);
		$string .= sprintf("%-10s", $ftp_pass);
		$string .= sprintf("%-10s", $jobq);
		$string .= sprintf("%-50s", $default_alias);
		$string .= sprintf("%-1s", $wrtxmlcont);
		$string .= sprintf("%-1s", $wrtbodcont);
		$string .= sprintf("%-1s", $wrtsplcont);
		$string .= sprintf("%-10s", $jobq_batch);
		$string .= sprintf("%-10s", $jobq_lib_batch);
		$string .= sprintf("%-10s", $jobq_mail);
		$string .= sprintf("%-10s", $jobq_lib_mail);
		$string .= sprintf("%-10s", $super_user);
		$string .= sprintf("%-10s", $super_pwd);
		$string .= sprintf("%-5s", $port);
		$string .= sprintf("%-60s", $batch_data_path);
		$string .= sprintf("%-60s", $path_server);
		//echo "STRING: $string<br>";
		$string_prova = str_replace(' ','-',$string);
		//echo "STRING: $string_prova<br>";
		
		//data_area_write("DTAZEMA", $string);
		data_area_write("DTAZEMA", $string);
		$messageContext->addMessage("SUCCESS", "Aggiornamento effettuato con successo");
		$actionContext->gotoAction($azione, "DEFAULT");
	}

?>