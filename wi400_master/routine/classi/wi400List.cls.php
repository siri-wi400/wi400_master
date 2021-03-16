<?php

class wi400List {
	
	private $idList;
	private $idNumList;
	
	private $subfile;
	
	private $keys;
	private $breakKey = "";
	private $breakClass = "";
	private $breakFunction = "";
	private $cols;
	private $colsGroups;
	
	private $messages;
	private $totalRows = 0;
	private $pageRows = 0;
	private $maxPageRows = 0;
	
	private $actionCounter = 0;
	
	private $cfgFileName;
	
	private $title = "";
	
	private $startFrom = 0;
	
	private $showMenu = true;
	private $showActions = true;
	private $showTabletActions = "both";
	private $showTabletNumPages = true;
	private $showNumPages = true;
	private $showTopScroll = false;
	private $hideSelectRow = false;
	private $hideBottom = false;
	private $autoScroll = false;
	
	private $righeDiPagina = array();
	
	private $canExport;
	private $canImport;
	private $canReload;
	private $canFilter = true;
	private $canSQL = false;
	private $calculateTotalRows = "RELOAD";		// '', 'true', 'false', 'RELOAD', 'RELOAD_FIRST'
	private $queryCount = true;
	
	private $passKey = false;
	private $passValue = false;
	private $passDesc = false;
	
	private $filters = array ();
	private $listFilters = array ();
	private $hasFilters = false;
	
	private $userWhere = false;
	private $forceUserWhere = false;
	
	// Filtri personalizzati
	private $defaultFilter;
	private $currentFilter;
	private $customFilters = array ();
	
	
	// Tool personalizzato
	private $includePhp;
	
	private $query = "";
	
	private $select = "";
	private $from = "";
	private $field ="";
	private $where = "";
	private $order = "";
	private $group = "";
	
	private $sql = "";
	
	private $actions = array ();
	
	private $tools = array ();
	
	private $selection = "SINGLE"; // SINGLE, MULTIPLE
	private $selectionArray = array();
	private $autoSelection = false;
	private $helpTool = false;
	
	// Presentation
	private $rowHeight;
	private $rowHeaderHeight;
	
	private $columnsOrder = array();
	private $columnsFixed = array();
	private $columnsWidth = array();
	private $parameters;
	private $rowParameters;
	
	private $tree;
	private $isMapping = false;
	
	// Oggetto di riordino
	private $sortList;
	
	// subtotali filtrati
	private $totals = array();
	
	// stile riga
	private $style;
	
	// dettaglio per esportazione
	private $exportDetails = array();
	
	private $noDetails = false;
	
    // id dell'eventuale progress bar
	private $progressBar;
	
	// Lista è editabile
	private $editable;
	
	// Ricarica la lista automaticamente
	private $timer = 0;
	
	private $includeFile = "";
	private $columnsFix = array();
	
	private $detailAjax;
	private $detailHtml;
	private $detailCondition;
    private $detailAjaxStatic;	
    private $draggableHeader = True;
	
    
    public $headerValues;
    
    private $autoWidth = False;
    private $subTitle = "Sub.";
    private $passKeyJsFunction = "";
    private $currentQuery ="";
    private $showTitle= False;
    private $status = "";
    private $autoRowNumber = False;
    
    private $commonCondition = "";
    
    // DEFAULT true: filtro aggiunto normalmente (direttamente sulla query)
	// false: per permette di filtrare campi non esistenti in tabella (ridenominati AS), viene creata una query con WITH, da impostare per query libere ($wi400List->setQuery();) tranne che per query con WITH
    private $autoFilter = true;
    
    // DEFAULT false
    // true: per permettere in caso di $autofilter = false; di eseguire la query senza trasformarla in una query con with
    private $execute_like_query = false;
    
    // DEFAULT true: viene utilizzata la paginazione con BETWEEN
    // false: viene utilizzata la paginazione normale
    private $pagBetween = true;
    
    // Gestione operazioni da eseguire su change della lista
    private $onChangeChecked="";
    
    private $refreshFocus=False;
    private $showHeadFilter=False;
    private $blockScrollHeader=False; // Blocco scroll Heade
    private $protectAllFields=False;
    
    // Funzioni attaccate alla lista per reperire informazioni e validazioni
    //private $functionValidation="";
    //private $functionGetRow="";
    private $callBackFunction = array();
    private $callableFunctionArray = array(
    	"validation", 
		"validationRow", 
		"formatting", 
		"setRow", 
		"getRow", 
		"updateRow", 
		"reload", 
		"beforePagination", 
		"final", 
    	"afterFetch",
    	"onEmpty",
    	"inputCell",
    	"buttonChangePage"
    );
	private $runtimeField = array();
	private $autoUpdateList = False;
	private $autoUpdateEvent = "onChange";
	private $updateOnChangeRow = false;
	private $currentRow = array();
	private $currentCol = "";
	private $autoUpdateKey = array();
	
	private $exportFilterSel = true;
	private $exportFilterSelChecked = true;
	private $exportBatch = false;
	private $autoFocus = True;
	private $isEnabledCaching=False;
	private $isCached=False;
	private $canPaste=True;
	private $pastCallback="";
	private $pasteTrigger=True;
	private $rowArray=array();
	private $normalizeData=False;
	private $cacheData=False;
	private $scriptOnAutoUpdate = "";
	private $enableMovingWithKeys = false;
	private $azione;
	private $form;
	private $selectRowEveryWhere = false;
	private $hideHeader = false;
	private $resizeList=""; // Lista di riferimento per fare il resize
	private $levelList="0";
	private $columnResize="0"; // Numero di colonne da ripartizionare a partire dall'ultima
	private $boxStyle=""; // Stile del box contenitore della lista
	private $scrollTop="true"; // Posizionamento automatico sulla parte superiore della lista. Stringa perchè passa a JS
	private $silentLoad=false; // Caricamento "Silenzioso" della lista senza rotelle che girano
	private $immediateLoad=False; // Se caricare immediatamente la LISTA senza AJAX
	private $treeSubList=array(); // ARRAY CON LE LISTA COLLEGATE
	private $topNavigationBar = False;
	private $labelErrorCol = '';
	private $staticOrder = "";
	
	public function addTreeSubList($lista) {
		if (!isset($this->treeSubList[$lista])) {
			$this->treeSubList[$lista]=$lista;
		}
	}
	
	/**
	 * Serve per settare una descrizione alla colonna dei messageError della lista 
	 * (quella che compare quando c'è un punto esclamativo rosso in autoupdateRow)
	 * @param string $label
	 */
	public function setLabelErrorCol($label) {
		$this->labelErrorCol = $label;
	}
	
	/**
	 * Guarda il setLabelErrorCol per info
	 */
	public function getLabelErrorCol() {
		return $this->labelErrorCol;
	}
	
	public function setTopNavigationBar($topNavigationBar) {
		$this->topNavigationBar = $topNavigationBar;
	}
	public function getTopNavigationBar() {
		return $this->topNavigationBar;
	}
	public function getTreeSubList() {
		return $this->treeSubList;
	}
		
	public function setImmediateLoad($immediateLoad) {
		$this->immediateLoad= $immediateLoad;
	}
	public function getImmediateLoad() {
		return $this->immediateLoad;
	}
	
	public function setSilentLoad($silentLoad) {
		$this->silentLoad = $silentLoad;
	}
	public function getSilentLoad() {
		return $this->silentLoad;
	}
	
	public function setScrollTop($scrollTop) {
		$this->scrollTop = $scrollTop;
	}
	public function getScrolltop() {
		return $this->scrollTop;
	}
	
	public function setBoxStyle($boxStyle) {
		$this->boxStyle = $boxStyle;
	}
	public function getBoxStyle() {
		return $this->boxStyle;
	}
	/**
	 * Setta se la lista è collegata ad un'altra lista (Si inserisce la lista padre)
	 * @param unknown $resizeList
	 */
	public function setResizeList($resizeList, $levelList="0", $columnResize="0") {
		$this->resizeList=$resizeList;
		$this->levelList=$levelList;
		$this->columnResize=$columnResize;
		// VERIFICARE: Setto il numero di pagina a 999 dato che non avrò i componenti di lista
		$this->setPageRows(999);
		// Se c'è una resize List devo andare a scrivere sull'oggetto collegato la lista che sto aprendo
		$wi400List0 = wi400Session::load(wi400Session::$_TYPE_LIST, $resizeList);
		$wi400List0->addTreeSubList($this->idList);
		wi400Session::save(wi400Session::$_TYPE_LIST, $resizeList, $wi400List0);
	}
	public function getResizeList() {
		return $this->resizeList;
	}
	public function setLevelList($level) {
		$this->level=$level;
	}
	public function getLevelList() {
		return $this->levelList;
	}
	public function setColumnResize($columnResize) {
		$this->columnResize=$columnResize;
	}
	public function getColumnResize() {
		return $this->columnResize;
	}
	/**
	 * Nasconde le colonne dell'HEADER
	 * @param unknown $hideHeader
	 */
	public function setHideHeader($hideHeader) {
		$this->hideHeader = $hideHeader;
	}
	public function getHideHeader() {
		return $this->hideHeader;
	}
	/**
	 * @return the $normalizeData
	 */
	public function getNormalizeData() {
		return $this->normalizeData;
	}

	/**
	 * @return the $cacheData
	 */
	public function getCacheData() {
		return $this->cacheData;
	}

	/**
	 * @desc setNormalizeData: normalizza tutti i dati della lista da view a model automaticamente
	 * @param boolean $normalizeData
	 */
	public function setNormalizeData($normalizeData) {
		$this->normalizeData = $normalizeData;
	}

	/**
	 * @desc setCacheData: salva le righe caricate sul DB sull'oggetto lista per ottimizzare l'ajx update list
	 * @param boolean $cacheData
	 */
	public function setCacheData($cacheData) {
		$this->cacheData = $cacheData;
	}

	/**
	 * @return the $rowArray
	*/
	public function getRowArray($index) {
		return $this->rowArray[$index];
	}
	
	/**
	 * @param multitype: $rowArray
	 */
	public function setRowArray($rowArray, $index) {
		$this->rowArray[$index] = $rowArray;
	}
	
	/**
	 * @return the $pasteTrigger
	 */
	public function getPasteTrigger() {
		return $this->pasteTrigger;
	}
	/**
	 * @param boolean $pasteTrigger
	 */
	public function setPasteTrigger($pasteTrigger) {
		$this->pasteTrigger = $pasteTrigger;
	}
	/**
	 * @return the $pastCallback
	 */
	public function getPastCallback() {
		return $this->pastCallback;
	}
	/**
	 * @param string $pastCallback
	 */
	public function setPastCallback($pastCallback) {
		$this->pastCallback = $pastCallback;
	}
	/**
	 * @return the $canPaste
	 */
	public function getCanPaste() {
		return $this->canPaste;
	}
	/**
	 * @param boolean $canPaste
	 */
	public function setCanPaste($canPaste) {
		$this->canPaste = $canPaste;
	}
	/**
	 * @return the $isEnableCaching
	 */
	public function setCachedFile($cachedFile) {
		$file = wi400File::getSessionFile(session_id(), "cache\\".$this->idList);	
		$handle = fopen($file, "w");
		fwrite($handle, $cachedFile);	
		fclose($handle);	
		$this->setisCached(True);
	}
	/**
	 * @return the $isEnableCaching
	 */
	public function getCachedFile() {
		$file = wi400File::getSessionFile(session_id(), "\\cache\\".$this->id);
		return file_get_contents($file);
	}
	/**
	 * @return the $isEnableCaching
	 */
	public function getIsEnabledCaching() {
		return $this->isEnabledCaching;
	}

	/**
	 * @return the $isCached
	 */
	public function getIsCached() {
		return $this->isCached;
	}

	/**
	 * @param boolean $isEnableCaching
	 */
	public function setIsEnabledCaching($isEnabledCaching) {
		$this->isEnabledCaching = $isEnabledCaching;
	}

	/**
	 * @param field_type $isCached
	 */
	public function setIsCached($isCached) {
		$this->isCached = $isCached;
	}

	/**
	 * @return the $autoFocus
	 */
	public function getAutoFocus() {
		return $this->autoFocus;
	}

	/**
	 * @desc setAutoFocus: Imposta se la lista ha l'autofocus automatico, default True
	 * @param boolean $autoFocus
	 */
	public function setAutoFocus($autoFocus) {
		$this->autoFocus = $autoFocus;
	}

	/**
	 * @return the $autoUpdateKey
	 */
	public function getAutoUpdateKey() {
		return $this->autoUpdateKey;
	}

	/**
	 * @param multitype: $autoUpdateKey
	 */
	public function setAutoUpdateKey($autoUpdateKey) {
		$this->autoUpdateKey[$autoUpdateKey] = $autoUpdateKey;
	}

	/**
	 * @return the $currentRow
	 */
	public function getCurrentRow() {
		return $this->currentRow;
	}

	/**
	 * @param multitype: $currentRow
	 */
	public function setCurrentRow($currentRow) {
		$this->currentRow = $currentRow;
	}
	/**
	 * @param multitype: $currentRow
	 */
	public function setCurrentRowValue($key, $value) {
		$this->currentRow[$key] = $value;
	}
	
	public function setCurrentCol($columnObj) {
		$this->currentCol = $columnObj;
	}
	
	public function getCurrentCol() {
		return $this->currentCol;
	}
	
	/**
	 * @return the $autoUpdateEvent
	 */
	public function getAutoUpdateEvent() {
		return $this->autoUpdateEvent;
	}
	/**
	 * @param string $autoUpdateEvent
	 */
	public function setAutoUpdateEvent($autoUpdateEvent) {
		$this->autoUpdateEvent = $autoUpdateEvent;
	}
	/**
	 * @desc getAutoUpdateList: recupero se la lista effettua l'auto aggiornamento dei campi e la validation
	 * @return the $autoUpdateList
	 */
	public function getAutoUpdateList() {
		return $this->autoUpdateList;
	}

	/**
	 * @desc setAutoUpdateList: imposta se la lista effettua l'auto update e l'agg. dei campo e il validation
	 * @param boolean $autoUpdateList
	 */
	public function setAutoUpdateList($autoUpdateList) {
		$this->autoUpdateList = $autoUpdateList;
	}
	
	/**
	 * La lista se è in modalità autoUpdate aggiornerà la riga nel momento in cui il focus si sposterà in un'altra riga
	 * 
	 * @param boolean $val
	 */
	public function setUpdateOnChangeRow($val) {
		$this->updateOnChangeRow = $val;
	}
	
	/**
	 * Ritorna true se l'autoUpdate viene richiamato nel momento in cui il focus si sposta da una riga all'altra
	 *
	 * @return boolean
	 */
	public function getUpdateOnChangeRow() {
		return $this->updateOnChangeRow;
	}

	/**
	 * @return the $value 
	 */
	public function getRuntimeField($field) {
		if (isset($this->runtimeField[$field])) {
			return $this->runtimeField[$field];
		} else {
			return false;
		}
	
	}
	/**
	 * @desc setRuntimeField: setta una variabile runtime da legare alla lista
	 *       action= Azione da eseguire al copletamento
	 *       message= Messaggio da mostrare dopo la paginazione
	 *       updateHTML = Aggiorna HTML riga dopo un autoUpdate
	 * @param multitype: set runtime field
	 * 
	 */
	public function setRuntimeField($field , $value) {
		$this->runtimeField[$field] = $value;
	}
	/**
	 * @return the $call_back_function
	 */
	public function getCallBackFunction($callable) {
		if (isset($this->callBackFunction[$callable])) {
			return $this->callBackFunction[$callable];
		} else {
			return false;
		}
		
	}
	/**
	 * @param multitype: $call_back_function
	 */
	public function setCallBackFunction($callable , $callBackFunction) {
		if (!in_array($callable, $this->callableFunctionArray)) die("Callable Function not implementable!!");
		$this->callBackFunction[$callable] = $callBackFunction;
	}

	/**
     * @desc setProtectAllFields: Protegge tutte le colonne di input
     * @param bool $protectAllFields: True/False
     */
    public function setProtectAllFields($protectAllFields) {
    	$this->protectAllFields = $protectAllFields;
    }
    /**
     * @desc getProtectAllFields: Verifica se tutte le colonne devono essere protette
     * @param bool $protectAllFields: True/False
     * @return boolean
     */
    public function getProtectAllFields() {
    	return $this->protectAllFields;
    }
    /**
     * @desc setBlockScrollHeader: Blocca lo scorrimento delle celle Header (Experimental)
     *       ** Attenzione **  Questa funzione non è compatibile con setBreakKey() per COLSPAN=100
     * @param bool $blockScrollHeader: True/False 
     * @return boolean
     */
    public function setBlockScrollHeader($blockScrollHeader) {
    	$this->blockScrollHeader = $blockScrollHeader;
    }
    /**
     * @desc getBlockScrollHeader: Verifico se è abilitato il blocco delle celle di Header
     * @return boolean
     */
    public function getBlockScrollHeader() {
    	return $this->blockScrollHeader;
    }
    /**
     * setRefreshfocus() Imposta se il focus deve rimanere sull'ultimo campo di input toccato
     * @param unknown $focus
     */
    public function setRefreshFocus($focus) {
    	$this->refreshFocus = $focus;
    	//unset($_SESSION['LAST_FOCUSED_FIELD']);
    	//unset($_SESSION['LAST_LAST_FOCUSED_FIELD']);
    	//unset($_SESSION['LAST_FOCUSED_TAB']);
    }
    public function getRefreshFocus() {
    	return $this->refreshFocus;
    }
    public function getOnChangeChecked() {
    	return $this->onChangeChecked;
    }
    public function setOnChangeChecked($onChange) {
    	$this->onChangeChecked = $onChange;
    }
    /**
     * @desc setAutoRowNumber: Imposta se la lista automaticamente fissa il numero di righe visibile in base al numero di righe lette
     * @param boolean $showTitle
     */
    public function setAutoRowNumber($autoRowNumber) {
    	$this->autoRowNumber=$autoRowNumber;
    }
    /**
     * @desc getAutoRowNumber: Reperisce se la lista automaticamente fissa il numero di righe visibile in base al numero di righe lette
     * @return boolean $showTitl
     */
    public function getAutoRowNumber() {
    	return $this->autoRowNumber;
    }
    /**
     * @desc setShowTitle: Imposta la visualizzazione o meno sul titolo della lista
     * @param boolean $showTitle
     */
    public function setShowTitle($showTitle) {
    	$this->showTitle=$showTitle;
    }
    /**
     * @desc getShowTitle: Reperisce se devo visualizzare il titolo della lista
     * @return boolean $showTitl
     */
    public function getShowTitle() {
    	return $this->showTitle;
    }
    /**
     * setDraggableHeader() Imposta se nella lista posso spostare le colonne con il drag & Drop, non funzione con campi di input presenti
     * @param boolean $draggableHeader  True/False
     */
    public function setDraggableHeader($draggableHeader) {
    	$this->draggableHeader = $draggableHeader;
    }
    /**
     * getDraggableHeader() Reperisce se le colonne possono essere riordinate tramite Drag&Drop
     * @return boolean $draggableHeader  True/False
     */
    public function getDraggableHeader() {
    	return $this->draggableHeader;
    }
    /**
     * @desc getStatus(): Reperisce lo stato della lista, aperta o chiusa
     * @param unknown $status
     */
    function getStatus() {
    	return $this->status;
    }
    /**
     * @desc setStatus(): Setta lo stato della lista, aperta o chiusa
     * @param unknown $status
     */
    function setStatus($status) {
    	$this->status = $status;
    }
    public function getType() {
    	return "LIST";
    }
    
    public function setId($idList) {
    	$this->idList = $idList;
    }
    
    public function getId() {
    	return $this->idList;
    }
    
    public function getSaveFile() {
    	return "";
    }
    public function getValue() {
    	return "";
    }
    public function getSource() {
    	return "";
    }
    public function getSaveSession() {
    	return "";
    }
    public function getLabel() {
    	return "";
    }
    public function getDecode() {
    	return "";
    }
    public function setIdDetail($idDetail) {
    	$this->idDetail=$idDetail;
    }
    public function getFromArray() {
    	return array();
    }
    /**
     * Setta la modalità di visualizzazione del menu delle azioni
     *
     * @param string $showActions booleano che indica se visualizzare o meno la scelta delle azioni
     */
    public function setShowActions($showActions) {
    	$this->showActions = $showActions;
    }
    
    public function getShowActions() {
    	return $this->showActions;
    }
    
    /**
     * se a true i bottoni e il numero di pagina non vengono visualizzati
     * 
     * @param boolean $val
     */
    public function setShowTabletNumPages($val) {
    	$this->showTabletNumPages = $val;
    }
    
    /**
     * Ritorna false se i bottone e il numero di pagina non sono visualizzati
     * 
     * @return boolean
     */
    public function getShowTabletNumPages() {
    	return $this->showTabletNumPages;
    }
    
    /**
     * Nascondere o meno il numero di pagina
     * 
     * @param boolean
     */
    public function setShowNumPages($val) {
    	$this->showNumPages = $val;
    }
    
    /**
     * Ritorna se il numero di pagina è nascosto o meno
     *
     * @return boolean
     */
    public function getShowNumPages() {
    	return $this->showNumPages;
    }
    
    /**
     * Metodo per decidere in quale punto visualizzare i bottoni delle azioni
     * top: sopra alla lista con bottoni blu
     * bottom: sotto alla lista con un campo di selezione
     * both: sia sopra che sotto
     * false: le azioni non vengono visualizzate
     *
     * @param string $val
     */
    public function setShowTabletActions($val) {
    	$this->showTabletActions = $val;
    }
    
    /**
     * Ritorna il metodo in cui vengono visualizzate le azioni
     *
     * @return string
     */
    public function getShowTabletActions() {
    	return $this->showTabletActions;
    }
    
    /**
     * @desc true => visualizza la scroll orizzontale anche sopra la lista
     *
     * @param boolean
     */
    public function setShowTopScroll($val) {
    	$this->showTopScroll = $val;
    }
    
    /**
     * @desc Ritorna true se la scroll orizzontale è presente anche sopra la lista
     *
     * @return boolean
     */
    public function getShowTopScroll() {
    	return $this->showTopScroll;
    }
    
    /**
     * @desc true => nasconde i checkBox per selezionare la riga
     * 
     * @param boolean
     */
    public function setHideSelectRow($val) {
    	$this->hideSelectRow = $val;
    }
    
    /**
     * @desc true => nasconde i checkBox per selezionare la riga
     *
     * @return boolean
     */
    public function getHideSelectRow() {
    	return $this->hideSelectRow;
    }

    /**
     * Nascondere i tool di lista, azioni e paginazione
     * ALL => nascondi tutto
     * TOOL => nasconde i tool
     * ACTION => nasconde le azioni
     * PAGE => nasconde le pagine
     * 
     * @param string $val
     */
    public function setHideBottom($val) {
    	$this->hideBottom = $val;
    	switch($val) {
    		case 'ALL': $this->setShowMenu(false);
    					$this->setShowActions(false);
    					$this->setShowNumPages(false);
    					break;
    		case 'TOOL': $this->setShowMenu(false); break;
    		case 'ACTION': $this->setShowActions(false); break;
    		case 'PAGE': $this->setShowNumPages(false); break;
    	}
    }
    
    /**
     * Nascondere i tool di lista, azioni e paginazione. 
     * Ritorna i seguenti valori:
	 * 		ALL => nascondi tutto
	 * 		TOOL => nasconde i tool
	 *		ACTION => nasconde le azioni
	 *		PAGE => nasconde le pagine
     *
     * @param void 
     * @return string
     */
    public function getHideBottom() {
    	return $this->hideBottom;
    }
    
    /**
     * true => Nascondere la scroll bar quando il contenuto non sfora
     * default => false
     * 
     * @param boolean $val
     */
    public function setAutoScroll($val) {
    	$this->autoScroll = $val;
    }
    
    /**
     * Ritorna true se nascondere la scroll bar quando il contenuto non sfora
     * 
     * @return boolean
     */
    public function getAutoScroll() {
    	return $this->autoScroll;
    }
    
    /**
     * @return Ritorno il valore dell'ultima query utilizzata per leggere la lista
     */
    public function getCurrentQuery() {
    
    	return $this->currentQuery;
    }
    
    /**
     * @param Query
     */
    public function setCurrentQuery($query) {
    	$this->currentQuery = $query;
    }    
    /**
     * @return Sumbit dell'azione sottostante alla chiusura del lookup
     */
    public function getPassKeyJsFunction() {
    
    	return $this->passKeyJsFunction;
    }
    
    /**
     * @param field_type $title
     */
    public function setPassKeyJsFunction($passKeyJsFunction) {
    	$this->passKeyJsFunction = $passKeyJsFunction;
    }
//
    /**
     * @return the $title
     */
    public function getSubTitle() {
    
    		return $this->subTitle;
    }
    
    /**
     * @param field_type $title
     */
    public function setSubTitle($title) {
    	$this->subTitle = $title;
    }
//    
	/**
	 * @return the $headerValues
	 */
	public function getHeaderValue($colKey, $default = 0) {
		
		if (isset($this->headerValues[$colKey])){
			return $this->headerValues[$colKey];
		}else{
			return $default;
		}
	}

	/**
	 * @param field_type $headerValues
	 */
	public function setHeaderValue($colKey, $headerValue) {
		$this->headerValues[$colKey] = $headerValue;
	}


	/**
	 * @return the $rowHeaderHeight
	 */
	public function getRowHeaderHeight() {
		return $this->rowHeaderHeight;
	}

	/**
	 * @param field_type $rowHeaderHeight
	 */
	public function setRowHeaderHeight($rowHeaderHeight) {
		$this->rowHeaderHeight = $rowHeaderHeight;
	}

	function setIncludeFile($moduleName, $fileName){
		global $root_path;
//		$this->includeFile = $root_path."modules/".$moduleName."/".$fileName;

		$path = p13n("modules/".$moduleName."/".$fileName);
		$this->includeFile = $path;
	}
	
	function getIncludeFile(){
		return $this->includeFile;
	}
	
    public function setProgressBar($progressBarId){
    	$this->progressBar = $progressBarId;
    }
    
    public function getProgressBar(){
    	return $this->progressBar;
    }
	
    public function getAutoWidth(){
    	return $this->autoWidth;
    }
    
    public function setAutoWidth($autoWidth){
    	$this->autoWidth = $autoWidth; 
    }
        
	public function getTimer(){
    	return $this->timer;
    }
    
    public function setTimer($timer){
    	$this->timer = $timer; 
    }
    
	public function getDetailHtml(){
    	return $this->detailHtml;
    }
    
    public function setDetailHtml($detailHtml){
    	$this->detailHtml = $detailHtml; 
    }
    
	public function getDetailAjax(){
    	return $this->detailAjax;
    }
	public function getDetailAjaxStatic(){
    	return $this->detailAjaxStatic;
    }    
    public function setDetailAjax($detailAjax, $static=true){
    	$this->detailAjax = $detailAjax; 
    	$this->detailAjaxStatic = $static;
    }
    
	public function getStyle(){
    	return $this->style;
    }
    
    public function setStyle($style){
    	$this->style = $style; 
    }
    
    public function isEditable(){
    	return $this->editable;
    	
    }
    
    public function setEditable($val) {
    	$this->editable = $val;
    }
    
    public function getExportDetails(){
    	return $this->exportDetails;
    }
    
    public function setExportDetails($exportDetails){
    	$this->exportDetails = $exportDetails; 
    }
	/**
	 * @desc Recupera l'array dei valori originali usati per caricare la pagina
	 * 
	 * @param integer: (opzionale) numero di riga
	 * 
	 * @return array di array per tutti i valori, array per singola riga
	 */	
    public function getRigheDiPagina($row=-1){
    	if ($row>= 0) {
    		return $this->righeDiPagina[$row];
    	} else {
    		return $this->righeDiPagina; 
    	}
    }
	/**
	 * @desc Imposta i dati utilizzati per caricare la pagina corrente
	 * 
	 * @param array: valori originali di pagina

	 */	    
    public function setRigheDiPagina($righeDiPagina){
    	$this->righeDiPagina = $righeDiPagina; 
    }    
    //
    public function addExportDetail($exportDetail) {
    	$this->exportDetails[] = $exportDetail;
    }
    
    public function setNoDetails($noDetails = false) {
    	$this->noDetails = $noDetails;
    }
    
    public function getNoDetails() {
    	return $this->noDetails;
    }
	
	/**
	 * E' possibile indicare un subtotale e il suo valore come formula. Se non indicato verrà inserita la somma della colonna.
	 * 
	 * @param string $columnId    Id univoco identificativo della colonna
	 * @param string $totaleValue Formula che calcola il totale nel formato EVAL:[nomecolonna]*x/ecc.ecc.
	 */
	public function addTotal($columnId, $totaleValue = 0){
		$this->totals[$columnId] = $totaleValue;
    }
    
    public function setTotals($totals){
    	$this->totals = $totals;
    }
    
    public function getTotals(){
    	return $this->totals;
    }
	
    public function setIncludePhp($phpPage, $options = array()){
    	$this->includePhp["INCLUDE_PHP"] = $phpPage; 
    	foreach ($options as $key => $val){
    		$this->includePhp[$key] = $val;
    	}
    }
    
    public function getIncludePhp($option = "INCLUDE_PHP"){
    	if (isset($this->includePhp[$option])){
    		return $this->includePhp[$option];
    	}else{
    		return false;
    	}
    	
    }
    
    
	/**
	 * Il costruttore setta i parametri per il collegamento all'AS400
	 * 
	 * @param string $idList      Id univoco identificativo della lista
	 * @param string $cleanSession Pulisce dalla sessione i dati relativi alla lista
	 */
	public function __construct($idList = "", $cleanSession = false) {
		global $settings, $actionContext, $menuContext, $lookUpContext;
		$this->idList = $idList;
		
		$this->from = "";
		$this->field = "*";
		$this->cols = array();
		$this->calculateTotalRows = "RELOAD";
		$this->queryCount = true;
		
		$this->parameters = array ();
		$this->rowParameters = array();
		$this->keys = array ();
		
		// Pulizia dati lista dalla sessione
		if ($cleanSession) {
			//$_SESSION [$idList] = null;
			unset($_SESSION['LAST_FOCUSED_FIELD']);
			wi400Session::delete(wi400Session::$_TYPE_LIST, $idList);
		}
		//Recupero di default titolo della pagina
		$this->setTitle ( $actionContext->getLabel () );
		
		$this->cfgFileName = $idList;
		
		$this->messages = array();
		$this->colsGroups = array();
		
		$this->canExport    = true;
		$this->canImport    = false;
		$this->canReload    = true;
		$this->canManage    = true;
		$this->canFilter    = true;
		if(isset($_SESSION ["WI400_GROUPS"]) && in_array("VIEW_SQL", $_SESSION ["WI400_GROUPS"])) {
			$this->canSQL = true;
		}
		$this->ManageOnlyNumRows = false;
		
		$this->exportDetails = array();
		$this->noDetails = false;
		$this->progressBar = "";
		
		// Controllo se la lista è contenuta in un lookup. In questo caso forzo le righe a 10 per pagina
		if (isset ( $_REQUEST ["DECORATION"] ) && $_REQUEST ["DECORATION"] == "lookUp") {
			$this->pageRows = $settings['wi400_lookup_rows'];
		} else {
			$this->pageRows = $settings['wi400_grid_rows'];
		}
		
		$this->subfile = null;
		$this->editable = false;
		
		$this->detailAjax = "";
		$this->detailHtml = "";
		$this->includePhp = array();
		
		$this->rowHeight = 26;
		$this->rowHeaderHeight = 26;
		
		$this->headerValues = array();
		
		$this->azione=$actionContext->getAction();
		$this->form=$actionContext->getForm();
		
		if(isset($settings['filter_user_where']))
			$this->userWhere = $settings['filter_user_where'];
		if(isset($settings['filter_force_user_where']))
			$this->forceUserWhere = $settings['filter_force_user_where'];
		
		if($idList && (isset($settings['check_field_enable_on_detail']) && $settings['check_field_enable_on_detail']===true)) {
			$this->checkSettingsAllList();
		}
	}
	
	public static function applyEval($value, $arrayObj, $arrayParam = array()){
		if (strpos($value, "EVAL:")===0){
			$evalValue = substr($value,5).";";
			foreach ($arrayObj as $search => $replace){
				//TODO:controllare PHP Notice:  Array to string conversion in /www/zendcore/htdocs/WI400_VPORRAZZO/routine/classi/wi400List.cls.php on line 209
//				echo "SEARC:<pre>"; print_r($search); echo "</pre>"; 
//				echo "REPLACE:<pre>"; print_r($replace); echo "</pre>"; 
//				echo "VAL:<pre>"; print_r($evalValue); echo "</pre>";		
				// Il problema sembra essere che in alcuni casi $replace è un array vuoto, mentre ci si aspetta una stringa
				$pos = strpos($evalValue, "[".$search."]");
				if ($pos !==False) {
					if (!is_numeric($replace)) {
						developer_debug("EVAL TOTAL:$serach non valorizzato!");
						return " -ERR -";
					}
				}
				$evalValue = str_replace("[".$search."]",$replace,$evalValue);
			}
			foreach ($arrayParam as $search => $replace){
				if (!is_array($replace)){
					$evalValue = str_replace("{".$search."}",$replace,$evalValue);
				}
			}
			preg_match_all("/\[[^\]]*\]/", $evalValue, $matches);
			if (count($matches[0])>0) {
				developer_debug("EVAL TOTAL:$evalValue ->Campi non sostituiti");
				return " -ERR-";
			}
			eval('$value='.$evalValue);
		}
		return $value;
	}
	
	public static function applyFormat($value, $format = ""){
//		echo "VALUE:".$value."_FORMAT:".$format."<br>";
		if ($format != ""){
		
			if (is_array($format)){
				$format_function = $format[0];
				array_shift($format);
				$parameters = array_merge(array($value), $format);
			}else{
				//Retrocompatibilità
				$parts = explode("|",$format);
				$num_parts = count($parts);
				$format_function = $parts[0];
				$parameters = array();
				$parameters[] = $value;
				if($num_parts>1) {
					for($i=1;$i<count($parts);$i++) {
						$parameters[] = $parts[$i];
					}
				}
			}
			
//			echo "PARAMETERS:<pre>"; print_r($parameters); echo "</pre>";
			
			if (is_callable("wi400_format_".$format_function,false)){
				$value = call_user_func_array("wi400_format_".$format_function, $parameters);
			}else{
				echo "<br>Funzione di formattazione wi400_format_".$format_function." non implementata.";
				exit();
			}
		}
		return $value;
	}

	public static function applyDecode($value, $decodeParameters = ""){
		global $base_path, $settings;
		if ($decodeParameters != ""){
			$decodeType = "table";
			if (isset($decodeParameters["TYPE"])){
				$decodeType = $decodeParameters["TYPE"];
			}
			//require_once $routine_path.'/decoding/siad/'.$decodeType.".php";
			//require_once $base_path.'/package/'.$settings['package'].'/decodingclass/'.$decodeType.".php";
		    require_once p13nPackage($decodeType);
		    		
			$decodeClass = new $decodeType();
			$decodeClass->setFieldValue($value);
			$decodeClass->setDecodeParameters($decodeParameters);
			
			$decodeResult = $decodeClass->decode();
			if ($decodeResult && !is_array($decodeResult)){
				$value = $decodeResult;
			}
		}
		return $value;
	}
	
	/**
	 * Ritorna il valore dell'attributo isMapping
	 * 
	 * @param void
	 * @return boolean
	 */
	public function getIsMapping() {
		return $this->isMapping;
	}
	
	public static function applyDecorator($value, $decorator = "", $parameters = array()){
		if ($decorator != ""){
			if (is_callable("wi400_decorator_".$decorator,false)){
				$value = call_user_func("wi400_decorator_".$decorator, $value, $parameters);
			}else{
				echo "<br>Funzione di decorazione wi400_decorator_".$decorator." non implementata.";
				exit();
			}
		}
		return $value;
	}
	
	private function checkSettingsAllList() {
		global $actionContext, $db, $settings;
		
		$curr_azione = $actionContext->getAction();
		if(!isset($settings['check_field_enable_on_detail']) || $settings['check_field_enable_on_detail']===true) {
			$chiave_cache = $curr_azione."|".$this->idList."|L";
			
			$cache_file = wi400File::getCommonFile("checkFieldEnabled", $_SESSION['user']."_".session_id());
			$parametri_array = null;
			if (file_exists($cache_file)) {
				$parametri_array = unserialize(file_get_contents($cache_file));
			}
			
			if ($parametri_array == null) {
				put_serialized_file($cache_file, array());
				$parametri_array= array();
			}
			
			if(isset($parametri_array[$chiave_cache])) {
				$this->isMapping = $parametri_array[$chiave_cache];
			}else {
				//echo "setting_lista_query<br/>";
				$query = "SELECT WIDKEY FROM ZWIDETPA WHERE WIDAZI='$curr_azione' AND WIDID='{$this->idList}' AND WIDDOL='L'";
				$rs = $db->singleQuery($query);
				if($row = $db->fetch_array($rs))
					$this->isMapping = true;
				
				$parametri_array[$chiave_cache] = $this->isMapping;
				put_serialized_file($cache_file, $parametri_array);
			}
			
			if($this->isMapping) {
				$checkTools = array("Reload", "Filtri", "Tree", "Esportazione", "Sql", "Configura lista", "Filtro testata", "Scroll testata", "Numero righe", "Nascondi lista");
				$func_key = array("setCanReload", "setCanFilter", "setTree", "setCanExport", "setCanSQL", "setCanManage", "setShowHeadFilter", "setBlockScrollHeader", "setPageRows", "setStatus");
				foreach($checkTools as $chiave => $valore) {
					$func = $func_key[$chiave];
					$check = checkFieldEnableOnDetail($curr_azione, $this->idList, $valore, "L", "TOOL");
					if(is_array($check)) {
						if($check[0] && $check[1]) {
							$this->$func($check[1]);
						}
					}else {
						$this->$func($check);
					}
				}
			}else {
			//	echo $this->idList."_non_mappatooo<br/>";
			}
		}
	}
	
	public function getHeaderAction($col){
		if ($col->getHeaderAction() != null && $col->getHeaderAction() != ""){
			$colKey = $col->getKey();
			$colIcons = $col->getHeaderIco();
			$colValue = $this->getHeaderValue($colKey, 1);
			$colImg = new wi400Image($colKey."_IMG");
			$target = $col->getHeaderTarget();
			$colImg->setUrl($colIcons[$colValue]);
			return "<span onClick='doHeader($colValue,\"".$this->getIdList()."\", \"".$colKey."\", \"".$col->getHeaderAction()."\", \"".$col->getHeaderForm()."\", \"".$col->getHeaderCallBack()."\",\"".$target."\"); event.stopPropagation();'>".$colImg->getHtml()."</span>";
		}
		return "";
	}
	
	function setQuery($sql="") {
		$this->sql = $sql;
	}
	
	function getQuery() {
		return $this->sql;
	}
	
	function getSql($field=null) {
		if (!isset($field)) $field = $this->field;
		$query = "SELECT " . $field . " FROM " . $this->getFrom () . " ";
		if ($this->getWhere () != "") {
			$query = $query . " WHERE " . $this->getWhere ();
		}
		if ($this->getGroup () != "") {
			$query = $query . " GROUP BY " . $this->getGroup();
		}
		return $query;
	}
	// @TODOODBC Portare questa funzione generica sul COMMON
	function isEmpty($filter = "", $value = "", $otherWhere="") {
		global $db;
		$query = "SELECT ".$this->field." FROM " . $this->getFrom () . " ";
		//static $stmt;
		
		if (! isset ( $stmt )) {
			if ($this->getWhere () != "" || $filter != "") {
				$query = $query . " WHERE " . $this->getWhere ();
				if ($filter != "") {
					if ($this->getWhere () != "") {
						$query = $query . " AND ";
					}
					$query = $query . $filter . "=?";
				}
				if ($otherWhere != "") {
					$query .= $otherWhere;
				}
			}
			$stmt = $db->prepareStatement ( $query, 1, True );
		}
		//$resultSet = $db->singleQuery($query);
		$result = $db->execute ( $stmt, $value );
		if ($result) {
			if ($db->fetch_array ( $stmt )) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
	
	public function setColumnsOrder($columnsOrder) {
		$this->columnsOrder = $columnsOrder;
		if (sizeof ( $this->cols ) > 0) {
			foreach ( $this->cols as $col ) {
				$col->setShow ( false );
			}
			foreach ( $this->columnsOrder as $colShow ) {
				if (isset ( $this->cols [$colShow] )) {
					$this->cols [$colShow]->setShow ( true );
				}
			}
		}
	}
	/**
	 * @desc setColumnsWidth() Setta la larghezza delle colonne
	 * @param unknown $columnsWidth
	 */
	public function setColumnsWidth($columnsWidth) {
			$x=0;
			foreach ( $this->columnsOrder as $colShow ) {
				if (isset ( $this->cols [$colShow] ) && isset($columnsWidth[$x])) {
					//$this->cols [$colShow]->setWidth ($columnsWidth[$x]);
					$this->columnsWidth[$colShow] = $columnsWidth[$x];
					//print_r($this->columnsWidth);
				}
				$x++;
			}
	}	
	public function setColumnsFix($columnsFix) {
		if (is_array($columnsFix)) {
			$this->columnsFixed = $columnsFix;
			if (sizeof ( $this->cols ) > 0) {
				foreach ( $this->cols as $col ) {
					if (in_array($col->getKey(), $columnsFix)){
						$col->setFixed(true);
					}else{
						$col->setFixed(false);
					}
				}
			}
		}
	}
	
	/**
	 * @desc Definisce un gruppo di colonne indicando
	 * 
	 * @param string $groupName    	   Id identificativo che verra utilizzato nel setGroup delle colonne
	 * @param string $groupDescription Descrizione che comparirà come testata del gruppo delle colonne
	 * @param string $groupColor 	   Colore della testata del gruppo delle colonne
	 */
	public function addColGroup($groupName, $groupDescription, $groupColor = "blu"){
		if (!isset($this->colsGroups[$groupName])) $this->colsGroups[$groupName] = array();
		$this->colsGroups[$groupName]["DESCRIPTION"] = $groupDescription;
		$this->colsGroups[$groupName]["COLOR"] 		= $groupColor;
	}
	
	public function getGroupDescription($groupName){
		if (isset($this->colsGroups[$groupName]) && isset($this->colsGroups[$groupName]["DESCRIPTION"])){
			return $this->colsGroups[$groupName]["DESCRIPTION"];
		}else{
			return $groupName;
		}
	}
	
	public function getGroupDescriptions() {
		$descriptions = array();
		foreach($this->colsGroups as $groupName => $group) {
			$descriptions[$groupName] = $group['DESCRIPTION'];
		}
		return $descriptions;
	}
	
	public function getGroupColor($groupName){
		if (isset($this->colsGroups[$groupName]) && isset($this->colsGroups[$groupName]["COLOR"])){
			return $this->colsGroups[$groupName]["COLOR"];
		}else{
			return "";
		}
	}
	
	public function getColsGroups(){
		return $this->colsGroups;
	}
	
	/**
	 * Aggiunge un parametro alla lista che verrà passato in request come hidden field.
	 * 
	 * @param string $parameterKey     Nome dell'input type hidden
	 * @param string $parameterValue   Valore del campo hidden
	 */
	public function addParameter($parameterKey, $parameterValue) {
		$this->parameters [$parameterKey] = $parameterValue;
	}

	
	public function addRowParameter($parameterKey, $parameterValue = null) {
		$this->rowParameters [$parameterKey] = $parameterValue;
	}
	
	public function getRowParameters() {
		return $this->rowParameters;
	}
	
	
	public function getParameters() {
		return $this->parameters;
	}
	
	public function addTool($wi400ActionList) {
		$this->tools [] = $wi400ActionList;
	}
	
	/**
	 * Setta i tools di lista
	 * 
	 * @param array $array_wi400ActionList
	 */
	public function setTools($array_wi400ActionList) {
		$this->tools = $array_wi400ActionList;
	}
	
	public function getTools() {
		return $this->tools;
	}
	
	public function getConfigFileName() {
		return $this->cfgFileName;
	}
	
	public function setConfigFileName($cfn) {
		$this->cfgFileName = $cfn;
	}
	
	public function setCurrentFilter($currentFilter) {
		$this->currentFilter = $currentFilter;
//		echo "CURRENT_FILTER:$currentFilter<br>";
		
		if (isset ( $this->customFilters [$this->getCurrentFilter ()] )) {
			
			$filterLoad = $this->customFilters [$this->getCurrentFilter ()];
//			echo "FILTER_LOAD:<pre>"; print_r($filterLoad); echo "</pre>";
			
//			echo "FILTERS:<pre>"; print_r($this->getFilters()); echo "</pre>";
			
			foreach ( $this->getFilters () as $filter ) {
//				echo "FILTER:".$filter->getId()."<br>";
				if (! $filter->getFast ()) {
					if (isset ( $filterLoad [$filter->getId ()] )) {
						$valueToSearch = $filterLoad [$filter->getId ()];
						
						if (!is_array($valueToSearch)) {
							$valueToSearch = trim ( $valueToSearch );
							if ($filter->getCaseSensitive () === false) {
								$valueToSearch = strtoupper ( $valueToSearch );
							}
							if ($filter->getType () == "STRING") {
								$valueToSearch = sanitize_sql_string ( $valueToSearch );
							}else if ($filter->getType() == "LOOKUP" && $valueToSearch != "") {
								$valueToSearch = array($valueToSearch);						
							}
						}
						
						$option = "";
						if (isset ( $filterLoad [$filter->getId () . "_OPTION"] )) {
							$option = $filterLoad [$filter->getId () . "_OPTION"];
						}
						
						$filter->setOption ( $option );
						$filter->setValue ( $valueToSearch );
						
						$this->addFilter ( $filter );
					} 
					else if ($filter->getType () == "CHECK_NUMERIC" || $filter->getType () == "CHECK_STRING") {
						$filter->setOption ( "" );
						$filter->setValue ( "" );
						$this->addFilter ( $filter );
					}
				
				}
			}
//			echo "FILTERS:<pre>"; print_r($this->getFilters()); echo "</pre>";
		
		} else {
			// Cancellazione filtri avanzati
			foreach ( $this->getFilters () as $filter ) {
				if (! $filter->getFast ()) {
					$filter->setOption ( "" );
					$filter->setValue ( "" );
					$this->addFilter ( $filter );
				}
			}
		}
	}
	
	public function getCurrentFilter() {
		return $this->currentFilter;
	}
	
	public function setDefaultFilter($defaultFilter) {
		$this->defaultFilter = $defaultFilter;
	}
	
	public function getDefaultFilter() {
		return $this->defaultFilter;
	}
	
	public function setListFilters($listFilters) {
		$this->listFilters = $listFilters;
	}
	
	public function getListFilters() {
		return $this->listFilters;
	}
	
	public function setCustomFilters($customFilters) {
		$this->customFilters = $customFilters;
	}
	
	public function getCustomFilters() {
		return $this->customFilters;
	}
/*	
	public function addCustomFilter($filterName, $filterSave, $listConfig="") {
		
		// Salvataggio configurazione di lista
		if ($listConfig !== ""){
			$filterSave["FILTER_LIST_CONFIG"] = $listConfig;
		}
		
		$this->customFilters [$filterName] = $filterSave;
		
		// *************************************************************
		// SALVATAGGIO FILTRI SU FILE
		// *************************************************************
		$filename = wi400File::getUserFile ( "list", $this->cfgFileName . ".flt" );
		
		$handle = fopen ( $filename, "w" );
		
		if (flock ( $handle, LOCK_EX )) {
			$putfile = True;
		} else {
			$putfile = False;
			fclose ( $handle );
		}
		if ($putfile) {
			
			$contents = serialize ( $this->getCustomFilters () );
			fputs ( $handle, $contents );
			flock ( $handle, LOCK_UN );
			fclose ( $handle );
		}
	}
*/
	public function addCustomFilter($filterName, $filterSave, $listConfig="", $filterGen="") {
		global $settings;
		
		// Salvataggio configurazione di lista
		if ($listConfig !== ""){
			$filterSave["FILTER_LIST_CONFIG"] = $listConfig;
		}
		
//		print_log("ORIGINAL FILTER NAME: $filterName");
		
		// *************************************************************
		// SALVATAGGIO FILTRI SU FILE
		// *************************************************************
		// Controllo se il filtro è di tipo generico
		$f_gen = false;
//		if(substr($filterName, 0, 1)=="*") {
		if($filterGen!=="") {
			$f_gen = true;
			
			if(substr($filterName, 0, 1)!="*")
				$filterName = "*".$filterName;
		}
//		echo "F_GEN:$f_gen<br>";

		$this->customFilters[$filterName] = $filterSave;
		
		// Controllo che l'utente sia abilitato a rimuovere il filtro se questo è generico
		$user_ab = false;
		if(isset($_SESSION["user_admin"]) && $_SESSION["user_admin"]==true) {
			$user_ab = true;
		}
//		echo "USER_AB:$user_ab<br>";

//		print_log("F_GEN: $f_gen - USER AB: $user_ab - FILTER NAME: $filterName");
		
		if($f_gen===false || ($f_gen===true && $user_ab===true)) {
			$wi400Filters = $this->getCustomFilters();
				
			$wi400Filters_user = array();
			$wi400Filters_gen = array();
				
			foreach($wi400Filters as $key => $val) {
				if(substr($key, 0, 1)=="*") {
					$wi400Filters_gen[substr($key, 1)] = $val;
				}
				else {
					$wi400Filters_user[$key] = $val;
				}
			}
			
			if($f_gen===false) {
				$filename = wi400File::getUserFile ( "list", $this->cfgFileName . ".flt" );
				
				$filters = $wi400Filters_user;
				wi400ConfigManager::saveConfig("list_filter", $this->cfgFileName,"",$filename,$filters);
				
			}
			else {
				/*if(!file_exists($settings['data_path']."filtri_master")) {
					wi400_mkdir($settings['data_path']."filtri_master");
				}*/
				$filename = $settings['data_path']."filtri_master/MASTER_".$this->cfgFileName.".flt";
				
				$filters = $wi400Filters_gen;
				wi400ConfigManager::saveConfig("list_master_filter", $this->cfgFileName,"",$filename,$filters);
				
			}
//			echo "FILE:$filename<br>";
//			echo "FILTERS:<pre>"; print_r($filters); echo "</pre>";

			/*$handle = fopen ( $filename, "w" );
			
//			print_log("FILENAME: $filename");
			
			if (flock ( $handle, LOCK_EX )) {
				$putfile = True;
			} else {
				$putfile = False;
				fclose ( $handle );
			}
			
			if ($putfile) {
				$contents = serialize ( $filters );
				fputs ( $handle, $contents );
				flock ( $handle, LOCK_UN );
				fclose ( $handle );
			}*/
		}
	}	
/*	
	public function removeCustomFilter($filterName) {
		
		unset ( $this->customFilters [$filterName] );
		// *************************************************************
		// SALVATAGGIO FILTRI SU FILE
		// *************************************************************
		$filename = wi400File::getUserFile ( "list", $this->cfgFileName . ".flt" );
		
		$handle = fopen ( $filename, "w" );
		
		if (flock ( $handle, LOCK_EX )) {
			$putfile = True;
		} else {
			$putfile = False;
			fclose ( $handle );
		}
		if ($putfile) {
			$contents = serialize ( $this->getCustomFilters () );
			fputs ( $handle, $contents );
			flock ( $handle, LOCK_UN );
			fclose ( $handle );
		}
	}
*/	
	public function removeCustomFilter($filterName) {
		global $settings;
		
//		print_log("FILTER NAME: $filterName");
		
//		echo "FILTER:$filterName<br>";
//		echo "FILTERS:<pre>"; print_r($this->customFilters); echo "</pre>";
		// Controllo se il filtro è di tipo generico
		$f_gen = false;
		if(substr($filterName, 0, 1)=="*") {
			$f_gen = true;
		}
//		echo "F_GEN:$f_gen<br>";
		
		// Controllo che l'utente sia abilitato a rimuovere il filtro se questo è generico
		$user_ab = false;
		if(isset($_SESSION["user_admin"]) && $_SESSION["user_admin"]==true) {
			$user_ab = true;
		}
//		echo "USER_AB:$user_ab<br>";

//		print_log("F_GEN: $f_gen - USER AB: $user_ab");
		
		if($f_gen===false || ($f_gen===true && $user_ab===true)) {
//			echo "REMOVE<br>";
			// Rimozione del filtro dall'elenco dei filtri
			unset ( $this->customFilters [$filterName] );
			
//			print_log("REMOVE FILTER: $filterName");
			
			// *************************************************************
			// SALVATAGGIO FILTRI SU FILE	(sovrascrittura dell'elenco nel file)
			// *************************************************************
			$wi400Filters = $this->getCustomFilters();
			
			$wi400Filters_user = array();
			$wi400Filters_gen = array();
			
			foreach($wi400Filters as $key => $val) {
				if(substr($key, 0, 1)=="*") {
					$wi400Filters_gen[substr($key, 1)] = $val;
				}
				else {
					$wi400Filters_user[$key] = $val;
				}
			}
			
			$filters = array();
			if($f_gen===false) {
				$filename = wi400File::getUserFile ( "list", $this->cfgFileName . ".flt" );
				
				$filters = $wi400Filters_user;
				wi400ConfigManager::saveConfig("list_filter", $this->cfgFileName,"",$filename,$filters);
				
			}
			else {
				$filename = $settings['data_path']."filtri_master/MASTER_".$this->cfgFileName.".flt";
				
				$filters = $wi400Filters_gen;
				wi400ConfigManager::saveConfig("list_master_filter", $this->cfgFileName,"",$filename,$filters);
				
			}
//			echo "FILE:$filename<br>";
//			echo "FILTERS:<pre>"; print_r($filters); echo "</pre>";

//			if(!empty($filters)) {
				/*$handle = fopen ( $filename, "w" );
			
				if (flock ( $handle, LOCK_EX )) {
					$putfile = True;
				} else {
					$putfile = False;
					fclose ( $handle );
				}
				if ($putfile) {
					$contents = serialize ( $filters );
					fputs ( $handle, $contents );
					flock ( $handle, LOCK_UN );
					fclose ( $handle );
				}*/
/*			}
			else {
				unlink($filename);
			}
*/			
			return true;
		}
		
		return false;
	}
	
	public function findCustomFilter($filterName) {
		if (isset ( $this->customFilters [$filterName] )) {
			return $this->customFilters [$filterName];
		} else {
			return array ();
		}
	}
	
	/**
	 * Aggiunge un titolo alla lista. Il titolo verrà visualizzato al momento dell'esportazione oppure quando viene chiusa la lista
	 * 
	 * @param string $title		Titolo descrittivo della lista
	 */
	public function setTitle($title) {
		$this->title = $title;
	}
	
	public function getTitle() {
		return $this->title;
	}
	
	public function getColumnsOrder() {
		return $this->columnsOrder;
	}
	
	public function getColumnsFix() {
		return $this->columnsFixed;
	}
	public function getColumnsWidth() {
		return $this->columnsWidth;
	}	
	public function setSelectionArray($selectionArray) {
		$this->selectionArray = $selectionArray;
	}
	public function setSelectionKey($key, $value=array()) {
		$this->selectionArray[$key] = $value;
		wi400Session::save(wi400Session::$_TYPE_LIST, $this->idList, $this);		
	}
	
	public function unsetSelectionKey($key) {
		unset($this->selectionArray[$key]);
		wi400Session::save(wi400Session::$_TYPE_LIST, $this->idList, $this);		
	}
	
	public function getSelectionArray() {
		return $this->selectionArray;
	}
	
	public function setAction($actions) {
		$this->actions = $actions;
	}
	/**
	 * Questa funziona ha lo scopo di ritornare le solo colonne necessarie nel select
	 */
	public function getFieldsForSelect() {
		global $db;
		getMicroTimeStep("inizio");
		// Recupero le colonne chiave
		$keys = array_flip(array_keys($this->getKeys()));
		$column = array();
		$break = array();
		// Recupero le colonne che devono essere visualizzate
		foreach ($this->getCols() as $key => $value) {
			if ($value->getShow()) {
				$column[$key]=$key;
			}
		}
		// Recupero eventuali Break Key
		$breakey = $this->getBreakKey();
		if ($breakey !="") {
			$break2 = explode(",",$breakey);
			foreach ($break2 as $key => $value) {
				$break[trim($value)]=$value;
			}
		}
		/*showArray($column);
		showArray($break);
		showArray($keys);
		die();*/
		$column = array_merge($column, $break);
		// todo devo eliminare le colonne virtuali non presenti sul file
		$froms = $this->getFrom();
		$from = explode(",",$froms);
		$coltab = array();
		foreach ($from as $key => $value) {
			// Tolgo eventuali ALIAS
			$pos = strpos(trim($value), " ");
			if ($pos>0) {
				$value = substr($value,0,$pos);
			}
			$coltab1 = $db->columns($value,"",True);
			$coltab = array_merge($coltab, array_flip($coltab1));
		}
		$retcol= Array();
		foreach ($column as $key => $value) {
			if (isset($coltab[$key])) {
				$retcol[$key]=$key;
			}
		}
		$retcol = array_merge($retcol, $keys);
		getMicroTimeStep("fine");
		return $retcol;
	}
	/**
	 * Se a true i campi input text quando focussati si autoselezionano
	 * 
	 * @param boolean
	 */
	public function setAutoSelection($val) {
		$this->autoSelection = $val;
	}
	
	/**
	 * Ritorna il valore dell'attributo autoSelection
	 * 
	 * Se a true i campi input text quando focussati si autoselezionano
	 * 
	 * @return boolean
	 */
	public function getAutoSelection() {
		return $this->autoSelection;
	}
	
	/**
	 * Aggiunge un'azione di tipo wi400ListAction (che estente wi400Action) alla lista.
	 * 
	 * @param string $action	Oggetto di tipo wi400ListAction
	 */
	public function addAction($action) {
		$this->actions [] = $action;
	}
	
	public function getActions() {
		return $this->actions;
	}
	
	/**
	 * Tipo di selezione possibile sulla lista
	 * 
	 * @param string $selection SINGLE o MULTIPLE
	 * 
	 */
	public function setSelection($selection) {
		$this->selection = $selection;
	}
	
	public function getSelection() {
		return $this->selection;
	}
	
	/**
	 * Ritorna l'argomento e la scheda dell'help tool richiesto
	 * 
	 * @return array [argomento, scheda]
	 */
	public function getHelpTool() {
		return $this->helpTool;
	}
	
	/**
	 * Setta il buttone helpTool nella lista per visualizzare l'argomento e la scheda scelta
	 * 
	 * @param string $argomento
	 * @param string $scheda
	 * @param number 
	 */
	public function setHelpTool($argomento, $scheda, $width=0, $height=0) {
		$this->helpTool = array($argomento, $scheda, $width, $height);
	}
	
	public function setFrom($from) {
		$this->from = $from;
	}
	
	public function getFrom() {
		return $this->from;
	}
	
	public function setField($field) {
		$this->field = $field;
	}
	
	public function getField() {
		return $this->field;
	}	
	public function setWhere($where) {
		$this->where = $where;
	}
	
	public function getWhere() {
		return $this->where;
	}
	
	public function setGroup($group) {
		$this->group = $group;
	}
	
	public function getGroup() {
		return $this->group;
	}
	
	public function setOrder($order, $static = False) {
		$this->order = $order;
		if ($static == True) {
			$this->setStaticOrder($order);
		}
	}
	public function getOrder() {
		return $this->order;
	}
	public function setStaticOrder($staticOrder) {
		$this->staticOrder = $staticOrder;
	}
	public function getStaticOrder() {
		return $this->staticOrder;
	}
	public function getStartFrom() {
		return $this->startFrom;
	}
	
	public function setStartFrom($startFrom) {
		$this->startFrom = $startFrom;
	}
	/**
	 * @desc Verifica la presenza di una condizione per abilitare il dettaglio sull'elemento della lista
	 * 
	 * @return string condizione
	 */	
	public function getDetailCondition() {
		return $this->detailCondition;
	}	
	/**
	 * @desc Aggiunge una condizione per abilitare il dettaglio sull'elemento della lista
	 * 
	 * @param string $tree oggetto di tipo wi400Tree
	 */	
	public function setDetailCondition($detailCondition) {
		$this->detailCondition = $detailCondition;
	}	
	
	public function setRowHeight($rowHeight) {
		$this->rowHeight = $rowHeight;
	}
	
	public function getRowHeight() {
		return $this->rowHeight;
	}
	
	public function setIdList($idList) {
		$this->idList = $idList;
	}
	
	public function getIdList() {
		return $this->idList;
	}
	
	/**
	 * Setta l'id numerico della lista
	 * 
	 * @param integer $index
	 */
	public function setIdNumList($index) {
		$this->idNumList = $index;
	}
	
	/**
	 * Ritorna l'id numerico della lista
	 * 
	 * @return integer 
	 */
	public function getIdNumList() {
		return $this->idNumList;
	}
	
	public function getSubfile() {
		return $this->subfile;
	}
	
	public function setSubfile(wi400Subfile $subfile) {
		
		// *************************************************************
		// GESTIONE SUBFILE
		// *************************************************************
		
		if (wi400Session::exist(wi400Session::$_TYPE_SUBFILE, $subfile->getIdTable())) {
			$wi400TmpSubfile = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $subfile->getIdTable());
			
			if ($wi400TmpSubfile->getPersistence () === True) {
				// Persistente
				

				$subfile->setInizialized ( $wi400TmpSubfile->isInizialized () );
				$subfile->setFinalized ( $wi400TmpSubfile->isFinalized () );
				$subfile->setTotals ( $wi400TmpSubfile->getTotals () );
				$subfile->setExtraRows($wi400TmpSubfile->getExtraRows());
				// Controllo se sono cambiate le chiavi del subfile
				foreach ( $subfile->getKeys () as $keyId => $keyValue ) {
					
					$oldKeys = $wi400TmpSubfile->getKeys ();
					// Controllo se sono presenti nuove chiavi o i valori sono cambiati rispetto ai vecchi
					if (! isset ( $oldKeys [$keyId] ) || $oldKeys [$keyId] !== $keyValue) {
						
						
						if (array_search($keyId, $wi400TmpSubfile->getKeysDrop()) !== false){
							$subfile->drop();
							break;
						}else{
							// Pulizia del subfile
							$subfile->delete();
						}
//						break;
					}
				}
			}
		}
		
		wi400Session::save(wi400Session::$_TYPE_SUBFILE, $subfile->getIdTable (), $subfile);
		
		$this->subfile = $subfile->getIdTable ();
		if ($this->from == "") {
			$this->from = $subfile->getFullTableName ();
		}
	}
	
	public function setTotalRows($totalRows) {
		$this->totalRows = $totalRows;
	}
	
	public function getTotalRows() {
		return $this->totalRows;
	}
	
	public function setPageRows($pageRows) {
		$this->pageRows = $pageRows;
	}
	
	public function getPageRows() {
		global $db;
		
		if($this->pageRows=="*ALL") {
			$wi400ListSql = new wi400ListSql($this);
			$query_num_rows = $wi400ListSql->get_query(false, true);
//	    	echo "<font color='blue'>QUERY_NUM_ROWS_1:</font> $query_num_rows<br>";
				
			$do = $db->singleQuery($query_num_rows);
			$row = $db->fetch_array($do);
				
			$totalRows = $row["COUNTER"];
//			echo "TOTAL_ROWS: $totalRows<br>";

			if($totalRows>$this->maxPageRows && $this->maxPageRows>0)
				$totalRows = $this->maxPageRows;
			
			return $totalRows;
		}
		
		return $this->pageRows;
	}
	
	// Può necessario limitare il numero massimo di righe per pagina per non superare il numero di variabili passabili in $_REQUEST,
	// in quanto tutti i campi di inserimento nella lista vengono riportati nella $_REQUEST
	public function setMaxPageRows($pageRows) {
		$this->maxPageRows = $pageRows;
	}
	
	public function getMaxPageRows() {
		return $this->maxPageRows;
	}
	
	/**
	 * Aggiunge un filtro alla lista (sia di tipo FAST che normale)
	 * 
	 * @param string $filter	Oggetto di tipo wi400Filter
	 */
	public function addFilter($filter) {
		$filter->setIdList ( $this->idList );
		$this->filters [$filter->getId ()] = $filter;
		
		// flag presenza filtri
		if (! $filter->getFast ()) {
			$this->hasFilters = true;
		}
	}
	
	public function getFilters() {
		return $this->filters;
	}
	
	public function setFilters($filters) {
		$this->filters = $filters;
	}
	
	public function setFilterUserWhere($userWhere) {
		$this->userWhere = $userWhere;
	}
	
	public function getFilterUserWhere() {
		return $this->userWhere;
	}
	
	public function setForceUserWhere($userWhere) {
		$this->forceUserWhere = $userWhere;
	}
	
	public function getForceUserWhere() {
		return $this->forceUserWhere;
	}
	
	public function getKey($id) {
		return $this->keys[$id];
	}
	
	public function getKeys() {
		return $this->keys;
	}
	
	/**
	 * Aggiunge una chiave alla lista
	 * 
	 * @param string $key Id della colonna chiave
	 * @param string $format eventuale formato da applicare alla chiave
	 */
	public function addKey($key, $format="", $whereFormat="") {
		$column = new wi400Column($key,"",$format);
		if ($whereFormat!="") {
			$column->setWhereFormat($whereFormat);
		}
		$this->keys[$key] = $column;
	}
	
	public function setKeys($key) {
		$this->keys = $key;
	}
	
	
	/**
	 * Recupero la colonna che viene usata come rottura<br/>
	 * Ogni volta che il valore della colonna cambia viene inserita una rottura nella lista
	 * 
	 * @return id della colonna chiave
	 */
	public function getBreakKey() {
		return $this->breakKey;
	}
	
	/**
	 * Setto la colonna su cui voglio una rottura di lista<br/>
	 * Ogni volta che il valore della colonna cambia viene inserita una rottura nella lista
	 * 
	 * @param string $key Id della colonna chiave
	 */
	public function setBreakKey($key) {
		$this->breakKey = $key;
	}
	
	/**
	 * Setta l'attributo classe del contenitore breakKey
	 * 
	 * @param string $class
	 */
	public function setBreakClass($class) {
		$this->breakClass = $class;
	}
	
	/**
	 * Reperisce l'attributo classe che è stato settato al breakKey
	 *
	 * @return string
	 */
	public function getBreakClass() {
		return $this->breakClass;
	}
	
	/**
	 * Funzione che viene eseguida per castomizare il breakKey
	 *
	 * @param string $funzione
	 */
	public function setBreakFunction($funzione) {
		$this->breakFunction = $funzione;
	}
	
	/**
	 * Ritorna il nome della funzione che viene usata per castomizare il breakkey
	 *
	 * @return string
	 */
	public function getBreakFunction() {
		return $this->breakFunction;
	}
	
	public function getMessage($rowKey){
		$message = array();
		if (isset($this->messages[$rowKey])){
			$message = $this->messages[$rowKey];
		}
		return $message;
	}
	
	public function getMessages(){
		return $this->messages;
	}
	/**
	 * @desc NO STATIC function setMessages
	 * @param unknown $errorMessages
	 * @return multitype:
	 */
	public function setErrorMessages($errorMessages){
		return $this->messages= $errorMessages;
	}
	
	public function addErrorMessages($errorMessages){
		return $this->messages = array_merge($this->messages, $errorMessages);
	}
	
	/**
	 * @desc  Aggiunge un messaggio ad una riga della lista
	 * 
	 * @param string $idList id della lista a cui applicare il messaggio
	 * @param string $rowKey chiave della riga a cui applicare il messaggio
	 * @param string $messageType tipo di messaggio: error, valid
	 * @param string $messageLabel label del messaggio
	 * 
	 */
	public static function addMessage($idList, $rowKey, $messageType, $messageLabel) {
		//if (isset($_SESSION[$idList])){
		//	$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		if (wi400Session::exist(wi400Session::$_TYPE_LIST, $idList)) {
			$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
			$newMessage = array($messageType,$messageLabel);
			$wi400List->messages[$rowKey] = $newMessage;
			wi400Session::save(wi400Session::$_TYPE_LIST, $idList, $wi400List);
			//$_SESSION[$idList] = $wi400List;
		}
	}
	
	/**
	 * @desc Aggiunge dei messaggi ad una riga della lista
	 * 
	 * @param string $idList id della lista a cui applicare i messaggi
	 * @param string $messagesArray array composto da chiave riga => array(tipo messaggio, messaggio) (Vedi addMessage)
	 * 
	 */
	public static function setMessages($idList, $messagesArray){
		//if (isset($_SESSION[$idList])){
		if (wi400Session::exist(wi400Session::$_TYPE_LIST, $idList)) {
			$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
			//$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
			$wi400List->messages = array();
			foreach($messagesArray as $key => $message){
				$wi400List->messages[$key] = $message;
			}
			wi400Session::save(wi400Session::$_TYPE_LIST, $idList, $wi400List);
			//$_SESSION[$idList] = $wi400List;
		}
	}
	
	/**
	 * @desc Rimuove un messaggio da una riga della lista
	 * 
	 * @param string $idList id della lista da cui rimuovere il messaggio
	 * @param string $rowKey chiave della riga da cui rimuovere il messaggio
	 * 
	 */
	public static function removeMessage($idList, $rowKey) {
//		if (isset($_SESSION[$idList])){
//			$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		if (wi400Session::exist(wi400Session::$_TYPE_LIST, $idList)) {
			$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
			unset($wi400List->messages[$rowKey]);
			//$_SESSION[$idList] = $wi400List;
			wi400Session::save(wi400Session::$_TYPE_LIST, $idList, $wi400List);
		}
	}
	
	/**
	 * @desc Cancella i messaggi legati ad una lista
	 * 
	 * @param string $idList id della lista di cui eliminare i messaggi
	 * 
	 */
	public static function removeMessages($idList) {
		//if (isset($_SESSION[$idList])){
		//	$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		if (wi400Session::exist(wi400Session::$_TYPE_LIST, $idList)) {
			$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
			$wi400List->messages = array();
			//$_SESSION[$idList] = $wi400List;
			wi400Session::save(wi400Session::$_TYPE_LIST, $idList, $wi400List);
		}
	}
	
	/**
	 * Aggiunge una colonna alla lista
	 * 
	 * @param string $wi400Column oggetto di tipo wi400Column
	 */
	public function addCol(wi400Column $wi400Column) {
		$wi400Column->setIdList ( $this->idList );
		$this->cols [$wi400Column->getKey ()] = $wi400Column;
//		if ($wi400Column->getShow()) {
			$this->columnsOrder[] = $wi400Column->getKey();
//		}
		if ($wi400Column->isFixed()) {
			$this->columnsFixed[] = $wi400Column->getKey();
		}
		if ($wi400Column->getInput()){
			$this->editable = true;
		}
	}
	
	
	public function removeCol($colKey){
		if (isset($this->cols[$colKey])){
			unset($this->cols[$colKey]);
		}
		$colIndex = array_search($colKey,$this->columnsOrder);
		if ($colIndex){
			unset($this->columnsOrder[$colIndex]);
		}
		// Reindicizza
		$this->columnsOrder = array_values($this->columnsOrder);
	}
	
	/**
	 * Setta le colonne di una lista
	 * 
	 * @param string $cols Array di oggetti di tipo wi400Column
	 */
	public function setCols($cols) {
		$colsArray = array ();
		$this->columnsOrder = array ();
		foreach ( $cols as $col ) {
			$col->setIdList ( $this->idList );
			$colsArray [$col->getKey ()] = $col;
//			if ($col->getShow ()) {
				$this->columnsOrder [] = $col->getKey ();
//			}
			if ($col->getInput()){
				$this->editable = true;
			}
			if($col->isFixed()) {
				$this->columnsFixed[] = $col->getKey();
			}
		}
		$this->cols = $colsArray;
	}
	
	public function getCols() {
		return $this->cols;
	}
	
	public function getCol($key) {
		if (isset ( $this->cols [$key] )) {
			return $this->cols [$key];
		} else {
			return null;
		}
	}
	
	/**
	 * Setta la modalità di visualizzazione di un menu
	 * 
	 * @param string $showMenu booleano che indica se visualizzare o meno il menu
	 */
	public function setShowMenu($showMenu) {
		$this->showMenu = $showMenu;
	}
	
	public function getShowMenu() {
		return $this->showMenu;
	}
	
	/**
	 * Indica se è possibile ricaricare il contenuto della lista
	 * 
	 * @param string $canReload booleano
	 */
	public function setCanReload($canReload) {
		$this->canReload = $canReload;
	}
	
	public function getCanReload() {
		return $this->canReload;
	}
	
	/**
	 * Setto il bottone SQL della lista
	 *
	 * @param boolean
	 */
	public function setCanSQL($val) {
		$this->canSQL = $val;
	}
	
	/**
	 * Ritorno true se il bottone SQL della lista è visibile
	 * altrimenti ritorna false
	 * 
	 * @return boolean
	 */
	public function getCanSQL() {
		return $this->canSQL;
	}
	
	/**
	 * Indica se è possibile gestire la lista
	 * 
	 * @param string $canManage booleano
	 */
	public function setCanManage($canManage) {
		$this->canManage= $canManage;
	}
	
	public function getCanManage() {
		return $this->canManage;
	}
	
	public function setManageOnlyNumRows($canManage) {
		$this->ManageOnlyNumRows= $canManage;
	}
	
	public function getManageOnlyNumRows() {
		return $this->ManageOnlyNumRows;
	}
	
	/**
	 * Indica se è possibile esportare il contenuto della lista
	 * 
	 * @param string $canExport mixed true/false/"RESUBMIT" (per liste con molti campi di input)
	 */
	public function setCanExport($canExport) {
		$this->canExport = $canExport;
	}
	
	public function getCanExport() {
		return $this->canExport;
	}
	/**
	 * Indica se è possibile importare i dati da EXCEL
	 *
	 * @param string $canExport mixed true/false/"RESUBMIT" (per liste con molti campi di input)
	 */
	public function setCanImport($canImport) {
		$this->canImport = $canImport;
	}
	
	public function getCanImport() {
		return $this->canImport;
	}
	/**
	 * @desc Indica se è possibile eseguire i filtri sulla lista
	 *
	 * @param string $canFilter  true/false
	 */
	public function setCanFilter($canFilter) {
		$this->canFilter = $canFilter;
	}
	
	public function getCanFilter() {
		return $this->canFilter;
	}	
	/**
	 * Indica la chiave che deve essere passata da un lockup alla finestra opener
	 * 
	 * @param string $passKey chiave da passare
	 */
	public function setPassKey($passKey) {
		$this->passKey = $passKey;
	}
	
	public function getPassKey() {
		return $this->passKey;
	}
	
	/**
	 * setta il campo a cui passare il valore elaborato della lista nel form PASS_VALUE
	 * 
	 * @param boolean 
	 */
	public function setPassValue($campo) {
		$this->passValue = $campo;
	}
	
	/**
	 * Ritorna l'id del campo a cui passare il valore elaborato dalla lista nel form PASS_VALUE
	 * 
	 * @return boolean
	 */
	public function getPassValue() {
		return $this->passValue;
	}
	
	/**
	 * Indica la descrizione che deve essere passata da un lockup come description del campo indicato nel passKey
	 * 
	 * @param string $passDesc id della colonna da utilizzare come descrizione
	 */
	public function setPassDesc($passDesc) {
		$this->passDesc = $passDesc;
	}
	
	public function getPassDesc() {
		return $this->passDesc;
	}
	
	public function setCalculateTotalRows($ctr) {
		$this->calculateTotalRows = $ctr;
	}
	public function getCalculateTotalRows() {
		return $this->calculateTotalRows;
	}
	
	public function set_queryCount($queryCount) {
		$this->queryCount = $queryCount;
	}
	
	public function get_queryCount() {
		return $this->queryCount;
	}
	
	/**
	 * Aggiunge un albero come filtro della lista
	 * 
	 * @param string $tree oggetto di tipo wi400Tree
	 */
	public function addTree($tree) {
		$tree->setIdList ( $this->getIdList () );
		$this->tree = $tree;
	}
	
	/**
	 * Setta il valore dell'attributo tree direttamente
	 * 
	 * @param unknown $val
	 */
	public function setTree($val) {
		$this->tree = $val;
	}
	
	public function getTree() {
		return $this->tree;
	}
	
	public function addSortList($sortList) {
		$this->sortList = $sortList;
	}
	
	public function getSortList() {
		return $this->sortList;
	}
	
	/**
	 * Permette di impostare un EVAL comune a una o più condizioni di più colonne della lista
	 * in modo che questo venga eseguito una volta sola per riga e poi il risultato venga sostituito al marker ##COMMON_LIST##
	 * in più eval specifici che hanno bisogno di eseguire lo stesso controllo ogni volta (es: readonly, default value, ...)  
	 */
	public function setCommonCondition($condition) {
		$this->commonCondition = $condition;
	}
	
	public function getCommonCondition(){
		return $this->commonCondition;
	}
	
	// DEFAULT true: filtro aggiunto normalmente (direttamente sulla query)
	// false: per permette di filtrare campi non esistenti in tabella (ridenominati AS), viene creata una query con WITH
	public function setAutoFilter($autoFilter) {
		$this->autoFilter = $autoFilter;
	}
	
	public function getAutoFilter() {
		return $this->autoFilter;
	}
	
	// DEFAULT false
	// true: per permettere in caso di $autofilter = false; di eseguire la query senza trasformarla in una query con with
	public function set_execute_like_query($like_query) {
		$this->execute_like_query = $like_query;
	}
	
	public function get_execute_like_query() {
		return $this->execute_like_query;
	}
	
	// DEFAULT true: viene utilizzata la paginazione con BETWEEN
	// false: viene utilizzata la paginazione normale
	public function setPagBetween($pagBetween) {
		$this->pagBetween = $pagBetween;
	}
	
	// DEFAULT true: viene utilizzata la paginazione con BETWEEN
	// false: viene utilizzata la paginazione normale
	public function getPagBetween() {
		return $this->pagBetween;
	}
	
	public function manage_eval_condition($cond, $row, $common_value=array(), $tipo="", $key=null) {
		$value = "";
	
//		echo "TIPO:".$tipo."<br>";
//		echo "CONDITION:<pre>"; print_r($cond); echo "</pre>";
//		echo "COMMON_VALUE:<pre>"; print_r($common_value); echo "</pre>";
	
		if(is_array($cond)>0) {
			$condition = false;
			foreach($cond as $rowCondition) {
//				echo "COND:".$rowCondition[0]."<br>";
				$evalValue = substr($rowCondition[0], 5).";";
					
				if(!empty($common_value))
					$evalValue = $this->manage_eval_condition_common($evalValue, $common_value);
//					echo "EVAL_VALUE:$evalValue<br>";
	
				eval('$condition='.$evalValue.';');
					
//				echo "EVAL_RES: $condition<br>";
					
				if($condition) {
					$value = $rowCondition[1];
					break;
				}
			}
		}
		else if(strpos($cond, "EVAL:")===0) {
			$evalValue = substr($cond, 5).";";
	
			if(!empty($common_value))
				$evalValue = $this->manage_eval_condition_common($evalValue, $common_value);
	
			eval('$value='.$evalValue);
		}
/*		
		else if(strpos($cond, "CALLBACK:")===0) {
			$start = strlen("CALLBACK:");
			$stop = strpos($cond, "(")-$start;
			$func = trim(substr($cond, $start, $stop));
			$p_str = trim(substr($cond, $start+$stop+1, -1));
			$p_array = explode(";", $p_str);
			$tipo = $p_array[0];
			$params = array();
			if(isset($p_array[1])) {
				$params = explode(",", $p_array[1]);
			}
//			echo "CALLBACK_FUNC:".$func."_TIPO:".$tipo."<br>";
//			echo "PARAMS:<pre>"; print_r($params); echo "</pre>";
						
			if(is_callable($func)) {
				$wi400Column = $this->getCurrentCol();
//				echo "COL:<pre>"; print_r($wi400Column); echo "</pre>";
//				echo "ROW:<pre>"; print_r($row); echo "</pre>";
				
				$value = call_user_func($func, $this, $row, $wi400Column, $tipo, $params);
			}
			else {
				die("call user func not valid ".$func);
			}
		}
*/
		else if(strpos($cond, "CALLBACK:")===0) {
//			$func = $this->getCallBackFunction("formatting");

			$func_str = $this->getCallBackFunction("formatting");
			
			$func_parts = explode(":", $func_str);
			$func = $func_parts[0];
			if(isset($func_parts[1])) {
				$p_str = $func_parts[1];
			}
			
//			echo "CALLBACK_FUNC:".$func."<br>";
//			echo "TIPO:".$tipo."<br>";
			
			$start = strlen("CALLBACK:");
			
//			$p_str = trim(substr($cond, $start));			
			$params = array();
			if(isset($p_str) && $p_str!="") {
				$params = explode(",", $p_str);
			}
//			echo "PARAMS:<pre>"; print_r($params); echo "</pre>";
		
//			if($func!="") {
				if(is_callable($func)) {
					$wi400Column = $this->getCurrentCol();
//					echo "COL:<pre>"; print_r($wi400Column); echo "</pre>";
//					echo "ROW:<pre>"; print_r($row); echo "</pre>";
		
					$value = call_user_func($func, $this, $row, $wi400Column, $tipo, $params);
//					$value = call_user_func($func, $this, $row, $wi400Column, $tipo);
				}
				else {
					die("call user func not valid ".$func);
				}
//			}
		}
		else {
			if(isset($key)) {
				if(!isset($row[$key])){
					$value = $cond;
				}
				else {
					$value = $row[$key];
				}
			}
			else {
				$value = $cond;
			}
		}
		
//		echo "VALUE:$value<br>";
	
		return $value;
	}
	
	/**
	 * Gestisce la composizione di un EVAL comune a una o più condizioni di una o più colonne della lista
	 * in modo che questo venga eseguito una volta sola per riga e poi il risultato venga sostituito al marker ##COMMON_LIST## o ##COMMON_COLUMN##
	 * in più eval specifici che hanno bisogno di eseguire lo stesso controllo ogni volta (es: readonly, default value, ...)
	 */
	public function manage_eval_condition_common($evalValue, $common_value) {
//		echo "COMMON_COND_VALUE:<pre>"; print_r($common_value); echo "</pre>";
	
		if(!empty($common_value)) {
			if(strpos($evalValue, "##COMMON_LIST##")!==false) {
				if($common_value['LIST']=="")
					$common_value['LIST'] = "''";
	
//				echo "EVAL_VALUE_1:$evalValue<br>";
				$evalValue = str_replace("##COMMON_LIST##", $common_value['LIST'], $evalValue);
//				echo "EVAL_VALUE_2:$evalValue<br>";
			}
	
			if(strpos($evalValue, "##COMMON_COLUMN##")!==false) {
				if($common_value['COLUMN']=="")
					$common_value['COLUMN'] = "''";
	
				$evalValue = str_replace("##COMMON_COLUMN##", $common_value['COLUMN'], $evalValue);
			}
	
//			echo "EVAL: $evalValue<br>";
		}
	
		return $evalValue;
	}
	
	/**
	 * @desc setShowHeadFilter():Setta se visualizzare i filtri in testata
	 * @param bollean: $showHeadFilter
	 */
	public function setShowHeadFilter($showHeadFilter) {
		$this->showHeadFilter=$showHeadFilter;
	}
	/**
	 * @desc getShowHeadFilter(): Verifica se visualizzare i filtri in testata
	 */
	public function getShowHeadFilter() {
		return $this->showHeadFilter;
	}
	
	public function setExportFilterSel($filterSel) {
		$this->exportFilterSel = $filterSel;
	}
	
	public function getExportFilterSel() {
		return $this->exportFilterSel;
	}
	
	public function setExportFilterSelChecked($checked) {
		$this->exportFilterSelChecked = $checked;
	}
	
	public function getExportFilterSelChecked() {
		return $this->exportFilterSelChecked;
	}
	
	public function setExportBatch($exportBatch) {
		$this->exportBatch = $exportBatch;
	}
	
	public function getExportBatch() {
		return $this->exportBatch;
	}
	
	public function dispose() {
		listDispose ( $this );
	}
	
	/**
	 * Setta lo script che viene eseguito al ritorno della riga aggiornata in autoupdate
	 *
	 * @param string (codice javascript)
	 */
	public function setScriptOnAutoUpdate($script) {
		$this->scriptOnAutoUpdate = $script;
	}
	
	/**
	 * Ritorna lo script che viene eseguito al ritorno della riga aggiornata in autoupdate
	 * 
	 * @return string (codice javascript)
	 */
	public function getScriptOnAutoUpdate() {
		return $this->scriptOnAutoUpdate;
	}
	
	/**
	 * Abilitare o meno lo spostamendo del focus tra input con le freccette direzionali
	 * 
	 * @param boolean $val
	 */
	public function setEnableMovingWithKeys($val) {
		$this->enableMovingWithKeys = $val;
	}
	
	/**
	 * Abilitare o meno lo spostamendo del focus tra input con le freccette direzionali
	 *
	 * @return boolean
	 */
	public function getEnableMovingWithKeys() {
		return $this->enableMovingWithKeys;
	}
	
	/**
	 * Abilitare o meno la possibilità di selezionare la riga cliccando in un punto qualsia di essa
	 * N.B. viene escluso se si clicca su un qualsiasi campo di input
	 * 
	 * @param boolean $val 
	 * 
	 */
	public function setSelectRowEveryWhere($val) {
		$this->selectRowEveryWhere = $val;
	}
	
	/**
	 * Ritorna true se è abilitato la possibilità di selezionare la riga cliccando in un punto qualsia di essa
	 * N.B. viene escluso se si clicca su un qualsiasi campo di input
	 * 
	 * @return boolean 
	 * 
	 */
	public function getSelectRowEveryWhere() {
		return $this->selectRowEveryWhere;
	}
	
	/**
	 * @desc viewToModelRow: Converte i dati di lista in variabi
	 * @param object $wi400List
	 * @param array $row
	 */	
	public static function viewToModelRow($wi400List, $row) {
		// 
		$colonne = $wi400List->getCols();
		foreach($row as $key => $valore){
			//$key = $col->getKey();
			if (isset($colonne[$key])){
				$col = $colonne[$key];
				$colField = $col->getInput();
				$format   = $col->getFormat();
				//$valore = $row[$key];
				// Formati previsti
				$format=strtoupper($format);
				$found = False;
				if ($format!="") {
					if ($format=="STRING") {
						$found=True;
					}
					if (strpos($format, "DOUBLE")!==False) {
						$valore = doubleViewToModel($valore);
						$found=True;
					}
					if ($format == "DATE") {
						$valore = dateViewToModel($valore);
						$found=True;
					}	
					if ($found==False) {
							$function = "wi400_format_".$format."_REVERSE";
							if (is_callable($function)) {
								$valore = $function($valore);
							} else {
								developer_debug($function. " non implementata!");
							}
					}
				}
				// DATE
				$row[$key]=$valore;
			}
		}
		return $row;
	}
 	public static function disposeSubfile($wi400List, $wi400Subfile, $pagination) {
 		global $settings, $db, $routine_path;
 		
 		$totalArray = $wi400Subfile->getTotals();
 		$extraRows = $wi400Subfile->getExtraRows();
 		// In caso di refresh droppo tabella subfile
 		if ((isset($pagination) && $pagination == 'REGENERATE')
 		||	!$wi400Subfile->getPersistence()){
 			$wi400Subfile->delete();
 		}
 		set_time_limit($wi400Subfile->getTimeLimit());
 		// Controllo se esiste subfile e in caso lo creo
 		if (!$wi400Subfile->exist()){
 			// ***************************************************
 			// GESTIONE SUBFILE
 			// ***************************************************
 			// Inizializzazione subfile personale
 			$modulo=$wi400Subfile->getModulo();
 			$percorso_classe ="";
 			if ($modulo!='') {
 				$percorso_classe = p13n('modules/'.$modulo.'/subfile/'.$wi400Subfile->getConfigFileName().".cls.php");
 			} else {
 				$percorso_classe = $routine_path.'/classi/subfile/'.$wi400Subfile->getConfigFileName().".cls.php";
 			}
 			// Patch per vecchi subfile all'interno del model
 			if (file_exists($percorso_classe)) {
 				require_once($percorso_classe);
 				$subfileClassName = $wi400Subfile->getConfigFileName();
 				$customSubfile = new $subfileClassName($wi400Subfile->getParameters());
 				$customSubfile->setFullTableName($wi400Subfile->getFullTableName());
 			} else {
 				developer_debug("Subfile ".$wi400Subfile->getConfigFileName()." non trovato");
 			}
 		
 			// creo array colonne se non presente
 			if (isset($wi400List)) {
 				$customSubfile->setIdList($wi400List->getIdList());
 			}
 			$customSubfile->init($wi400Subfile->getParameters());
 				
 			// inizializzo subfile se non esistente
 			$wi400Subfile->inz($customSubfile->getCols());
 		
 			// Azzeramento totali del subfile
 			$newTotalArray = array();
 			if(!empty($totalArray)) {
 				foreach (array_keys($totalArray) as $totalKey){
 					if (strpos($totalArray[$totalKey], "EVAL:")===0){
 						$newTotalArray[$totalKey] = $totalArray[$totalKey];
 					}else{
 						$newTotalArray[$totalKey] = 0;
 					}
 				}
 			}
 			$wi400Subfile->setTotals($newTotalArray);
 			//			echo "TOTAL_ARRAY_SET:<pre>"; print_r($newTotalArray); echo "</pre>";
 				
 			// Azzeramento righe extra
 			$wi400Subfile->setExtraRows(array());
 				
 			// operazioni iniziali
 			$customSubfile->start($wi400Subfile);
 				
 			// Eseguo query
 			if ($wi400Subfile->getSql()!='*AUTOBODY' && $wi400Subfile->getSql()!="") {
 				$result = $db->query($wi400Subfile->getSql(), False , -1);
 		
 				while($row = $db->fetch_array($result)){
 					$rowSubFile = $customSubfile->body($row, $wi400Subfile->getParameters());
 					if($rowSubFile=="SKIP") {
 						continue;
 					}
 					if($rowSubFile!==false){
 						$wi400Subfile->write($rowSubFile);
 					}
 				}
 			} else if ($wi400Subfile->getSql()=='*AUTOBODY') {
 		
 				while($rowSubFile = $customSubfile->body(array(), $wi400Subfile->getParameters())){
 					$wi400Subfile->write($rowSubFile);
 				}
 			}
 				
 			$wi400Subfile->finalize();
 				
 			//			echo "TOTAL_ARRAY_SUBFILE_1:<pre>"; print_r($wi400Subfile->getTotals()); echo "</pre>";
 				
 			// Operazioni finali
 			$customSubfile->end($wi400Subfile);
 				
 			//			echo "TOTAL_ARRAY_SUBFILE_2:<pre>"; print_r($wi400Subfile->getTotals()); echo "</pre>";
 				
 			// Aggiunta righe extra
 			foreach ($extraRows as $extraDesc => $extraRow){
 				$wi400Subfile->addExtraRow($extraDesc, $customSubfile->extraRow($extraDesc, $wi400Subfile->getParameters()));
 				$wi400Subfile->addExtraRowExport($extraDesc, $customSubfile->extraRowExport($extraDesc, $wi400Subfile->getParameters()));
 			}
 				
 			if ($wi400Subfile->getSql()!='*AUTOBODY') {
 				if (isset($wi400List)) {
 					$_SESSION[$wi400List->getIdList()."_TOTAL_ROWS"] = $wi400Subfile->getTotalRecord();
 				}
 				$totalFromSubfile=True;
 			}
 			// ***************************************************
 			if (isset($wi400List)) {	
 				wi400Session::save(wi400Session::$_TYPE_SUBFILE,  $wi400List->getSubfile(), $wi400Subfile);
 			}
 		}
	}
	/**
	 * Questa funzione permette di ottenere il risultato della paginazione
	 * @param unknown $idList
	 */
	public static function doPagination($idList, $parameters=array()) {
		global $moduli_path, $routine_path, $db, $messageContext;
		// Carico l'oggetto Lista
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		// Reperisco i dati presenti nello STREAM
		$dati_reperiti = ob_get_clean();
		ob_start();
		// Eseguo il Pagination
		$old_GET=$_GET;
		$_GET=$parameters;
		$_GET['IDLIST'] = $idList;
		// Ritorno il buffer generato dall'azione
		require_once $moduli_path."/list/wi400Pagination.php";
		// Ripristino l'originale IDLIST eventualmente presente
		$_GET = $old_GET;
		$dati_restituiti = ob_get_clean();

		// Restarto l'ob e butto fuori l'output che avevo salvato prima di partire con l'azione forzata
		ob_start();
		echo $dati_reperiti;
		
		return $dati_restituiti;
	}
}
?>