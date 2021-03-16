<?php
class checkUserDB  {
	
	function checkUser($name, $pwd) {
		global $users_table, $db, $messageContext, $settings;
		// compare $name to what's in the database.
		// return true if the name is found in the database and the password matches.
		//$sql = "select * from " . $users_table . " where user_name='" . $name . "'";
		
		$sql = "select * from " . $users_table . " where user_name=?";
		
		$result = $db->singlePrepare($sql);
		$do = $db->execute($result, array($name));
		//$result = $db->singleQuery ( $sql );
		$row = $db->fetch_array ( $result );
		if($row['AUTH_METOD'] != $settings['auth_method'] && $row['AUTH_METOD'] == "DB" && $settings['security_password_hash']== 'md5') {
			$pwd = md5($pwd);
		}
		
		if (! checkPwd ( $pwd, $row ['MYPASSWORD'] )) {
			$messageContext->addMessage ( "ERROR", _t("NOME_UTENTE_PASSWORD_ERRATA"));
			return false;
		}
		// Scrittura Log Accesso	
		// Scrittura Log Accesso
		writeLogAccess($name);
		/*$values= array();
		$values['ZSUTE']= $name; //UTENTE
		$values['ZSESI']= 'OK'; // ESITO LOG 
		$values['ZSSWS']= 'WEB'; //USER
		$values['ZSIP']= $_SERVER['REMOTE_ADDR']; //INDIRIZZO IP 
		$values['ZDEV']= ''; //DEVICE ?
		$values['ZTIME']= getDb2Timestamp();  //TIMESTAMP
		// Reperisco gli attributi del lavoro
		if (isset($settings['XMLSERVICE'])) {
			$rtv = executeCommand("rtvjoba", array(), array("JOB"=>"JOB","USER"=>'USER',"NBR"=>"NBR"));
			$values['ZUSR']=$JOB;
			$values['ZJOB']=$USER;
			$values['ZNBR']=$NBR;
			$values['ZFRE']='';
		}
		
		$stmtDoc = $db->prepare("INSERT", "ZSLOG", null, array_keys($values));
		$result = $db->execute($stmtDoc, $values);*/
		
		// if user the password for the given user is correct, return true
		return true;
	}
}
?>
