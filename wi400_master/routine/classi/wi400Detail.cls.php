<?php
class wi400Detail {


	private $idDetail;
	private $fields;
	private $imageDetail;
	private $editable;
	private $title;
	private $titleCss;
	private $source;
	private $buttons;
	private $parameters;

	
	private $images= array();

	private $tabs;

	// Utilizzato per il subdetail
	private $type;

	private $colsNum;
	private $colsWidth;

	private $isMapping = false;
	private $saveDetail;
	private $loadOnlyDetail;
	private $resetDetail;
	private $customValues;

	private $fieldSessionList;
	//private $fieldValidationList;

	private $selectable;
	private $currentTab;

	private $width = '100%';
	
	private $checkUpdate;
	private $hidden = False;
	private $inputCleanField = False;
	private $status = "";  //Open Close Detail
	private $readOnly; // Il dettaglio viene prottetto tutto
	
	private $showFieldRequired = false;
	private $showTopButtons = "";
	private $showTopButtonsCond = false;
	private $showTopSaveDetail = "";
	private $showLowerButtons = "";
	private $showLowerSaveDetail = "";
	
	private $jsToTab = array();
	
	private $refreshFocus=False;
	private $azione="";
	private $form="";
	private $reloadAction="";
	private $reloadForm="";
	private $reloadTimeout="";
	public function setReloadAction($reloadAction) {
		$this->reloadAction = $reloadAction;
	}
	public function getReloadAction() {
		return $this->reloadAction;
	}
	public function setReloadForm($reloadForm) {
		$this->reloadForm = $reloadForm;
	}
	public function getReloadForm() {
		return $this->reloadForm;
	}
	public function setReloadTimeout($reloadTimeout) {
		$this->reloadTimeout = $reloadTimeout;
	}
	public function getReloadTimeout() {
		return $this->reloadTimeout;
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

	/**
	 * @desc setInputFieldClean: Setta se i campi del detail avranno l'icona di puliza
	 * @param boolean $clean True/False
	 */
	public function setInputFieldClean($clean){
		$this->inputCleanField = $clean;
	}
	/**
	 * @desc getInputFieldClean: Recupero se i campi di input del dettaglio dovranno avere l'icona di pulizia
	 * @param $hidden boolean : True/False
	 */
	public function getInputFieldClean(){
		return $this->inputCleanField;
	}
	/**
	 * Imposta la larghezza del detail all'interno della pagina
	 *
	 * @param string $w	larghezza espressa in percentuale o px
	 */
	public function setWidth($w) {
		$this->width = $w;
	}
	/**
	 * getHidden: Ritorna se il detail deve essere nascosto (per sempre!)
	 * @return $hidden boolean
	 */
	public function getHidden(){
		return $this->hidden;
	}
	/**
	 * setHidden: Setta se il detail deve essere nascosto (per sempre!)
	 * @param $hidden boolean : True/False
	 */
	public function setHidden($hidden){
		$this->hidden = $hidden;
	}
	/**
	 * @desc setStatus(): Imposta se il detail deve essere aperto o chiuso CLOSE-OPEN alla prima visualizzazione
	 * 		 Dopo la prima visualizzazione non può più essere overizzato ma segue le impostazioni dell'utente.
	 *       Se $rest viene impostato a True viene invece overizzato
	 * @param unknown $status
	 */
	public function setStatus($status, $reset=False) {
		if ($this->status=="" || $reset == True) {
			$this->status = $status;
		}
	}
	// Per evitare che vada in crash il subdetail che non ha id
	public function getId(){
		return null;
	}

	public function getSelectable(){
		return $this->selectable;
	}
	public function setSelectable($selectable){
		$this->selectable = $selectable;
	}

	public function getCustomValues(){
		return $this->customValues;
	}

	public function setCustomValues($customValues){
		$this->customValues = $customValues;
	}

	public function getType(){
		return $this->type;
	}
	public function setType($type){
		$this->type = $type;
	}
	/**
	 * @desc getReadOnly: Imposta se il dettaglio deve settare tutti i campi in readonly
	 */
	public function getReadOnly(){
		return $this->readOnly;
	}
	/**
	 * @desc setReadOnly: Recupera la proprietà readOnly del dettaglio
	 */
	public function setReadOnly($readOnly){
		$this->readOnly = $readOnly;
	}
	public function getColsNum(){
		return $this->colsNum;
	}
	public function setColsNum($cn){
		$this->colsNum = $cn;
	}

	public function getColsWidth(){
		return $this->colsWidth;
	}
	public function setColsWidth($cw){
		$this->colsWidth = $cw;
	}

	public function setSaveDetail($saveDetail){
		$this->saveDetail = $saveDetail;
	}

	public function getSaveDetail(){
		return $this->saveDetail;
	}
	
	/**
	 * @desc true: non permette di salvare o configurare il detail, ma solo di caricarlo
	 * @param unknown_type $loadOnlyDetail
	 */
	public function setLoadOnlyDetail($loadOnlyDetail){
		$this->loadOnlyDetail = $loadOnlyDetail;
	}
	
	public function getLoadOnlyDetail(){
		return $this->loadOnlyDetail;
	}
	
	/**
	 * @desc setResetDetail(): Abilita o meno la possibiltà di ripulire e resettare il contenuto del detail
	 * @param boolean $resetDetail
	 */
	public function setResetDetail($resetDetail){
		$this->resetDetail = $resetDetail;
	}
	/**
	 * @desc getResetDetail(): Recupera informazioni sul reset del detail
	 * @return Ambigous <boolean, unknown>
	 */
	public function getResetDetail(){
		return $this->resetDetail;
	}
	public function setCheckUpdate($checkUpdate){
    	$this->checkUpdate = $checkUpdate;
    }

	public function getCheckUpdate(){
    	return $this->checkUpdate;
    }
    /**
     * __construct: Costruttore del dettaglio
     * @param $idDetail string: Nome del dettaglio
     * @param $cleanSession mixed true/flase per pulire valori dettaglio da sessione, AUTO per pulizia in base al form di provenienza
     * @parma $form string: parametro opzionale, se $cleanSession AUTO allore contiene il form di riferimento per mantenere la sessione
     */
	public function __construct($idDetail, $cleanSession = True, $form="") {
		global $settings, $actionContext ,$wi400GO;
		
		$this->idDetail   = $idDetail;
		$this->rowsDetail = array();
		$this->fields     = array();
		$this->buttons    = array();
		$this->parameters = array();
		$this->tabs 	  = array();
		 
		$this->fieldSessionList = array();
		//$this->fieldValidationList = array();
		$this->type = "DETAIL";
		$this->colsNum = 1;
		$this->colsWidth = array();
		$this->saveDetail = false;

		$this->selectable = false;
		 
		$this->editable = true;
		 
		$this->title = "";
		$this->titleCss = "";
		 
		$this->checkUpdate = false;
		$this->azione=$actionContext->getAction();
		$this->form=$actionContext->getForm();

		// Pulizia dati dettaglio dalla sessione
		if ($cleanSession===True) {
			$this->cleanSession($this->idDetail);
		}
		if ($cleanSession=='AUTO') {
			if (isset($_REQUEST['CURRENT_FORM']) && $_REQUEST['CURRENT_FORM']!=$form) {
				$this->cleanSession($this->idDetail);
			}
		}

		if (existDetail($this->idDetail)){
			$detailSessionObj = getDetail($this->idDetail);
			$this->fieldSessionList = $detailSessionObj["FIELDS"];
			if (isset($detailSessionObj["STATUS"])) {
				$this->status = $detailSessionObj["STATUS"];
			} else {
				$this->status ="";
			}
		}

		if (isset($_SESSION["WIZARD"])){
			// Impongo i bottoni del wizard
			$wi400Wizard = $_SESSION["WIZARD"];
			$this->buttons = $wi400Wizard->getFooterButtons();
		}
		/* 
		 * Assicurarsi si aver aggiunto il settings nel wi400.conf altrimenti checkSettigsAllDetail verrà eseguito comunque
		 * e se non è presente alcun parametro salvato verrà visualizzato a video su tutti i detail la X per pulire i campi 
		*/
		if(!isset($settings['check_field_enable_on_detail']) || $settings['check_field_enable_on_detail']===true) {
			$this->checkSettingsAllDetail();
		}
		// PATCH per avere in tempo reale l'ID del Detail che sto costruendo
		$_SESSION['WI400_CURRENT_ID_DETAIL_INIT']=$idDetail;
		// Salvataggio Dettaglio su Oggetto Globale
		$wi400GO->addObject($idDetail, $this);
		// Debug
		
		developer_add_system_var($actionContext->getAction()."|".$idDetail, "DETAIL");
	}
	/**
	 * Ricaricamento dati da sessione, nel caso sia interventuo qualcosa tra la costruzione del dettaglio ed il dispose. I dati di 
	 * sessione sono caricati con il costruttore
	 */
	public function reloadSession() {
		if (existDetail($this->idDetail)){
			$detailSessionObj = getDetail($this->idDetail);
			$this->fieldSessionList = $detailSessionObj["FIELDS"];
			if (isset($detailSessionObj["STATUS"])) {
				$this->status = $detailSessionObj["STATUS"];
			} else {
				$this->status ="";
			}
		}
	}
	public static function loadCustomValues($idDetail){
		if ($idDetail != ""){
			$filename = wi400File::getUserFile("detail", $idDetail.".dtl");
			/*if (file_exists($filename)) {
				$handle = fopen($filename, "r");
				$contents = fread($handle, filesize($filename));
				fclose($handle);
				$customValues = unserialize($contents);
				if (is_array($customValues)){
					return $customValues;
				}
			}*/
			return wi400ConfigManager::readConfig('detail', $idDetail, '',$filename);
		}
		return array();
	}

	public static function saveCustomValues($idDetail, $customValues){
		$filename = wi400File::getUserFile("detail", $idDetail.".dtl");
		wi400ConfigManager::saveConfig('detail', $idDetail, '', $filename, $customValues);
		/*$handle = fopen($filename, "w");

		if (flock($handle, LOCK_EX)){
			$putfile = True;
		} else {
			$putfile = False;
			fclose($handle);
		}
		if ($putfile){
			$contents = serialize($customValues);
	   
			fputs($handle, $contents);
			flock($handle, LOCK_UN);
			fclose($handle);
		}else{
			echo "GRAVE: Errore durante il salvataggio.";
			exit();
		}*/
	}

	public static function cleanSession($idDetail){
		wi400Session::delete(wi400Session::$_TYPE_DETAIL, $idDetail);
	}
	/**
	 * @desc getDetailValue() Reperisce il valore di un campo di detail
	 * 
	 * @param string $idDetail Identificativo del detail
	 * @param string $fieldId  Identificativo del campo 
	 * @param bool $forceValue Se impostato a True solo per i campi di tipo CheckBox va a verificare se esiste un Value
	 * @return mixed $fieldValue Valore del campo. Array nel caso di campo multiplo o SelectCheckBox
	 */
	public static function getDetailValue($idDetail, $fieldId, $forceValue=False){
		$fieldValue = null;
		$sessionFields = wi400Detail::getDetailFields($idDetail);
		if (isset($sessionFields[$fieldId])){
			$fieldObj = $sessionFields[$fieldId];
			if($fieldObj->getType()=="CHECKBOX" && $forceValue==False)
				$fieldValue = $fieldObj->getChecked();
			else
				$fieldValue = $fieldObj->getValue();
		}else {
			developer_debug("Errore getDetailValue: non esiste nessuna field '$fieldId' nel detail '$idDetail'");
		}
		return $fieldValue;
	}
	
	public static function getDetailValues($idDetail){
		$sessionFields = wi400Detail::getDetailFields($idDetail);
		$fieldValues = array();
		foreach ($sessionFields as $key => $field){
			$fieldValues[$key] = wi400Detail::getDetailValue($idDetail, $key);
		}
		return $fieldValues;
	}
	
	public static function getDetailDescription($idDetail, $fieldId){
		$fieldValue = null;
		$sessionFields = wi400Detail::getDetailFields($idDetail);
		if (isset($sessionFields[$fieldId])){
			$fieldObj = $sessionFields[$fieldId];
			$fieldValue = $fieldObj->getDescription();
		}else {
			developer_debug("Errore getDetailDescription: non esiste nessuna field '$fieldId' nel detail '$idDetail'");
		}
		return $fieldValue;
	}

	public static function getDetailField($idDetail, $fieldId){
		$fieldObj = null;
		$sessionFields = wi400Detail::getDetailFields($idDetail);
		if (isset($sessionFields[$fieldId])){
			$fieldObj = $sessionFields[$fieldId];
		}else {
			developer_debug("Errore getDetailField: Non esiste nessuna field '$fieldId' nel detail '$idDetail'");
		}
		return $fieldObj;
	}

	public static function getDetailTitle($idDetail){
		$detailTitle = "";
		if (existDetail($idDetail)){
			$detailSessionObj = getDetail($idDetail);
			if (isset($detailSessionObj["TITLE"])){
				$detailTitle = $detailSessionObj["TITLE"];
			}else {
				developer_debug("Errore getDetailTitle: non esiste l'attributo TITLE nel detail '$idDetail'");
			}
		}else {
			developer_debug("Errore getDetailTitle: non esiste il detail '$idDetail'");
		}
		return $detailTitle;
	}

	public static function getDetailFields($idDetail=""){
		$fields = array();
		/*static $id, $cache; Ripetute chiamate su gateway vedono oggetto vecchio
		if (!isset($id)) $id="";
		if ($id==$idDetail and $idDetail!="") {
			return $cache;
		} */ 
		if ($idDetail != ""){
			if (existDetail($idDetail)){
				$detailSessionObj = getDetail($idDetail);
				$fields = $detailSessionObj["FIELDS"];
				//$cache = $fields;
				//$id = $idDetail;
			}else {
				developer_debug("Errore getDetailFields: non esiste il detail '$idDetail'");
			}		
		}else {
			developer_debug("Errore getDetailFields: passato il parametro idDetail vuoto");
		}
		return $fields;
	}

	public static function setDetailFields($idDetail, $fields){
		$detailSessionObj = array();
		if (existDetail($idDetail)){
			$detailSessionObj = getDetail($idDetail);
		}
		$detailSessionObj["FIELDS"] = $fields;
		saveDetail($idDetail, $detailSessionObj);
	}
	public static function deleteDetailField($idDetail, $fieldId){
		$detailSessionObj = array();
		if (existDetail($idDetail)){
			$detailSessionObj = getDetail($idDetail);
		}
		unset($detailSessionObj["FIELDS"][$fieldId]);
		saveDetail($idDetail, $detailSessionObj);
	}

	public static function setDetailField($idDetail, $fieldObj){

		$sessionFields = wi400Detail::getDetailFields($idDetail);
		$fieldId 	   = $fieldObj->getId();
		$fieldValue    = $fieldObj->getValue();
			
		if (isset($sessionFields[$fieldId])){
			$fieldObj = $sessionFields[$fieldId];
			$fieldObj->setValue($fieldValue);
		}
			
		$sessionFields[$fieldId] = $fieldObj;
			
		wi400Detail::setDetailFields($idDetail, $sessionFields);
	}

	public static function setDetailValue($idDetail, $fieldId, $fieldValue){
		$sessionFields = wi400Detail::getDetailFields($idDetail);
			
		if (isset($sessionFields[$fieldId])){
			$fieldObj = $sessionFields[$fieldId];
			$fieldObj->setValue($fieldValue);
			$sessionFields[$fieldId] = $fieldObj;

			wi400Detail::setDetailFields($idDetail, $sessionFields);
		}else {
			developer_debug("Errore setDetailValue: non esiste nessuna field '$fieldId' nel detail '$idDetail'");
		}
	}
	
	/**
	 * Funzione privata per settare gli attributi del detail con i parametri *all_detail
	 * 
	 * @param void
	 * @return void
	 */
	private function checkSettingsAllDetail() {
		global $actionContext, $db, $settings;
		
		$curr_azione = $actionContext->getAction();
		
		if(!isset($settings['check_field_enable_on_detail']) || $settings['check_field_enable_on_detail']===true) {
			$chiave_cache = $actionContext->getAction()."|".$this->idDetail."|D";
			
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
				$query = "SELECT WIDKEY FROM ZWIDETPA WHERE WIDAZI='{$actionContext->getAction()}' AND WIDID='{$this->idDetail}' AND WIDDOL='D'";
				$rs = $db->singleQuery($query);
				if($row = $db->fetch_array($rs)) {
					$this->isMapping = true;
				}
				
				$parametri_array[$chiave_cache] = $this->isMapping;
				put_serialized_file($cache_file, $parametri_array);
			}
		}

		if($this->isMapping) {
			$checkTools = array("Detail saved", "Only detail", "Nascondi detail");
			$func_key = array("saveDetail", "loadOnlyDetail", "status");
			foreach($checkTools as $chiave => $valore) {
				$func = $func_key[$chiave];
				$check = checkFieldEnableOnDetail($curr_azione, $this->idDetail, $valore, "D", "TOOL");
				if(is_array($check)) {
					$this->$func = $check[0];
				}else {
					$this->$func = $check;
				}
			}
		}else {
			//echo $this->idDetail."_non_mappatooo<br/>";
		}
	}

	public function getIdDetail(){
		return $this->idDetail;
	}

	public function getImages(){
		return $this->images;
	}

	public function addImage($image, $tabId = null){
		if ($tabId != null){
			if (!isset($this->tabs[$tabId])){
				$this->tabs[$tabId] = new wi400Tab();
			}
			$this->tabs[$tabId]->addImage($image);
		}else{
			$this->images[] = $image;
		}
	}

	public function getButtons(){
		return $this->buttons;
	}

	public function setButtons($buttons, $tabId = null){
		//$this->buttons = $buttons;
		if ($tabId != null){
			if (!isset($this->tabs[$tabId])){
				$this->tabs[$tabId] = new wi400Tab();
			}
			$this->tabs[$tabId]->setButtons($buttons);
		} else {
			$this->buttons = $buttons;
		}
	}

	public function addButton($button, $tabId = null){
		//$this->buttons[] = $button;
		if ($tabId != null){
			if (!isset($this->tabs[$tabId])){
				$this->tabs[$tabId] = new wi400Tab();
			}
			$this->tabs[$tabId]->addButton($button);
		} else {	
			$this->buttons[] = $button;
		}	
	}

	public function getFields(){
		return $this->fields;
	}

	public function addField($field, $tabId = null){
		 
		if ($field->getType() == "DETAIL") {
			$field->setType("SUB_DETAIL");
		}
		
		if ($field->getType() == "INPUT_TEXT"){
			
			if (is_array($field->getDecode())){
				$decodeObj = $field->getDecode();
				$decodeObj["ID_DETAIL"] = $this->idDetail;
				$field->setDecode($decodeObj);
			}
		}
		
		if ($tabId != null){
			if (!isset($this->tabs[$tabId])){
				$this->tabs[$tabId] = new wi400Tab();
			}
			$this->tabs[$tabId]->addField($field);
		}
		$this->fields[] = $field;
	}

	public function setFields($fieldArray, $tabId = null){
		foreach ($fieldArray as $field){
			$this->addField($field,$tabId);
		}
	}

	public function addParameter($parameterKey, $parameterValue){
		$this->parameters[$parameterKey] = $parameterValue;
	}

	public function getParameters(){
		return $this->parameters;
	}

	public function isEditable($isEditable){
		$this->editable = $isEditable;
	}

	public function getEditable(){
		return $this->editable;
	}

	public function setTitle($title){
		$this->title = $title;
	}

	public function getTitle(){
		return $this->title;
	}

	public function setTitleCss($titleCss){
		$this->titleCss = $titleCss;
	}

	public function getTitleCss(){
		return $this->titleCss;
	}

	public function setImageDetail($imageDetail){
		$this->imageDetail = $imageDetail;
	}

	public function getImageDetail(){
		return $this->imageDetail;
	}

	public function setSource($source){
		$this->source = $source;
	}
	
	public function getSource(){
		return $this->source;
	}
	
	public function setSourceField($field,$value){
		$this->source[$field] = $value;
	}
	
	public function getSourceField($field){
		return $this->source[$field];
	}

	public function addTab($tabId, $description, $colsNum = 1){
		if (!isset($this->tabs[$tabId])){
			$this->tabs[$tabId] = new wi400Tab($tabId, $description, $colsNum);
		}else{
			$this->tabs[$tabId]->setDescription($description);
		}
	}

	public function getTabs(){
		return $this->tabs;
	}
	
	public function getTab($tabId){
		return $this->tabs[$tabId];
	}
	
	public function setActiveTab($tabId) {
		$this->tabs[$tabId]->setActive(true);
	}
	
	/**
	 * Funzioni js scatenate nel momento in cui si seleziona il specifico tab
	 * 
	 * @param string $idTab
	 * @param string $function
	 */
	public function addJsToTab($idTab, $function) {
		if(isset($this->jsToTab[$idTab])) {
			array_push($this->jsToTab[$idTab], $function);
		}else {
			$this->jsToTab[$idTab] = array($function);
		}
	}
	
	/**
	 * Ritorna l'array delle funzioni js che sono state aggiunte alla tab
	 * 
	 * @param string $idTab
	 * @return array
	 */
	public function getJsToTab($idTab) {
		if(isset($this->jsToTab[$idTab])) {
			return $this->jsToTab[$idTab];
		}else {
			return array();
		}
	}
	
	/**
	 * @desc se impostato a true segna i campi "required" con un asteristo rosso vicino alla descrizione
	 * @param boolean $val
	 */
	public function setShowFieldRequired($val) {
		$this->showFieldRequired = $val;
	}
	
	public function getShowFieldRequired() {
		$this->showFieldRequired;
	}
	
	public function setShowLowerButtons($show) {
		$this->showLowerButtons = $show;
	}
	
	public function getShowLowerButtons() {
		return $this->showLowerButtons;
	}
	
	public function setShowLowerSaveDetail($show) {
		$this->showLowerSaveDetail = $show;
	}
	
	public function getShowLowerSaveDetail() {
		return $this->showLowerSaveDetail;
	}
	
	public function setShowTopButtons($show) {
		$this->showTopButtons = $show;
	}
	
	public function getShowTopButtons() {
		return $this->showTopButtons;
	}
	
	public function setShowTopButtonsCond($show) {
		$this->showTopButtonsCond = $show;
	}
	
	public function getShowTopButtonsCond() {
		return $this->showTopButtonsCond;
	}
	
	public function checkShowTopButtonsCond() {
		if($this->showTopButtonsCond===true) {
			// @todo Stabilire le condizioni secondo le quali vengono visualizzati i bottoni delle azioni anche in cima al detail
				
			return true;
		}
	
		return false;
	}
	
	public function setShowTopSaveDetail($show) {
		$this->showTopSaveDetail = $show;
	}
	
	public function getShowTopSaveDetail() {
		return $this->showTopSaveDetail;
	}

	public function setFromArray($field){

		$array = $field->getFromArray();

		// Setto la grandezza massima del campo
		if  (isset($array['VIDEO_LENGTH'])){
			if (method_exists($field, 'setMaxLength')) {
				$field->setMaxLength($array['VIDEO_LENGTH']);
			}
			// Setto la lunghezza a video dell'input Text
			if ($array['VIDEO_LENGTH'] > 45) $field->setSize(45);
			else $field->setSize($array['VIDEO_LENGTH']);
		}

		// Setto i controlli di numericità
		if  ((isset($array['DATA_TYPE_STRING'])) && ($array['DATA_TYPE_STRING']=="NUMERIC" || $array['DATA_TYPE_STRING']=="DECIMAL") && ($array['NUM_SCALE']<=0)){
			$field->addValidation("numeric");
			$field->setMask('1234567890');
		}
		// Se ènumerico e ci sono decimali cambio la maschera di editazione
		if  ((isset($array['DATA_TYPE_STRING'])) && ($array['DATA_TYPE_STRING']=="NUMERIC" || $array['DATA_TYPE_STRING']=="DECIMAL") && ($array['NUM_SCALE']!=0)){
			$field->setDecimals($array['NUM_SCALE']);
			$field->setMask('1234567890,.');
			$field->addValidation("double");
		}
	}

	public function disposeButtons($position) {
		global $appBase,$actionContext,$base_path,$messageContext,$db,$viewContext, $temaDir,$settings, $validationCounter;
		
//		echo "POSITION: $position<br>";
		
		$showButtons = false;
		$showSaveDetail = true;
		
		$cache_abil = array();
		
		if($position=="TOP") {
			if($this->getShowTopButtons()=="") {
				if(!isset($settings['showTopButtons'])) {
					$this->setShowTopButtons(false);
				}
				else {
					$this->setShowTopButtons($settings['showTopButtons']);
				}
			}
			
			if(($this->getShowTopButtons()===true || $this->checkShowTopButtonsCond()===true))
				$showButtons = true;
			
			if($this->getShowTopSaveDetail()=="") {
				if(!isset($settings['showTopSaveDetail'])) {
					$this->setShowTopSaveDetail(true);
				}
				else {
					$this->setShowTopSaveDetail($settings['showTopSaveDetail']);
				}
				
			}
			
			if($this->getShowTopSaveDetail()===false)
				$showSaveDetail = false;
				
			$filterStyle = "wi400-grid-filter";
		}
		else if($position=="LOWER") {
			if($this->getShowLowerButtons()=="") {
				if(!isset($settings['showLowerButtons'])) {
					$this->setShowLowerButtons(true);
				}
				else {
					$this->setShowLowerButtons($settings['showLowerButtons']);
				}
			}
			
			if($this->getShowLowerButtons()===true)
				$showButtons = true;
			
			if($this->getShowLowerSaveDetail()=="") {
				if(!isset($settings['showLowerSaveDetail'])) {
					$this->setShowLowerSaveDetail(false);
				}
				else {
					$this->setShowLowerSaveDetail($settings['showLowerSaveDetail']);
				}
			}
				
			if($this->getShowLowerSaveDetail()===false)
				$showSaveDetail = false;
		
			$filterStyle = "wi400-grid-filter-lower";
		}
//		echo "SHOW BUTTONS: $showButtons - SHOW SAVE DETAIL: $showSaveDetail<br";

		if($showButtons===true || $showSaveDetail===true) {
			if(sizeof($this->getButtons()) >0 || ($this->getSaveDetail() || $this->getResetDetail())) {
?>
				<table width="100%" cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td width="40%">
<?				
			}
		}
		
		if($showButtons===true) {
			if(sizeof($this->getButtons()) > 0) {
				echo "<table  cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class='wi400-grid-button-detail'>
					<tr>
						<td>";
				
				foreach($this->getButtons() as $button) {
					$button->dispose(true);
				}
				
				echo "</td>
					</tr>
				</table>";
				
?>
						</td>
<?php
			}
		}
		
		if($showSaveDetail===true) {
			if ($this->getSaveDetail() || $this->getResetDetail()) {
				if($showButtons===true && sizeof($this->getButtons()) > 0) {
?>
						<td width="20%"></td>
						<td width="40%">
<?php 
					}

?>
				<table width="100%" cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td align="right"><input type="hidden"
							name="<?=$this->idDetail ?>_DETAIL_SAVE"
							ID="<?=$this->idDetail ?>_DETAIL_SAVE" value="">
							<table cellpadding="0" cellspacing="0" class="<?=$filterStyle ?>">
								<tr class="detail-row">
<?	
			}
			
			//***********************************
			// VALORI PERSONALIZZATI
			//***********************************
			
			if ($this->getSaveDetail()){
				if (is_array($this->getCustomValues()) && sizeof($this->getCustomValues()) > 0) { ?>
					<td>&nbsp;&nbsp; 
<?
						$mySelect = new wi400InputSelect($this->idDetail."_CUSTOM_FILTER");
						$mySelect->setFirstLabel(_t("Applica Valori Personalizzati"));
							
						$gateway="";
						if ($actionContext->getGateway()!="") {
							$gateway = "&g=".$actionContext->getGateway();
						}
						
						$mySelect->setOnChange("doSubmit('LOAD_DETAIL&IDDETAIL=".$this->idDetail.$gateway."')");
						$mySelect->setStyleClass("select-field");
										
						foreach ($this->getCustomValues() as $key => $value){
							if ($key != "DEFAULT_DETAIL"){
								$mySelect->addOption($key);
							}
						}
						
						$det_value = "";
										
						if(isset($this->customValues["DEFAULT_DETAIL"]))
							$det_value = $this->customValues["DEFAULT_DETAIL"];
						
						if(isset($_SESSION[$this->idDetail."_LOAD_DETAIL"]))					
							$det_value = $_SESSION[$this->idDetail."_ID_LOAD_DETAIL"];
//						echo "DET_VALUE: $det_value<br>";
							
						$mySelect->setValue($det_value);
						$mySelect->dispose();
?>
					</td>
<?					
					if(!$this->getLoadOnlyDetail()) {
?>
						<td>
							<input style="margin-right: 5px; margin-left: 5px;"
								class="wi400-pointer" type="image" title="Configura Valori"
								onClick="manageDetail('<?= $this->idDetail ?>')"
								src="<?=  $temaDir ?>images/grid/config.gif">
						</td>
<?
					}
					
				} // end if is_array								
?>
				<td>
<?
					if(!$this->getLoadOnlyDetail()) {
?>
						<input style="margin-right: 5px; margin-left: 5px;"
							class="wi400-pointer" type="image" title="<?=_t('VALUE_SAVE')?>"
							onClick="doSaveDetail('<?= $this->idDetail ?>')"
							src="themes/common/images/save.png">
<?
					}
?>	
					<input style="margin-right: 5px; margin-left: 5px;"
						class="wi400-pointer" type="image" title="<?=_t('VALUE_EMPTY')?>"
						onClick="doResetDetail('<?= $this->idDetail ?>')"
						src="themes/common/images/empty.png">

				</td>
<?
			}
			//***********************************
			// RESET DETAIL AND CLEAN DETAIL
			//***********************************
			if ($this->getResetDetail()){
?>
				<td>
					<input style="margin-right: 5px; margin-left: 5px;"
						class="wi400-pointer" type="image" title="<?=_t('CLEAN')?>"
						onClick="doResetDetail('<?= $this->idDetail ?>')"
						src="themes/common/images/clean.png"><input style="margin-right: 5px; margin-left: 5px;"		
						class="wi400-pointer" type="image" title="<?=_t('RELOAD')?>"
						onClick="doSubmit('<?= $actionContext->getAction()?>', '<?= $actionContext->getForm() ?>', false, false, false,false, '<?= $this->idDetail ?>')"
						src="<?=  $temaDir ?>images/grid/reload.gif">
				</td>
<?
			}
					
			if ($this->getSaveDetail() || $this->getResetDetail()) {
?>			
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
<?
			}
		}
		
		if($showButtons===true || $showSaveDetail===true) {
			if(sizeof($this->getButtons()) >0 || ($this->getSaveDetail() || $this->getResetDetail())) {
?>
					</tr>
				</table>
<?				
			}
		}
	}

	public function dispose($reloadAjax=False) {
		global $appBase,$actionContext,$base_path,$messageContext,$db,$viewContext, $temaDir,$settings, $validationCounter;

        if ($this->status =="") {
        	$this->status ="OPEN";
        }
        if ($reloadAjax==False) {
		?>
		<div style="width: 100%">
			<div onClick="openDetail('<?= $this->idDetail ?>')" id="<?= $this->idDetail ?>_opener" style="display: none;">
				<div class="detail-header-cell">+ <?= $this->getTitle()?></div>
			</div>
		</div>
		<input type="hidden" name="<?=$this->idDetail ?>_STATUS" ID="<?=$this->idDetail ?>_STATUS" value="<?=$this->status ?>">
		<? } ?>
		<div id="<?= $this->idDetail ?>_slider"><?    	

			if ($this->getSaveDetail()){
				// *************************************************
				// CARICAMENTO VALORI PERSONALIZZATI DA FILE
				// *************************************************
				$loaded_detail_fields = array();
				$this->setCustomValues($this->loadCustomValues($this->idDetail));
//				echo "CUSTOM VALUES:<pre>"; print_r($this->getCustomValues()); echo "</pre>";
			
				if (is_array($this->getCustomValues()) && sizeof($this->getCustomValues()) > 0){
					if (isset($this->customValues["DEFAULT_DETAIL"]) && !existDetail($this->idDetail)){
						$loaded_detail_fields = $this->customValues[$this->customValues["DEFAULT_DETAIL"]];
					}
				}
			}
	
			$fieldsList = $this->getFields();
	
			$fieldCounter = 0;
			//static $validationCounter = 0;
			if (!isset($validationCounter)) {
				$validationCounter = 0;
			}
			//$errorMessages = $messageContext->getMessages();
	
			$autofocus = true;
			
			/*
			 * Non serve più con la nuova gestione della validazione.
			 * I campi da validare ed il tipo di validazione la si identifica nella SESSION istanziata sopra.
			 *
			 if (!isset($_SESSION[$actionContext->getAction()."_".$actionContext->getForm()."_FORM_VALIDATION"])){
			 	$_SESSION[$actionContext->getAction()."_".$actionContext->getForm()."_FORM_VALIDATION"] = $this;
			 }
			 */
	
			if ($this->editable){
				//$this->colsNum = 1;
			}
			
			$colSelect = 0;
			if ($this->getSelectable()){
				$colSelect = 1;
			}
			
			// **************************************************
			// INIZIO GESTIONE WI400 TABS - HEADER
			// **************************************************
			$hasTabs = false;
			if (sizeof($this->tabs)>0 && !isset($this->tabs["default"])){

				$hasTabs = true;
			
				// Ricerca tab attivo. Se non trovato rende attivo il primo
				$foundActive = false;
				$firstTab = "";
				$tabActive = "";
				if (isset($_REQUEST[$this->idDetail."_ACTIVE_TAB"]) && $_REQUEST[$this->idDetail."_ACTIVE_TAB"] != ""){
					$tabActive = $_REQUEST[$this->idDetail."_ACTIVE_TAB"];
				}
				if (isset($_SESSION['LAST_FOCUSED_TAB']) && $_SESSION['LAST_FOCUSED_TAB']!="" && $this->getRefreshFocus()==True){
					$tabActive = $_SESSION['LAST_FOCUSED_TAB'];
				}
				if ($tabActive!="") {	
					foreach($this->tabs as $tabId => $tab) {
						if ($tabActive == $tabId){
							$this->tabs[$tabId]->setActive(true);
						}else{
							$this->tabs[$tabId]->setActive(false);
						}
					}
				}else{
					foreach($this->tabs as $tabId => $tab) {
						if ($tab->isActive()) $foundActive = true;
						if ($firstTab == "") $firstTab = $tabId;
					}
					if (!$foundActive) $this->tabs[$firstTab]->setActive(true);
				}
			
			}else{
				// Nessun tab. Viene inserito uno di default
				$this->addTab("default","");
				$this->tabs["default"]->setFields($fieldsList);
				$this->tabs["default"]->setActive(true);
			}
	
			$this->disposeButtons('TOP');
	
			if ($hasTabs) {
	?>			<script>
	<?
					echo "var ".$this->idDetail."_wi400TabsArray = new Array(";
					$isFirst = true;
					foreach($this->tabs as $tabId => $tab) {
						if (!$isFirst){
							echo ",";
						}else{
							$isFirst = false;
						}
						echo "'".$tabId."'";
					}
					echo ");\n";
					
					echo "var ".$this->idDetail."_wi400TabsErrors = new wi400Map();\n";
					$tabCounter = 0;
					$tabActive = "";
					foreach($this->tabs as $tabId => $tab){
						if ($tab->isActive()) {
							$tabActive = $tabId;
							echo "var ".$this->idDetail."_wi400TabsActiveCounter = ".$tabCounter.";\n";
						}
						echo $this->idDetail."_wi400TabsErrors.put('".$tabId."',false);\n";
						$tabCounter++;
					}
					
					echo "yav.id = '".$this->idDetail."';";
	?>
				</script>
				<input type="hidden" value="<?= $tabActive ?>" id="<?= $this->idDetail ?>_ACTIVE_TAB" name="<?= $this->idDetail ?>_ACTIVE_TAB">
				<table class="wi400TabHeader" border="0" cellpadding="0" cellspacing="0">
					<tr><?
						$isFirst = true;
						$tabCounter = 0;
						foreach($this->tabs as $tabId => $tab){
							if ($isFirst){
								$tab->setActive(true);
								$isFirst = false;
								$this->tabs[$tabId] = $tab;
							}
							?>
							<td id="<?= $this->idDetail ?>_wi400Tab_<?= $tabId ?>"
								onClick="wi400Tab_select('<?= $this->idDetail ?>','<?= $tabId ?>');<?= implode("", $this->getJsToTab($tabId)) ?>"
								class="wi400Tab<? if ($tab->isActive()) echo "Active" ?>"><?= $tab->getDescription() ?></td>
							<td>&nbsp;</td>
							<?
							$tabCounter++;
						}?>
						<td width="100%"></td>
					</tr>
				</table>
				<table id="<?= $this->idDetail ?>_wi400Tab_Container"
					class="wi400TabContainer" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td><?	
			} // END IF HAS TAB
	
			foreach($this->tabs as $tabId => $tab) {
			    $this->currentTab = $tabId;
				$fieldsList = $tab->getFields();
				$divShow = "Hide";
				if ($tab->isActive()) $divShow = "Show";
				echo "<div id=\"".$this->idDetail."_".$tabId."\" class=\"wi400TabContent_".$divShow."\">";
				// **************************************************
			
				//***********************************
			
				?>	<input type="hidden" name="IDDETAIL[]" value="<?=$this->idDetail ?>">
					<table width="<?= $this->width ?>" id="tableWidth" cellpadding="0" cellspacing="0" border="0" class="work-area">
						<tr><?
							$imagesArray = array();
							if ($hasTabs) {
								$imagesArray = $tab->getImages();
							}else {
								$imagesArray = $this->getImages();
							}
				
							if (is_array($imagesArray) && sizeof($imagesArray)>0){
								$imageHtml = "";
								foreach($imagesArray as $image){
									$imageHtml.= $image->getHtml()."<br>";
								}
								if (trim($imageHtml) != "<br>"){ ?>
									<td valign="middle" align="center" class="detail-image-cell"><?= $imageHtml ?>
									</td>
								<?}
							}?>
							<td valign="top" class="detail-data-cell">
							<table width="100%" cellpadding="0" cellspacing="0" class="wi400-grid">
								<?
								if ($hasTabs) {
									$this->colsNum = $tab->getColsNum();
								}
			
								if ($this->title != "") { ?>
									<tr class="detail-header <?= $this->getTitleCss() ?>">
										<td colspan="<?= 2 * $this->colsNum + $colSelect?>" class="detail-header-cell">
											<span style="margin-right:5px;float:left;">
												<input class="wi400-pointer" type="image" src="themes/common/images/grid/collapse.gif" onClick="closeDetail('<?= $this->idDetail ?>');" title="<?=_t('HIDE')?>"/>
											</span><?= $this->title ?>
										</td>
									</tr>
								<?}
			
								$detailCounter = 0;
								$columnCounter = 0;
			
								// *********************************************
								// Se presente un caricamento di valori di
								// default da utente li carico e consumo l'array
								// *********************************************
			
								if (isset($_SESSION[$this->idDetail."_LOAD_DETAIL"])){
									$loaded_detail_fields = $_SESSION[$this->idDetail."_LOAD_DETAIL"];
									unset($_SESSION[$this->idDetail."_LOAD_DETAIL"]);
								}
								// *********************************************
								
								$all_def_flag = 1;
								$all_def_value = null;
								foreach($fieldsList as $field){
									if($this->isMapping) {
										$isEnable = checkFieldEnableOnDetail($actionContext->getAction(), $this->getIdDetail(), $field, "D");
										if(is_array($isEnable) && $isEnable[0] === 'hide') {
											$field = new wi400InputHidden($field->getId());
											$field->setValue($isEnable[1] != null ? $isEnable[1] : '');
										}else {
											//if(is_string($isEnable) && $isEnable == "readonly") $field->setReadOnly(true);
											if(is_array($isEnable) && $isEnable[0] === "readonly") $field->setReadOnly(true);
											$all_def_value = (is_array($isEnable) && isset($isEnable[1])) ? $isEnable[1] : null;
										}
									}
									
									if ($field->getType()=="HIDDEN" && get_class($field) == 'wi400InputHidden') {
										$this->fieldSessionList[$field->getId()] = $field;
										
										if($field->getDispose()) {
											$field->dispose();
										}
										
										continue;
									}
									
									$tdLabelWidth = "";
									$tdValueWidth = "";
									$all_def_flag = 1;
									// Se il dettaglio è readOnly Imposta la proprietà su tutti i campi di INPUT come readonly
									if ($this->getReadOnly() == True && ($field->getType() =="INPUT_TEXT" or $field->getType() == "CHECKBOX" or $field->getType() =="SELECT")) {
										$field->setReadOnly(True);
									}
									//echo "<h1>".sizeof($this->colsWidth)."</h1>";
									if (sizeof($this->colsWidth)>0){
										$widthCounter = $columnCounter * 2;
										if (isset($this->colsWidth[$widthCounter])){
											$tdLabelWidth = ' width="50" ';
										}
										if (isset($this->colsWidth[$widthCounter + 1])){
											$max_width = intval($this->colsWidth[$widthCounter + 1]);
											$tdValueWidth = ' width="40%" style="max-width: '.$max_width.'px;"';
										}
									}
									
									$detailCounter++;
									$columnCounter++;
			
									// *************************************************
									// Consumo array valori salvati
									// *************************************************
									if (method_exists($field, "getSaveFile") && $field->getSaveFile() && isset($loaded_detail_fields[$field->getId()])){
										$loaded_field = $loaded_detail_fields[$field->getId()];
										$field->setValue($loaded_field->getValue());
										if ($field->getType() == "CHECKBOX"){
											$field->setChecked($loaded_field->getChecked());
										}
										$all_def_flag = 0;
									}
									// *********************************************
			
									// Controllo se il field è a sua volta un detail
									if(in_array($field->getType(), array("SUB_DETAIL", "LIST", "DRAG_AND_DROP"))) {
										echo "<tr class='detail-row'><td colspan='2'>";
										$field->dispose();
										if (sizeof($fieldsList) > $detailCounter) echo "<br>";
										echo "</td></tr>";
									}else {
										$field->setIdDetail($this->getIdDetail());
										if ($hasTabs) {
											$field->setIdTab($tabId);
										}
										if ($this->getCheckUpdate()){
											$field->setCheckUpdate(true);	
										}
										
										if (sizeof($field->getFromArray())){
											// Setto valori da db
											$this->setFromArray($field);
										}
			
										$fieldId = $field->getId();
			
										// Se il valore è ancora vuoto lo ricerco nel source
										if ($field->getValue() == ""){
											// Recupero il source del campo o in caso non sia settato il source del form
											$fieldSource = $field->getSource();
											if (!isset($fieldSource)){
												$fieldSource = $this->getSource();
											}
												
											if (is_array($fieldSource) && sizeof($fieldSource)>0){
												if (isset($fieldSource[$fieldId])){
													$field->setValue($fieldSource[$fieldId]);
													$all_def_flag = 0;
												}
											}
										}
			
										// Se il valore è ancora vuoto controllo non sia impostato uno userApplicationData
										if ($field->getType() == "INPUT_TEXT"
											&& $field->getValue() == ""
											&& $field->getUserApplicationValue() != ""
											&& isset($_SESSION["USER_APPLICATION_DATA"])
											&& isset($_SESSION["USER_APPLICATION_DATA"][$field->getUserApplicationValue()])){
											$field->setValue($_SESSION["USER_APPLICATION_DATA"][$field->getUserApplicationValue()]);
											$all_def_flag = 0;
										}
			
										// Se salvato in sessione recupero field
										if (isset($this->fieldSessionList[$field->getId()])) {
											
											$fieldSession = $this->fieldSessionList[$field->getId()];
											if ($field->getType() != "TEXT"){
												if ($field->getType() == "CHECKBOX"){
													$field->setChecked($fieldSession->getChecked());
												}
												$field->setValue($fieldSession->getValue());
												$all_def_flag = 0;
												//echo "<br>".$field->getId()." : ".$fieldSession->getValue();
											} else {
												// Se ricarica AJAX passo anche i TEXT
												if ($reloadAjax==True) {
													$field->setValue($fieldSession->getValue());
												}
											}
										}
										
										// Valore di default con l'azione ABILITAZIONI_CAMPI_DETAIL
//										if($all_def_flag && (isset($all_def_value) && $all_def_value != null)) {
										if($all_def_flag && (isset($all_def_value) && $all_def_value !== null)) {
/*
											if($field->getValue() == null) {
												$field->setValue($all_def_value);
											}
*/
//											echo "<font color='orange'>DEF VALUE: $all_def_value</font><br>";
											
											if ($field->getType() == "CHECKBOX"){
//												echo "<font color='blue'>CHECKBOX:"; var_dump($field->getChecked()); echo "<br>";
												
												if($field->getChecked() == null) {
//												if($field->getChecked() === null) {
//													echo "<font color='green'>SET DEFAULT</font><br>";
													$field->setChecked($all_def_value);
												}
											}
											else {
//												echo "VAL: "; var_dump($field->getValue()); echo "<br>";
//												if($field->getValue() == null) {
												if($field->getValue() === null) {
//													echo "<font color='green'>SET INPUT DEFAULT</font><br>";
													$field->setValue($all_def_value);
												}
											}
										}
										
										if ($field->getType() != "INPUT_TEXT" || !$field->getShowMultiple()){
											// Decodifica campo
											if ($field->getDecode() != "" && $field->getType() != "TEXT"){
												if ($field->getValue() != ""){
													// Nuova decodifica
													$decodeParameters = $field->getDecode();
													$decodeType = "table";
													if (isset($decodeParameters["TYPE"])){
														$decodeType = $decodeParameters["TYPE"];
													}
													//require_once $base_path.'/package/'.$settings['package'].'/decodingclass/'.$decodeType.".php";
													require_once p13nPackage($decodeType);
													$decodeClass = new $decodeType();
													$decodeClass->setDecodeParameters($decodeParameters);
													$decodeClass->setFieldLabel($field->getLabel());
														
													$decodeClass->setFieldId($fieldId);
													$decodeClass->setFieldValue($field->getValue());
													$field->setDescription($decodeClass->decode());
														
												}else{
													$field->setDescription("");
												}
											}
										}
			
										$hiddenClass = "";
										if ($field->getType()=="HIDDEN" && $this->colsNum == 1) {
											$hiddenClass = "detail-row-hidden";
										}
										
										if ($columnCounter == 1) {
											echo '<tr class="detail-row '.$hiddenClass.'">';
										}
			
										$tdValueId = $this->getIdDetail()."_".$field->getId();
										if ($this->getSelectable() == true){
											$radioId = $field->getId();
											?>
											<td width="5" class="detail-select-cell" id="<?= $tdValueId ?>_SELECT" <?if ($fieldCounter == 0){echo 'style="border-top:none"';}?>>
												<input onClick="wi400Detail_select_row('<?= $this->getIdDetail()?>','<?= $field->getId()?>',this)"
														type="radio" name="<?= $this->getIdDetail() ?>" value="<?= $radioId ?>">
											</td>
											<?
										}
										if (trim($field->getLabel()) == ""){
											$altezza ="";
											if ($field->getHeight()>0) {
												$altezza = ' height="'.$field->getHeight().'" ';
											}
											$textStyle = "";
											if ($field->getType() == "TEXT" && $field->getStyle() != ""){
												$textStyle = "text-align: right;";
											}
											?>
											<td colspan="2" class="detail-value-cell" <?= $textStyle ?> <?= $altezza ?>>
												<table width="100%" border="0" cellpadding="0" cellspacing="0">
													<tr>
														<? $field->dispose() ?>
													</tr>
												</table>
											</td>
											<?
										}else if ($field->getType() == "TABLE"){ ?>
											<td class="detail-label-cell" id="<?= $tdValueId ?>_LABEL" <?= $tdLabelWidth ?> <?if ($fieldCounter == 0){echo 'style="border-top:none"';} ?>>
												<?	$label = $field->getLabel();
												if (in_array("required", $field->getValidations())) {
													if((isset($settings['show_field_required']) && $settings['show_field_required']) || $this->showFieldRequired ) {
														$label .= "&nbsp;<font color='red' size='4'>*</font>";
													}
												}
												echo $label;
												?>
											</td>
											<td class="detail-value-cell"><? $field->dispose() ?></td>
										<?} else {?>
											<td class="detail-label-cell" id="<?= $tdValueId ?>_LABEL"
												<?if(!strrpos($_SERVER['HTTP_USER_AGENT'], "MSIE")) { echo 'width="50"'; } ?>
												<?=$tdLabelWidth ?>
												<?if ($fieldCounter == 0){echo 'style="border-top:none"';} ?>> <?
												//CONTENUTO ------
												$label = $field->getLabel();
												if (in_array("required", $field->getValidations())) {
													if((isset($settings['show_field_required']) && $settings['show_field_required']) || $this->showFieldRequired ) {
														$label .= "&nbsp;<font color='red' size='4'>*</font>";
													}
												}	
												echo $label;
												?>
											</td>
								
			<?
											// Allineamento testo
											$textAlign = "";				
											if ($field->getType() == "TEXT" && $field->getAlign() != ""){
												$textAlign = "align='right'";
											}
											/*$textStyle = "";
											if ($field->getType() == "TEXT" && $field->getStyle() != ""){
												$textStyle = "text-align: right;";
											}*/
			?>
								
											<td class="detail-value-cell"
													<?
													if($this->getColsNum() > 1) {
														echo "width='40%' style='max-width: 200px;'";
													}
													if ($fieldCounter == 0) {
														echo 'style="border-top:none; max-width: 120px;"';
													}else{
														if($this->getColsNum() < 1) {
															echo 'style="max-width: 100px;"';
														}
													}
													
													if ($this->getColsNum() == 1){?>
														width="100%" <? 
													}?> 
													<?= $tdValueWidth ?>
													 <?= $textAlign ?>>
												<?php 
												$styleTable = "";
												if (method_exists($field, "getHideHeaderTable") && $field->getHideHeaderTable()==True) {
													$styleTable='style="display:none;"';
												}
												?>
												<table id="TABLEFIELD_<?= $fieldId?>" <?=$styleTable ?> border="0" cellpadding="0" cellspacing="0">
													<tr><? 
														if (method_exists($field, "setCleanable") && $field->getCleanable()=="") {
															$field->setCleanable($this->getInputFieldClean());
														}
														$field->dispose();?>
														<td class="detail-message-cell"><? 
															$errorMessages = $messageContext->getMessages();
								                            //Tentativo di messaggio con chiave
								                            $erroreCampo = False;
															if (array_key_exists($fieldId, $errorMessages)){
														        $myclass='innerError';
														        $tipo_error = $errorMessages[$fieldId][0];
														        if ($tipo_error == 'INFO') {
														           $myclass = 'innerInfo';
														        }
														        if(strtoupper($tipo_error) == "WARNING") $myclass = 'innerError innerWarning';
																echo "<span id='errorsDiv_".$fieldId."' class='$myclass' >".$errorMessages[$fieldId][1]."</span>";
																$erroreCampo = True;
															}else {
															// Ultimo tentativo ciclando sull'array dei messaggi sul terzo campo
																foreach($errorMessages as $key=>$value) {
																	if ($fieldId==$value[2]) {
																        $myclass='innerError';
																        if ($value[0]=='INFO') {
																           $myclass = 'innerInfo';
																        }
																		echo "<span id='errorsDiv_".$fieldId."' class='$myclass' >".$value[1]."</span>";
																		$erroreCampo = True;
																		break;
																	}
																}
															}
															if ($erroreCampo==False) {
																echo "<span id='errorsDiv_".$fieldId."'></span>";
															}
															// All Javascript Functions
															// EVITO VALIDAZIONE SE MULTI VALUE. VERRA' ESEGUITA LATO SERVER
															 $myScript="";
															//if ($field->getType() != "INPUT_TEXT" || !$field->getShowMultiple()){
															//if ($field->getType() == "INPUT_TEXT" || $field->getType() == "TEXT_AREA"){
															if (1==1) {
																$myScript= "<script type=\"text/javascript\">if (yav.get('$fieldId')){yav.objStyle.put('$fieldId', yav.get('$fieldId').className);}";
																// Segnalo errore se presente
																//if (array_key_exists($fieldId, $errorMessages)){
																if ($erroreCampo) {
																	$class_error = "errorField";
																	if(isset($tipo_error) && strtoupper($tipo_error) == "WARNING") $class_error = "warningField";
																	$myScript .="if (yav.get('$fieldId')){
											                			yav.get('$fieldId').className = yav.get('$fieldId').className + \" $class_error\";
																	}
												                	yav.serverErrors.put('$fieldId','{$field->getValue()}');";
																	if ($hasTabs){
																		$myScript .="{$this->idDetail}_wi400TabsErrors.put(\"$tabId\",true);";
																		//wi400Tab_refresh('$this->idDetail');";
																	}
																}
																if ($field->getDecode() != "" && $field->getType() != "TEXT"){
																	$myScript .="rules[$validationCounter]='{$field->getId()}:{$field->getLabel()}|custom|decodeValidation(\"{$field->getId()}\",\""._t("VALORE_DI").$field->getLabel()._t("NON_VALIDO")."\")';";
																	$validationCounter++;
																}
																if (sizeof($field->getValidations()) > 0 || $field->getMask() != "" || $field->getInfo() != ""){
																	foreach ($field->getValidations() as $validation){
																			if(method_exists($field, "getShowMultiple") && $field->getShowMultiple() && $validation == "required" && !$field->getLookUp()) {
																				$myScript .="rules[$validationCounter]=\"{$field->getId()}:{$field->getLabel()}|custom|checkRequiredShowMultiple('{$field->getId()}', '{$field->getLabel()}')\";";
																			}else {
																				$myScript .="rules[$validationCounter]=\"{$field->getId()}:{$field->getLabel()}|$validation\";";
																			}
																			$validationCounter++;
																	}
																	if($field->getType() == "INPUT_TEXT" && ($field->getMinValues() > 0 || $field->getMaxValues() > 0)) {
																		$max = $field->getMaxValues();
																		$min = $field->getMinValues();
																		$myScript .= "rules[$validationCounter]='{$field->getId()}:{$field->getLabel()}|custom|checkMinMaxValues(\"{$field->getId()}\", \"{$field->getLabel()}\", \"$min\", \"$max\");';";
																		$validationCounter++;
																	}
																	if ($field->getMask() != ""){
																			$myScript .="rules[$validationCounter]=\"{$field->getId()}|mask|{$field->getMask()}\";";
																			$validationCounter++;
																		}
																	if ($field->getInfo() != ""){
																		$myScript .="yav.addHelp(\"{$field->getId()}\",\"{$field->getInfo()}\");";
																	} 
																 } 
																	
																	$myScript .="yav.objTabs.put(\"{$field->getId()}\",\"$tabId\");";
																	// Autofocus sul primo campo
																if ($autofocus && $field->getReadonly() == false){ 
																	$autofocus = false;
																//window["AUTO_FOCUS_FIELD_ID"] = "<--?= $field->getId() ?-->";
																 } 
																 $myScript .="</script>";
																 echo $myScript;				 
															}  // END MULTY VALUE?>
														</td>
													</tr>
												</table>
												<?
												// ********************************************************************
												// GESTIONE MULTI VALUE
												// ********************************************************************
//												if ($field->getType() == "INPUT_TEXT" && $field->getShowMultiple()){
												if (in_array($field->getType(), array("INPUT_TEXT", "HIDDEN")) && method_exists($field, "getShowMultiple") && $field->getShowMultiple()){
?>
													<ul id="<?= $field->getId()?>_PARENT" class="deactiveOrder"><?
														$inputValueArray = $field->getValue();
														$fieldArrayCounter = 0;
														if (is_array($inputValueArray)){
															// Nuova decodifica
															$decodeParameters = $field->getDecode();
															$decodeType = "table";
															if (isset($decodeParameters["TYPE"])){
																$decodeType = $decodeParameters["TYPE"];
															}
															//require_once $routine_path.'/decoding/siad/'.$decodeType.".php";
															//require_once $base_path.'/package/'.$settings['package'].'/decodingclass/'.$decodeType.".php";
															require_once p13nPackage($decodeType);
									
															$decodeClass = new $decodeType();
															$decodeClass->setDecodeParameters($decodeParameters);
															$decodeClass->setFieldLabel($field->getLabel());
															
															foreach ($inputValueArray as $inputValue){
																$fieldArrayCounter++;
									
																$multyFieldId = $field->getName().'_'.$fieldArrayCounter;
									
																$fieldHTML = '<li id="'.$field->getName().$fieldArrayCounter.'" class="deactiveOrder sizeDeactiveOrder" >';
									
																//$fieldHTML.= '<table border="0" cellpadding="0" cellspacing="0"><tr>';
																if ($field->getSortMultiple()){
																	$fieldHTML .= '<img class="wi400-updown" src="themes/common/images/triangle_up_down.png"></img>';
																}
																else {
																	$fieldHTML .= '<div class="wi400-updown-none"></div>';
																}
																//$fieldHTML.='<td><input id="'.$multyFieldId.'" name="'.$field->getName().'[]" type="TEXT" value="'.$inputValue.'" size="'.$field->getSize().'" readonly class="inputtextDisabled"></td>';
																$fieldHTML.='<input id="'.$multyFieldId.'" name="'.$field->getName().'[]" type="TEXT" value="'.$inputValue.'" size="'.$field->getSize().'" readonly class="inputtextDisabled multiInputtextDisabled">';
																//$fieldHTML.='<td><img onClick="multiFieldAddRemove(\'REMOVE\',\''.$field->getName().'\', '.$fieldArrayCounter.')" hspace="5" class="wi400-pointer" src="'.$temaDir.'images/remove.png" title="'._t('REMOVE').'"></td>';
																if($field->getDisabled() != true && $field->getReadonly() != true) {
																	$fieldHTML.='<img onClick="multiFieldAddRemove(\'REMOVE\',\''.$field->getName().'\', '.$fieldArrayCounter.')" hspace="5" class="multi-wi400-pointer" src="'.$temaDir.'images/remove.png" title="'._t('REMOVE').'">';
																}
																
																if (array_key_exists($multyFieldId, $errorMessages)){
																	//$fieldHTML.='<td class="detail-message-cell">';
																	$fieldHTML.= "<span id='errorsDiv_".$field->getName().'_'.$fieldArrayCounter."' class='innerError' >".$errorMessages[$multyFieldId][1]."</span>";
																	//$fieldHTML.='</td>';
																}
									
																if ($field->getDecode() != ""){
																	$decodeDescription = $viewContext->__get($multyFieldId."_DESCRIPTION");
									
																	if ($decodeDescription == ""){
																		$decodeClass->setFieldId($multyFieldId);
																		$decodeClass->setFieldValue($inputValue);
																		$decodeDescription = $decodeClass->decode();
																	}
									
																	if ($decodeDescription != ""){
																		$fieldHTML.= "<span class=\"multi-detail-message-cell\" id=\"".$multyFieldId."_DESCRIPTION\">&nbsp;".$decodeDescription."</span>";
																	}
																	//$fieldHTML .= '<script>alert("'.$decodeDescription.'");</script>';
									
																}
									
																//$fieldHTML.='</tr></table>';
																$fieldHTML.= '</li>';
									
																echo $fieldHTML;
															}
														}?>
													</ul>
													<script>window["<?= $field->getId()?>_COUNTER"]=<?= $fieldArrayCounter ?></script>
													<input type="hidden" id="<?= $field->getId()?>_COUNTER" name="<?= $field->getId()?>_COUNTER" value="<?= $fieldArrayCounter ?>"><?
												}
												// ******************** FINE GESTIONE MULTIVALUE ************************?>
											</td><?
								 		}// end check Text editor 
			
										if ($columnCounter == $this->colsNum) {
											$columnCounter = 0;
											echo "</tr>";
										}
										$fieldCounter++;
			
										// **********************************
										// Creazione oggetto per la sessione se segnalato (default: true)
										// **********************************
			
										if ($field->getSaveSession()){
											//$this->fieldValidationList[$field->getId()] = $field;
											$this->fieldSessionList[$field->getId()] = $field;
										}
			
									}// End if subdetail
								}// Foreach	?>
							</table>
							</td>
						</tr>
					</table><?
					// **************************************************
					// GESTIONE WI400 TABS - FOOTER
					// **************************************************
					if (is_array($tab->getButtons()) && sizeof($tab->getButtons()) > 0){
						echo "<table  style=\"margin-left:15px;border:1px solid #CCCCCC;border-top:none;background-color: #e0e0e0\"><tr><td>";
						foreach($tab->getButtons() as $button) {
							$button->dispose(true);
						}
						echo "</td></tr></table>";
					}
				echo "</div>";
			}
			if ($hasTabs){?> 
							<script>wi400Tab_refresh('<?= $this->idDetail ?>');</script>
						</td>
					</tr>
				</table><?
			}
			if ($this->getRefreshFocus()==True) { ?>
				<script>
					REFRESH_FOCUS = true;
				</script>
				<?
				if (isset($_SESSION['LAST_FOCUSED_FIELD'])) { ?>
					<script>
						if (jQuery("#<?php echo $_SESSION['LAST_FOCUSED_FIELD']?>").length) {
							window["AUTO_FOCUS_FIELD_ID"] = "<? echo $_SESSION['LAST_FOCUSED_FIELD']?>";
						}
					</script><?
			    }
			}?>
			<script>
				var <?= $this->idDetail ?>_FORM_ARRAY = jQuery('#<?= $this->idDetail ?>_slider input, textarea').toArray();
			</script><?
			// **************************************************
	
			foreach($this->getParameters() as $parameterKey => $parameterValue) {?>
				<input type="hidden" name="<?= $parameterKey ?>" id="<?= $parameterKey ?>" value="<?= $parameterValue ?>"><?
			}
	
			// **************************************************
			// 	GESTIONE BUTTONS
			// **************************************************
			$all_buttons = $this->getButtons();
			$abil_button = array();
			if($this->isMapping) {
				foreach($all_buttons as $button) {
					$isEnable = checkFieldEnableOnDetail($actionContext->getAction(), $this->getIdDetail(), $button, "D");
					if(is_array($isEnable) && $isEnable[0] === 'hide') {
						continue;
					}else {
						if(is_string($isEnable) && $isEnable == "disabled") $button->setDisabled(true);
						$abil_button[] = $button;
					}
				}
			}else {
				$abil_button = $all_buttons;
			}
			
			if(isset($_SESSION['BUTTON_MAPPA_DETAIL'])) {
				$myButton = new wi400InputButton('MAPPING_DETAIL');
				$myButton->setLabel("Mappa dettaglio");
				$myButton->setAction("ABILITAZIONI_CAMPI_DETAIL");
				$myButton->setForm('MAP_DETAIL');
				$myButton->addParameter("MAP_DETAIL", $this->idDetail);
				$myButton->addParameter("TITLE_DETAIL", $this->getTitle());
				$this->addButton($myButton);
				$abil_button[] = $myButton;
			}
			
			$this->setButtons($abil_button);
			
			$this->disposeButtons('LOWER');
		
			// **************************************************
			if ($this->getEditable()){
				// TODO: in futuro abilitare validazione detail a scelta dell'utente NAME=<?= $this->idDetail."_VALIDATION
				?> <input disabled type="HIDDEN" id="ACTION_FORM_VALIDATION" name="<?= "DETAIL_VALIDATION" ?>" value="true"> <?
			}
	
			// Salvo in sessione il dettaglio cosi' com'e' configurato
			$detailSessionObj = getDetail($this->idDetail);
			$detailSessionObj["FIELDS"] = $this->fieldSessionList;
			$detailSessionObj["TITLE"] = $this->getTitle();
			$detailSessionObj["STATUS"] = $this->status;
			$detailSessionObj["BUTTONS"] = $all_buttons;
			// Se devo ricaricarlo devo salvare anche la sua struttura
			if ($this->getReloadAction()!="") {
				saveDetail($this->idDetail."_STRUCT", $this);
			}
			saveDetail($this->idDetail, $detailSessionObj);?>
		</div>
		<? if ($reloadAjax==False) { ?>
		<script>resizeDescriptionDetail()</script>
		<? }
		if ($this->getReloadTimeout()!="" && $this->getReloadAction()!="") {
		//Per settare il focus dentro alla finestra
			?><script>
			setTimeout(function() {
			doReloadDetail("<?= $this->idDetail ?>");
		}, <?= $this->getReloadTimeout() ?>);
				</script> <? 
		}
	    if ($this->getHidden()) { ?>
	    	<script>
		    	jQuery("#<?= $this->idDetail ?>_opener").hide();
				jQuery("#<?= $this->idDetail ?>_slider").hide();
			</script> <? 
	    }
	    // Chiusura del Dettaglio se era chiuso e deve rimanere chiuso
	    if ($this->status=="CLOSE") {
			?>
			<script>
				closeDetail('<?= $this->idDetail ?>');
			</script>
			<?
		}
	}
}
?>
