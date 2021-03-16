<?php 
	    
        require_once $routine_path."/PHPMailer/class.phpmailer.php";
	
		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->Host = $settings["smtp_host"];
		$mail->SMTPAuth = (bool) $settings["smtp_auth"];
		if($mail->SMTPAuth===true) {
			// Impostazione dell'attributo Username della classe PHPMailer
			$mail->Username = $settings["smtp_user"];
			// Impostazione dell'attributo Password della classe PHPMailer
			$mail->Password = $settings["smtp_pass"];
		}
		// Impostazione dell'attributo From della classse PHPMailer
		$mail->From = trim("info@siri-informatica.it");
		// Impostazione dei destinatari dell'e-mail
//		$mail->AddAddress(trim("luca.zovi@siri-informatica.it"));
		$mail->AddAddress(trim("valeria.porrazzo@siri-informatica.it"));
		// Impostazione dell'attributo Subject della classe PHPMailer
		$mail->Subject = trim("elaborazione batch completata");
		
		// Impostazione dell'attributo Body della classe PHPMailer
		$mail->Body = trim("L'elaborazione del batch è stata completata in modo corretto");
		
		// Impostazione dell'attributo WordWrap della classe PHPMailer
		$mail->WordWrap = 50;
		//$mail->AddAttachment($file_path);
		
		$sent = $mail->Send();

?>