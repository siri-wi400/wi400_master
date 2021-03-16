<?php
	require_once "user_commons.php";
	$steps = $history->getSteps();
//	echo "STEPS:<pre>"; print_r($steps); echo "</pre><br>";

	if($actionContext->getForm()=="DEFAULT" || in_array("CHGPWD_DEFAULT",$steps))
		$user = $_SESSION['user'];
	else if($actionContext->getForm()=="LOGIN")
		$user = $_REQUEST['user'];
	else if(in_array("CHGPWD_LOGIN",$steps))
		$user = wi400Detail::getDetailValue("chgpwd","USER");
//	echo "USER: $user<br>";
	
	if(in_array($actionContext->getForm(),array("DEFAULT","LOGIN")))
		$history->addCurrent();

	if($actionContext->getForm()=="MODIFICA") {
		$current_pwd = wi400Detail::getDetailValue("chgpwd","CURPWD");
		$check_pwd = $current_pwd;
		$new_pwd = wi400Detail::getDetailValue("chgpwd","NEWPWD");
		$ver_pwd = wi400Detail::getDetailValue("chgpwd","VERPWD");
		if ($settings['platform']!='AS400') {
			// @todo attenzione a settare l'algoritmo di HASH sui settings altrimenti non funziona .. ITALTRANS
			//$check_pwd = md5($current_pwd);
		}
		// Se arrivo da un reset della password non devo fare il controllo della vecchia password
		$error = False;
		if (isset($_REQUEST['CURPWD'])) {
			if(checkUser($user,  $check_pwd)) {	
				// OK
			} else {
					$messageContext->addMessage("ERROR", _t('W400015'), "", false);
					$error=true;
			}
		}
		//if(checkUser($user,  $check_pwd)) {
		if ($error == False) {
		if(strcmp($new_pwd,$ver_pwd)!=0) {
				$messageContext->addMessage("ERROR", _t('W400012'), "", false);	
			}
			else {
				$auth_method = retriveAuthMethod($user);
				// @todo devo guardare il tipo di autenticazione UTENTE o di sistema ..
				//if ($settings['platform']=='AS400') {
				if ($auth_method=='AS') {
	//				$zchgpwd = new wi400Routine('ZCHGPWD', $connzend);
					$zchgpwd = new wi400Routine('ZCHGUSRP', $connzend);
					$zchgpwd->load_description();
					$zchgpwd->prepare();
					$zchgpwd->set('USER', $user);
					$zchgpwd->set('PASSWORD', strtoupper($current_pwd));
					$zchgpwd->set('NEWPASS', strtoupper($new_pwd));
					$zchgpwd->call();
					
	//				echo "FLAG: ".$zchgpwd->get('FLAG')."<br>";
					
					if($zchgpwd->get('FLAG')==='0') {
						$messageContext->addMessage("SUCCESS", $zchgpwd->get('MSG2'));
						$messageContext->addMessage("SUCCESS", $zchgpwd->get('MSG1'));
						$messageContext->addMessage("SUCCESS",_t('W400013', array($user)));
					}
					else {
						// Verifico i messaggi arrivati, se è da programma esterno
						$msg2 = $zchgpwd->get('MSG2');
						$msg1 = $zchgpwd->get('MSG1');
						if (strpos($msg2, "DSPMSG")!==False) {
							// Recupero il messaggio del log del lavoro
							$joba = getJobInfo(True);
							$job = $joba['NBR']."/". $joba['USR']."/".$joba['JOB'];
							$sql="SELECT CHAR ( MESSAGE_TEXT) AS MESSAGE FROM TABLE(QSYS2.JOBLOG_INFO(    
								'$job')) A WHERE FROM_PROGRAM = 'CHECKPWD' ORDER 
								BY ORDINAL_POSITION DESC";
								$result = $db->query($sql);
								$row = $db->fetch_array($result);
								$messageContext->addMessage("ERROR", $row['MESSAGE']);
						} else {
							$messageContext->addMessage("ERROR", $zchgpwd->get('MSG2'));
							$messageContext->addMessage("ERROR", $zchgpwd->get('MSG1'));
						}
						$messageContext->addMessage("ERROR", _t('W400014'), "", false);
					}
				} else {
					$psw = md5($new_pwd);					
					$sql = "UPDATE SIR_USERS SET MYPASSWORD='$psw' WHERE USER_NAME='$user'";
					$db->query($sql);
					$messageContext->addMessage("SUCCESS",_t('W400013', array($user)));
				}
			}
			// Resetto eventuali errori di login
			if (isset($settings['advanced_security']) && $settings['advanced_security']==True) {
				require_once $routine_path."/classi/wi400AdvancedUserSecurity.cls.php";
				$advanced_security = new wi400AdvancedUserSecurity($user);
				$sec = $advanced_security->resetUserError(0);
				// Reset del cambio password al prossimo LOGIN
				$advanced_security->resetChangePasswordLogin();
			}	
		}
		//else {
		//	$messageContext->addMessage("ERROR", _t('W400015'), "", false);
		//}
		
		if(in_array("CHGPWD_DEFAULT",$steps)) {
			$actionContext->onSuccess("CHGPWD", "DEFAULT");
			$actionContext->onError("CHGPWD", "DEFAULT");
		}
		else if(in_array("CHGPWD_LOGIN",$steps)) {
			if($messageContext->getSeverity()=="ERROR")
				//header("Location: ".$appBase."index.php?t=CHGPWD&f=LOGIN&DECORATION=login&user=$user");
				goHeader($appBase."index.php?t=CHGPWD&f=LOGIN&DECORATION=login&user=$user");
			else
				//header("Location: ".$appBase."index.php?t=LOGIN");
				goHeader($appBase."index.php?t=LOGOUT&f=DELETE");
				//goHeader($appBase."index.php?t=LOGIN");
				die();
		}
	}
?>
