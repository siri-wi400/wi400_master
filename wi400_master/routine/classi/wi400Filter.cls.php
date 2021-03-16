<?php
require_once $routine_path.'/classi/wi400InputText.cls.php';


class wi400Filter extends wi400InputText {

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
    private $fieldObj = "";
    private $multiFast;
    
    private $sqlKey;
    
    private $group;
    
    // Logical operator
    private $logOperator;
    private $logOperatorGroup;
    
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
		$this->logOperatorGroup = "";
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
    public function setFieldObj($fieldObj) {
    	$this->fieldObj = $fieldObj;
    }
    
    public function getFieldObj() {
    	return $this->fieldObj;
    }
        
    public function getLogicalOperator(){
    	return $this->logOperator;
    }
    
    public function setLogicalOperator($lo){
    	$this->logOperator = $lo;
    }
    
    public function setLogicalOperatorGroup($gr) {
    	$this->logOperatorGroup = $gr;
    }
    
    public function getLogicalOperatorGroup(){
    	return $this->logOperatorGroup;
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
    	if($this->fast && $this->getType() == "LOOKUP") {
    		$this->setType("STRING");
    	}
    }
    
    public function getMultiFast(){
    	return $this->multiFast;
    }
    
    public function setgetMultiFast($fast){
    	$this->multiFast = $fast;
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
    
    public function getHtml($clean=False){
    	global $temaDir, $viewContext, $messageContext;
    	// Per il filtro veloce multiplo
    	$isMultyFilter = ($this->getFast() && is_array($this->getKey()) && sizeof($this->getKey())>1);
    	
    	/*$fastSelected = "";
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
    	}*/
    	// Fine per il filtro veloce multiplo
    	
    	$filterStyle = "";
    	if ($this->getFast()) $filterStyle = 'class="wi400-grid-filter"';
    	
    	$html = "";
    	/*if ($isMultyFilter){
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
    			$html = $html.'var '.$this->getIdList().'_FILTER_CASE_SENSITIVE = new wi400Map();';
    		foreach ($this->getKey() as $key => $description){
    			$html = $html.$this->getIdList().'_FILTER_CASE_SENSITIVE.put("'.$key.'",'.$filterCases[$key].');';
    		}
    		$html = $html.'</script>';
    	}*/
    	
    	if(!$this->multiFast) {
    		$html .= '<table cellpadding="0" cellspacing="0" '.$filterStyle.'><tr class="detail-row">';
    	}
    	
    	/*if ($isMultyFilter){
    		$html = $html.'<td  class="text-label"><select id="MULTY_FILTER_KEY" onChange="setMultyFilter(this)" class="select-field">';
    		//showArray($this->getKey());
    		foreach ($this->getKey() as $key => $description){
    			$selectedFast = "";
    			if ($fastSelected == $key){
    				$selectedFast = "selected";
    			}
    			$html = $html.'<option value="'.$key.'" '.$selectedFast.'>'.$description.'</option>';
    		}
    		$html = $html.'</select></td>';
    	}else if ($this->getFast() && !$this->getMultiFast()){
			$html = $html.'<td  class="text-label">'.$this->getDescription().':</td>';
    	}*/
    	
    	if ($this->getFast() && !$this->getMultiFast()){
    		$html = $html.'<td  class="text-label">'.$this->getDescription().':</td>';
    	}
    	
    	//echo "Ciao: ".$this->getType()."<br/>";
		if ($this->getType() == "STRING") {
	    	
			$arraySelect = get_text_condition_array();
	    	
			/*if ($isMultyFilter){
				$html = $html.'<td><select class="select-field" id="FAST_MULTI_FILTER_OPTION" name="FAST_FILTER_'.$fastSelected.'_OPTION">';
			}else{
				
			}*/
			$html = $html.'<td><select class="select-field" name="FAST_FILTER_'.$this->getId().'_OPTION" onChange="checkSelection(this)">';
			
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
			
		//}else if ($this->getType() == "DATE" && $this->getFunction() == ""){
		}else if ($this->getType() == "DATE"){
						
			$arraySelect = array(
			
    	 			"=" => "=",
					">" => ">",
    				"<" => "<",
					">=" => ">=",
					"<=" => "<="
					);
					
			$html = $html.'<td><select class="select-field" name="FAST_FILTER_'.$this->getId().'_OPTION">';
			
			foreach($arraySelect as $key => $description){
				$selected = "";
				if ($key == $this->getOption()) $selected = "selected";
				$html = $html.'<option '.$selected.' value="'.$key.'">'.$description.'</option>';
			}
			
			$html = $html.'</select></td>';
			
			
		}
		else if($this->getType()=="USER_WHERE") {
			$text_area = new wi400InputTextArea("FAST_FILTER_".$this->getId());
			$text_area->setLabel($this->getName());
			$text_area->setValue($this->getValue());
			$text_area->setRows(5);
			$text_area->setSize(60);
//			$text_area->setMaxLength(1000);
			$html .= $text_area->getHtml();
		}
		
		
		if ($this->getType() == wi400Filter::$TYPE_CHECK_NUMERIC || $this->getType() == wi400Filter::$TYPE_CHECK_STRING ) {
			$checked = "";
//			echo "GET CHECKED VALUE: ".$this->getCheckedValue()."<br>";
//			echo "VALUE: ".$this->getValue()."<br>";
			if ($this->getCheckedValue() == $this->getValue()){
				$checked = "checked";
			}
			$html = $html.'<td><input '.$checked.' name="FAST_FILTER_'.$this->getId().'" type="checkbox" value="'.$this->getCheckedValue().'"></td>';		
		}else if ($this->getType() == "SELECT"){

			$html = $html.'<td><select id="FAST_FILTER_'.$this->getId().'" class="select-field" name="FAST_FILTER_'.$this->getId().'">';

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
			
			
		}else if($this->getType()!="USER_WHERE") {
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
		    
//		    $inputFilter = new wi400InputText("FAST_FILTER_".$this->getId());
		    
//		    if ($this->getType() == "LOOKUP" ) $inputFilter->setShowMultiple(true);

		    if ($isMultyFilter){
		    	/*$inputFilter = new wi400InputText("FAST_MULTI_FILTER");
		    	$inputFilter->setName("FAST_FILTER_".$fastSelected);
		    	$inputFilter->setValue($filterValues[$fastSelected]);
		    	if ($this->getFieldOBj()!="") {
		    		$lokup = $this->getFieldObj()->getLookUp();
		    		$lokup->setFields(array());
		    		$this->setLookUp($lokup);
		    		$inputFilter = $this->getFieldOBj();
		    		$inputFilter->setLookUp("");
		    		$inputFilter->setId("FAST_FILTER_".$this->getId());
		    	}*/
		    }
		    else{
		    	$inputFilter = new wi400InputText("FAST_FILTER_".$this->getId());
		    	if ($this->getFieldOBj()!="") {
		    		$lokup = $this->getFieldObj()->getLookUp();
		    		$lokup->setFields(array());
		    		$this->setLookUp($lokup);
		    		$inputFilter = $this->getFieldOBj();
		    		$inputFilter->setLookUp("");
		    		$inputFilter->setId("FAST_FILTER_".$this->getId());
		    	}
		    	$value = $this->getValue();
		    	
		    	if($this->getShowMultiple()!==false) {
			    	if(!is_array($value) && !empty($value) && $this->getType()=="LOOKUP")
			    		$value = array($value);
		    	}
		    	
		    	$inputFilter->setValue($value);
		    	
		    	if($this->getFast()!==true) {
//		    		if(in_array($this->getType(), array("LOOKUP", "STRING"))) {	
		    		if(in_array($this->getType(), array("LOOKUP"))) {	
		    			if($this->getShowMultiple()!==false) {    		
		    				$inputFilter->setShowMultiple(true);
		    				
//		    				$onChange .= "multiFieldAddRemove('ADD','".$inputFilter->getId()."', null, true)";
		    			}
		    		}
		    	}
		    }

		 	if ($this->getType() == "DATE"){
		    	$inputFilter->addValidation("date");
		    }
		    
		    $inputFilter->setOnChange($onChange);
		    $inputFilter->setOnKeyDown("enterFastFilter(event, '".$this->getIdList()."')");
		    $inputFilter->setStyleClass("filter-field");
		    $inputFilter->setIdDetail("SEARCH_DETAIL".$this->getIdList());
		    if ($this->getLookUp()){
		    	$filterLookUp = $this->getLookUp();
		    	$filterLookUp->setFields(array());
		    	
//		    	$filterLookUp->addParameter("ONCHANGE","multiFieldAddRemove('ADD','".$inputFilter->getId()."', null, true)");
		    	
		    	$inputFilter->setLookUp($this->getLookUp());
		    }
		    
		    if ($this->getGroup() != ""){
		    	$inputFilter->setOnClick("checkGroup(this)");
		    }
			//$html = $html.'<td><input onChange="'.$onChange.'" class="filter-field" name="FAST_FILTER_'.$this->getId().'" type="text" value="'.$this->getValue().'"></td>';
		    
		    $html = $html.'<td><table cellpadding="0" cellspacing="0" border="0"><tr>'.$inputFilter->getHtml().'<td class="detail-message-cell" id="'.$inputFilter->getId().'_DESCRIPTION"></td></tr></table>';
			
					// ********************************************************************
					// GESTIONE MULTI VALUE
					// ********************************************************************

					if ($inputFilter->getShowMultiple()){
					  $html = $html.'<ul id="'.$inputFilter->getId().'_PARENT" class="deactiveOrder">';
				
					$inputValueArray = $inputFilter->getValue();
					$fieldArrayCounter = 0;
					if (is_array($inputValueArray)){
						
						// Nuova decodifica
						$decodeParameters = $inputFilter->getDecode();
						$decodeType = "table";
						if (isset($decodeParameters["TYPE"])){
							$decodeType = $decodeParameters["TYPE"];
						}
						//require_once $routine_path.'/decoding/siad/'.$decodeType.".php";
						//require_once $base_path.'/package/'.$settings['package'].'/decodingclass/'.$decodeType.".php";
						require_once p13nPackage($decodeType);

						$decodeClass = new $decodeType();
						$decodeClass->setDecodeParameters($decodeParameters);
						$decodeClass->setFieldLabel($inputFilter->getLabel());
						
						$errorMessages = $messageContext->getMessages();
						
						
						foreach ($inputValueArray as $inputValue){
							$fieldArrayCounter++;

							$multyFieldId = $inputFilter->getName().'_'.$fieldArrayCounter;

							$html .= '<li id="'.$inputFilter->getName().$fieldArrayCounter.'" class="deactiveOrder sizeDeactiveOrder" >';
							
							if ($inputFilter->getSortMultiple()){
								$html .= '<img class="wi400-updown" src="themes/common/images/triangle_up_down.png"></img>';
							}
							else {
//								$html .= '<div class="wi400-updown-none"></div>';
							}
							/*$html = $html.'<table border="0" cellpadding="0" cellspacing="0"><tr>';
							$html = $html.'<td><input id="'.$multyFieldId.'" name="'.$inputFilter->getName().'[]" type="TEXT" value="'.$inputValue.'" size="'.$inputFilter->getSize().'" readonly class="inputtextDisabled"></td>';
							$html = $html.'<td><img onClick="multiFieldAddRemove(\'REMOVE\',\''.$inputFilter->getName().'\', '.$fieldArrayCounter.')" hspace="5" class="wi400-pointer" src="'.$temaDir.'images/remove.png" title="'._t('REMOVE').'"></td>';*/
							$html .= '<input id="'.$multyFieldId.'" name="'.$inputFilter->getName().'[]" type="TEXT" value="'.$inputValue.'" size="'.$inputFilter->getSize().'" readonly class="inputtextDisabled">';
							$html .= '<img onClick="multiFieldAddRemove(\'REMOVE\',\''.$inputFilter->getName().'\', '.$fieldArrayCounter.')" hspace="5" class="wi400-pointer" src="'.$temaDir.'images/remove.png" title="'._t('REMOVE').'"  style="position: relative;">';
							if (array_key_exists($multyFieldId, $errorMessages)){
								//$html = $html.'<td class="detail-message-cell">';
								$html .= "<span id='errorsDiv_".$inputFilter->getName().'_'.$fieldArrayCounter."' class='innerError' >".$errorMessages[$multyFieldId][1]."</span>";
								//$html = $html.'</td>';
								//$html = $html."<span id='errorsDiv_".$inputFilter->getName().'_'.$fieldArrayCounter."' class='innerError' >".$errorMessages[$multyFieldId][1]."</span>";
							}

							if ($inputFilter->getDecode() != ""){
								$decodeDescription = $viewContext->__get($multyFieldId."_DESCRIPTION");

								if ($decodeDescription == ""){
									
									$decodeClass->setFieldId($multyFieldId);
									$decodeClass->setFieldValue($inputValue);
									$decodeDescription = $decodeClass->decode();
									
								}

								if ($decodeDescription != ""){
									//$html = $html.'<td class="detail-message-cell" id="'.$multyFieldId.'_DESCRIPTION">&nbsp;'.$decodeDescription.'</td>';
									//echo "alert('Sono qua dentro');";
									//$html = $html.'<span class="detail-message-cell" id="'.$multyFieldId.'_DESCRIPTION">&nbsp;'.$decodeDescription;
									$html .= "<span class=\"detail-message-cell\" id=\"".$multyFieldId."_DESCRIPTION\">&nbsp;".$decodeDescription."</span>";
								}

							}
							//$html = $html.'</tr></table>';
							$html .= '</li>';

							
						}
					}

						$html = $html.'</ul>';
						$html = $html.'<script>window["'.$inputFilter->getId().'_COUNTER"]='.$fieldArrayCounter.'</script>';
						$html = $html.'<input type="hidden" id="'.$inputFilter->getId().'_COUNTER" name="'.$inputFilter->getId().'_COUNTER" value="'.$fieldArrayCounter.'">';					
					}
					
					$html = $html.'</td>';
		}
		
		
		if ($this->getFast()){
			$html = $html.'<td><input class="filter-button" type="button" value="'._t('SEARCH').'" onClick="doPagination(\''.$this->getIdList().'\', _PAGE_FIRST)"></td>';
		}
		if(!$this->getMultiFast()) {
			$html = $html.'</tr></table>';
		}
		return $html;
    }
    
    public function dispose($clean=False){
		echo $this->getHtml();
    }
    
}
