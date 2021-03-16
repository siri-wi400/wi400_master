<?php 

/**
 * @name wi400Gantt_chart Classe per la creazione di grafici di stato
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author Valeria Porrazzo
 * @version 1.00 19/08/2009
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400Gantt_chart {
	
	private $image;
	
	private $elements = array();
	
	private $image_width;
	private $image_height;
	
	private $colors = array();
	private $heights = array();
	private $widths = array();
	private $fonts = array();
	
	private $w_hour;
	private $spacing;
	
	private $periodo = array();
	private $days;
	private $num_h;
	
	private $grid = array();
	
	/**
	 * Costruttore
	 *
	 */
	public function __construct() {
		
	}
	
	/**
	 * Distruttore
	 *
	 */
	public function __destruct() {
 
	}
	
	public function setImageDim($w, $h) {
		$this->image_width = $w;
		$this->image_height = $h;
	}
	
	public function getImageWidth() {
		return $this->image_width;
	}
	
	public function getImageHeight() {
		return $this->image_height;
	}
	
	public function setColor($type, $color) {
		$this->colors[$type] = $color;
	}
	
	public function setHeight($type, $h) {
		$this->heights[$type] = $h;
	}
	
	public function setWidth($type, $w) {
		$this->widths[$type] = $w;
	}
	
	public function setFont($type, $font, $h) {
		$this->fonts[$type]['font'] = $font;
		$this->fonts[$type]['height'] = $h;
	}
	
	public function setWidth_hour($w) {
		$this->w_hour = $w;
	}
	
	public function setSpacing($spacing) {
		$this->spacing = $spacing;
	}
	
	public function add_element($id, $type, $start, $end) {
		$date_ini = explode("-", substr($start,0,10));
		$time_ini = explode(".", substr($start,11));
		$date_fin = explode("-", substr($end,0,10));
		$time_fin = explode(".", substr($end,11));
		
		$this->elements[] = array(
			'id' => $id,
			'type' => $type,
			'start-date' => implode("", $date_ini),
			'start-time' => implode("", $time_ini),
			'end-date' => implode("", $date_fin),
			'end-time' => implode("", $time_fin)
		);
	}
	
	public function outputGantt($file=null,$quality=90) {
//		echo "ELEMENTS: "; print_r($this->elements); echo "<br><br>";
	
		$this->drawGantt();
		
		if(!empty($file)){
			imagejpeg($this->image,$file,$quality);
		}
		else {
			header("Content-type: image/jpeg");
			imagejpeg($this->image,"",$quality);
		}
	}
	
	private function drawGantt() {
		$this->createCanvas();
		$this->drawGrid();
		$this->drawElements();
	}
	
	private function createCanvas() {
		if(empty($this->width)) {
			$this->setRange();
		}
//		echo "CANVAS WIDTH: ".$this->image_width."<br>";
//		echo "CANVAS HEIGHT: ".$this->image_height."<br><br>";
	
		$this->image = imagecreatetruecolor($this->image_width, $this->image_height);
		
		// allocate colors
		$this->colorAllocate();
		// set background color
		imagefill($this->image,0,0,$this->colors["background"]);
	}
	
	private function setRange() {
		$this->getPeriodo();
		
		$this->days = ((strtotime($this->periodo['end-date']) - strtotime($this->periodo['start-date']))/86400)+1;
//		echo "DAYS: $this->days<br>";
		
		$num_h = 0;
/*		
		if($this->days>2)
			$num_h = 24*($this->days-2);
		// ore primo giorno del range
		$hour = substr($this->periodo['start-time'],0,2);
		for($h=$hour; $h<=24; $h++) {
			$num_h++;
		}
		// ore ultimo giorno del range
		$hour = substr($this->periodo['end-time'],0,2);
		for($h=0; $h<=$hour; $h++) {
			$num_h++;
		}
*/
		
		// ore primo giorno del range
		$hour = substr($this->periodo['start-time'],0,2);
		for($h=$hour; $h<=24; $h++) {
			$num_h++;
		}
//		echo "ORE INI: $num_h<br>";
		if($this->days>1) {
			// ore ultimo giorno del range
			$hour = substr($this->periodo['end-time'],0,2);
			for($h=0; $h<=$hour; $h++) {
				$num_h++;
			}
		}
//		echo "ORE FIN: $num_h<br>";
		if($this->days>2) {
			$num_h += 24*($this->days-2);
		}

//		echo "ORE: $num_h<br><br>";
		
		$this->num_h = $num_h;
		$this->image_width = ($this->w_hour * $this->num_h)+$this->widths['label']+($this->widths['time']*2);
	}
	
	private function getPeriodo() {
		$isStartFirst = true;
		$isEndFirst = true;
		$height = 0;
		
		$periodo = array();
		
		foreach($this->elements as $el) {
//			echo "EL PER: "; print_r($el); echo"<br>";

			if(!empty($el['start-date']) && (substr($el['start-date'],0,4)!="1985")) {
				if($isStartFirst===true) {
//					echo "HERE 1<br>";
					// start range
					$periodo['start-date'] = $el['start-date'];
					$periodo['start-time'] = $el['start-time'];
					$isStartFirst = false;
				}
				else {
//					echo "HERE 2<br>";
					if($el['start-date']<$periodo['start-date']) {
						$periodo['start-date'] = $el['start-date'];
						$periodo['start-time'] = $el['start-time'];
					}
					else if($el['start-date']==$periodo['start-date']) {
						if($el['start-time']<$periodo['start-time']) {
							$periodo['start-time'] = $el['start-time'];
						}
					}
				}
			}

			if(!empty($el['end-date']) && (substr($el['end-date'],0,4)!="2999")) {
				if($isEndFirst===true) {
					// end range
					$periodo['end-date'] = $el['end-date'];
					$periodo['end-time'] = $el['end-time'];
					$isEndFirst = false;
				}
				else {
					if($el['end-date']>$periodo['end-date']) {
						$periodo['end-date'] = $el['end-date'];
						$periodo['end-time'] = $el['end-time'];
					}
					else if($el['end-date']==$periodo['end-date']) {
						if($el['end-time']>$periodo['end-time']) {
							$periodo['end-time'] = $el['end-time'];
						}
					}
				}
			}
			
//			echo "S: ".$periodo['start-date']." - ".$periodo['start-time']."<br>";
//			echo "E: ".$periodo['end-date']." - ".$periodo['end-time']."<br><br>";	
			// calcolo l'altezza dell'immagine già che si stanno scorrendo gli elementi
			$height += $this->heights[$el['type']] + $this->spacing;
		}
		
		$this->periodo['start-date'] = $periodo['start-date'];
		$this->periodo['start-time'] = $periodo['start-time'];
		$this->periodo['end-date'] = $periodo['end-date'];
		$this->periodo['end-time'] = $periodo['end-time'];
//		echo "START: ".$this->periodo['start-date']." - ".$this->periodo['start-time']."<br>";
//		echo "END: ".$this->periodo['end-date']." - ".$this->periodo['end-time']."<br><br>";
	
		if(empty($this->image_height)) {
			$this->image_height = $height + (($this->heights['days-box'] + $this->heights['hours-box'])*2);
		}
	}
	
	private function drawGrid() {
		$i=0;
		$start = $this->widths['label'] + $this->widths['time'];
		$x1 = ($i * $this->w_hour) + $start;
		$y1 = $this->heights['days-box'] + $this->heights['hours-box'];
		$y2 = $this->image_height - ($this->heights['days-box'] + $this->heights['hours-box']);
		
		imagestring($this->image,$this->fonts['days']['height'],$x1,5,dateModelToView($this->periodo['start-date']),$this->colors["font-days"]);
		imagestring($this->image,$this->fonts['days']['height'],$x1,($y2+$this->heights['hours-box']),dateModelToView($this->periodo['start-date']),$this->colors["font-days"]);
		
		$this->grid[$this->periodo['start-date']] = $x1;
		
		// ore primo giorno del range
		$hour = substr($this->periodo['start-time'],0,2);
		for($h=(int)$hour; $h<24; $h++) {
			$x1 = ($i * $this->w_hour) + $start;
			imageline($this->image,$x1,$y1,$x1,$y2,$this->colors["grid-hours"]);
			imagestring($this->image,$this->fonts['days']['height'],$x1,$this->heights['days-box'],$h,$this->colors["font-hours"]);
			imagestring($this->image,$this->fonts['days']['height'],$x1,($y2+5),$h,$this->colors["font-hours"]);
			$i++;
		}
		
		$giorno = substr($this->periodo['start-date'],6);
		$mese = substr($this->periodo['start-date'],4,2);
		$anno = substr($this->periodo['start-date'],0,4);
		
		if($this->days>1) {
			// giorni del range			
			for($day=2; $day<$this->days; $day++) {
				switch($mese) {
					case 4:
					case 6:
					case 9:
					case 11: 
						if($giorno==30) {
							$giorno = 1;
							$mese++;
						}
						else {
							$giorno++;
						}
						break;
					case 2: 
						if((($anno%4)==0 && $giorno==29) || (($anno%4)!=0) && $giorno==28) {
							$giorno = 1;
							$mese++;
						}
						else {
							$giorno++;
						}
						break;
					case 12:
						if($giorno==31) {
							$giorno = 1;
							$mese = 1;
							$anno++;
						}
						else {
							$giorno++;
						}
						break;
					default:
						if($giorno==31) {
							$giorno = 1;
							$mese++;
						}
						else {
							$giorno++;
						}
				}
				
				$date = dateString($giorno, $mese, $anno);
	
				// ore ultimo giorno del range
				for($h=0; $h<24; $h++) {
					$x1 = ($i * $this->w_hour) + $start;
					if($h==0) {
						imageline($this->image,$x1,$y1,$x1,$y2,$this->colors["grid-days"]);
						$this->grid[$date] = $x1;
						imagestring($this->image,$this->fonts['days']['height'],($x1+5),5,dateFormat($giorno,$mese,$anno),$this->colors["font-days"]);
						imagestring($this->image,$this->fonts['days']['height'],($x1+5),($y2+$this->heights['hours-box']),dateFormat($giorno,$mese,$anno),$this->colors["font-days"]);
					}
					else {
						imageline($this->image,$x1,$y1,$x1,$y2,$this->colors["grid-hours"]);
					}
					imagestring($this->image,$this->fonts['days']['height'],$x1,$this->heights['days-box'],$h,$this->colors["font-hours"]);
					imagestring($this->image,$this->fonts['days']['height'],$x1,($y2+5),$h,$this->colors["font-hours"]);
					$i++;
				}
			}
	
			// ore ultimo giorno del range
			$hour = substr($this->periodo['end-time'],0,2);
			for($h=0; $h<=$hour+1; $h++) {
				$x1 = ($i * $this->w_hour) + $start;
				if($h==0) {
					imageline($this->image,$x1,$y1,$x1,$y2,$this->colors["grid-days"]);
					$this->grid[$this->periodo['end-date']] = $x1;
					imagestring($this->image,$this->fonts['days']['height'],$x1,5,dateModelToView($this->periodo['end-date']),$this->colors["font-days"]);
					imagestring($this->image,$this->fonts['days']['height'],$x1+5,($y2+$this->heights['hours-box']),dateModelToView($this->periodo['end-date']),$this->colors["font-days"]);
				}
				else {
					imageline($this->image,$x1,$y1,$x1,$y2,$this->colors["grid-hours"]);
				}		
				imagestring($this->image,$this->fonts['days']['height'],$x1,$this->heights['days-box'],$h,$this->colors["font-hours"]);
				imagestring($this->image,$this->fonts['days']['height'],$x1,($y2+5),$h,$this->colors["font-hours"]);
				$i++;
			}
		}
		else {
			$x1 = ($i * $this->w_hour) + $start;
			imageline($this->image,$x1,$y1,$x1,$y2,$this->colors["grid-days"]);
			imagestring($this->image,$this->fonts['days']['height'],$x1,$this->heights['days-box'],0,$this->colors["font-hours"]);
			imagestring($this->image,$this->fonts['days']['height'],$x1,($y2+5),0,$this->colors["font-hours"]);
		}
		$this->grid['end-grid'] = $x1;
	}
	
	private function colorAllocate() {
		foreach($this->colors as $k=>$v){
			list($r,$g,$b) = sscanf($v,"%2x%2x%2x");
			$this->colors[$k] = imagecolorallocate($this->image,$r,$g,$b);
		}
	}
	
	private function drawElements() {
		$Y_start = $this->heights['days-box'] + $this->heights['hours-box'];
		
//		echo "GRID: "; print_r($this->grid); echo "<br><br>";
//		$i = 1;
		foreach($this->elements as $el) {	
//			echo "EL $i: "; print_r($el); echo "<br>";
//			$i++;
			imagestring($this->image,$this->fonts['label']['height'],($this->w_hour/2),$Y_start,$el['id'],$this->colors[$el['type']]);
			
			if(!empty($el['start-date']) || !empty($el['end-date'])) {
				$pos = array();	
				$pos = $this->calDimEl($el);
				
				$x1 = $pos['start'];
				$x2 = $pos['end'];
//				echo "START: $x1 - END: $x2<br>";			
				$y2 = $Y_start + $this->heights[$el['type']];
				
				$h1 = $this->printTime($el['start-time']);
				$h2 = $this->printTime($el['end-time']);
				
				if($el['start-date']==$el['end-date'] && strncmp($el['start-time'],$el['end-time'],4)==0) {
					imagefilledellipse($this->image,$x1,$Y_start,7,7,$this->colors[$el['type']]);
				}
				else {
					imagefilledrectangle($this->image,$x1,$Y_start,$x2,$y2,$this->colors[$el['type']]);
				}
				
				if(!empty($el['start-date']) && (substr($el['start-date'],0,4)!="1985")) {
					if(($el['start-date']!=$el['end-date']) && strncmp($el['start-time'],$el['end-time'],4)!=0) {
						imagestring($this->image,$this->fonts['days']['height'],($x1 - $this->widths['time']),$Y_start,$h1,$this->colors["font-hours"]);
					}
				}
				
				if((!empty($el['end-date'])) && (substr($el['end-date'],0,4)!="2999")) {
					imagestring($this->image,$this->fonts['days']['height'],($x2+2),$Y_start,$h2,$this->colors["font-hours"]);
				}
				
				$Y_start += $this->heights[$el['type']] + $this->spacing;
			}
			else if(empty($el['start-date']) && empty($el['end-date'])) {
				$Y_start += $this->heights[$el['type']] + $this->spacing;
			}
		}
	}
	
	private function calDimEl($el) {
		$pos = array();
		
		if(!empty($el['start-date']) && (substr($el['start-date'],0,4)!="1985")) {
			$pos['start'] = $this->grid[$el['start-date']];
//			echo "POS INI".$el['start-date'].": ".$pos['start']." - ";
			if($el['start-date']!=$this->periodo['start-date']) {
				$hour = substr($el['start-time'],0,2);
				$pos['start'] += ($this->w_hour * $hour);
			}
			else {
				if($el['start-time']!=$this->periodo['start-time']) {
					$hour = substr($el['start-time'],0,2);
					$pos['start'] += ($this->w_hour * abs($hour - substr($this->periodo['start-time'],0,2)));	
//					echo "HOUR: ".abs(substr($this->periodo['start-time'],0,2) - $hour)."<br>";
				}
			}
			$min = substr($el['start-time'],2,2);
			$pos['start'] += (($this->w_hour/60)*$min);
//			echo $pos['start']."<br>";
		}
		else {
			$pos['start'] = $this->grid[$this->periodo['start-date']];
		}

		if(!empty($el['end-date']) && substr($el['end-date'],0,4)!="2999") {
			$pos['end'] = $this->grid[$el['end-date']];
//			echo "POS FIN ".$el['end-date'].": ".$pos['end']." - ";
			if($el['end-date']!=$this->periodo['start-date']) {
				$hour = substr($el['end-time'],0,2);
				$pos['end'] += $this->w_hour * $hour;
				
				$min = substr($el['end-time'],2,2);
				$pos['end'] += (($this->w_hour/60)*$min);
			}
			else {
				if(strncmp($el['end-time'],$this->periodo['start-time'],4)!=0) {
					$hour = substr($el['end-time'],0,2);
					$pos['end'] += ($this->w_hour * abs($hour - substr($this->periodo['start-time'],0,2)));
//					echo "HOUR: ".abs(substr($this->periodo['start-time'],0,2) - $hour)."<br>";
					$min = substr($el['end-time'],2,2);
					$pos['end'] += (($this->w_hour/60)*$min);
				}
				else {
					$pos['end'] = $pos['start'];
				}
			}
//			echo $pos['end']."<br>";
		}
		else {
			$pos['end'] = $this->grid['end-grid'];
		}
		
		return $pos;
	}
	
	private function printTime($time) {
		return substr($time,0,2).":".substr($time,2,2);
	}
	
}

?>