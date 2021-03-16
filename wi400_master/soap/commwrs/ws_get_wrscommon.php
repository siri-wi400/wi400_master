<?php
global $dati, $paramall, $AA, $errnum, $errori, $connzend, $INTERNALKEY, $routine_path,$base_path;

$start = time();
$AA = array();
$errnum = 0;

require_once 'commwrs_commons.php';
require_once 'coord.php';
require_once 'curl.php';
require_once 'ios_notification.php';

$timestamp = date("Y-m-d-H.i.s.00000");

// Prendo i dati del segmento che mi interessa
$dati = $param['segmento']['0010'];
$paramall = $param;

$ENTITA = '0100';
$DATASET = '0010';
$DATASET_DES ="WRS_COMMON";
$user = $dati['user'];
$ip = $dati['ip'];
$id = $param['privateID'];
$tipo = $dati['tipo'];
$deposito = $dati['deposito'];
$save_data =  $settings['doc_root']."upload/commwrs/log/";

$connzend = $this->conn;

// @todo sistemare While ...

if (!file_exists($save_data)) {
	wi400_mkdir($save_data, 0777, True);
}

$filename = $save_data.$id.".log";
$data_rif = date("Ymd");

$AA['errCount'] = 0;
		
// Scrittura del log
//fwrite($handle, "Tempo esecuzione".$tempo. " secondi");
//fclose($handle);

while(True) {
	/*if (checkConnection($id, $ip)==False) {
		$CODE = "999";
		$CODE_MESSAGE  ="CONNESSIONE NON TROVATA";
		$XMLPHP = componiXML();
		break;
	}*/
	
	if ($tipo=="LOGOUT") {
	    // Sostituire con funzione logout
	    wrs_logout($id);
	    
		$AA['logout']='LOGOUT';
	} 
	elseif ($tipo=="CARICA_DEPOSITI") {
		$AA["TIPO"]="CARICA_DEPOSITI";
		$utente = $dati['user'];
		$filtro ="";
		$AA['DEFDEP']="";
		if ($utente !="") {
			$sql_user = "SELECT T17SOC FROM FTAB017 WHERE T17SIG='0017' AND T17LOG='".$utente."' AND T17STA='1'";
			$result_user = $db->query($sql_user);
			$row_user = $db->fetch_array($result_user);
			if ($row_user) {
				$AA['DEFDEP']=$row_user['T17SOC'];
			}
		}
		$sql ="select mafcde, mafdse  from FMAFENTI A, FTAB018,                              
                LATERAL ( SELECT rrn(o) AS NREL FROM   LMAFENTI o WHERE  A.MAFCDE = o.MAFCDE and 
				digits(MAFAVA)!!digits(MAFMVA)!!digits(MAFGVA) <= ".date("Ymd") ."
                FETCH FIRST ROW ONLY ) AS x             
				where rrn(A) = x.NREL AND MAFTPE=T18COD AND             
    			t18cla = '02'                                       
 				order by mafcde";
		$count = 1;
		$result = $db->query($sql);
		while ($row=$db->fetch_array($result)) {
			$AA['depo_'.$count]=$row['MAFCDE']." ".htmlspecialchars($row["MAFDSE"]);
			$count ++;
		}
		$AA['recCount']=$count-1;
	}
	elseif ($tipo=="CONFERMA_MISURE") {
		$AA["TIPO"]="CONFERMA_MISURE";
		
		$deposito = $dati['deposito'];
		$articolo = $dati['articolo'];
		$altezza = $dati['altezza'];
		$utente = $dati['utente'];
		$larghezza = $dati['larghezza'];
		$profondita = $dati['profondita'];
		$peso = $dati['peso'];				
		
		$rtsmisc = new wi400Routine('RTSMISC', $connzend);
		$rtsmisc->load_description();
		$rtsmisc->prepare();
		$rtsmisc->set('DATINV', date("Ymd"));
		$rtsmisc->set('DEPOSITO', $deposito);
		$rtsmisc->set('UTENTE', $utente);
		$rtsmisc->set('ARTICOLO', $articolo);
		$rtsmisc->set('ALTEZZA', $altezza);
		$rtsmisc->set('LARGHEZZA', $larghezza);
		$rtsmisc->set('PROFONDITA', $profondita);
		$rtsmisc->set('PESO', $peso);
		
		$rtsmisc->call();
		
		if ($rtsmisc->get("FLAG")=='0') {
			
		} else {
			addError('conferma','Errore di scrittura! Verificare i dati');
		}

	}
	elseif ($tipo=="GET_PRICE") {
		$codice = $dati['codice'];
		$coordinate = $dati['coordinate'];
		/*if ($coordinate=="") {
			$coordinate = getCoordsByIp($_SERVER['REMOTE_ADDR']);
			$AA['coord']==$coordinate;
		}*/
		$range = $dati['range'];
		$datcoord = explode("," , $coordinate);
		$latitude = $datcoord[0];
		$longitude = $datcoord[1];
		if ($range=="") {
			$range = 20;
		}
		$distance = $range;
		$unit = "km";
		// Latitudine Nord
		$bearing = 0;
		$tmpcoord = new_coords($latitude, $longitude, $bearing, $distance, $unit);
		$lat_nord = $tmpcoord['latitude'];
		// Latitudine Sud
		$bearing = 180;
		$tmpcoord = new_coords($latitude, $longitude, $bearing, $distance, $unit);
		$lat_sud = $tmpcoord['latitude'];
		// Latitudine Nord
		$bearing = 90;
		$tmpcoord = new_coords($latitude, $longitude, $bearing, $distance, $unit);
		$long_est = $tmpcoord['longitude'];
		// Latitudine Sud
		$bearing = 270;
		$tmpcoord = new_coords($latitude, $longitude, $bearing, $distance, $unit);
		$long_ovest = $tmpcoord['longitude'];			

		$sql = "SELECT EANLAT, EANLON, EANPRZ FROM ZEANDB02 WHERE EANCDB='$codice' AND EANLAT BETWEEN $lat_sud AND $lat_nord AND EANLON BETWEEN $long_ovest AND $long_est";
		//$AA['sql']=$sql;
		$result = $db->query($sql);
		$i=0;
		if ($result) {
			while ($row = $db->fetch_array($result)) {
				$i++;
				//$AA['price'.$i]=$row['EANPRZ'];
				//$AA['coord'.$i]=$row['EANPOS'];
				$AA['dati'][]= array($row['EANPRZ'], $row['EANLAT'].",".$row['EANLON']);
			}
		}
		$AA['recCount']=$i;
		$end = time();
		$tempo = $end-$start;
		$AA['tempo']= $tempo;
		$ATTRIBUTES = esplodiDati($AA);
		$CODE = "0";
		$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
		$XMLPHP = componiXML();
		
	} elseif ($tipo=="CHECK_BARCODE") {
		$deposito = $dati['deposito'];
		$codice = $dati['codice'];
		$coordinate = $dati['coordinate'];
		/*if ($coordinate=="") {
			$coordinate = getCoordsByIp($_SERVER['REMOTE_ADDR']);
			//$AA['coord']=$coordinate;
		}*/
		$datcoord = explode("," , $coordinate);
		$latitude = $datcoord[0];
		$longitude = $datcoord[1];
		$range = $dati['range'];
		//$range = 10;
		$handle = fopen('/home/wi400/log_ws_ean.txt', 'a+');
		$sql = "SELECT * FROM ZEANDB01 WHERE EANCDB='$codice'";
		$result = $db->singleQuery($sql);
		$found = false;
		$trovato = ' NO';
		if ($result) {
			$row = $db->fetch_array($result);
			if ($row) {
				$AA['articolo']=$row['EANCDA'];
				$AA['peso']=0;
				$AA['deslun']=$row['EANDSL'];
				$AA['tipo']=$row['EANTPA'];			
				$AA['picking']="NON CODIFICATO";
				$AA['recCount']="1";
				$found = True;
				$trovato = ' OK';
			}
		}
		if (!$found) {
			// Cerco nel DB alternativo se trovo qualcosa
			$sql = "SELECT GTIN_NM FROM GTIN WHERE GTIN_CD='$codice'";	
			$result = $db->singleQuery($sql);
			if ($result) {
				$row = $db->fetch_array($result);
				if ($row) {
					$AA['articolo']="*GTIN";
					$AA['peso']=0;
					$AA['deslun']=$row['GTIN_NM'];
					$AA['tipo']="";
					$AA['picking']="NON CODIFICATO";
					$found = True;
					$trovato = ' OK';
				}
			}
			if (!$found) {
				$result = $db->singleQuery($sql);
				addError('ean','Codice non trovato in anagrafica');
			}
		}
		// Cerco informazioni sul produttore
		$AA['produttore']="";
		$AA['indirizzo']="";
		$AA['cap']="";
		$AA['comune']="";
		$AA['country']="";
		if (strlen($codice)>=13) {
			$prod = substr($codice,0,7);
			$sql = "SELECT GLN_NM, GLN_ADDR_02, GLN_ADDR_POSTALCODE, GLN_ADDR_CITY,GLN_COUNTRY_ISO_CD  FROM GS1_GCP WHERE GCP_CD='$prod'";
			//$AA['sql_GS1']=$sql;
			$result = $db->singleQuery($sql);
			if ($result) {
				$row = $db->fetch_array($result);
				if ($row) {
					$AA['produttore']=$row['GLN_NM'];
					$AA['indirizzo']=$row['GLN_ADDR_02'];
					$AA['cap']=$row['GLN_ADDR_POSTALCODE'];
					$AA['comune']=$row['GLN_ADDR_CITY'];
					$AA['country']=$row['GLN_COUNTRY_ISO_CD'];
					$trovato = ' OK';
				}
			}		
		}
		$AA['si_prezzi']="N";
		// Cerco se qualcuno ha segnalato il prezzo
			if ($coordinate !="" && $range !="") {
			// Reperisco i limiti delle coordinate per i prezzi
			$unit = "km";
			$distance = $range;
			// Latitudine Nord
			$bearing = 0;
			$tmpcoord = new_coords($latitude, $longitude, $bearing, $distance, $unit);
			$lat_nord = $tmpcoord['latitude'];
			// Latitudine Sud
			$bearing = 180;
			$tmpcoord = new_coords($latitude, $longitude, $bearing, $distance, $unit);
			$lat_sud = $tmpcoord['latitude'];
			// Latitudine Nord
			$bearing = 90;
			$tmpcoord = new_coords($latitude, $longitude, $bearing, $distance, $unit);
			$long_est = $tmpcoord['longitude'];
			// Latitudine Sud
			$bearing = 270;
			$tmpcoord = new_coords($latitude, $longitude, $bearing, $distance, $unit);
			$long_ovest = $tmpcoord['longitude'];			

			$sql = "SELECT * FROM ZEANDB02 WHERE EANCDB='$codice' AND EANLAT BETWEEN $lat_sud AND $lat_nord AND EANLON BETWEEN $long_ovest AND $long_est";
			//$AA['sql']=$sql;
			$result = $db->singleQuery($sql);
			if ($result) {
				if ($rowprezzi = $db->fetch_array($result)) {
					$AA['si_prezzi']="S";
				}
			}
		}
		fwrite($handle, date("Y-M-D_h:i:s"). " - ".$codice. $trovato." - ".$_SERVER['REMOTE_ADDR']."\r\n");
		// time di esecuzione
		$end = time();
		$tempo = $end-$start;
		$AA['tempo']= $tempo;
		$ATTRIBUTES = esplodiDati($AA);
		$CODE = "0";
		$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
		$XMLPHP = componiXML();
		
		break;
	}
	elseif ($tipo== 'SEND_FOTO') {
		$codice = $dati['codice'];
		$foto = $dati['foto'];
		
		//Quello che mi arriva butto nel file
		$file = fopen('/home/crm/foto_app/'.$timestamp.'_'.$codice.'.jpg', 'wb');
		fwrite($file, base64_decode( $foto));
		fclose($file);
		
		//Quello che mi arriva lo comprimo e lo salvo
		/*$tmp = imagecreatefromstring(base64_decode($foto));
		imagejpeg($tmp, '/home/crm/foto_app/'.$timestamp.'_'.$codice.'.jpg', 100);*/
		
	
		$AA["TIPO"]="SEND_FOTO";
		$AA['recCount']="1";
		$ATTRIBUTES = esplodiDati($AA);
		$CODE = "0";
		$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
		$XMLPHP = componiXML();
		break;
	}
	elseif ($tipo=="SEND_MARKET") {
		$coordinate = explode(",", $dati['coordinate']);
		$insegna = $dati['insegna'];
		
//		$sql = "INSERT INTO PHPLIB.POIGPS (POILAT, POILON, POIINS, POINEW)
//			VALUES('".$coordinate[0]."', '".$coordinate[1]."', '".$insegna."', 1)";
		$sql = "INSERT INTO POIGPS (POILAT, POILON, POIINS, POINEW)
			VALUES('".$coordinate[0]."', '".$coordinate[1]."', '".$insegna."', 1)";
		$result = $db->query($sql);
		
		$AA["TIPO"]="SEND_MARKET";
		$AA['recCount']="1";
		$ATTRIBUTES = esplodiDati($AA);
		$CODE = "0";
		$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
		$XMLPHP = componiXML();
		break;
	}
	elseif ($tipo=="GET_CATALOG_ZIP") {
		$versione = $dati['versione'];
		$ultimaVer = 1;
		
		if($versione < $ultimaVer) {
			$stringa = base64_encode(file_get_contents("catalogoDB.zip"));
			$AA['zip'] = $stringa;
		}
	
		$AA["TIPO"]="GET_CATALOG_ZIP";
		$AA['recCount']="1";
		$ATTRIBUTES = esplodiDati($AA);
		$CODE = "0";
		$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
		$XMLPHP = componiXML();
		break;
	}
	elseif ($tipo=="SAVE_LIST_WIM") {
		$guid = $dati['guid'];
		$gps = $dati['gps'];
		$insegna = $dati['insegna'];
		$testo = $dati['testo'];
		
		$sql = "SELECT SPEID FROM ZTSSPE01 WHERE SPEID='".$guid."'";
		$result = $db->query($sql);
		
		if($row = $db->fetch_array($result)) {
			$AA["success"] = 0;
		}else {
			$file = fopen('/home/crm/alberto/lista.csv', 'w');
			fwrite($file, base64_decode($testo));
			fclose($file);
			
			$sql = "INSERT INTO ZTSSPE01 (SPEID, SPEGPS, SPECOD)
				VALUES('".$guid."', '".$gps."', '".substr($insegna, 0, 10)."')";
			$db->query($sql);
			
			$file = fopen('/home/crm/alberto/lista.csv', 'r');
			//$echo = fopen('/home/crm/alberto/echo.txt', 'w');
			//fwrite($echo, $sql."\r\n\n");
			//fwrite($echo, $guid."____".$gps."____".$insegna);
			fgets($file);
			while(!feof($file)) {
				//fwrite($echo, trim(fgets($file)));
				$array = explode(";", trim(fgets($file)));
				$codice = explode('"', $array[2]);
				$offerta = 0;
				if($array[4]) {
					$offerta = 1;
				}
				//fwrite($echo, $codice[1]);
				//fwrite($echo, $array[2]);
				
				$sql = "INSERT INTO ZRWSPE01 (SPEID, SPEEAN, SPEPRZ, SPEQTA, SPEOFF, SPEDES)
				VALUES('".$guid."', '".$codice[1]."', ".str_replace(",", ".", $array[3]).", ".$array[1].", ".$offerta.", '".$array[0]."')";
				//fwrite($echo, $sql."\r\n");
				$db->query($sql);
			}
			fclose($file);
			$AA["success"] = 1;
		}
	
		$AA["TIPO"]="SAVE_LIST_WIM";
		$AA['recCount']="1";
		$ATTRIBUTES = esplodiDati($AA);
		$CODE = "0";
		$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
		$XMLPHP = componiXML();
		break;
	}
	elseif ($tipo=="SEND_DATI") {
		$codice = $dati['codice'];
		$prezzo = $dati['prezzo'];
		$descrizione = $dati['descrizione'];
		$coordinate = $dati['coordinate'];
		$posizione = $dati['coordinate'];
		$offerta = $dati['offerta'];
		$insegna = $dati['insegna'];
		
		if ($coordinate=="") {
		 	$coordinate = getCoordsByIp($_SERVER['REMOTE_ADDR']);
			$AA['coord']==$coordinate;
		}
		if(!$offerta) {
			$offerta = "";
		}
		//$posizione = $dati['posizione'];
		$datcoord = explode("," , $coordinate);
		$latitude = $datcoord[0];
		$longitude = $datcoord[1];
		$AA['coordinate']=$coordinate;
		if (!is_numeric($latitude)) $latitude = 0;
		if (!is_numeric($longitude)) $longitude = 0;		
		if (!is_numeric($prezzo)) $prezzo = 0;
		if (!isset($descrizione)) $descrizione = "";
		// Pulizia Stringhe
		$codice = substr($codice, 0 ,14);
		$descrizione = substr($descrizione, 0 ,50);
		$prezzo = str_replace(",",".", $prezzo);
		$prezzo = number_format($prezzo, 2, ".","");
		$posizione = substr($posizione, 0 ,20);
		$handle = fopen('/home/wi400/log_ws_ean.txt', 'a+');
		$AA["TIPO"]="CONFERMA_BOLLE";
		$sql = "INSERT INTO ZEANDB02 (EANCDB, EANDSL, EANPRZ, EANPOS, EANTIM, EANSTA, EANLAT, EANLON, EANOFF, EANINS)
			VALUES('".$codice."','".$descrizione."',".$prezzo.",'".$posizione."' ,'".getDb2Timestamp()."', '0', $latitude, $longitude, '".$offerta."', '".$insegna."')";
		$result = $db->query($sql);
		fclose($file);
		$AA["SQL"]=$sql;
		$AA['recCount']="1";
		$AA['tempo']= $tempo;
		$ATTRIBUTES = esplodiDati($AA);
		$CODE = "0";
		$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
		$XMLPHP = componiXML();
		break;
	}
	elseif ($tipo=="SEGNALA") {
		$codice = $dati['codice'];
		$motivo = $dati['motivo'];
		if(!$motivo) {
			$motivo = "None";
		}
		$handle = fopen('/home/wi400/log_ws_ean.txt', 'a+');
		$AA["TIPO"]="SEGNALA";
		$sql = "INSERT INTO ZEANDB03 (EANCDB, EANTIM, EANMOT, EANSTA)
			VALUES('".$codice."','".getDb2Timestamp()."', '".$motivo."', '0')";
		$result = $db->query($sql);
		$AA["SQL"]=$sql;
		$AA['recCount']="1";
		$ATTRIBUTES = esplodiDati($AA);
		$CODE = "0";
		$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
		$XMLPHP = componiXML();
		break;
	}
	elseif ($tipo=="PROVA") {
		$targa = $dati['targa'];
		
		$file = fopen('/home/crm/targhe/fileDaEliminare.txt', 'a');
		fwrite($file, "Ho scelto la targa: ".$targa);
		fclose($file);
		
		$AA['risposta'] = 'Ciao dal web service! Hai scelto la targa '.$targa;
		
		$AA["TIPO"]="PROVA";
		$AA['recCount'] = "1";
		$ATTRIBUTES = esplodiDati($AA);
		$CODE = "0";
		$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
		$XMLPHP = componiXML();
		break;
	}
	elseif ($tipo=="GET_JOB") {
		$AA["TIPO"]="GET_JOB";
		require_once $routine_path."/os400/wi400Os400Job.cls.php";
		$list = new wi400Os400Job();
		$list->getList();
		$num = $list->getEntryNum();
		/*for ($i=1; $i<=$num; $i++) {
			$row = $list->getEntry(True);
			$AA['dati'][]=array($row['JOBNAME'], $row['JOBUSER'],$row['JOBNBR'],$row['JOBSTATUS'],$row['JOBACTST']);
		}*/
		$i=0;
		while ($row = $list->getEntry(True)) {
			$i++;
			$AA['dati'][]=array($row['JOBNAME'], $row['JOBUSER'],$row['JOBNBR'],$row['JOBSTATUS'],$row['JOBACTST']);
		}
		$ATTRIBUTES = esplodiDati($AA);
		$AA['recCount']=$i;
		$CODE = "0";
		$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
		$XMLPHP = componiXML();
		break;
	}
	elseif ($tipo=="GET_CPU") {
		$AA["TIPO"]="GET_CPU";
		 require_once $routine_path."/os400/APIFunction.php";

		$rtv_spc = new wi400Routine('QWCRSSTS', $connzend);
		$tracciato = getApiDS("QWCRSSTS", "SSTS0200");
		$rtv_spc->load_description(null, $tracciato, True);
		$do = $rtv_spc->prepare();
		$rtv_spc->set('FORMAT',"SSTS0200");
		$rtv_spc->set('RESET',"*NO");
		$rtv_spc->set('SIZEDATA', 2048);
		$do = $rtv_spc->call(True);
		$dati = $rtv_spc->get('DATI');
		$AA['dati'] = $dati;
		$ATTRIBUTES = esplodiDati($AA);
		$AA['recCount']=1;
		$CODE = "0";
		$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
		$XMLPHP = componiXML();
		break;
	}
	elseif ($tipo=="GET_TARGA") {
	
		$foto = $dati['foto'];
		$targaUS = $dati['targaUS'];
		
		//Quello che mi arriva butto nel file
		$file = fopen('/home/crm/targhe/'.$timestamp.'_targa.jpg', 'wb');
		fwrite($file, base64_decode( $foto));
		fclose($file);
		
		$file = fopen('/home/crm/targhe/targa.txt', 'a');
		fwrite($file, "\r\nTargaUS: ".$targaUS."\r\n");
		fclose($file);
		
		if($targaUS == "true") {
			$file = fopen('/home/crm/targhe/targa.txt', 'a');
			fwrite($file, "\r\nSono dentro al'if\r\n");
			fclose($file);
			$output = exec("alpr -c us -n 5 /home/crm/targhe/".$timestamp."_targa.jpg", $retval);
		}else {
			$file = fopen('/home/crm/targhe/targa.txt', 'a');
			fwrite($file, "\r\nSono nell'else\r\n");
			fclose($file);
			$output = exec("alpr -c eu -n 5 /home/crm/targhe/".$timestamp."_targa.jpg", $retval);
		}
		
		$AA['dati'] = array();
		//$file = fopen('/home/crm/targhe/'.$timestamp.'_targa.txt', 'w');
		for($i = 1; $i < count($retval); $i++) {
			//Mi prendo la targa
			$targa = explode(" ", trim($retval[$i]));
			array_push($AA['dati'], trim($targa[1]));
		}
		
		//fclose($file);
		
		$AA["TIPO"]="GET_TARGA";
		$ATTRIBUTES = esplodiDati($AA);
		$AA['recCount']= 2;
		$CODE = "0";
		$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
		$XMLPHP = componiXML();
		break;
	}
	elseif ($tipo=="SEND_NOTIFICATION_DEVELOPER") {
	
		$targa = $dati['targa'];
		$myToken = $dati['token'];
		$lingua = $dati['lingua'];
		$device = $dati['device'];
		$statoApp = $dati['statoApp'];
		$sendToken = null;
		$sendLingua = "en";
		$sendDevice = null;
	
		/*$file = fopen('/home/crm/targhe/targaDaEliminare.txt', 'w');
		fwrite($file, "\r\nSono quaaaa cazzoooooo Targa: ".$targa."\r\nStato: ".$statoApp."\r\n");
		fclose($file);*/
	
		$sql = "SELECT PRKTOK, PRKLAN, PRKDEV FROM PRKDB01 WHERE PRKTAR='".$targa."'";
		$result = $db->query($sql);
		
		$row = $db->fetch_array($result);
		if ($row) {
			$sendToken = $row['PRKTOK'];
			$sendLingua = $row['PRKLAN'];
			$sendDevice = $row['PRKDEV'];
		}
		
		$AA['send'] = -1;
		if($sendToken) {
			if($sendDevice == "iOS") {
				$file = fopen('/home/crm/targhe/targa.txt', 'a');
				fwrite($file, "Token: ".$sendToken."\r\n");
				fclose($file);
				
				$sql = "SELECT PRKBAD FROM PRKDB01 WHERE PRKTOK='".$sendToken."'";
				$result = $db->query($sql);
				
				$badge = 1;
				
				$row = $db->fetch_array($result);
				if ($row) {
					$badge = $row['PRKBAD']+1;
				}
				
				$rs = sendNotification($sendToken, 1, $badge, $sendLingua);
				
				if($rs) {
					$AA['send'] = 1;
					$sql = "UPDATE PRKDB01 SET PRKBAD=PRKBAD+1 WHERE PRKTAR='".$targa."'";
					$db->query($sql);
					
					$sql = "INSERT INTO PRKDB02 (PRKTOK, PRKTAR, PRKLAN, PRKDEV) VALUES ('".$myToken."', '".$targa."', '".$lingua."', '".$device."')";
					$db->query($sql);
				}else {
					$AA['send'] = 0;
					$sql = "UPDATE PRKDB01 SET PRKBAD=0 WHERE PRKTAR='".$targa."'";
					$db->query($sql);
				}
			}else {
				$file = fopen('/home/crm/targhe/targa.txt', 'a');
				fwrite($file, "Sono dentro a: ".$sendDevice."\r\n");
				fclose($file);
				
				$AA['token'] = $sendToken;
				$AA['lingua'] = $sendLingua;
			}
		}else {
			$AA['send'] = 2;
		}
	
		$AA["TIPO"]="SEND_NOTIFICATION";
		$ATTRIBUTES = esplodiDati($AA);
		$AA['recCount']= 1;
		$CODE = "0";
		$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
		$XMLPHP = componiXML();
		break;
	}
	elseif ($tipo=="SEND_NOTIFICATION") {
	
		$targa = $dati['targa'];
		$myToken = $dati['token'];
		$lingua = $dati['lingua'];
		$device = $dati['device'];
		$statoApp = $dati['statoApp'];
		$sendToken = null;
		$sendLingua = "en";
		$sendDevice = null;
		
		if($statoApp) {
			$check = checkFurto($targa);
		}else {
			$check == -1;
		}
		//$check = checkFurto($targa);
		
		$file = fopen('/home/crm/targhe/targaa.txt', 'w');
		fwrite($file, "\r\nStatoApp: ".$check."\r\n");
		fclose($file);
		
		if($check == -1 || $check == 0) {
			$sql = "SELECT PRKTOK, PRKLAN, PRKDEV FROM PRKDB01 WHERE PRKTAR='".$targa."'";
			$result = $db->query($sql);
		
			$row = $db->fetch_array($result);
			if ($row) {
				$sendToken = $row['PRKTOK'];
				$sendLingua = $row['PRKLAN'];
				$sendDevice = $row['PRKDEV'];
			}
		
			$AA['send'] = -1;
			if($sendToken) {
				if($sendDevice == "iOS") {
					$file = fopen('/home/crm/targhe/targa.txt', 'a');
					fwrite($file, "Token: ".$sendToken."\r\n");
					fclose($file);
		
					$sql = "SELECT PRKBAD FROM PRKDB01 WHERE PRKTOK='".$sendToken."'";
					$result = $db->query($sql);
		
					$badge = 1;
		
					$row = $db->fetch_array($result);
					if ($row) {
						$badge = $row['PRKBAD']+1;
					}
		
					if(!$statoApp) {
						$rs = sendNotification($sendToken, 1, $badge, $sendLingua);
					}else {
						if($statoApp == "production") {
							$rs = sendNotification($sendToken, 1, $badge, $sendLingua);
						}else {
							$rs = sendNotificationDeveloper($sendToken, 1, $badge, $sendLingua);
						}
					}
		
					if($rs) {
						$AA['send'] = 1;
						$sql = "UPDATE PRKDB01 SET PRKBAD=PRKBAD+1 WHERE PRKTAR='".$targa."'";
						$db->query($sql);
							
						$sql = "INSERT INTO PRKDB02 (PRKTOK, PRKTAR, PRKLAN, PRKDEV) VALUES ('".$myToken."', '".$targa."', '".$lingua."', '".$device."')";
						$db->query($sql);
					}else {
						$AA['send'] = 0;
						$sql = "UPDATE PRKDB01 SET PRKBAD=0 WHERE PRKTAR='".$targa."'";
						$db->query($sql);
					}
				}else {
					$file = fopen('/home/crm/targhe/targa.txt', 'a');
					fwrite($file, "Sono dentro a: ".$sendDevice."\r\n");
					fclose($file);
		
					$AA['token'] = $sendToken;
					$AA['lingua'] = $sendLingua;
				}
			}else {
				$AA['send'] = 2;
			}
		}else {
			if($check != -1) {
				$AA['furto'] = $check;
			}else {
				$AA['furto'] = 0;
			}
		}
	
		$AA["TIPO"]="SEND_NOTIFICATION";
		$ATTRIBUTES = esplodiDati($AA);
		$AA['recCount']= 1;
		$CODE = "0";
		$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
		$XMLPHP = componiXML();
		break;
	}
	elseif ($tipo=="SEND_ANSWER") {
		
		$targa = $dati['targa'];
		$file = fopen('/home/crm/targhe/targa2.txt', 'a');
		fwrite($file, "targa: ".$targa."\r\n");
		fclose($file);
	
		$sql = "SELECT PRKTOK, PRKLAN, PRKDEV FROM PRKDB02 WHERE PRKTAR='".$targa."'";
		$result = $db->query($sql);
		
		$AA['tokens'] = "";
		$flag = 0;
		while($row = $db->fetch_array($result)) {
			$file = fopen('/home/crm/targhe/targa2.txt', 'a');
			fwrite($file, "token: ".$row['PRKTOK']);
			fclose($file);
			
			if($row['PRKDEV'] == "iOS") {
				$rs = sendNotification($row['PRKTOK'], 0, 1, $row['PRKLAN']);
					
				if($rs) {
					$file = fopen('/home/crm/targhe/targa2.txt', 'a');
					fwrite($file, "Risposta mandata\r\n");
					fclose($file);
				}else {
					$file = fopen('/home/crm/targhe/targa2.txt', 'a');
					fwrite($file, "Risposta non mandata\r\n");
					fclose($file);
				}
			}else {
				$AA['tokens'] .= $row['PRKTOK'].",";
			}

			$flag = 1;
		}
		
		$AA['tokens'] = substr($AA['tokens'], 0, -1);
		
		if($flag) {
			$sql = "DELETE FROM PRKDB02 WHERE PRKTAR='".$targa."'";
			$db->query($sql);
		}
	
		$AA["TIPO"]="SEND_ANSWER";
		$ATTRIBUTES = esplodiDati($AA);
		$AA['recCount']= 1;
		$CODE = "0";
		$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
		$XMLPHP = componiXML();
		break;
	}
	elseif ($tipo=="ADD_ANSWER") {
	
		$token = $dati['token'];
		$targa = $dati['targa'];
		$lingua = $dati['lingua'];
		$device = $dati['device'];
	
		$file = fopen('/home/crm/targhe/targa.txt', 'w');
		fwrite($file, 	"ADD ANSWER - Token: ".$token."\r\n".
				"Targa: ".$targa."\r\n".
				"Lingua: ".$lingua."\r\n".
				"device: ".$lingua."\r\n");
		fclose($file);
	
		$sql = "INSERT INTO PRKDB02 (PRKTOK, PRKTAR, PRKLAN, PRKDEV)
			VALUES('".$token."', '".$targa."', '".$lingua."', '".$device."')";
		$db->query($sql);
	
		$AA["TIPO"]="ADD_ANSWER";
		$ATTRIBUTES = esplodiDati($AA);
		$AA['recCount']= 1;
		$CODE = "0";
		$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
		$XMLPHP = componiXML();
		break;
	}
	elseif ($tipo=="ADD_PARKING") {
	
		$token = $dati['token'];
		$targa = $dati['targa'];
		$lingua = $dati['lingua'];
		$device = $dati['device'];
	
		$file = fopen('/home/crm/targhe/targa.txt', 'w');
		fwrite($file, 	"Token: ".$token."\r\n".
						"Targa: ".$targa."\r\n".
						"Lingua: ".$lingua."\r\n".
						"device: ".$lingua."\r\n");
		fclose($file);
		
		$sql = "INSERT INTO PRKDB01 (PRKTOK, PRKTAR, PRKBAD, PRKLAN, PRKDEV)
			VALUES('".$token."', '".$targa."', 0, '".$lingua."', '".$device."')";
		$db->query($sql);
	
		$AA["TIPO"]="ADD_PARKING";
		$ATTRIBUTES = esplodiDati($AA);
		$AA['recCount']= 1;
		$CODE = "0";
		$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
		$XMLPHP = componiXML();
		break;
	}
	elseif ($tipo=="REMOVE_PARKING") {
	
		$token = $dati['token'];
	
		$file = fopen('/home/crm/targhe/targa.txt', 'w');
		fwrite($file, "token: ".$token."\r\n");
		fclose($file);
	
		$sql = "DELETE FROM PRKDB01 WHERE PRKTOK='".$token."'";
		$db->query($sql);
	
		$AA["TIPO"]="REMOVE_PARKING";
		$ATTRIBUTES = esplodiDati($AA);
		$AA['recCount']= 1;
		$CODE = "0";
		$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
		$XMLPHP = componiXML();
		break;
	}
	elseif ($tipo=="RESET_BADGE") {
	
		$token = $dati['token'];
	
		$file = fopen('/home/crm/targhe/targa.txt', 'w');
		fwrite($file, "token RESET badge: ".$token."\r\n");
		fclose($file);
	
		$sql = "UPDATE PRKDB01 SET PRKBAD=0 WHERE PRKTOK='".$token."'";
		$db->query($sql);
	
		$AA["TIPO"]="RESET_BADGE";
		$ATTRIBUTES = esplodiDati($AA);
		$AA['recCount']= 1;
		$CODE = "0";
		$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
		$XMLPHP = componiXML();
		break;
	}
	elseif ($tipo=="CHECK_BARCODE_2") {
		$AA["TIPO"]="CHECK_BARCODE";
		
		$deposito = $dati['deposito'];
		$codice = $dati['codice'];
//		$articolo = $dati['articolo'];
		
//		$AA['errCount']=0;
		
		$rtcean2 = new wi400Routine('RTCEAN2', $connzend);
		$rtcean2->load_description();
		$rtcean2->prepare();
		$rtcean2->set('DATINV', date("Ymd"));
		$rtcean2->set('EAN', $codice);
		$rtcean2->call();
		
		if ($rtcean2->get("FLAG")=='0') {
			$articolo = $rtcean2->get("ARTICOLO");
			
			$AA['articolo']=$articolo;
			
					$rtlart = new wi400Routine('RTLART', $connzend);
					$rtlart->load_description();
					$rtlart->prepare();
					$rtlart->set('DATINV', date("Ymd"));
					$rtlart->set('NUMRIC', 1);
					$rtlart->set('ARTICOLO', $articolo);
					$rtlart->call();
					
					if ($rtlart->get('FLAG')=='0') {
						$AA['deslun']=$rtlart->get('DART60');
						$AA['tipo']=$rtlart->getDSPARM('ARTI', 'MDATPA');
						
						// Controllo se per caso � in eliminazione
						$rtlaad = new wi400Routine('RTLAA1', $connzend);
						$rtlaad->load_description();
						$rtlaad->prepare();
						$rtlaad->set('DATARF', date("Ymd"));
						$rtlaad->set('CODICE',$deposito);
						$rtlaad->set('CODART',$articolo);
						$rtlaad->call();
						
						$eliminazione = '0';
						$altezza = '0';
						$larghezza = '0';
						$profondita = '0';
						$peso = '0';
						
						if($rtlaad->get('FLAG')=="0") {
							$dim = $rtlaad->get('AADP');
							$eliminazione = $dim['MHLELI'];
							$altezza = $dim['MHLALT'];
							$larghezza = $dim['MHLLAR'];
							$profondita = $dim['MHLPRO'];
							$peso = $dim['MHLPES'];
						} else {
							addError('ean','Articolo non trovato in deposito');
						}
						
						$AA['eliminazione']=$eliminazione;
						$AA['altezza']=$altezza;
						$AA['larghezza']=$larghezza;
						$AA['profondita']=$profondita;
						$AA['peso']=$peso;
						
						// Reperisco il posto picking
						$rtlpik = new wi400Routine('RTLPIK', $connzend);
						$ciccio = $rtlpik->load_description();
						$ciccio = $rtlpik->prepare();
						$rtlpik->set('ARTICOLO',$articolo);
						$rtlpik->set('DEPOSITO',$deposito);
						$do = $rtlpik->call();
						
						$mhp = $rtlpik->get('FMHP');
						
						$AA['picking']=$mhp['MHPZOD'].".".$mhp['MHPCOR'].".".$mhp['MHPBAY'].".".$mhp['MHPPOS'];
						$AA['recCount']="1";
					}
					else {
						addError('ean','Articolo legato ad Ean non trovato in anagrafica');
					}
		}
		else {
			addError('ean','Codice non trovato in anagrafica');
		}
	}elseif ($tipo=="GET_FILE_ACCOUNT") {
		$azione = $dati['azione'];
	
		/*$file = fopen('dati_json.txt', 'r');
		fwrite($file, "Sono qui: ".$azione."\r\n");
		fclose($file);*/
		$AA['account'] = base64_encode(file_get_contents("dati_app_commander.txt"));
	
		$AA["TIPO"]=$tipo;
		$ATTRIBUTES = esplodiDati($AA);
		//$AA['recCount']= 1;
		$CODE = "0";
		$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
		$XMLPHP = componiXML();
		break;
	}elseif ($tipo=="a") {
		$azione = $dati['azione'];
		if(isset($dati['getStatus'])) {
			$AA['getStatus'] = "yes STATUS A";
			//$AA['urlImg'] = "http://4everstatic.com/immagini/80x80/astratti/sfondo-colorato,-sfondo-astratto-225862.jpg";
		}else {
			$AA['evento'] = "window";
			$id_otm = create_OTM("VEGANEW");
			$AA['url'] = "http://10.0.40.1:89/WI400_PASIN/index.php?OTM=$id_otm&t=JQUERY_VALID&LCK_DLT=true&f=";
			/*$AA['evento'] = "alert";
			$AA['messaggio'] = "Non puoi entrare!";*/
			
		}
	
		/*$file = fopen('/home/crm/targhe/test.txt', 'w');
		fwrite($file, "Sono qui: ".$azione."\r\n");
		fclose($file);*/
	
		$AA["TIPO"]=$tipo;
		$ATTRIBUTES = esplodiDati($AA);
		//$AA['recCount']= 1;
		$CODE = "0";
		$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
		$XMLPHP = componiXML();
		break;
	}elseif ($tipo=="inventario") {
		$azione = $dati['azione'];
		if(isset($dati['getStatus'])) {
			$AA['getStatus'] = "yes STATUS B";
			//$AA['urlImg'] = "http://marano93.altervista.org/immagini/viola.gif";
		}else {
			if(isset($dati['barcode'])) {
				$barcode = $dati['barcode'];
				$descrizione = $dati['descrizione'];
				$produttore = $dati['produttore'];
				$indirizzo = $dati['indirizzo'];
				
				$AA['evento'] = "window";
				$AA['login'] = "si";
				$id_otm = create_OTM("VEGANEW");
				
				$url = "/WI400/index.php?OTM=$id_otm&t=APP_INVENTARIO&LCK_DLT=true&f=&articolo=$barcode&descrizione=$descrizione&DECORATION=app";
				//$url = "https://89.96.201.82/WI400/index.php?OTM=$id_otm&t=APP_INVENTARIO&LCK_DLT=true&f=&articolo=$barcode&descrizione=$descrizione&DECORATION=app";
				//$url = "http://10.0.40.1:89/WI400_PASIN/index.php?OTM=$id_otm&t=APP_INVENTARIO&LCK_DLT=true&DECORATION=app&f=&articolo=$barcode&descrizione=$descrizione";
				$AA['url'] = $url;
			}else {
				$AA['evento'] = "alert";
				$AA['messaggio'] = "Errore reindirizzamento";
			}
		}
	
		/*$file = fopen('/home/crm/targhe/test.txt', 'w');
		fwrite($file, "Sono qui: ".$azione."\r\n");
		fclose($file);*/
	
		$AA["TIPO"]=$tipo;
		$ATTRIBUTES = esplodiDati($AA);
		//$AA['recCount']= 1;
		$CODE = "0";
		$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
		$XMLPHP = componiXML();
		break;
	}elseif ($tipo=="prezzi") {
		$azione = $dati['azione'];
		if(isset($dati['getStatus'])) {
			$AA['getStatus'] = "yes STATUS c";
			//$AA['urlImg'] = "http://4everstatic.com/immagini/80x80/astratti/sfondo-colorato,-sfondo-astratto-225862.jpg";
		}else {
			if(isset($dati['barcode'])) {
				$barcode = $dati['barcode'];
				$descrizione = $dati['descrizione'];
				$produttore = $dati['produttore'];
				$indirizzo = $dati['indirizzo'];
			
				$AA['evento'] = "window";
				//$AA['http'] = "si";
				$AA['login'] = "si";
				$id_otm = create_OTM("VEGANEW");
			
				//$url = "https://89.96.201.82/WI400/index.php?OTM=$id_otm&t=CONTROLLO_PREZZI&LCK_DLT=true&DECORATION=app&f=&articolo=$barcode&descrizione=$descrizione";
				//$url = "http://10.0.40.1:89/WI400_pasin/index.php?OTM=$id_otm&t=INFO_ART&LCK_DLT=true&DECORATION=app&f=&articolo=$barcode&descrizione=$descrizione";
				$url = "/WI400/index.php?OTM=$id_otm&t=INFO_ART&LCK_DLT=true&DECORATION=app&f=&articolo=$barcode&descrizione=$descrizione";
				$AA['url'] = $url;
			}else {
				$AA['evento'] = "alert";
				$AA['messaggio'] = "Errore reindirizzamento";
			}
		}
	
		/*$file = fopen('/home/crm/targhe/test.txt', 'w');
		fwrite($file, "Sono qui: ".$azione."\r\n");
		fclose($file);*/
	
		$AA["TIPO"]=$tipo;
		$ATTRIBUTES = esplodiDati($AA);
		//$AA['recCount']= 1;
		$CODE = "0";
		$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
		$XMLPHP = componiXML();
		break;
	}
	
	// time di esecuzione
	$end = time();
	$tempo = $end-$start;
	$AA['tempo']= $tempo;
	$AA['tipo'] = $tipo;
	$ATTRIBUTES = esplodiDati($AA);
	$CODE = "0";
	$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
	$XMLPHP = componiXML();
	
	break;
}

function addError($fld, $msg) {
	global $AA, $errnum, $errori;
	$errnum = $errnum + 1;
	$AA['ERRORE'.$errnum]=$msg;
	$AA['FLDERR'.$errnum]=$fld;
	$AA['errCount']=$errnum;
	$errori = True;
}