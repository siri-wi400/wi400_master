<?php

	$azione = $actionContext->getAction();
	
//	if(in_array($actionContext->getForm(), array("DEFAULT", "LIST")))
		$history->addCurrent();
	
	$ambito_sel = array();
	if(wi400Detail::getDetailValue($azione."_SRC", "AMBITO")!="")
		$ambito_sel = wi400Detail::getDetailValue($azione."_SRC", "AMBITO");
	
	$area_fun_sel = array();
	if(wi400Detail::getDetailValue($azione."_SRC", "AREA_FUN")!="")
		$area_fun_sel = wi400Detail::getDetailValue($azione."_SRC", "AREA_FUN");
	
	$data_ini = wi400Detail::getDetailValue($azione."_SRC", "DATA_INI");
	
	$data_fin = wi400Detail::getDetailValue($azione."_SRC", "DATA_FIN");
	
	$tipo_segn_sel = array();
	if(wi400Detail::getDetailValue($azione."_SRC", "TIPO_SEGN")!="")
		$tipo_segn_sel = wi400Detail::getDetailValue($azione."_SRC", "TIPO_SEGN");
	
	$grp_err_sel = array();
	if(wi400Detail::getDetailValue($azione."_SRC", "GRP_ERR")!="")
		$grp_err_sel = wi400Detail::getDetailValue($azione."_SRC", "GRP_ERR");
	
	$gravita_sel = array();
	if(wi400Detail::getDetailValue($azione."_SRC", "GRAVITA")!="")
		$gravita_sel = wi400Detail::getDetailValue($azione."_SRC", "GRAVITA");
	
	$cod_err_sel = array();
	if(wi400Detail::getDetailValue($azione."_SRC", "COD_ERR")!="")
		$cod_err_sel = wi400Detail::getDetailValue($azione."_SRC", "COD_ERR");
	
	$des_evento_option = get_text_condition("DES_EVENTO_SRC", $azione);
	$des_evento = wi400Detail::getDetailValue($azione."_SRC",'DES_EVENTO_SRC');
//	echo "DES EVENTO OPTION: $des_evento_option - DES EVENTO: $des_evento<br>";

	$des_agg_option = get_text_condition("DES_AGG_SRC", $azione);
	$des_agg = wi400Detail::getDetailValue($azione."_SRC",'DES_AGG_SRC');
//	echo "DES AGG OPTION: $des_agg_option - DES AGG: $des_agg<br>";
	
	if(!in_array($actionContext->getForm(), array("DEFAULT", "LIST"))) {
		$keyArray = array();
		$keyArray = getListKeyArray($azione."_LIST");
		
		$ambito = $keyArray['IFLAMB'];
		$area_fun = $keyArray['IFLFUN'];
		$file_path = $keyArray['IFLFIL'];
		$cod_err = $keyArray['IFLCOD'];
		$des_err = $keyArray['IFEDES'];
		$gravita = $keyArray['IFLGVT'];
		$data = $keyArray['IFLDMO'];
		$ora = $keyArray['IFLHMO'];
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		$label = $actionContext->getLabel();
		$actionContext->setLabel("Parametri");
		
		unset($_SESSION[$azione.'_DES_EVENTO_SRC_OPTION']);
		unset($_SESSION[$azione.'_DES_AGG_SRC_OPTION']);
	}
	else if($actionContext->getForm()=="LIST") {
		$where_array = array();
		
		if(!empty($ambito_sel)) {
			$where_array[] = "IFLAMB in ('".implode("', '", $ambito_sel)."')";
		}
		
		if(!empty($area_fun_sel)) {
			$where_array[] = "IFLFUN in ('".implode("', '", $area_fun_sel)."')";
		}
		
		if(!empty($data_ini)) {
			$where_array[] = "IFLDMO between ".dateViewToModel($data_ini)." and ".dateViewToModel($data_fin);
		}
		
		if(!empty($tipo_segn_sel)) {
			$where_array[] = "IFLTIP in ('".implode("', '", $tipo_segn_sel)."')";
		}
		
		if(!empty($grp_err_sel)) {
			$where_array[] = "IFLGRP in ('".implode("', '", $grp_err_sel)."')";
		}
		
		if(!empty($gravita_sel)) {
			$where_array[] = "IFLGVT in ('".implode("', '", $gravita_sel)."')";
		}
		
		if(!empty($cod_err_sel)) {
			$where_array[] = "IFLCOD in ('".implode("', '", $cod_err_sel)."')";
		}
		
		// Descrizione Evento
		if($des_evento!="") {
			$where_array[] = where_text_condition($des_evento_option, $des_evento, "IFLDES");
		}
		
		// Descrizione Aggiuntiva
		if($des_agg!="") {
			$where_array[] = where_text_condition($des_agg_option, $des_agg, "IFLDE2");
		}
		
		$where = "";
		if(!empty($where_array)) {
			$where = implode(" and ", $where_array);
		}
	}
	else if($actionContext->getForm()=="FILE_VIEW") {
		$actionContext->setLabel("Dettaglio File");
		
//		wi400Detail::cleanSession($azione."_".$actionContext->getForm());
		
		$size = 0;
		if(file_exists($file_path)) {
//			$size = filesize($file_path);
//			if($size<20000000) {
				$path_parts = pathinfo($file_path);
//				if(isset($path_parts['extension']) && $path_parts['extension']=="log")
					$lines = file_get_contents($file_path);
//					echo "LINES: $lines<br>";

//					$lines = wordwrap($lines, 80, "\r\n");
//			}
		}
	}
	else if($actionContext->getForm()=="ERR_VIEW") {
		$actionContext->setLabel("Dettaglio Errore");
		
//		wi400Detail::cleanSession($azione."_".$actionContext->getForm());
		
		$sql = "select IFECOD, IFEDES, IFEEST 
			from FIFSERRO
			where IFECOD='$cod_err'";
		$res = $db->singleQuery($sql);
		$row = $db->fetch_array($res);
//		echo "ROW:<pre>"; print_r($row); echo "</pre>";

		$des_est = $row['IFEEST'];
//		$normalizeChars = normalizeChars();
//		$des_est = strtr($des_est, $normalizeChars);

		$des_est = wordwrap($des_est, 80, "\r\n");
	}