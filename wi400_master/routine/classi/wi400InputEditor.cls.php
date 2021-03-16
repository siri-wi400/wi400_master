<?php

/**
 * @name wi400InputEditor 
 * @desc Classe per la creazione di un editor di testo
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 04/03/2009
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */
// WYSIWYG - Editor
if (file_exists($routine_path.'/ckeditor/ckeditor.php')) {
	require_once $routine_path.'/ckeditor/ckeditor.php';
}

class wi400InputEditor extends wi400InputText {

	private $ckeditor;
	private $value;
	private $toolBarSet ="Default";
	private $toolbarBasic = array(array( 'Bold','Italic','-','OrderedList','UnorderedList','-','Link','Unlink','-','wi400marker', 'About'));
	private $toolbarWI400 = array(array('Bold','Italic','Underline','-','Subscript','Superscript','-','TextColor','BGColor','-','OrderedList','UnorderedList','-','Link','Unlink','-','Cut','Copy','PasteText'),
								  array('JustifyLeft','JustifyCenter','JustifyRight','JustifyFull','-','Table', "wi400marker"));
	/**
	 * Costruttore della classe
	 *
	 * @param string $id	: ID dell'editor di testo da creare
	 */
    public function __construct($id){
    	global $appBase;
    	
		$this->setId($id);
    	$this->setType("TEXT_EDITOR");

    	$this->ckeditor = new CKeditor();
		/*$this->ckeditor->BasePath	= $appBase."routine".$this->ckeditor->BasePath;
		$this->ckeditor->ToolbarSet = "Default";*/
		
/*		$ckeditor = new CKEditor();
		$ckeditor->basePath = $appBase."routine/ckeditor/";
		//$ckeditor->config['filebrowserBrowseUrl'] = '/ckfinder/ckfinder.html';
		//$ckeditor->config['filebrowserImageBrowseUrl'] = '/ckfinder/ckfinder.html?type=Images';
		//$ckeditor->config['filebrowserFlashBrowseUrl'] = '/ckfinder/ckfinder.html?type=Flash';
		//$ckeditor->config['filebrowserUploadUrl'] = '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';
		//$ckeditor->config['filebrowserImageUploadUrl'] = '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images';
		//$ckeditor->config['filebrowserFlashUploadUrl'] = '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash';
		$this->ckeditor->ToolbarSet = "Default";
		$ckeditor->editor($id);*/
    }
    
    /**
     * Impostazione del nome della toolbar da caricare
     *
     * @param string $tbs	: nome della toolbar da caricare
     */
    public function setToolBarSet($tbs){
    	$this->toolBarSet = $tbs;
    }
    
    /**
     * Recupero del nome della toolbar da scaricare
     *
     * @return string
     */
    public function getToolBarSet(){
    	return $this->toolBarSet;
    }
    
    /**
     * Impostazione del valore iniziale
     *
     * @param unknown_type $value
     */
    public function setValue($value=""){
    	$this->value = $value;
    }
    
    /**
     * Recupero del valore iniziale
     *
     * @return unknown
     */
    public function getValue(){
    	return $this->value;
    }
    
    /**
     * Impostazione dell'altezza dell'edito
     *
     * @param integer $height
     */
    public function setHeight($height){
    	$this->ckeditor->config['height'] = $height;
    }
    
    /**
     * Recupero dell'altezza dell'editor
     *
     * @return interger
     */
    public function getHeight(){
    	return $this->ckeditor->Height;
    }
    
    /**
     * Impostazione della larghezza dell'editor
     *
     * @param integer $width
     */
    public function setWidth($width){
    	$this->ckeditor->Width = $width;
    }
    
    /**
     * Recupero della larghezza dell'editor
     *
     * @return integer
     */
    public function getWidth(){
    	return $this->ckeditor->Width;
    }
    
    /**
     * Recupero del codice html che costituisce l'editor
     *
     * @return unknown
     */
    public function getHtml(){
    	//return $this->ckeditor->CreateHtml();
    }
    
    /**
     * Visualizzazione dell'editor di testo
     *
     */
    public function dispose(){
    	global $appBase;
    	// Mi trovo all'interno di un dettaglio	
    	if ($this->getIdDetail()!="") {
    		echo "<td>";  	
    	}
    	//echo $this->getHtml();
    	$this->ckeditor->basePath = $appBase."routine/ckeditor/";
    	$toolBar = $this->getToolBarSet();
    	$config = array();
    	if ($toolBar =="WI400") {
    		$config['toolbar'] = $this->toolbarWI400;
    	} elseif ($toolBar =="Basic") {
    		$config['toolbar'] = $this->toolbarBasic;
    	}
    	$config['marker_type']="marker.html";

    	$this->ckeditor->editor($this->getId(), $this->getValue(), $config);
	    // Mi trovo all'interno di un dettaglio	
    	if ($this->getIdDetail()!="") {
    		echo "</td>";  	
    	}
    }
    
}
?>