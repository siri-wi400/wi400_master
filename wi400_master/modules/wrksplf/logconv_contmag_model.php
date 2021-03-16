<?php

	require_once 'logconv_contmag_common.php';

	$azione = $actionContext->getAction();
//	echo "AZIONE: $azione - FORM: ".$actionContext->getForm()."<br>";
	
	if(in_array($actionContext->getForm(), array("DEFAULT", "LIST")))
		$history->addCurrent();
	
	$societa = wi400Detail::getDetailValue($azione."_SRC",'SOCIETA');
	$mese = wi400Detail::getDetailValue($azione."_SRC",'MESE');
	$anno = wi400Detail::getDetailValue($azione."_SRC",'ANNO');
	$user = wi400Detail::getDetailValue($azione."_SRC",'USER');
	
	$data_rif_ana = date("Ymd");
	if(isset($anno) && !empty($anno)) {
		if(isset($mese) && !empty($mese)) {
			$data_rif_ana = date("Ymd", mktime(0,0,0,$mese+1,0,$anno));
		}
		else {
			$data_rif_ana = $anno."1231";
		}
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
			if($azione=="LOGCONV_CONTMAG" && $val=="N")
				continue;
			
			$len = strlen($key);
			$where_mod[] = "substr(LOGMOD, 1, $len)='$key'";
		}
		
		if(!empty($where_mod)) {
			$where_array[] = "(".implode(" or ", $where_mod).")";
		}
		
		// Code di stampa
		if($azione=="LOGCONV_FATTURE") {
			if(!empty($mod_outq_abil))
				$where_array[] = "LOGOUT in ('".implode("', '", $mod_outq_abil)."')";
		}
			$where_array[] = "LOGKU1='CONTMAG'";
		if(isset($mese) && $mese!="" && isset($anno) && $anno!=""){
			$periodo_dal = $anno.$mese."01";
			$periodo_al = $anno.$mese."31";
			$where_array[] = "LOGKU3 between '$periodo_dal' and '$periodo_al'";
		}
		if(isset($mese) && $mese=="" && isset($anno) && $anno!=""){
			$periodo_dal = $anno."0101";
			$periodo_al = $anno."1231";
			$where_array[] = "LOGKU3 between '$periodo_dal' and '$periodo_al'";
		}
		if(isset($societa) && $societa!="")
			$where_array[] = "LOGKU4='$societa'";
		
		if(isset($user) && $user!="")
			$where_array[] = "LOGUSR='$user'";
		
		$where = implode(" and ", $where_array);
//		echo "WHERE: $where<br>";
	}

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