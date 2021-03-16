<?php

/**
 * @name wi400Applet
 * @desc Inserisce un'applet java
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 13/09/2010
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400Applet {

    private $code;
    private $name;
    private $width;
    private $height;
    private $archive;
    private $params;
    private $urlSuffix;

    public function __construct(){
    	$getAttach = "";
    }
    
	public function setCode($code){
		$this->code = $code;
	}
	public function getCode(){
		return $this->code;
	}

	public function setArchive($archive){
		$this->archive = $archive;
	}
	public function getArchive(){
		return $this->archive;
	}
    
	/**
	 * Impostazione altezza dell'applet. 
	 * 
	 * @param numeric $height: altezza in pixel  dell'applet. 
	 */
	public function setHeight($height){
		$this->height = $height;
	}
	
	/**
	 * Recupero dell'altezza dell'applet.
	 *
	 * @return numeric
	 */
	public function getHeight(){
		return $this->height;
	}
	
	/**
	 * Impostazione larghezza  dell'applet. 
	 *
	 * @param numeric $width: larghezza in pixel  dell'applet. 
	 */
	public function setWidth($width){
		$this->width = $width;
	}
	
	/**
	 * Recupero della larghezza dell'applet. 
	 *
	 * @return numeric
	 */
	public function getWidth(){
		return $this->width;
	}
	
	
	
    public function addParam($key, $desc, $attachToUrl = false){
    	if ($attachToUrl){
    		$this->urlSuffix = $this->urlSuffix."&".$key."=".$desc;
    	}
    	$this->params[$key] = $desc;
    }
    
    public function dispose(){
		echo "<script>";
		echo "var obHTML = \"".$this->getHtml()."\";";
		echo "document.write(obHTML);";
		echo "</script>";
    }
	
	public function getHtml(){
		$html = "<applet code='".$this->code."' archive='".$this->archive."'  width='".$this->width."' height='".$this->height."'>";
    	foreach ($this->params as $key => $desc){
    		   $html .= "<param name='".$key."' value='".$desc."'>";
    	}
    	$html .= "<param name='url_suffix' value='".$this->urlSuffix."'>";
    	$html .= "</applet>";
		return $html;
	}
    
}
?>