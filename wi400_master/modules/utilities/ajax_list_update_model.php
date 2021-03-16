<?php
/**
 * @desc Questo script ha lo scopo di aggiornare gli attributi delle celle sulla lista
 * Sono supportati i REFRESH di
 * Style
 * Formatting
 */
if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
	// Just for ajax response
	$pageDefaultDecoration = "clean_";
	//$fieldId 			= $_REQUEST["FIELD_ID"];
	//Controllo se è un campo multiplo
	if(strpos($fieldId, "new_") !== false) {
		//Recupero l'ID padre
		$arr = explode("_", $fieldId);
		unset($arr[count($arr)-1]);
		unset($arr[0]);
		$fieldId = implode("_", $arr);
	}
	if(isset($_REQUEST['DETAIL_ID']) && $_REQUEST['DETAIL_ID']) {
		$fieldObj = wi400Detail::getDetailField($_REQUEST["DETAIL_ID"], $fieldId);
	}else {
		if (isset($_REQUEST['COLONNA'])) {
			$colonna = $_REQUEST['COLONNA'];
			$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $_REQUEST['IDLIST']);
			//$cols = $wi400List->getCols();
			//$col = $cols[$colonna];
			$col = $wi400List->getCol($colonna);
			if($col) { 
				$fieldObj = $col->getInput();
			}
		}
	}
	// Codice Copiato da PAGINATION
	if ($wi400List->getIncludeFile() != ""){
		require_once $wi400List->getIncludeFile();
	}
	//ob_clean();
	//ob_start();
	$key = $_REQUEST['LIST_KEY'];
	$tab_index = $_REQUEST['TABINDEX']-1;

	$keyArrayId = $wi400List->getKeys();
	$columnsArray = array();
	$fixedValuesArray =array();
	$lastColumn = "";
	$fixedArray = array();
	// ************************************************************************
	// Raggruppamenti
	// ************************************************************************
	$colsGroups = array();
	$colsInput = array();
	$hasGroupBr = false;
	$hasGroup = false;
	foreach ($wi400List->getColumnsOrder() as $columnKey) {
		$wi400Column = $wi400List->getCol($columnKey);
		if (is_object($wi400Column)) {
			if ($wi400Column->getGroup() != ""){
				$hasGroup = true;
				if (!$hasGroupBr && strpos($wi400List->getGroupDescription($wi400Column->getGroup()),"<br>") > 0){
					$hasGroupBr = true;
				}
				$colsGroups[$wi400Column->getGroup()][$wi400Column->getKey()] = $wi400Column->getKey();
			}
		}
	}
	foreach ($wi400List->getColumnsOrder() as $columnKey) {
		$wi400Column = $wi400List->getCol($columnKey);

		if ($wi400Column && $wi400Column->getInput() != null){
			$colsInput[$wi400Column->getKey()]=$wi400Column->getKey();
		}
		if ($wi400Column != null && $wi400Column->getShow()){
			if (method_exists($wi400Column,"isFixed") && $wi400Column->isFixed()){
				// Fixed Col
				if (!isset($fixedArray[$columnKey])){
					$fixedArray[$columnKey] = $wi400Column;
					// Rimuovo la colonna da un eventuale gruppo di colonne
					if (isset($colsGroups[$wi400Column->getGroup()][$columnKey])) {
						unset($colsGroups[$wi400Column->getGroup()][$columnKey]);
						$columnsArray[] = $columnKey;
					}
						
				}
			}
				
			if ($wi400Column->getGroup() != ""){
				$colsList = $colsGroups[$wi400Column->getGroup()];
				foreach ($colsList as $ck) {
					if (array_search($ck,$columnsArray) === false){
						$columnsArray[] = $ck;
					}
				}
			}else{
				if (array_search($columnKey,$columnsArray) === false){
					$columnsArray[] = $columnKey;
				}
			}
		}
	}
	// Valorizzo la ROW Dei campi per ricalcolare la riga
	$arrayKey = explode("|", $key);
	$wi400List->setRuntimeField("action", "");
	$wi400List->setRuntimeField("updateHTML", True);	
	$wi400List->setRuntimeField("message", "");
	// Funzione personalizzata per recupero riga ..
	if ($wi400List->getCallBackFunction("getRow")!=False) {
		if (is_callable($wi400List->getCallBackFunction("getRow"))) {
			$wi400List->setRunTimeField("callBack", "getRow");
			$wi400List = call_user_func($wi400List->getCallBackFunction("getRow"), $wi400List, $_REQUEST);
			$row=$wi400List->getCurrentRow();
		} else {
			die("call user func not valid ".$wi400List->getCallBackFunction("getRow"));
		}		
	} else {
		// Provo a reperirlo da cache
		$row=array();
		if ($wi400List->getCacheData()==True) {
			$row = $wi400List->getRowArray($_REQUEST['ROW_COUNTER']);
		}
		if (count($row)==0) {
			$sql = $wi400List->getSql();
			$i=0;
			$and = " WHERE ";
			if (strpos(strtoupper($sql), "WHERE")!==False) $and = "AND";
			// Verifico se presenti chiavi prestabilite
			$keyArrayList = $wi400List->getAutoUpdateKey();
			$arrayKeyV=array();
			foreach ($keyArrayId as $chiavi => $dati) {
				if (count($keyArrayList)>0 && !isset($keyArrayList[$chiavi])) continue;
				$chiave = $chiavi;
				// Verifico se la colonna ha una formattazione particolare
				$wi400ColumnA = $wi400List->getKeys($chiavi);
				$wi400Column = $wi400ColumnA[$chiavi];
				if ($wi400Column->getWhereFormat() !="") {
					$chiave = $wi400Column->getWhereFormat();
				}
				//$sql .= " $and $chiavi='".$arrayKey[$i]."'";
				$sql .= " $and $chiave=?";
				$arrayKeyV[$chiavi]=$arrayKey[$i];
			    $i++;
			    $and = " AND ";
	
			}
			$result = $db->singlePrepare($sql);
			$do = $db->execute($result, $arrayKeyV);
			$row = $db->fetch_array($result);
			$db->freeResult($result);
		}
	}
	// Reperisco i valori A video
	$rowsSelectionArray = $wi400List->getSelectionArray();
	// Imposto la row con tutti i campi di input	
	if (isset($rowsSelectionArray[$key])) {
		//foreach ($wi400List->getColumnsOrder() as $columnKey) {
		foreach ($colsInput as $columnKey) {
			$wi400Column = $wi400List->getCol($columnKey);
			if (!is_object($wi400Column)) {
				developer_debug("Errore reprimento colonna: non esiste la colonna '$columnKey' nella lista '".$_REQUEST['IDLIST']."'");
				continue;
			}
			if ( $wi400Column->getInput() != null){
				$inputField = $wi400Column->getInput();
				$idKey = $inputField->getId();
				$idKeyArray = explode("-", $idKey);
				$id ="";
				if (isset($idKeyArray[2])) {
					$id = $idKeyArray[2];
				} 
				if (isset($rowsSelectionArray[$key][$id])) {
					$requestValue = $rowsSelectionArray[$key][$id];
					/*if ($inputField->getType() == "INPUT_TEXT" && method_exists($inputField, "getDecimals") && $inputField->getDecimals()>0){
						//$requestValue = doubleViewToModel($requestValue, $inputField->getDecimals());
					}*/
					$row[$columnKey]=$requestValue;
				}
				$idRiga = $key;
//				$idRiga = $tab_index;
				$chiave = $_REQUEST['IDLIST']."-".$_REQUEST['ROW_COUNTER']."-".$columnKey;
				$chiave = trim($chiave);
				if (isset($_REQUEST[$chiave])) {
					$row[$columnKey]=$_REQUEST[$chiave];
				}
			}
		}	
	}
	// Sostituisco eventuali $row con il Selection Array se colonna di input
	if ($wi400List->getCallBackFunction("setRow")!=False) {
		if (is_callable($wi400List->getCallBackFunction("setRow"))) {
			$wi400List->setRunTimeField("callBack", "setRow");
			//showArray($row);
			$wi400List->setCurrentRow($row);
			$wi400List = call_user_func($wi400List->getCallBackFunction("setRow"), $wi400List, $rowsSelectionArray[$key]);
			$row = $wi400List->getCurrentRow();
			//showArray($row);
			//die();
		} else {
			die("call user func not valid ".$wi400List->getCallBackFunction("setRow"));
		}
	}
	$rowsCounter=$_REQUEST['ROW_COUNTER'];
	$rowsCounter2=$_REQUEST['ROW_COUNTER'];
	$firstTime=False;
	//$row ="";
	if (isset($row)) {
		$wi400List->setCurrentRow($row);
		if ($wi400List->getCacheData()==True) {
			$wi400List->setRowArray($row, $rowsCounter);
		}
		// Experimental .. Calcolo autoWidth delle celle settando la lunghezza massima
		// @todo calcolo width con lunghezza massima contenuta nella colonna select min(length(trim(<col>))) from <file>
		/*if ($firstTime && $autoWidth) {
			foreach ($row as $key=>$value) {
				//$size = db2_field_display_size ($resultSet ,$key );
				$size = $db->field_display_size($resultSet, $key);
				$wi400Column = $wi400List->getCol($key);
				if (is_object($wi400Column) && $wi400Column->getWidth()=='') {
					$stringa = str_pad("W", $size, "W");
					//$wi400Column->setWidth(($size+18)*2);
					$wi400Column->setWidth(strlen_pixels($stringa));
				}
			}
			$firstTime = False;
		}*/
		//die();
		if ($rowsCounter < $pageRows){
			$righeDiPagina[$rowsCounter]=$row;
			// CARICAMENTO CHIAVI
			$keysRow = "";
			$keyValue = "";
			$isFirst = true;
			foreach ($wi400List->getKeys() as $key => $keyColumn) {
				if (isset($row[$key])){
					$keyValue = $row[$key];
				}else{
					//$keyValue = $wi400Column->getValue();
					if (method_exists($wi400Column, "getValue")) {
						$keyValue = $wi400Column->getValue();
					} else {
						developer_debug("Colonna $key non presente sulla lista e non può essere usata come chiave");
					}
				}
				//				echo "COLUMN: $key - FORMAT: ".$keyColumn->getFormat()."<br>";
	
				$keyValue = wi400List::applyFormat($keyValue, $keyColumn->getFormat());
	
				if (!$isFirst){
					$keysRow = $keysRow."|".$keyValue;
				}else{
					$isFirst = false;
					$keysRow = $keysRow."".$keyValue;
				}
			}
			//$keysRow = utf8_encode(trim($keysRow));
			$keysRow = trim($keysRow);
				
			$isSelected = false;
			if (isset($rowsSelectionArray[$keysRow])){
				$rowSelected = $rowsCounter;
				$isSelected = true;
			}
				
			$descRow = "";
			if ($wi400List->getPassDesc() && isset($row[$wi400List->getPassDesc()])){
				$descRow = $row[$wi400List->getPassDesc()];
				//$descRow = utf8_encode($row[$wi400List->getPassDesc()]);
			}
				
			// **********************************************
			// Stile riga
			// **********************************************
			$rowStyle = "wi400-grid-row";
			if ($wi400List->getStyle() != ""){
				$rowStyle = $wi400List->getStyle();
	
				if (is_array($rowStyle)>0){
					$condition = false;
					foreach($rowStyle as $rowCondition){
						$evalValue = substr($rowCondition[0],5).";";
						eval('$condition='.$evalValue.';');
						if ($condition){
							$rowStyle = $rowCondition[1];
							break;
						}
					}
				}
			}
				
			$pairStyle = $rowsCounter % 2 == 0 ? $rowStyle.'_pair' : '';
				
			// BREAK KEY
			if($wi400List->getBreakKey() != "" && true == False) {
				$breaKey = $row[$wi400List->getBreakKey()];
				if($breaKey != $saveBreaKey) {
					echo "<tr ><td colspan='100'><div class='wi400-grid-row-categories'>- $breaKey </div></td></tr>";
					$saveBreaKey = $breaKey;
						
					//Verifico se ci sono colonne fisse
					if(count($wi400List->getColumnsFix())) {
					$fixedValuesArray[$rowsCounter2] = "";
					$rowsCounter2++;
					}
					}
				}
				// END
				?>
			<tr id="<?= $wi400List->getIdList()."-".$rowsCounter."-tr" ?>" 
				class="<?= $rowStyle ?> <?= $pairStyle ?> <? if ($isSelected) echo $rowStyle.'_selected' ?>" 
	<? 
		if ($wi400List->getPassKey()){ 
				$jsFunction = "";
				if($wi400List->getPassKeyJsFunction()!="") {
					$jsFunction = $wi400List->getPassKeyJsFunction();
				}
	?>			
				onClick="passKey('<?= $wi400List->getIdList()?>', <?= $rowsCounter ?>, '<?= $jsFunction?>');"
	<? 
		} 
	?>
				onMouseOver="overGridRow('<?= $wi400List->getIdList() ?>',<?= $rowsCounter ?>)"
				onMouseOut="outGridRow('<?= $wi400List->getIdList() ?>',<?= $rowsCounter ?>)" style="height:<?= $wi400List->getRowHeight() ?>px">
	
				<td class="wi400-grid-row-cell" style="height:<?= $wi400List->getRowHeight() ?>px; <?= $wi400List->getHideSelectRow() ? "display: none;" : ""?>" width="5">
					<input disabled type="hidden" name="<?= $wi400List->getIdList() ?>-<?= $rowsCounter ?>" id="<?= $wi400List->getIdList() ?>-<?= $rowsCounter ?>" value="<?= htmlspecialchars($keysRow) ?>">
					
	<?
			foreach ($wi400List->getRowParameters() as $rowParamKey => $rowParamValue){
				if (!isset($rowParamValue)){
					$rowParamValue = $row[$rowParamKey];
				}
	?>				
					<input disabled type="hidden" name="<?= $wi400List->getIdList() ?>-<?= $rowsCounter ?>-<?= $rowParamKey ?>" id="<?= $wi400List->getIdList() ?>-<?= $rowsCounter ?>-<?= $rowParamKey ?>" value="<?= $rowParamValue ?>">
	<?
			}
		if ($wi400List->getPassDesc()){
	?>
					<input disabled type="hidden" name="<?= $wi400List->getIdList() ?>-<?= $rowsCounter ?>_DESCRIPTION" id="<?= $wi400List->getIdList() ?>-<?= $rowsCounter ?>_DESCRIPTION" value="<?= $descRow ?>">
	<?	}
?>					<input 
					onFocus="overGridRow('<?= $wi400List->getIdList() ?>',<?= $rowsCounter ?>)"
				onBlur="outGridRow('<?= $wi400List->getIdList() ?>',<?= $rowsCounter ?>)"
					 id="<?= $wi400List->getIdList() ?>-<?= $rowsCounter ?>-checkbox" name="<?= $wi400List->getIdList() ?>-<?= $rowsCounter ?>-checkbox" type="checkbox" value="true" <? if ($isSelected) echo 'checked' ?> onClick="checkGridRow('<?= $wi400List->getIdList() ?>', <?= $rowsCounter ?>);<?= $wi400List->getOnChangeChecked() ?>" <?= $wi400List->getHideSelectRow() ? "style='display: none;'" : ""?>>
			</td>
	 <?
		if ($wi400List->getCallBackFunction("validationRow")!=False) {
			if (is_callable($wi400List->getCallBackFunction("validationRow"))) {
				$wi400List->setRunTimeField("callBack", "validationRow");
				$wi400List = call_user_func($wi400List->getCallBackFunction("validationRow"), $wi400List, $_REQUEST);
				$row = $wi400List->getCurrentRow();
			} else {
				die("call user func not valid ".$wi400List->getCallBackFunction("validationRow"));
			}
		}
		// Aggiornamento del record
		if ($wi400List->getCallBackFunction("updateRow")!=False) {
			if (is_callable($wi400List->getCallBackFunction("updateRow"))) {
				$wi400List->setRunTimeField("callBack", "updateRow");
				$wi400List = call_user_func($wi400List->getCallBackFunction("updateRow"), $wi400List, $_REQUEST);
				$row = $wi400List->getCurrentRow();
				// La lista potrebbe impostare delle CUSTOM KEY
				if ($wi400List->getRuntimeField("KEYS")!="") {
					$keyRow=$wi400List->getRuntimeField("KEYS");
					// Resetto la chiave così non rimane sporca
					$wi400List->setRuntimeField("KEYS","");
				}
			} else {
				die("call user func not valid ".$wi400List->getCallBackFunction("updateRow"));
			}
		}
		//Aggiornamento totali di lista
		$cols_tot = $wi400List->getTotals();

		if(count($cols_tot) > 0) {
			$query_tot = "SELECT ";
			
			$select_tot = array();
			foreach ($cols_tot as $idCol => $totale) {
				$select_tot[] = "SUM($idCol) $idCol";
			}
			$query_tot .= implode(", ", $select_tot)." FROM ".$wi400List->getFrom();
			if($wi400List->getWhere()) {
				$query_tot .= " WHERE ".$wi400List->getWhere();
			}
			
			$rs = $db->singleQuery($query_tot);
			$dati = $db->fetch_array($rs);
			foreach ($dati as $idCol => $totale) {
				$num_decimali = strlen(substr(strrchr($totale, "."), 1));
				if($num_decimali) {
					$funzione = "wi400_format_DOUBLE_".$num_decimali;
					$dati[$idCol] = $funzione($totale);
				}
			}
?>
			<script>
				jQuery(document).ready(function() {
					var cols_tot = <?= json_encode($dati); ?>;
					for(var element in cols_tot) {
						//jQuery("#TOT_"+element).html(" "+cols_tot[element]);
						jQuery("#<?= $wi400List->getIdList() ?>Scroll").find("#TOT_"+element).html(" "+cols_tot[element]);
					}
				});
			</script>
<?php 
		}
		//FINE aggiornamento totali di lista
		
		//Script di ritorno
		$returnScript = $wi400List->getScriptOnAutoUpdate();
		if($returnScript) {
?>
			<script>
				<?= $returnScript?>
			</script>
<?php 
		}
		
		if (sizeof($wi400List->getMessages($keysRow))>0){
			$message = $wi400List->getMessage($keysRow);
			$messageHtml = "&nbsp;";
			$icona ="";
			$string_message="";
			$count = 1;
			$peso_errore = array("success"=>1, "error" =>10, "warning"=>5, "info"=>2);
			$current_peso = 0;
	        foreach ($message as $key => $valore) {
				$string_message .="$count) ".$valore[1]."\r\n";
				if (isset($peso_errore[strtolower($valore[0])])) { 
							if ($peso_errore[$valore[0]] > $current_peso) {
								$icona = $valore[0];
								$current_peso = $peso_errore[$valore[0]];
							}
				}
				// Setto la variabile in errore
				$rowErrorField[$valore[2]]=$valore;
				$count = $count +1;
			}	
			if (sizeof($message)>0){
				$onClickMessage = ' onclick="alert(\''.str_replace("\r\n", "", addslashes($string_message)).'\')" ';
				$messageHtml = '<img src="themes/common/images/yav/'.strtolower($icona).'.gif" title="'.$string_message.'" '.$onClickMessage.'>';
				//$messageHtml = '<img src="themes/common/images/yav/'.strtolower($icona).'.gif" title="'.$string_message.'">';
			}
			$classeErrore = "";
			if ($current_peso == 10) {
				$classeErrore = "row-has-error";
			}
			if ($current_peso == 5) {
				$classeErrore = "row-has-warning";
			}
			if ($current_peso == 2) {
				$classeErrore = "row-has-info";
			}
			if ($current_peso == 1) {
				$classeErrore = "row-success";
			}	
			echo '<td class="wi400-grid-row-cell '.$classeErrore.'" width="4">'.$messageHtml.'</td>';	
		} else {
			echo '<td class="wi400-grid-row-cell" width="0" style="padding: 0px 0px;"></td>';
		}
		
			if ($wi400List->getDetailHtml() != "" || $wi400List->getDetailAjax() != ""){
				$statico = 'false';
				// Verifico se esiste una condizione di abilitazione del drop
				if ($wi400List->getDetailAjax() != "" && $wi400List->getDetailCondition()!="") {
					$statico = 'true';
					if(!$wi400List->getDetailAjaxStatic()) {
						$statico = 'false';
					}	
					$evalValue = $wi400List->getDetailCondition();
					eval('$condition='.$evalValue.";");
							if ($condition){
								echo '<td class="wi400-grid-row-cell" width="5"><img src="themes/common/images/grid/expand.png" style="cursor:pointer" id="'.$wi400List->getIdList().'-'.$rowsCounter.'-detail-img" onClick="openRowDetail(\''.$wi400List->getIdList().'\','.$rowsCounter.','.$statico.')"></td>';
							} else {
								echo '<td class="wi400-grid-row-cell" width="5">&nbsp;</td>';
							}	
				} else {	
					echo '<td class="wi400-grid-row-cell" width="5"><img src="themes/common/images/grid/expand.png" style="cursor:pointer" id="'.$wi400List->getIdList().'-'.$rowsCounter.'-detail-img" onClick="openRowDetail(\''.$wi400List->getIdList().'\','.$rowsCounter.','.$statico.')"></td>';
				}
			}
			
				$common_value = array();
				if($wi400List->getCommonCondition()!="") {
					/**
					 * Recupero di un EVAL comune a una o più condizioni di più colonne della lista
					 * in modo che questo venga eseguito una volta sola per riga e poi il risultato venga sostituito al marker ##COMMON_LIST##
					 * in più eval specifici che hanno bisogno di eseguire lo stesso controllo ogni volta (es: readonly, default value, ...)
					 */
//					$common_list_value = manage_eval_condition($wi400List->getCommonCondition(), $row);
					$common_list_value = $wi400List->manage_eval_condition($wi400List->getCommonCondition(), $row);
	//				echo "COMMON_LIST:$common_list_value<br>";
	
					$common_value["LIST"]  = $common_list_value;
				}
			
				$colsCounter = 0;
				foreach ($columnsArray as $columnKey) {
					
					$wi400Column = $wi400List->getCol($columnKey);
					
					if ($wi400Column != null && $wi400Column->getShow()){
						
						$wi400List->setCurrentCol($wi400Column);
						
						// **********************************************
						// Valore colonna
						// **********************************************
						$rowValue="";
	//					echo "<font color='red'>KEY:".$wi400Column->getKey()."</font><br>";
	/*
						if ($wi400Column->getDefaultValue() != ""){
							$defaultValue = $wi400Column->getDefaultValue();
							if (is_array($defaultValue)>0){
								$condition = false;
								foreach($defaultValue as $rowCondition){
									$evalValue = substr($rowCondition[0],5).";";
									eval('$condition='.$evalValue.';');
									if ($condition){
										$rowValue = $rowCondition[1];
										break;
									}
								}
							}else if (strpos($defaultValue, "EVAL:")===0){
									
								$evalValue = substr($defaultValue,5).";";
									
								eval('$rowValue='.$evalValue);
								$row[$wi400Column->getKey()] = $rowValue;
							}else{
								if (!isset($row[$wi400Column->getKey()])){
									$row[$wi400Column->getKey()] = $defaultValue;
								}
							}
						}
	*/					
						if($wi400Column->getCommonCondition()!="") {
							/**
							 * Recupera un EVAL comune a più condizioni di una colonna della lista
							 * in modo che questo venga eseguito una volta sola per colonna nella riga e poi il risultato venga sostituito al marker ##COMMON_COLUMN##
							 * in più eval specifici che hanno bisogno di eseguire lo stesso controllo ogni volta (es: readonly, default value, ...)
							 */
//							$common_col_value = manage_eval_condition($wi400Column->getCommonCondition(), $row);
							$common_col_value = $wi400List->manage_eval_condition($wi400Column->getCommonCondition(), $row);
	//						echo "COMMON_COL:$common_col_value<br>";
						
							$common_value["COLUMN"]  = $common_col_value;
						}
						
						if ($wi400Column->getDefaultValue() != ""){
	//						echo "CONDITION:<pre>"; print_r($wi400Column->getDefaultValue()); echo "</pre>";
	//						echo "COMMON VALUE:<pre>"; print_r($common_value); echo "</pre>";
	
//							$value = manage_eval_condition($wi400Column->getDefaultValue(), $row, $common_value, $wi400Column->getKey());
							$value = $wi400List->manage_eval_condition($wi400Column->getDefaultValue(), $row, $common_value, "defaultValue", $wi400Column->getKey());
	//						echo "DEFAULT_VALUE:$value<br>";
						
							$defaultValue = $wi400Column->getDefaultValue();
							if (is_array($defaultValue)>0) {
								$rowValue = $value;
							}
							else {
								$row[$wi400Column->getKey()] = $value;
							}
						}
						
						if (isset($row[$wi400Column->getKey()]) && $row[$wi400Column->getKey()] != ""){
							$rowValue = "".$row[$wi400Column->getKey()];
							//$rowValue = htmlentities($rowValue);
						}
	//					echo "VAL:$rowValue<br>";
						
						// **********************************************
						// Stile colonna
						// **********************************************
						$rowStyle = "";
						if ($wi400Column->getStyle() != ""){
//							$rowStyle = manage_eval_condition($wi400Column->getStyle(), $row, $common_value);
							$rowStyle = $wi400List->manage_eval_condition($wi400Column->getStyle(), $row, $common_value, "style");
						}
						//if (isset($rowErrorField[$wi400Column->getKey()])) {
						//	$rowStyle .= " ".$rowErrorField[$wi400Column->getKey()]."Field ";
						//}
						$rowAlign  = $wi400Column->getAlign();
						
	//					$rowFormat = $wi400Column->getFormat("LIST");
						$rowFormat = "";
						if ($wi400Column->getFormat("LIST")){
//							$rowFormat = manage_eval_condition($wi400Column->getFormat("LIST"), $row, $common_value);
							$rowFormat = $wi400List->manage_eval_condition($wi400Column->getFormat("LIST"), $row, $common_value, "format");
						}				
						
	//					$rowDecorator = $wi400Column->getDecorator("LIST");
						$rowDecorator = "";
						if ($wi400Column->getDecorator("LIST")){
//							$rowDecorator = manage_eval_condition($wi400Column->getDecorator("LIST"), $row, $common_value);
							$rowDecorator = $wi400List->manage_eval_condition($wi400Column->getDecorator("LIST"), $row, $common_value, "decorator");
						}					
						
						$rowDecode  = $wi400Column->getDecode();
						$rowWidth = "";
						
	//					echo "CONDITION:<pre>"; print_r($wi400Column->getReadonly()); echo "</pre>";
	//					echo "COMMON VALUE:<pre>"; print_r($common_value); echo "</pre>";
						
						$rowReadonly = false;
//						$rowReadonly = manage_eval_condition($wi400Column->getReadonly(), $row, $common_value);
						$rowReadonly = $wi400List->manage_eval_condition($wi400Column->getReadonly(), $row, $common_value, "readonly");
	//					echo "READONLY:"; var_dump($rowReadonly); echo "<br>";
						
						if ($lastColumn == $wi400Column->getKey()){
							//$rowWidth = "width=\"100%\"";
						}
						
						$rowValue = wi400List::applyFormat($rowValue, $rowFormat);
						if ($wi400Column->getDecodeKey()){
							$rowValue = wi400List::applyDecode($row[$wi400Column->getDecodeKey()], $rowDecode);
						}
						$rowValue = wi400List::applyDecorator($rowValue, $rowDecorator, array("ROW" => $rowsCounter));
						
						$inputField = false;
						$columnOnClick = false;
						
						if ($wi400Column->getInput() != null){
	
							$inputField = clone $wi400Column->getInput();
							// SE RIGA SELEZIONATA REPERISCO, SE PRESENTE, IL VALORE DALLA SESSIONE
							// NO VIENE FATTO IN PARTENZA
							/*if ($isSelected){
								if (isset($rowsSelectionArray[$keysRow][$wi400Column->getKey()])){
									$rowValue = $rowsSelectionArray[$keysRow][$wi400Column->getKey()];
									if (method_exists($inputField, "getDecimals") && $inputField->getDecimals()>0) {
										$rowValue = doubleModelToView($rowsSelectionArray[$keysRow][$wi400Column->getKey()], $inputField->getDecimals());	
									}
								}
								
							}*/
							
							//$inputField = $wi400Column->getInput();
							$inputField->setRowNumber($rowsCounter);
							$inputField->setReadonly($rowReadonly);
							$inputField->setIdList($wi400List->getIdList());
							$inputFieldId = $wi400List->getIdList()."-".$rowsCounter."-".$wi400Column->getKey();
							$inputField->setId($inputFieldId);
							if (method_exists($inputField,'getLookUp')) {
								if ($inputField->getLookUp() != ""){
									$myLookUp = $inputField->getLookUp();
									// Ciclo sulle chiavi
									$keyToPass = array();
									$inpField = array();
									if (count($myLookUp->getCpyFields())> 1) {
									$keyToPass = $myLookUp->getCpyFields();
									foreach ($keyToPass as $k=>$val) {
										$inpField[] = $wi400List->getIdList()."-".$rowsCounter."-".$val;
									}
									} else {
									  	$inpField[]=$inputFieldId;
									} 
									$myLookUp->setFields($inpField);
									$inputField->setLookUp($myLookUp);
								}
							}
							
							$inputField->setTitle("");
							//$inputField->setStyleClass("inputtext");
							if ($inputField && $inputField->getDecode() != "" && $rowValue != ""){
			
								$decodeParameters = $inputField->getDecode();
								$decodeType = "table";
								if (isset($decodeParameters["TYPE"])){
									$decodeType = $decodeParameters["TYPE"];
								}
								require_once p13nPackage($decodeType);
								// Cerco eventuali decode Parameters
								if (isset($decodeParameters['JS_PARAMETERS'])) {
									foreach ($decodeParameters['JS_PARAMETERS'] as $key => $value) {
										// Se Dettaglio
										if(isset($_REQUEST['DETAIL_ID']) && $_REQUEST['DETAIL_ID']) {
											$decodeParameters[$value]=$_REQUEST[$key];
										} else {
											// Recupero dai pezzi della lista
											$lista= $_REQUEST['IDLIST'];
											$myrow= $_REQUEST['ROW_COUNTER'];
											$decodeParameters[$value]=$_REQUEST[$lista."-".$myrow."-".$key];
										}
									}
								}					
								$decodeClass = new $decodeType();
								$decodeClass->setDecodeParameters($decodeParameters);
								
								if ($rowValue != ""){
									$decodeClass->setFieldId($inputFieldId);
									$decodeClass->setFieldValue($rowValue);								
									$decodeResult = $decodeClass->decode();
									if ($decodeResult != ""){
										$inputField->setTitle($decodeResult);
									}else{
										$inputField->setStyleClass("inputerror errorField inputtext");
									}
								}							
							}
													
							$inputField->setName($inputFieldId);
							// Aggiunge JS per AutoUpdate
							// Aggiunge JS per AutoUpdate
							if ($wi400List->getAutoUpdateList() && $wi400Column->getDisableAutoUpdate()==False) {
								$backGround='false';
								if ($wi400Column->getAutoUpdateBackGround()) {
										$backGround='true';
								}
								if(!$wi400List->getUpdateOnChangeRow()) {
									$stringFunction = "updateListRow";
									if($inputField->getType()=='CHECKBOX') $stringFunction = "updateListRowTimeout";
									if (strtoupper($wi400List->getAutoUpdateEvent())=="ONBLUR") 
										$inputField->setOnBlur("$stringFunction(this, '', $backGround)");
									if (strtoupper($wi400List->getAutoUpdateEvent())=="ONCHANGE")
										//$inputField->setOnChange($inputField->getOnChange());
										if(strpos($inputField->getOnChange(), $stringFunction) !== false) {
										}else {
											$inputField->setOnChange("$stringFunction(this, '', $backGround);".$inputField->getOnChange());
										}
								}
							}																													
							// Aggiunge indicatore per tab e focus
							//$inputField->setTabIndex($rowsCounter+1);
							if($inputField->getType() != "CHECKBOX") {
								$inputField->setTabIndex();
							}
							if (isset($_SESSION['LAST_FOCUSED_FIELD']) && $wi400List->getRefreshFocus()==True) {
								if ($inputFieldId==$_SESSION['LAST_FOCUSED_FIELD']) {
									$inputField->setAutoFocus(true);
									$firstTime = false;
								}else{
									$inputField->setAutoFocus(false);
								}
							} else {
								if ($firstTime) {
									$inputField->setAutoFocus(true);
									$firstTime = false;
								}else{
									$inputField->setAutoFocus(false);
								}
							}
							
							if ($inputField->getType()=='INPUT_TEXT'){
								if ($inputField->getAlign() == ""){
									$inputField->setAlign($wi400Column->getAlign());
								}
							} else if ($inputField->getType()=='CHECKBOX'){
								if ("".$inputField->getValue() === "".$rowValue) {
									$inputField->setChecked(true);
								}else{
									$inputField->setChecked(false);
								}
								
							} else if ($inputField->getType()=='IMAGE'){
								$inputField->setUrl($rowValue);
							}
							
							if ($inputField->getType()!='CHECKBOX'){
								$inputField->setValue($rowValue); 
							}
						}else if(!$wi400Column->getActionListId() && $rowDecorator && ($wi400Column->getToolTip() || sizeof($wi400Column->getToolTipAjax()) > 0)) {
							$columnOnClick = true;
						}
						
						if ($rowValue == "") $rowValue = "&nbsp;";
				//if (!isset($fixedArray[$columnKey])){	
	
		$outputHtml="<td";		
		$startLink = "";
		$endLink = "";
		$showDetail = false;
		$onClick = "";
	/*	
		$column_key = "";
		if($wi400Column->getDetailLabel()) {
			$column_key = $wi400Column->getKey();
		}
	*/	
		if ($wi400Column->getDetailAction() != "" && trim($rowValue) != "&nbsp;"  && !$rowReadonly){
			$showDetail = true;

			//detail Style
			$detailStyle = "";
			if ($wi400Column->getDetailStyle() != ""){
//				$detailStyle = manage_eval_condition($wi400Column->getDetailStyle(), $row, $common_value);
				$detailStyle = $wi400List->manage_eval_condition($wi400Column->getDetailStyle(), $row, $common_value, "detailStyle");
			}
			
			$startLink = "";
			if(!$rowReadonly) {
				
				if($wi400Column->getDetailTarget()=="WINDOW") {
	//				echo "<br><pre>ROWVALYUE:".$rowValue;
	//				if($wi400Column->getKey() == "STATO") {
					if(preg_match('/(?i)msie [1-8]/',$_SERVER['HTTP_USER_AGENT']) && strpos($rowValue, "wi400Empty")!==False) {
						$onClick = "showDetail('".$wi400List->getIdList()."','".$colsCounter."','".$rowsCounter."','".$wi400Column->getDetailAction()."','".$wi400Column->getDetailForm()."', ".$wi400Column->getDetailWidth().", ".$wi400Column->getDetailHeight().", ".$wi400Column->getDetailModal().", ".$wi400Column->getDetailUrlEncode().", '".$wi400Column->getKey()."', ".$wi400Column->getDetailClose().",'".$wi400Column->getDetailGateway()."')";
						$startLink  ="";
						$endLink = "";
					}
					else {
						$startLink = "<a id=\"".$wi400List->getIdList()."_".$wi400Column->getKey()."_".$rowsCounter."\" class=\"rowDetail ".$detailStyle."\" href='javascript:showDetail(\"".$wi400List->getIdList()."\",\"".$colsCounter."\",\"".$rowsCounter."\",\"".$wi400Column->getDetailAction()."\",\"".$wi400Column->getDetailForm()."\", ".$wi400Column->getDetailWidth().", ".$wi400Column->getDetailHeight().", ".$wi400Column->getDetailModal().", ".$wi400Column->getDetailUrlEncode().", \"".$wi400Column->getKey()."\", ".$wi400Column->getDetailClose().",\"".$wi400Column->getDetailGateway()."\")'>";
						$endLink = "</a>";
					}
				}
				else if($wi400Column->getDetailTarget()=="ON_CLICK") {
	//				doSubmit(actionArray.get("action")+"&IDLIST=" + idList + "&g=" + actionArray.get("gateway"), actionArray.get("form"), false, false, actionArray.get("confirmMessage"));			
	//				$onClick = 'doSubmit("'.$this->action.$gatewayUrl.'","'.$this->form.'", '.$checkValidation.', "'.$this->getCheckUpdateText().'", "'.$message.'")';
	//				checkGridRow('".$this->getIdList()."',".$this->getRowNumber().",true)
				
					$message = "";
			    	if ($wi400Column->getConfirmMessage()!=""){
			    		$message = $wi400Column->getConfirmMessage();
			    	}
			    	
			    	// Aggiunta gateway
			    	$gatewayUrl = "";
			    	if ($wi400Column->getDetailGateway() != ""){
			    		$gatewayUrl = "&g=".$wi400Column->getDetailGateway();
			    	}
			    	
			    	$startLink = "<a class=rowDetail href=\"javascript:onClickDetail('".$wi400List->getIdList()."',$colsCounter,$rowsCounter,'".$wi400Column->getDetailAction()."','".$wi400Column->getDetailForm()."','$gatewayUrl','$message');\">";
			    	$endLink = "</a>";
				}
				else if($wi400Column->getDetailTarget()=="SEL_ACTION") {
					$message = "";
					if ($wi400Column->getConfirmMessage()!=""){
						$message = $wi400Column->getConfirmMessage();
					}
					
					// Aggiunta gateway
					$gatewayUrl = "";
					if ($wi400Column->getDetailGateway() != ""){
						$gatewayUrl = "&g=".$wi400Column->getDetailGateway();
					}
					
					$startLink = "<a class=rowDetail href=\"javascript:onClickSelAction('".$wi400List->getIdList()."',$colsCounter,$rowsCounter,'".$wi400Column->getDetailAction()."','".$wi400Column->getDetailForm()."','$gatewayUrl','$message');\">";
					$endLink = "</a>";
				}
			}
			$detailInput = new wi400InputHidden($wi400Column->getDetailAction()."-".$wi400List->getIdList()."-".$colsCounter."-".$rowsCounter);
			$detailInput->setDisabled(true);
			
			$detailUrlAttach = "";
			if (sizeof($wi400Column->getDetailKeys())>0){
				$dk = array();
				foreach($wi400Column->getDetailKeys() as $colKey){
					if(!array_key_exists($colKey, $row)) {
						$dk[] = "";
						$detailUrlAttach .= "&".$colKey."=''";
					}
					else {
						$dk[] = $row[$colKey];
						$detailUrlAttach .= "&".$colKey."=".$row[$colKey];
					}
				}
				$detailInput->setValue(implode("|",$dk).$detailUrlAttach);
			}else{
				foreach ($wi400List->getKeys() as $key => $keyColumn) {
					if (isset($row[$key])){
						$detailUrlAttach .= "&".$key."=".wi400List::applyFormat($row[$key], $keyColumn->getFormat());
					}
				}
				$detailInput->setValue($keysRow.$detailUrlAttach);
			}
			
		}
	
		// Click action
	/*	
	//	if($wi400Column->getActionListId()!="" && !$rowReadonly) {
		if ($wi400Column->getActionListId() != "" && trim($rowValue) != "&nbsp;" && !$rowReadonly){
			$startLink = "<a class=\"rowDetail\" href=\"javascript:doSelectListAction('".$wi400List->getIdList()."','".$rowsCounter."','".$wi400Column->getActionListId()."')\">";
			$endLink = "</a>";
		}
	*/
//		$actionListId = manage_eval_condition($wi400Column->getActionListId(), $row, $common_value);
		$actionListId = $wi400List->manage_eval_condition($wi400Column->getActionListId(), $row, $common_value, "actionList");
		
		if ($actionListId != "" && trim($rowValue) != "&nbsp;" && !$rowReadonly){
			$startLink = "<a class=\"rowDetail\" href=\"javascript:doSelectListAction('".$wi400List->getIdList()."','".$rowsCounter."','".$actionListId."')\">";
			$endLink = "</a>";
		}
		
		$startWidth = "";
		$endWidth = "";
		if ($wi400Column->getWidth() !== "" && $wi400Column->getOrientation() != "vertical"){
			$startWidth = "<div class=\"wi400-grid-row-content\" style=\"width:".$wi400Column->getWidth()."\">";
			$endWidth   = "</div>";
		}
		
		if ($startLink == "" && $wi400Column->getUpdatable()){
			$startLink = "<div id=\"".$wi400List->getIdList()."_".$wi400Column->getKey()."_".$rowsCounter."\">";
			$endLink = "</div>";
		}
	/*	
		if ($startLink == "" && $wi400Column->getDraggable()){
			$canDrag = $wi400Column->getKey();
			$startLink = "<div id=\"".$wi400List->getIdList()."_".$wi400Column->getKey()."_".$rowsCounter."\" title=\"".$rowsCounter."\" style=\"border:1px solid #bdbdbd; padding:2px;background-color:#fde19a;cursor:move\">";
			$endLink = "</div>";
		}
	*//*
		$draggable = false;
		if($startLink=="") {
			if (strpos($wi400Column->getDraggable(), "EVAL:")===0){
				$evalValue = substr($wi400Column->getDraggable(),5).";";
				eval('$draggable='.$evalValue);
			}else {
				$draggable = $wi400Column->getDraggable();
			}
		}
	*/
		$draggable = false;
		if($startLink=="") {
//			$draggable = manage_eval_condition($wi400Column->getDraggable(), $row, $common_value);
			$draggable = $wi400List->manage_eval_condition($wi400Column->getDraggable(), $row, $common_value, "draggable");
		}
			
		if($draggable===true) {
			$canDrag = $wi400Column->getKey();
			$startLink = "<div id=\"".$wi400List->getIdList()."_".$wi400Column->getKey()."_".$rowsCounter."\" title=\"".$rowsCounter."\" style=\"border:1px solid #bdbdbd; padding:2px;background-color:#fde19a;cursor:move\">";
			$endLink = "</div>";
		}
		
		$toolTip = "";
		$toolTipArray = array();
		$toolTipUrl = "";
		if ($wi400Column->getToolTip() != ""){
			if (!is_array($wi400Column->getToolTip())){
				$toolTipArray[] = $wi400Column->getToolTip();
			}else{
				$toolTipArray = $wi400Column->getToolTip();
			}
			foreach ($toolTipArray as $toolTipPart){
				if (!is_array($toolTipPart)){
					$toolTipPart = array($toolTipPart);
				}
				if (isset($row[$toolTipPart[0]])){
					$tooltipValue = $row[$toolTipPart[0]];
					if (sizeof($toolTipPart)>1){
						// apply format
						$tooltipValue = wi400List::applyFormat($tooltipValue, $toolTipPart[1]);
					}
					$toolTip.= $tooltipValue;
				}else{
					$toolTip.= $toolTipPart[0];
				}
				$toolTip = str_replace('"', "'", $toolTip);
			}
		//}else if (sizeof($wi400Column->getToolTipAjax()) > 0 && $row[$wi400Column->getKey()] !=""){
		}else if (sizeof($wi400Column->getToolTipAjax()) > 0){
			$abilitaTooltip = True;
			if (isset($row[$wi400Column->getKey()]) && $row[$wi400Column->getKey()] =="" && $wi400Column->getToolTipAjax("hasValue")==True) {
				$abilitaTooltip = False;
			}	
			// Tooltip Ajax
			if ($abilitaTooltip == True) {
				$tooltipKey = "";
				foreach ($wi400List->getKeys() as $key => $keyColumn) {
					if (isset($row[$key])){
						$tooltipKey .= "&".$key."=".base64_encode(wi400List::applyFormat($row[$key], $keyColumn->getFormat()));
	//					$tooltipKey .= "&".$key."=".wi400List::applyFormat($row[$key], $keyColumn->getFormat());
					}
				}
				$extraParameters ="";
				if (count($wi400Column->getToolTipAjax("extraParameters"))>0) {
				     $extraParameters = "&EXTRA_PARAMETERS=".$wi400Column->getToolTipAjax("extraParameters");
				}
				$toolTipUrl = "index.php?t=".$wi400Column->getToolTipAjax("action")."&f=".$wi400Column->getToolTipAjax("form").$tooltipKey.$extraParameters;
		
			}
		}
		// Gestione errore su campo
		if (isset($rowErrorField[$wi400Column->getKey()])) {
			$rowStyle .= " ".$rowErrorField[$wi400Column->getKey()][0]."Field ";
			$toolTip = $rowErrorField[$wi400Column->getKey()][1];
		}	
		$outputHtml .=" class=\"wi400-grid-row-cell $rowStyle\" ";
		// Scrittura sul campo di evenutali chiavi per javascript
		if (count($wi400Column->getKeys()) > 0) {
				$chiavi = $wi400Column->getKeys();
				//if (count($chiavi>0)) {
					$outputHtml .= " wi400_keys=";
					$separatore = "";
					foreach ($chiavi as $mykey => $myvalore) {
						$outputHtml .= $separatore.$row[$myvalore];
						$separatore = "|";	
					}	
				//}			
	    }
	    // Scrittura dell'identificativo univoco del campo
	    if ($wi400Column->getWriteUniqueId()==True) {
	    		//$outputHtml .= " id=\"{$columnKey}_$rowCounter\" ";
	    		$outputHtml .= " id=\"".$wi400Column->getKey()."\">";
	    }     
		// SE colonna fixed devo impostare manualmente l'altezza
		if (isset($fixedArray[$columnKey])){
			/*if(preg_match('/(?i)msie [1-10]/',$_SERVER['HTTP_USER_AGENT'])) {
				$outputHtml.= " style=\"height: 33px;\"";
			}
			else if(preg_match('/(?i)Firefox/',$_SERVER['HTTP_USER_AGENT'])) {
				$outputHtml.= " style=\"height: 27px;\"";
			}
			else {
				$outputHtml.= " style=\"height:".$wi400List->getRowHeight()."px;\"";
			}*/
	       $outputHtml.= " style=\"height:".$wi400List->getRowHeight()."px;\"";
		}
		if ($toolTipUrl != ""){
			$toolAlert = "false";
			if($columnOnClick && isset($_SESSION['NAVIGAZIONE_TABLET_ATTIVA']) && $_SESSION['NAVIGAZIONE_TABLET_ATTIVA']) {
				$outputHtml.= " onClick=\"";
				$toolAlert = "true";
				$columnOnClick = false;
			}else {
				$outputHtml.= " onmouseenter=\"";
			}
			$outputHtml.= "showToolTipQueued(this,'$toolTipUrl', ".booleanToString($wi400Column->getToolTipAjax("persistence")).", $toolAlert)\" ";
		}
		if($columnOnClick && !$showDetail && isset($_SESSION['NAVIGAZIONE_TABLET_ATTIVA']) && $_SESSION['NAVIGAZIONE_TABLET_ATTIVA']) {
			$toolTip = str_replace("'", "&rsquo;", $toolTip);
			$columnOnClick = "onClick='alert(\"$toolTip\")'";
		}else {
			$columnOnClick = "";
		}
		$outputHtml.=' title="'.$toolTip.'"  '.$columnOnClick.' align="'.$rowAlign.'" '.$rowWidth.'>'.$startWidth.$startLink;
		if ($inputField) {
				if ($wi400List->getProtectAllFields()==True) {
					$inputField->setReadonly(True);
				}
				// Call back per $inputField
				if ($wi400List->getCallBackFunction("inputCell")!=False) {
					if (is_callable($wi400List->getCallBackFunction("inputCell"))) {
						$inputField = call_user_func($wi400List->getCallBackFunction("inputCell"), $wi400List, $inputField, $row);
					} else {
						die("call user func not valid ".$wi400List->getCallBackFunction("inputCell"));
					}
				}
				//Label scritta sopra all'input come descrizione
				if ($inputField->getForceLabel()==True) {
					$outputHtml .= "<label id='".$inputField->getId()."-FORCELABEL'>".$inputField->getLabel()."</label>";
				}
				$outputHtml .=$inputField->getHtml();
		} else {
				if($onClick) {
					$rowValue = str_replace("wi400Empty()", $onClick, $rowValue);
				}else {
					$rowValue = str_replace("style='cursor: pointer;' onClick=\"wi400Empty()\"", "", $rowValue);
				}
				$outputHtml .= $rowValue.$endLink;
	    }
		if ($showDetail) $outputHtml .= $detailInput->getHtml();
		$outputHtml.=$endWidth;
		$outputHtml.="</td>";
		if (!isset($fixedArray[$columnKey])){
			echo $outputHtml;
			$colsCounter++;
		} else {
	/*
	    	$fixedValuesArray[$keysRow][$wi400Column->getKey()] = $rowValue;
			$fixedValuesHtml[$keysRow][$wi400Column->getKey()]  = $outputHtml;
	*/		
			$fixedValuesArray[$rowsCounter2][$wi400Column->getKey()] = $rowValue;
			$fixedValuesHtml[$rowsCounter2][$wi400Column->getKey()]  = $outputHtml;
		}	
				/*} else {
	// Colonna fixed con attributi .. @todo FINIRE
	// Questo � quello che fa fixed alla fine..
	
	                $htmlfixed ='<td class=\'wi400-grid-row-cell\' style=\'height:'.$wi400List->getRowHeight().'px;\'';
	                //if ($toolTipUrl != ""){ 
					//	$htmlfixed.='onmouseover="showToolTipQueued(this,\''.$toolTipUrl.'\', '.booleanToString($wi400Column->getToolTipAjax("persistence")).'" onmouseout="hideToolTip(this, '.booleanToString($wi400Column->getToolTipAjax("persistence")).')';
					//}
					$htmlfixed.=' title=\''.utf8_encode($toolTip).'\' align=\''.$rowAlign.'\' '.$rowWidth.'>'.$startWidth.$startLink;
					$htmlfixed.=$rowValue.$endLink;
					//if ($showDetail) $detailInput->dispose();
					$htmlfixed.=$endWidth;
				    $htmlfixed.="</td>";
					$fixedValuesArray[$keysRow][$wi400Column->getKey()] = $rowValue;
					$fixedValuesHtml[$keysRow][$wi400Column->getKey()]  = $htmlfixed;
	
				} // is not fixed */
						}// Is visible column
					} // Column cicle
	?>
			</tr>
	<?
	
				if ($wi400List->getDetailHtml() != "" || $wi400List->getDetailAjax() != ""){
	?>		
				<tr id="<?= $wi400List->getIdList().'-'.$rowsCounter.'-detail' ?>" style="display:none;">
					<td style="background-color:#ededed">&nbsp;</td>
					<td colspan="<?= $colsCounter + 1 ?>" id="<?= $wi400List->getIdList().'-'.$rowsCounter.'-detail-html' ?>"><?= $wi400List->getDetailHtml() ?></td>
				</tr>
	<?
				}// End detail
	
			}
			//$rowsCounter++;
			//$rowsCounter2++;
		}
	// RIGHE FIXED
	$fixedHtml="";
	foreach ($fixedValuesArray as $mykey => $fixedRow){
			$fixedHtml .="<tr id='".$wi400List->getIdList()."-Fixed-".$rowsCounter."-tr' >";
			foreach ($fixedArray as $fixedKey => $fixedCol) {
				//$fixedWidth = $fixedCol->getWidth();
				//if ($fixedWidth === 0) $fixedWidth = 150;
				if(isset($fixedValuesHtml[$mykey][$fixedKey])) {
					$fixedHtml.= $fixedValuesHtml[$mykey][$fixedKey];
				}
			}
			if ($fixedRow=="") {
				$fixedHtml.= "<td colspan='100' class='wi400-grid-row-categories'></td>";
			}
			$fixedHtml.= "</tr>";
			//$rowsCounter++;
	}
	// FINE RIGHE FIXED
	// Operazioni finali
	if ($wi400List->getCallBackFunction("final")!=False) {
		if (is_callable($wi400List->getCallBackFunction("final"))) {
			$wi400List->setRunTimeField("callBack", "final");
			$wi400List = call_user_func($wi400List->getCallBackFunction("final"), $wi400List, $_REQUEST);
		} else {
			die("call user func not valid ".$wi400List->getCallBackFunction("final"));
		}
	}
	// fine operazioni finali
	$outputHtml = ob_get_contents();
	if (version_compare(phpversion(), '5.6.4', '>')) {
		ob_end_clean();	
	}
	if ($wi400List->getRuntimeField("updateHTML")!=True) {
		$outputHtml = "*NONE";
		$fixedHtml = "*NONE";
    } 
    $action  = $wi400List->getRuntimeField("action");
    $message  = $wi400List->getRuntimeField("message");
    $wi400List->setScriptOnAutoUpdate("");
    // Salvataggio Oggetto Lista
    wi400Session::save(wi400Session::$_TYPE_LIST, $_REQUEST['IDLIST'], $wi400List);
	// FINE CODICE COPIATO DA PAGINATION
    if($wi400List->getSelectRowEveryWhere()) {
    	$outputHtml .= '<script>
							jQuery("#'.$_REQUEST['IDLIST'].'-'.$_REQUEST['ROW_COUNTER'].'-tr").on("click", function(event){
								selectRowEveryWhere(this);
							});
						</script>';
    }
} else {
	die("not Ajax Request");
}
?>