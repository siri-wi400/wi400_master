<?php

	$azione = $actionContext->getAction();
	
	$history->addCurrent();
/*	
	$data_rif_ini = wi400Detail::getDetailValue($azione."_SRC",'DATA_RIF_INI');
	$data_rif_fin = wi400Detail::getDetailValue($azione."_SRC",'DATA_RIF_FIN');
	
	$data_rif = $data_rif_fin;
	
	$anno_rif = getAnno($data_rif);
	$mese_rif = getMese($data_rif);
	$giorno_rif = getGiorno($data_rif);
*/
	$anno_rif_ini = wi400Detail::getDetailValue($azione."_SRC",'ANNO_SRC_INI');
	$mese_rif_ini = wi400Detail::getDetailValue($azione."_SRC",'MESE_SRC_INI');
	
	$anno_rif_fin = wi400Detail::getDetailValue($azione."_SRC",'ANNO_SRC_FIN');
	$mese_rif_fin = wi400Detail::getDetailValue($azione."_SRC",'MESE_SRC_FIN');
	
	if(!isset($anno_rif_ini)) {
		$data = date("Ymd", mktime(0, 0, 0, date("m")-1, 1, date("Y")));
		
		$anno = substr($data, 0, 4);
		$mese = substr($data, 4, 2);
		
		$anno_rif_ini = $anno;
		$mese_rif_ini = $mese;
		
		$anno_rif_fin = $anno;
		$mese_rif_fin = $mese;
	}
	
	$data_rif_ini = "";
	if($anno_rif_ini!="" && $mese_rif_ini!="")
		$data_rif_ini = sprintf("%04s", $anno_rif_ini).sprintf("%02s", $mese_rif_ini);
	
	$data_rif_fin = "";
	if($anno_rif_fin!="" && $mese_rif_fin!="")
		$data_rif_fin = sprintf("%04s", $anno_rif_fin).sprintf("%02s", $mese_rif_fin);
	
	$data_rif = "";
	if($data_rif_fin!="")
		$data_rif = date("Ymd", mktime(0, 0, 0, $mese_rif_fin+1, 0, $anno_rif_fin));
	
	if($actionContext->getForm()=="DEFAULT") {
		$label = $actionContext->getLabel();
		$actionContext->setLabel("Parametri");
	}
	else if($actionContext->getForm()=="LIST") {
/*				
		$select = "fabcd1, fabcda, fabnbl, fabiva, fabppr,
			fabpro!!digits(fabnft) as FATTURA, (fabppr*fabces) as VALORE, 
			digits(fabasp)!!digits(fabmsp)!!(fabgsp) as DATA_BOL, 
			digits(fabast)!!digits(fabmst)!!(fabgst) as DATA_FAT,
			fabppr, fabces,      
			mdadsa, mdacon, mdatpg, mdagra, mdapez, 
			mafdse";
		$from = "ffabfadi, fmdaanar, fmafenti";
		$where = "fabtip in ('F', 'N', 'B', 'R') and 
			fabast = ".sprintf("%04s", $anno_rif)." and (fabasp * 100 + fabmsp) = ".sprintf("%04s", $anno_rif).sprintf("%02s", $mese_rif)." and 
			fabsta <> '0' and fabpro = 'C8' and 
			fabcda = mdacda and 
			(mdaava * 10000 + mdamva * 100 + mdagva) <= ".dateViewToModel($data_rif)." and 
			(mdaafv * 10000 + mdamfv * 100 + mdagfv) >= ".dateViewToModel($data_rif)." and                                                                  
			fabcd1 = mafcde and 
			(mafava * 10000 + mafmva * 100 + mafgva) <= ".dateViewToModel($data_rif)." and 
			(mafafv * 10000 + mafmfv * 100 + mafgfv) >= ".dateViewToModel($data_rif);
*//*	
		$select = "fabcd1, fabcda, fabnbl, fabiva, fabppr,
			fabpro!!digits(fabnft) as FATTURA, (fabppr*fabces) as VALORE, 
			digits(fabasp)!!digits(fabmsp)!!(fabgsp) as DATA_BOL, 
			digits(fabast)!!digits(fabmst)!!(fabgst) as DATA_FAT,
			fabppr, fabces,
			mhlpes";
		$from = "FFABFADI, FMHLAADP o, LATERAL ( 
					SELECT rrn(i) AS NREL 
					FROM FMHLAADP i 
					WHERE o.mhlcda = i.mhlcda and o.mhlcde = i.mhlcde and digits(mhlava)!!digits(mhlmva)!!digits(mhlgva) <= ".dateViewToModel($data_rif)." 
					ORDER BY mhlcda, digits(mhlava)!!digits(mhlmva)!!digits(mhlgva) desc 
					FETCH FIRST ROW ONLY ) AS x";
*//*		
		$where = "rrn(o)=x.NREL and FABCDA=MHLCDA and o.MHLELI<>'9' and o.MHLSTA='1' and o.MHLCDE=FABCD1 and 
			fabtip in ('F', 'N', 'B', 'R') and 
			fabast = ".sprintf("%04s", $anno_rif)." and (fabasp * 100 + fabmsp) = ".sprintf("%04s", $anno_rif).sprintf("%02s", $mese_rif)." and 
			fabsta <> '0' and fabpro = 'C8'";
*//*		
		$where = "rrn(o)=x.NREL and FABCDA=MHLCDA and
			fabtip in ('F', 'N', 'B', 'R') and
			fabast = ".sprintf("%04s", $anno_rif)." and (fabasp * 100 + fabmsp) = ".sprintf("%04s", $anno_rif).sprintf("%02s", $mese_rif)." and
			fabsta <> '0' and fabpro = 'C8'";
		
		$sql = "select ".$select." from ".$from." where ".$where;
*//*
		$sql = "select fabcd1, fabcda, fabnbl, fabiva, fabppr,
				fabpro!!digits(fabnft) as FATTURA, (fabppr*fabces) as VALORE, 
				digits(fabasp)!!digits(fabmsp)!!(fabgsp) as DATA_BOL, 
				digits(fabast)!!digits(fabmst)!!(fabgst) as DATA_FAT,
				fabppr, fabces
			from FFABFADI
			where fabtip in ('F', 'N', 'B', 'R') and
				fabast = ".sprintf("%04s", $anno_rif)." and 
				(fabasp * 100 + fabmsp) = ".sprintf("%04s", $anno_rif).sprintf("%02s", $mese_rif)." and
				fabsta <> '0' and fabpro = 'C8'";	
*/			
		$sql = "select fabcd1, fabcda, fabnbl, fabiva, fabppr, fabcde, 
				fabpro!!digits(fabnft) as FATTURA, (fabppr*fabces) as VALORE,
				digits(fabasp)!!digits(fabmsp)!!(fabgsp) as DATA_BOL,
				digits(fabast)!!digits(fabmst)!!(fabgst) as DATA_FAT,
				fabces
			from FFABFADI
			where fabtip in ('F', 'N', 'B', 'R') and
				fabast between $anno_rif_ini and $anno_rif_fin and
				(fabasp * 100 + fabmsp) between $data_rif_ini and $data_rif_fin and 	
				fabsta <> '0' and fabpro = 'C8'";
//		echo "SQL: $sql<br>";
		
		subfileDelete($azione."_LIST");
		
		$subfile = new wi400Subfile($db, $azione."_LIST", $settings['db_temp'], 20);
		$subfile->setConfigFileName("MONITOR_INFRASTAT_LIST");
		$subfile->setModulo(rtvModuloAzione($azione));
		
//		$subfile->addParameter("DATA_RIF", dateViewToModel($data_rif));
		$subfile->addParameter("DATA_RIF", $data_rif);
		
		$subfile->setSql($sql);
	}