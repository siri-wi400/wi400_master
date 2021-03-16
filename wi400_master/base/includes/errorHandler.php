<?php
function wi400ErrorHandler($errno, $errstr, $errfile, $errline){
	global $messageContext, $settings;
	
	if ($settings["show_system_error"] == True AND substr($errstr, 0, 13)!="i5_pconnect()") {
	
	    switch ($errno) {
	    case E_USER_ERROR:
	        
	    	if ($settings["mail_system_error"] == True){
		    	
		        // Istanzio la classe per l'invio delle e-mail
				$mail = new PHPMailer();
				// Impostazione di Mailer per l'invio di messaggi tramite SMTP
				$mail->IsSMTP();
				// Impostazione dell'attributo Host della classe PHPMailer
				$mail->Host = $settings["smtp_host"];
				// Impostazione dell'autorizzazione all'autorizzazione all'autenticazione
				$mail->SMTPAuth = (bool) $settings["smtp_auth"];
				if($mail->SMTPAuth === true) {
					// Impostazione dell'attributo Username della classe PHPMailer
					$mail->Username = $settings["smtp_user"];
					// Impostazione dell'attributo Password della classe PHPMailer
					$mail->Password = $settings["smtp_pass"];
				}
				// Impostazione dell'attributo From della classse PHPMailer
				$mail->From = "as400@siri-informatica.it";
				$mail->FromName = $settings["cliente_installazione"];
				$mail->AddAddress($settings['admin_email'],"Wi400 Admin");
		        $mail->Subject = "Fatal Error WI400";
		        
		        $errorBody = "$errstr<br>"."  Fatal error on line $errline in file $errfile";
		        $errorBody.= "<br><b>------------ Request ------------</b><br>";
		        foreach ($_REQUEST as $key => $value){
		        	 $errorBody.= $key." = ".$value."<br>";
		        }
		        $mail->Body = $errorBody;
		        $mail->IsHTML(true);
		        $emailResult = $mail->Send();
		        echo "Si è verificato un errore grave.<br>E' stato inviato in automatico un messaggio con i dettagli dell'errore al servizio di assistenza WI400.<br>Ci scusiamo per l'inconveniente.";
	    		exit();
	    	}else{
	    		echo "$errstr<br>"."  Fatal error on line $errline in file $errfile";
				exit();
	    	}
	        break;
	
	    case E_USER_WARNING:
	        $messageContext->addMessage("ALERT",$errstr." (".$errline." in file ".$errfile.") ","",false);
	        break;
	
	    case E_USER_NOTICE:
	    	$messageContext->addMessage("ALERT",$errstr." (".$errline." in file ".$errfile.") ","",false);
	        break;
	        
	    default:
	        $messageContext->addMessage("ALERT",$errstr." (".$errline." in file ".$errfile.") ","",false);
	    	break;
	    }
	}
}

?>