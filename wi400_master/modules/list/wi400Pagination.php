<?
	require_once $routine_path.'/classi/wi400List.cls.php';
//	require_once $routine_path.'/classi/wi400ListSql.cls.php';
	require_once $routine_path.'/classi/wi400Row.cls.php';
		
	global $temaDir;
	$canDrag = "";
	
	$hideHeader=False;
	if (isset($_GET['HIDE_HEADER']) && $_GET['HIDE_HEADER']==True) {
		$hideHeader=True;
	}
	//$wi400List = new wi400List();
	if (isset($_GET['IDLIST'])){
	//if (wi400Session::exist(wi400Session::$_TYPE_LIST, $_GET['IDLIST'])) {
		//$wi400List =  $_SESSION[$_GET['IDLIST']];
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $_GET['IDLIST']);
	}else{
		echo "ERRORE GRAVISSIMO:".$_GET['IDLIST'];
		error_log("Lista ".$_GET['IDLIST']. " non trovata!!");
		exit();
	}
	if ($wi400List->getIncludeFile() != ""){
		require_once $wi400List->getIncludeFile();
	}
	if ($wi400List->getHideHeader()==True) {
		$hideHeader=True;
	}
	$idList = $wi400List->getIdList();
	$idNumList = $wi400List->getIdNumList();
	if (isset($_GET['NOTHING_TO_DO'])) {
		die();
	}
	// Pulisco e inizio raccolta buffer
	if ($wi400List->getIsEnabledCaching()) {
	    ob_end_clean();
	    ob_start();
	    if ($wi400List->getIsCached()) {
	    	return $wi400List->getCachedFile();
	    }
	}
    //
	$startFrom   = $wi400List->getStartFrom();
	$totalFromSubfile=False;	
	// Funzione personalizzata Reload
	$wi400List->setRunTimeField("callBack", "reload");
	if ($wi400List->getCallBackFunction("reload")!=False) {
		if (is_callable($wi400List->getCallBackFunction("reload"))) {
			$wi400List = call_user_func($wi400List->getCallBackFunction("reload"), $wi400List, $_REQUEST);
		} else {
			die("call user func not valid ".$wi400List->getCallBackFunction("reload"));
		}
	}
	//getMicroTimeStep("INIZIO SUBFILE");
	if ( $wi400List->getSubfile() != null){
		$wi400Subfile = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $wi400List->getSubfile());
		$pagination = "";
		if (isset($_GET['PAGINATION'])) {
			$pagination = $_GET['PAGINATION'];
		}
		wi400List::disposeSubfile($wi400List, $wi400Subfile, $pagination);
	} 
	if(isset($_SESSION['NAVIGAZIONE_TABLET_ATTIVA']) && isset($settings['disable_drag_header']) && $settings['disable_drag_header']===true) {
		$wi400List->setDraggableHeader(false);
	}
	//getMicroTimeStep("FINE SUBFILE");
	
	//getMicroTimeStep("INIZIO LISTA");

	// ***************************************************
	// ORDINAMENTO
	// ***************************************************
	if(isset($_GET['ORDER'])) {
		$breakKey = $wi400List->getBreakKey();
		$ordinamento = "ASC";
		$break = "";
		
		if($breakKey != "" && $breakKey != $_GET['ORDER']) {
			if(strpos($wi400List->getOrder(), $breakKey." DESC") !== false) {
				$ordinamento = "DESC";
			}
			$break = $breakKey." ".$ordinamento.", ";
		}
		// Verifico se ho un ordinamento statico da mantenere ad ogni costo
		$SO = $wi400List->getStaticOrder();
		$ordFixed="";
		if ($SO != "") {
			$ordFixed .= " , ".$SO;
		}
//		$a = $break.$_GET['ORDER']."_DESC_==_".$wi400List->getOrder()."<br/><br/>";
//		echo str_replace(" ", "_", $a);
		$order = $wi400List->getOrder();
		$order = str_replace($SO, "", $order);
		if(strpos($order, $break.$_GET['ORDER']." DESC")!==False) {
			$wi400List->setOrder($_GET['ORDER']." ASC".$ordFixed);
		}else {
			$wi400List->setOrder($_GET['ORDER']." DESC".$ordFixed);
		}
		
		if($breakKey != "" && $_GET['ORDER']!= $breakKey) {
			$wi400List->setOrder($breakKey." $ordinamento, ".$wi400List->getOrder().$ordFixed);
		}
		
//		echo "<br/><br/>".str_replace(" ", "_", $wi400List->getOrder());
	}
	
	$hasOrder = 'false';
	if(is_array($wi400List->getOrder()) && sizeof($wi400List->getOrder()) > 0) 
		$hasOrder = 'true';
	
	// ***************************************************
	// COSTRUZIONE QUERY
	// ***************************************************
	$wi400ListSql = new wi400ListSql($wi400List, $wi400List->getAutoFilter(), $totalFromSubfile);
	
//	$wi400ListSql->prepare_query_parts();
	
	$query = $wi400ListSql->get_query();
//	echo "<font color='blue'>QUERY:</font> $query<br>";
	
	$orderBySql = $wi400ListSql->get_order_by();
	
	$hasFilter = $wi400ListSql->get_hasFilter();
	$totalFromSubfile = $wi400ListSql->get_totalFromSubfile();
	
	// Start From
	$startFrom = $wi400List->getStartFrom();
	$pageRows    = $wi400List->getPageRows();
	//*****************************************************
	// Evita il conteggio totale delle righe
	//*****************************************************
	$lastPage = false;
	$firstPage = false;
	$reloadPage = false;
	if ($totalFromSubfile==False){
		if (isset($_GET['PAGINATION']) && $_GET['PAGINATION'] == 'LAST'){
			$lastPage = true;
		}
		if (isset($_GET['PAGINATION']) && ($_GET['PAGINATION'] == 'FIRST' || $_GET['PAGINATION'] == 'REGENERATE')){
			$firstPage = true;
		}
		$reloadPage = false;
		if (isset($_GET['PAGINATION']) && $_GET['PAGINATION'] == 'RELOAD' ){
			//Aggiornamento del record
			if($wi400List->getAutoUpdateList()) {
				$wi400List->setErrorMessages(array());
			}
			/*$wi400List->setRunTimeField("callBack", "reload");
			if ($wi400List->getCallBackFunction("reload")!=False) {
				if (is_callable($wi400List->getCallBackFunction("reload"))) {
					$wi400List = call_user_func($wi400List->getCallBackFunction("reload"), $wi400List, $_REQUEST);
				} else {
					die("call user func not valid ".$wi400List->getCallBackFunction("reload"));
				}
			}*/
			$reloadPage = true;
		}
	}
	$totalRows = 0;
	// Se la query originale contiene già un WITH non è possibile eseguire la query per il conteggio delle righe totali
	if(!($wi400List->getQuery()!="" && strtoupper(substr(trim($wi400List->getQuery()), 0, 4))=="WITH")) {
	if (is_numeric($wi400List->getCalculateTotalRows())){
		$totalRows = $wi400List->getCalculateTotalRows();
//		echo "TOT_ROWS:$totalRows<br>";
	}
	else {
//		echo "CALCULATE_TOTAL_ROWS:".$wi400List->getCalculateTotalRows()."<br>";
//		echo "LAST_PAGE:$lastPage<br>";
//		echo "FIRST_PAGE:$firstPage<br>";
//		echo "RELOAD_PAGE:$reloadPage<br>";
		if ($wi400List->getCalculateTotalRows()=="" || 
			$lastPage || 
			($firstPage && $wi400List->getCalculateTotalRows() != "FALSE" && $wi400List->getCalculateTotalRows() != "LAST")	|| 
			($wi400List->getCalculateTotalRows() == "RELOAD_FIRST") ||
			($wi400List->getCalculateTotalRows()=="RELOAD" && $reloadPage)
		) {
/*			
			// Se è una query diretta tento di recuperare il numero di record ...
			if($wi400List->getQuery()!="") {
				if($wi400ListSql->get_filterWhere()!="") {
					$query_num_rows = $wi400ListSql->get_query(false, true);
//	    			echo "<font color='blue'>QUERY_NUM_ROWS_1:</font> $query_num_rows<br>";
	
					$do = $db->singleQuery($query_num_rows);
					$row = $db->fetch_array($do);
		    
					$totalRows = $row["COUNTER"];
				}
				else {
					$do   = $db->query($query, true, $pageRows);
					
					$totalRows = $db->num_rows($do);
				}
			}
			else {
				// Calcolo la query corretta per il numero di pagine
				$query_num_rows = $wi400ListSql->get_query(false, true);				
//	    		echo "<font color='blue'>QUERY_NUM_ROWS_2:</font> $query_num_rows<br>";
		   
				$do = $db->singleQuery($query_num_rows);
				$row = $db->fetch_array($do);
		   
				$totalRows = $row["COUNTER"];
	    	}
*/
			if($wi400List->getCalculateTotalRows()=="RELOAD_FIRST") {
				$wi400List->setCalculateTotalRows("RELOAD");
//				echo "CALCULATE_TOTAL_ROWS_2:".$wi400List->getCalculateTotalRows()."<br>";
			}
			
			// Calcolo la query corretta per il numero di pagine
/*			
			$query_num_rows = $wi400ListSql->get_query(false, true);
//	    	echo "<font color='blue'>QUERY_NUM_ROWS_1:</font> $query_num_rows<br>";
			
			$do = $db->singleQuery($query_num_rows);
			$row = $db->fetch_array($do);
*//*
			// CON PULIZIA DEL FROM
			error_reporting(0);
//			$db->set_write_logs(false);
			
			$wi400ListSql->set_cleanCount(true);
			$query_num_rows = $wi400ListSql->get_query(false, true);
//	    	echo "<font color='blue'>QUERY_NUM_ROWS_1_CLEAN:</font> $query_num_rows<br>";
	    	
	    	$do = $db->singleQuery($query_num_rows);
	    	
	    	error_reporting($settings['display_errors']);
//	    	$db->set_write_logs(true);
	    	
	    	if(!$do) {
	    		// SENZA PULIZIA DEL FROM
	    		$wi400ListSql->set_cleanCount(false);
	    		$query_num_rows = $wi400ListSql->get_query(false, true);
//		    	echo "<font color='blue'>QUERY_NUM_ROWS_1:</font> $query_num_rows<br>";
	    		
	    		$do = $db->singleQuery($query_num_rows);
	    	}
	    	
	    	$row = $db->fetch_array($do);
*//*
			// CON PULIZIA DEL FROM
			$wi400ListSql->set_cleanCount(true);
			$query_num_rows = $wi400ListSql->get_query(false, true);
//	    	echo "<font color='blue'>QUERY_NUM_ROWS_1_CLEAN:</font> $query_num_rows<br>";
			
	    	$stmt_count = $db->singlePrepare($query_num_rows, 0 , True);
	    	if($stmt_count) {
	    		$res_count = $db->execute($stmt_count);
	    	}
	    	else {
	    		// SENZA PULIZIA DEL FROM
	    		$wi400ListSql->set_cleanCount(false);
	    		$query_num_rows = $wi400ListSql->get_query(false, true);
//		    	echo "<font color='blue'>QUERY_NUM_ROWS_1:</font> $query_num_rows<br>";

	    		$stmt_count = $db->singlePrepare($query_num_rows, 0 , True);
	    		$res_count = $db->execute($stmt_count);
	    	}
*/
			$count_int = false;
			if($wi400List->get_queryCount()===true) {
				// CON PULIZIA DEL FROM
				$wi400ListSql->set_cleanCount(true);
				$query_num_rows = $wi400ListSql->get_query(false, true);
//	    		echo "<font color='blue'>QUERY_NUM_ROWS_1_CLEAN:</font> $query_num_rows<br>";
			
				$stmt_count = $db->singlePrepare($query_num_rows, 0 , True);
				if($stmt_count) {
					$res_count = $db->execute($stmt_count);
				}
				else {
					$count_int = true;
				}
			}
			else if($wi400List->get_queryCount()===false) {
				$count_int = true;
			}
			else if($wi400List->get_queryCount()!=="") {
				// QUERY COUNT SPECIFICATA
				$query_count = $wi400List->get_queryCount();
				$filter_where = $wi400ListSql->get_filterWhere();
			
				$query_num_rows = "select COUNT(*) as COUNTER from ($query_count)";
				if($filter_where!="")
					$query_num_rows .= " where ".$filter_where;
//				echo "<font color='blue'>QUERY_NUM_ROWS_1_SET:</font> $query_num_rows<br>";
			
				$stmt_count = $db->singlePrepare($query_num_rows, 0 , True);
				if($stmt_count) {
					$res_count = $db->execute($stmt_count);
				}
				else {
					$count_int = true;
				}
			}
			
			if($count_int===true) {
				// SENZA PULIZIA DEL FROM
				$wi400ListSql->set_cleanCount(false);
				$query_num_rows = $wi400ListSql->get_query(false, true);
//		    	echo "<font color='blue'>QUERY_NUM_ROWS_1:</font> $query_num_rows<br>";
					
				$stmt_count = $db->singlePrepare($query_num_rows, 0 , True);
				$res_count = $db->execute($stmt_count);
			}
				    	
	    	$row = $db->fetch_array($stmt_count);
	    	
			$totalRows = $row["COUNTER"];
//			echo "TOTAL_ROWS_1:$totalRows<br>";
			
			if ($wi400List->getCalculateTotalRows()=="RELOAD"){
				$_SESSION[$idList."_TOTAL_ROWS"] = $totalRows;
			}
		}
	
		if ($wi400List->getCalculateTotalRows()=="RELOAD" && !$reloadPage){
			$totalRows = $_SESSION[$idList."_TOTAL_ROWS"];
//			echo "TOTAL_ROWS_2:$totalRows<br>";
		}
//		echo "TOTAL_ROWS:$totalRows<br>";
	}
	}
	
	// **********************************************************
	// Posizionamento automatico DB2 only
	// **********************************************************
	if(isset($_SESSION[$idList."_SHOW_ROW"])) {
		if(sizeof($_SESSION[$idList."_SHOW_ROW"])>0){
			$searchWhere = "WHERE 1=1";
			foreach($_SESSION[$idList."_SHOW_ROW"] as $sk => $sv){
				$searchWhere.= " AND ".$sk." = '".$sv."'";
			}
	
			$searchQuery = "SELECT numtable.RNUM as STARTFROM FROM (
				SELECT INT(ROWNUMBER() OVER ()) AS RNUM, t.* FROM (".$query.") t) numtable ".$searchWhere." ".$orderBySql;
//			echo "SEARCH QUERY: $searchQuery<br>";
	
			$resultCounter = $db->query($searchQuery, False);
			$counterArray = $db->fetch_array($resultCounter);
				
			$startFrom = (ceil($counterArray["STARTFROM"]/$pageRows) * $pageRows) - $pageRows;
		}
	
		unset($_SESSION[$idList."_SHOW_ROW"]);
	}
	
	// ***************************************************
	// PAGINAZIONE
	// ***************************************************
	if(isset($_GET['PAGINATION'])){
		if($_GET['PAGINATION']=='NEXT'){
			$startFrom = $startFrom + $pageRows;
		}
		else if ($_GET['PAGINATION']=='PREV') {
			$startFrom = $startFrom - $pageRows;
		}
		else if($_GET['PAGINATION']=='REGENERATE') {
			$startFrom = 0;
		}
		else if($_GET['PAGINATION']=='FIRST') {
			$startFrom = 0;
		}
		else if(in_array($_GET['PAGINATION'], array("RELOAD", "EXPORT")) || substr($_GET['PAGINATION'],0,7) == 'EXPORT:') {
			$startFrom = $startFrom;
		}
		else if($_GET['PAGINATION']=='LAST') {
			$startFrom = (ceil($totalRows / $pageRows) - 1) * $pageRows;
		}
	}
	
	if(isset($_GET['NUM_PAGE'])) {
		$numero_pag = $_GET['NUM_PAGE'];
		$startFrom = ($numero_pag-1)*$pageRows;
	}
//	echo "<font color='red'>START_FROM:</font> $startFrom<br>";

	// PAGINATION BETWEEN
	// DEFAULT true: viene utilizzata la paginazione con BETWEEN
	// false: viene utilizzata la paginazione normale
	// IMPORTANTE! Impostare la singola lista con setPagBetween() a FALSE 
	// in caso la lista faccia uso di query diretta con campi specifici impostati con setField()
	// con contengano riferimenti ad alias di tabelle (es: a.CAMPO)
	
	if($startFrom>0 && 
		(isset($settings['query_pagination_between']) && $settings['query_pagination_between']===true) &&
		$wi400List->getPagBetween()===true
	) {
		$query = $wi400ListSql->get_query_start($startFrom+1, $startFrom+$pageRows+1);
	}
	
//	echo "<font color='red'>CURRENT_QUERY:</font> $query<br>";	

	$selection   = $wi400List->getSelection();
	
	$wi400List->setCurrentQuery($query);
	//echo "PAGINATION!!".$startFrom. " --->".$query;
	$resultSet   = $db->query($query, True, $pageRows+1, $startFrom);
	//echo $query. " -PR:".$pageRows. " SF;".$startFrom;
	// paginazione startFrom se non è abilitato parametro 'query_pagination_between'
	// o se è disabilitato il pagination between nella lista o se la query è libera ($wi400List->setQuery();)
	if($startFrom>0 &&
		!((isset($settings['query_pagination_between']) && $settings['query_pagination_between']===true) &&
			$wi400List->getPagBetween()===true)
	) {
		 // Se il DB non supporta il caricamento paginato mi devo posizionare con la fetch
		 if ($db->getDBAttribute("DB_SUPPORT_PAGINATION")==Null or strpos(strtoupper($query), "ORDER BY")===False) {
		 	$db->fetch_array($resultSet, $startFrom);
		 }
	}
	
	// **************** TOTALI DELLA LISTA ****************
	if (isset($_GET['PAGINATION'])) {
		if (($_GET['PAGINATION'] == 'RELOAD' || $_GET['PAGINATION'] == 'REGENERATE' || $_GET['PAGINATION'] == 'FIRST') 
				&& sizeof($wi400List->getTotals())>0){
			
			$subTotalArray = $wi400List->getTotals();
			
			$selectSumCol = "";
			foreach ($wi400List->getCols() as $columnObj) {
				$columnKey = $columnObj->getKey();
				
				if (isset($subTotalArray[$columnKey])){					
					if (strpos($subTotalArray[$columnKey], "EVAL:")===0){
						// do nothing	
/*						
						// @todo Calcolare i totali tenendo conto di alcune condizioni
						$cond = substr($subTotalArray[$columnKey], strlen("EVAL:"));
						
						// Comportamente di default. Somma
						if ($selectSumCol != ""){
							$selectSumCol.= ",";
						}
						$selectSumCol.= " VALUE(SUM(".$cond."), 0) as ".$columnKey;
*/						
					}
					else{
						// Comportamente di default. Somma
						if ($selectSumCol != ""){
							$selectSumCol.= ",";
						}
						$selectSumCol.= " VALUE(SUM(".$columnKey."), 0) as ".$columnKey;
					}
				}
			}
//			echo "<font color='green'>SELECT:</font> $selectSumCol<br>";
/*			
			$whereClause = "";
			if (stripos($query,"WHERE") > 0){
				$whereClause = substr($query,stripos($query,"WHERE"));
			}
			if (stripos($whereClause,"ORDER") > 0){
				$whereClause = substr($whereClause,0,stripos($whereClause,"ORDER"));
			}
//			echo "<font color='green'>WHERE:</font> $whereClause<br>";
*/			
			if ($selectSumCol != ""){
//				$totQuery = "SELECT ".$selectSumCol." FROM ".$wi400List->getFrom()." ".$whereClause;				
				
				// Calcolo la query corretta per il totale delle colonne
				$totQuery = $wi400ListSql->get_query(false, false, $selectSumCol);
//				echo "<font color='red'>TOT QUERY:</font> $totQuery<br>";
				
				$totalSet = $db->query($totQuery,true);
				
				$totalArray = $db->fetch_array($totalSet);
				
				if(!empty($totalArray)) {
					foreach($totalArray as $subTotCol => $subTotValue){
						if ($subTotValue == "") $subTotValue = 0;
						$wi400List->addTotal($subTotCol, $subTotValue);
					}
				}
			}
		}
	}
	// ******************* FINE TOTALI LISTA ********************
	
	$rowsSelectionArray = $wi400List->getSelectionArray();
			
	$wi400List->setTotalRows($totalRows);
	$wi400List->setStartFrom($startFrom);
	// Imposto il numero di righe di pagina uguale al numero di righe lette .. Qui è troppo tardi
	/*if ($wi400List->getAutoRowNumber() && $totalRows <> 0) {
		$pageRows = $totalRows;
		$wi400List->setPageRows($totalRows);
	}*/

	// ************************************************************************
	// Dimensione Header
	// ************************************************************************
	$rowHeaderHeight = $wi400List->getRowHeaderHeight();
	if ($wi400List->getRowHeaderHeight() <= $wi400List->getRowHeight()) {
		// Dimensione Header NON impostata manualmente o minore di quella definita a sistema
		foreach ($wi400List->getCols() as $columnObj) {
			if ($columnObj->getShow() && strpos($columnObj->getDescription(),"<br>") > 0){
				$rowHeaderHeight = $rowHeaderHeight + ($wi400List->getRowHeight()/2);
				break;
			}
		}	
	}
	// ************************************************************************
	// Larghezza Salvata delle colonne
	// ************************************************************************	
	$misure_save = $wi400List->getColumnsWidth();
	// NO SU COLONNE SINGOLE MA SULL'HEADER
	/*foreach ($wi400List->getColumnsOrder() as $columnKey) {
		$wi400Column = $wi400List->getCol($columnKey);
		if (is_object($wi400Column) && isset($misure_save[$columnKey])) {
			$wi400Column->setWidth($misure_save[$columnKey]);
		}
	}*/
	// ************************************************************************
	// Raggruppamenti
	// ************************************************************************
	$colsGroups = array();
	$hasGroupBr = false;
	$hasGroup = false;
	foreach ($wi400List->getColumnsOrder() as $columnKey) {
		$wi400Column = $wi400List->getCol($columnKey);
		if (is_object($wi400Column)) {
			if($wi400Column->getShow()===false)
				continue;
			
			if ($wi400Column->getGroup() != ""){
				$hasGroup = true;
				if (!$hasGroupBr && strpos($wi400List->getGroupDescription($wi400Column->getGroup()),"<br>") > 0){
					$hasGroupBr = true;
				}
				$colsGroups[$wi400Column->getGroup()][$wi400Column->getKey()] = $wi400Column->getKey();
			}
		}
	}
	
	if ($wi400List->getRowHeaderHeight() <= $wi400List->getRowHeight()) {
		if ($hasGroup) $rowHeaderHeight = $rowHeaderHeight + $wi400List->getRowHeight();
		if ($hasGroupBr) $rowHeaderHeight = $rowHeaderHeight + ($wi400List->getRowHeight()/2);
	}
	
	
	$columnsArray = array();
	$fixedArray = array();
	foreach ($wi400List->getColumnsOrder() as $columnKey) {
		$wi400Column = $wi400List->getCol($columnKey);
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
	// ************************************************************************
	$colsCounter = 0;
	$lastColumn = "";
	$currentGroup = "";
	if ($hideHeader != True) {
?>
	<table width="100%" cellpadding="0" cellspacing="0" class="wi400-grid" id="table_<?= $wi400List->getIdList() ?>" class="resizable">
	    <thead>
		<tr class="wi400-grid-header ui-sortable" >
    	<th class="wi400-grid-header-first-cell nosort" style='height:<?= $rowHeaderHeight ?>px; <?= $wi400List->getHideSelectRow() ? "display: none;" : ""?>' <? if (sizeof($colsGroups)>0){ ?> rowspan="2" <?}?> width="5"><? if ($selection == "MULTIPLE"){ ?><input onClick="selectGridAll('<?= $wi400List->getIdList() ?>',this.checked)" id="select_all_<?= $wi400List->getIdList() ?>" type="checkbox" value="checked"><? }else{?>&nbsp;<?}?></th>
<?
	if (sizeof($wi400List->getMessages())>0){
		// Colonna messaggi SEMPRE
		echo '<th class="wi400-grid-header-first-cell nosort" width="5"';
		if (sizeof($colsGroups)>0){ 
			echo ' rowspan="2" ';
		}
		echo '>&nbsp;</th>';
	} else {
		echo '<th class="wi400-grid-header-first-cell nosort" width="0" style="padding: 0px 0px;"';
		if (sizeof($colsGroups)>0){
			echo ' rowspan="2" ';
		}
		echo '>'.$wi400List->getLabelErrorCol().'</th>';
	}
	
	if ($wi400List->getDetailHtml() != "" || $wi400List->getDetailAjax() != ""){
		echo '<th class="wi400-grid-header-first-cell nosort" width="5"';
		if (sizeof($colsGroups)>0){ 
			echo ' rowspan="2" ';
		}		
		echo '>&nbsp;</th>';
	}
	// COLONNE
	$colsCounter = 0;
	$lastColumn = "";
	$currentGroup = "";
	foreach ($columnsArray as $columnKey) {
		$wi400Column = $wi400List->getCol($columnKey);
		if ($wi400Column != null && $wi400Column->getShow()){
			$outputHtml ="";	
			$orderFunction = "";
			if ($wi400Column->getKey() != ""){
				$orderFunction = "";
			}
			
			if ($wi400Column->getGroup() == "" || isset($fixedArray[$wi400Column->getKey()])){
				$col_des_style = "wi400-grid-header-cell";
				
				$outputHtml = "<th class=\"$col_des_style\" "; 
				// Larghezza colonna impostata sull'header
				if (isset($misure_save[$columnKey])) {
					$outputHtml .= " style=\"width: $misure_save[$columnKey]\" ";
				}
				if ($wi400Column->getSortable()){
					// Fine stile.
					//$outputHtml.='"';
					if($wi400Column->getOrientation()!="vertical"){
						$outputHtml.="	onMouseOver=\"this.className='wi400-grid-header-cell-over'\" onMouseOut=\"this.className='wi400-grid-header-cell'\""; 
					} 
					//$outputHtml.= "onClick=\"doOrder('".$wi400List->getIdList()."','".$wi400Column->getKey()."', $hasOrder);\"";
				}
				if (sizeof($colsGroups)>0 && $wi400Column->getGroup() == "" && !isset($fixedArray[$wi400Column->getKey()])){
					$outputHtml.='rowspan="2"';
				}
				if ($wi400Column->getHeaderTooltip()!="") {
					$tooltipHeader=$wi400Column->getHeaderTooltip();
					$outputHtml.= " title=\"$tooltipHeader\" ";				
				}
				
				$outputHtml.=" id=\"".$wi400Column->getKey()."\">";		
				if($wi400Column->getOrientation()=="vertical"){
						$outputHtml.="<div class=\"wi400-grid-header-cell-vertical\"";
						if (!isIE()){
							$outputHtml.=" style=\"margin-top:".($wi400List->getRowHeaderHeight()-40)."px\"";
				    	}	
						if (isIE()){
							$outputHtml.=" style=\"height:".$wi400List->getRowHeaderHeight()."px\"";
						}
						$outputHtml.=">";
				}
		        $outputHtml.="<div class=\"resize\" ";
		        if ($wi400Column->getSortable()){
					if($wi400Column->getOrientation()!="vertical"){
						$outputHtml.=" onMouseOver=\"this.style.cursor='pointer'\" onMouseOut=\"this.style.cursor='pointer'\"";
					}
		        	$outputHtml.= " onClick=\"doOrder('".$wi400List->getIdList()."','".$wi400Column->getKey()."', $hasOrder);\"";
		        }
		        //$outputHtml.=">".utf8_encode($wi400Column->getDescription()).$wi400List->getHeaderAction($wi400Column);
		        $outputHtml.=">".$wi400Column->getDescription().$wi400List->getHeaderAction($wi400Column);
		        $outputHtml .="</div>";
		        if($wi400Column->getOrientation()!="vertical"){
						if ($wi400List->getOrder() == $wi400Column->getKey()." ASC") $outputHtml.="<img src='".$temaDir."images/grid/asc.gif'>";
						if ($wi400List->getOrder() == $wi400Column->getKey()." DESC") $outputHtml.="<img src='".$temaDir."images/grid/desc.gif'>";
				}
				if($wi400Column->getOrientation()=="vertical"){
					//$outputHtml="</div>";
					$outputHtml.="</td>";
				}	
			} else if ($currentGroup != $wi400Column->getGroup() && !isset($fixedArray[$wi400Column->getKey()])){
				$currentGroup = $wi400Column->getGroup();
				$color = $wi400List->getGroupColor($wi400Column->getGroup());
				if (substr($color,0,1)!="#") {
					$outputHtml.="<td class=\"wi400-grid-header-group nosort\" gruppo=\"{$wi400Column->getGroup()}\" style=\"background-image: url(themes/common/images/grid/grid_group_".$wi400List->getGroupColor($wi400Column->getGroup()).".gif);\" colspan=\"".sizeof($colsGroups[$wi400Column->getGroup()])."\">".$wi400List->getGroupDescription($wi400Column->getGroup())."</td>";
				} else {
					$outputHtml.="<td class=\"wi400-grid-header-group2 nosort\" gruppo=\"{$wi400Column->getGroup()}\" style=\"background-color: ".$wi400List->getGroupColor($wi400Column->getGroup()).";\" colspan=\"".sizeof($colsGroups[$wi400Column->getGroup()])."\">".$wi400List->getGroupDescription($wi400Column->getGroup())."</td>";
				}	
			}
			$outputHtml .="</th>";
			if (!isset($fixedArray[$wi400Column->getKey()])) {
				$lastColumn = $wi400Column->getKey();
				$colsCounter++;
				echo $outputHtml;
			} else {
				$fixedHeaderColumn[$wi400Column->getKey()]=$outputHtml;
			}
		}
	}
?>
	

<?
	// ************************************************
	// COLONNE RAGGRUPPATE
	// ************************************************
	if (sizeof($colsGroups)>0){
?>
	<tr class="wi400-grid-header">
<?
	
	foreach ($columnsArray as $columnKey) {
		$wi400Column = $wi400List->getCol($columnKey);
		if ($wi400Column != null && $wi400Column->getShow() && $wi400Column->getGroup() != "" && !isset($fixedArray[$wi400Column->getKey()])){
			$mytitle="";
			if ($wi400Column->getHeaderTooltip()!="") {
				$tooltipHeader=$wi400Column->getHeaderTooltip();
				$mytitle= " title='$tooltipHeader' ";
			}
?>
		<th class="wi400-grid-header-cell nosort" 	id="<?= $wi400Column->getKey()?>" <?= $mytitle?> gruppo="<?=$wi400Column->getGroup()?>"
<?
			if ($wi400Column->getSortable()){
?>
				style="cursor:pointer"
				onMouseOver="this.className='wi400-grid-header-cell-over'" 
				onMouseOut="this.className='wi400-grid-header-cell'"
				onClick="doOrder('<?= $wi400List->getIdList() ?>','<?= $wi400Column->getKey()?>', <?= $hasOrder ?>);"
<?
			}
	    $headerAction = $wi400List->getHeaderAction($wi400Column);
		?>
		><?= $wi400Column->getDescription(); ?><?= $headerAction ?>
		<? if ($wi400List->getOrder() == $wi400Column->getKey()." ASC") echo "<img src='".$temaDir."images/grid/asc.gif'>"?>
		<? if ($wi400List->getOrder() == $wi400Column->getKey()." DESC") echo "<img src='".$temaDir."images/grid/desc.gif'>"?></th>
<?
		}
	}
?>
	</tr>
<?
	}
	echo "</thead>";
	} else {
		// Devo mettere fuori l'intestazione della tabella
		?>
			<table width="100%" cellpadding="0" cellspacing="0" class="wi400-grid" id="table_<?= $wi400List->getIdList() ?>" class="resizable">
		<?
		// Devo comunque calcolare le colonne totali se nascondo l'HEADER
		foreach ($columnsArray as $columnKey) {
			$wi400Column = $wi400List->getCol($columnKey);
			if ($wi400Column != null && $wi400Column->getShow()){
				$lastColumn = $wi400Column->getKey();
				$colsCounter++;
			}
		}
		if ($wi400List->getDetailHtml() != "" || $wi400List->getDetailAjax() != ""){
			$colsCounter++;
		}
			
	}
	// ************************************************
	// END COLONNE RAGGRUPPATE
	// ************************************************
	$saveBreaKey = "____WI400____";
	
	$rowsCounter = 0;
	$rowsCounter2 = 0;
	$rowSelected = -1;
	$righeDiPagina = array();
	$fixedValuesArray = array();
	$fixedSelectedRows = array();
	$firstTime = True;
	$autoWidth = $wi400List->getAutoWidth();
	while( ($row = $db->fetch_array($resultSet)) && ($rowsCounter < ($pageRows + 1)) ){
		// After FETCH CALLABLE FUNCTION. Nel caso si filtrino le righe Totale Pagine deve essere *
		if ($wi400List->getCallBackFunction("afterFetch")!=False) {
			if (is_callable($wi400List->getCallBackFunction("afterFetch"))) {
				$wi400List->setRunTimeField("callBack", "afterFetch");
				$row = call_user_func($wi400List->getCallBackFunction("afterFetch"), $wi400List, $row);
				// Se non ritorna nulla vuol dire che devo saltare la riga
				if ($row==Null) continue;
			} else {
				die("call user func not valid ".$wi400List->getCallBackFunction("afterFetch"));
			}
		}
		// Experimental .. Calcolo autoWidth delle celle settando la lunghezza massima 
		// @todo calcolo width con lunghezza massima contenuta nella colonna select min(length(trim(<col>))) from <file>
		if ($firstTime && $autoWidth) {
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
		}
		//die();
		if ($rowsCounter < $pageRows){
			$righeDiPagina[$rowsCounter]=$row;
			// CARICAMENTO CHIAVI
			$keysRow = "";
			//$keysKey = "";
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
					//$keysKey = $keysKey."|".$key;
				}else{
					$isFirst = false;
					$keysRow = $keysRow."".$keyValue;
					//$keysKey = $keysKey."".$key;
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
			if($wi400List->getBreakKey() != "") {
				$breaKey = $row[$wi400List->getBreakKey()];
				if($breaKey != $saveBreaKey) {
					$breakClass = "wi400-grid-row-categories";
					if($wi400List->getBreakClass()) {
						$breakClass = $wi400List->getBreakClass();
					}
					$funzioneBreak = $wi400List->getBreakFunction();
					if($funzioneBreak) {
						$breakValue = $funzioneBreak($breaKey, $row);
					}else {
						$breakValue = "- ".$breaKey;
					}
					echo "<tr ><td colspan='100'><div class='$breakClass'>$breakValue </div></td></tr>";
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
			class="<?= $rowStyle ?> <?= $pairStyle ?> <? if ($isSelected)  { echo $rowStyle.'_selected'; $fixedSelectedRows[$rowsCounter] = true; }?>" 
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
	
	if($wi400List->getPassValue()) {
?>
	
		onClick="checkGridRow('<?= $wi400List->getIdList()?>', <?=$rowsCounter?>, true);doSubmit('<?= $_REQUEST['CURRENT_ACTION']?>', 'PASS_VALUE&CAMPO=<?=$wi400List->getPassValue()?>', false, false)"

<?	}
?>
			onMouseOver="overGridRow('<?= $wi400List->getIdList() ?>',<?= $rowsCounter ?>)"
			onMouseOut="outGridRow('<?= $wi400List->getIdList() ?>',<?= $rowsCounter ?>)" style="height:<?= $wi400List->getRowHeight() ?>px">

		<td class="wi400-grid-row-cell" style="height:<?= $wi400List->getRowHeight() ?>px; <?= $wi400List->getHideSelectRow() ? "display: none;" : ""?>" width="5">

			<input disabled class="wi400_row_key" type="hidden" name="<?= $wi400List->getIdList() ?>-<?= $rowsCounter ?>" id="<?= $wi400List->getIdList() ?>-<?= $rowsCounter ?>" value="<?= htmlspecialchars($keysRow) ?>">
				
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
<?
			}
?>
				<input 
				onFocus="overGridRow('<?= $wi400List->getIdList() ?>',<?= $rowsCounter ?>)"
			onBlur="outGridRow('<?= $wi400List->getIdList() ?>',<?= $rowsCounter ?>)"
				 id="<?= $wi400List->getIdList() ?>-<?= $rowsCounter ?>-checkbox" name="<?= $wi400List->getIdList() ?>-<?= $rowsCounter ?>-checkbox" type="checkbox" value="true" <? if ($isSelected) echo 'checked' ?> onClick="checkGridRow('<?= $wi400List->getIdList() ?>', <?= $rowsCounter ?>);<?= $wi400List->getOnChangeChecked() ?>" <?= $wi400List->getHideSelectRow() ? "style='display: none;'" : ""?>>
		</td>
<?	
	if ($wi400List->getCallBackFunction("validation")!=False) {
		if (is_callable($wi400List->getCallBackFunction("validation"))) {
			$wi400List->setCurrentRow($row);
			$_REQUEST['LIST_KEY']=$keysRow;
			$wi400List->setRunTimeField("callBack", "validation");
			$wi400List = call_user_func($wi400List->getCallBackFunction("validation"), $wi400List, $_REQUEST);
			$row = $wi400List->getCurrentRow();
		} else {
			die("call user func not valid ".$wi400List->getCallBackFunction("validation"));
		}
	}
    $rowErrorField = array(); 
	if (sizeof($wi400List->getMessages())>0){
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
//				echo "COMMON_CONDITION:".$wi400List->getCommonCondition()."<br>";
				/**
				 * Recupero di un EVAL comune a una o più condizioni di più colonne della lista
				 * in modo che questo venga eseguito una volta sola per riga e poi il risultato venga sostituito al marker ##COMMON_LIST##
				 * in più eval specifici che hanno bisogno di eseguire lo stesso controllo ogni volta (es: readonly, default value, ...)
				 */
//				$common_list_value = manage_eval_condition($wi400List->getCommonCondition(), $row);
				$common_list_value = $wi400List->manage_eval_condition($wi400List->getCommonCondition(), $row);
//				echo "COMMON_CONDITION_VALUE:$common_list_value<br>";

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
//						$common_col_value = manage_eval_condition($wi400Column->getCommonCondition(), $row);
						$common_col_value = $wi400List->manage_eval_condition($wi400Column->getCommonCondition(), $row);
//						echo "COMMON_COL:$common_col_value<br>";
					
						$common_value["COLUMN"]  = $common_col_value;
					}
					
					if ($wi400Column->getDefaultValue() != ""){
//						echo "CONDITION:<pre>"; print_r($wi400Column->getDefaultValue()); echo "</pre>";
//						echo "COMMON VALUE:<pre>"; print_r($common_value); echo "</pre>";

//						$value = manage_eval_condition($wi400Column->getDefaultValue(), $row, $common_value, $wi400Column->getKey());
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
//						$rowValue = strtr($rowValue, normalizeChars());
						//$rowValue = $rowValue;
					}
//					echo "VAL:$rowValue<br>";
					
					// **********************************************
					// Stile colonna
					// **********************************************
					$rowStyle = "";
					if ($wi400Column->getStyle() != ""){
//						echo "STYLE:"; var_dump($wi400Column->getStyle()); echo "<br>";
//						$rowStyle = manage_eval_condition($wi400Column->getStyle(), $row, $common_value);
						$rowStyle = $wi400List->manage_eval_condition($wi400Column->getStyle(), $row, $common_value, "style");
					}
					//if (isset($rowErrorField[$wi400Column->getKey()])) {
					//	$rowStyle .= " ".$rowErrorField[$wi400Column->getKey()][0]."Field ";
					//}
					$rowAlign  = $wi400Column->getAlign();
					
//					$rowFormat = $wi400Column->getFormat("LIST");
					$rowFormat = "";
					if ($wi400Column->getFormat("LIST")){
//						$rowFormat = manage_eval_condition($wi400Column->getFormat("LIST"), $row, $common_value);
						$rowFormat = $wi400List->manage_eval_condition($wi400Column->getFormat("LIST"), $row, $common_value, "format");
					}				
					
//					$rowDecorator = $wi400Column->getDecorator("LIST");
					$rowDecorator = "";
					if ($wi400Column->getDecorator("LIST")){
//						$rowDecorator = manage_eval_condition($wi400Column->getDecorator("LIST"), $row, $common_value);
						$rowDecorator = $wi400List->manage_eval_condition($wi400Column->getDecorator("LIST"), $row, $common_value, "decorator");
					}
					
					$rowDecode  = $wi400Column->getDecode();
					$rowWidth = "";
					
//					echo "CONDITION:<pre>"; print_r($wi400Column->getReadonly()); echo "</pre>";
//					echo "COMMON VALUE:<pre>"; print_r($common_value); echo "</pre>";
					
					$rowReadonly = false;
//					$rowReadonly = manage_eval_condition($wi400Column->getReadonly(), $row, $common_value);
					$rowReadonly = $wi400List->manage_eval_condition($wi400Column->getReadonly(), $row, $common_value, "readonly");
//					echo "READONLY:"; var_dump($rowReadonly); echo "<br>";
/*
					// @todo SERVE????? a qualche punto è stato perso? o tolto?
					$rowCleanable = false;
					$rowCleanable = $wi400List->manage_eval_condition($wi400Column->getCleanable(), $row, $common_value, "cleanable");
//					echo "CLEANABLE:"; var_dump($rowCleanable); echo "<br>";
*/					
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
						if ($isSelected){
							if (isset($rowsSelectionArray[$keysRow][$wi400Column->getKey()])){
								$rowValue = $rowsSelectionArray[$keysRow][$wi400Column->getKey()];
								if (method_exists($inputField, "getDecimals") && $inputField->getDecimals()>0) {
									$rowValue = doubleModelToView($rowsSelectionArray[$keysRow][$wi400Column->getKey()], $inputField->getDecimals());	
								}
							}
							
						}
						
						//$inputField = $wi400Column->getInput();
						$inputField->setRowNumber($rowsCounter);
						$inputField->setReadonly($rowReadonly);
//						$inputField->setReadonly($rowReadonly, $rowCleanable);		// @todo SERVE????? a qualche punto è stato perso? o tolto?
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
						// INIZIO VALIDATION
						/*if ($inputField && count($inputField->getValidations()) > 0 && $rowValue != ""){
							if (sizeof($inputField->getValidations()) > 0){
								foreach ($inputField->getValidations() as $validation){
										if (is_callable("wi400_validate_".$validation,false)){
											$testField = new wi400Input();
											$testField->setValue($rowValue);
											$testField->setId($fieldId."_".$fieldArrayCounter);
											if (!call_user_func("wi400_validate_".$validation, $testField)) {
													$inputField->setStyleClass("inputerror errorField inputtext");
											}
										}else{
											echo "<br>Funzione di controllo wi400_validate_".$validation." non implementata.";
											exit();
										}
								}
							}
						}*/
						// FINE VALIDATION
						if ($inputField && $inputField->getDecode() != "" && $rowValue != ""){
		
							$decodeParameters = $inputField->getDecode();
							$decodeType = "table";
							if (isset($decodeParameters["TYPE"])){
								$decodeType = $decodeParameters["TYPE"];
							}
							require_once p13nPackage($decodeType);
							// Se ci sono JS PARAMETER cerco di prendelo dai valori di ROW
							if (isset($decodeParameters['JS_PARAMETERS'])) {
								foreach ($decodeParameters['JS_PARAMETERS'] as $key => $value) {
									if (isset($row[$key])) {
										$decodeParameters[$value]=$row[$key];
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
						if ($wi400List->getAutoUpdateList() && $wi400Column->getDisableAutoUpdate()==False) {
							$backGround='false';
							if ($wi400Column->getAutoUpdateBackGround()) {
									$backGround='true';
							}
							
							if(!$wi400List->getUpdateOnChangeRow()) {
								$stringFunction = "updateListRow";
								if($inputField->getType()=='CHECKBOX') {
									$stringFunction = "updateListRowTimeout";
								}
								
								if (strtoupper($wi400List->getAutoUpdateEvent())=="ONBLUR") {
									$inputField->setOnBlur("$stringFunction(this, '', $backGround)");
								}
								
								if (strtoupper($wi400List->getAutoUpdateEvent())=="ONCHANGE") {
									if(strpos($inputField->getOnChange(), $stringFunction) !== false) {

									}
									else {
										$inputField->setOnChange("$stringFunction(this, '', $backGround);".$inputField->getOnChange());
									}
								}
							}
						}
						
						if($inputField->getType() != "CHECKBOX") {
							$inputField->setTabIndex();
						}
						if($wi400List->getAutoSelection() && $inputField->getType() == "INPUT_TEXT") {
							$inputField->setAutoSelection(true);
						}
						// Aggiunge indicatore per tab e focus
						if ($wi400List->getTimer()>0 || $wi400List->getAutoFocus()==False) {
						  // NO FOCUS
						} else {
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
					
					if ($rowValue == "") 
						$rowValue = "&nbsp;";
					else if(!isHtml($rowValue))
						$rowValue = str_replace(" ", "&nbsp;", $rowValue);
//					echo "ROW_VALUE:$rowValue<br>";
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
//			$detailStyle = manage_eval_condition($wi400Column->getDetailStyle(), $row, $common_value);
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
//	$actionListId = manage_eval_condition($wi400Column->getActionListId(), $row, $common_value);
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
//		$draggable = manage_eval_condition($wi400Column->getDraggable(), $row, $common_value);
		$draggable = $wi400List->manage_eval_condition($wi400Column->getDraggable(), $row, $common_value, "draggable");
	}
		
	if($draggable===true) {
		$canDrag = $wi400Column->getKey();
		$startLink = "<div id=\"".$wi400List->getIdList()."_".$wi400Column->getKey()."_".$rowsCounter."\" class=\"".$rowStyle."\" title=\"".$rowsCounter."\" style=\"border:1px solid #bdbdbd; padding:2px;background-color:#fde19a;cursor:move\">";
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
			if (is_array($wi400Column->getToolTipAjax("extraParameters")) && count($wi400Column->getToolTipAjax("extraParameters"))>0) {
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
	$outputHtml.=' title="'.$toolTip.'" '.$columnOnClick.' align="'.$rowAlign.'" '.$rowWidth.'>'.$startWidth.$startLink;
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
			//
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
// Questo è quello che fa fixed alla fine..

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
				<td style="background-color:#ededed;"><div class="wi400-grid-row-content" style="width:27px">&nbsp;</div></td>
				<td colspan="<?= $colsCounter + 2 ?>" id="<?= $wi400List->getIdList().'-'.$rowsCounter.'-detail-html' ?>"><?= $wi400List->getDetailHtml() ?></td>
			</tr>
<?
			}// End detail

		}
		if ($wi400List->getCacheData()==True) {
			$wi400List->setRowArray($row, $rowsCounter);
		}
		$rowsCounter++;
		$rowsCounter2++;
	}

	
	if ($rowsCounter==0) {
		if($wi400List->getCallBackFunction("onEmpty")!=False) {
			if(is_callable($wi400List->getCallBackFunction("onEmpty"))) {
				$wi400List->setRunTimeField("callBack", "onEmpty");
				$wi400List = call_user_func($wi400List->getCallBackFunction("onEmpty"), $wi400List, $_REQUEST);
			}else {
				die("call user func not valid ".$wi400List->getCallBackFunction("onEmpty"));
			}
		}
?>
		<tr height="30"><td colspan="<?= $colsCounter + 1 ?>" class="wi400-grid-row-cell" align="center"><?= _t('NO_RECORD') ?></td></tr>
<? 
	} else {
		$wi400List->setRigheDiPagina($righeDiPagina);
		// Forzo salvataggio lista
		wi400Session::save(wi400Session::$_TYPE_LIST, $_GET['IDLIST'], $wi400List);
		//$_SESSION[$_GET['IDLIST']] = $wi400List;
		//var_dump($wi400List);
	}

	$returnScript = $wi400List->getScriptOnAutoUpdate();
	if($returnScript) {
?>
		<script>
			<?= $returnScript?>
		</script>
<?php 
	}
	
	// SUBTOTALI ********
	if (sizeof($wi400List->getTotals())>0 && $rowsCounter > 0){
		$subTotalArray = $wi400List->getTotals();
	?>
		<tr id="<?= $wi400List->getIdList()."-".$rowsCounter."-tr" ?>" 
				class="wi400-grid-header"><td class="wi400-grid-sub-total-cell"><?= $wi400List->getSubTitle() ?></td>
	<? 
		// Cella vuota corrispondente a errore lista
		if(!$wi400List->getHideSelectRow())
			echo "<td class='wi400-grid-sub-total-cell' style='padding: 0px 0px;'></td>";
		// Se c'è il drop aggiungo un TD per allineare correttamente i totali		
		if ($wi400List->getDetailHtml() != "" || $wi400List->getDetailAjax() != ""){
			echo "<td class='wi400-grid-sub-total-cell'></td>";
		}
		foreach ($columnsArray as $columnKey) {
		
				$wi400Column = $wi400List->getCol($columnKey);
				if ($wi400Column != null && $wi400Column->getShow() && !$wi400Column->isFixed()){
				?> <td id="TOT_<?= $wi400Column->getKey()?>" class="wi400-grid-sub-total-cell"  align="<?= $wi400Column->getAlign() ?>"> <?
					if (isset($subTotalArray[$columnKey])){

						$subTotalValue = wi400List::applyEval($subTotalArray[$columnKey], $subTotalArray, $wi400List->getParameters());
						$subTotalValue = wi400List::applyFormat($subTotalValue, $wi400Column->getFormat("TOTAL"));
						$subTotalValue = wi400List::applyDecorator($subTotalValue, $wi400Column->getDecorator("TOTAL"));
						
						if ($subTotalValue != ""){
							echo $subTotalValue;
						}else{
							echo "-";
						}
						
					}else{
						echo "-";
					}
					echo "</td>";
				}
			}
			echo "</tr>";
		}
	// END SUB TOTALI

	// TOTALI SUBFILE
		
	if ( $wi400List->getSubfile() != null && $rowsCounter > 0){
		$totalArray = $wi400Subfile->getTotals();
//		echo "TOTAL_ARRAY_2:<pre>"; print_r($totalArray); echo "</pre>";
//		if (sizeof($totalArray)>0){
		if(!empty($totalArray)) {
?>
<tr id="<?= $wi400List->getIdList()."-".$rowsCounter."-tr" ?>" 
			class="wi400-grid-header" style="height:<?= $wi400List->getRowHeight() ?>px"><td class="wi400-grid-total-cell" style="height:<?= $wi400List->getRowHeight() ?>px">Tot.</td>
<?
			if(!$wi400List->getHideSelectRow()) 
				echo "<td class='wi400-grid-total-cell'></td>";
			
			// Se c'è il drop aggiungo un TD per allineare correttamente i totali
			if ($wi400List->getDetailHtml() != "" || $wi400List->getDetailAjax() != ""){
				echo "<td class='wi400-grid-sub-total-cell'></td>";
			}
			
			foreach ($columnsArray as $columnKey) {
	
				$wi400Column = $wi400List->getCol($columnKey);
				if ($wi400Column != null && $wi400Column->getShow() && !$wi400Column->isFixed()){
					?> <td id="TOT_<?= $wi400Column->getKey()?>" class="wi400-grid-total-cell"  align="<?= $wi400Column->getAlign() ?>"> <?
					if (isset($totalArray[$columnKey])){
						$totalValue = $totalArray[$columnKey]; 
						$totalValue = wi400List::applyEval($totalValue, $totalArray, $wi400Subfile->getParameters());
						$totalValue = wi400List::applyFormat($totalValue, $wi400Column->getFormat("TOTAL"));
						$totalValue = wi400List::applyDecorator($totalValue, $wi400Column->getDecorator("TOTAL"));
								
						if ($totalValue != ""){
							echo $totalValue;
						}else{
							echo "-";
						}
						
					}else{
						echo "-";
					}
					echo "</td>";
				}
			}
			echo "</tr>";
		}
	
	// END TOTALI SUBFILE
		

	// RIGHE EXTRA
	$extraRows = $wi400Subfile->getExtraRows();
	if (sizeof($extraRows)>0 && $rowsCounter > 0){
		foreach ($extraRows as $extraDesc => $extraArray){
?>
			<tr id="<?= $wi400List->getIdList()."-".$rowsCounter."-tr" ?>" 
						class="wi400-grid-header"><td class="wi400-grid-extra-cell" style="height:<?= $wi400List->getRowHeight() ?>px"><?= $extraDesc ?></td>
<?
						echo "<td class='wi400-grid-extra-cell'></td>";
						foreach ($columnsArray as $columnKey) {
				
							$wi400Column = $wi400List->getCol($columnKey);
							if ($wi400Column != null && $wi400Column->getShow() && !$wi400Column->isFixed()){
								?> <td class="wi400-grid-extra-cell"  align="<?= $wi400Column->getAlign() ?>"> <?
								if (isset($extraArray[$columnKey])){
								
									//$extraArray[$columnKey] = wi400List::applyEval($extraArray[$columnKey], $extraArray);
									//$extraArray[$columnKey] = wi400List::applyFormat($extraArray[$columnKey], $wi400Column->getFormat("EXTRA"));
									$extraArray[$columnKey] = wi400List::applyDecorator($extraArray[$columnKey], $wi400Column->getDecorator("EXTRA"));

									if ($extraArray[$columnKey] != ""){
										echo $extraArray[$columnKey];
									}else{
										echo "-";
									}
									
								}else{
									echo "-";
								}
								echo "</td>";
							}
						}
						echo "</tr>";
			}
		}
	}
	
	// END RIGHE EXTRA
	if ($hideHeader!=True) {
?>
	</table>
<?
	}
	// AGGIORNO PAGINAZIONE VIDEO
	$currentPage = ceil(($startFrom + 1)/ $pageRows);

	$totalPages = "*";
	// Se la query originale contiene già un WITH non è possibile eseguire la query per il conteggio delle righe totali
	if(!($wi400List->getQuery()!="" && strtoupper(substr(trim($wi400List->getQuery()), 0, 4))=="WITH")) {
		if (is_numeric($wi400List->getCalculateTotalRows()) || 
			$wi400List->getCalculateTotalRows()=="" || 
			$wi400List->getCalculateTotalRows()=="RELOAD" || 
//			$wi400List->getCalculateTotalRows()=="RELOAD_FIRST" ||
			$lastPage
		) {
			$totalPages = ceil($totalRows / $pageRows);
		}
	}
//	echo "TOT_PAG:$totalPages<br>";
	
	$firstPage = "";
	$prevPage = "";
	$nextPage = "";
	$lastPage = "";
	
	if ( $startFrom == 0){
		$firstPage = "_disabled";
		$prevPage = "_disabled";
	}
	if ($rowsCounter <= $pageRows){
		$nextPage = "_disabled";
		$lastPage = "_disabled";
	}
	
	if ($rowsCounter == 0){
		$currentPage = 0;
	}
	//getMicroTimeStep("FINE LISTA");
	
	// Se collegata una progress bar la chiudo
	if ($wi400List->getProgressBar()){
		wi400ProgressBar::setPercentage($wi400List->getProgressBar(),100);
	}
	if ($wi400List->getRefreshFocus()==True) {
	?>
	<script>
	REFRESH_FOCUS = true;
	</script>
	<?php 
    }
?>
<script>
	<?= $idList ?>_CR = <?= $rowSelected ?>;
	<?= $wi400List->getIdList() ?>_SC = <?= sizeof($wi400List->getSelectionArray()) ?>;
	refreshFilter("<?= $wi400List->getIdList() ?>", <?= $hasFilter ?>);
	refreshOrder("<?= $wi400List->getIdList() ?>", <?= $hasOrder ?>);
<?
	if ($wi400List->getSelection() == "MULTIPLE"){?>

	refreshListSelection("<?= $wi400List->getIdList() ?>");
<?
	}
	global $tab_index;
	$noFocus="false";
	if ($wi400List->getTimer() > 0 || $wi400List->getAutoFocus()==False){
		$noFocus="true";
	}
?>
	refreshPagination("<?= $wi400List->getIdList() ?>", "<?= $firstPage ?>", "<?= $prevPage ?>", "<?= $nextPage ?>", "<?= $lastPage ?>", "<?= $currentPage ?>", "<?= $totalPages ?>", <?= $noFocus ?>);
	refreshCustomFilter("<?= $wi400List->getIdList() ?>", "<?= $wi400List->getCurrentFilter() ?>");
	refreshTabIndex("<?= $wi400List->getIdList() ?>", <?= $tab_index ?>);
<? 
/*	// A column can be dragged --> Spostato dopo colonne fisse
	if ($canDrag != ""){
?>
		startDraggableColumn("<?= $wi400List->getIdList() ?>", "<?= $canDrag ?>", <?= $pageRows ?>);
<?	
	}*/
	if (isset($_GET['PAGINATION']) && $_GET['PAGINATION'] == 'EXPORT'){
?>
	alert("DEBUG: CHIAMATA ERRATA!");
<? 
	} else if (isset($_GET['PAGINATION']) && substr($_GET['PAGINATION'], 0, 7) == 'EXPORT:'){
		$form = "";
		if(isset($_GET['f'])){
			$form = "&f=".$_GET['f'];
		}
?>
		openWindow(_APP_BASE + APP_SCRIPT + "?DECORATION=lookUp&t=<?= substr($_GET['PAGINATION'], 7).$form ?>&EXP_LIST=<?= $wi400List->getIdList() ?>&IDLIST=<?= $wi400List->getIdList() ?>", "Esporta", 600, 450);
<?
	}
?>
	wi400Init();
<?
	if (isset($_GET['SCROLL']) && $_GET['SCROLL'] > 0){
?>
		var listScroll = document.getElementById("<?= $wi400List->getIdList() ?>Scroll");
		setTimeout(function(){listScroll.scrollLeft = <?= $_GET['SCROLL'] ?>}, 100);
<?
	}
	
	if ($wi400List->getTimer() > 0){
	    $reloadJsAction = "_PAGE_RELOAD";
	    if ($wi400List->getSubfile() != null){
	    	$reloadJsAction = "_PAGE_REGENERATE";
	    }
?>
	if (!window["<?= $wi400List->getIdList() ?>_timer_state"]){
		timerStart("<?= $wi400List->getIdList() ?>", <?= $wi400List->getTimer() ?>, <?= $reloadJsAction?>);
	}
<?
	}
	
	
?>

</script>
<?
	// Gestione errori Lista
	if (isset($messageContext) && sizeof($messageContext->getMessages("NOT_FIELD"))>0){
?>
	<div id="messagesPagination" style="visibility:hidden;overflow:hidden;position:absolute;display:none;"><br>
<?		
		$isFirstMessage = true;
		$messagesArray = array_reverse($messageContext->getMessages());
		foreach ($messagesArray as $messageKey => $messageObj){
			if ($messageObj[2] == ""){
				if ($isFirstMessage){
					$isFirstMessage = false;
				}
?>
				<div class="messageLabel_<?= $messageObj[0] ?>"><?= $messageObj[1] ?></div>
<?
			}
		}
		
	?>
	<br></div>
	<script>
		showMessages("messagesPagination", '<?= $messageContext->getSeverity('NOT_FIELD')?>');
	</script>
<?
	}
	$messageContext->removeMessages();
	
	// Resize Column per Sottoliste
	if ($wi400List->getResizeList()!="") {
	?>
	<script>
	resizeListColumn("<?= $wi400List->getResizeList() ?>", "<?= $wi400List->getIdList() ?>", "<?= $wi400List->getColumnResize() ?>","<?= $wi400List->getLevelList() ?>");
	</script>
	<?
	}
	// Colonne fisse
	if (sizeof($fixedArray)>0){
		//echo "<br>passo di qua<pre>";
		//print_r( $fixedValuesHtml);
		//echo "</pre>";
		$fixedHtml = "";
		if($wi400List->getShowTopScroll()) {
			$fixedHtml .= "<div class='doubleScroll2' style='height: 15px; background-color: white;'></div>";
		}
		$fixedHtml .= "<table width='100%' cellpadding=0 cellspacing=0 id='table_fixed_".$wi400List->getIdList()."'>";
		$altezza = $wi400List->getRowHeaderHeight();
		//if (sizeof($colsGroups)>0) $altezza=$altezza*2;
		$fixed_col_thead = true;
		
		$fixedHtml.= "<thead><tr class='wi400-grid-header-fixed' style='height:".$altezza."px'>";
		foreach ($fixedArray as $fixedKey => $fixedCol) {
			$fixedHtml.= $fixedHeaderColumn[$fixedKey];
			//$fixedHtml.= "<td class='wi400-grid-header-cell' style='cursor: pointer;' onmouseover='gridHeaderOver(this)' onmouseout='gridHeaderOut(this)' onclick='doOrder(\\\"".$wi400List->getIdList()."\\\",\\\"".$fixedKey."\\\", false);'>".utf8_encode($fixedCol->getDescription())."</td>";
		}
		$fixedHtml.= "</tr></thead><tbody>";

		$rowsCounter = 0;
		foreach ($fixedValuesArray as $mykey => $fixedRow){
			if ($fixedRow=="") {
				$fixedHtml.= "<tr><td colspan='100' class='wi400-grid-row-categories'></td></tr>";
				continue;
			}
			$pairStyle = "";
			if(count($fixedSelectedRows) && isset($fixedSelectedRows[$rowsCounter])) {
				$pairStyle = "wi400-grid-row_selected ";
			}
			$pairStyle .= $rowsCounter % 2 == 0 ? 'wi400-grid-row_pair' : '';
			//$fixedHtml.= "<tr class='wi400-grid-row '".$pairStyle."'>";	
			//$fixedHtml .= "<tr>";
			$fixedHtml .="<tr id='".$wi400List->getIdList()."-Fixed-".$rowsCounter."-tr' class='wi400-grid-row $pairStyle'>";
			foreach ($fixedArray as $fixedKey => $fixedCol) {
				//$fixedWidth = $fixedCol->getWidth();
				//if ($fixedWidth === 0) $fixedWidth = 150;
				if(isset($fixedValuesHtml[$mykey][$fixedKey])) {
					$fixedHtml.= $fixedValuesHtml[$mykey][$fixedKey];
				}
			}
			$fixedHtml.= "</tr>";
			$rowsCounter++;
		}
		$fixedHtml.= "</table>";
		//$fixedHtml = htmlspecialchars_decode(str_replace("\"","'",$fixedHtml));
		//$fixedHtml = htmlspecialchars($fixedHtml);
?>
		<script>
	    	/*mydoc = getFrameWindowById("<?= $wi400List->getIdList()?>_fixedContainer");
	    	var fixedContainer = document.getElementById("<?= $wi400List->getIdList()?>_fixedContainer");
		    if (mydoc.document.getElementById("<?= $wi400List->getIdList()?>_fixedContainer")) {
		    	fixedContainer = mydoc.document.getElementById("<?= $wi400List->getIdList()?>_fixedContainer");
		    }*/
		    //fixedContainer = getFrameWindowById2("<?= $wi400List->getIdList()?>_fixedContainer");
		    fixedContainer = getFrameWindowById("<?= $wi400List->getIdList()?>_fixedContainer", false, true);
			jQuery(fixedContainer).html(<?= json_encode($fixedHtml) ?>);
		    jQuery("#TD_<?= $wi400List->getIdList()?>_fixedContainer").show();
			// Altezza delle varie righe			
			resizeListRow('<?= $wi400List->getIdList()?>');
		</script>
		
<?		
	} else {
?>
		<script>
		jQuery("#TD_<?= $wi400List->getIdList()?>_fixedContainer").hide();
		</script>
<?php 		
	}
	
	/*if(isset($_SESSION['NAVIGAZIONE_TABLET_ATTIVA'])) { ?>
		<script>
			var objTabIndex = jQuery('input[tabindex]');
			if(objTabIndex.length > 1) {
				jQuery("#<?= $wi400List->getIdList() ?>_TAB_BUTTON_BACK").css('display', "block");
				jQuery("#<?= $wi400List->getIdList() ?>_TAB_BUTTON_NEXT").css('display', "block");
				focus_input = objTabIndex[0];
			}else {
				jQuery("#<?= $wi400List->getIdList() ?>_TAB_BUTTON_BACK").css('display', "none");
				jQuery("#<?= $wi400List->getIdList() ?>_TAB_BUTTON_NEXT").css('display', "none");
			}
		</script>
<?	}*/
		 
    // EVENTO PASTE
  if ($wi400List->getCanPaste()){
?>
  <script>
    var myElement = document.getElementById("table_<?= $wi400List->getIdList() ?>");
    var myElementFixed = document.getElementById("table_fixed_<?= $wi400List->getIdList() ?>");
    
    myElement.onpaste = function(e) {
		wi400_managePaste(e,"<?= $wi400List->getIdList() ?>","<?= $wi400List->getPastCallback() ?>",<?= $wi400List->getPageRows() ?>);
		return false;
    };
    if(myElementFixed) {
	    myElementFixed.onpaste = function(e) {
			wi400_managePaste(e, "<?= $wi400List->getIdList() ?>","<?= $wi400List->getPastCallback() ?>",<?= $wi400List->getPageRows() ?>);
			return false;
		};
    }
</script>
<?
 }
	// FINE EVENTO PASTE
 
	// A column can be dragged
	if (isset($canDrag) && $canDrag != ""){
		?>	<script>
			startDraggableColumn("<?= $wi400List->getIdList() ?>", "<?= $canDrag ?>", <?= $pageRows ?>);
			</script>
		<?	
		}
		
		if ($wi400List->getDraggableHeader() && $wi400List->getCanManage()) { 
		?>	
		<script>
	
		//jQuery('#marameo_table').dragtable({maxMovingRows:1});
	    jQuery(function () {
	        jQuery('th').resizable({
	            create: function (event, ui) {
	                var nodeName = this.nodeName.toUpperCase();
	                if (nodeName == 'TH' || nodeName == 'TD') {
	                    var self = jQuery(this);
	                    self.removeClass('ui-resizable');
	                    (self.data('ui-resizable') | self.data('resizable')).element = self.wrapInner('<div class="ui-resizable" style="position: relative;"/>');
	                    self.resizable('option', 'alsoResize', this.parentNode);
	                }
	            },
	            handles: 'e',
	            start: function() {
	            	jQuery('#<?= $wi400List->getIdList() ?>Container').css("width","");
	            },    
	            stop: function() {
	            	saveAjaxListConf('<?= $wi400List->getIdList() ?>');
	            	jQuery('#<?= $wi400List->getIdList() ?>Container').css("width",jQuery(".wi400-grid-header").width());
		        }    
	        });
			jQuery('#table_<?= $wi400List->getIdList() ?>').sorttable({
				items: '>:not(.nosort)',
				placeholder: 'placeholderList',
				//helperCells: null,
				stop: function (e, ui) {
					saveAjaxListConf('<?= $wi400List->getIdList() ?>');
					if(typeof(floatTheadTable) != "undefined") {
						setTimeout(function() {
							floatTheadTable.floatThead("reflow");
						}, 100);
					}
				}
//			}).disableSelection();
			});
			jQuery('#table_fixed_<?= $wi400List->getIdList() ?>').sorttable({
				items: '>:not(.nosort)',
				stop: function (e, ui) {
					saveAjaxListConf('<?= $wi400List->getIdList() ?>');
					if(typeof(floatTheadTable_fixed) != "undefined") {
						setTimeout(function() {
							floatTheadTable_fixed.floatThead("reflow");
						}, 100);
					}
				}
			});
	    });
       </script>
       <script>
			jQuery("#<?= $wi400List->getIdList() ?>Container").load( function() {
				jQuery("#<?= $wi400List->getIdList() ?>Container").height(jQuery("#<?= $wi400List->getIdList() ?>Container").contents().find("body").height());
			});
	</script>
		<?
		}
		if ($wi400List->getBlockScrollHeader()) {
			/*if ($wi400List->getBreakKey()!="") {
				die("blockScrollHeader not compatible with BreakKey");
			}*/
			?>
			<script>
<?	 			if($wi400List->getShowTopScroll()) {?>
					var stickyEl = jQuery(".doubleScroll-wrapper");
					var stick2 = jQuery(".doubleScroll2");
					var elTop = stickyEl.offset().top;
					var elBottom = jQuery(".double-scroll").innerHeight()+elTop;
					
					jQuery(window).bind("scroll.topScoll", function() {
						var current_top = jQuery(window).scrollTop();
						stickyEl.toggleClass("floatTopScroll", current_top > elTop && current_top < elBottom);
						stick2.css("width", jQuery("#<?=$wi400List->getIdList()?>_fixedContainer").width());
						stick2.toggleClass("floatTopScroll", current_top > elTop && current_top < elBottom);
					});
<?				}?>
				var floatTheadTable = jQuery("#table_<?= $wi400List->getIdList() ?>");
				var floatTheadOption = {
					position: 'absolute',
					top: <?= $wi400List->getShowTopScroll() ? 15 : 0?>,
					autoreflow: true,
					getSizingRow: function($table, $cols, $fthCells){ //Viene richiamata solo per ie. Ritorna i td di una riga
				      return $table.find('tbody tr:visible[class^="wi400-grid-row"]:first>*:visible');
				    }
				};
				setTimeout(function() {
					floatTheadTable.floatThead(floatTheadOption);
					jQuery('#'+window["AUTO_FOCUS_FIELD_ID"]).focus();
				}, 300);
				<?if(isset($fixed_col_thead)) {?>
					var floatTheadTable_fixed = jQuery("#table_fixed_<?= $wi400List->getIdList() ?>");
					setTimeout(function() {
						floatTheadTable_fixed.floatThead(floatTheadOption);
						jQuery('#'+window["AUTO_FOCUS_FIELD_ID"]).focus();
					}, 300);
				<?}?>
			</script>
			<? 
		}else {?>
			<script>
				jQuery(window).unbind('scroll.topScoll');
				jQuery(".doubleScroll-wrapper").removeClass("floatTopScroll");
			</script>
<?		}
	if ($wi400List->getIsEnabledCaching()) {
		$html = ob_get_contents();
		$wi400List->setCachedFile($html);
	}
	
	function strlen_pixels($text) { 
    /* 
        Pixels utilized by each char (Verdana, 10px, non-bold) 
        04: j 
        05: I\il,-./:; <espace> 
        06: J[]f() 
        07: t 
        08: _rz* 
        09: ?csvxy 
        10: Saeko0123456789$ 
        11: FKLPTXYZbdghnpqu 
        12: A�BCERV 
        13: <=DGHNOQU^+ 
        14: w 
        15: m 
        16: @MW 
    */ 

    // CREATING ARRAY $ps ('pixel size') 
    // Note 1: each key of array $ps is the ascii code of the char. 
    // Note 2: using $ps as GLOBAL can be a good idea, increase speed 
    // keys:    ascii-code 
    // values:  pixel size 

    // $t: array of arrays, temporary 
    $t[] = array_combine(array(106), array_fill(0, 1, 4)); 

    $t[] = array_combine(array(73,92,105,108,44), array_fill(0, 5, 5)); 
    $t[] = array_combine(array(45,46,47,58,59,32), array_fill(0, 6, 5)); 
    $t[] = array_combine(array(74,91,93,102,40,41), array_fill(0, 6, 6)); 
    $t[] = array_combine(array(116), array_fill(0, 1, 7)); 
    $t[] = array_combine(array(95,114,122,42), array_fill(0, 4, 8)); 
    $t[] = array_combine(array(63,99,115,118,120,121), array_fill(0, 6, 9)); 
    $t[] = array_combine(array(83,97,101,107), array_fill(0, 4, 10)); 
    $t[] = array_combine(array(111,48,49,50), array_fill(0, 4, 10)); 
    $t[] = array_combine(array(51,52,53,54,55,56,57,36), array_fill(0, 8, 10)); 
    $t[] = array_combine(array(70,75,76,80), array_fill(0, 4, 11)); 
    $t[] = array_combine(array(84,88,89,90,98), array_fill(0, 5, 11)); 
    $t[] = array_combine(array(100,103,104), array_fill(0, 3, 11)); 
    $t[] = array_combine(array(110,112,113,117), array_fill(0, 4, 11)); 
    $t[] = array_combine(array(65,195,135,66), array_fill(0, 4, 12)); 
    $t[] = array_combine(array(67,69,82,86), array_fill(0, 4, 12)); 
    $t[] = array_combine(array(78,79,81,85,94,43), array_fill(0, 6, 13)); 
    $t[] = array_combine(array(60,61,68,71,72), array_fill(0, 5, 13)); 
    $t[] = array_combine(array(119), array_fill(0, 1, 14)); 
    $t[] = array_combine(array(109), array_fill(0, 1, 15)); 
    $t[] = array_combine(array(64,77,87), array_fill(0, 3, 16));   
   
    // merge all temp arrays into $ps 
    $ps = array(); 
    foreach($t as $sub) $ps = $ps + $sub; 
   
    // USING ARRAY $ps 
    $total = 1; 
    for($i=0; $i<strlen($text); $i++) { 
        $temp = $ps[ord($text[$i])]; 
        if (!$temp) $temp = 10.5; // default size for 10px 
        $temp = $temp / 2.5;
        $total += $temp; 
    } 
    $total = $total + 4;
    return $total; 
}

if($wi400List->getSelectRowEveryWhere()) {
?>
	<script>
	jQuery(".wi400-grid-row").on("click", function(event){
		selectRowEveryWhere(this, event);
	});
	</script>
<?php
}
