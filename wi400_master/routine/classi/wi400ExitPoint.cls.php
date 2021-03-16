<?php
/**
 * @name wi400ExitPoint
 * @desc Classe per la gestione di EXIT point su Architettura e programmi applicativi
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.01 01/04/2018
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */
class wi400ExitPoint {
	private $id;
	private $cond;
	private $cache=Null;
	private $exitFile = "ZEXIPOIN";
	private $exitAzio = "ZEXIAZIO";

	public function __construct($load=True) {
		global $settings;
		// Carico tutti i dati in cache dal file serializzato
		if ($load==True) {
			$this->loadCache();
		}
	}
	public function registerExitPoint($id, $event, $type="*WI400", $description="", $param="") {
		global $db, $settings;
		if (isset($settings['exit_point']) && $settings['exit_point']==True) {
			// Controllo se l'EXIT Point esiste già
			$keyarray = $id."-".$event;
			$dati="";
			// Prima da CACHE
			$this->loadCache();
			if (!isset($this->cache[$keyarray])) {
				$sql = "SELECT * FROM $this->exitFile WHERE EXID=? AND EXEVENT=?";
				$stmt = $db->singlePrepare($sql);
				$result = $db->execute($stmt, array($id, $event));
				$row = $db->fetch_array($stmt);
				// Se non trovato procedo alla registrazione
				if (!$row) {
					$sec = getDs($this->exitFile);
					$timestamp = date('d/m/Y H:i:s');
					$stmtTes = $db->prepare("INSERT", $this->exitFile, null, array_keys($sec));
					$sec['EXID']=$id;
					$sec['EXEVENT']=$event;
					$sec['EXTYPE']=$type;
					$sec['EXDESC']=$description;
					$sec['EXPARAM']=$param;
					$sec['EXSTA']="1";
					$result = $db->execute($stmtTes, $sec);
					// Distruggo il file serializzato
					$filename = wi400File::getCommonFile("serialize", "AZIONE_LOGIN.dat");
					unlink($filename);
					// Ricarico CACHE
					$this->loadCache(True);
				}
			}
		}
	}
	/**
	 * 
	 * @param unknown $id
	 * @param unknown $event
	 * @param unknown $azione
	 * @param unknown $form
	 * @param unknown $gateway
	 * @param number $prg
	 */
	public function registerExitPointAction($id, $event, $dati, $temp=False) {
		global $db, $settings;
		// Controllo se l'EXIT Point esiste già
		$keyarray = $id."-".$event;
		$dati="";
		// Prima da CACHE
		$this->loadCache();
		if (!isset($this->cache[$keyarray])) {
			// Se non esiste come faccio
		} else {
			$sql = "SELECT * FROM $this->exitFile WHERE EXID=? AND EXEVENT=?";
			$stmt = $db->singlePrepare($sql);
			$result = $db->execute($stmt, array($id, $event));
			$row = $db->fetch_array($stmt);
			// Se non trovato procedo alla registrazione
			if (!$row) {
				$sec = getDs($this->exitFile);
				$timestamp = date('d/m/Y H:i:s');
				$stmtTes = $db->prepare("INSERT", $this->exitFile, null, array_keys($sec));
				$sec['EXID']=$id;
				$sec['EXEVENT']=$event;
				$sec['EXTYPE']=$type;
				$sec['EXDESC']=$description;
				$sec['EXPARAM']=$param;
				$sec['EXSTA']="1";
				$result = $db->execute($stmtTes, $sec);
				// Distruggo il file serializzato
				$filename = wi400File::getCommonFile("serialize", "AZIONE_LOGIN.dat");
				unlink($filename);
				// Ricarico CACHE
				$this->loadCache(True);
			}
		}
	}
	public function executeExitPoint($id, $event, $param=array(), $azione="", $form="", $gateway="", $register=False) {
		global $actionContext, $routine_path, $wi400GO, $settings;
		if (isset($settings['exit_point']) && $settings['exit_point']==True) {
			
			if ($azione=="") $azione=$actionContext->getAction();
			if ($form=="") $form=$actionContext->getForm();
			if ($gateway=="") $gateway=$actionContext->getGateway();
			// Verifico se c'è qualcosa da richiamare
			$keyarray = $id."-".$event;
			$dati="";
			if (isset($this->cache[$keyarray])) {
				foreach ($this->cache[$keyarray] as $key => $value) {
					if ($value['EASTA']=="1") {
						$row = $value;
						// Verifico se c'è qulache situazione discriminante 
						// Filtro per AZIONE
						if ($row['EASAZI']!=="") {
							if ($row['EXSAZI']!=$azione) {
								continue;
							}
						}
						// Filtro per FORM
						if ($row['EASFRM']!=="") {
							if ($row['EXSFRM']!=$form) {
								continue;
							}
						}
						// Filtro per GATEWAY
						if ($row['EASGTW']!=="") {
							if ($row['EXSGTW']!=$gateway) {
								continue;
							}
						}
						// Test ulteriori situazioni discriminanti
						if ($row['EACOND']!="") {
							$cond = $row['EACOND'];
							$cond = str_replace("#","\$param", $cond);
							$cond = str_replace("§","\$param['row_trigger']", $cond);
							$cond1="if (($cond)){"." return true;"."}";
							$forMe = eval($cond1);
							if ($forMe!=True) {
								continue;
							}
						}
						// @todo Da implementare
						// Se arrivo qui devo lanciare l'azione target con i parametri
						require_once $routine_path."/generali/encryption.php";
						// Aggiorno il record con la data di ultimo lancio
						$this->updateAzioneCall($id, $event, $row['EAPRG']);
						// Aggiungo Parametri di SISTEMA
						$internal_id = uniqid("TRG_", true);
						$batch = False;
						if ($row['EASBCH']=="1") {
							$batch = True;
						}
						$wait = True;
						if ($row['EASWAI']!="1") {
							$wait = False;
						}
						$async = false;
						// Verifico se processo asincrono
						if ($row['EASYNC']=="1") {
							$async = True;
						}
						// Verifico se devo scrivere il log
						$log = False;
						if ($row['EASLOG']=="1" || $async =='S') {
							$log = True;
							// @todo metodo per scrivere il log e aggiornarlo
							$this->writeLog($id, $event, $row['EAPRG'], $internal_id, $param);
						}
						$param['WI_HEADER'] = array(
								"ID"=>$id,
								"EVENT"=>$event,
								"PRG"=>$row['EAPRG'],
								"INTERNAL_ID"=>	$internal_id,
								"BATCH" => $batch,
								"WAIT" => $wait,
								"LOG" => $log
						);
						if ($async == "S") {
							// Scrittura file parametri complementare
							$this->writeAdvancedLog($id, $param);
							// Aggiornamento LOG
							$this->updateLog($internal_id, "B", "PARKED");
							// Innesco coda processi
							$this->innescaCoda($id, $event);
						} else {
							// Non posso monitorare errori di compilazione o del core PHP solo fatal
							try {
								$dati .= wi400_runAction($row['EATAZI'],$row['EATFRM'],$row['EATGTW'], "","","", $param, $batch, $wait);
							} catch (ErrorException $e) {
								echo "Errore EXIT POINT NON RICHIAMATO per";
							}
						}
						//return $dati;
					}
				}
				return $dati;
			} else {
				if ($register==True) {
					//
				}
			}
		}
		return false;
	}
	/**
	 * Risottometti evento Non triggerato
	 * @param unknown $id
	 */
	public function resubmitEvent($id) {
		global $db, $routine_path;
		
		$param = array();
		// Recupero dati ID
		$sql = "SELECT * FROM ZEXILOGA WHERE EAINTID='$id'";
		$result = $db->singleQuery($sql);
		$row = $db->fetch_array($result);
		// Recupero informazioni sull'azione da eseguire
		$sql2 = "SELECT * FROM ZEXIAZIO WHERE EAID='{$row['EAID']}' AND 
	EAEVENT='{$row['EAEVENT']}' AND EAPRG ={$row['EAPRG']}";
		$result2 = $db->singleQuery($sql2);
		$row2 = $db->fetch_array($result2);
		//
		$parts = explode("|", $row['EASTR']);
		$parts = array_map("trim", $parts);
		foreach($parts as $currentPart)
		{
			list($key, $value) = explode("=", $currentPart);
			$param[$key] = $value;
		}
 		$wait = True;
 		$batch = False;
		$param['WI_HEADER'] = array(
				"ID"=>$row['EAID'],
				"EVENT"=>$row['EAEVENT'],
				"PRG"=>$row['EAPRG'],
				"INTERNAL_ID"=>	$id,
				"BATCH" => $batch,
				"WAIT" => $wait,
				"LOG" => True
		);
		echo var_dump($param);die();
		// Se arrivo qui devo lanciare l'azione target con i parametri
		require_once $routine_path."/generali/encryption.php";
		$dati .= wi400_runAction($row2['EATAZI'],$row2['EATFRM'],$row2['EATGTW'], "","","", $param, $batch, $wait);
	}
	public function loadCache($reset=False) {
		global $db, $settings;
		if (isset($settings['exit_point']) && $settings['exit_point']==True) {
			if ($this->cache==Null || $reset==True) {
				$filename = wi400File::getCommonFile("serialize", "EXIT_POINT.dat");
				$this->cache=fileSerialized($filename);
				if ($this->cache== Null) {
					$sql = "select * from $this->exitFile left join zexiazio on eaid=exid and eaevent=exevent and easta='1' where exsta='1' ORDER BY exid, exevent, eaprg";
					$this->cache = make_serialized_file($sql, $filename, array("EXID", "EXEVENT"), True);
				}	
			}
		}
	}
	
	/**
	 * Restituisce il tracciato di una riga exit point
	 * 
	 * @param string $id
	 * @param string $evento
	 * @param numeric $prg
	 * @return array
	 */
	public function getDettaglioExitPoint($id, $evento, $prg) {
		global $db;
		static $stmt_select;
		if (!$stmt_select) {
			$sql = "SELECT * FROM ZEXIAZIO WHERE EAID=? AND EAEVENT=? AND EAPRG=?";
			$stmt_select = $db->prepareStatement($sql);
		}		
		$rs = $db->execute($stmt_select, array($id, $evento, $prg));
		$row = $db->fetch_array($stmt_select);
		
		return $row;
	}
	
	/**
	 * Restituisce il massimo progressivo in base all'id ed evento
	 * 
	 * @param unknown $id
	 * @param unknown $evento
	 * @return numeric
	 */
	public function getMaxProgressivo($id, $evento) {
		global $db;
		
		$sql = "SELECT MAX(EAPRG) AS MAX_PRG FROM ZEXIAZIO WHERE EAID=? AND EAEVENT=?";
		$stmt_max_prg = $db->singlePrepare($sql);
		$rs = $db->execute($stmt_max_prg, array($id, $evento));
		$row = $db->fetch_array($stmt_max_prg);
		
		return $row['MAX_PRG'];
	}
	
	/**
	 * Cancellazione della cache
	 */
	public function deleteCache() {
		$filename = wi400File::getCommonFile("serialize", "EXIT_POINT.dat");
		
		if(file_exists($filename)) {
			unlink($filename);
		}
	}
	
	/**
	 * Aggiorna lo stato della testata
	 * 
	 * @param string $stato 1/0
	 * @param string $id
	 * @param string $evento
	 * @return boolean
	 */
	public function updateStatoTestata($stato, $id, $evento) {
		global $db;
	
		$where = array(
				"EXID" => $id,
				"EXEVENT" => $evento
		);
		$stmt_update = $db->prepare("UPDATE", $this->exitFile, $where, array("EXSTA"));
	
		$rs = $db->execute($stmt_update, array($stato));
		if($rs) $this->deleteCache();
	
		return $rs;
	}
	
	/**
	 * Aggiorna i campi delle righe
	 * 
	 * @param array $tracciato => i campi che si vogliono aggiornare
	 * @param string $id
	 * @param string $evento
	 * @param string $prg
	 * @return boolean
	 */
	public function updateDettaglioValue($tracciato, $id, $evento, $prg) {
		global $db;
		
		$where = array(
			"EAID" => $id,
			"EAEVENT" => $evento,
			"EAPRG" => $prg
		);
		$stmt_update_val = $db->prepare("UPDATE", "ZEXIAZIO", $where, array_keys($tracciato));
		$rs = $db->execute($stmt_update_val, $tracciato);
		if($rs) $this->deleteCache();
		
		return $rs;
	}
	
	/**
	 * Inserimento di una nuova riga
	 * 
	 * @param unknown $tracciato => i campi che si voglio inserire
	 * @param unknown $id
	 * @param unknown $evento
	 * @param string $prg
	 * @return boolean
	 */
	public function insertDettaglioValue($tracciato, $id, $evento, $prg='auto') {
		global $db;
		
		if($prg == "auto" || !is_numeric($prg)) {
			$prg = $this->getMaxProgressivo($id, $evento);
		}
	
		$tracciato = array_merge($tracciato, array(
			"EAID" => $id,
			"EAEVENT" => $evento,
			"EAPRG" => $prg+1
		));
		$stmt_update_val = $db->prepare("INSERT", "ZEXIAZIO", null, array_keys($tracciato));
		$rs = $db->execute($stmt_update_val, $tracciato);
		if($rs) $this->deleteCache();
	
		return $rs;
	}
	
	/**
	 * Eliminazione di una riga
	 * 
	 * @param unknown $id
	 * @param unknown $evento
	 * @param unknown $prg
	 * @return boolean
	 */
	public function deleteDettaglioValue($id, $evento, $prg) {
		global $db;
		
		$keys = array(
			"EAID" => $id,
			"EAEVENT" => $evento,
			"EAPRG" => $prg
		);
		
		$stmt_delete = $db->prepare("DELETE", "ZEXIAZIO", array_keys($keys), null);
		$rs = $db->execute($stmt_delete, $keys);
		if($rs) $this->deleteCache();
		
		return $rs;
	}
	function updateAzioneCall($id, $evento, $prg, $wich="C") {
		global $db;
		static $stmt_update_c, $stmt_update_r;
		if (!$stmt_update_c) {
			$sql = "UPDATE ZEXIAZIO SET EASLCA=? WHERE EAID=? AND EAEVENT=? AND EAPRG=?";
			$stmt_update_c = $db->prepareStatement($sql);
		}
		if (!$stmt_update_r) {
			$sql = "UPDATE ZEXIAZIO SET EASLRU=? WHERE EAID=? AND EAEVENT=? AND EAPRG=?";
			$stmt_update_r = $db->prepareStatement($sql);
		}
		$timeStamp = getDb2Timestamp();
		if ($wich=="C") {
			$rs = $db->execute($stmt_update_c, array($timeStamp, $id, $evento, $prg));
		} else {
			$rs = $db->execute($stmt_update_r, array($timeStamp, $id, $evento, $prg));
		}
	}
	/**
	 * Inserimento di una riga di log
	 *
	 */
	public function writeLog($id, $event, $prg, $internal_id, $param) {
		global $db;
		
		$tracciato = getDs("ZEXILOGA");
		
		$str ="";
		$sep="";
		if (is_array($param)) {
			foreach ($param as $key => $value) {
				if (is_array($value)) {
					$str .=$sep."$key=Array()";
					$sep="|";
				} else {
					$str .=$sep."$key=".$value;
					$sep="|";
				}
			}
		}
		
		$tracciato['EAID']=$id;
		$tracciato['EAEVENT']=$event;
		$tracciato['EAPRG']=$prg;
		$tracciato['EAESI']="*";
		$tracciato['EAINTID']=$internal_id;
		$tracciato['EASTR']=$str;
		$tracciato['EASTRR']="";
		$tracciato['EATIMC']=getDb2Timestamp();
		$tracciato['EATIMA']=getDb2Timestamp("*INZ");
		
		$stmt_update_val = $db->prepare("INSERT", "ZEXILOGA", null, array_keys($tracciato));
		$rs = $db->execute($stmt_update_val, $tracciato);
	
		return $rs;
	}
	/**
	 * Inserimento di una riga di log
	 *
	 */
	public function writeAdvancedLog($id, $param) {
		global $db;
		
		$tracciato = getDs("ZEXILOGP");
		$strparm = base64_encode(serialize($param));

		$tracciato['EAID']=$id;
		$tracciato['EAPARM']=$strparm;
		
		$stmt_update_val = $db->prepare("INSERT", "ZEXILOGP", null, array_keys($tracciato));
		$rs = $db->execute($stmt_update_val, $tracciato);
		
		return $rs;
	}
	public function innescaCoda($id) {
		// Scrive il process ID in qualche coda per un processo che lo legge
	}
	
	function updateLog($int_id, $esito, $str="") {
		global $db;
		static $stmt_update_c, $stmt_update_r;
		if (!$stmt_update_c) {
			$sql = "UPDATE ZEXILOGA SET EAESI=?, EASTRR=?, EATIMA=? WHERE EAINTID=?";
			$stmt_update_c = $db->prepareStatement($sql);
		}
		$timeStamp = getDb2Timestamp();
		$rs = $db->execute($stmt_update_c, array($esito, $str, $timeStamp, $int_id));
	}
 }