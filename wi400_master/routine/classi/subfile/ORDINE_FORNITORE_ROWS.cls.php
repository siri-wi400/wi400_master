<?php

class ORDINE_FORNITORE_ROWS extends wi400CustomSubfile {

	public static $STATO_EMPTY = 0;
	public static $STATO_VALID = 1;
	public static $STATO_ERROR = 2;
	
	public function ORDINE_FORNITORE_ROWS($parameters){
		
		global $db;

	}
	
	public function init($parameters){
		global $db;
		$dateConsegna = $parameters["DATE_CONSEGNA"];
		
		$array['ORDER']=$db->singleColumns("1", 10);
		$array['ARTICOLO']=$db->singleColumns("1", 7);
		$array['DESLUNG'] =$db->singleColumns("1", 35);
		$qta=1;
		foreach($dateConsegna as $key => $value) {
			$array['QTA'.$qta] =$db->singleColumns("3", 8, 2);
			$qta++;
		}
		$array['STATO']=$db->singleColumns("3", 1, 0);
		$array['SETTORE']=$db->singleColumns("1", 12);
		$array['FAMIGLIA']=$db->singleColumns("1", 12);
		$array['SOTTOFAMIGLIA']=$db->singleColumns("1", 12);
		
		$this->setCols($array);
		
	}
	
	public function start($subfile){
		global $db, $connzend;

		// INSERT ROW VUOTE *******************************
		$field = array("NREL", "ORDER","ARTICOLO","DESLUNG","STATO","SETTORE","FAMIGLIA","SOTTOFAMIGLIA");
		$stmtinsert = $db->prepare("INSERT", $subfile->getTable(), null, $field);
		
		$maxRows = $subfile->getParameter("MAX_ROWS");
		$orderNum = $subfile->getParameter("ORDER_CODE");
		$proposta = $subfile->getParameter("PROPOSTA"); 
		$fornitore = $subfile->getParameter("FORNITORE");
		$start = 1;
		if ($proposta == "S") {
			$articoli = siad_retrive_articoli_fornitore($fornitore);
			foreach($articoli as $key => $value) {
					$rtlart = new wi400Routine('RTLART', $connzend);
				    $rtlart->load_description();
				    $rtlart->prepare();
				    $rtlart->set('NUMRIC',1);
				    $rtlart->set('DATINV', date("Ymd"));
				    $rtlart->set('ARTICOLO',$value);
					$rtlart->call();
					$row = $rtlart->get('ARTI');
				    $writeRow = array($start, $orderNum , $value, $row["MDADSA"], 1,$row["MDASET"], $row["MDAFAM"], $row["MDASUF"]);
					$db->execute($stmtinsert, $writeRow);
					$start++;
			}
		}
		
		for ($rowNum = $start; $rowNum < $maxRows; $rowNum++){
			$campi = array($rowNum, $orderNum, "", "",  0,"","","");
			$db->execute($stmtinsert, $campi);
		}

	}
	
	public function body($row, $parameters){
		return false;
	}
	
	
	public function end($subfile){
	}

}
?>