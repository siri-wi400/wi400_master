<?php
class wi400Tree {

	private $id;
	
	private $idList = "";
	private $idField = "";
	
    private $description;
    private $selection; // SINGLE || MULTIPLE
    // true || false per OGNI livello 
    // es. albero a tre lielli solo l'ulitmo selezionabile array(false,false,true)
    private $selectionLevels = array(); 
	
    // SPECIFICO PER ALBERO LEGATO A LISTA: filtro da applicare nella lista 
    // a seconda del livello selezionato
    private $filterLevels = array(); 
    
    // Funzione js chiamata alla selezione del nodo
    private $jsSelectionFunction;
    
    // Condizioni extra da applicare nella query
	private $where = "";
    
    private $rootFunction;
    private $nodeArray;
    
    private $showEmpty = true;
    
    private $childCheck = False;
    
    private $extra_params = array();
    
    private $max_depth;
    
    public function __construct($id){
    	$this->id = $id;
		$this->selection = "SINGLE";

		$this->nodeArray = array();
		$this->max_depth = -1;
    }
    
    
    public function findParent($node, $level){
    	$levelFunction = $this->nodeArray[$level];
		foreach($levelFunction as $parent => $childs){
			foreach ($childs as $k => $n){
				if ($k == $node){
					return $n->getParent();	
				}
			}
		}
		return "";
    }
    
    public function addNode($function, $parent, $wi400Node, $checkChild = false){
    	global $db;
    	if (!isset($this->nodeArray[$function])){
    		$this->nodeArray[$function] = array();
    	}
    	if (!isset($this->nodeArray[$function][$parent])){
    		$this->nodeArray[$function][$parent] = array();
    	}
    	// Devo avere abilitato il checkChild anche a livello di classe
     	if ($checkChild && $this->childCheck){

    		// Controllo esistenza figli
			$parameters = array();
			$parameters["NODE"] = $wi400Node->getId();
			$parameters["LEVEL"] = $wi400Node->getLevel();
			$parameters["SQL"] = true;
			$parameters["TREE"] = $this;
			
			//$sql = call_user_func("wi400_tree_".$wi400Node->getChildFunction(),$parameters);
			//$resultCount = $db->singleQuery($sql);
			$stmt = call_user_func("wi400_tree_".$wi400Node->getChildFunction(),$parameters);
			$resultCount = $db->execute($stmt, array($parameters["NODE"]));
			
		    if ($db->fetch_array($stmt)){
		    	$wi400Node->setHasChild(true);
		   	}else{
		   		$wi400Node->setHasChild(false);
		   	}
    	}
    	
    	// Nel caso sia definito showEmpty = false mostra i figli di root solo se hanno figli
    	if ($this->showEmpty || $wi400Node->getHasChild() || $parent != "ROOT"){
    		$this->nodeArray[$function][$parent][$wi400Node->getId()] = $wi400Node;
    	}
    }
    
	public function getMaxDepth(){
    	return $this->max_depth;
    }
    
    public function setMaxDepth($md){
    	$this->max_depth = $md;
    }
    
	public function getShowEmpty(){
    	return $this->showEmpty;
    }
    
    public function setShowEmpty($showEmpty){
    	$this->showEmpty = $showEmpty; 
    }
	
    public function getChildCheck(){
    	return $this->childCheck;
    }
    
    public function setChildCheck($childCheck){
    	$this->childCheck = $childCheck; 
    }    
	public function getJsSelectionFunction(){
    	return $this->jsSelectionFunction;
    }
    
    public function setJsSelectionFunction($jsSelectionFunction){
    	$this->jsSelectionFunction = $jsSelectionFunction; 
    }
    
	public function getFilterLevels(){
    	return $this->filterLevels;
    }
    
    public function setFilterLevels($filterLevels){
    	$this->filterLevels = $filterLevels; 
    }
    
	public function getSelectionLevels(){
    	return $this->selectionLevels;
    }
    
    public function setSelectionLevels($selectionLevels){
    	$this->selectionLevels = $selectionLevels; 
    }
    
    public function setWhere($where) {
    	$this->where = $where;
    }
    
    public function getWhere() {
    	return $this->where;
    }
    
    public function getNodeArray(){
    	return $this->nodeArray;
    }
    
    public function getId(){
    	return $this->id;
    }
    
    public function setId($id){
    	$this->id = $id; 
    }

    public function getIdList(){
    	return $this->idList;
    }
    
    public function setIdList($idList){
    	$this->idList = $idList; 
    }
    
    public function getIdField(){
    	return $this->idField;
    }
    
    public function setIdField($idField){
    	$this->idField = $idField; 
    }
    
    public function getDescription(){
    	return $this->description;
    }
    
    public function setDescription($description){
    	$this->description = $description; 
    }
    
    public function getSelection(){
    	return $this->selection;
    }
    
    public function setSelection($selection){
    	$this->selection = $selection; 
    }
    
    public function getRootFunction(){
    	return $this->rootFunction;
    }
    
    public function setRootFunction($rootFunction){
    	$this->rootFunction = $rootFunction; 
    	
    }
    
	public function setExtraParams($extra_params=array()) {
    	$this->extra_params = $extra_params;
    }
    
	public function getExtraParams() {
    	return $this->extra_params;
    }
    
    public function closeNode($childFunction, $node = "ROOT", $parentFunction = "", $parentNode = "ROOT", $level = 0){
    	
    	if ($parentFunction == ""){
			$parentFunction = $this->getRootFunction();
		}
    	
    	if (isset($this->nodeArray[$childFunction]) && isset($this->nodeArray[$childFunction][$node])){
    		if ($node != "ROOT"){
    			$nodeObj = $this->nodeArray[$parentFunction][$parentNode][$node];
    			$nodeObj->setOpen(false);
    			$this->nodeArray[$parentFunction][$parentNode][$node] = $nodeObj;
    		}
    	}
    	
    	wi400Session::save(wi400Session::$_TYPE_TREE, $this->id, $this);
    }
    
      public function getChild($childFunction, $node = "ROOT", $parentFunction = "", $parentNode = "ROOT", $level = 0){
		global $temaDir;
      	
		if ($parentFunction == ""){
			$parentFunction = $this->getRootFunction();
		}
			
    	$parameters = array(
    		"NODE" => $node,
    		"TREE"	=> $this,
    		"LEVEL" => $level
    	);
    	
    	
    	if (!isset($this->nodeArray[$childFunction]) || !isset($this->nodeArray[$childFunction][$node])){
    		call_user_func("wi400_tree_".$childFunction,$parameters);
    	}
    	
    	if (isset($this->nodeArray[$childFunction]) && isset($this->nodeArray[$childFunction][$node])){
    		
    		$counter = 0;
			
    		// Recupero nodo che ho appena cliccato
    		$hasNext = false;
    		if ($node != "ROOT"){
    			$nodeObj = $this->nodeArray[$parentFunction][$parentNode][$node];
    			$hasNext = $nodeObj->getHasNext();
    			$nodeObj->setOpen(true);
				$this->nodeArray[$parentFunction][$parentNode][$node] = $nodeObj;
    		}
    		
    		
    		foreach ($this->nodeArray[$childFunction][$node] as $id => $wi400Node){
	    		$lineImg = "";
	    		$folder = "";

	    		if (sizeof($this->nodeArray[$childFunction][$node])> ($counter + 1)){
	    			$wi400Node->setHasNext(true);
		    		if ($counter == 0 && $level == 0){
		    			if ($wi400Node->getHasChild()){
		    				if ($wi400Node->isOpen()){
		    					$lineImg = "minustop";
		    					$folder = "folder-expanded";
		    				}else{
		    					$lineImg = "plustop";
		    					$folder = "folder";
		    				}
		    			}else{
		    				$lineImg = "branchtop";
		    			}
		    		}else{
		    			if ($wi400Node->getHasChild()){
		    				if ($wi400Node->isOpen()){
		    					$lineImg = "minus";
		    					$folder = "folder-expanded";
		    				}else{
		    					$lineImg = "plus";
		    					$folder = "folder";
		    				}
		    			}else{
		    				$lineImg = "branch";
		    			}
		    		}
	    		}else{
	    			$wi400Node->setHasNext(false);
		    		if ($wi400Node->getHasChild()){
		    			if ($wi400Node->isOpen()){
		    				$lineImg = "minusbottom";
		    				$folder = "folder-expanded";
		    			}else{
		    				$lineImg = "plusbottom";
		    				$folder = "folder";
		    			}
		   			}else{
		   				$lineImg = "branchbottom";
		   			}
	    		}
	    		
    			$jsFunction = "";
				if ($wi400Node->getHasChild()){
					$jsFunction = "loadTreeChild('".$this->id."', '".$wi400Node->getChildFunction()."', '".$wi400Node->getId()."', '".$childFunction."', '".$node."', ".($level + 1).")";
				}
	    		
	    		$html = "<div id=\"".$this->id."_".($level + 1)."_".$wi400Node->getId()."\" style=\"display: inline;\" class=\"wi400-tree-menu\"><nobr>";
				
	    		for ($c=0; $c < $level;$c++){
	    			if (!$hasNext && $c == ($level - 1)){
	    				$html = $html."<img src=\"".$temaDir."images/treeDark/linebottom.gif\" align=\"top\" border=\"0\">";
	    			}else{
	    				$html = $html."<img src=\"".$temaDir."images/treeDark/line.gif\" align=\"top\" border=\"0\">";
	    			}
	    		}

				$html = $html."<img src=\"".$temaDir."images/treeDark/".$lineImg.".gif\" id=\"".$this->id."_".($level + 1)."_".$wi400Node->getId()."_line\"";
				$html = $html."onmousedown=\"".$jsFunction."\" align=\"top\" border=\"0\">";		
	    		$boldStart = "";
				$boldEnd   = "";
				
				//$nodeDescription = utf8_encode($wi400Node->getDescription());
				$nodeDescription = $wi400Node->getDescription();
				if (isset($this->selectionLevels[$level])){
					$wi400Node->setSelectable($this->selectionLevels[$level]);
				}
				
				if ($wi400Node->getSelectable()){
					$typeCheck = "checkbox";
					if ($this->selection == "SINGLE"){
						$typeCheck = "radio";
					}
					
					$html = $html."<input type=\"".$typeCheck."\" value=\"".$wi400Node->getId()."\" ";
					
					// Albero per lista
					if ($this->getIdList() != "" && $this->jsSelectionFunction != "" 
						&& isset($this->filterLevels[$level])
							&& $this->filterLevels[$level] != ""){
						$html = $html." onClick=\"".$this->jsSelectionFunction."('".$this->getIdList()."',this.value,'".$this->filterLevels[$level]."')\" ";
					}
					
					// Albero per field
					if ($this->getIdField() != "" && $this->jsSelectionFunction != "" ){
						$html = $html." onClick=\"".$this->jsSelectionFunction."(this.value,'".str_replace('"', "'", addslashes($nodeDescription))."','".$this->getIdField()."')\"";
					}
					
					$html = $html." name=\"".$this->id."_NODE\" id=\"".($level + 1)."\">";
				}

				if ($wi400Node->getHasChild()){
					$boldStart = "<b>";
					$boldEnd   = "</b>";
					$html = $html."<img src=\"".$temaDir."images/treeDark/".$folder.".gif\" id=\"".$this->id."_".($level + 1)."_".$wi400Node->getId()."_folder\" align=\"top\">";
				}
				
				
				$html = $html."<a href=\"javascript:".$jsFunction."\">";
				$html = $html."<span style='color:#666666'>".$boldStart.$nodeDescription.$boldEnd."</span>";
				$html = $html."</a>";
				$html = $html."</nobr><br>";
				
				/*if ($wi400Node->getHasChild()){
					$html = $html."<div style=\"display:block;margin:0px;padding:0px;\" id=\"".$this->id."_".($level + 1)."_".$wi400Node->getId()."_childs\"></div>";
				}*/
				echo $html;
	    		
	    		if ($wi400Node->isOpen()){
	    			echo "<div id=\"".$this->id."_".($level + 1)."_".$wi400Node->getId()."_childs\">";
	    			$this->getChild($wi400Node->getChildFunction(), $wi400Node->getId(), $childFunction, $node, ($level + 1));
	    			echo "</div>";
	    		}
	    		echo "</div>";
	    		
	    		$counter++;
	    	}
	    	wi400Session::save(wi400Session::$_TYPE_TREE, $this->id, $this);
    	}
    	
    }
    
    
    public function dispose(){
    	
    	echo "<div id=\"".$this->id."treeContainer\" class=\"treeContainer\">";
    	
    	$this->getChild($this->getRootFunction());
    	
    	echo "</div>";
    }
}
?>