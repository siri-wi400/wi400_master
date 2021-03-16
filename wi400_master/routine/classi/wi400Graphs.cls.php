<?php 

/**
 * @name wi400Graphs 
 * @desc Classe per la creazione di grafici di stato
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author Valeria Porrazzo
 * @version 1.00 26/05/2009
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400Graphs {
	
	private $value;
	private $title;
	
	private $width;
	private $height;
	
	private $color = array();
	
	private $fontSize;
	private $fontType;
	
	private $more;
	private $less;

	/**
	 * Costruttore della classe
	 *
	 * @param unknown_type $value	: valore da riportare nel grafico
	 */
	public function __construct($value=null, $title=null) {
		$this->value = $value;
		$this->title = $title;
	}
	
	/**
	 * Distruttore della classe
	 *
	 */
	public function __destruct() {
 
	}
	
	/**
	 * Impostazione della dimensione del grafico
	 *
	 * @param integer $width	: larghezza
	 * @param integer $height	: altezza
	 */
	public function setSize($width, $height) {
		$this->width = $width;
		$this->height = $height;
	}
	
	/**
	 * Impostazione dei colori da utilizzare nel grafico
	 *
	 * @param string $type	: tipo di utilizzo del colore
	 * @param string $color	: codice del colore
	 */
	public function setColor($type, $color) {
		$this->color[$type] = $color;
	}
	
	/**
	 * Impostazione del font da utilizzare
	 *
	 * @param integer $size	: dimensione del font
	 * @param string $color	: colore del font
	 * @param string $type	: tipo di font
	 */
	public function setFont($size, $color, $type='verdana') {
		$this->fontSize = $size;
		$this->color['font'] = $color;
		$this->fontType = $type;
	}
	
	public function setMoreLess($more="", $less="") {
		$this->more = $more;
		$this->less = $less;
	}
	
	/**
	 * Barra percentuale
	 *
	 * @return 	Ritorna il codice del grafico
	 */
	public function percentage_bar() {
//		$perc = round($this->value,2);
		$perc = $this->value;
//		$perc = wi400_format_DOUBLE_2($this->value);

//		echo "VALUE:".$this->value."<br>";
		
		if($this->more!="") {
			if(change_num_format($this->value)>$this->more) {
				$this->value = $this->more;
			}
		}
//		echo "MORE:".$this->more."<br>";
		
		if($this->less!="") {
			if(change_num_format($this->value)<$this->less) {
				$this->value = $this->less;
			}
		}
//		echo "LESS:".$this->less."<br>";		
		
		$dim = $this->width/100;
		$perc_val = $this->value;
//		$perc_dim = $perc_val * $dim;
//		echo "VAL:$perc_val"."_DIM:$perc_dim<br>";
/*		
		$perc_val = str_replace(".","",$perc_val);
		$perc_val = str_replace(",",".",$perc_val);
		settype($perc_val, "float");
*/
		$perc_val = change_num_format($perc_val);
		$perc_dim = $perc_val * $dim;
//		echo "NEW_VAL:$perc_val"."_NEW_DIM:$perc_dim<br>";

		$graph = "";
		
		if($this->value==0) {
			$graph .= "<table bgcolor='".$this->color['background']."' border='1' width='".$this->width."' height='".$this->height."' cellspacing='0'>";
			$graph .= "<tr><td align='center'>";
			$graph .= "<font face='".$this->fontType."' size='".$this->fontSize."' color='".$this->color['font']."'>".$perc."%</font>";
			$graph .= "</td></tr>";
			$graph .="</table>";
		}
		else if($this->value==100) {
			$graph .= "<table bgcolor='".$this->color['filler']."' border='1' width='".$this->width."' height='".$this->height."' cellspacing='0'>";
			$graph .= "<tr><td align='center'>";
			$graph .= "<font face='".$this->fontType."' size='".$this->fontSize."' color='".$this->color['font']."'>".$perc."%</font>";
			$graph .= "</td></tr>";
			$graph .="</table>";
		}
		else if($this->value>100) {
			$graph .= "<table bgcolor='".$this->color['background']."' border='1' width='".$perc_dim."' height='".$this->height."' cellspacing='0'>";
			$graph .= "<tr>";
			$graph .= "<td bgcolor='".$this->color['filler']."' border='0' width='".$this->width."' height='".$this->height."' align='center'>";
			$graph .= "<font face='".$this->fontType."' size='".$this->fontSize."' color='".$this->color['font']."'>".$perc."%</font>";
			$graph .= "</td><td bgcolor='".$this->color['plus']."' border='0' width='".($perc_dim-$this->width)."' height='".$this->height."' align='center'>";
			$graph .= "</td></tr>";
			$graph .="</table>";
		}
		else if($this->value<0) {
			$dim_tot = $this->width-$perc_dim;
			
			$graph .= "<table bgcolor='".$this->color['background']."' border='1' width='".$dim_tot."' height='".$this->height."' cellspacing='0'>";
			$graph .= "<tr>";
			$graph .= "<td bgcolor='".$this->color['minus']."' border='0' width='".(-$perc_dim)."' height='".$this->height."' align='center'>";
			$graph .= "</td><td bgcolor='".$this->color['background']."' border='0' width='".$this->width."' height='".$this->height."' align='center'>";
			$graph .= "<font face='".$this->fontType."' size='".$this->fontSize."' color='".$this->color['font']."'>".$perc."%</font>";
			$graph .= "</td></tr>";
			$graph .="</table>";
		}
		else if($this->value>0 && $this->value<100) {
			$graph .= "<table bgcolor='".$this->color['background']."' border='1' width='".$this->width."' height='".$this->height."' cellspacing='0'>";
			$graph .= "<tr>";
			$graph .= "<td bgcolor='".$this->color['filler']."' border='0' width='".$perc_dim."' height='".$this->height."' align='center'>";
				if($perc_dim>($this->width-$perc_dim))
					$graph .= "<font face='".$this->fontType."' size='".$this->fontSize."' color='".$this->color['font']."'>".$perc."%</font>";
			$graph .= "</td><td bgcolor='".$this->color['background']."' border='0' width='".($this->width-$perc_dim)."' height='".$this->height."' align='center'>";
				if($perc_dim<=($this->width-$perc_dim))
						$graph .= "<font face='".$this->fontType."' size='".$this->fontSize."' color='".$this->color['font']."'>".$perc."%</font>";
					else
						$graph .= "<font face='".$this->fontType."' size='".$this->fontSize."'>&nbsp</font>";
			$graph .= "</td></tr>";
			$graph .="</table>";
		}
		
		return $graph;
	}
	
	/**
	 * Impostazione e creazione di una barra percentuale stantdard
	 *
	 * @param numeric $perc	: valore percentuale da rappresentare
	 * @param string $title	: titolo della barra
	 * @param integer $w	: lunghezza della barra
	 * @param integer $h	: altezza della barra
	 * @param integer $font	: dimensione del font da utilizzare per scrivere il label con l'indicazione 
	 * 						della percentuale rappresentata
	 * 
	 * @return Ritorna il codice html della barra percentuale
	 */
	public static function perc_bar($perc=0, $title=null, $w=100, $h=10, $font=1) {
/*		
		$grafico = new wi400Graphs($perc, $title);
	
		$grafico->setSize($w,$h);
		
		$grafico->setFont($font, "black");
		
		$grafico->setColor("background", "Lavender");
		$grafico->setColor("filler", "CornflowerBlue");
		$grafico->setColor("plus", "LimeGreen");
		$grafico->setColor("minus", "Red");
		$grafico->setColor("border", "black");
*/
		$grafico = wi400Graphs::set_perc_bar($perc, $title, $w, $h, $font);
		
		return $grafico->percentage_bar();
	}
	
	public static function perc_bar_limited($perc=0, $more="", $less="", $title=null, $w=100, $h=10, $font=1) {
		$grafico = wi400Graphs::set_perc_bar($perc, $title, $w, $h, $font);
		
		$grafico->setMoreLess($more, $less);
		
		return $grafico->percentage_bar();
	}
	
	public static function set_perc_bar($perc=0, $title=null, $w=100, $h=10, $font=1) {
		$grafico = new wi400Graphs($perc, $title);
		
		$grafico->setSize($w,$h);
		
		$grafico->setFont($font, "black");
		
		$grafico->setColor("background", "Lavender");
		$grafico->setColor("filler", "CornflowerBlue");
		$grafico->setColor("plus", "LimeGreen");
		$grafico->setColor("minus", "Red");
		$grafico->setColor("border", "black");
		
		return $grafico;
	}
	
}

?>