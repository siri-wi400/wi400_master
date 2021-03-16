<?php
require_once $routine_path.'/classi/wi400InputText.cls.php';


class wi400Filter2 extends wi400InputText {

	private $idList;
	private $fast;
	private $id;
	private $key;
    private $description;
    private $type;
    private $option;
    private $value;
    private $checkedValue;
    private $caseSensitive;
    private $function;
    
    private $sqlKey;
    
    private $group;
    
    // Logical operator
    private $logOperator;
    
    public static $CASE_SENSITIVE_BOTH = 4;
    public static $CASE_SENSITIVE_INPUT = 3;
    public static $CASE_SENSITIVE_DB = 2; // Default
    public static $CASE_SENSITIVE_NONE = 1;
    
    public static $LOGICAL_OPERATOR_OR = 1;
    public static $LOGICAL_OPERATOR_AND = 0; // Default
    
    public static $TYPE_CHECK_STRING  = "CHECK_STRING";
    public static $TYPE_CHECK_NUMERIC = "CHECK_NUMERIC"; // Default
    
    public function __construct($key= "", $description="", $type = "STRING", $checkedValue = ""){
		$this->key = $key;
		$this->id = $key;
		$this->description = $description;
		$this->type = $type;
		$this->option = "";
		$this->group = "";
		$this->sqlKey = "";
		$this->function = "";
		$this->logOperator = wi400Filter::$LOGICAL_OPERATOR_AND;
		$this->caseSensitive = wi400Filter::$CASE_SENSITIVE_DB;
		if ($type == wi400Filter::$TYPE_CHECK_STRING || $type == wi400Filter::$TYPE_CHECK_NUMERIC){
			$this->checkedValue = $checkedValue;
		}
		$this->fast = false;
    }

    public function getId(){
    	return $this->id;
    }
    
    public function setId($id){
    	$this->id = $id;
    }
    
    public function getLogicalOperator(){
    	return $this->logOperator;
    }
    
    public function setLogicalOperator($lo){
    	$this->logOperator = $lo;
    }
    
    public function getFunction(){
    	return $this->function;
    }
    
    public function setFunction($function){
    	$this->function = $function;
    }
    
    public function getSqlKey(){
    	return $this->sqlKey;
    }
    
    public function setSqlKey($sqlKey){
    	$this->sqlKey = $sqlKey;
    }
    
    public function setGroup($group){
    	$this->group = $group;
    }

	public function getGroup(){
    	return $this->group;
    }
    
    public function getCaseSensitive(){
    	return $this->caseSensitive;
    }
    /**
     *   Logica di applicazione dell'upper case sull'input e sul db
     *   @param CASE_SENSITIVE_INPUT | UPPER INPUT
     *   @param CASE_SENSITIVE_DB    | UPPER DB
     *   @param CASE_SENSITIVE_BOTH  | ----
     *   @param CASE_SENSITIVE_NONE  | UPPER INPUT & DB
     */
    public function setCaseSensitive($caseSensitive){
    	$this->caseSensitive = $caseSensitive;
    }   
    
    public function getOption(){
    	return $this->option;
    }
    
    public function getCheckedValue(){
    	return $this->checkedValue;
    }
    
	public function setCheckedValue($checkedValue){
    	$this->checkedValue = $checkedValue;
    }
    
    public function setOption($option){
    	$this->option = $option; 
    }
    
    public function getFast(){
    	return $this->fast;
    }
    
    public function setFast($fast){
    	$this->fast = $fast; 
    }
    
    public function setKey($key){
    	$this->key = $key;
    }
    
    public function getKey(){
    	return $this->key;
    }

    public function getDescription(){
    	return $this->description;
    }
    
    public function setDescription($description){
    	$this->description = $description; 
    }
    
    public function getType(){
    	return $this->type;
    }
    
    public function setType($type){
    	$this->type = $type; 
    }
    
    public function getValue(){
    	return $this->value;
    }
    
    public function setValue($value=""){
    	$this->value = $value; 
    }

    public function setIdList($idList){
    	$this->idList = $idList; 
    }
    
	public function getIdList(){
    	return $this->idList; 
    }
    
	public function getHtml(){
    	
    	// Per il filtro veloce multiplo
    	$isMultyFilter = ($this->getFast() && sizeof($this->getKey())>1);
    	$fastSelected = "";
    	$filterValues = $this->getValue();
    	$filterOption = $this->getOption();
    	$filterCases = $this->getCaseSensitive();
    	if ($isMultyFilter){
    		$firstTime = true;
    		foreach ($this->getKey() as $key => $description){
    			if ($filterValues[$key] != "" || $firstTime){
    				$fastSelected = $key;
    				$this->setOption($filterOption[$key]);
    				$firstTime = false;
    			}
    		}
    	}
    	// Fine per il filtro veloce multiplo
    	
    	$filterStyle = "";
    	if ($this->getFast()) $filterStyle = 'class="wi400-grid-filter"';
    	
    	$html = "";
    	if ($isMultyFilter){
    		// Gestione case sensitive multiplo
    		$html = $html.'<script>
    			function checkFastFilterCase(fastFilter){
    				var key = document.getElementById("MULTY_FILTER_KEY").value;
    				var filterCase = '.$this->getIdList().'_FILTER_CASE_SENSITIVE.get(key);
    				if (filterCase=='.wi400Filter::$CASE_SENSITIVE_NONE.' 
    						|| filterCase=='.wi400Filter::$CASE_SENSITIVE_DB.'){
    					fastFilter.value=fastFilter.value.toUpperCase();
    				}
    			};';
    			$html = $html.'var '.$this->getIdList().'_FILTER_CASE_SENSITIVE = new Map();';
    		foreach ($this->getKey() as $key => $description){
    			$html = $html.$this->getIdList().'_FILTER_CASE_SENSITIVE.put("'.$key.'",'.$filterCases[$key].');';
    		}
    		$html = $html.'</script>';
    	}
    	
    	$html .= '<table cellpadding="0" cellspacing="0" '.$filterStyle.'><tr class="detail-row">';
    	
    	if ($isMultyFilter){
    		$html = $html.'<td  class="text-label"><select id="MULTY_FILTER_KEY" onChange="setMultyFilter(this)" class="select-field">';
    		foreach ($this->getKey() as $key => $description){
    			$selectedFast = "";
    			if ($fastSelected == $key){
    				$selectedFast = "selected";
    			}
    			$html = $html.'<option value="'.$key.'" '.$selectedFast.'>'.$description.'</option>';
    		}
    		$html = $html.'</select></td>';
    	}else if ($this->getFast()){
			$html = $html.'<td  class="text-label">'.$this->getDescription().':</td>';
    	}
    	
    	
		if ($this->getType() == "STRING") {
	    	
			$arraySelect = array(
				"INCLUDE"=>_t("CONTIENE"),
				"START"=>_t("INIZIA_PER"),
 				"EQUAL"=>_t("UGUALE_A"),
				"NOT_INCLUDE" => _t("NON_CONTIENE"),
				"NOT_START" => _t("NON_INIZIA_PER"),
				"NOT_EQUAL" => _t("DIVERSO_DA"),
				"EMPTY" => _t("VUOTO"),
				"NOT_EMPTY" => _t("NON_VUOTO")
			);
	    	
			if ($isMultyFilter){
				$html = $html.'<td><select class="select-field" id="FAST_MULTI_FILTER_OPTION" name="FAST_FILTER_'.$fastSelected.'_OPTION">';
			}else{
				$html = $html.'<td><select class="select-field" name="FAST_FILTER_'.$this->getId().'_OPTION">';
			}
			
			foreach($arraySelect as $key => $description){
				$selected = "";
				if ($key == $this->getOption()) $selected = "selected";
				$html = $html.'<option '.$selected.' value="'.$key.'">'.$description.'</option>';
			}
			
			$html = $html.'</select></td>';
			
		}else if ($this->getType() == "NUMERIC") {
			
			$arraySelect = array(
					 ">" => ">",
    	 			 "=" => "=",
    				 "<" => "<"
					);
					
			$html = $html.'<td><select class="select-field" name="FAST_FILTER_'.$this->getId().'_OPTION">';
			
			foreach($arraySelect as $key => $description){
				$selected = "";
				if ($key == $this->getOption()) $selected = "selected";
				$html = $html.'<option '.$selected.' value="'.$key.'">'.$description.'</option>';
			}
			
			$html = $html.'</select></td>';
		}else if ($this->getType() == "LOOKUP"){
			
		}else if ($this->getType() == "DATE" && $this->getFunction() == ""){
				
			$arraySelect = array(
			
    	 			 "=" => "=",
					 ">" => ">",
    				 "<" => "<"
					);
					
			$html = $html.'<td><select class="select-field" name="FAST_FILTER_'.$this->getId().'_OPTION">';
			
			foreach($arraySelect as $key => $description){
				$selected = "";
				if ($key == $this->getOption()) $selected = "selected";
				$html = $html.'<option '.$selected.' value="'.$key.'">'.$description.'</option>';
			}
			
			$html = $html.'</select></td>';
			
			
		}
		
		
		if ($this->getType() == wi400Filter::$TYPE_CHECK_NUMERIC || $this->getType() == wi400Filter::$TYPE_CHECK_STRING ) {
			$checked = "";
			if ($this->getCheckedValue() == $this->getValue()){
				$checked = "checked";
			}
			$html = $html.'<td><input '.$checked.' name="FAST_FILTER_'.$this->getId().'" type="checkbox" value="'.$this->getCheckedValue().'"></td>';		
		}else if ($this->getType() == "SELECT"){

			$html = $html.'<td><select class="select-field" name="FAST_FILTER_'.$this->getId().'">';

			$selectedFilter = "";
			if ("" == $this->getValue()) $selectedFilter = "selected";
			
			$html = $html.'<option value="" '.$selectedFilter.'></option>';
			$counterFilter = 0;
			foreach ($this->getSource() as $key => $desc){
				$selectedFilter = "";
				if ($counterFilter."" == $this->getValue()."") $selectedFilter = "selected";
				$html = $html.'<option value="'.$counterFilter.'" '.$selectedFilter.'>'.$desc.'</option>';
				$counterFilter++;
			}
			
			$html = $html.'</select></td>';
			
			
		}else{
		    $formatNumber = false;
		    $onChange = "";
		    if ($this->getDecimals()>0){
		    	$formatNumber = true;
		    	$onChange = "this.value=currencyFormatter(this.value," .$this->getDecimals().")";
		    }else{
		    	
		    	if ($isMultyFilter){
		    			$onChange = "checkFastFilterCase(this)";
		    	}else{
			    	if ($this->getCaseSensitive()==wi400Filter::$CASE_SENSITIVE_DB 
			    			|| $this->getCaseSensitive()==wi400Filter::$CASE_SENSITIVE_NONE){
			    		$onChange = "this.value=this.value.toUpperCase();";
			    	}
		    	}
		    	
		    	
		    	
		    }
		    $inputFilter = new wi400InputText("FAST_FILTER_".$this->getId());
		    
		    if ($isMultyFilter){
		    	$inputFilter = new wi400InputText("FAST_MULTI_FILTER");
		    	$inputFilter->setName("FAST_FILTER_".$fastSelected);
		    	$inputFilter->setValue($filterValues[$fastSelected]);
		    }else{
		    	$inputFilter->setValue($this->getValue());
		    }

		 	if ($this->getType() == "DATE"){
		    	$inputFilter->addValidation("date");
		    }
		    
		    $inputFilter->setOnChange($onChange);
		    $inputFilter->setStyleClass("filter-field");
		    $inputFilter->setIdDetail("SEARCH_DETAIL");
		    if ($this->getLookUp()){
		    	$filterLookUp = $this->getLookUp();
		    	$filterLookUp->setFields(array());
		    	$inputFilter->setLookUp($this->getLookUp());
		    }
		    
		    if ($this->getGroup() != ""){
		    	$inputFilter->setOnClick("checkGroup(this)");
		    }
			//$html = $html.'<td><input onChange="'.$onChange.'" class="filter-field" name="FAST_FILTER_'.$this->getId().'" type="text" value="'.$this->getValue().'"></td>';
			$html = $html.'<td><table cellpadding="0" cellspacing="0" border="0"><tr>'.$inputFilter->getHtml().'</tr></table></td>';
				
			
		}
		
		if ($this->getFast()){
			$html = $html.'<td><input class="filter-button" type="button" value="'._t('SEARCH').'" onClick="doPagination(\''.$this->getIdList().'\', _PAGE_FIRST)"></td>';
		}
		$html = $html.'</tr></table>';
		return $html;
    }
    
    public function dispose(){
		echo $this->getHtml();
    }
    
}
