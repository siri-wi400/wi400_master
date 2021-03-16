<?php
/**
 * Costruisce automaticamente il descrittore di una DS legata ad un file reperendo automaticamente la sua
 * struttura 
 *
 * @param $file      string:file di cui costruire il descrittore
 * @param $db        object:oggetto di connessione al DB
 * @param $connzend  string:connessione a ZEND. Non viene usato $this->Connezend perchè la funzione viene usata anceh esternamente
 * @param $libre     string:libreria del file. Se non passata viene ricercata
 *
 * @return array     Array contenente la descrizine dei campi del file
 */
function create_descriptor_linux($file, $connzend, $libre = Null, $desc = False) {
	global $db, $settings;

	static $stmt;

	if ($desc) {
		$name = 'COLUMN_TEXT';
	} else {
		$name = 'COLUMN_NAME';
	}
	// Verifico se ho già creato il descrittore del file e se esiste se per caso ha la data del giorno
	$putfile = False;
	// Se non mi è stata passata la libreria la cerco
	if (! isset ( $libre )) {
		$libre = rtvLibre ( $file, $connzend );
	}
	$filename = wi400File::getCommonFile ( "serialize", $libre . "_" . $file . ".dat" );
	$desc = fileSerialized ( $filename );
	if ($desc != null) {
		return $desc;
	}
	// Se arrivo qui devo ricaricre il descrittore e quindi apro il file per la scrittura
	// Accedo alla tabella su AS400 per recuperarne la struttura
	//if (! isset ( $stmt )) {
		$sql = "SELECT COLUMN_NAME, COALESCE(CHARACTER_MAXIMUM_LENGTH, 0) AS LENGTH, UPPER(DATA_TYPE) AS DATA_TYPE, COALESCE(NUMERIC_SCALE, 0) AS NUMERIC_SCALE, COALESCE(NUMERIC_PRECISION, 0) AS NUMERIC_PRECISION, COALESCE(COLUMN_COMMENT,'') AS COLUMN_TEXT FROM
	INFORMATION_SCHEMA".$settings['db_separator']."COLUMNS WHERE TABLE_NAME = '$file'
	AND TABLE_SCHEMA = '$libre' ORDER BY ORDINAL_POSITION";
	//	$stmt = $db->prepareStatement ( $sql );
	//}
	//$result = $db->execute ( $stmt, array ($file, $libre ) );
		$result = $db->query($sql);
	// Verifico se ho trovato qualcosa
	if (! $result) {
		return false;
	}
	$desc1 = array ();
	// Ciclo di costruzione e caricamento del descrittore della DS da utilizzare
	while ( $info = $db->fetch_array ( $result ) ) {
		// Campi Packed
		if ($info ['DATA_TYPE'] == 'DECIMAL') {
			$len = $info ['NUMERIC_PRECISION'];
			$dec = $info ['NUMERIC_SCALE'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_PACKED, "Length" => "$len.$dec" );
		}
		// Campi alfanumerici
		if ($info ['DATA_TYPE'] == 'CHAR') {
			$len = $info ['LENGTH'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "$len" );

		}
		// Campi alfanumerici
		if ($info ['DATA_TYPE'] == 'TEXT') {
			$len = $info ['LENGTH'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "$len" );
		
		}
		// Zoned
		if ($info ['DATA_TYPE'] == 'NUMERIC') {
			$len = $info ['NUMERIC_PRECISION'];
			$dec = $info ['NUMERIC_SCALE'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_ZONED, "Length" => "$len.$dec" );
		}
		// Integer
		if ($info ['DATA_TYPE'] == 'INTEGER') {
			$len = $info ['NUMERIC_PRECISION'];
			$dec = $info ['NUMERIC_SCALE'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_INT, "Length" => "$len.$dec" );
		}
		// Time
		if ($info ['DATA_TYPE'] == 'TIME') {
			$len = $info ['NUMERIC_PRECISION'];
			$dec = $info ['NUMERIC_SCALE'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "8" );
		}
		// Date
		if ($info ['DATA_TYPE'] == 'DATE') {
			$len = $info ['NUMERIC_PRECISION'];
			$dec = $info ['NUMERIC_SCALE'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "10" );
		}
		// TimeStamp
		if ($info ['DATA_TYPE'] == 'TIMESTAMP') {
			$len = $info ['NUMERIC_PRECISION'];
			$dec = $info ['NUMERIC_SCALE'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "26" );
		}
		// TimeStamp
		if ($info ['DATA_TYPE'] == 'FLOAT') {
			$len = $info ['NUMERIC_PRECISION'];
			$dec = $info ['NUMERIC_SCALE'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_FLOAT, "Length" => "26" );
		}

	}
	//db2_free_result($result);
	put_serialized_file($filename, $desc1);
	// Ritorno il descrittore recuperato dalla routine
	return $desc1;
}
function rtvLibre_linux($file, $conn) {
	global $settings;

	return $settings['db_name'];
}
function getSequence_linux($name) {
	global $db;
	$numero =0;
	// Controllo se esiste il numeratore altrimenti lo creo.
	$query = "SELECT LXXNUM FROM ZCNUMERI WHERE LXXNUM='$name'";
	$result = $db->query($query);
	$row = $db->fetch_array($result);
	if (!$row) {
		$query = "INSERT INTO ZCNUMERI (LXXNUM, LXXSEQ) VALUES('$name', 0)";
		$db->query($query);
	}
	// Aggiorno il numeratore
	$query = "UPDATE ZCNUMERI SET LXXSEQ = (@cur_value := LXXSEQ) + 1 WHERE LXXNUM = '$name'";
	$db->query($query);
	// REperisco il numeratore
	$query = "SELECT @cur_value";
	$result = $db->query($query);
	$row = $db->fetch_array($result);
	$numero = $row['@cur_value'];
	return $numero;
	
}
function getSysSequence_linux($name) {
	global $db;
	$numero =0;
	// @todo Manca l'autorestart del contatore se cambia l'anno
	// Controllo se esiste il numeratore altrimenti lo creo.
	$query = "SELECT LXXNUM FROM ZSYSNUME WHERE LXXNUM='$name'";
	$result = $db->query($query);
	$row = $db->fetch_array($result);
	if (!$row) {
		$query = "INSERT INTO ZSYSNUME (LXXNUM, LXXSEQ) VALUES('$name', 0)";
		$db->query($query);
	}
	// Aggiorno il numeratore
	$query = "UPDATE ZCNUMERI SET LXXSEQ = (@cur_value := LXXSEQ) + 1 WHERE LXXNUM = '$name'";
	$db->query($query);
	// REperisco il numeratore
	$query = "SELECT @cur_value";
	$result = $db->query($query);
	$row = $db->fetch_array($result);
	$numero = $row['@cur_value'];
	return $numero;
}
function clearPHPTEMP_linux($session_id) {
	global $db, $settings;

	$sql = "select TABLE_SCHEMA, TABLE_NAME from INFORMATION_SCHEMA".$settings['db_separator']."TABLES where TABLE_SCHEMA ='".$settings['db_temp']."' and TABLE_NAME like'%".strtoupper($session_id)."%'";
	$result = $db->query($sql, False, 0);
	if ($result) {
		while ($row = $db->fetch_array($result)) {
			$sql1 = "DROP TABLE ".trim($row['TABLE_SCHEMA']).$settings['db_separator'].trim($row['TABLE_NAME']);
			$db->query($sql1);
		}
	}
}
function getJobInfo_linux($reset=False) {
	return array("NBR"=>0,"USR"=>0,"JOB"=>0);
}