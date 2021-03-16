<?php
	//Creo array dei temi disponibili
	//unset($_SESSION['LISTA_TEMI_5250']);
	//unset($_SESSION['TEMA_5250']);
	if(!isset($_SESSION['LISTA_TEMI_5250']) || !$_SESSION['LISTA_TEMI_5250']) {
		$path =  $moduli_path."/emulatore5250/css";
		$files = array_diff(scandir($path), array('.', '..'));
		$dati = array();
		foreach($files as $nome) {
			$info = pathinfo($nome);
			$arr_d = explode("_", $info['filename']);
			$colorCursor = array_pop($arr_d);
			$label = implode(' ', $arr_d);
		
			$dati[$nome] = array(
				'file' => $nome,
				'label' => $label,
				'cursor' => $colorCursor
			);
		}

		$_SESSION['LISTA_TEMI_5250'] = $dati;
		
	}
	
	//showArray($_SESSION['LISTA_TEMI_5250']);

	function add_tag($tagname, $timestamp) {
		global $db;
		
		$sql = "UPDATE zopnlogs SET LPGTAG='$tagname' WHERE LOGSTM='$timestamp'";
		//$sql = "select * from zopnlogs WHERE LOGSTM='$timestamp'";
		$rs = $db->query($sql);
		
		return $rs;
	}
	
	function getTema5250() { 
		if(isset($_REQUEST['CAMBIO_TEMA'])) {
			$_SESSION['TEMA_5250'] = $_SESSION['LISTA_TEMI_5250'][$_REQUEST['TEMA']];
		}
		
		if(!isset($_SESSION['TEMA_5250']) || !$_SESSION['TEMA_5250']) {
			$_SESSION['TEMA_5250'] = $_SESSION['LISTA_TEMI_5250']['AS400_white.css'];
		}
		
		return '<link rel="stylesheet" type="text/css" href="modules/emulatore5250/css/'.$_SESSION['TEMA_5250']['file'].'">';
	}