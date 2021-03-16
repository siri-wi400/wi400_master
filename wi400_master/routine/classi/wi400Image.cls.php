<?php

/**
 * @name wi400Image 
 * @desc Classe per la gestione delle immagini
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author Massimiliano Consigli
 * @version 1.00 12/08/2008
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

require_once $routine_path.'/classi/wi400Input.cls.php';

class wi400Image extends wi400Input {

	private $url;
	private $width;
	private $height;
	private $showContenitore;
	private $sizeContenitore;
	
	private $imgArray = array();
	private $des_array = array();
	
	private $html_width;
	private $html_height;

	private $showZoom;
	private $zoomUrl;
	private $directUrl;
	private $confirmDelete;

	private $manager;
	private $objCode;
	private $objType; // ART, ENT, ecc ...
	private $imgType; // contesto. default null
	private $maxCount;

	private $addImage;
	private $allImageButton;
	private $deleteImage;
	
	private $horizontalView;

	private $basePath;
	
	private $alt = "";
	
	private $imgPathArray = array();
	
	private $colsNum = 1;
	
	private $openImage = true;
	
	private $convertImage = "";
	private $convertTmp = false;
	private $customTools = array();

	/**
	 * Costruttore della classe
	 *
	 * @param string $id	: ID dell'immagine
	 */
	public function __construct($id){
		$this->setId($id);
		$this->setType("IMAGE");
		$this->url = "";
		$this->showZoom = false;
		$this->showContenitore = false;
		$this->zoomUrl = "";
		$this->directUrl = false;
		$this->confirmDelete = false;

		$this->setManager(false);
		$this->setAddImage(false);
		$this->setAllImageButton(false);
		$this->setDeleteImage(true);
		$this->setMaxCount(8);
		$this->setSizeContenitore(150);
		
		$this->setHorizontalView(false);
		 
		$this->setImgType("");
		$this->setObjType("");
		$this->setObjCode("");
		$this->setBasePath("");
	}

	/**
	 * Restituisce il percorso completo di un'immagine
	 *
	 * @return string
	 */
	public function getImagePath(){
		global $settings;
		$completePath = $settings['uploadPath'];
		if ($this->getObjType() != ""){
			$completePath = $completePath.$this->getObjType()."/";
		}
		if ($this->getImgType() != ""){
			$completePath = $completePath.$this->getImgType()."/";
		}
		return $completePath;
	}

	/**
	 * Impostazione della possibilità di cancellare l'associazine di un'immagine
	 *
	 * @param boolean $confirmDelete	: true se è possibile cancellare l'associazione di un'immagine, false altrimenti
	 */
	public function setConfirmDelete($confirmDelete){
		$this->confirmDelete = $confirmDelete;
	}
	
	/**
	 * Recupero dell'impostazione della possibilità di cancellare l'associazione di un'immagine
	 *
	 * @return boolena	Ritorna true se è possibile cancellare l'associazione di un'immagine, false altrimenti
	 */
	public function getConfirmDelete(){
		return $this->confirmDelete;
	}

	/**
	 * Impostazione della possibilità di visualizzare una versione ingrandita dell'immagine
	 *
	 * @param boolean $showZoom	: true se è possibile visualizzare una versione ingrandita dell'immagine, false altrimenti
	 */
	public function setShowZoom($showZoom){
		$this->showZoom = $showZoom;
	}
	
	/**
	 * Recupero dell'impostazione di visualizzazione dell'immagine
	 *
	 * @return boolean	Ritorna true se si può visualizzare una versione ingrandita dell'immagine, false altrimenti
	 */
	public function getShowZoom(){
		return $this->showZoom;
	}

	/**
	 * Impostazione della possibilità di associare un'immagine
	 *
	 * @param boolean $addImage	: true se è possibile associare un'immagine, false altrimenti
	 */
	public function setAddImage($addImage){
		$this->addImage = $addImage;
	}
	
	/**
	 * Recupero dell'impostazione della possibilità di associare un'immagine
	 *
	 * @return boolean	Ritorna true se è possibile associare un'immagine, false altrimenti
	 */
	public function getAddImage(){
		return $this->addImage;
	}
	
	/**
	 * Impostazione della possibilità di cancellare un'immagine
	 *
	 * @param boolean $deleteImage	: true se è possibile cancellare un'immagine, false altrimenti
	 */
	public function setDeleteImage($deleteImage){
		$this->deleteImage = $deleteImage;
	}
	
	/**
	 * Recupero dell'impostazione della possibilità di cancellare un'immagine
	 *
	 * @return boolean	Ritorna true se è possibile cancellare un'immagine, false altrimenti
	 */
	public function getDeleteImage(){
		return $this->deleteImage;
	}

	public function setBasePath($basePath){
		$this->basePath = $basePath;
	}
	
	public function getBasePath(){
		return $this->basePath;
	}
	
	/**
	 * Impostazione del tipo di visulizzazione delle immagini. Orizzontale o verticale
	 * 
	 * @param boolean
	 */
	public function setHorizontalView($view) {
		$this->horizontalView = $view;
	}
	
	/**
	 * Recupero del tipo di visulizzazione delle immagini. Orizzontale o verticale
	 *
	 * @return boolean  Ritorna true se le immagini sono disposte in modo orizzontale, false in verticale
	 */
	public function getHorizontalView() {
		return $this->horizontalView;
	}

	/**
	 * Impostazione del tipo dell'immagine
	 *
	 * @param string $objType	: tipo dell'immagine
	 */
	public function setImgType($imgType){
		$this->imgType = $imgType;
	}
	
	/**
	 * Recupero del tipo dell'immagine
	 *
	 * @return string
	 */
	public function getImgType(){
		return $this->imgType;
	}

	/**
	 * Impostazione dell'ID dell'immagine
	 *
	 * @param string $objCode	: ID dell'immagine
	 */
	public function setObjCode($objCode){
		$this->objCode = $objCode;
	}
	
	/**
	 * Recupero dell'ID dell'immagine
	 *
	 * @return string
	 */
	public function getObjCode(){
		return $this->objCode;
	}

	/**
	 * Impostazione del tipo dell'oggetto
	 *
	 * @param string $objType	: tipo dell'oggetto	(es: ART)
	 */
	public function setObjType($objType){
		$this->objType = $objType;
	}
	
	/**
	 * Recupero del tipo dell'oggetto (es: ART)
	 *
	 * @return string
	 */
	public function getObjType(){
		return $this->objType;
	}

	/**
	 * Abilitazione della gestione dell'immagine
	 *
	 * @param boolean $manager	: settato a true se si vuole aggiungere all'immagine lo strumento di gestione, false altrimenti
	 */
	public function setManager($manager){
		$this->manager = $manager;
	}
	
	/**
	 * Impostazione del numero massimo di immagini visualizzate per l'articolo selezionato
	 * 
	 * @param int
	 */
	public function setMaxCount($num) {
		$this->maxCount = $num;
	}
	
	/**
	 * Recupero il numero massimo di immagini visualizzate per l'articolo selezionato
	 *
	 * @return int
	 */
	public function getMaxCount() {
		return $this->maxCount;
	}
	
	/**
	 * Impostazione per la visulizzazione del bottone: "Tutte le immagini"
	 * 
	 * @param boolean
	 */
	public function setAllImageButton($show) {
		$this->allImageButton = $show;
	}
	
	/**
	 * Recupero dell'impostazione di visulizzazione del bottone: "Tutte le immagini"
	 * 
	 * @return boolean  Ritorna true se il bottone è visibile, false altrimenti
	 */
	public function getAllImageButton() {
		return $this->allImageButton;
	}


	/**
	 * Recupero dello stato dell'impostazione del manager dell'immagine
	 *
	 * @return boolean	Ritorna true se il manager è abilitato, false altrimenti
	 */
	public function getManager(){
		return $this->manager;
	}

	/**
	 * Impostazione altezza dell'immagine. 
	 * Se impostata solo l'altezza, la larghezza viene calcolata in percentuale.
	 *
	 * @param numeric $height	: altezza in pixel dell'immagine
	 */
	public function setHeight($height){
		$this->height = $height;
	}
	
	/**
	 * Recupero dell'altezza dell'immagine
	 *
	 * @return numeric
	 */
	public function getHeight(){
		return $this->height;
	}
	
	/**
	 * Impostazione larghezza dell'immagine. 
	 * Se impostata solo la larghezza, l'altezza viene calcolata in percentuale.
	 *
	 * @param numeric $width	: larghezza in pixel dell'immagine
	 */
	public function setWidth($width){
		$this->width = $width;
	}
	
	/**
	 * Recupero della larghezza dell'immagine
	 *
	 * @return numeric
	 */
	public function getWidth(){
		return $this->width;
	}
	
	/**
	 * Impostazione altezza dell'immagine.
	 * Se impostata solo l'altezza, la larghezza viene calcolata in percentuale.
	 *
	 * @param numeric $height	: altezza in pixel dell'immagine
	 */
	public function setHtmlHeight($height){
		$this->html_height = $height;
	}
	
	/**
	 * Recupero dell'altezza dell'immagine
	 *
	 * @return numeric
	 */
	public function getHtmlHeight(){
		return $this->html_height;
	}
	
	/**
	 * Impostazione larghezza dell'immagine.
	 * Se impostata solo la larghezza, l'altezza viene calcolata in percentuale.
	 *
	 * @param numeric $width	: larghezza in pixel dell'immagine
	 */
	public function setHtmlWidth($width){
		$this->html_width = $width;
	}
	
	/**
	 * Recupero della larghezza dell'immagine
	 *
	 * @return numeric
	 */
	public function getHtmlWidth(){
		return $this->html_width;
	}
	
	/**
	 * Impostazione grandezza contenitore per adattare l'immagine a esso
	 * 
	 * @param numeric
	 */
	public function setSizeContenitore($value) {
		$this->sizeContenitore = $value;
	}
	
	/**
	 * Recupero grandezza contenitore per adattare l'immagine a esso
	 *
	 * @return numeric
	 */
	public function getSizeContenitore() {
		return $this->sizeContenitore;
	}
	
	/**
	 * Impostazione per visualizzare il contenitore per adattare l'immagine a esso
	 *
	 * @param boolen
	 */
	public function setShowContenitore($value) {
		$this->showContenitore = $value;
	}
	
	/**
	 * Recupero l'impostazione di visualizzazione del contenitore per adattare l'immagine a esso
	 *
	 * @param boolen
	 */
	public function getShowContenitore() {
		return $this->showContenitore;
	}
	
	public function setColsNum($num) {
		$this->colsNum = $num;
	}
	
	public function getColsNum() {
		return $this->colsNum;
	}
	
	public function setAlt($alt) {
		$this->alt = $alt;
	}
	
	public function getAlt() {
		return $this->alt;
	}

	/**
	 * Impostazione dell'indirizzo completo di un'immagine
	 *
	 * @param string $url	: indirizzo completo dell'immagine
	 */
	public function setUrl($url=null){
		$this->url = $url;
	}
	
	/**
	 * Recupero dell'indirizzo completo di un'immagine
	 *
	 * @return string
	 */
	public function getUrl(){
		return $this->url;
	}
	
	public function get_imgArray() {
		return $this->imgArray;
	}
	
	public function set_des_array($des_array) {
		$this->des_array = $des_array;
	}
	
	public function get_des_array() {
		return $this->des_array;
	}
	
	public function setZoomUrl($url=null){
		$this->zoomUrl = $url;
	}
	
	public function getZoomUrl(){
		return $this->zoomUrl;
	}
	
	public function setDirectUrl($directUrl){
		$this->directUrl = $directUrl;
	}
	
	public function getDirectUrl(){
		return $this->directUrl;
	}
	
	public function setImgPathArray($urlArray) {
		$this->imgPathArray = $urlArray;
	}
	
	public function setImgPathArray_el($url) {
		$this->imgPathArray = $url;
	}
	
	public function getImgPathArray() {
		return $this->imgPathArray;
	}
	
	public function set_openImage($open) {
		$this->openImage = $open;
	}
	
	public function get_openImage() {
		return $this->openImage;
	}
	
	public function setConvertImage($convert, $tmp=false) {
		$this->convertImage = $convert;
		$this->convertTmp = $tmp;
	}
	
	public function getConvertImage() {
		return $this->convertImage;
	}
	
	public function getConvertTmp() {
		return $this->convertTmp;
	}
	
	/**
	 * Aggiunge un customTool al campo di testo
	 *
	 * @param object wi400CustomTool
	 */
	public function addCustomTool($tool) {
		$this->customTools[] = $tool;
	}
	
	 
	/**
	 * Recupero i tool associati alla classe wi400CustomTool
	 *
	 * @return array di wi400CustomTool
	 */
	public function getCustomTool() {
		return $this->customTools;
	}
	
	public static function ConvertImage($filename, $imagePath, $to="jpg", $tmp=false) {
		$doc_root = $_SERVER ['DOCUMENT_ROOT'];
		if(substr($_SERVER ['DOCUMENT_ROOT'], -1)=="/")
			$doc_root = substr($_SERVER ['DOCUMENT_ROOT'], 0, -1);
//		echo "SERVER DOCUMENT_ROOT: ".$_SERVER ['DOCUMENT_ROOT']."<br>";
//		echo "DOC_ROOT: $doc_root<br>";

		$system = explode ( '.', $filename );

		$new_file = $system[0] . "." .$to;
	
		$saveFile = $doc_root . $imagePath . $new_file;
		
		if($tmp==true) {
			$saveFile = wi400File::getUserFile("tmp", basename($new_file));
			$new_file = $saveFile;
		}
		
//		echo "IMAGE PATH: $imagePath - FILENAME: $filename<br>";
//		echo "NEW_FILE: $saveFile<br>";

		if (preg_match ( '/jpg|jpeg|JPG|JPEG/', $system [1] )) {
			$from = "jpg";
			
			if (preg_match ( '/jpg|jpeg|JPG|JPEG/', $to ))
				return false;
		}
		else if (preg_match ( '/png|PNG/', $system [1] )) {
			$from = "png";	
			
			if (preg_match ( '/png|PNG/', $to ))
				return false;
		} 
		else if (preg_match ( '/gif|GIF/', $system [1] )) {
			$from = "gif";
			
			if (preg_match ( '/gif|GIF/', $to ))
				return false;
		} 
		else if (preg_match ( '/bmp|BMP/', $system [1] )) {
			$from = "bmp";
			
			if (preg_match ( '/bmp|BMP/', $to ))
				return false;
		}
	
		if (! file_exists ( $saveFile )) {
//			$name = $_SERVER ['DOCUMENT_ROOT'] . $imagePath . $filename;
			$name = $doc_root . $imagePath . $filename;
//			echo "NAME: $name<br>";
			// LZ Patch per alzare il limite di memoria, fatal error in VEGA
			ini_set("memory_limit","400M");
			
//			$system = explode ( '.', $name );

			if($system[1]=="jpg") {
				$src_img = imagecreatefromjpeg ( $name );
			}
			else if($system[1]=="png") {
				$src_img = imagecreatefrompng ( $name );
			}
			else if($system[1]=="gif") {
				$src_img = imagecreatefromgif ( $name );
			}
			else if($system[1]=="bmp") {
				$src_img = imagecreatefromwbmp ( $name );
			}
	
			$imageFormat = "";
			if (preg_match ( '/jpg|jpeg|JPG|JPEG/', $to )) {
				imagejpeg ( $src_img, $saveFile );
				$imageFormat = "jpg";
			} else if (preg_match ( '/png|PNG/', $to )) {
				imagepng ( $src_img, $saveFile );
				$imageFormat = "png";
			} else if (preg_match ( '/gif|GIF/', $to )) {
				imagegif ( $src_img, $saveFile );
				$imageFormat = "gif";
			} else if (preg_match ( '/bmp|BMP/', $to )) {
				imagewbmp ( $src_img, $saveFile );
				$imageFormat = "bmp";
			}
	
//			imagedestroy ( $dst_img );
			imagedestroy ( $src_img );
//			imagedestroy ( $saveFile );
		}
		
		return $new_file;
	}

	/**
	 * Recupero del codice html per visualizzare un'immagine
	 *
	 * @return string	Ritorna il codice html da utilizzare per visualizzare un'immagine
	 */
	public function getHtml($br=true){
		global $appBase,$temaDir,$root_path,$db, $settings;
		
		$html = "";
		$oriz = $this->getHorizontalView();
			
		
		//Altezza div che contiene le immagini orizzontali
		if($this->manager) {
			$altez = $this->getSizeContenitore()+2+32;
		}
		else {
			$altez = $this->getSizeContenitore()+2;
		}
		
		$this->imgArray = array();
		
		if ($this->getObjCode() != "" && $this->getObjType() != ""){
			// Reperisco le immagini collegate a questo oggetto
			$sql="SELECT * FROM OBJ_IMG WHERE OBJ_CODE = '".$this->getObjCode()."' AND OBJ_TYPE='".$this->getObjType()."' ";

			if ($this->getImgType() != ""){
				$sql = $sql." AND IMG_TYPE='".$this->getImgType()."'";
			}
			else{
				$sql = $sql." AND IMG_TYPE is null";
			}
			
			$sql .= " order by IMG_CODE";
			
			$max = $this->getMaxCount();
			//$max = 100;
			$cont = 0;

			$result = $db->query($sql);
			while(($imgRow = $db->fetch_array($result)) && $cont < $max) {
				if (isset($imgRow["IMG_CODE"])) {
					$this->imgArray[$imgRow["IMG_CODE"]] = $imgRow["IMG_CODE"].".".$imgRow["IMG_EXT"];
				}
				if (sizeof($this->imgArray)===0) {
					$this->imgArray[] = "null";
				}
				$cont++;
			}
		}
		else{
			//Nome del file giusto
			if(is_array($this->url))
				$this->imgArray = $this->url;
			else
				$this->imgArray[] = $this->url;
		}
		
		$confirmString = "false";
		if ($this->getConfirmDelete()){
			$confirmString = "true";
		}
		
		//Div che contiene tutte le immagini
		if ($this->manager && !$oriz) {
			$html = $html."<div id='IMG_MANAGER_".$this->getId()."'>";
		}
		else {
			if($this->manager && $oriz) {
				// +2 per i bordi + 32 per il pezzo manager che elimina l'immagine
				$html .= '<div style="position: absolute; width: 342px; height: '.$altez.'px; top: 50%; margin-top: -'.($altez/2).'px">';
			}
		}
		//Più colonne
		if($this->colsNum>1 && !$oriz) {
			$html = $html."<table>";
			
			$c = 0;
		}
		
		$des_array = $this->get_des_array();
//		echo "DES_ARRAY:<pre>"; print_r($des_array); echo "</pre>";
		
		$left = 0;
		foreach ($this->imgArray as $imgCode => $imgName){
//			echo "IMGCODE: $imgCode<br>";
			
			if($this->colsNum>1 && !$oriz) {
				if(($c % $this->colsNum)==0)
					$html = $html."<tr>";
			
				$html = $html."<td>";
			}
			
			if ($imgName != "" && $imgName != "null") {
				
				$completePath = "";
				if($this->getDirectUrl()==false)

					
					$completePath = $this->getImagePath();
//				echo "COMPLETE PATH: $completePath<br>";	
					
//				echo "PATH: "$_SERVER['DOCUMENT_ROOT'].$completePath.$imgName."<br>";
				$imgUrl = "";
				if (!file_exists($_SERVER['DOCUMENT_ROOT'].$completePath.$imgName)){

					if (strnpos($imgName, "http:") !== false){
						// Indirizzo web
						$imgUrl = $imgName;
/*
						if ($this->getShowZoom() !== false){
							$this->setOnClick("showImageZoom(null,null,'".$this->getShowZoom()."');");
						}
*/
						if ($this->getShowZoom()!==false){
							$zoomImg = $this->getShowZoom();
							if($this->getZoomUrl()!="")
								$zoomImg = $this->getZoomUrl();
//							echo "IMG ZOOM: $zoomImg<br>";
							$this->setOnClick("showImageZoom(null,null,'".$zoomImg."','".$this->getDirectUrl()."');");
						}
					}else{
						if ($this->width > 0 || $this->height > 0){
							$imgUrl = $settings['uploadPath'].$this->createthumb($this->getBasePath().$settings['uploadPath'],"null.jpg",$this->width,$this->height);
						}
						else{
	//						$imgUrl = $this->getBasePath().$settings['uploadPath']."null.jpg";
							if(file_exists($imgName))
								$imgUrl = $imgName;
							else
								$imgUrl = $this->getBasePath().$settings['uploadPath']."null.jpg";
						}
					}
//					echo "IMG URL: $imgUrl<br>";
				}
				else{
					if ($this->width > 0 || $this->height > 0){
						$thumbPath = $completePath;
						$thumbName = $imgName;
						if($this->getDirecturl()==true) {
							$thumbPath = dirname($imgName)."/";
							$thumbName = basename($imgName);
						}
						
						$imgUrl = $thumbPath.$this->createthumb($thumbPath,$thumbName,$this->width,$this->height);
/*						
						if ($this->getShowZoom()){
							$this->setOnClick("showImageZoom('".$this->getObjType()."','".$this->getImgType()."','".$imgName."');");
						}
*/						 
					}
					else{
						$imgUrl = $completePath.$imgName;
					}
					
//					echo "IMG URL: $imgUrl<br>";
					
					if ($this->getShowZoom()){
						$zoomImg = $imgName;
						if($this->getZoomUrl()!="")
							$zoomImg = $this->getZoomUrl();
//						echo "IMG ZOOM: $zoomImg<br>";
						$this->setOnClick("showImageZoom('".$this->getObjType()."','".$this->getImgType()."','".$zoomImg."','".$this->getDirectUrl()."');");
					}
				}
				
//				echo "IMG_URL: $imgUrl<br>";
				
				if($this->getConvertImage()!="") {
//					echo "PATH: $this->getBasePath()<br>";
					
					$imgConv = $this->ConvertImage($imgUrl, $this->getBasePath(), $this->getConvertImage(), $this->getConvertTmp());
					if($imgConv!==false)
						$imgUrl = $imgConv;
				}
				
//				echo "IMG_URL: $imgUrl<br>";
				
				$this->url = $imgUrl;
				
				$this->imgPathArray[] = $imgUrl;
				 
				$imgStyle = "";
				$onClickFunction = "";
				if ($this->getStyle()!="") {
					$imgStyle = "style='".$this->getStyle()."'";
				}
				if ($this->manager) {
					if($this->showContenitore) {
						//Reperisco base e altezza dal percorso locale nel server
						list($width, $height, $type, $attr) = getimagesize($_SERVER['DOCUMENT_ROOT'].$this->getBasePath().$imgUrl);
						
						// Se non trovo niente reperisco i varoli dall'url
						if(!$width) {
							list($width, $height, $type, $attr) = getimagesize($imgUrl);
						}
						
						if($width > $height) {
							$imgStyle = "style='position: absolute; width: 100%; top: 0px; right: 0px; bottom: 0px; left: 0px; margin: auto;'";
						}
						else {
							$imgStyle = "style='position: absolute; height: 100%; top: 0px; right: 0px; bottom: 0px; left: 0px; margin: auto;'";
						}
					}
					else {
						$imgStyle = "style='border:1px solid #CCCCCC'";
					}
				}
				 
				if ($this->getOnClick() != ""){
					$onClickFunction = "onClick=\"".$this->getOnClick()."\"";
				}
				$alt='';
				if($this->alt!="") 
					$alt = "alt='".$this->getAlt()."'";
				 
					
				$width = "";
				if ($this->width > 0){
					$width = 'width="'.$this->width.'"';
				}
				
				$height = "";
				if ($this->height > 0){
					$height = 'height="'.$this->height.'"';
				}
				
				$id = "";
				if ($this->getId() != ""){
					$id = 'id="'.$this->getId().'"';
				}
				
				//Creazione codice foto
				if(!$oriz) {
					if($this->showContenitore) {
						$html .= "<div style='position: relative; width: ".$this->sizeContenitore."px; height: ".$this->sizeContenitore."px; border:1px solid #CCCCCC; '>";
					}
					
					//$width = "";
					if ($this->html_width > 0){
						$width = 'width="'.$this->html_width.'"';
					}
					
					//$height = "";
					if ($this->html_height > 0){
						$height = 'height="'.$this->html_height.'"';
					}
					
					$html .= "<img ".$id." src=\"".$this->getBasePath().$imgUrl."\" $width $height class=\"wi400-pointer\" border=\"0\" ".$imgStyle." ".$onClickFunction." ".$alt.">";
					
					// Descrizioni
					if(isset($des_array[$imgCode])) {
//						echo "DES:".$des_array[$imgCode]."<br>";
						$html .= "<p align='center'>".$des_array[$imgCode]."</p>";
					}
					
					if($br===true)
						$html .= "<br>";
					
					if($this->showContenitore) {
						$html .= "</div>";
					}
				}
				else {
					$html .= "<div style=\"position: absolute; top: 0px; left: ".$left."px; width: ".($this->getSizeContenitore()+2)."px;\" id='IMG_MANAGER_".$this->getId()."'>";
					if($this->showContenitore) {
						$html .= "<div style='position: relative; width: ".$this->sizeContenitore."px; height: ".$this->sizeContenitore."px; border:1px solid #CCCCCC; '>";
					}
					
					$html .= "<img ".$id." src=\"".$this->getBasePath().$imgUrl."\" class=\"wi400-pointer\" border=\"0\" ".$imgStyle." ".$onClickFunction." ".$alt.">";
					
					// Descrizioni
					if(!empty($des_array)) {
//						echo "DES:".$des_array[$imgCode]."<br>";
						$html .= "<p>".$des_array[$imgCode]."</p>";
					}
					
					if($br===true)
						$html .= "<br>";
					
					if($this->showContenitore) {
						$html .= "</div>";
					}
					if(!$this->manager) {
						$html .= "</div>";
					}
				}
				
				//Pezzo aggiuntivo per eliminare la foto
				if ($this->manager && $this->getDeleteImage()){
					/*$html = $html."<table width='100%' class='wi400ManageImage'><tr>";
					$html = $html."<td align='left'><input type=\"image\" title=\"Cancella immagine\"  onClick=\"deleteImage('".$imgCode."','".$this->objCode."','".$this->objType."','".$this->imgType."','IMG_MANAGER_".$this->getId()."',".$confirmString.",".$this->colsNum.")\" src=\"".$temaDir."images/delete.gif\"></td>";
					$html = $html."<td width='100%'>&nbsp;</td>";
					$html = $html."</tr></table><br>";*/
					$isOriz = $oriz ? 1 : 0;
					$html .= '<div class="wi400ManageImage" width="100%" style="position: relative;">';
					$html .= "<input type=\"image\" title=\"Cancella immagine\" style=\"position: absolute; height: 16px; top: 50%; margin-top: -8px; left: 0px;\" onClick=\"deleteImage('".$imgCode."','".$this->objCode."','".$this->objType."','".$this->imgType."','IMG_MANAGER_".$this->getId()."',".$confirmString.",".$this->colsNum.",".$this->maxCount.",".$this->sizeContenitore."); if(".$isOriz."){setTimeout(function(){var iframe = parent.jQuery('iframe'); var num = iframe.length-1; iframe[num].src = iframe[num].src;}, 100);}\" src=\"".$temaDir."images/delete.gif\">";
					if($oriz) {
						$html .= '</div></div>';
					}
					else {
						$html .= '</div>';
						if($br===true)
							$html .= '<br/>';
					}
				}
				
				$left += $this->getSizeContenitore()+30;
			}
			else{
				$html = $html.$this->getValue();
			}
			
			//Più colonne
			if($this->colsNum>1 && !$oriz) {
				$html = $html."</td>";
			
				if(($c+1 % $this->colsNum)==0)
					$html = $html."</tr>";
			
				$c++;
			}
		}
		
		if ($this->manager) {
			if($this->colsNum>1  && !$oriz) {
				$html = $html."</table>";
			}
			
			$html = $html."</div>";
		}
			
		if ($this->getObjCode() != "" && $this->manager && $this->getAddImage()){
			$html = $html."<table width='100%' class='wi400ManageImage' style='border:1px solid #CCCCCC'><tr>";
			$html = $html."<td align='center' class='text'><input type=\"image\" title=\"Aggiungi Immagine\"  onClick=\"manageImage('".$this->objCode."','".$this->objType."','".$this->imgType."','IMG_MANAGER_".$this->getId()."', ".$confirmString.", ".$this->colsNum.", ".$this->maxCount.", ".$this->sizeContenitore.")\" src=\"".$temaDir."images/add.png\">&nbsp;<br><span style='white-space: nowrap;'>Aggiungi Immagine</span></td>";
			$html = $html."</tr></table>";
		}
		
		//echo '<script>alert("Prima dell\'if '.count($this->imgArray).'   '.$this->getAllImageButton().'");</script>';
		if($this->getAllImageButton()) {
			//echo $this->getAllImageButton()."<br/>";
			$html .= "<br/>";
			
			$myButton = new wi400InputButton($this->getId().'_ALL_IMAGE');
			$myButton->setLabel("Tutte le immagini");
			$myButton->setButtonStyle("width: 100%; height: 32px; margin: 0px; border: solid 1px #CCCCCC; color: black; font-weight: normal; display: inline;");
			$myButton->setAction("IMAGE_WINDOW_MANAGER");
			$myButton->setTarget("WINDOW", "675", "372", true, "reloadImage(\'".$this->objCode."\',\'".$this->objType."\',\'".$this->imgType."\',\'IMG_MANAGER_".$this->getId()."\',".$confirmString.",".$this->colsNum.",".$this->maxCount.",".$this->sizeContenitore."); closeLookUp();");
			//$myButton->setTarget("WINDOW");
			$myButton->setGateway("ARTICOLI");
			$html .= $myButton->getHtml();
		}
		
		if($this->openImage===true) {
			if(count($this->imgArray) == 0) {
				$html .= "<script>
							var bottone2 = jQuery('#".$this->getId()."_ALL_IMAGE');
							bottone2.css('display', 'none');
							//var bottone2 = parent.jQuery('#".$this->getId()."_ALL_IMAGE');
							//console.log(bottone2);
						</script>";
			}
			else {
				$html .= "<script>
							var bottone2 = jQuery('#".$this->getId()."_ALL_IMAGE');
							bottone2.css('display', 'inline');
							//var bottone2 = parent.jQuery('#".$this->getId()."_ALL_IMAGE');
							//console.log(bottone2);
						</script>";
			}
		}
		 
		return $html;
	}

	/**
	 * Crea l'immagine con dimensioni indicate (se non già creata da un processo precedente)
	 *
	 * @param string $imagePath	: percorso completo dell'immagine
	 * @param string $filename	: nome del file
	 * @param string $new_w		: nuova larghezza dell'immagine
	 * @param string $new_h		: nuova altezza dell'immagine
	 * 
	 * @return string
	 */
	function createthumb($imagePath, $filename, $new_w, $new_h) {
		global $appBase, $root_path;

		if ($new_h == "")
			$new_h = $new_w;
		if ($new_w == "")
			$new_w = $new_h;
		
		$doc_root = $_SERVER ['DOCUMENT_ROOT'];
		if(substr($_SERVER ['DOCUMENT_ROOT'], -1)=="/")
			$doc_root = substr($_SERVER ['DOCUMENT_ROOT'], 0, -1);
//		echo "SERVER DOCUMENT_ROOT: ".$_SERVER ['DOCUMENT_ROOT']."<br>";
//		echo "DOC_ROOT: $doc_root<br>";

//		$saveFile = $_SERVER ['DOCUMENT_ROOT'] . $imagePath . $new_w . "_" . $new_h . "_" . $filename;
		$saveFile = $doc_root . $imagePath . $new_w . "_" . $new_h . "_" . $filename;
//		echo "IMAGE PATH: $imagePath - FILENAME: $filename<br>";
//		echo "THUMB: $saveFile<br>";

		if (! file_exists ( $saveFile )) {		
//			$name = $_SERVER ['DOCUMENT_ROOT'] . $imagePath . $filename;
			$name = $doc_root . $imagePath . $filename;
//			echo "NAME: $name<br>";
			// LZ Patch per alzare il limite di memoria, fatal error in VEGA
			ini_set("memory_limit","400M");				
			$system = explode ( '.', $name );
			if (preg_match ( '/jpg|jpeg|JPG|JPEG/', $system [1] )) {
				$src_img = imagecreatefromjpeg ( $name );
			} else if (preg_match ( '/png|PNG/', $system [1] )) {
				$src_img = imagecreatefrompng ( $name );
			} else if (preg_match ( '/gif|GIF/', $system [1] )) {
				$src_img = imagecreatefromgif ( $name );
			} else if (preg_match ( '/bmp|BMP/', $system [1] )) {
				$src_img = imagecreatefromwbmp ( $name );
			}
				
			$old_x = imageSX ( $src_img );
			$old_y = imageSY ( $src_img );
				
			if ($old_x > $old_y) {
				$thumb_w = $new_w;
				$thumb_h = $old_y * ($new_h / $old_x);
			} else if ($old_x < $old_y) {
				$thumb_w = $old_x * ($new_w / $old_y);
				$thumb_h = $new_h;
			} else if ($old_x == $old_y) {
				$thumb_w = $new_w;
				$thumb_h = $new_h;
			}
				
			$dst_img = ImageCreateTrueColor ( $thumb_w, $thumb_h );
			imagecopyresampled ( $dst_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y );
				
			$imageFormat = "";
			if (preg_match ( '/jpg|jpeg|JPG|JPEG/', $system [1] )) {
				imagejpeg ( $dst_img, $saveFile );
				$imageFormat = "jpg";
			} else if (preg_match ( '/png|PNG/', $system [1] )) {
				imagepng ( $dst_img, $saveFile );
				$imageFormat = "png";
			} else if (preg_match ( '/gif|GIF/', $system [1] )) {
				imagegif ( $dst_img, $saveFile );
				$imageFormat = "gif";
			} else if (preg_match ( '/bmp|BMP/', $system [1] )) {
				imagewbmp ( $dst_img, $saveFile );
				$imageFormat = "bmp";
			}
				
			imagedestroy ( $dst_img );
			imagedestroy ( $src_img );
/*				
			// ***************************
			// CONTROLLO CMYK per jpg
			// ***************************
			if ($imageFormat == "jpg"){
				$i = new Imagick($saveFile);
				$cs = $i->getImageColorspace();
				if ($cs == Imagick::COLORSPACE_CMYK) {
					$i->setImageColorspace(Imagick::COLORSPACE_SRGB);
					$i->setImageFormat('jpeg');
					$cs = $i->getImageColorspace();
					if ($cs != Imagick::COLORSPACE_CMYK) {
						$i->writeImage($saveFile);
					}
					$i->clear();
					$i->destroy();
					$i = null;
				}
			}
			// ***************************
*/		}
		return $new_w . "_" . $new_h . "_" . $filename;
	}

	/**
	 * Visualizzazione della barra dei bottoni
	 * 
	 */
	public function dispose(){
		echo $this->getHtml();
	}
	
}

?>