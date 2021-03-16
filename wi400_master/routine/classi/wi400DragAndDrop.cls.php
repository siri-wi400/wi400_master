<?php
	class wi400DragAndDrop {
	
		
		private $id;
		private $lists;
		private $width = 0;
		private $height;
		private $checkUpdate;
		private $saveSession;
		private $itemDisabled = array();
		
		public function __construct($id, $saveSession = false) {
			$this->id = $id;
			$this->lists = array();
//			$this->width = 300;
			$this->height = 350;
			$checkUpdate = false;
			$this->saveSession = $saveSession;
		}
		
		public function getType() {
			return "DRAG_AND_DROP";
		}
		
	    public function setCheckUpdate($checkUpdate){
    		$this->checkUpdate = $checkUpdate;
		}
		
		public function getCheckUpdate(){
		    return $this->checkUpdate;
		}
		
		/**
		 * Disabilita gli item presenti nell'array
		 * es array("18", "111199")
		 * 
		 * @param array $array_item
		 */
		public function setItemDisabled($array_item){
			$this->itemDisabled = $array_item;
		}
		
		/**
		 * Ritorna l'array degli item che devono essere disabilitati
		 * 
		 * @return array
		 */
		public function getItemDisabled(){
			return $this->itemDisabled;
		}

		public function dispose(){
			    disable_text_selection();
?>
<style>

.sortableListContainer {
	float: left;
	margin: 2px;
	padding: 0em;
	border:1px solid #D0D0D0;
<?	
	if($this->width!==0) { ?>
		width: <?= $this->width ?>;
<? }else if(sizeof($this->lists) > 3){ ?>
	width: <?= round(100/sizeof($this->lists)) - 1?>%;
<? }else{?>
	width: 25%;
<? } ?>	
	;
}

.sortableContainer {

	margin: 0;
	padding: 0;
	height: 400px;
	overflow:auto;
}

ul {
	list-style: none none;
	margin: 0em;
	padding: 0em;
	height: 100%;
}

li {
	border: 0px solid #000000;
	margin: 0.1em;
	padding: 0.1em;
}


	
li.sortable {
	position: relative;
	cursor: move;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	color:#636363;
	border: 1px solid #D0D0D0;
	margin: 1px;
	padding: 3px;
	vertical-align: middle;
}
li.disabled {
	opacity: 0.4;
	cursor: no-drop;	
}

		
<?
	foreach ($this->lists as $idList => $list){
		if ($list->getColor() != ""){
?>
		#<?=$idList ?> li.sortable {
			background-color: <?= $list->getColor() ?>;
		}
<?
		}
	}
?>
			</style>
		
<?
			$html = '';
			$listsortable ="";
			$listvalue = array();
			
			$that = $this;
			//showArray($_SESSION[$this->id]);
			if($that->saveSession) {
				if(isset($_SESSION[$this->id])) {
					$that = $_SESSION[$this->id];
				}
				
				$thisList = $this->lists;
				
				$sessLists = array();
				//$this->save($that->id, $that);
			}
			
			foreach ($that->lists as $idList => $list){
				if($that->saveSession) {
					if($thisList[$idList]->getPersistent()) {
						$list = $thisList[$idList];
						$sessLists[$idList] = $thisList[$idList];
					}else {
						$sessLists[$idList] = $list;
					}
				}
				$listsortable .= "#".$idList.", ";
				$html .= '<div class="sortableListContainer" '.$list->getContainerStyle().'>';
				$html .= '<div class="detail-header detail-header-cell" style="line-height:22px">'.$list->getTitle().'</div>';
				$html .= '<div class="sortableContainer"><ul id="'.$idList.'" class="sortableContainer">';
				//$html .= '<script>jQuery("#'.$idlist.'").sortable();</script>';
				// Scrittura elementi della lista:
				foreach ($list->getRows() as $key => $value){
					//$listvalue[$idList][]=$value;
					$listvalue[$idList][]=$key;
					$rowsArray[] = '"'.$key.'" : "'.$value.'"';
					$style = "";
					if ($list->getRowStyle()!="") {
						$style = ' style="'.$list->getRowStyle().'"';
					}
					$disabled = "";
					if(in_array($key, $this->itemDisabled)) $disabled = "disabled";
					$html .='<li id="'.$key.'" class="sortable '.$disabled.'" '.$style.'>'.$value.'</li>';
					//$html .= '<script>jQuery("#'.$key.'").draggable()</script>';
				}
				$html .= '</ul></div>';
				$html .= '</div>';
			}
			echo $html;
			
			foreach ($that->lists as $idList => $list){
				$valori = "";
				if (isset($listvalue[$idList])) {
					$valori = implode(",",$listvalue[$idList]);
				}	
				echo '<input type="hidden" size="100" id="'.$that->id.'_'.$idList.'" name="'.$that->id.'_'.$idList.'" value="'.$valori.'">';
			}
			// Sortable finale
			$listsortable = substr($listsortable, 0, strlen($listsortable)-2);
			?>
			<script>
			jQuery(function (){
			jQuery( "<?= $listsortable?>" ).sortable({
			connectWith: ".sortableContainer",
			cancel: ".disabled",
		    update: function(event, ui)
		    {
				// La funzione viene chiamata sui contenitori interessati allo spostamento ... sender e target
				// Aggiornamento dei dati del contenitore di arrivo
				// Aggiornamento dei dati del contenitore di partenza
		        var gruppo = this.id;
		        var elemento = ui.item[0].id; // Which item
		        var virgola = "";
		        var stringa = "";
		        jQuery('ul#'+gruppo +' li').each(function( index ) {
					stringa = stringa + virgola + jQuery( this ).attr('id');
					//stringa = stringa + virgola + elemento;
		        	virgola = ",";
		        });
		        jQuery("#<?= $that->id?>_"+gruppo).val(stringa);
	        	//console.log(stringa);
		    }
			}).disableSelection();
			});
			</script>
			<?php
			if($that->saveSession) {
				echo '<input type="hidden" name="DRAG_ID[]" value="'.$that->id.'">';
				
				$that->setLists($sessLists);
				$this->save($that->id, $that, true);
			}
			
			echo "<div style='clear:both'></div>";
		}
		
		public function getId(){
	    	return $this->id;
	    }
	    
	    public function setId($id){
	    	$this->id = $id;
	    }
	    
	    public function getWidth(){
	    	return $this->width;
	    }
	    
	    public function setWidth($width){
	    	$this->width = $width;
	    }
		
		public function getHeight(){
	    	return $this->height;
	    }
	    
	    public function setHeight($height){
	    	$this->height = $height;
	    }
	    
		public function getLists(){
	    	return $this->lists;
	    }
	    
	    public function setLists($lists){
	    	$this->lists = $lists;
	    }
	    
		public function addList($list){
	    	$this->lists[$list->getId()] = $list;
	    }
	    
	    public function getAllRows() {
			$righe = array();
			
			foreach($this->getLists() as $oggetto) {
				$righe = $righe + $oggetto->getRows();
			}
			return $righe;
		}
	    public static function save($id, $obj, $directSave = false) {
			$lists = array_keys($obj->getLists());
			
			if(!isset($lists[0])) {
				$lists = array("");
			}
			
			if(isset($_REQUEST[$id."_".$lists[0]]) && !$directSave) {
				$allRows = $obj->getAllRows();
				foreach($obj->getLists() as $key => $list) {
					$app = array();
					if($_REQUEST[$id."_".$key]) {
						$request_val = explode(",", $_REQUEST[$id."_".$key]);
						foreach($request_val as $idRow) {
							$app[$idRow] = $allRows[$idRow];
						}
					}
					$list->setRows($app);
				}
			}else {
				$_SESSION[$id] = $obj;
			}
		}
		
		public static function clear($id) {
			unset($_SESSION[$id]);
		}
	}
?>