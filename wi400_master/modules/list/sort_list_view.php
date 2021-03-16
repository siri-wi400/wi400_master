<?
	if ($actionContext->getForm() == "SAVE"){
?>		
		<script>
		if (IFRAME_LOOKUP){
			top.location.href=top.location.href;
			top.f_dialogClose();
		}
		else {
			window.opener.location.href=window.opener.location.href;
			self.close();
		}
		</script>
<?	
	}
	else if($actionContext->getForm() == "DEFAULT") {
		$query = "SELECT * ";
//		$query = "SELECT ".$wi400List->getField();
		
		$query .= " FROM ".$wi400List->getFrom()." ";
		
		$where = array();
		if($wi400List->getWhere() != ""){
			//$query .= " WHERE ".$wi400List->getWhere();
			$where[] = $wi400List->getWhere();
		}
		if($where_list) {
			$where[] = $where_list;
		}
		if($where) $query .= " WHERE ".implode(" and ", $where);
		
		$query .= " ORDER BY ".$field;
//		echo "SQL: $query<br>";
		
		$resultSet = $db->query($query);
?>
<table width="100%" border="0">
	<tr>
		<td width="100%">
			<select id="double_list_RIGHT" size="20" class="wi400-double-list" style="width:100%" onChange="sortListClick()">
<?
		$colsList = array();
		
		$keyValue = $sortList->getSortKeys();
//		echo "CAMPI CHIAVE:<pre>"; print_r($keyValue); echo "</pre>";
		
		while($row = $db->fetch_array($resultSet)) {
/*			
			$colsList[] = $row[$keyValue[0]];
			
			echo"<option value=\"".$row[$keyValue[0]]."\">";
*/
			$rowKeys = array();
			foreach($keyValue as $key) {
				$rowKeys[] = $row[$key];
			}
			
//			$chiave = implode(";", $rowKeys);
			$chiave = implode("§", $rowKeys);
//			echo "CHIAVE DI RIGA: $chiave<br>";
			
			$colsList[] = $chiave;
			
			echo"<option value=\"".$chiave."\">";
			
			foreach($sortList->getSortColumns() as $columnKey) {
				$wi400Column = $wi400List->getCol($columnKey);
				
				if($wi400Column->getDefaultValue() != "") {					
					$rowValue = "";
					
					$defaultValue = $wi400Column->getDefaultValue();
					
					if(is_array($defaultValue)>0) {
						$condition = false;
						
						foreach($defaultValue as $rowCondition) {
							$evalValue = substr($rowCondition[0],5).";";
							eval('$condition='.$evalValue.';');
							if($condition) {
								$rowValue = $rowCondition[1];
								break;
							}
						}
					}
					else if(strpos($defaultValue, "EVAL:")===0) {
						$evalValue = substr($defaultValue,5).";";
						eval('$rowValue='.$evalValue);
					}
					else {
						if(!isset($row[$wi400Column->getKey()])) {
							$rowValue = $defaultValue;
						}
					}
						
					echo utf8_encode("      ".$rowValue);					
				}
				else if(isset($row[$wi400Column->getKey()])) {
					echo utf8_encode("      ".$row[$columnKey]);
				}
			}
			
			echo "</option>";
		}
?>
			</select>
		</td>
		<td align="center">
			<table cellpadding="5">
				<tr>
					<td><input disabled type="image" src="<?=  $temaDir ?>images/grid/up_disabled.gif"
						id="ARROW_UP" title="Sposta su"
						onmousedown="continuosScrollUp = true;moveUpList()" onmouseup="continuosScrollUp = false" onmouseout="continuosScrollUp = false">
					</td>
				</tr>
				<tr>
					<td><input disabled type="image" src="<?=  $temaDir ?>images/grid/down_disabled.gif" title="Sposta già"
						id="ARROW_DOWN"
						onmousedown="continuosScrollDown = true;moveDownList()" onmouseup="continuosScrollDown = false" onmouseout="continuosScrollDown = false">
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<script>
	var columnsMap = new wi400Map();
<?
	foreach ($sortList->getSortColumns() as $columnKey) {
?>
		columnsMap.put("<?= $columnKey ?>",false);
<?
	}
?>
</script>
<?
		$inputHidden = new wi400InputHidden("IDLIST");
		$inputHidden->setValue($_REQUEST['IDLIST']);
		$inputHidden->dispose();
	
		$inputHidden = new wi400InputHidden("columnOrder");
		$inputHidden->setName("COLUMN_ORDER");
		$inputHidden->setValue(join("|", $colsList));
		$inputHidden->dispose();
		
		$myButton = new wi400InputButton("FILTER_ADD_BUTTON");
		$myButton->setAction("SORT_LIST");
		$myButton->setForm("SAVE");
		$myButton->setValidation(true);
		$myButton->setLabel("Salva");
		$buttonsBar[] = $myButton;
			
		$myButton = new wi400InputButton("FILTER_REMOVE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Annulla");
		$buttonsBar[] = $myButton;
	}