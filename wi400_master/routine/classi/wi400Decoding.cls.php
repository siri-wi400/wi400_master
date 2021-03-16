<?php
	class wi400Decoding {
		
		public static $decodeMemory;
		
		private $fieldId;
		private $fieldLabel;
		private $fieldValue;
		
		private $fieldMessage;
		private $decodeParameters;
		private $decodeObject;
		
		private $decodeFields;
		private $maxResult = 10;
		private $minchar = 2;
		private $allowedNew = False;
		
		public function __construct(){
			$decodeObject = array();
			$decodeMemory = array();
			$decodeFields = array();
			$this->decodeParameters = array();
		}
		public function setAllowedNew($allowed_new) {
			$this->allowedNew = $allowed_new;
		}
		public function getAllowedNew() {
			return $this->allowedNew;
		}
		/**
		 * getMinChar() Recupero il numero minimo di caratteri per la decodifica
		 * @return minChar
		 */
		public function getMinChar(){
			return $this->minChar;
		}
		/**
		 * setMinChar() Setto il numero massimo di caratteri per iniziare la decodifica
		 * @param $minChar
		 */
		public function setMinChar($minChar){
			return $this->minChar = $minChar;
		}
		/**
		 * getMaxResult() Recupero il numero massimo di righe ritornate dalla richiesta
		 * @return maxResult
		 */
		public function getMaxResult(){
			return $this->maxResult;
		}
		/**
		 * setMaxResult() Setto il numero massimo di risultati ritornati dalla query di complete
		 * @param $maxResult
		 */
		public function setMaxResult($maxResult){
			return $this->maxResult = $maxResult;
		}
		public function getFieldId(){
			return $this->fieldId;
		}

		public function setFieldId($fieldId){
			$this->fieldId = $fieldId;
		}
		
		public function getFieldMessage(){
			return $this->fieldMessage;
		}

		public function setFieldMessage($fieldMessage){
			$this->fieldMessage = $fieldMessage;
		}
		
		public function getDecodeObject(){
			return $this->decodeObject;
		}

		public function setDecodeObject($decodeObject){
			$this->decodeObject = $decodeObject;
		}
		
		public function getFieldLabel(){
			return $this->fieldLabel;
		}

		public function setFieldLabel($fieldLabel){
			$this->fieldLabel = $fieldLabel;
		}
		
		public function getFieldValue(){
			return $this->fieldValue;
		}

		public function setFieldValue($fieldValue){
			$this->fieldValue = $fieldValue;
		}

		public function getDecodeParameter($key){
			if (isset($this->decodeParameters[$key])){
				return $this->decodeParameters[$key];
			}else if (isset($_REQUEST[$key])){
				return $_REQUEST[$key];
			}else if(isset($this->decodeParameters["ID_DETAIL"])){
				return wi400Detail::getDetailValue($this->decodeParameters["ID_DETAIL"], $key);
			}else{
				return null;
			}
		}
		
		public function addDecodeParameter($key, $value){
			$this->decodeParameters[$key] = $value;
		}
		public function setDecodeObjectField($field, $value) {
			$this->decodeObject[$field]=$value;
		}
		public function getDecodeParameters(){
			return $this->decodeParameters;
		}

		public function setDecodeParameters($decodeParameters){
			$this->decodeParameters = $decodeParameters;
			$allowed_new = False;
			if (isset($decodeParameters['ALLOW_NEW'])) {
				$allowed_new = $decodeParameters['ALLOW_NEW'];
			}
			$this->setAllowedNew($allowed_new);
		}
		
		public function decodeFields(){
			return $this->decodeFields;
		}
		
		
	}
?>