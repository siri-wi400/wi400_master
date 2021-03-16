<?php

	$azione = $actionContext->getAction();
	
	if($actionContext->getForm()=="DEFAULT") {
		// Debug
		if(isset($_SESSION['DEBUG'])) {
			if($_SESSION['DEBUG']===true) {
				$_SESSION['DEBUG'] = true;
			}
			else {
				$_SESSION['DEBUG'] = false;
			}
		}
		else {
			$_SESSION['DEBUG'] = $settings['debug'];
		}
		
		// DB Debug
		if(isset($_SESSION['DB_DEBUG'])) {
			if($_SESSION['DB_DEBUG']===true) {
				$_SESSION['DB_DEBUG'] = true;
			}
			else {
				$_SESSION['DB_DEBUG'] = false;
			}
		}
		else {
			$_SESSION['DB_DEBUG'] = $settings['db_debug'];
		}
	}
	else if($actionContext->getForm()=="SAVE") {
		// Debug
		if(isset($_POST['DEBUG'])) {
			$_SESSION['DEBUG'] = true;
		}
		else {
			$_SESSION['DEBUG'] = false;
		}
		
		// DB Debug
		if(isset($_POST['DB_DEBUG'])) {
			$_SESSION['DB_DEBUG'] = true;
		}
		else {
			$_SESSION['DB_DEBUG'] = false;
		}
		
		$actionContext->gotoAction($azione,"DEFAULT","",true);
	}