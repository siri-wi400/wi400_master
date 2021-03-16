<?php
	
	//$colonne = 4;
	$where_menu = "";
	$style_sortable = "";
	if(isset($from_menu)) {
		$style_sortable = "style='width: 250px;'";
		$where_menu = " AND WIDDOC=1";
		if($azione == "WIDGET" || $azione == "") {
			echo "<center><h2>Men&ugrave; non disponibile per questa azione.</h2></center>";
			die();
		}
	}
	
	if(isset($from_announce)) {
		echo "<script>var from_announce = true;</script>";
		$style_sortable = $from_announce;
	}
	
	echo '<link rel="stylesheet" type="text/css" href="'.$appBase.'modules/announce/widget_style.css">';
	echo '<script src="'.$appBase.'modules/announce/widget_script.js"></script>';
	
	if(!isset($from_menu) && !isset($from_announce)) {
		echo "<button class='savePosition' onClick='savePositionWidget()'>Salva posizione</button><br/><br/>";
	}
	
	$sql_widget = "SELECT * FROM ZWIDGUSR WHERE WIDUSR in ('".implode("', '", $_SESSION['WI400_GROUPS'])."', '".$_SESSION['user']."') AND WIDSTA='1'".$where_menu." ORDER BY WIDRIG";
	$rs = $db->query($sql_widget);
	/*while($row = $db->fetch_array($rs)) {
		$elenco_widget[] = $row;
	}*/
	/*$elenco_widget = file_get_contents("elenco_widget.txt", true);
	$elenco_widget = explode("\r\n", $elenco_widget);*/
	/*$array_colonne = array();
	
	$num_column = $colonne-1;
	for($i=0;$i<=$num_column;$i++) {
		$array_colonne[] = array();
	}*/
	//if(isset($elenco_widget)) {
		//Creao la struttura widget a colonne
		/*foreach($elenco_widget as $dati_widget) {
			$col = !isset($from_menu) ? $dati_widget['WIDCOL'] : 0;
			$array_colonne[$col][] = $dati_widget;
		}*/
		//showArray($array_colonne);
		//$widget_interval = array();
		
		//echo "<center ".(isset($from_announce) ? $from_announce : "").">";
		$num_array = array(1, 1, 1, 4, 1);
		$cont = 0;
		echo "<div class='sortable' $style_sortable>";
		//foreach($array_colonne as $colonna) {
		//foreach($colonna as $widget) {
			$widget_array = array();
			while($widget = $db->fetch_array($rs)) {
				$nome_widget = $widget['WIDAZI'];
				/*if($nome_widget == "ACCESS_LOG") {
					echo "<div class='portlet span-4 ui-widget ui-widget-content ui-corner-all ui-sortable-placeholder' style='visibility: hidden; height: 94px;'></div>";
				}*/
				
				$widget_type = $nome_widget."-".$widget['WIDPRG'];
				if(!in_array($widget_type, $widget_array)) {
					$widget_array[] = $widget_type;
				}
				else {
					continue;
				}
				
				$dati_azione = rtvAzione($nome_widget);
				//require_once $moduli_path."/".$dati_azione['MODULO']."/widget/".strtolower($nome_widget)."_widget.cls.php";
				require_once p13n("modules/".$dati_azione['MODULO']."/widget/".strtolower($nome_widget)."_widget.cls.php");

				$classe = $nome_widget."_WIDGET";
				$obj_widget = new $classe($widget['WIDPRG']);
				$parm = $obj_widget->getParameters();
				//showArray($parm);
				
				$doSubmit = "";
				$idWidget = $nome_widget."-".$widget['WIDPRG']."-".$widget['WIDUSR'];
//				echo "IDWIDGET: $idWidget<br>";
				
				//if($parm['ONCLICK']) $doSubmit = "onClick=\"doSubmit('{$dati_azione['AZIONE']}', 'DEFAULT')\" style=\"cursor: pointer;\"";
				if($parm['ONCLICK']) $doSubmit = "onClick=\"console.log('click')\" style=\"cursor: pointer;\"";
				echo "<div id='$idWidget' class='portlet span-".$widget['WIDCOL']."'>
						<div class='portlet-header'>
							<span class='ui-icon ui-icon-minusthick portlet-toggle' style='display: ".($parm['MINIMIZED'] ? "block" : "none").";'></span>
							<i class='fa fa-refresh refresh' aria-hidden='true' style='display: ".(intval($parm['INTERVAL']) && $parm['RELOAD'] ? "block" : "none").";'></i>
							<label class='label-header'>{$parm['TITLE']}</label>
						</div>
						<div class='portlet-content'><center><i class='fa fa-spinner fa-pulse fa-3x fa-fw'></i></center></div>
						<label class='portlet-error'>0</label>
					</div>
					<script>
						creaAjax('$idWidget');
					</script>";
			}
		//}
		echo "</div>";
		//echo "</center>";
	//}