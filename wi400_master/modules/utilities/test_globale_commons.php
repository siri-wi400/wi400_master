<?php
	
	function getInfoOrdine($id) {
		global $db;
		
		static $stmtInfo;
		
		if(!isset($stmtInfo) || !$stmtInfo) {
			$query = "SELECT * FROM ORDINI where ID_ORDINE=?";
			$stmtInfo = $db->prepare($query);
			echo "prepare<br/>";
		}else {
			echo "prepare gia fatto<br/>";
		}
		
		$rs = $db->execute($stmtInfo, array("ID_ORDINE" => $id));
		if($row = $db->fetch_array($stmtInfo)) {
			return $row;
		}else {
			return null;
		}
	}
	
	function getLabel($row, $azione) {
		$label = "<a href='javascript:openWindow(\"index.php?t={$azione}&f=INFO&ID_ORDINE={$row['ID_ORDINE']}\", \"info\", undefined, undefined, undefined, undefined, undefined, undefined, jQuery(\"#\"+APP_FORM).serialize());'>{$row['ID_ORDINE']} - {$row['FORNITORE']} - {$row['DESFOR']}</a><br/>";
		$label .= $row['FORNCLI']." - ".$row['DESFORCLI']."<br/>";
		$label .= $row['DEPOSITO']." - ".$row['DESDEP']."<br/>";
			
		return $label;
	}