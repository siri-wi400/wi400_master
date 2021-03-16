<?php

//	if($actionContext->getForm()=="DEFAULT") {
		$detail_name = $_REQUEST['ID_EMAIL_DET'];
		
		$from = "";
		if(wi400Detail::getDetailValue($detail_name,"FROM")!="")
			$from = wi400Detail::getDetailValue($detail_name,"FROM");
			
		$to_array = array();
		if(wi400Detail::getDetailValue($detail_name,"TO")!="")
			$to_array = wi400Detail::getDetailValue($detail_name,"TO");
			
		$cc_array = array();
		if(wi400Detail::getDetailValue($detail_name,"CC")!="")
			$cc_array = wi400Detail::getDetailValue($detail_name,"CC");
		
		$bcc_array = array();
		if(wi400Detail::getDetailValue($detail_name,"BCC")!="")
			$bcc_array = wi400Detail::getDetailValue($detail_name,"BCC");
		
		$rpy_array = array();
		if(wi400Detail::getDetailValue($detail_name,"RPYTO")!="")
			$rpy_array = unserialize(wi400Detail::getDetailValue($detail_name,"RPYTO"));
		
		$con_array = array();
		if(wi400Detail::getDetailValue($detail_name,"CONTO")!="")
			$con_array = unserialize(wi400Detail::getDetailValue($detail_name,"CONTO"));
			
		$subject = "";
		if(wi400Detail::getDetailValue($detail_name,"SUBJECT")!="")
			$subject = wi400Detail::getDetailValue($detail_name,"SUBJECT");
			
		$body = "";
		if(wi400Detail::getDetailValue($detail_name,"BODY")!="")
			$body = wi400Detail::getDetailValue($detail_name,"BODY");

		$isHtml = False;
		if($_REQUEST['ISHTML']!="")
			$isHtml = $_REQUEST['ISHTML'];
		
		$specialConf = "";
		if($_REQUEST['SPECIAL_CONF']!="")
			$specialConf = $_REQUEST['SPECIAL_CONF'];
		
		$atc = "";
		if($_REQUEST['ALLEGATO']!="")
			$atc = $_REQUEST['ALLEGATO'];

		$file_path = "";
		if($_REQUEST['COMMON']=="COMMON") {
			$file_path = wi400File::getCommonFile($_REQUEST['CONTEST'], $atc);
		}
		else{
			if(isset($_REQUEST['CONTEST']) && !empty($_REQUEST['CONTEST'])) {
				$file_path = wi400File::getUserFile($_REQUEST['CONTEST'], $atc);
			}
			else {
				$file_path = $atc;
			}
		}
		
		$allegati_array = array();
		if($file_path!="")
			$allegati_array[] = $file_path;
		
		// ALLEGATI EXTRA
/*		
		$loaded_file = check_load_file("IMPORT_FILE", array(), false);
		
		if($loaded_file!==false) {
			$load_file_name = $loaded_file['tmp_name'];
		
			$file_name = $loaded_file['name'];
			$file_parts = pathinfo($file_name);
//			echo "FILE PARTS:<pre>"; print_r($file_parts); echo "</pre>";
		
			$temp = "tmp";
			$file_path = wi400File::getUserFile($temp, $file_name);
//			echo "NEW FILE PATH: $file_path<br>";
		
			$rinomina = copy($load_file_name, $file_path);
			chmod($file_path, 777);
		
			$allegati_array[] = $file_path;
		}
*/
//		echo "REQUEST - ALLEGATI_PATH_0:<pre>"; var_dump($_REQUEST["ALLEGATI_PATH_0"]); echo "</pre>";
		if(isset($_REQUEST["ALLEGATI_PATH_0"])) {
			for($i=0; ; $i++) {
				$key = "ALLEGATI_PATH_".$i;
		
				if(!isset($_REQUEST[$key]))
					break;
		
				$file_path = $_REQUEST[$key];
		
				$allegati_array[] = $file_path;
			}
		}
//		echo "ALLEGATI:<pre>"; print_r($allegati_array); echo "</pre>";
		
//		echo "INVIO_EMAIL_MODEL FILE PATH: $file_path<br>";
		require_once $routine_path."/classi/wi400invioEmail.cls.php";		
//		$sent = wi400invioEmail::invioEmail($from,$to_array,$cc_array,$subject,$body,array($file_path));

		$dest_array = array(
			"CC" => $cc_array,
			"BCC" => $bcc_array,
			"RPYTO" => $rpy_array,
			"CONTO" => $con_array
		);
		
		// Verifico se prevista una configurazione Speciale
		$SMTP=array();
		if ($specialConf!="") {
			$sp=$specialConf;
			$SMTP['mail_host'] = $settings[$sp."_smtp_host"];
			$SMTP['SMTPauth'] = $settings[$sp."_smtp_auth"];
			$SMTP['user'] = $settings[$sp."_smtp_user"];
			$SMTP['pass'] = $settings[$sp."_smtp_pass"];
			$SMTP['from_name'] = $settings[$sp.'_smtp_from'];
			$SMTP['smtp_secure'] = $settings[$sp."_smtp_secure"];
			$SMTP['smtp_port'] = $settings[$sp."_smtp_port"];
		}
		
//		$sent = wi400invioEmail::invioEmail($from,$to_array, $dest_array,$subject,$body,array($file_path), array(), $isHtml);
//		$sent = wi400invioEmail::invioEmail($from,$to_array, $dest_array,$subject,$body,$allegati_array, array(), $isHtml);
		$sent = wi400invioEmail::invioEmail($from,$to_array, $dest_array,$subject,$body,$allegati_array, $SMTP, $isHtml);
		
		if($sent===false)
			$messageContext->addMessage("ERROR", "Errore durante l'invio dell'email");
		else
			$messageContext->addMessage("SUCCESS", "Email inviata con successo");
		
		$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW_MSG", "", true);
//	}

?>