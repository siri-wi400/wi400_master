<?php
$timeout = 300;
$authSysInf = parse_ini_file("././soap/template/abilitazioni.ini");
$sysPooling = parse_ini_file("pooling.php");
require_once $routine_path."/os400/wi400Os400Job.cls.php";
// Scrittura log di inzio pooling
$file = "/home/wi400/pooling.txt";
$handle = fopen($file, "a");
$time_end = microtime(true);
$time = $time_end - $time_start;
$dati = date("d:m:Y-H:i:s")." Inizio Job Pooling"."\r\n";
fwrite($handle, $dati);
// Ciclo sui vari sottosistemi per controllare quanti lavori ho attivi e aprire quelli mancani
foreach($sysPooling as $key=>$value)
{
	        // Se ho un valore maggiore di 0
			if ($value > 0) {
				$dati = date("d:m:Y-H:i:s")." Sistema Informativo:".$key." lavori minimi $value\r\n";
				fwrite($handle, $dati);
				poolJob($key, $value, $handle);
	        }
}
$dati = date("d:m:Y-H:i:s")." Fine job!"."\r\n";
fwrite($handle, $dati);
fclose($handle);
// Dsconnessione del lavoro utilizzato per lanciare i comandi
if (isset($settings['xmlservice'])) {
	$InputXML = '<?xml version="1.0"?>';
	$InternalKey = $mykey;
	$ControlKey="*immed";
	$OutputXML = '';
	$callPGM = $db->getCallPGM();
	if ($settings['xmlservice_driver']=="DB") {
		$db->bind_param ($callPGM, 1, "InternalKey", DB2_PARAM_IN );
		$db->bind_param ($callPGM, 2, "ControlKey", DB2_PARAM_IN );
		$db->bind_param ($callPGM, 3, "InputXML", DB2_PARAM_IN );
		$db->bind_param ($callPGM, 4, "OutputXML", DB2_PARAM_OUT );
		$ret = db2_execute($callPGM);
	} else if($settings['xmlservice_driver']=="PDO") {
		$result = $callPGM->execute(array($InternalKey, $ControlKey, $InputXML));
		if (!$result) {
			return false;
		}
		$OutputXML="";
		$row = $callPGM->fetchAll(PDO::FETCH_NUM);
		//print_r($row);
		foreach ($row as $key => $value) {
			$OutputXML.=$value[0];
		}
	}
}
// Fine programma
exit(0);

function insertKey($key, $privateId) {
		$seg = msg_get_queue($key) ;
		$daten=$privateId."/".time();
    	msg_send ($seg, 1, $daten, true, true, $msg_err);				
}
/**
 * 
 * Verifico quanti lavori sono aperti per il sistema informativo e ne apro tanti fino ad arriva al numero impostato da prametro
 * 
 **/	
function poolJob($sysinf, $attivi, $handle){

	      global $db, $settings, $INTERNALKEY, $CONTROLKEY, $authSysInf, $timeout, $mykey;
     
//	      $db->add_to_librarylist(array("PHPLIB"), True);
	      $db->add_to_librarylist(array($settings['db_name']), True);
          // Cerco di recuperare una connessione già esistente in coda per il sistema informativo
          //$this->privateId=0;
          if (substr($sysinf,0 ,1)=='Y') {
          	$settings['db_host']='QUALITY';
          }
		  $db->set($settings['db_host'], $settings['db_user'], $settings['db_pwd'], $settings['db_name']);
          // Inizializzo il Private ID
       	  if (isset($settings['i5_toolkit'])) { 
              $privateId=0;
       	  } else {
              $privateId=uniqid("WS_");
       	  }
          // Verifico se mi è stato passato un ID privato di connessione
          $Key=$authSysInf["$sysinf"];
          //$Key = 1;
          $DTAQKey=$Key;
          $msgtype_receive=1;
          $maxsize=1000;
	      $message='';   
		  $serialize_needed=True;
		  $block_send=false;      
		  $msgtype_send=1;  
		  $option_receive=MSG_IPC_NOWAIT;
		  $seg = msg_get_queue($Key);
		  $queue_status=msg_stat_queue($seg);
		  $time_start = microtime(true);		 
		  // Verifico quanti ce ne sono fisicamente attivi.
          $numjob = 0;
          $list = new wi400Os400Job("I".substr($sysinf,0, 9));
          $list->getList();
          $messaggi = array();
          // Verifico il numero di lavori attivi e la relativa chiave
          while ($dati = $list->getEntry()) {
          	$key = str_pad($dati['JOBNAME'], 10).str_pad($dati['JOBUSER'], 10).str_pad($dati['JOBNBR'], 6);
          	//echo $key."\r\n";
          	$dati = $list->getJobLogMsg($key, "*FIRST", "");
          	$messaggi[]=$dati['MESSAGE'];
          	$dati = $list->getJobLogMsg($key, "*NEXT", $dati['KEY']);
          	$messaggi[]=$dati['MESSAGE'];
          	$dati = $list->getJobLogMsg($key, "*NEXT", $dati['KEY']);
          	$messaggi[]=$dati['MESSAGE'];
          	$numjob++;
          }
          $active_key = array();
          // Metto in array le chiavi
          foreach ($messaggi as $key => $value) {
          	//echo "\r\nValore:".$value;
          	if (strpos($value, "CALL PGM(".$settings['xmlservice_lib']."/XMLSERVICE) PARM")!==False) {
          		//echo "passo di qua!!";
          		$key = strpos($value, "WS_",0);
          		$fine = strpos($value, ")", $key);
          		$len = $fine-$key-1;
          		$internal_key = substr($value, $key, $len);
          		$active_key[] = $internal_key;
          		//echo $key. " ---> ".$internal_key."\r\n";
          	}
          }
          $mykey = $INTERNALKEY;
          $dati = date("d:m:Y-H:i:s")." Lavori Attivi:".$numjob."\r\n";
          fwrite($handle, $dati);
          if ($numjob==0) {
          	// Ripulisco la coda
          	msg_remove_queue($seg);
          	$dati = date("d:m:Y-H:i:s")." Pulizia Coda:".$numjob."\r\n";
          	$seg = msg_get_queue($Key);
          }
		  // Se in numero congruo esco
		  if ($numjob >= $attivi) {
		  	//return true;
		  }
		  //print_r($active_key);
		  //echo "job:".$numjob;
		  // Se in numero minore controllo le code, non si sa mai che qualcuno sia in scadenza e in ogni caso li rinfresco per tenerli attivi
          $k=0;
		  //if ($numjob < $value) {
			  // Controllo se in coda c'è già qualcosa
			  if ($queue_status['msg_qnum']>0) {
			  // Cerco una connessione non scaduta
			      $i=0;
				  for ($i; $i<=$queue_status['msg_qnum']-1; $i++){
				      if (msg_receive($seg,$msgtype_receive ,$msgtype_erhalten,$maxsize,$daten,$serialize_needed, $option_receive, $err)===true) {
				      	$valori = explode("/", $daten);
				              //echo "<br>key:".$valori[0];
				              if (in_array($valori[0], $active_key) && $valori[0]!="") {
				              	  //echo $valori[0];
					              $diff = time() - $valori[1];
					              if ($diff < $timeout) {
					                 //$this->privateId=intval($valori[0]);
					                 $k++;
					                 if (isset($settings['i5_toolkit'])) { 
						                 insertKey($DTAQKey,intval($valori[0]));
					                 } else {
					                     insertKey($DTAQKey,$valori[0]);
					                 }    
					              }
				              }
				  	   }
			  	   }
		       }
		  //}
		  $dati = date("d:m:Y-H:i:s")." Lavori in coda $DTAQKey:".$k."\r\n";
		  fwrite($handle, $dati);
		  // Calcolo il numero dei lavori mancanti
		  //echo "K:".$k;
		  //echo "VALUE;".$attivi;
		  $mancano = $attivi - $k;
		  //echo "mancano:".$mancano;
		  if ($mancano <= 0) {
		  	return true;
		  }
		  $dati = date("d:m:Y-H:i:s")." Lavori da istanziare:".$mancano."\r\n";
		  fwrite($handle, $dati);
          // Creazione lavori mancanti
          $kl=0;
          for ($j=1;$j<=$mancano;$j++) {
          	  $kl++;
          	  //echo "<br>Progressivo $kl"; 
			  if (isset($settings['i5_toolkit'])) {
			  	$privateId=0;
			  } else {
			  	$privateId=uniqid("WS_");
			  }
	          if (isset($settings['i5_toolkit'])) {
		          $mycon->set_options("I".substr($sysinf,0, 9),null ,null ,null , null , null, $this->timeout , True, $privateId);
			      $conn = $mycon->connect();
				  $privateId=$mycon->getPrivateId();	      
			      if (!is_resource($conn)){
		 	           return '3';
				  }
	          } else {
				  $CONTROLKEY = '*idle('.$timeout.') *sbmjob(ZENDSVR/ZSVR_JOBD/'."I".substr($sysinf,0, 9).')';
				  $INTERNALKEY = '/tmp/'.$privateId;
	          }
	          insertKey($DTAQKey,$privateId);
	          //echo "<br>Key $privateId";
			  // Ora che ho i parametri cerco il sistema informativo
			  $filename = wi400File::getCommonFile("serialize", "SYSINF_NAME_".$sysinf.".dat");
			  $library=fileSerialized($filename);
			  if (substr($sysinf,0 ,1)=='Y') {
	          		$do = executeCommand("CALL QGPL/ZDT_ASPQA2");
	          }
	          // @todo la lista libreria se $library null viene già caricate ...
			  if ($library == Null) {
					connectDB();	
				    $library = retrive_sysinf_by_name($sysinf);				
				    $db->add_to_librarylist($library, True);
	    	  }
	    	  if (!empty($library)) {
	    	  	if (isset($settings['i5_toolkit'])) {  
	    	  		$mycon->add_to_librarylist($library);
	    	  	} else {
	    	  		$db->add_to_librarylist($library, True);
	    	  	}
	    	  }
		  }
}
/**
 * Mi connetto al Database, mi basta una volta con qualsiasi lista librerie
 */	
function connectDB($library="") {
	global $db, $settings;
	
	if ($settings['database']=='DB2I5')  {
		$db->setLink($this->conn);
	} else {
		$db->connect(False);
		$db->add_to_librarylist($library);
	}

}
	
