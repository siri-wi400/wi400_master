<?php 
$start = time();
$AA = array();
require_once 'commwrs_commons.php';
// Prendo i dati del segmento che mi interessa
$dati = $param['segmento']['0001'];
$ENTITA = '0100';
$DATASET = '0001';
$DATASET_DES ="USER_INFO";
$user = $dati['user'];
$ip = $dati['ip'];
//$id = $dati['privateID'];
$id = $param['privateID'];
$save_data =  $settings['doc_root']."upload/commwrs/log/";
if (!file_exists($save_data)) {
	wi400_mkdir($save_data, 0777, True);
}
$data_rif = date("Ymd");
$filename = $save_data.$id."_$data_rif.log";		
// Scrittura del log
//fwrite($handle, "Tempo esecuzione".$tempo. " secondi");
//fclose($handle);
while (True) {
if (checkConnection($id, $ip, $ENTITA, $DATASET, $tipo)==False) {
	$CODE = "999";
	$CODE_MESSAGE  ="CONNESSIONE NON TROVATA";
	$XMLPHP = componiXML();
	break;
}
$sql = "SELECT * FROM FTAB017 WHERE T17LOG='$user'";
$result = $db->query($sql);
$row = $db->fetch_array($result);
if (!$row) {
	$CODE = "999";
	$CODE_MESSAGE  ="SOCIETA UTENTE NON TROVATA";
	$XMLPHP = componiXML();
	break;
}
$AA['societa'] = $row['T17SOC'];
$deposito = $row['T17SOC'];
// Verifico se abilitato in tabella 703
$sql = "SELECT * FROM FTAB703 WHERE T703CD='".$row['T17COD']."'";
$result = $db->query($sql);
$row = $db->fetch_array($result);
if (!$row) {
	$CODE = "999";
	$CODE_MESSAGE  ="UTENTE NON ABILITATO A RF IN TABELLA 703";
	$XMLPHP = componiXML();
	break;
}
$AA['descrizione'] = $row['T703DE'];
$AA['Codice Utente'] = $row['T703CD'];
// ......
// Recupero la descrizione del deposito
$sql = "SELECT MAFDSE FROM FMAFENTI A, LATERAL ( SELECT
rrn(o) AS NREL
FROM   LMAFENTI o
WHERE  A.MAFCDE = o.MAFCDE and
digits(MAFAVA)!!digits(MAFMVA)!!digits(MAFGVA) <=".$data_rif."
FETCH FIRST ROW ONLY ) AS x WHERE rrn(A) = x.NREL AND
digits(MAFAIO)!!digits(MAFMIO)!!digits(MAFGIO) <=".$data_rif." AND
digits(MAFAFO)!!digits(MAFMFO)!!digits(MAFGFO) >=".$data_rif. " AND MAFCDE='".$deposito."'";
$result = $db->query($sql);
$row = $db->fetch_array($result);
$AA['dessoc']=$row['MAFDSE'];

// Applicazioni
/*
$AA['applicazione1']="S;BOLLE_RESO;Spunta Bolle di Reso;BOLLE_RESO.EXE;exe;http://10.0.40.1:89/download/BOLLE_RESO_1_1;1.2;BOLLE_RESO_1_1";
$AA['applicazione2']="M;BOLLE_RESO;Spunta Bolle di Reso;BOLLE_RESO.EXE;exe;http://10.0.40.1:89/download/BOLLE_RESO_1_1;1.2;BOLLE_RESO_1_1";
$AA['applicazione3']="M;CARICHI;Carichi Diretti;W4_PPC_CAR.EXE;cab;http://10.0.40.1:89/download/W4_CAR_CAB.CAB;1.4;W4_CAR_CAB.CAB";
$AA['appCount']="3";
*/
$sql_user_app = "select * from FMO1USRA where MO1USR='".$AA['Codice Utente']."'";
$res_user_app = $db->singleQuery($sql_user_app);
$row_user_app = $db->fetch_array($res_user_app);
if(!empty($row_user_app)) {
	$apps = trim($row_user_app['MO1APP']);
	
	if($apps=="") {
		$CODE = "999";
		$CODE_MESSAGE = "UTENTE '".$AA['Codice Utente']."' ESISTE MA NON HA ASSOCIATA NESSUNA APPLICAZIONE";
		$XMLPHP = componiXML();
		break;
	}
	
	$app_array = explode(",", $apps);
	
//	$AA['appCount']=count($app_array);
	
	$sql_app = "select * from FMOLAPPW where MOLCOD=? and MOLSTA='1' order by TMSMOD desc";
	$stmt_app = $db->singlePrepare($sql_app,0,true);
	
	$i = 0;
	$errors = false;
	$CODE_MESSAGE = "";
	
	foreach($app_array as $app) {
		$res_app = $db->execute($stmt_app, array($app));
		
		$row_app = $db->fetch_array($stmt_app);
		
		if($row_app) {
			$params = array(
				trim($row_app['MOLTIP']),
				trim($row_app['MOLCOD']),
				trim($row_app['MOLDES']),
				trim($row_app['MOLEXE']),
				trim($row_app['MOLINS']),
				trim($row_app['MOLDWN']),
				trim($row_app['MOLVER']),
				trim($row_app['MOLTMP'])
			);
			
			$AA['applicazione'.++$i] = implode(";", $params);
		}
		else {
			$errors = true;
			$CODE_MESSAGE .= "APPLICAZIONE '".$row_app['MOLCOD']."' NON DEFINITA";
		}
	}
	
	if($errors===true) {
		$errors = true;
		$CODE = "999";
		$XMLPHP = componiXML();
		break;
	}
	
	$AA['appCount']=$i;
}
else {
	$CODE = "999";
	$CODE_MESSAGE = "NESSUNA APPLICAZIONE ASSOCIATA ALL'UTENTE '".$AA['Codice Utente']."'";
	$XMLPHP = componiXML();
	break;
}

// time di esecuzione
$end = time();
$tempo = $end-$start;
$AA['tempo']= $tempo;
// Riporto il private id
//$AA['privateID']= $this->privateId;
$ATTRIBUTES = esplodiDati($AA);
$CODE = "0";
$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
$XMLPHP = componiXML();
break;
}


