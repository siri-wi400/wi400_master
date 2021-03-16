<?

if ($actionContext->getForm() == "SAVE"){?>
		<script>
			var idList = '<?php echo $_REQUEST['IDLIST']?>';
			//idList = idList.slice(0, -5);
			//var parentWindow = getParentObj(idList);
			var parentWindow = getFrameWindowById(idList);
			// Sempre l'URL della pagina perchè ne ho bisogno per i request
			// ???  parentWindow.location.href=parentWindow.location.href;
			
<?php 		if(isset($res_del) && $res_del)  {
				echo "closeWindow();";
			}else { ?>
				var callback = function() {
					closeLookUp();
				};
		    	parentWindow.doPagination('<?php echo $_REQUEST['IDLIST']?>', "RELOAD", undefined, undefined, callback);
<?php 		} ?>
		</script>
	<?
} else if ($actionContext->getForm() == "RESTORE"){
?>
	<script>
		var parentWindow = getFrameWindowById('<?= $_REQUEST['IDLIST']?>');
		// Sempre l'URL della pagina perchè ne ho bisogno per i request
		parentWindow.location.href=parentWindow.location.href+"&WI400_HMAC="+__WI400_HMAC;
	   // parentWindow.doPagination('<?php echo $_REQUEST['IDLIST']?>', "RELOAD");
		closeLookUp();
	</script>
<?			
}else if ($actionContext->getForm() == "DEFAULT"){
	//$wi400List = new wi400List();
	if (isset($_REQUEST['IDLIST'])){
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $_REQUEST['IDLIST']);
		
		$hide = $wi400List->getManageOnlyNumRows();
//		echo "HIDE: $hide<br>";
	}else{
		echo "ERRORE GRAVE";
		exit();
	}
if($hide===true) {
?>
<div style="display:none">
<?php
}

$col_id = false;
if(isset($_REQUEST['COL_ID']) && $_REQUEST['COL_ID']) {
	$col_id = true;
	echo "<input type='hidden' name='COL_ID' value='SI'/>";
}
	?>
<table width="100%" border="0">
	<tr>
		<td class="wi400-double-header"><?php echo _t("COLONNE_NASCOSTE")?></td>
		<td>&nbsp;</td>
		<td class="wi400-double-header"><?php echo _t("COLONNE_VISUALIZZATE")?></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><select size="10" id="double_list_LEFT" class="wi400-double-list"
			onChange="doubleListClick('LEFT')" multiple="multiple">
			<?
			foreach ($wi400List->getCols() as $wi400Column) {
				if (!$wi400Column->getShow()){
					$colGroup = "";
					if ($wi400Column->getGroup() != "") $colGroup = "(".$wi400Column->getGroup().")";
					$colKey = "";
					if($col_id) $colKey = " (".$wi400Column->getKey().")";
					?>
			<option value="<?= $wi400Column->getKey() ?>"><?= $wi400Column->getDescription().$colGroup.$colKey ?></option>
			<?
				}
			}
			?>
		</select></td>
		<td align="center" width="100%">
		<table cellpadding="5">
			<tr>
				<td><input type="image" src="<?=  $temaDir ?>images/grid/first.gif"
					id="REMOVE_ALL" title="<?php echo _t("RIMUOVI_TUTTE")?>"
					onClick="doubleListMove('RIGHT','LEFT',true)"></td>
			</tr>
			<tr>
				<td><input disabled type="image"
					src="<?=  $temaDir ?>images/grid/prev_disabled.gif" id="REMOVE"
					title="<?php echo _t("RIMUOVI")?>"
					onClick="doubleListMove('RIGHT','LEFT')"></td>
			</tr>
			<tr>
				<td><input disabled type="image"
					src="<?=  $temaDir ?>images/grid/next_disabled.gif"
					title="<?php echo _t("AGGIUNGI")?>" id="ADD"
					onClick="doubleListMove('LEFT','RIGHT')"></td>
			</tr>
			<tr>
				<td><input type="image" src="<?=  $temaDir ?>images/grid/last.gif"
					title="<?php echo _t("AGGIUNGI_TUTTE")?>" id="ADD_ALL"
					onClick="doubleListMove('LEFT','RIGHT',true)"></td>
			</tr>
		</table>
		</td>
		<td><select id="double_list_RIGHT" size="10" class="wi400-double-list"
			onChange="doubleListClick('RIGHT')" multiple="multiple">
			<?
			$colsList = array();
			foreach ($wi400List->getColumnsOrder() as $columnKey) {
				$wi400Column = $wi400List->getCol($columnKey);
				
				if ($wi400Column != null && $wi400Column->getShow()){
					$colGroup = "";
					if ($wi400Column->getGroup() != "") $colGroup = "(".$wi400List->getGroupDescription($wi400Column->getGroup()).")";
					$colKey = "";
					if($col_id) $colKey = " (".$wi400Column->getKey().")";
					$colsList[] = $wi400Column->getKey();
					$styleClass = "";
					if ((method_exists($wi400Column,"isRequired") && $wi400Column->isRequired()) || $wi400Column->getInput() != ""){
						$styleClass = "class=\"select-option-disabled\"";
					}else if (method_exists($wi400Column,"isFixed") && $wi400Column->isFixed()){
						$styleClass = "class=\"select-option-fixed\"";
					}
					?>
			<option value="<?= $wi400Column->getKey() ?>" <?= $styleClass ?>><?= str_replace("<br>"," ",$wi400Column->getDescription().$colGroup.$colKey) ?></option>
			<?
				}
			}
			?>
		</select></td>
		<td align="center">
		<table cellpadding="5">
			<!-- <tr>
				<td>
					<button id='SHOW_COL_ID' title="Mostra id delle colonne" onclick='window.location.href=window.location.href+"&COL_ID=SI";'>ID</button>
				</td>
			</tr>-->
			<tr>
				<td><input disabled type="image"
					src="<?=  $temaDir ?>images/grid/up_disabled.gif" id="ARROW_UP"
					title="<?php echo _t("SPOSTA_SU")?>"
					onmousedown="continuosScrollUp = true;moveUpList()"
					onmouseup="continuosScrollUp = false"
					onmouseout="continuosScrollUp = false"></td>
			</tr>
			<tr>
				<td><input disabled type="image"
					src="<?=  $temaDir ?>images/grid/down_disabled.gif"
					title="<?php echo _t("SPOSTA_GIU")?>" id="ARROW_DOWN"
					onmousedown="continuosScrollDown = true;moveDownList()"
					onmouseup="continuosScrollDown = false"
					onmouseout="continuosScrollDown = false"></td>
			</tr>
			<tr>
				<td><input disabled type="image"
					src="themes/common/images/grid/colfix_disabled.gif"
					title="Blocca colonna" id="COL_FIX"
					onClick="doubleListFix()"></td>
			</tr>
		</table>
		</td>
	</tr>
</table>
<br>
<script>
	var columnsMap = new wi400Map();
	var columnsFixMap = new wi400Map();
	var columnsInputMap = new wi400Map();
<?
$fixColsList = array();
$inputColsList = array();
foreach ($wi400List->getCols() as $wi400Column) {
	$blocked = "false";
	$fixed = "false";
	$input = "false";
	if ((method_exists($wi400Column,"isRequired") && $wi400Column->isRequired())){
		$blocked = "true";
	}
	
	if (method_exists($wi400Column,"isFixed") && $wi400Column->isFixed()){
		$fixed = "true";
		$fixColsList[] = $wi400Column->getKey();
	}
	
	if ($wi400Column->getInput() != ""){
		$input = "true";
		$inputColsList[] = $wi400Column->getKey();
	}
	
	
?>
	columnsFixMap.put("<?= $wi400Column->getKey() ?>",<?= $fixed ?>);
	columnsMap.put("<?= $wi400Column->getKey() ?>",<?= $blocked ?>);
	columnsInputMap.put("<?= $wi400Column->getKey() ?>",<?= $input ?>);
<?
}
?>

</script>
<input name="IDLIST" type="hidden" value="<?=  $_REQUEST['IDLIST'] ?>">
<input id="columnOrder" name="COLUMN_ORDER" type="hidden"
	   	value="<?= join("|",$colsList); ?>">
<input id="columnsFix" name="COLUMNS_FIX" type="hidden" 
		value="<?= join("|",$fixColsList); ?>">
<?
if($hide===true) {
?>
</div>
<?php
}

$azioniDetail = new wi400Detail("MANAGELIST");
$azioniDetail->setColsNum(1);

$myField = new wi400InputText('NUM_ROWS');
$myField->addValidation('required');
$myField->addValidation('numeric');
$myField->setInfo(_t('LIST_ROW_INFO'));
$myField->setLabel(_t("NUMERO_RIGHE"));
$myField->setSize(3);
$myField->setMaxLength(3);
$myField->setValue($wi400List->getPageRows());

// Può necessario limitare il numero massimo di righe per pagina per non superare il numero di variabili passabili in $_REQUEST,
// in quanto tutti i campi di inserimento nella lista vengono riportati nella $_REQUEST
if($wi400List->getMaxPageRows()>0)
	$myField->setOnChange("listCheckRange(this, 0, ".$wi400List->getMaxPageRows().")");

$azioniDetail->addField($myField);

if($hide===false) {
	if (sizeof($wi400List->getCustomFilters())>0){
		$mySelect = new wi400InputSelect('DEFAULT_FILTER');
		$mySelect->setInfo(_t('LIST_CUST_FILTER_INFO'));
		$mySelect->setLabel(_t("FILTRO_DEFAULT"));
		$mySelect->setFirstLabel(_t("NESSUN_FILTRO"));
	/*	
		// Controllo che l'utente sia abilitato a rimuovere il filtro se questo è generico
		$user_ab = false;
		if(isset($_SESSION["user_admin"]) && $_SESSION["user_admin"]==true) {
			$user_ab = true;
		}
	*/	foreach ($wi400List->getCustomFilters() as $key => $value){
	//		if($user_ab===false && substr($key, 0, 1)=="*")
	//			continue;
	
			$mySelect->addOption($key);
		}
		$mySelect->setValue($wi400List->getDefaultFilter());
		$azioniDetail->addField($mySelect);
		
	//	echo "ADMIN: ".$_SESSION["user_admin"]."<br>";
	
		$myField = new wi400InputCheckbox('DELETE_FILTER');
		$myField->setLabel(_t("ELIMINA_FILTRO"));
		$azioniDetail->addField($myField);
	}
	$myField = new wi400InputCheckbox('FILTRI_TESTATA');
	$myField->setLabel("Abilita Filtro Testata");
	$myField->setChecked($wi400List->getShowHeadFilter());
	$azioniDetail->addField($myField);
	$myField = new wi400InputCheckbox('BLOCK_HEADER_SCROLL');
	$myField->setLabel("Blocco Scroll testata");
	$myField->setChecked($wi400List->getBlockScrollHeader());
	$azioniDetail->addField($myField);
	$myField = new wi400InputCheckbox('NASCONDI_LISTA');
	$myField->setLabel("Nascondi lista");
	$myField->setChecked($wi400List->getStatus() == "CLOSE_CONFIG" ? true : false);
	$myField->setValue("CLOSE_CONFIG");
	$azioniDetail->addField($myField);
	// Configurazione Master di Default
	if(isset($_SESSION["user_admin"]) && $_SESSION["user_admin"]==true) {
		$myField = new wi400InputCheckbox('MASTER_CONFIG');
		$myField->setLabel("Configurazione Master di Default");
		$azioniDetail->addField($myField);
	
		$filename = $settings['data_path']."list_master/MASTER_".$wi400List->getConfigFileName().".lst";
		//if(file_exists($filename)) {
		$wi400ListFile= wi400ConfigManager::readConfig('list_master', $wi400List->getConfigFileName(), '',$filename);
		if ($wi400ListFile) {
			$myField = new wi400InputCheckbox('DELETE_MASTER_CONFIG');
			$myField->setLabel("Elimina Configurazione Master di Default");
			$azioniDetail->addField($myField);
		}
		
		$myField = new wi400InputCheckbox('DELETE_PERS_CONFIG');
		$myField->setLabel("Elimina Configurazioni Personalizzate");
		$azioniDetail->addField($myField);
	}
}

$azioniDetail->dispose();


	$myButton = new wi400InputButton("FILTER_ADD_BUTTON");
	$myButton->setAction("MANAGELIST");
	$myButton->setForm("SAVE");
	$myButton->addParameter("IDLIST", $_REQUEST['IDLIST']);
	$myButton->setValidation(true);
	$myButton->setLabel(_t("SALVA"));
	$buttonsBar[] = $myButton;

	$myButton = new wi400InputButton("FILTER_REMOVE_BUTTON");
	$myButton->addParameter("IDLIST", $_REQUEST['IDLIST']);
	$myButton->setScript('closeLookUp()');
	$myButton->setLabel(_t("ANNULLA"));
	$buttonsBar[] = $myButton;

	$myButton = new wi400InputButton("FILTER_RESTORE_BUTTON");
	$myButton->setConfirmMessage("Ripristinare le impostazioni iniziali della lista?");
	$myButton->addParameter("IDLIST", $_REQUEST['IDLIST']);
	$myButton->setAction("MANAGELIST");
	$myButton->setForm("RESTORE");
	$myButton->setLabel("Ripristina");
	$buttonsBar[] = $myButton;
	
	$myButton = new wi400InputButton("SHOW_COL_ID_BUTTON");
	$myButton->setScript('window.location.href=window.location.href+"&COL_ID=SI";');
	$myButton->setLabel("Mostra id");
	$buttonsBar[] = $myButton;
	
	}
		
?>