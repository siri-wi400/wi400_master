<?php

class MONITOR_PID_LIST extends wi400CustomSubfile {
	
	private $file_path;
	
	private $des_stato_array = array();
	
	public function __construct($parameters){
		global $db, $connzend, $moduli_path, $data_path, $settings, $routine_path;		
		$this->file_path = $parameters['FILE_PATH'];
		require_once $routine_path."/generali/process.php";
		require_once $routine_path."/os400/wi400Os400Job.cls.php";
	}
	
	public function getArrayCampi() {	
		global $db;
		
		$array = array();
		$array['RRN']=$db->singleColumns("3", "7", "0", "RRN");
		$array['PID']=$db->singleColumns("1", "10", "", "Pid Proceso");
		$array['PIDSTS']=$db->singleColumns("1", "1", "", "Stato Pid Proceso");
		$array['PIDACT']=$db->singleColumns("1", "1", "", "Active Pid");
		$array['JOBINFO']=$db->singleColumns("1", "30", "", "Job INFO");
		$array['JOBINFOSTS']=$db->singleColumns("1", "1", "", "Stato Job");
		$array['JOBINFOACT']=$db->singleColumns("1", "1", "", "Active Job");
		$array['DBINFO']=$db->singleColumns("1", "30", "", "DB INFO");
		$array['DBINFOSTS']=$db->singleColumns("1", "1", "", "Stato DB INFO");		
		$array['DBINFOACT']=$db->singleColumns("1", "1", "", "Active DB");
		$array['USER']=$db->singleColumns("1", "30", "", "Utente");
	    $array['SESSIONE']=$db->singleColumns("1", "50", "", "Sessione");
	    $array['DATA_CREAZIONE']=$db->singleColumns("1", "20", "", "TimeStamp");
	    $array['RUNNING']=$db->singleColumns("3", "9", 0, "Temp");
	    $array['ITSME']=$db->singleColumns("1", "1", "", "ME");
	    $array['DBFILE']=$db->singleColumns("1", "100", "", "DB FILE");
	    $array['JOBFILE']=$db->singleColumns("1", "100", "", "JOB FILE");	    
	    $array['PIDFILE']=$db->singleColumns("1", "100", "", "PID FILE");
	
		return $array;
	}
	
	public function init($parameters){
		global $db, $connzend;
		
		$this->setCols($this->getArrayCampi());
	}
	
	public function start($subfile) {
		global $db, $connzend;
		
		// Prepare della query di inserimento
		$fields = array_keys($this->getArrayCampi());
		$array_file = array();
		// Carico i processi PID PHP per verificare in caso se un PID letto è ancora presente nel sistema
		$process = getProcessList("", "php-cgi");
		$now = time();
		$stmtinsert = $db->prepare("INSERT", $subfile->getTable(), null, $fields);
		
		if(file_exists($this->file_path)) {
			$dir_handle = opendir($this->file_path);
			
			// Recupero dei file della directory
			while(($file_name = readdir($dir_handle))!==false) {
				if($file_name!="." && $file_name!="..") {
					$addon = array();
					$file = $this->file_path."/".$file_name;
					$file_parts = pathinfo($file);
					$extension = $file_parts['extension'];
					$stato = "";
					$dati = unserialize(file_get_contents($this->file_path."/".$file_name));
					$key = md5(serialize($dati));
					$dati_old = array();
					if (isset($array_file[$key])) {
						$dati_old = $array_file[$key];
					}
					// se il file è più vecchio di 24 ore lo cancello
					$last_modified = filemtime ( $file );
					$diff = $now - $last_modified;
					if ($last_modified && $diff > 216000) {
						unlink($this->file_path."/".$file_name);
						continue;						
					}			
  					// verifico suffisso file	
					$prefix = substr($file_name, 0 , 4);
					// FILE PID
					if ($prefix=="PID_") {
						// Se non c'è traccia del processo cancello il file
						$addon['PID_ACTIVE']=True;
						$addon['PIDFILE']=$file;
						$array_file[$key]=array_merge($dati, $addon, $dati_old);
						if (!isset($process[$dati['PID']])) {
							unlink($this->file_path."/".$file_name);
							continue;
						}
						//$addon['PID_ACTIVE']=True;
						//$array_file[$key]=array_merge($dati, $addon, $dati_old);
					}
					// FILE DB
					if ($prefix=="DBI_") {
						$addon['DBI_ACTIVE']=True;
						$addon['DBFILE']=$file;
						$array_file[$key]=array_merge($dati, $addon, $dati_old);
					}
					// FILE INFO 
					if ($prefix=="JOB_") {
						$addon['JOB_ACTIVE']=True;
						$addon['JOBFILE']=$file;
						$array_file[$key]=array_merge($dati, $addon, $dati_old);
					}
					
				}
			}
		
			closedir($dir_handle);
		}
		// Reperisco i miedi dati
		$rrn=0;
		$mydati = unserialize(file_get_contents($_SESSION['wi400_pid_file'][0]));
		// Giro per caricamento Subfile
		foreach ($array_file as $key => $dati) {
			//echo "<br>KEY;".$key;
			//showArray($dati);
			$pid = $dati['PID'];
			$jobinfo = $dati['JOBINFO'];
			$dbinfo = $dati['DBINFO'];
			$dbinfo = preg_replace('!\s+!', '_', $dbinfo);
			$user = $dati['USER'];
			$sessione = $dati['SESSION'];
			$data_creazione = date("h:i:s d/m/Y", $dati['TIMESTAMP']);
			$running = time() - $dati['TIMESTAMP'];
			if ($running > 1000000) $running = 100000;
			// Verifico presenza degli altri file collegati
			$jobinfosts="0";
			$dbinfosts="0";
			$pidsts = "0";
			if (isset($dati['PID_ACTIVE'])) $pidsts = "1";
			if (isset($dati['DBI_ACTIVE'])) $dbinfosts = "1";
			if (isset($dati['JOB_ACTIVE'])) $jobinfosts = "1";
			// Controllo se i lavori sono attivi sul sistema
			$jobinfoact = "0";
			$dbinfoact = "0";
			$pidact = "0";
			$jj = explode("_", $jobinfo);
			$list = new wi400Os400Job($jj[0],$jj[1], $jj[2]);
			$list->getList();
			if ($list->getEntry()) $jobinfoact = "1";
			$jj = explode("_", $dbinfo);
			$list = new wi400Os400Job($jj[0],$jj[1], $jj[2]);
			$list->getList();
			if ($list->getEntry()) $dbinfoact = "1";
			// Controllo se è il mio lavoro
			$itsme="0";
			if ($jobinfo == $mydati['JOBINFO'] && $pid ==$mydati['PID'] && $dbinfo==preg_replace('!\s+!', '_', $mydati['DBINFO'])) {
				$itsme= "1";
			} 
			if (isset($process[$pid])) {
				$pidact = "1";
			}
			// File di riferimento
			$pidfile="";
			$dbfile="";
			$jobfile="";
			if (isset($dati['JOBFILE'])) $jobfile = $dati['JOBFILE'];
			if (isset($dati['DBFILE'])) $dbfile = $dati['DBFILE'];
			if (isset($dati['PIDFILE'])) $pidfile = $dati['PIDFILE'];
			// Se non c'è nessun riferimento attivo .. salto
			
			
			//
			$rrn++;
			// Carico tutti i processi PID per verificare se è ancora attivo altrimenti lo cancello
			$writeRow = array(
					$rrn,
					$pid,
					$pidsts,
					$pidact,
					$jobinfo,
					$jobinfosts,
					$jobinfoact,
					$dbinfo,
					$dbinfosts,
					$dbinfoact,
					$user,
					$sessione,
					$data_creazione,
					$running,
					$itsme,
					$dbfile,
					$jobfile,
					$pidfile
			);
			// Inserimento della riga nel subfile
			$db->execute($stmtinsert, $writeRow);			
		}
		// Butto via tutto quello che non serve 
		$query = "SELECT * FROM ". $subfile->getTable();
		$result = $db->query($query);
		while ($row = $db->fetch_array($result)) {
				if($row['PIDACT']=="0" && $row['JOBINFOACT']=="0" && $row['DBINFOACT']=="0") {
					@unlink($row['PIDFILE']);
					@unlink($row['JOBFILE']);
					@unlink($row['DBFILE']);
					$delq = "DELETE FROM ". $subfile->getTable(). " WHERE RRN=".$row['RRN'];
					$db->query($delq); 
				}
		}		
	}
	
	public function body($campi, $parameters) {
		global $db, $connzend;
		
		return false;
	}
	
}

?>