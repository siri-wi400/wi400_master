<?php

		if (!isset($_SESSION['user_data'])){
			// Retrive As400 User Data
			if (isset($_SESSION['AUTH_METOD'])) {
				if ($_SESSION['AUTH_METOD']=="AS") {
					$userData = rtvUserAS400($_SESSION['user']);
				} elseif ($_SESSION['AUTH_METOD']=="LDAP") {
					$userData = rtvUserLDAP($_SESSION['user']);
				} elseif ($_SESSION['AUTH_METOD']=="DB") {
					$userData = rtvUserDB($_SESSION['user']);
				}
				
				$_SESSION['user_data'] = $userData;
				$_SESSION['locale'] = $userData['CODICE'];
				$_SESSION['uid']='';
				if (isset($userData['UID'])) {
					$_SESSION['uid'] = $userData['UID'];
				}
			} else {
				$_SESSION['user_data'] = "";
				$_SESSION['locale'] = "";
				$_SESSION['uid']='';
			}
		}
       
?>
