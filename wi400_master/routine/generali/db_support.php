<?php
/*
 create procedure PHPLIB/ZVRYOPNF          
(in count decimal(5, 0), in tipo char(10),
out oOut   char(2000))                     
language rpgle deterministic no sql       
external name PHPLIB/ZVRYOPNF             
parameter style general 

drop procedure phplib/ZVRYOPNF    

SYSROUTINE QSYS2      SYSRO00001 SYSRO00001 PHY       15 I   NO            385
QASQRESL   QSYS2      QASQRESL   SYSRO00001 LGL       20 I   NO            385
ZMSGNOT    PHPLIB     ZMSGNOT               PHY        0 I   NO               
ZMSGUSR    PHPLIB     ZMSGUSR    FORMAT0001 PHY        1 I   NO              8

                            */
/**
 * @desc getCurrentOpenFile; Recuper i file paerti dalla connessione 
 * @param string $filter : filtro sui file DSPF, PF, LF
 * @return array $returnFile: Array con i file aperti e le informazioni
 */
function getCurrentOpenFile_AS400($filter="") {

	global $db, $settings, $Counter, $Output, $Type;
	if (!isset($_SESSION['PROCEDURE_ZVRYUSROPN'])) {
		// Check if exists
//		$sql = "SELECT * FROM sysroutine WHERE ROUTINE_SCHEMA ='PHPLIB' and SPECIFIC_NAME = 'ZVRYOPNF'";
		if($settings['xmlservice_driver']!="PDO") {
			$routine_name = 'ZVRYOPNF';
		} else {
			$routine_name = 'ZVRYOPNFR';
		}
		$sql = "SELECT * FROM sysroutine WHERE ROUTINE_SCHEMA = '".$settings['db_name']."' and SPECIFIC_NAME = '$routine_name'";
		$result = $db->singleQuery($sql);
		$row = $db->fetch_array($result);
		// Se non esiste ..
		if (!$row) {
			if($settings['xmlservice_driver']!="PDO") {
				
				$sql = "create procedure ".$settings['db_name'].$settings['db_separator']."ZVRYOPNF          
						(in count decimal(5, 0), in tipo char(10),
						out oOut   char(2000))                     
						language rpgle deterministic no sql       
						external name ".$settings['db_name'].$settings['db_separator']."ZVRYOPNF             
						parameter style general ";
				$result = $db->query($sql);
			} else {
				$sql = "create procedure ".$settings['db_name'].$settings['db_separator']."ZVRYOPNFR
						(in count decimal(5, 0), in tipo char(10))
						MODIFIES SQL DATA    
						Result Sets 1        
						language rpgle NOT deterministic
						external name ".$settings['db_name'].$settings['db_separator']."ZVRYOPNFR
						parameter style general ";
				$oldSetting = $settings['auto_resolve_table'];
				$settings['auto_resolve_table']=False;
				$result = $db->query($sql);
				$settings['auto_resolve_table']=$oldSetting;
				$result = $db->query($sql);
			}
			
		} 
		$_SESSION['PROCEDURE_ZVRYUSROPN']=True;
	}
	$excludeLib = array("QSYS", "QSYS2");
	$returnFile=array();
	$Counter = 0;
	$Output="";
	$Type = $filter;
	if($settings['xmlservice_driver']!="PDO") {
//		$query =  "call PHPLIB".$settings['db_separator']."ZVRYOPNF(?,?,?)";
		$query =  "call ".$settings['db_name'].$settings['db_separator']."ZVRYOPNF(?,?,?)";
		$stmt2 = $db->prepareStatement($query, 0, False);
		$db->bind_param($stmt2, 1, "Counter", DB2_PARAM_IN );
		$db->bind_param($stmt2, 2, "Type", DB2_PARAM_IN );
		$db->bind_param($stmt2, 3, "Output", DB2_PARAM_OUT);
		$result = db2_execute($stmt2);
	} else {
//		$stmt2 =  "call PHPLIB".$settings['db_separator']."ZVRYOPNFR(?,?)";
		$stmt2 =  "call ".$settings['db_name'].$settings['db_separator']."ZVRYOPNFR(?,?)";
		$oldSetting = $settings['auto_resolve_table'];
		$settings['auto_resolve_table']=False;
		$stmt2 = $db->prepareStatement($stmt2, 0, False);
		$settings['auto_resolve_table']=$oldSetting;
		$result = $stmt2->execute(array($Counter, $Type));
		$row = $stmt2->fetchAll(PDO::FETCH_NUM);
		//print_r($row);
		foreach ($row as $key => $value) {
			$Output.=$value[0];
		}
	}
	$Output = utf8_encode($Output);
	$array = explode("!",$Output);
	foreach ($array as $key => $value) {
		if ($value!="") {
			$array2 = explode(";",$value);
			if (!in_array($array2[1], $excludeLib)) {
				$returnFile[$array2[0]]=$array2;
			}
		}
	}
	return $returnFile;
}
/**
 * @desc getAllFieldUser: Recupera tutti i possibile Campi utilizzati dai file Aperti
 * @param array $usedFile: array di file usati ottenuti da getCurrentOpenFile
 */
function getAllFieldUsed($usedFile) {
	global $db;
	$arrayCampi = array();
	foreach ($usedFile as $key => $value) {
		//$arrayCampi2 = $db->columns($key);
		// Ho la libreria fissa ..
		$arrayCampi2 = $db->columns($key, "",False,"", $value[1]);
		$arrayCampi = array_merge($arrayCampi, $arrayCampi2);
	}
	return $arrayCampi;
}
/**
 * @desc getCustomFieldDesc: Cerca di recuperare una descrizione per il campo passato, se non trovata usa il nome del campo
 * @param string $field: Nome del campo
 * @param array $arrayCampi: array di campi ottenuto con getAllFieldUsed
 * @param boolean $addBr: Se True sostituisce gli spazi con <BR>
 * @param boolena $customTrad: Se True verifica se ci sono Tag particolari sul vocabolario
 * @return string $desc: Descrizione da usare per il campo
 */
function getCustomFieldDesc($field, $arrayCampi, $addBr=False, $customTrad=False) {
	global $wi400Lang;

	$desc = $field;
	if (isset($arrayCampi[$field])) $desc = $arrayCampi[$field]['REMARKS'];
	if ($addBr==True) {
		$desc = str_replace(" ", "<br>", $desc);
	}
	if ($customTrad==True) {
		$desc2 = _t($field, array(), True);
		if ($desc2 !=$field) $desc = $desc2;
	}
	return $desc;
}