<?php

/**
 * @name wi400ZoomPan 
 * @desc Classe per eseguire lo zoom e il pan di un'immagine
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author Valeria Porrazzo
 * @version 1.01 03/11/2009
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400ZoomPan {
	
	private $fileName;
	
	private $img_w;
	private $img_h;
	
	private $idFrame;
	private $frm_w;
	private $frm_h;
	
	/**
	 * Costruttore della classe
	 *
	 * @param string $fileName	: nome del file di cui fare lo zoom/pan
	 * @param string $idFrame	: nome del frame in cui inserire il file di cui fare lo zoom/pan
	 */
	public function __construct($fileName, $idFrame){
		$this->fileName = $fileName;
		$this->idFrame = $idFrame; 
	}
	
	public function __destruct() {
		
	}
	
	/**
	 * Impostazione delle dimensioni dell'immagine
	 *
	 * @param integer $w	: larghezza
	 * @param integer $h	: altezza
	 */
	public function setImageDim($w, $h) {
		$this->img_w = $w;
		$this->img_h = $h;
	}
	
	/**
	 * Impostazione delle dimensioni del frame in cui inserire l'immagine
	 *
	 * @param unknown_type $w
	 * @param unknown_type $h
	 */
	public function setFrameDim($w, $h) {
		$this->frm_w = $w;
		$this->frm_h = $h;
	}
	
	/**
	 * Visualizzazione dell'immagine nel browser all'interno di un frame con tools per lo zoom e il pan dell'immagine
	 *
	 */
	public function dispose(){
    	global $appBase;
    	
		echo "<script type=\"text/JavaScript\" src=\"".$appBase."modules/utilities/js/zoom_pan.js\"></script>";


		echo "<a href=\"#\" onmousedown=\"callZoom(".$this->img_w.",".$this->img_h.",'myimage','in','".$this->idFrame."')\" onmouseup=\"callClearzoom('".$this->idFrame."')\"><img src=\"".$appBase."/modules/tracciabilita/zoom_in.png\" id=\"zoom_in\" border='0'/></a>";
		echo "<a href=\"#\" onmousedown=\"callZoom(".$this->img_w.",".$this->img_h.",'myimage','restore','".$this->idFrame."')\"><img src=\"".$appBase."/modules/tracciabilita/reload.gif\" id=\"restore\" width='18' height='18' border='0'/></a>";
		echo "<a href=\"#\" onmousedown=\"callZoom(".$this->img_w.",".$this->img_h.",'myimage','out','".$this->idFrame."')\" onmouseup=\"callClearzoom('".$this->idFrame."')\"><img src=\"".$appBase."/modules/tracciabilita/zoom_out.png\" id=\"zoom_out\" border='0'/></a>";
		
//		echo "<br><br>";
		
		echo "<table width='".$this->frm_w."' height='".$this->frm_h."' cellpadding=0 cellspacing=0 ><tr><td align='center' style='background=#FFFFFF;border: 1px solid #CCCCCC'>";
		echo "<iframe src='".$appBase."index.php?DECORATION=clean&t=ZOOMIMG&FILE_NAME=".$this->fileName."&IMGW=".$this->img_w."&IMGH=".$this->img_h."' nam='".$this->idFrame."' id='".$this->idFrame."' width='".$this->frm_w."' height='".$this->frm_h."' frameborder='2' scrolling='no' align='middle'></iframe>";
		echo "</td></tr></table>";
		
//		echo "<br>";
		
		echo "<a href=\"#\" onmousedown=\"frm='".$this->idFrame."'; val=-10; run(); return false;\" onmouseup=\"val=0; stop(); return false;\"><img src=\"".$appBase."/modules/tracciabilita/pan_left.png\" id=\"pan_left\" border='0'/></a>";
		echo "<a href=\"#\" onmousedown=\"frm='".$this->idFrame."'; val1=-10; run(); return false;\" onmouseup=\"val1=0; stop(); return false;\" ><img src=\"".$appBase."/modules/tracciabilita/pan_up.png\" id=\"pan_up\" border='0'/></a>";
		echo "<a href=\"#\" onmousedown=\"frm='".$this->idFrame."'; val1=10; run(); return false;\" onmouseup=\"val1=0; stop(); return false;\"><img src=\"".$appBase."/modules/tracciabilita/pan_down.png\" id=\"pan_down\" border='0'/></a>";
		echo "<a href=\"#\" onmousedown=\"frm='".$this->idFrame."'; val=10; run(); return false;\" onmouseup=\"val=0; stop(); return false;\"><img src=\"".$appBase."/modules/tracciabilita/pan_right.png\" id=\"pan_right\" border='0'/></a>";
   }
	
}

?>