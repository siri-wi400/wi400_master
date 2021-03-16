<?php

/**
 * @name wi400Column 
 * @desc Classe per la creazione di colonne in una lista
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.01 08/02/2010
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400Column {

	private $idList;
	
	private $key;
    private $description;
    private $show;
    private $aling;
    private $format;
    private $formatArray;
    private $decorator;
    private $decoratorArray;
    private $style;
    private $orientation;
    
    private $width;
     
    private $defaultValue;
        
    private $actionListId;
    
    private $detailAction = "";
    private $detailForm = "";
//	private $detailLabel = "";
	private $detailStyle = "";
    private $detailWidth = 0;
    private $detailHeight = 0;
    private $detailModal = "true";
    private $detailClose = "true";
    private $detailUrlEncode = "false";
    private $detailKeys = array();
    private $detailParameters = array();
    
    private $detailTarget = "WINDOW";
	private $confirmMessage = "";
	private $detailGateway = "";
    
    private $decode;
    private $group;
    private $decodeKey;
    
    private $input;
    private $required;
    private $fixed;
    
    private $sortable;
    private $exportable;
    private $draggable;
    // può essere modificata al volo il valore
    private $updatable;
    
    private $readonly;
    private $cleanable = true;
    
    private $commonCondition = "";
    
    // Header Action
    private $headerIco;
    private $headerAction;
    private $headerForm;
    private $headerCallBack;
    private $headerTarget = "ajax";
    
    private $toolTip;
    private $keys;
    private $writeUniqueId;
    private $disableAutoUpdate=False;
    private $autoUpdateBackGround=False;
    private $whereFormat="";
    private $headerTooltip="";

    /**
	 * @return the $headerTooltip
	 */
	public function getHeaderTooltip() {
		return $this->headerTooltip;
	}
	/**
	 * @param string $headerTooltip
	 */
	public function setHeaderTooltip($headerTooltip) {
		$this->headerTooltip = $headerTooltip;
	}

	/**
     * @desc Reperisce se l'auto update deve essere fatto in background
	 * @return the $autoUpdateBackGround
	 */
	public function getAutoUpdateBackGround() {
		return $this->autoUpdateBackGround;
	}

	/**
	 * @desc Setta se l'auto update deve essere fatto  in background
	 * @param boolean $autoUpdateBackGround
	 */
	public function setAutoUpdateBackGround($autoUpdateBackGround) {
		$this->autoUpdateBackGround = $autoUpdateBackGround;
	}

	/**
	 * @return the $whereFormat
	 */
	public function getWhereFormat() {
		return $this->whereFormat;
	}

	/**
	 * @param string $whereFormat
	 */
	public function setWhereFormat($whereFormat) {
		$this->whereFormat = $whereFormat;
	}

	/**
	 * @return the $disableAutoUpdate
	 */
	public function getDisableAutoUpdate() {
		return $this->disableAutoUpdate;
	}

	/**
	 * @param boolean $disableAutoUpdate
	 */
	public function setDisableAutoUpdate($disableAutoUpdate) {
		$this->disableAutoUpdate = $disableAutoUpdate;
	}

	/**
	 * Costruttore della classe
	 *
	 * @param string $key			: ID della colonna
	 * @param string $description	: Descrizione della colonna
	 * @param string $format		: Formato della colonna
	 * @param string $align			: Allineamento dei dati all'intero della colonna
	 * @param string $style			: Stile della colonna
	 * @param boolean $show			: se true mostra la colonna se false mette la colonna nell'elenco delle colonne da nascondere
	 * @param integer $width		: Larghezza della colonna
	 */
    public function __construct($key = "", $description = "", $format = "", $align = "left", $style="", $show = true, $width= ""){
		$this->key = $key;
		$this->description = $description;
		$this->show = $show;
		$this->align = $align;
		$this->style = $style;
		$this->format = $format;
		$this->decorator = "";
		$this->orientation = "horizontal";
		
		$this->decode = "";
		$this->decodeKey = "";
		$this->group = "";
		
		$this->actionListId = "";
		
		$this->formatArray = array();
		$this->decoratorArray = array();
		
		$this->draggable = false;
		$this->sortable = true;
		$this->exportable = true;
		$this->readonly = false;
		$this->width = $width;
		$this->toolTip = "";
		$this->toolTipAjax = array();
		$this->updatable = false;
		
		$this->required = false;
		$this->fixed = false;
		
		$this->headerAction = "";
		$this->headerForm = "";
		$this->headerIco = "";
		$this->keys= array();
		$this->writeUniquId = False;
    }
    /**
     * @desc Aggiunta chiavi sul codice HTML per futuri utilizzi con javascript per avere subito in linea i dati e le chiavi
     * @param array $keys
     */
	public function addKeys($keys) {
		$this->keys = $keys;
	}
	public function getKeys() {
		return $this->keys;
	}
	/**
	 * Setto se sulla cella deve essere inserito l'id univoco del campo
	 * @param boolena $unique
	 */
	public function setWriteUniqueId($unique) {
		$this->writeUniqueId = $unique;
	}
	public function getWriteUniqueId() {
		return $this->writeUniqueId;
	}
	/**
	 * @return the $actionListId
	 */
	public function getActionListId() {
		return $this->actionListId;
	}
	
	/**
	 * @param field_type $actionListId
	 */
	public function setActionListId($actionListId) {
		$this->actionListId = $actionListId;
	}

	public function setDecode($decode, $decodeKey = ""){
    	$this->decode       = $decode;
    	$this->decodeKey 	= $decodeKey;
	}
    
    public function getDecode(){
    	return $this->decode;
    }
    /**
     * @desc Aggancio un tooltip AJAX alla colonna. Attenzione che le chiavi vengono passate in BASE64. 
     *       Devono essere riconvertite quando le ricevo con base64_decode 
     *
     * @param string $action: Azione da richiamare
     * @param string $form: Eventuale Forma da richiamare, default ""
     * @param boolean $persistence: Tooltip Persistente, una volta richiamato i dati non cambiano, default True
     * @param string $extraParameters: Parametri extra da utilizzare
     * @param boolean $hasValue: Tooltip da richiamare solo se la cella ha dei valori
     */
	public function setToolTipAjax($action, $form = "", $persistence = false, $extraParameters="", $hasValue = True){
    	$this->toolTipAjax["action"] = $action;
    	$this->toolTipAjax["form"] = $form;
    	$this->toolTipAjax["persistence"] = (string)$persistence;
    	$this->toolTipAjax["extraParameters"] = $extraParameters;
    	$this->toolTipAjax["hasValue"] = $hasValue;
	}
    
    public function getToolTipAjax($parameter=null){
    	if (isset($this->toolTipAjax[$parameter])){
    		return $this->toolTipAjax[$parameter];
    	}
    	return $this->toolTipAjax;
    }
    
    /**
	 * @return the $fixed
	 */
	public function isFixed() {
		return $this->fixed;
	}

	/**
	 * @param field_type $fixed
	 */
	public function setFixed($fixed) {
		$this->fixed = $fixed;
	}

	public function isRequired(){
    	return $this->required;
    }
    
    /**
	 * @desc Indica che la colonna è richiesta e non può quindi essere eliminata dalla visualizzazione. default = true
	 *
	 * @param string $required: booleano che indica se la colonna è richiesta
	 */
    public function setRequired($required){
    	$this->required = $required;
	}
	
    public function getGroup(){
    	return $this->group;
    }
    
    public function setGroup($group){
    	$this->group = $group;
	}
    
    public function getDecodeKey(){
    	return $this->decodeKey;
    }
    
    
    public function setUpdatable($updatable){
    	$this->updatable = $updatable;
    }
    
    public function getUpdatable(){
    	return $this->updatable;
    }    
    
     /**
	 * Impostare il valore della colonna trascinabile
	 *
	 * @param boolean $readonly
	 */
    public function setDraggable($draggable){
    	$this->draggable = $draggable;
    }
    
    /**
     * Recupero dell'impostazione draggable
     *
     * @return boolean	Ritorna il valore di draggable: false/true
     */
    public function getDraggable(){
    	return $this->draggable;
    }    
    
    /**
     * Aggiunta del id di una colonna della lista in un array di chiavi, 
     * utilizzato per quando si vuole associare un'azione alla colonna
     *
     * @param string $columnKey	: Codice del campo da passare come elemento della chiave della colonna
     */
    public function addDetailKey($columnKey){
    	$this->detailKeys[] = $columnKey;
    }
    
	/**
     * Recupero della chiave associata alla colonna
     *
     * @return array
     */
	public function getDetailKeys(){
    	return $this->detailKeys;
    }

    public function addDetailParameter($fieldId){
    	$this->detailParameters[] = $fieldId;
    }
    
	public function getDetailParameters(){
    	return $this->detailParameters;
    }
    
    
    /**
	 * @return the $detailStyle
	 */
	public function getDetailStyle() {
		return $this->detailStyle;
	}

	/**
	 * @param field_type $detailStyle
	 */
	public function setDetailStyle($detailStyle) {
		$this->detailStyle = $detailStyle;
	}

	/**
	 * Impostare la colonna a readonly
	 *
	 * @param boolean $readonly	: valore di readonly (false/true)
	 */
    public function setReadonly($readonly, $cleanable=false){
    	$this->readonly = $readonly;
    	if($readonly===true)
    		$this->cleanable = $cleanable;
    }
    
    /**
     * Recupero dell'impostazione readonly
     *
     * @return boolean	Ritorna il valore di readonly: false/true
     */
    public function getReadonly(){
    	return $this->readonly;
    }

    /**
     * @desc setCleanable() : Setto se sul campo di input aggiungo l'icona di pulizia
     *
     * @param boolean $clean	: campo con scopino pulizia (True/False)
     */
    public function setCleanable($clean){
    	$this->cleanable = $clean;
    }
    
    /**
     * @desc getCleanable: Recupero se il campo può essere pulito
     *
     * @return boolean	     */
    public function getCleanable(){
    	return $this->cleanable;
    }
    
    /**
     * Associazione di un'azione alla colonna
     *
     * @param string $detailAction	: codice dell'azione associata alla colonna
     * @param string $detailForm	: codice del form dell'azione associata alla colonna
     */
    public function setDetailAction($detailAction, $detailForm = ""){
    	$this->detailAction = $detailAction;
    	$this->detailForm	= $detailForm;
    }
    
    /**
     * Recupero del nome dell'azione associata alla colonna
     *
     * @return string
     */
    public function getDetailAction(){
    	return $this->detailAction;
    }
    
    /**
     * Recupero del nome del form dell'azione associata alla colonna
     *
     * @return string
     */
    public function getDetailForm(){
    	return $this->detailForm;
    }
    
    public function setDetailTarget($target="WINDOW") {
    	$this->detailTarget = $target;
    }
    
    public function getDetailTarget() {
    	return $this->detailTarget;
    }
    
 	/**
     * Aggiunta di un messaggio da visualizzare una volta premuto il bottone 
     * per chiedere la conferma della decisione di eseguire l'azione associata al bottone
     *
     * @param stirng $cm	: messaggio di conferma
     */
    public function setConfirmMessage($cm){
    	$this->confirmMessage = $cm;
    }
    
    /**
     * Recupero del messaggio di conferma associato al bottone
     *
     * @return string
     */
    public function getConfirmMessage(){
    	return $this->confirmMessage;
    }
    
	/**
     * Impostazione del gateway da associare all'azione legata al bottone
     *
     * @param string $gateway	: nome del gateway
     */
    public function setDetailGateway($gateway){
    	$this->detailGateway = $gateway;
    }
    
    /**
     * Recupero del nome del gateway associato all'azione legata al bottone
     *
     * @return string
     */
    public function getDetailGateway(){
    	return $this->detailGateway;
    }
/*    
	public function setDetailLabel($label) {
    	$this->detailLabel = $label;
    }
    
    public function getDetailLabel() {
    	return $this->detailLabel;
    }
*/    
    /**
     * Impostazione delle dimensioni della colonna
     *
     * @param integer $width	: larghezza della colonna
     * @param integer $height	: altezza della colonna
     */
    public function setDetailSize($width, $height){
    	$this->detailWidth = $width;
    	$this->detailHeight = $height;
    }
    
    /**
     * Recupero della larghezza della colonna
     *
     * @return integer
     */
	public function getDetailWidth(){
    	return $this->detailWidth;
    }
    
    /**
     * Recupero dell'altezza della colonna
     *
     * @return integer
     */
    public function getDetailHeight(){
    	return $this->detailHeight;
    }
    
    public function setDetailModal($dm){
    	$this->detailModal = $dm;
    }
    
    public function getDetailModal(){
    	return $this->detailModal;
    }
    
        
    /**
	 * @return the $detailClose
	 */
	public function getDetailClose() {
		return $this->detailClose;
	}

	/**
	 * @param field_type $detailClose
	 */
	public function setDetailClose($detailClose) {
		$this->detailClose = $detailClose;
	}

	public function setDetailUrlEncode($ue){
    	$this->detailUrlEncode = $ue;
    }
    
    public function getDetailUrlEncode(){
    	return $this->detailUrlEncode;
    }
    
    /**
     * Impostazione della possibilità di ordinare la colonna
     *
     * @param boolean $sortable	: true se la colonna è ordinabile, false altrimenti
     */
    public function setSortable($sortable){
    	$this->sortable = $sortable; 
    }
    
    /**
     * Recupero dell'impostazione di ordinamento della colonna
     *
     * @return boolean	Ritorna true se la colonna è orinabile, false altrimenti
     */
	public function getSortable(){
    	return $this->sortable;
    }
    
    public function getExportable(){
    	return $this->exportable;
    }
   
    /**
     * Impostazione dell'esportabilità o meno della colonna
     *
     * @param boolean $exportable	: true se la colonna è esportabile, false altrimenti
     */
    public function setExportable($exportable){
    	$this->exportable = $exportable; 
    }
    
    /**
     * Recupero del valore di default associato a tutta la colonna
     *
     * @return unknown
     */
    public function getDefaultValue(){
    	return $this->defaultValue;
    }
    
    /**
     * Impostazione del valore di default da associare a tutta la colonna
     *
     * @param unknown_type $value
     */
    public function setDefaultValue($value){
    	$this->defaultValue = $value; 
    }
    
    public function getIdList(){
    	return $this->idList;
    }
    
    public function setIdList($idList){
    	$this->idList = $idList; 
    }
    
    public function getInput(){
    	return $this->input;
    }
    
    public function setInput(wi400Input $input){
    	$this->input = $input; 
    }
    
    public function getKey(){
    	return $this->key;
    }
    
    public function setKey($key){
    	$this->key = $key; 
    }
    
    public function getDescription(){
    	return $this->description;
    }
    
    public function setDescription($description){
    	$this->description = $description; 
    }
    
    /**
     * Recupero del tipo di allineamento del contenuto della colonna 
     *
     * @return string
     */
    public function getAlign(){
    	return $this->align;
    }

    /**
     * Impostazione dell'allineamento del contenuto della colonna
     * Attualmente funzionante solo per dettaglio
     *
     * @param string $align	: allineamento (left, right)
     */
    public function setAlign($align){
    	$this->align = $align; 
    }
    
    /**
     * Recupero dello stato di visualizzazione della colonna
     *
     * @return boolean Ritorna true se la colonna viene visualizzata, false se fa parte delle colonne nascoste
     */
    public function getShow(){
    	return $this->show;
    }
    
    /**
     * Impostazione della visibiltà della colonna
     *
     * @param boolean $show	: true per mostrare la colonna, false per piazzare la colonna tra quelle nascoste
     */
    public function setShow($show){
    	$this->show = $show; 
    }
    
    /**
     * Recupero dello stile della colonna
     *
     * @return string
     */
    public function getStyle(){
    	return $this->style;
    }
    
    /**
     * Impostazione dello stile della colonna
     *
     * @param string $style	: stile della colonna
     */
    public function setStyle($style){
    	$this->style = $style; 
    }
    
 	public function getOrientation(){
    	return $this->orientation;
    }
    
	public function setOrientation($orientation){
    	$this->orientation = $orientation; 
    }
    
    /**
     * Recupera il formato per un determinato contesto. Altrimenti quello di default
     *
     * @param string $context	: Contesto a scelta tra:
     * 								LIST,TOTALS,EXTRA,EXPORT_PDF,EXPORT_CSV,EXPORT_XLS,EXPORT_XML 
     * 
     * @return string
     */
    public function getFormat($context=""){
    	if ($context != "" && isset($this->formatArray[$context])){
    		return $this->formatArray[$context];
    	}
    	else{
    		if(is_array($this->format))
    			$format = implode("_", $this->format);
    		else
    			$format = $this->format;
    		
    		return $format;
    	}
    }
    
    /**
     * Setta il formato per un determinato contesto. Altrimenti quello di default
     *
     * @param string $format	: Nome formatter definito in formatting.php
     * @param string $context	: Contesto a scelta tra:
     * 								LIST,TOTALS,EXTRA,EXPORT_PDF,EXPORT_CSV,EXPORT_XLS,EXPORT_XML 
     */
    public function setFormat($format, $context = ""){
    	if ($context == ""){
    		$this->format = $format;
    	}
    	else{
    		$this->formatArray[$context] = $format;
    	}
    }
    
    /**
     * Setta il decoratore per un determinato contesto. Altrimenti quello di default
     *
     * @param string $decorator	: Nome decoratore definito in decorators.php
     * @param string $context	: Contesto a scelta tra:
     * 								LIST,TOTALS,EXTRA,EXPORT_PDF,EXPORT_CSV,EXPORT_XLS,EXPORT_XML 
     */
    public function setDecorator($decorator, $context = ""){
    	if ($context == ""){
    		$this->decorator = $decorator;
    	}
    	else{
    		$this->decoratorArray[$context] = $decorator;
    	}
	}
    
	/**
	 * @desc Recupera il decoratore per un determinato contesto. Altrimenti quello di default
	 *
	 * @param string $context	: Contesto a scelta tra:
	 * 								LIST,TOTALS,EXTRA,EXPORT_PDF,EXPORT_CSV,EXPORT_XLS,EXPORT_XML
	 *  
	 * @return string
	 */
    public function getDecorator($context=""){
    	if ($context != "" && isset($this->decoratorArray[$context])){
    		return $this->decoratorArray[$context];
    	}
    	else{
    		return $this->decorator;
    	}
    }
    
    /**
     * Permette di impostare un EVAL comune a più condizioni di una colonna della lista
     * in modo che questo venga eseguito una volta sola per colonna nella riga e poi il risultato venga sostituito al marker ##COMMON_COLUMN##
     * in più eval specifici che hanno bisogno di eseguire lo stesso controllo ogni volta (es: readonly, default value, ...)
     */
    public function setCommonCondition($condition) {
    	$this->commonCondition = $condition;
    }
    
    public function getCommonCondition(){
    	return $this->commonCondition;
    }
    
    public function getWidth(){
    	return $this->width;
    }
    
    /**
	 * @desc Imposta la dimensione in pixel della colonna. Non funziona con campi di input
	 *
	 * @param string $width	: larghezza in pixel (passare PX alla fine)
	 * 
	 */
    
    public function setWidth($width){
    	$this->width = $width; 
    	if (strpos(strtolower($this->width), "px")!==False) {
    		
    	} else {
    		$this->width = $this->width."px";
    	}
    }
    

    public function getToolTip(){
    	return $this->toolTip;
    }
    
   /**
	 * @desc Setta il nome di una seconda colonna da cui reperire il testo che compare on mouse over
	 *
	 * @param string $toolTip: Nome di una colonna
	 *  
	 * @return string
	*/
    public function setToolTip($toolTip){
    	$this->toolTip = $toolTip; 
    }
    
	/**
	 * @return the $headerIco
	 */
	public function getHeaderIco() {
		return $this->headerIco;
	}

	/**
	 * @return the $headerAction
	 */
	public function getHeaderAction() {
		return $this->headerAction;
	}

	/**
	 * @return the $headerCallBack
	 */
	public function getHeaderCallBack() {
		return $this->headerCallBack;
	}

	/**
	 * Funzione javascript chiamata dopo l'esecuzione dell'header action
	 * @param field_type $headerCallBack
	 */
	public function setHeaderCallBack($headerCallBack) {
		$this->headerCallBack = $headerCallBack;
	}

	/**
	 * @return the $headerForm
	 */
	public function getHeaderForm() {
		return $this->headerForm;
	}

	/**
	 * @param field_type $headerIco
	 */
	public function setHeaderIco($headerIco) {
		if (!is_array($headerIco)){
			$headerIco = array($headerIco);
		}
		$this->headerIco = $headerIco;
	}

	/**
	 * @param field_type $headerAction
	 */
	public function setHeaderAction($headerAction) {
		$this->headerAction = $headerAction;
	}

	/**
	 * @param field_type $headerForm
	 */
	public function setHeaderForm($headerForm) {
		$this->headerForm = $headerForm;
	}
	/**
	 * @desc setHeaderTarget: come aprire l'azione legata all'header della colonna
	 * @param string $headerTarget: ajax (default), windows, detail
	 */
	public function setHeaderTarget($headerTarget) {
		$this->headerTarget = $headerTarget;
	}
	/**
	 * @param field_type $headerTarget
	 */
	public function getHeaderTarget() {
		return $this->headerTarget;
	}
    
    
    
    
    
}

?>