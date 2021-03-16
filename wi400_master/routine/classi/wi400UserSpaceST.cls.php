<?php
/**
 * GESTIONE USER SPACE CON STORED PROCEDURE - MOST FAST
 * @author luca
 *
 */
class wi400UserSpaceST {
	
    private $handle;
    private $descrittore;
    private $userspcae;
    private $position; 
    private $libl;
    private $len;
    private $io;
    private $descString;
        
	public function __construct($userspace, $libl, $desc="", $io='r') {
		global $settings, $stmt_userspace, $db;
	    $this->descrittore = $desc;
	    $this->userspace = $userspace;
	    $this->libl = $libl; 
	    $this->io = strtolower($io);
	    if ($desc!="") {
	    	$this->len = DSlen($desc);
	    	$this->descString = $this->_dumpDesctoString();
	    }
	    $this->position = 0;
	    // Creo lo statement Globale
	    if (!isset($stmt_userspace)) {
		    $sql =  "call PHPLIB/ZDT_USRSPC(?,?,?,?,?,?,?)";
		    $stmt_userspace = $db->prepareStatement($sql, 0, False);
		    $this->stmt =  $stmt_userspace;
	    } else {
	    	$this->stmt =  $stmt_userspace;
	    }
	}
	public function setDescrittore($desc) {
		$this->descrittore = $desc;
		if ($desc!="") {
			$this->len = DSlen($desc);
			$this->descString = $this->_dumpDesctoString();
		}
	}
	public function get($start = 1, $len=0) {
		global $db, $stmt_userspace, $routine_path;
		
		require_once $routine_path."/generali/conversion.php";
		// Se inizio da uno o comunque proseguo la lettura da dove ero arrivato sono già posizionato
		$spc_lib=$this->libl;
		$spc_name=$this->userspace;
		$spc_start=$start;
		if ($len!=0) {
			$this->len=$len;
		}
		$spc_length=$this->len;
		$spc_oper="R ";
		$spc_data=$this->descString;
		$output="";
		$db->bind_param($this->stmt, 1, "spc_lib", DB2_PARAM_INOUT );
		$db->bind_param($this->stmt, 2, "spc_name", DB2_PARAM_INOUT );
		$db->bind_param($this->stmt, 3, "spc_start", DB2_PARAM_IN );
		$db->bind_param($this->stmt, 4, "spc_length", DB2_PARAM_IN );
		$db->bind_param($this->stmt, 5, "spc_oper", DB2_PARAM_IN );
		$db->bind_param($this->stmt, 6, "spc_data", DB2_PARAM_IN );
		$db->bind_param($this->stmt, 7, "output", DB2_PARAM_OUT );
		$result = db2_execute($this->stmt);
		if ($settings['db_encode_output']) {
			$output = utf8_encode($output);
		}
		//echo "<br>QUESTO E' L'OUTPUT:".$output;
		if ($this->descrittore!="") {
			$dati = array();
			$us = explode("<!>", $output);
			$i=0;
			//print_r($us);
			foreach($this->descrittore as $field) {
				$dati[$field['Name']]=$us[$i];
				$parts = explode('.',$field['Length']);
				if ($field["Type"]==I5_TYPE_ZONED || $field["Type"]==I5_TYPE_PACKED) {
					$dati[$field['Name']]=number_format($us[$i], $parts[1], ".","");
				}
				$i++;
			}
			return $dati;
			//return $array = string2DS($output, $this->descrittore);
		} else {
			return $output;
		}
	}
	private function _dumpDesctoString() {
		$tipo="A";
		$len ="000000";
		$dec ="00";
		$free = "00000000000";
		$stringa="";
		foreach($this->descrittore as $field) {
			$dati = explode('.',$field['Length']);
			switch ($field["Type"]) {
				 case I5_TYPE_CHAR:
				 	 $tipo="A";
				 	 $len = str_pad($dati[0], 6, "0", STR_PAD_LEFT);
				 	 $dec = "00";
					 break;
				 case I5_TYPE_ZONED:
				 	$tipo="Z";
				 	$len = str_pad($dati[0], 6, "0", STR_PAD_LEFT);
				 	$dec = str_pad($dati[1], 2, "0", STR_PAD_LEFT);
				 	break;
				 break;
				 case I5_TYPE_PACKED:
				 	$tipo="P";
				 	$len = str_pad(floor($dati[0]/2)+1, 6, "0", STR_PAD_LEFT);
				 	$dec = str_pad($dati[1], 2, "0", STR_PAD_LEFT);
				 	break;
				 default:
				 	$tipo="O";
				 	$len = str_pad($dati[0], 6, "0", STR_PAD_LEFT);
				 	$dec = str_pad($dati[1], 2, "0", STR_PAD_LEFT);
				 	break;
				 	break;
			 }
			 $stringa .=$tipo.$len.$dec.$free;
		}
		return $stringa;
		
	}
	public function put($string, $start = 1, $len=0) {
		global $db, $stmt_userspace, $routine_path;
		require_once $routine_path."/generali/conversion.php";
		
		// Se inizio da uno o comunque proseguo la lettura da dove ero arrivato sono già posizionato
		$spc_lib=$this->libl;
		$spc_name=$this->userspace;
		$spc_start=$start;
		if ($len!=0) {
			$this->len=$len;
		}
		$spc_length=$this->len;
		$spc_oper="W";
		if ($this->descrittore!="") {
			$spc_data=ds2string($string, $this->descrittore);
		} else {
			$spc_data = $string;
		}
		$output="";
		$db->bind_param($this->stmt, 1, "spc_lib", DB2_PARAM_INOUT );
		$db->bind_param($this->stmt, 2, "spc_name", DB2_PARAM_INOUT );
		$db->bind_param($this->stmt, 3, "spc_start", DB2_PARAM_IN );
		$db->bind_param($this->stmt, 4, "spc_length", DB2_PARAM_IN );
		$db->bind_param($this->stmt, 5, "spc_oper", DB2_PARAM_IN );
		$db->bind_param($this->stmt, 6, "spc_data", DB2_PARAM_IN );
		$db->bind_param($this->stmt, 7, "output", DB2_PARAM_INOUT );
		
		$result = db2_execute($this->stmt);
	}
	public function __destruct() {
			//
	}
	public static function create_userspace($property) {
		global $db, $stmt_userspace;
		
		if (!isset($stmt_userspace)) {
			$sql =  "call PHPLIB/ZDT_USRSPC(?,?,?,?,?,?,?)";
			$stmt_userspace = $db->prepareStatement($sql, 0, False);
		}
		$spc_lib=$property[I5_LIBNAME];
		$spc_name=$property[I5_NAME];
		$spc_start=1;
		$spc_length=$property[I5_INITSIZE];
		$spc_oper="C";
		$spc_data=$property[I5_DESCRIPTION];
		$output="";
		$db->bind_param($stmt_userspace, 1, "spc_lib", DB2_PARAM_INOUT );
		$db->bind_param($stmt_userspace, 2, "spc_name", DB2_PARAM_INOUT );
		$db->bind_param($stmt_userspace, 3, "spc_start", DB2_PARAM_IN );
		$db->bind_param($stmt_userspace, 4, "spc_length", DB2_PARAM_IN );
		$db->bind_param($stmt_userspace, 5, "spc_oper", DB2_PARAM_IN );
		$db->bind_param($stmt_userspace, 6, "spc_data", DB2_PARAM_IN );
		$db->bind_param($stmt_userspace, 7, "output", DB2_PARAM_INOUT);
		$result = db2_execute($stmt_userspace);
		if ($output=="OK") {
			return array("LIB"=>$spc_lib, "NAM"=>$spc_name);
		} else {
			return False;
		}
	}
  
}

?>