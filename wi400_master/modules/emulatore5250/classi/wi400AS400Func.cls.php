<?php
/**
 * Funzioni generiche
 * @author luca
 *
 */
class wi400AS400Func {
	static function hex2str($hex) {
		return pack('H*', str_replace(array("\r", "\n", ' '), '', $hex));
	}
	static function str2hex($string){
		$hex = '';
		for ($i=0; $i<strlen($string); $i++){
			$ord = ord($string[$i]);
			$hexCode = dechex($ord);
			$hex .= substr('0'.$hexCode, -2);
		}
		return strToUpper($hex);
	}
	static function num2hex($string) {
		return strtoupper(sprintf('%02s', dechex($string)));
	}
	static function loadDisplay($display) {
		$displayObj=Null;
		$filename = wi400File::getUserFile("sessioni5250", $display.".dat");
		if (file_exists($filename)) {
			$contents = file_get_contents($filename);
			$displayObj = unserialize($contents);
		}
		return $displayObj;
	}
	static function getStringFromArray($array, $start, $len) {
		$stringa="";
		$array_part = array_slice($array, $start, $len);
		$stringa = implode("", $array_part);
		return $stringa;
	}
	static function _e2a($dati) {
		return utf8_encode(e2a($dati));
	}
	static function getWindowField($name, $row, $column, $posx, $posy) {
		$rh=strtoupper(sprintf('%02s',dechex($row)));
		$ch=strtoupper(sprintf('%02s',dechex($column)));
		$window="0009D951800000".$rh.$ch."11080A20C6899585A2A3998120";
		//echo $window;
		$dati = str_split($window, 2);
		$structuredField = new wi400AS400StructeredField($dati,0);
		//$structuredField->windowRow=$row;
		//$structuredField->windowColumn=$column;
		//showArray($structuredField);
		$nameField=$name;
		$field = new wi400AS400Field($nameField);
		$field->setStructuredData($structuredField);
		$field->setStructured(True);
		$field->setIO(True);
		$field->setXposition($posx);
		$field->setYposition($posy);
		return $field;
	}
	static function getSessionId($predefined=False) {
		$session_id = session_id()."_".uniqid();
		
		return substr($session_id,0,40);
	}
	static function getCampoXY($info, $x, $y) {
		global $db;
		static $stmt;
		static $stmt2;
		$file = substr($info, 0 , 10);
		$form = substr($info, 10, 10);
		$lib  = substr($info, 40, 10);
		$pgm  = substr($info, 50, 10);
		// Statement Principale
		if (!$stmt) {
			$sql  ="SELECT OT5FLD, OT5EDT, B.OT5KEY FROM ZOT5FILL A, ZOT5FLDL B WHERE
			A.OT5KEY=B.OT5KEY AND A.OT5FIL=? AND A.OT5LIB=? AND B.OT5ROW=? AND B.OT5COL=?";
			$stmt = $db->singlePrepare($sql);
		}
		// Statement Funzioni Particolari WI400
		if (!$stmt1) {
			$sql  ="SELECT * FROM ZOT5FLDF WHERE OT5KEY=? AND OT5FMT=? AND OT5FLD=?";
			$stmt1 = $db->singlePrepare($sql);
		}
		$result = $db->execute($stmt, array($file, $lib, $x, $y));
		$row = $db->fetch_array($stmt);
		// Cerco le eventuali funzioni aggiuntive
		if (isset($row)) {
			$result = $db->execute($stmt1, array($row['OT5KEY'], $form, $row['OT5FLD']));
			$row1 = $db->fetch_array($stmt1);
			$row['OT5DEC']=$row1['OT5DEC'];
			$row['OT5LOK']=$row1['OT5LOK'];
			$row['OT5ABI']=$row1['OT5ABI'];
			$row['OT5OTH']=$row1['OT5OTH'];
			$row['OT5STY']=$row1['OT5STY'];			
		}
		
		//echo $sql;
		//print_r($row);die();
		return $row;
	}
	static function getBoxInfo($fields, $hex=False) {
		$boxInfo = array("TL"=>0,"TR"=>0,"LL"=>0,"LR"=>0,"ROW"=>0,"COL"=>0);
		$maxx=0;
		$minx=999;
		$maxy=0;
		$miny=999;
		$numRow=0;
		$numCol=0;
		foreach ($fields as $key => $value) {
			if ($value->getStructured()==False) {
				$x = $value->getXposition();
				$y = $value->getYposition();
				if ($x<$minx) $minx=$x;
				if ($x>$maxx) $maxx=$x;
				if ($y<$miny) $miny=$y;
				if ($y>$maxy) $maxy=$y;
			}
		}
		$row=($maxx-$minx)+1;
		$col=($maxy-$miny)+1;
		if ($hex==True) {
			// @todo Cambiare con funzione generica
			$maxx=sprintf('%02s',dechex($maxx));
			$maxy=strtoupper(sprintf('%02s',dechex($maxy)));
			$minx=sprintf('%02s',dechex($minx));
			$miny=sprintf('%02s',dechex($miny));
			$row=strtoupper(sprintf('%02s',dechex($row)));
			$col=sprintf('%02s',dechex($col));
			
		}
		return array("TL"=>$minx,"TR"=>$maxx,"LL"=>$miny,"LR"=>$maxy,"ROW"=>$row,"COL"=>$col);
	}
	static function getT5DecodeParameters($id) {
		$decodeParameters = array(
				'TYPE' => 'articolo',
				'AJAX' => true,
				'COMPLETE' => true,
				'COMPLETE_MIN' => 2,
				'COLUMN' => 'MDADSA',
				'COMPLETE_MAX_RESULT' => 15,
		);
		return $decodeParameters;
	}
}