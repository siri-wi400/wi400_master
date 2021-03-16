<?php
	class wi400DragList {
	
		
		private $id;
		private $title;
		private $color;
		private $rows;
		private $rowStyle;
		private $containerStyle = "";
		private $persistent = false;
		
		public function __construct($id, $title = "", $color = "#dddddd"){
			
			$this->id = $id;
			$this->title = $title;
			$this->rows = array();
			$this->color = $color;
			
		}
		/**
		 * setRowStyle(): Imposta uno stile particolare per la riga
		 */
		public function setRowStyle($rowStyle) {
			$this->rowStyle = $rowStyle;
		}
		/**
		 * getRowStyle(): ritorna lo stile della riga
		 */
		public function setContainerStyle($style) {
			$this->containerStyle = "style='$style'";
		}
		
		public function getContainerStyle() {
			return $this->containerStyle;
		}
		
		public function getRowStyle() {
			return $this->rowStyle;
		}
		public function setTitle($title) {
			$this->title = $title;
		}
		
		public function getTitle() {
			return $this->title;
		}
		
		public function setId($id) {
			$this->id = $id;
		}
		
		public function getId() {
			return $this->id;
		}
		
		public function setColor($color) {
			$this->color = $color;
		}
		
		public function getColor() {
			return $this->color;
		}

		public function getRows() {
			return $this->rows;
		}
		
		/**
		 * Se la lista è persistente viene ingnorata quella in SESSIONE
		 * 
		 * return boolean
		 */
		public function getPersistent() {
			return $this->persistent;
		}
		
		public function setRows($rows, $persistent = false){
			$this->rows = $rows;
			
			$this->persistent = $persistent;
		}
		
		public function addRow($key, $value){
			//$value = str_replace('"','\"',$value);
			$this->rows[$key] = $value;
		}
		
		public function removeRow($key){
			if(isset($this->rows[$key])) {
				unset($this->rows[$key]);
				return true;
			}
			return false;
		}
		
	}
?>