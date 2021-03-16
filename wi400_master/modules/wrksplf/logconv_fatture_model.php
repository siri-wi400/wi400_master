<?php

	require_once 'logconv_fatture_common.php';

	$azione = $actionContext->getAction();
//	echo "AZIONE: $azione - FORM: ".$actionContext->getForm()."<br>";
	
	if(in_array($actionContext->getForm(), array("DEFAULT", "LIST")))
		$history->addCurrent();
	
	$societa = wi400Detail::getDetailValue($azione."_SRC",'SOCIETA');
	$cliente = wi400Detail::getDetailValue($azione."_SRC",'CLIENTE');
//	$stampata = wi400Detail::getDetailValue($azione."_SRC",'STAMPATA');
	$data_ini = wi400Detail::getDetailValue($azione."_SRC",'DATA_INI');
	$data_fin = wi400Detail::getDetailValue($azione."_SRC",'DATA_FIN');
	if($azione=="LOGCONV_FATTURE_OP") {
		$data_stmp_ini = wi400Detail::getDetailValue($azione."_SRC",'DATA_STMP_INI');
		$data_stmp_fin = wi400Detail::getDetailValue($azione."_SRC",'DATA_STMP_FIN');
	}
	$user = wi400Detail::getDetailValue($azione."_SRC",'USER');
	
	$data_rif_ana = date("Ymd");
	if(isset($data_stmp_fin) && !empty($data_stmp_fin)) {
		$data_rif_ana = dateViewToModel($data_stmp_fin);
	}
	else if(isset($data_fin) && !empty($data_fin)) {
		$data_rif_ana = dateViewToModel($data_fin);
	}
//	echo "DATA RIF ANA: $data_rif_ana<br>";
	
	if($actionContext->getForm()=="DEFAULT") {
		$actionContext->setLabel("Parametri");
	}
	else if($actionContext->getForm()=="LIST") {
		wi400Session::delete(wi400Session::$_TYPE_DETAIL, $azione.'_STAMPA_SEL_DET');
		
		$where = "";
		$where_array = array();
		
		// Modelli
		$where_mod = array();
		foreach($mod_conv_abil as $key => $val) {
			if($azione=="LOGCONV_FATTURE" && $val=="N")
				continue;
			
			$len = strlen($key);
			$where_mod[] = "substr(LOGMOD, 1, $len)='$key'";
		}
		
		if(!empty($where_mod)) {
			$where_array[] = "(".implode(" or ", $where_mod).")";
		}
		
//		$where_array[] = "LOGMOD like 'FAT%'";
		
		// Code di stampa
		if($azione=="LOGCONV_FATTURE") {
			if(!empty($mod_outq_abil))
				$where_array[] = "LOGOUT in ('".implode("', '", $mod_outq_abil)."')";
		}
		
		if(isset($societa) && $societa!="")
			$where_array[] = "LOGKU1='$societa'";
		if(isset($cliente) && $cliente!="")
			$where_array[] = "LOGKY3='$cliente'";
/*		
		if(isset($stampata) && $stampata!="")
			$where_array[] = "LOGSTP='$stampata'";
*/
		if(isset($data_ini) && $data_ini!="") {
			$di = dateViewToModel($data_ini);
			$df = dateViewToModel($data_fin);
			$where_array[] = "substr(LOGKY2, 7, 2)!!substr(LOGKY2, 4, 2)!!substr(LOGKY2, 1, 2) between substr('$di', 3) and substr('$df', 3)";
		}
		
		if($azione=="LOGCONV_FATTURE_OP") {
			if(isset($data_stmp_ini) && $data_stmp_ini!="") {
/*				
				$di_stmp = dateToTimestamp($data_stmp_ini);
				$df_stmp = dateToTimestamp($data_stmp_fin);
				$where_array[] = "LOGINS between '$di_stmp' and '$df_stmp'";
*/
				$di = dateViewToModel($data_stmp_ini);
				$df = dateViewToModel($data_stmp_fin);
				$where_array[] = "substr(char(LOGINS), 1, 4)!!substr(char(LOGINS), 6, 2)!!substr(char(LOGINS), 9, 2) between $di and $df";
			}
		}
		
		if(isset($user) && $user!="")
			$where_array[] = "LOGUSR='$user'";
		
		$where = implode(" and ", $where_array);
//		echo "WHERE: $where<br>";
	}
/*
	else if($actionContext->getForm()=="DOWNLOAD_FILE") {
		$file_parm = explode('|', $_REQUEST["DETAIL_KEY"]);
		$path = trim($file_parm[0]);
		$name = trim($file_parm[1]);
	
		$filename = $path."/".$name;
	
		//		echo "FILE: $filename<br>";
	
		$TypeImage = "";
		$file_parts = pathinfo($filename);
		if(isset($file_parts['extension']))
			$TypeImage = strtolower($file_parts['extension']);
	}
*/
	else if($actionContext->getForm()=="DOWNLOAD_FILE") {
		$file_parm = explode('|', $_REQUEST["DETAIL_KEY"]);
//		$path = trim($file_parm[0]);
//		$name = trim($file_parm[1]);

		$user = $file_parm[0];
		$job = $file_parm[1];
		$nbr = $file_parm[2];
		$user_data = $file_parm[3];
		$modulo = $file_parm[6];
		$ultima_conv = $file_parm[7];
		
		$path = trim($file_parm[4]);
		$name = trim($file_parm[5]);
	
		$filename = $path."/".$name;
	
//		echo "FILE: $filename<br>";

		$temp = "";
	
		$TypeImage = "";
		$file_parts = pathinfo($filename);
		if(isset($file_parts['extension']))
			$TypeImage = strtolower($file_parts['extension']);
		
		$campi = array();
		
		$sql = "select * 
			from FPDFCONV a left join FEMAILDT b on a.ID=b.ID 
			where a.ID='$ultima_conv' and MAIUSR='$user' and MAIJOB='$job' and MAINBR='$nbr'";
//		echo "SQL: $sql<br>";
		$res = $db->query($sql, false, 0);
		
//		$to_email = getEmailNegozio($negozio);
		
		while($row = $db->fetch_array($res)) {
//			echo "ROW:<pre>"; print_r($row); echo "</pre>";
			if(empty($campi)) {
				$campi['FROM'] = $row['MAIFRM'];
				$campi['SUBJECT'] = $row['MAISBJ'];
			}
			
			$campi[$row['MATPTO']][] = $row['MAITOR'];
		}
	}
	else if($actionContext->getForm()=="REMOVE") {
		$idList = $azione."_LIST";
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		
		$rowsSelectionArray = $wi400List->getSelectionArray();	
//		echo "SEL_ARRAY: "; print_r($rowsSelectionArray); echo "<br>";
	
		foreach($rowsSelectionArray as $key => $val) {
			$keys = explode("|", $key);
//			echo "KEYS:<pre>"; print_r($keys); echo "</pre><br>";
				
			$path = $keys[4];
			$name = $keys[5];
				
			$file_path = $path."/".$name;
//			echo "FILE: $file_path<br>";
				
			if(file_exists($file_path)) {
				unlink($file_path);
			}
				
			$keys_array = array(
				"LOGUSR" => $keys[0],
				"LOGJOB" => $keys[1],
				"LOGNBR" => $keys[2],
				"LOGDTA" => $keys[3],
				"LOGPTH" => $path,
				"LOGNOM" => $name,
				"LOGMOD" => $keys[6],
				"LOGID" => $keys [7]
			);
				
			$j = 8;
			for($i=1; $i<=$settings['modelli_pdf_keys']; $i++) {
				$keys_array["LOGKY".$i] = $keys[$j++];
			}
				
//			echo "DEL KEYS:<pre>"; print_r($keys_array); echo "</pre><br>";
	
			$stmtdelete = $db->prepare("DELETE", "FLOGCONV", array_keys($keys_array), null);
			$deleteRes = $db->execute($stmtdelete, $keys_array);
				
			if($deleteRes)
				$messageContext->addMessage("SUCCESS", "Eliminazione del log avvenuta con successo");
			else
				$messageContext->addMessage("ERROR", "Errore durante l'eliminazione del log");
		}
	
		$actionContext->onError($azione, "DEFAULT");
		$actionContext->onSuccess($azione, "DEFAULT");
	}
/*	
	else if ($actionContext->getForm() == "CALCULATE"){
		$check_duplex = "";
		if (isset($_GET["OUTQ"])){
			$sql = "select * from FP2OPARM where PROUTQ='{$_GET["OUTQ"]}'";
			$res = $db->singleQuery($sql);
			if($row = $db->fetch_array($res)) {
				$check_duplex = "S";
			}
		}
		die($check_duplex);
	}
*/
	else if($actionContext->getForm()=="REINVIO") {
		$idList = $azione."_LIST";
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		
		$rowsSelectionArray = $wi400List->getSelectionArray();
//		echo "SEL_ARRAY: "; print_r($rowsSelectionArray); echo "<br>";

		$keyUpdt = array("LCOIDI" => "?");
		$fieldsValue = array("LCONZP" => "", "LCOIZP" => "");
		
		$stmt_updt = $db->prepare("UPDATE", "FLCONSSO", $keyUpdt, array_keys($fieldsValue));
		
		$sql = "select PARPE4 from FPARFDOC";
		$res = $db->singleQuery($sql);
		
		$path_copy = "";
		if($row = $db->fetch_array($res)) {
			$path_copy = $row['PARPE4']; 
		}
		
		if($path_copy!="" && !file_exists($path_copy)) {
			wi400_mkdir($path_copy, 777, true);
		}
		
		$sql_con = "select LCONOM from FLCONSSO where LCOIDI=?";
		$stmt_con = $db->singlePrepare($sql_con, 0, true);
		
		$success = false;
		foreach($rowsSelectionArray as $key => $val) {
			$keys = explode("|", $key);
//			echo "KEYS:<pre>"; print_r($keys); echo "</pre><br>";
		
			$id = $keys[7];
			
			$path = $keys[4];
			$name = $keys[5];
			
			$file_path = $path."/".$name;
//			echo "FILE: $file_path<br>";

			if($file_path=="" || !file_exists($file_path)) {
//				echo "FILE INPUT NON TROVATO<br>";
				continue;
			}

			$file_name = "";
			$res_con = $db->execute($stmt_con, array($id));
			if($row_con = $db->fetch_array($stmt_con)) {
				$file_name = $row_con['LCONOM'];
			}
			//die("<br>Source:$file_path<br>Dest:$file_copy");
			if($file_name=="") {
//				echo "FILE NAME DI OUTPUT NON TROVATO<br>";
				$messageContext->addMessage("ERROR", "Record non trovato per l'$id in FLCONSSO");
				continue;
			}
			
			$file_copy = $path_copy.$file_name;
//			echo "COPIA: $file_copy<br>";
			//die("<br>Source:$file_path<br>Dest:$file_copy");
			copy($file_path, $file_copy);
			
			// Update di FLCONSSO
			$campi = $fieldsValue;
			$campi["LCOIDI"] = $id;
			
			$res_updt = $db->execute($stmt_updt, $campi);
			
			$success = true;
		}
		
		if($success===true)
			$messageContext->addMessage("SUCCESS", "Files reinviati con successo");
		
		$actionContext->onSuccess($azione, "LIST");
		$actionContext->onError($azione, "LIST", "", "", true);
	}