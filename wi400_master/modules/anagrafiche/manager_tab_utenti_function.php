<?php
	function get_tabella_descrizione($tabella, $ele) {
		global $persTable;
	
		$rs = $persTable->decodifica($tabella, $ele);
	
		return trim(substr($rs['TABELLA']['TABREC'], 0, 30));
	}