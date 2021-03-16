<?php
require_once $routine_path.'/classi/wi400List.cls.php';
require_once $routine_path.'/classi/wi400Column.cls.php';
require_once $routine_path.'/classi/wi400Filter.cls.php';


function listDispose(wi400List $wi400List, $cleanSelection=True, $showRow = array()){
	global $appBase,$db,$actionContext,$temaDir,$temaCommonDir,$data_path,$settings,$lookUpContext, $menuContext, $listCounter, $listsActionCounter, $jsAutoUpdate;
	
	$idList = $wi400List->getIdList();
	
	developer_add_system_var($actionContext->getAction()."|".$idList, "LIST");
	
	if (!isset($listCounter)) {
		$listCounter = 0;
		unset($_SESSION['TOP_SCROLL']);
	}
	$listCounter++;
	$wi400List->setIdNumList($listCounter);
	
	// Auto update della lista
	if (!isset($jsAutoUpdate)) {
		$jsAutoUpdate=False;
	}
	$disposeJS = False;
	if($wi400List->getAutoUpdateList() && $jsAutoUpdate==False) {
		$jsAutoUpdate=True;
		$disposeJS = True;
	}
	if($wi400List->getUpdateOnChangeRow()) $wi400List->setAutoFocus(false);
	
//	echo "FILTERS:<pre>"; print_r($wi400List->getFilters()); echo "</pre>";
	if($wi400List->getFilterUserWhere()===true || $wi400List->getForceUserWhere()===true) {
//		echo "FILTER_USER_WHEREbr>";
		
		$filter = new wi400Filter("USER_WHERE", "Filtro<br>personalizzato", "USER_WHERE");
		$wi400List->addFilter($filter);
			
//		echo "FILTERS:<pre>"; print_r($wi400List->getFilters()); echo "</pre>";
	}
	
	//	if (isset($_SESSION[$idList])){
	$save_conf = false;
	if (wi400Session::exist(wi400Session::$_TYPE_LIST, $idList)) {
		$save_conf = true;
		// Carico la lista dalla sessione
		//$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		// Pulisco le selezioni
		if ($cleanSelection) {
			$wi400List->setSelectionArray(array());
		}
	}else{
		
	    // CARICAMENTO PERSONALIZZAZIONE LISTA DA FILE
		
    	if (isset($idList) && $idList != ""){
    		// Se esiste una personalizzazione dell'utente carico quella
			$filename = wi400File::getUserFile("list", $wi400List->getConfigFileName().".lst");
			$wi400ListFile = wi400ConfigManager::readConfig("list", $wi400List->getConfigFileName(),"",$filename);
			
	    	//if (file_exists($filename)) {
			if ($wi400ListFile) {
	    		$save_conf = true;
	    		
				/*$handle = fopen($filename, "r");
				$contents = fread($handle, filesize($filename));
			    fclose($handle);
			    $wi400ListFile = unserialize($contents);*/
	    		//$wi400ListFile = wi400ConfigManager::readConfig("list", $wi400List->getConfigFileName(),"",$filename);
			   
			    //if ($wi400ListFile){
			    	if (!$wi400List->getManageOnlyNumRows()) {
				    	$wi400List->setPageRows($wi400ListFile->getPageRows());
				    	$wi400List->setMaxPageRows($wi400ListFile->getMaxPageRows());
				    	$wi400List->setColumnsOrder($wi400ListFile->getColumnsOrder());
				    	$wi400List->setColumnsFix($wi400ListFile->getColumnsFix());
				    	$wi400List->setDefaultFilter($wi400ListFile->getDefaultFilter());
				    	$wi400List->setShowHeadFilter($wi400ListFile->getShowHeadFilter());
				    	$wi400List->setBlockScrollHeader($wi400ListFile->getBlockScrollHeader());
				    	$wi400List->setStatus($wi400ListFile->getStatus());
			    	} else {
			    		$wi400List->setPageRows($wi400ListFile->getPageRows());
			    	}
			    //}
			}
			else {
				// Altrimenti carico la Configurazione Master di Default, se esiste 
				$filename = $settings['data_path']."list_master/MASTER_".$wi400List->getConfigFileName().".lst";				
				$wi400ListFile = wi400ConfigManager::readConfig("list_master", $wi400List->getConfigFileName(),"",$filename);
				if ($wi400ListFile) {
					$wi400List->setPageRows($wi400ListFile->getPageRows());
					$wi400List->setMaxPageRows($wi400ListFile->getMaxPageRows());
					$wi400List->setColumnsOrder($wi400ListFile->getColumnsOrder());
					$wi400List->setColumnsFix($wi400ListFile->getColumnsFix());
					$wi400List->setDefaultFilter($wi400ListFile->getDefaultFilter());
					
				}
				/*if (file_exists($filename)) {
					$handle = fopen($filename, "r");
					$contents = fread($handle, filesize($filename));
					fclose($handle);
					$wi400ListFile = unserialize($contents);
				
					if ($wi400ListFile){
						$wi400List->setPageRows($wi400ListFile->getPageRows());
						$wi400List->setMaxPageRows($wi400ListFile->getMaxPageRows());
						$wi400List->setColumnsOrder($wi400ListFile->getColumnsOrder());
						$wi400List->setColumnsFix($wi400ListFile->getColumnsFix());
						$wi400List->setDefaultFilter($wi400ListFile->getDefaultFilter());
					}
				}*/
			}
    	}
/*    	
		// CARICAMENTO FILTRO LISTA DA FILE
    	if (isset($idList) && $idList != ""){
    		
			$filename = wi400File::getUserFile("list", $wi400List->getConfigFileName().".flt");
	    	if (file_exists($filename)) {
				$handle = fopen($filename, "r");
				$contents = fread($handle, filesize($filename));
			    fclose($handle);
			    $wi400Filter = unserialize($contents);
			   
			    if ($wi400Filter){
			    	$wi400List->setCustomFilters($wi400Filter);
			    	if ($wi400List->getDefaultFilter() != ""){
			    		$wi400List->setCurrentFilter($wi400List->getDefaultFilter());
			    	}
			    }
			}
    	}
*/
    	// CARICAMENTO FILTRO LISTA DA FILE
    	if (isset($idList) && $idList != ""){
    		// FILTRI GENERICI
    		$filename = $settings['data_path']."filtri_master/MASTER_".$wi400List->getConfigFileName().".flt";
    		$wi400GenFilter = array();
    		$wi400_filter = wi400ConfigManager::readConfig("list_master_filter", $wi400List->getConfigFileName(),"",$filename);
    		if ($wi400_filter) {
    			if(!empty($wi400_filter)) {
    				foreach($wi400_filter as $key => $val) {
    					$wi400GenFilter["*".$key] = $val;
    				}
    				unset($wi400_filter);
    			}
    		}
    		/*if (file_exists($filename)) {
    			$handle = fopen($filename, "r");
    			$contents = fread($handle, filesize($filename));
    			fclose($handle);
    			$filters_gen = unserialize($contents);
    			if(!empty($filters_gen)) {
    				foreach($filters_gen as $key => $val) {
    					$wi400GenFilter["*".$key] = $val;
    				}
    				unset($filters_gen);
    			}
    		}*/
    	
    		// FILTRI UTENTE
    		$filename = wi400File::getUserFile("list", $wi400List->getConfigFileName().".flt");
    		$wi400UserFilter = array();
    		$wi400_filter = wi400ConfigManager::readConfig("list_filter", $wi400List->getConfigFileName(),"",$filename);
    		if ($wi400_filter && count($wi400_filter)>0) {
    			$wi400UserFilter = $wi400_filter;
    		}
    		/*if (file_exists($filename)) {
    			$handle = fopen($filename, "r");
    			$contents = fread($handle, filesize($filename));
    			fclose($handle);
    			$wi400UserFilter = unserialize($contents);
    		}*/
    			
			$wi400Filter = array_merge($wi400GenFilter, $wi400UserFilter);
    		if ($wi400Filter){
//    			echo "LIST_FILTERS:<pre>"; print_r($wi400List->getFilters()); echo "</pre>";
    			$wi400List->setCustomFilters($wi400Filter);
    			if ($wi400List->getDefaultFilter() != ""){
    				$wi400List->setCurrentFilter($wi400List->getDefaultFilter());
    			}
    		}
    	}
	}

	// Salvo in sessione la lista cosi' come configurata
	//$_SESSION[$idList] = $wi400List;
	wi400Session::save(wi400Session::$_TYPE_LIST, $idList, $wi400List);
	
	$tools = array();
	
	if($wi400List->getIsMapping()) {
//		$tools = array();
		foreach($wi400List->getTools() as $chiave => $tool) {
			$name = explode("/", $tool->getIco());
			$name = explode(".", array_pop($name));
			$name = $name[0];
//			echo "TOOL_NAME:$name<br>";
			
			if(checkFieldEnableOnDetail($actionContext->getAction(), $idList, $name, "L", "TOOL")) {
				$tools[] = $tool;
			}
		}
//		echo "TOOLS:<pre>"; print_r($tools); echo "</pre>";
		$wi400List->setTools($tools);
		
		$colonne = $wi400List->getCols();
		$sequenza_col = array();
		$filtri_fast = array();
		foreach($colonne as $idCol => $colonna) {
			$dat = checkFieldEnableOnDetail($actionContext->getAction(), $idList, $idCol, "L", "COLUMN");
			if(!$dat) {
				$wi400List->removeCol($idCol);
				unset($colonne[$idCol]);
			}else {
				// [0] => visibile o non [1] => default value
				if(isset($dat[1]) && $dat[1] !== null) {
					$descrizione = $dat[1];
					if (substr($descrizione,0,3)=="#F(") {
						//$descrizione = substituteFolderArray($descrizione, $row3);
						$descrizione = applicaFunzioni($descrizione);
					}
					$colonna->setDescription($descrizione);
				}
				// [2] => sequenza ordinamento
				if(isset($dat[2]) && $dat[2]) {
					$sequenza_col[$idCol] = $dat[2];
				}
				// [3] => Filtro veloce
				if(isset($dat[3]) && $dat[3]) { 
					$filtri_fast[$idCol] = $colonna->getDescription();
				}
			}
			
			if(isset($dat[0]) && is_string($dat[0]) && $dat[0] == "hide" && !$save_conf) {
				$colonna->setShow(false);
			}
		}
		
		//Ordino le colonne tenendo conto della sequenza
		if($sequenza_col && !$save_conf) {
			asort($sequenza_col);
			foreach ($sequenza_col as $idCol => $seq) {
				$col = $colonne[$idCol];
				unset($colonne[$idCol]);
				$colonne[$idCol] = $col;
			}
			
			$wi400List->setCols($colonne);
		}
		
		$actions = array();
		foreach($wi400List->getActions() as $action) {
			$label = $action->getLabel();
			if(checkFieldEnableOnDetail($actionContext->getAction(), $idList, $label, "L", "ACTION")) {
				$actions[] = $action;
			}
		}
		$wi400List->setAction($actions);
		
		//showArray($filtri_fast);
		if(!empty($filtri_fast)) {
			//showArray($filtri_fast);
			$filterAvanz = array();
			foreach($wi400List->getFilters() as $filter) {
				if(!$filter->getFast()) {
					$filterAvanz[$filter->getId()] = $filter;
				}
			}
			
			foreach($filtri_fast as $idFiltro => $labelFiltro) {
				$mioFiltro = new wi400Filter($idFiltro, $labelFiltro);
				$mioFiltro->setFast(true);
				$mioFiltro->setIdList($wi400List->getIdList());
				$filtri_fast[$idFiltro] = $mioFiltro;
			}
			
			$wi400List->setFilters(array_merge($filtri_fast, $filterAvanz));
		}
	}
	else {
		$tools = $wi400List->getTools();
	}
	
	// Aggiunta Tool di Import se previsto sul subfile
	if ($wi400List->getCanImport()==True) {
		// Verifico in ogni caso che ci sia il subfile
		if ($wi400List->getSubfile() != null){
			$tool = new wi400ListAction();
			$tool->setScript("openWindow(_APP_BASE + APP_SCRIPT + '?DECORATION=lookUp&t=IMPORT_INTO_SUBFILE&IDLIST=$idList', 'Scanner', 600, 450)");
			$tool->setIco($temaDir."images/grid/scanner.gif");
			$tool->setLabel("Importazione");
//			$wi400List->addTool($tool);
			$tools['IMPORT'] = $tool;
		} else {
			developer_debug("Import subfile non ammesso su liste senza subfile");
		}
	}
	
	$wi400List->setTools($tools);
//	echo "TOOLS:<pre>"; print_r($tools); echo "</pre>";

	$num_to_show = $wi400List->getPageRows();
	$selection = $wi400List->getSelection();
	
	$offset = 0;
	$firstPage = "_disabled";
	$prevPage = "_disabled";
	$nextPage = "_disabled";
	$lastPage = "_disabled";
	
	$titleList = "Lista";
	if ($wi400List->getTitle() != ""){
		$titleList = "".$wi400List->getTitle();
	}
	
	if (sizeof($showRow)>0){
		$_SESSION[$idList."_SHOW_ROW"] = $showRow;
	}
	
	// Presenza di colonne fixed
	$hasFixedCol = false;
	$fixedColSpan = "";
	$fixWidth = 0;
	$columnFixed = array();
	$columnsOrder = array();
	// Sempre 2
	$fixedColSpan = 'colspan="2"';
	foreach ($wi400List->getCols() as $columnObj) {
		if (method_exists($columnObj,"isFixed") && $columnObj->isFixed()){
			$hasFixedCol = true;
			$fixedColSpan = 'colspan="2"';
			$colWidth = $columnObj->getWidth();
			if ($colWidth == "" || $colWidth === 0) $colWidth = 150;
			
			$fixWidth = $fixWidth + intval($colWidth);
			
			$columnFixed[] = $columnObj->getKey();
		}
	}
	
	$wi400List->setColumnsFix($columnFixed);

	// LZ Gestione Filtri in testata
	if ($wi400List->getShowHeadFilter()==True) {
		$filtri = new wi400Iframe($idList."_GRIDSRCH_HEADER");
		$filtri->setAutoResize(true);
		$filtri->setUrl($appBase."index.php?t=GRIDSRCH&IDLIST=$idList&DECORATION=lookup&&LOOKUP_PARENT=lookup0&WAIT_LOADING=true");
		$filtri->dispose();
	}
	
	wi400Session::save(wi400Session::$_TYPE_LIST, $idList, $wi400List);
	
?>
<div onClick="openDetail('<?= $idList ?>')" id="<?= $idList ?>_opener"  style="display:none;"><div class="detail-header-cell">+ <?= $titleList ?></div></div>
<div id="duplicate_navigation_bar"></div>
<?

	// In caso di subfile l'aggiornamento produce la rigenerazione del subfile stesso
	$reloadJsAction = "_PAGE_RELOAD";
	$progressBarJsAction = "";
	 
	if ($wi400List->getSubfile() != null){
		$reloadJsAction = "_PAGE_REGENERATE";
	}
	 
	if ($wi400List->getProgressBar() != ""){
		$progressBarJsAction = "openProgressBar('".$wi400List->getId()."')";
	}
	if(isset($_SESSION['TOP_SCROLL']) && $_SESSION['TOP_SCROLL'])	$wi400List->setShowTopScroll(false);
	
	// FILTRI
	$filterCounter = 0;
	$hasFilters = false;
	$hasFastFilters = false;
	$filterKeys = array();
	$filterValues = array();
	$filterOptions = array();
	$filterCases = array();
	foreach($wi400List->getFilters() as $filter){
		if ($filter->getFast()){
			//echo "FAST: ".$fiter->getId()."<br>";
			$fastFilter = $filter;
			$filterKeys[$filter->getId()] = $filter->getDescription();
			$filterValues[$filter->getId()] = $filter->getValue();
			$filterOptions[$filter->getId()] = $filter->getOption();
			$filterCases[$filter->getId()] = $filter->getCaseSensitive();
			$hasFastFilters = true;
		}else{
//			echo "FILTER:".$filter->getId()."<br>";
			$hasFilters = true;
			$filterCounter++;
		}
	}
	
	
	if(isset($_SESSION['NAVIGAZIONE_TABLET_ATTIVA']) && $_SESSION['NAVIGAZIONE_TABLET_ATTIVA'] && $wi400List->getShowMenu()) {
		echo '<link rel="stylesheet" type="text/css" href="themes/common/css/button.css"  media="screen">';
		
		$actions = $wi400List->getActions();
		
		echo '<div style="position: relative; width: 100%; height: auto; /*background: yellow;*/">';
		if ($wi400List->getCanReload() && $wi400List->getTimer() === 0){
			$button = new wi400InputButton($idList."_RELOAD_TABLET");
			$button->setLabel(_t("RICARICA"));
			$button->setStyleClass("ccq-button-active tools");
			$button->setOnClick("doPagination(\"$idList\",$reloadJsAction);");
			$button->dispose();
			
		} 
		if ($hasFilters && $wi400List->getCanFilter()){
			$button = new wi400InputButton($idList."_SEARCH_IMG_TABLET");
			$button->setLabel(_t("FILTRI_AVANZATI"));
			$button->setStyleClass("ccq-button-active tools");
			$button->setOnClick("showSearch(\"$idList\",\"GRIDSRCH\",$filterCounter);");
			$button->dispose();
		}
		if ($wi400List->getTree()!="") {
			$button = new wi400InputButton($idList."_TREE_TABLET");
			$button->setLabel("TREE");
			$button->setStyleClass("ccq-button-active tools");
			$button->setOnClick("showListTree(\"$idList\")");
			$button->dispose();
		}
		$exportJsAction="";
		
		if ($wi400List->getCanExport() !== false && $wi400List->getCanExport() !== "false"){
		 	if ($wi400List->getCanExport() === "RESUBMIT"){
			   	$exportJsAction = "doPagination(\"$idList\",\"EXPORT:EXPORTLIST\")";
			}else{
				$exportJsAction = "exportList(\"$idList\")";
			}
			
			$button = new wi400InputButton($idList."_EXPORT_TABLET");
			$button->setLabel(_t("ESPORTA_LISTA"));
			$button->setStyleClass("ccq-button-active tools");
			$button->setOnClick("$exportJsAction");
			$button->dispose();
	 	} 
		
		if($wi400List->getCanSQL()) {
			$button = new wi400InputButton($idList."_SQL_TABLET");
			$button->setLabel("Query SQL"); //preg_replace('/ /i', " ", "Query SQL", 1)
			$button->setStyleClass("ccq-button-active tools");
			$button->setOnClick("viewListSql(\"$idList\")");
			$button->dispose();
		}
	
		if ($wi400List->getCanManage()){
			$button = new wi400InputButton($idList."_CONF_TABLET");
			$button->setLabel(_t("CONFIGURA_LISTA"));
			//$button->setButtonStyle($styleButtonTablet."left: ".$left."px;");
			$button->setStyleClass("ccq-button-active tools");
			$button->setOnClick("manageList(\"$idList\")");
			$button->dispose();
			
			$button = new wi400InputButton($idList."_SORT_TABLET");
			$button->setLabel("Ordinamenti");
			$button->setStyleClass("ccq-button-active tools");
			$button->setOnClick("ordersList(\"$idList\")");
			$button->dispose();
		}
/*		
		// Aggiunta Tool di Import se previsto sul subfile
		if ($wi400List->getCanImport()==True) {
			// Verifico in ogni caso che ci sia il subfile
			if ($wi400List->getSubfile() != null){
				$button = new wi400InputButton($idList."_IMP_TABLET");
				$button->setLabel("Importazione");
				$button->setStyleClass("ccq-button-active tools");
				$button->setOnClick("openWindow(_APP_BASE + APP_SCRIPT + '?DECORATION=lookUp&t=IMPORT_INTO_SUBFILE&IDLIST=$idList', 'Scanner', 600, 450)");
				$button->dispose();
			}
			else {
				developer_debug("Import subfile non ammesso su liste senza subfile");
			}
		}
*/		
		if ($wi400List->getSelection() == "MULTIPLE"){
			$button = new wi400InputButton($idList."_SELECTION_IMG_TABLET");
			$button->setLabel("Uncheck");
			$button->setStyleClass("ccq-button-active tools");
			$button->setOnClick("removeListSelection(\"$idList\")");
			$button->dispose();
		}
		
		foreach ($wi400List->getTools() as $tool) {
			$button = new wi400InputButton($idList."_SELECTION_IMG_TABLET");
			if($tool->getLabel() == "Esportazione con barcode") {
				$button->setLabel("Esporta con barcode");
			}else {
				$button->setLabel($tool->getLabel());
			}
			$button->setStyleClass("ccq-button-active tools");
			$button->setOnClick(str_replace("'", '"', $tool->getScript()));
			$button->dispose();
		}

		$clear = true;
		if(count($actions) && in_array($wi400List->getShowTabletActions(), array("top", "both"))) {
			//echo "<div style='position: absolute; height: 50px; top: 55px; left: 0px; right: 0px; /*background: red;right: 0px;*/'>";
			foreach($wi400List->getActions() as $key => $obj) {
				if(!$obj->getShow()) continue; 
				$button = new wi400InputButton($idList."_ACTION_".$key);
				$button->setLabel($obj->getLabel());
				if($clear) { 
					$stile = "clear: left;";
					$clear = false;
					$button->setButtonStyle($stile);
				}
				$button->setStyleClass("ccq-button-active action");
				$button->setOnClick("doListAction(\"{$wi400List->getIdList()}\",\"".($obj->getId() ? $obj->getId() : ($key+1))."\");");
				$button->dispose();
			}
			//echo "</div>";
		}
		
		if($wi400List->getShowTabletNumPages()) {
			echo "<div style='clear: left; height: 50px; bottom: 5px; left: 0px; width: 265px;'>";
			
			$button = new wi400InputButton($idList."_FIRST_BUTTON");
			$button->setLabel("<<");
			$button->setStyleClass("ccq-button-active pages");
			$button->setOnClick("doPagination(\"$idList\", _PAGE_FIRST)");
			$button->dispose();
			
			$button = new wi400InputButton($idList."_PREV_BUTTON");
			$button->setLabel("<");
			$button->setStyleClass("ccq-button-active pages");
			$button->setOnClick("doPagination(\"$idList\", _PAGE_PREV)");
			$button->dispose();
			
			echo "<div id=\"".$idList."_PAGINATION\" style='position: relative; top: 0px; height: 50px; font-size: 16px; margin-top: 5px; float: left;'>
						<div onClick='goToPage(\"$idList\")' style='cursor: pointer; position: relative; height: 17px; top: 50%; margin-top: -8.5px; margin-right: 3px;'>
    						<font id=\"".$idList."_PAGINATION_LABEL\">0 / 0</font>
						</div>
					</div>";
			
			$button = new wi400InputButton($idList."_NEXT_BUTTON");
			$button->setLabel(">");
			$button->setStyleClass("ccq-button-active pages");
			$button->setOnClick("doPagination(\"$idList\", _PAGE_NEXT)");
			$button->dispose();
			
			$button = new wi400InputButton($idList."_LAST_BUTTON");
			$button->setLabel(">>");
			$button->setStyleClass("ccq-button-active pages");
			$button->setOnClick("doPagination(\"$idList\", _PAGE_LAST)");
			$button->dispose();
			echo "</div>";
		}
		
		//Siccome il contenitore ha height auto forzo l'altezza con un div
		echo "<div style='height: 1px; width: 1px; clear: left;'></div>";
		
		/*$styleButtonTablet = "position: absolute;
								bottom: 3px;
								width: 80px;
								height: 60px;
								font-size: 14px;
								border-radius: 8px;
								text-align: center;
								display: none;";
		$styleButtonTablet .= $buttonColor;
		$button = new wi400InputButton($idList."_TAB_BUTTON_BACK");
		$button->setLabel("<< tab");
		$button->setButtonStyle($styleButtonTablet."right: 102px;");
		//$button->setStyleClass("ccq-button-active");
		$button->setOnClick("tabList(\"prima\")");
		$button->dispose();
		
		$button = new wi400InputButton($idList."_TAB_BUTTON_NEXT");
		$button->setLabel("tab >>");
		$button->setButtonStyle($styleButtonTablet."right: 10px;");
		//$button->setStyleClass("ccq-button-active");
		$button->setOnClick("tabList(\"dopo\")");
		$button->dispose();*/
		echo '</div><br/>';
	}
?>

<div id="<?= $idList ?>_slider">
<input type="hidden" name="<?= $idList ?>_STATUS" ID="<?= $idList ?>_STATUS" value="<?=$wi400List->getStatus() ?>">
<input type="HIDDEN" value="<?= $idList ?>" name="IDLISTS[]">			
<table id="tableWidth" cellpadding="0" cellspacing="0" border="0" width="100%">
    <?php
    if ($wi400List->getShowTitle()) {
    ?>  
    <tr>
    <td class="detail-header-cell">
    	<label onClick="closeDetail('<?= $idList?>');">- <?= $titleList?></label>
    </td>
    </tr>
    <?php 
    }
    ?>
	<tr>
		<td <?= $fixedColSpan ?>">
			<table width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td align="left">
<?	
/*						
						if(($wi400List->getFilterUserWhere()===true && $hasFilters===true) || $wi400List->getForceUserWhere()===true) {
//							echo "USER_WHERE<br>";
							$filter = new wi400Filter("USER_WHERE", "Filtro<br>personalizzato", "USER_WHERE");
							$wi400List->addFilter($filter);
							
							wi400Session::save(wi400Session::$_TYPE_LIST, $idList, $wi400List);
								
							$hasFilters = true;
							$filterCounter++;
						}
//						echo "FILTERS:<pre>"; print_r($wi400List->getFilters()); echo "</pre>";
*/						
						if (sizeof($filterKeys)>1){
							/*$multyFilter  = new wi400Filter();
							$multyFilter->setIdList($idList);
							$multyFilter->setKey($filterKeys);
							$multyFilter->setValue($filterValues);
							$multyFilter->setOption($filterOptions);
							$multyFilter->setCaseSensitive($filterCases);
							$multyFilter->setFast(true);
							$multyFilter->dispose();*/

							$html = '<table cellpadding="0" cellspacing="0" class="wi400-grid-filter"><tr class="detail-row">';
							
							$fastSelected = "";
							$firstTime = true;
							foreach ($filterKeys as $key => $description){
								if ($filterValues[$key] != "" || $firstTime){
									$fastSelected = $key;
									//$this->setOption($filterOption[$key]);
									$firstTime = false;
								}
							}
							
							$html .= '<td  class="text-label"><select id="MULTY_FILTER_KEY" onChange="setMultyFilter(this, \''.$idList.'\')" class="select-field">';

							foreach ($filterKeys as $key => $description){
								$selectedFast = "";
								if ($fastSelected == $key){
									$selectedFast = "selected";
								}
								$html .= '<option value="'.$key.'" '.$selectedFast.'>'.$description.'</option>';
							}
							$html .= '</select></td><td><table>';
							
							foreach ($wi400List->getFilters() as $filter) {
								if($filter->getFast()) {
									$filter->setgetMultiFast(true);
									if($filter->getId() == $fastSelected) {
										$styleTr = "style='display: block;'";
									}else {
										$styleTr = "style='display: none;'";
									}
									$html .= "<tr id='TABLE_TR_".$filter->getId()."' class='multi_filterFast_display' ".$styleTr.">".$filter->getHtml()."</tr>";
								}
							}
							$html .= "</table></td></tr></table>";
							echo $html;
						}else if ($hasFastFilters){
							$fastFilter->dispose();
						}
?>
					</td>
<?

if ($wi400List->getIncludePhp("INCLUDE_PHP")){
?>
					<td align="left">
						<table cellpadding="0" cellspacing="0" class="wi400-grid-filter">
							<tr class="detail-row">
								<td>
<?
	require $wi400List->getIncludePhp("INCLUDE_PHP");
?>								</td>
							</tr>
						</table>
					</td>
<?
}
if (sizeof($wi400List->getCustomFilters())>0){
?>					
					<td align="right" style="vertical-align: bottom;">
						<table cellpadding="0" cellspacing="0" class="wi400-grid-filter">
							<tr class="detail-row">
								<td>&nbsp;&nbsp;
<?
	$mySelect = new wi400InputSelect($idList."_CUSTOM_FILTER");
	$mySelect->setFirstLabel(_t("APPLICA_FILTRO"));
	$mySelect->setOnChange("doPagination('".$idList."', _PAGE_FIRST)");
	$mySelect->setStyleClass("select-field");
	foreach ($wi400List->getCustomFilters() as $key => $value){
		$mySelect->addOption($key);
	}
	$mySelect->setValue($wi400List->getCurrentFilter());
	$mySelect->dispose();
?>								&nbsp;&nbsp;</td>
							</tr>
						</table>
					</td>
<?
}
?>

				</tr>
			</table>
		</td>
	</tr>
	<tr>
<?
	//if ($hasFixedCol){
?>		
		<td id="TD_<?= $idList ?>_fixedContainer"width="<?= $fixWidth ?>" valign="top" class="work-area" style="border-right:none;background-color:#ffffff; overflow: hidden; width: 1px; display: none;">
			<div id="<?= $idList ?>_fixedContainer" class="fixedContainer" style="width: 100%;"></div>
		</td>
<?//}?>
		<td <? if (isIE()) echo 'width="100%"' ?>>
<script>
<?
	// Campi per lookup
	if (sizeof($lookUpContext->getFields())> 0){
		$lookUpFields = array();
		foreach ($lookUpContext->getFields() as $lookUpField){
			$lookUpFields[] = "'".$lookUpField."'";
		}
		echo "var ".$idList."_LOOKUP_FIELDS = new Array(".join(",",$lookUpFields).");";
		?>
		// Gestione ENTER su LOOKUP per tornare indietro il dato senza usare il mouse
		shortcut.add("ENTER",function(e) {
				var code = e.keyCode || e.which;
	 			if(code == 13) {
					eval(jQuery("#<?= $idList ?>"+"-"+window["<?= $idList ?>"+"_CR"]+"-tr").attr('onclick'));
	 			}
			});
		<?
	}
?>

	var <?= $idList ?>_parameters = new Array();
	var <?= $idList ?>_pc = 0;
<?
	foreach($wi400List->getParameters() as $parameterKey => $parameterValue) {?>
	<?= $idList ?>_parameters[<?= $idList ?>_pc++] = "<?= $parameterKey ?>";
<? 
	}

	$actionsList = $wi400List->getActions();
	// Azioni di lista
	if (sizeof($actionsList)>0){
		$actionCounter = 1;
		foreach ($actionsList as $action){
			$actionListId = $actionCounter;
			if ($action->getId() != "") $actionListId = $action->getId();
			// ***********************************
			// Aggiungo azione al menu contestuale
			// ***********************************
	    	$listAction = new wi400ListAction();
	    	$listAction->setLabel($action->getLabel());
	    	$listAction->setScript("doListAction('".$idList."','".$actionListId."')");
	    	$listAction->setIco($temaDir."images/next_disabled.gif");
	    	$listAction->setIdList($idList);
	    	$listAction->setShow($action->getShow());
    		$menuContext->addAction($listAction, "LIST");
	    	// ***********************************
	    	$closeFunction = $action->getCloseFunction();
	    	if($closeFunction != "undefined") {
				$closeFunction = "'".addslashes($action->getCloseFunction())."'";
			}
?>
			var <?= $idList ?>_AL_<?= $actionListId ?> = new wi400Map();
			<?= $idList ?>_AL_<?= $actionListId ?>.put('action','<?= addslashes($action->getAction()) ?>');
	        <?= $idList ?>_AL_<?= $actionListId ?>.put('form','<?= $action->getForm() ?>');
	        <?= $idList ?>_AL_<?= $actionListId ?>.put('gateway','<?= $action->getGateway() ?>');
	        <?= $idList ?>_AL_<?= $actionListId ?>.put('validation', <?= $action->getValidation() ?>);
	        <?= $idList ?>_AL_<?= $actionListId ?>.put('confirmMessage','<?= addslashes($action->getConfirmMessage()) ?>');
	        <?= $idList ?>_AL_<?= $actionListId ?>.put('selection','<?= $action->getSelection() ?>');
	        <?= $idList ?>_AL_<?= $actionListId ?>.put('script','<?= addslashes($action->getScript()) ?>');
	        <?= $idList ?>_AL_<?= $actionListId ?>.put('parameters','<?= addslashes($action->getUrlParameters()) ?>');
	        <?= $idList ?>_AL_<?= $actionListId ?>.put('target','<?= $action->getTarget() ?>');
	        <?= $idList ?>_AL_<?= $actionListId ?>.put('canClose', <?= $action->getCanClose() ?>);
	        <?= $idList ?>_AL_<?= $actionListId ?>.put('closeFunction', <?= $closeFunction ?>);
	        <?= $idList ?>_AL_<?= $actionListId ?>.put('width','<?= $action->getWidth() ?>');
	        <?= $idList ?>_AL_<?= $actionListId ?>.put('height','<?= $action->getHeight() ?>');
	        <?= $idList ?>_AL_<?= $actionListId ?>.put('serialize','<?= $action->getSerialize() ?>');
	        <?= $idList ?>_AL_<?= $actionListId ?>.put('modale', <?= $action->getModale() ?>);
<?
			$actionCounter++;
		}
	}//End actionlist 
	
	if ($wi400List->getDetailAjax() != ""){
		// Ajax action detail
?>		
		var <?= $idList ?>_DETAIL_AJAX = new wi400Map();
		<?= $idList ?>_DETAIL_AJAX.put('action','<?= addslashes($wi400List->getDetailAjax()->getAction()) ?>');
		<?= $idList ?>_DETAIL_AJAX.put('form','<?= $wi400List->getDetailAjax()->getForm() ?>');
<?
	}
	
?>
</script>
<?
			foreach($wi400List->getParameters() as $parameterKey => $parameterValue) {?><input type="hidden" name="<?= $parameterKey ?>" id="<?= $parameterKey ?>" value="<?= $parameterValue ?>"><?}	

			// ************************************************************************
			// Dimensione Header
			// ************************************************************************
			$rowHeaderHeight = $wi400List->getRowHeaderHeight();
			$extraHeight 	 = 0;

			if ($wi400List->getRowHeaderHeight() <= $wi400List->getRowHeight()) {

				$hasBr = false;
				if ($wi400List->getRowHeaderHeight() <= $wi400List->getRowHeight()) {
					// Presenza di <br>
					foreach ($wi400List->getCols() as $columnObj) {
						if ($columnObj->getShow() && strpos($columnObj->getDescription(),"<br>") > 0){
							$rowHeaderHeight = $rowHeaderHeight + ($wi400List->getRowHeight()/2);
							$hasBr = true;
							break;
						}
					}
				}
				
				// ************************************************************************
				// Raggruppamenti e  <br>
				// ************************************************************************
				$hasGroupBr = false;
				$hasGroup = false;
				foreach ($wi400List->getColumnsOrder() as $columnKey) {
					$wi400Column = $wi400List->getCol($columnKey);
					if (is_object($wi400Column)) {
						if ($wi400Column->getGroup() != ""){
							$hasGroup = true;
							if (strpos($wi400List->getGroupDescription($wi400Column->getGroup()),"<br>") > 0){
								$hasGroupBr = true;
								break;
							}
						}
					}
				}
				
				if ($hasGroup) $rowHeaderHeight = $rowHeaderHeight + $wi400List->getRowHeight();
				if ($hasGroupBr) $rowHeaderHeight = $rowHeaderHeight + ($wi400List->getRowHeight()/2);
			}
			
			// Totali di lista
			if (sizeof($wi400List->getTotals())>0){
				$extraHeight = $extraHeight + $wi400List->getRowHeight();
			}
				
			// Subfile
			if ($wi400List->getSubfile() != null){
				$wi400Subfile = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $wi400List->getSubfile());
				// totali subfile
				if ($wi400Subfile->getTotals()){
					$extraHeight = $extraHeight + $wi400List->getRowHeight();
				}
				// Righe extra
				$extraRows = $wi400Subfile->getExtraRows();
				$extraHeight = $extraHeight + ($wi400List->getRowHeight() * sizeof($extraRows));
			}
			
			
			// Replico field di passaggio del lookup
			if (sizeof($lookUpContext->getFields())> 0){ 
				$lookupId = $_REQUEST["LOOKUP"];
	?>
				<input type="HIDDEN" value="<?= $lookupId ?>" name="LOOKUP">
				<input type="HIDDEN" value="<?= $_REQUEST[$lookupId."_FIELDS"] ?>" name="<?= $lookupId ?>_FIELDS">
	<?
				if (isset($_REQUEST["FROM_LIST"])){
	?>
					<input type="HIDDEN" value="<?= $_REQUEST["FROM_LIST"] ?>" name="FROM_LIST" id="FROM_LIST">
					<input type="HIDDEN" value="<?= $_REQUEST["FROM_ROW"] ?>" name="FROM_ROW" id="FROM_ROW">
	<?					
				}
		
			}
			
			$listCssHeight = (($num_to_show)*$wi400List->getRowHeight()) + $rowHeaderHeight + $extraHeight + 19;
			
			//$listCss = "height:".$listCssHeight."px;overflow: scroll;";
			$listCss = "overflow: scroll;";
			if ($wi400List->getAutoScroll()) $listCss = "overflow: auto;";
			if ($num_to_show === 9999){
				$listCss = "";
			}
			if ($wi400List->getHideHeader()==True) {
				$listCss = "overflow: hidden;";
			}
			if ($wi400List->getBoxStyle()!="") {
				$listCss.=$wi400List->getBoxStyle();
			}	
			// Gestione CAMPO CURRENT_IDLIST per situazioni video con più di una lista, di DEFAULT PRIMA LISTA
			if ($listCounter==1) {
			?>
			<input type="HIDDEN" value="<?= $idList ?>" id="CURRENT_IDLIST" name="CURRENT_IDLIST">
			<?
			}
	?>
			<input type="HIDDEN" value="<?= $idList ?>" name="IDLIST">
			<input type="HIDDEN" name="<?= $wi400List->getIdList() ?>" id="<?= $wi400List->getIdList() ?>" value="">
			<div id="<?= $idList ?>Scroll" style="width: 100%;<?= $listCss ?>" class="work-area <?= $wi400List->getShowTopScroll() ? "double-scroll" : ""?> selectableInputRowList">

				<!-- <div id="<?= $idList ?>Container" style="width: 1px;float:left;"></div> -->
				
				<div id="<?= $idList ?>Container" style="width: 100%;float:left;">
<?						if($wi400List->getImmediateLoad()==True) {  
							wi400Session::save(wi400Session::$_TYPE_LIST, $idList, $wi400List);
							$dati = wi400List::doPagination($idList);
							echo $dati;
						}
?>				
<?						if(!$wi400List->getProgressBar() && $wi400List->getSilentLoad()!=True) {  ?>
							<div style="position: relative; width: 100%; height: 300px;">
								<img src="<?=$temaDir?>images/loading.gif" style="position: absolute; width: 40px; height: 40px; top: 50%; left: 50%; margin-left: -20px; margin-top: -20px;">
							</div>
<?						}	?>
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td <?= $fixedColSpan ?>>
			<table id="navigation_bar" width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr>
<?
			    
				if ($wi400List->getShowMenu() && !isset($_SESSION['NAVIGAZIONE_TABLET_ATTIVA'])) {
					// Aggiunta comandi paginazione al menu
					$listAction = new wi400ListAction();
				    $listAction->setLabel(_t("NEXT_PAGE"));
				    $listAction->setScript("doPagination('".$idList."',_PAGE_NEXT)");
				    $listAction->setIco($temaDir."images/grid/next.gif");
				    $listAction->setIdList($idList);
				    $menuContext->addAction($listAction, "LIST");
				    
					$listAction = new wi400ListAction();
				    $listAction->setLabel(_t("PREV_PAGE"));
				    $listAction->setScript("doPagination('".$idList."',_PAGE_PREV)");
				    $listAction->setIco($temaDir."images/grid/prev.gif");
				    $listAction->setIdList($idList);
				    $menuContext->addAction($listAction, "LIST");
				    
				    $listAction = new wi400ListAction();
				    $listAction->setLabel("Vai a pagina");
 				    $listAction->setScript("goToPage('".$idList."')");
				    $listAction->setIco($temaDir."images/grid/last.gif");
				    $listAction->setIdList($idList);
				    $menuContext->addAction($listAction, "LIST");
				    
				    $listAction = new wi400ListAction();
				    $listAction->setLabel("Attiva/Disattiva selezione righe");
				    $listAction->setScript("attivaCopiaDatiRiga('".$idList."')");
				    $listAction->setIco("themes/common/images/table-select-row.png");
				    $listAction->setIdList($idList);
				    $menuContext->addAction($listAction, "LIST");
				    
				    $listAction = new wi400ListAction();
				    $listAction->setLabel("Copia dati selezione");
				    $listAction->setScript("copiaDatiSelezionati('".$idList."')");
				    $listAction->setIco("themes/common/images/copy.png");
				    $listAction->setIdList($idList);
				    $menuContext->addAction($listAction, "LIST");
				    
				    $listAction = new wi400ListAction();
				    $listAction->setLabel("Incolla dati selezione");
				    $listAction->setScript("incollaDatiSelezionati('".$idList."')");
				    $listAction->setIco("themes/common/images/convert.png");
				    $listAction->setIdList($idList);
				    $menuContext->addAction($listAction, "LIST");
				    
					if ($wi400List->getCanReload()){
						$listAction = new wi400ListAction();
				    	$listAction->setLabel(_t("AGGIORNA_LISTA"));
				    	$listAction->setScript("doPagination('".$idList."',".$reloadJsAction.");".$progressBarJsAction);
				    	$listAction->setIco($temaDir."images/grid/reload.gif");
					    $listAction->setIdList($idList);
					    $menuContext->addAction($listAction, "LIST");
					}
			    	
			    	if ($wi400List->getCanExport() !== false && $wi400List->getCanExport() !== "false"){
						$listAction = new wi400ListAction();
					    $listAction->setLabel(_t("ESPORTA_LISTA"));
					    
					    
					    if ($wi400List->getCanExport() === "RESUBMIT"){
					    	$listAction->setScript("doPagination('".$idList."','EXPORT:EXPORTLIST')");
					    }else{
							$listAction->setScript(" exportList('$idList')");
					    }
					    $listAction->setIco($temaDir."images/grid/export.gif");
					    $listAction->setIdList($idList);
    					$menuContext->addAction($listAction, "LIST");
			    	}
			    	
			    	if(isset($_SESSION ["WI400_GROUPS"]) && in_array("VIEW_SQL", $_SESSION ["WI400_GROUPS"])) {
						$listAction = new wi400ListAction();
						$listAction->setLabel("Query SQL");
						$listAction->setScript("viewListSql('".$idList."')");
//						$listAction->setAction("LOG_ERROR");
//						$listAction->setForm("DEFAULT");
//						$listAction->setTarget("WINDOW");
						$listAction->setIco($temaCommonDir."images/sql.png");
						$listAction->setIdList($idList);
						$menuContext->addAction($listAction, "LIST");
					}
			    	
			    	if ($wi400List->getCanManage()){
					    $listAction = new wi400ListAction();
					    $listAction->setLabel(_t("CONFIGURA_LISTA"));
					    $listAction->setScript("manageList('".$idList."')");
					    $listAction->setIco($temaDir."images/grid/config.gif");
					    $listAction->setIdList($idList);
	    				$menuContext->addAction($listAction, "LIST");
			    	}
/*			    	
			    	// Aggiunta Tool di Import se previsto sul subfile
			    	if ($wi400List->getCanImport()==True) {
			    		// Verifico in ogni caso che ci sia il subfile
			    		if ($wi400List->getSubfile() != null){
							$listAction = new wi400ListAction();
							$listAction->setLabel("Importazione");
							$listAction->setScript("openWindow(_APP_BASE + APP_SCRIPT + '?DECORATION=lookUp&t=IMPORT_INTO_SUBFILE&IDLIST=$idList', 'Scanner', 600, 450)");
							$listAction->setIco($temaDir."images/grid/scanner.gif");
							$listAction->setIdList($idList);
							$menuContext->addAction($listAction, "LIST");
			    		}
			    		else {
			    			developer_debug("Import subfile non ammesso su liste senza subfile");
			    		}
			    	}
*/			    	
?>
					<td align="left">
					<table class="wi400-grid-menu">
						<tr>
							<td><input class="wi400-pointer" type="image" src="themes/common/images/grid/collapse.gif" onClick="closeDetail('<?= $idList ?>');" title="<?=_t('HIDE')?>"/></td>
<? if ($wi400List->getCanReload() && $wi400List->getTimer() === 0){?>
							<td>
							<input class="wi400-pointer" type="image" src="<?=  $temaDir ?>images/grid/reload.gif" 
								id="<?= $idList ?>_RELOAD" title="<? echo _t("RICARICA")?>"
								onClick="doPagination('<?= $idList ?>',<?= $reloadJsAction ?>);<?= $progressBarJsAction?>"></td>
<? }
	if ($wi400List->getTimer() > 0){
?>
		<td><input id="<?= $idList ?>_TIMER_IMG" class="wi400-pointer" type="image" title="<? echo _t("TIMER_LISTA")?> (<?= $wi400List->getTimer()?> sec.)" onClick="timerPause('<?= $idList ?>', <?= $reloadJsAction?>)" src="themes/common/images/grid/grid_timer.gif"></td>
<?
	} 
			if ($hasFilters && $wi400List->getCanFilter()){ 
				$listAction = new wi400ListAction();
		    	$listAction->setLabel(_t("FILTRI_LISTA"));
		    	$listAction->setIco($temaDir."images/grid/search.gif");
		    	$listAction->setScript("showSearch('".$idList."','GRIDSRCH')");
		    	$listAction->setIdList($idList);
   				$menuContext->addAction($listAction, "LIST");
?>
							<td>
							<input class="wi400-pointer" type="image" src="<?=  $temaDir ?>images/grid/search_disabled.gif" 
								id="<?= $idList ?>_SEARCH_IMG" title="<? echo _t("FILTRI_AVANZATI")?>"
								onClick="showSearch('<?= $idList ?>','GRIDSRCH',<?= $filterCounter ?>)"></td>
			<? } ?>
			
<?
	if ($wi400List->getTree()!="") {
?>
		<td><input class="wi400-pointer" type="image" src="<?=  $temaDir ?>images/tree.gif" id="<?= $idList ?>_TREE" title="" onClick="showListTree('<?= $idList ?>')"></td>
<?
	}
	$exportJsAction="";
	
	if ($wi400List->getCanExport() !== false && $wi400List->getCanExport() !== "false"){
	 	if ($wi400List->getCanExport() === "RESUBMIT"){
		   	$exportJsAction = "doPagination('".$idList."','EXPORT:EXPORTLIST')";
		}else{
			$exportJsAction = "exportList('$idList')";
		}
?>
		<td><input class="wi400-pointer" type="image" title="<? echo _t("ESPORTA_LISTA")?>" onClick="<?=$exportJsAction?>" src="<?=  $temaDir ?>images/grid/export.gif"></td>
<?  } 

	if($wi400List->getCanSQL()) {
?>
		<td><input class="wi400-pointer" type="image" title="<? echo "Query SQL"?>" onClick="viewListSql('<?= $idList ?>')" src="<?=  $temaCommonDir ?>images/sql.png"></td>
<?php
	}

if ($wi400List->getCanManage()){
?>
							<td><input class="wi400-pointer" type="image" title="<? echo _t("CONFIGURA_LISTA")?>" onClick="manageList('<?= $idList ?>')" src="<?=  $temaDir ?>images/grid/config.gif"></td>
							<td><input class="wi400-pointer" type="image" id="<?= $idList ?>_ORDER_IMG" title="<? echo _t("ORDINAMENTI_AVANZATI")?>" onClick="ordersList('<?= $idList ?>')" src="themes/common/images/grid/grid_order_disabled.gif"></td>
<? 
	}
/*	
	// Aggiunta Tool di Import se previsto sul subfile
	if ($wi400List->getCanImport()==True) {
		// Verifico in ogni caso che ci sia il subfile
		if ($wi400List->getSubfile() != null){
?>
							<td><input class="wi400-pointer" type="image" title="<? echo "Importazione"?>" onClick="openWindow(_APP_BASE + APP_SCRIPT + '?DECORATION=lookUp&t=IMPORT_INTO_SUBFILE&IDLIST=<?= $idList ?>', 'Scanner', 600, 450)" src="<?=  $temaDir ?>images/grid/scanner.gif"></td>
<? 			
		}
		else {
			developer_debug("Import subfile non ammesso su liste senza subfile");
		}
	}
*/	
	if ($wi400List->getSelection() == "MULTIPLE"){
?>
		<td><input id="<?= $idList ?>_SELECTION_IMG" class="wi400-pointer" type="image" title="<? echo _t("RIMUOVI_SELEZIONI")?>" onClick="removeListSelection('<?= $idList ?>')" src="themes/common/images/grid/selection_disabled.png"></td>
<?
	}
	$helpTool = $wi400List->getHelpTool();
	if($helpTool) {
?>
		<td><input id="<?= $idList ?>_HELP_IMG" class="wi400-pointer" type="image" title="Help Tool" onClick="viewHelpTool('<?=$helpTool[0]?>', '<?= $helpTool[1]?>', <?= $helpTool[2]?>, <?= $helpTool[3]?>)" src="themes/common/images/yav/info.png"> </td>
<?
	}
	
   foreach ($wi400List->getTools() as $tool){ ?>							
							<td><input class="wi400-pointer" type="image" title="<?= $tool->getLabel() ?>" onClick="<?= $tool->getScript() ?>" src="<?= $tool->getIco() ?>"></td>
<? } 
	if(isset($_SESSION['BUTTON_MAPPA_DETAIL'])) { ?>
		<td><input class="wi400-pointer" type="image" title="Mappa lista" onClick="doSubmit('ABILITAZIONI_CAMPI_DETAIL&MAP_LIST=<?= $idList ?>&TITLE_DETAIL=<?= $wi400List->getTitle()?>', 'MAP_DETAIL', false, false, '', true)" src="<?=  $appBase ?>themes/common/images/mapping.png"></td>
<?	}
?>
						</tr>
					</table>
					</td>
<? 
	} //Chiusura if tablet attivo
	
	if ($wi400List->getTimer() > 0){
		echo "<script> {$idList}_timer = true; {$idList}_timer_state = false;</script>";
	}
	
	if (sizeof($actionsList)>0 && $wi400List->getShowActions() && in_array($wi400List->getShowTabletActions(), array("bottom", "both"))){
?>
					<td align="center">
						<table class="wi400-grid-menu">
							<tr>
								<td class="text-label"><? echo _t("ESEGUI_AZIONE")?>:</td>
								<td><select class="select-field" id="<?= $idList ?>_actionSelector" size="1"
									onChange="changeListAction('<?= $idList ?>')">
									<option value=""><? echo _t("SELEZIONA")?></option>
<?
						$actionCounter = 1;
						if (!isset($listsActionCounter)) {
							$listsActionCounter=1;
						}	
						foreach ($actionsList as $action){ 
							if($action->getShow()===false) {
								$actionCounter++;
								continue;
							}
							$actionListId = $actionCounter;
							if ($action->getId() != "") $actionListId = $action->getId();
?>
									<option value="<?= $actionListId ?>"><?= $action->getLabel().' '.$action->getShortcutKeys() ?></option>
									<?php
									if ($action->getShortcutKeys()!="") {
									?>	 
									<script>
									setKeyScript('<?=$action->getShortcutKeys()?>', '<?= $idList ?>', '<?= $actionListId?>');
									</script>
									<?php 
									}
							$actionCounter++;
							$listsActionCounter++;
						}
						
?>
								</select></td>
								<td><input id="<?= $idList ?>_actionConfirm" type="image" onClick="doListAction('<?= $idList ?>')" disabled src="<?=  $temaDir ?>images/next_disabled.gif"></td>
							</tr>
						</table>
					</td>
<?			
	} 
?>
					<td align="right">
					<? if ($wi400List->getShowNumPages() && !isset($_SESSION['NAVIGAZIONE_TABLET_ATTIVA'])) { 
							if ($wi400List->getCallBackFunction("buttonChangePage")!=False) {
								if (is_callable($wi400List->getCallBackFunction("buttonChangePage"))) {
									$wi400List->setRunTimeField("callBack", "buttonChangePage");
									$wi400List = call_user_func($wi400List->getCallBackFunction("buttonChangePage"), $wi400List, $_REQUEST);
									$row=$wi400List->getCurrentRow();
								}else {
									echo '<table class="wi400-grid-menu">
											<tr>
												<td>';
													echo "call user func not valid ".$wi400List->getCallBackFunction("buttonChangePage");
									echo '		</td>
											</tr>
										</table>';
								}		
							}else {?>
								<table class="wi400-grid-menu">
									<tr>
										<td><input type="image" src="<?=  $temaDir ?>images/grid/first<?= $firstPage ?>.gif" 
											id="<?= $idList ?>_FIRST_BUTTON" alt="<? echo _t("PRIMA")?>" 
											onClick="doPagination('<?= $idList ?>',_PAGE_FIRST)"></td>
										<td><input type="image" src="<?=  $temaDir ?>images/grid/prev<?= $prevPage ?>.gif"
											id="<?= $idList ?>_PREV_BUTTON" alt="<? echo _t("PRECEDENTE")?>"
											onClick="doPagination('<?= $idList ?>',_PAGE_PREV)"></td>
										<td class="wi400-grid-menu-page">
											<div id="<?= $idList ?>_PAGINATION_LABEL" onClick="goToPage('<?= $idList ?>')" style="cursor: pointer;">0 / 0</div>
										</td>
										<td><input type="image" src="<?=  $temaDir ?>images/grid/next<?= $nextPage ?>.gif" alt="<? echo _t("SUCCESSIVA")?>"
											id="<?= $idList ?>_NEXT_BUTTON"
											onClick="doPagination('<?= $idList ?>',_PAGE_NEXT)"></td>
										<td><input type="image" src="<?=  $temaDir ?>images/grid/last<?= $lastPage ?>.gif" alt="<? echo _t("ULTIMA")?>"
											id="<?= $idList ?>_LAST_BUTTON"
											onClick="doPagination('<?= $idList ?>',_PAGE_LAST)"></td>
									</tr>
								</table>
						  <?}
					 	}?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td <?= $fixedColSpan ?>>
			<div class="wi400-grid-detail" id="<?= $idList ?>Detail"></div>
			<script>
				var paginationListKey = '<?= $idList ?>';
				var <?= $idList ?>_ST = "<?= $selection ?>";
				var <?= $idList ?>_SELECT = -1;
				var <?= $idList?>_ROWS = <?= $wi400List->getPageRows() ?>;
				<? if ($wi400List->isEditable()){?>EDITABLE_LIST_IN_PAGE = true;<?}?>
				<? if ($wi400List->getImmediateLoad()!=True){?>
				doPagination('<?= $idList ?>', _PAGE_RELOAD, undefined, undefined, undefined, <?= $wi400List->getScrollTop()?>);
				<?}?>
				var listCounterDisposed = <?= $listCounter ?>;
                // Creo l'array delle liste utilizzate sul form 
				if (typeof(arrayPaginationListKey) == "undefined"){
					var arrayPaginationListKey = new Array();
				}
				arrayPaginationListKey[listCounterDisposed]='<?= $idList ?>';
				var currentPaginationListKey="";

				/*jQuery(document).ready(function() {
					var contList = jQuery("#<?=$idList?>_slider");
					contList.focusin(function () {
						//console.log(this);
						var inIframe = window.frameElement;
						if(inIframe) {
							//console.log("iframe: "+inIframe.id);

							wi400SetCookie("iframe_<?=$idList?>", inIframe.id);
						}
						
						//console.log(window.frameElement.id);
					});
				});*/
			</script>
		</td>
	</tr>
</table>
</div>

<?php
	if($wi400List->getEnableMovingWithKeys()) {
		echo "<script>enableMovingWithKeys('".$wi400List->getIdList()."');</script>";
		echo '<input type="HIDDEN" value="" name="MOVINGWITHKEYS" id="MOVINGWITHKEYS">';
	}
?>

<div id="<?=$idList?>_dialog" style="display: none;">
	<?php
		wi400Session::save(wi400Session::$_TYPE_LIST, $idList, $wi400List);
	
		$ListDetail = new wi400Detail("LABEL_PAGE_DIALOG");
		$ListDetail->setColsNum(1);
		
		// Numero pagina
		$labelDetail = new wi400InputText($idList."_NUM_PAGE");
		$labelDetail->setLabel("Numero pagina: ");
		$labelDetail->setOnKeyDown("enterNumPageKey(event, '".$idList."')");
		$ListDetail->addField($labelDetail);
		
		$ListDetail->dispose();
	?>
</div>
<?
	if($wi400List->getShowTopScroll()) {
		$_SESSION['TOP_SCROLL'] = true;?>
		<script type="text/javascript" src="<?= $appBase?>routine/jquery/jquery.doubleScroll.js"></script>
		<script type="text/javascript">
			var doubleScroll;
			jQuery(document).ready(function(){
				doubleScroll = jQuery(".double-scroll").doubleScroll({useParent: true});
            });
		</script>
<?	}
	
	// Chiusura del Dettaglio se era chiuso e deve rimanere chiuso
	if (in_array($wi400List->getStatus(), array("CLOSE", "CLOSE_CONFIG"))) {
	?>
		<script>
			closeDetail('<?= $idList ?>');
		</script>
		<?
	}
	if ($disposeJS==True) {

		if($wi400List->getUpdateOnChangeRow()) {
			updateListRowJsChange($idList);
		}else {
			updateListRowJs();
		}
	}
	if ($wi400List->getTopNavigationBar()==True) {
		?>
		<script>
		jQuery("#navigation_bar").appendTo("#duplicate_navigation_bar");
		</script>
		<?
	}
}

?>
