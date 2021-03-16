<?php
class wi400Node {

	private $id;
    private $description;
    private $selectable;
    private $action;
    private $childFunction;
    private $hasChild;
    private $hasNext;
    private $level;
    private $open;
    private $parent;
    
    public function __construct($id = ""){
    	$this->id = $id;
    	$this->selectable = null;
		$this->level = 0;
    }
    
    public function setOpen($open){
    	$this->open = $open; 
    }
    
    public function isOpen(){
    	return $this->open;
    }
    
    public function getId(){
    	return $this->id;
    }
    
    public function setId($id){
    	$this->id = $id; 
    }
    
    public function getLevel(){
    	return $this->level;
    }
    
    public function setLevel($level){
    	$this->level = $level; 
    }

    public function getDescription(){
    	return $this->description;
    }
    
    public function setDescription($description){
    	$this->description = $description; 
    }
    
    public function getSelectable(){
    	return $this->selectable;
    }
    
    public function setSelectable($selectable){
    	$this->selectable = $selectable; 
    }

    public function getAction(){
    	return $this->action;
    }
    
    public function setAction($action){
    	$this->action = $action; 
    }
    
    public function getChildFunction(){
    	return $this->childFunction;
    }
    
    public function setChildFunction($childFunction){
    	$this->childFunction = $childFunction; 
    }

    public function getHasChild(){
    	return $this->hasChild;
    }
    
    public function setHasChild($hasChild){
    	$this->hasChild = $hasChild; 
    }
    
	public function getHasNext(){
    	return $this->hasNext;
    }
    
    public function setHasNext($hasNext){
    	$this->hasNext = $hasNext; 
    }

	public function getParent() {
		return $this->parent;
	}

	public function setParent($parent) {
		$this->parent = $parent;
	}

    
    
    
    
}
?>