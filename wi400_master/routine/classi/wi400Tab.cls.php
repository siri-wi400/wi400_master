<?php
class wi400Tab {

	private $id;
    private $description;
    private $fields;
    private $active;
    private $images = array();
    private $buttons;
    private $colsNum;
    
    public function __construct($id, $description, $colsNum = 1){
    	$this->fields = array();
    	$this->images = array();
    	$this->id = $id;
    	$this->description = $description;
    	$this->setActive(false);
    	$this->colsNum = $colsNum;
    }

    public function getColsNum(){
    	return $this->colsNum;	
    }
    
    public function setId($id){
    	$this->id = $id;
    }
    
    public function getId(){
    	return $this->id;
    }
    
	public function addImage($img){
    	$this->images[] = $img;
    }
    
    public function getImages(){
    	return $this->images;
    }
    
    public function setDescription($description){
    	$this->description = $description;
    }
    
    public function getDescription(){
    	return $this->description;
    }
    
	public function addField($field){
    	$this->fields[] = $field;
    }
    public function addButton($button){
    	$this->buttons[] = $button;
    }
    public function setButtons($buttons) {
    	$this->buttons = $buttons;
    }
    public function getFields(){
    	return $this->fields;
    }
	/**
	 * @desc ritorna l'array degli oggetti button della scheda
	 */
    public function getButtons(){
    	return $this->buttons;
    }
    
    public function setFields($fields){
    	$this->fields = $fields;
    }
    
    public function setActive($active){
    	$this->active = $active;
    }
    
    public function isActive(){
    	return $this->active;
    }

}
?>