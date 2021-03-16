<?php

	if($actionContext->getForm()=="DEFAULT") {
		if(isset($_POST['PATH_EMAIL']) && $_POST['PATH_EMAIL']!="" && $settings['platform']=="AS400") {
			if(!file_exists($_POST['PATH_EMAIL']))
				$messageContext->addMessage("ERROR", "File inesistente", "PATH_EMAIL", true);
		}
	}

?>