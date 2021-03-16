<?php
/**
 * @name wi400AS400Display
 * @desc Gestione della sessione 5250
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author
 * @version 0.02B 01/04/2018
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400AS400Display {
	private $id_display="";
	private $idExtraction="";
	private $record = array();
	private $fields = Null;
	private $streamObj=Null;
	private $commands=Null;
	private $message="";
	private $block=False;
	private $messageLine=24;
	private $forceWindow=False;
	private $saved=Null;
	private $readMDT=False;
	private $readALL=False;
	private $cursorPosition=Null;
	private $info=Null;
	private $isWindow = False;
	private $posWindow = array('row' => 1, 'col' => 1);
	private $disposeContainer = false;
	private $disposeFunctionButton = false;
	private $display = array(
		//TODO fix input size chrome da spacca firefox (css font-size: 13px in .inputtext)
		//'Chrome' => array('width' => array(80 => 7.14, 132 => 5.2), 'height' => array(17), 'size' => array(80 => 7.154, 132 => 6.14)), 
		//'Chrome' => array('width' => array(80 => 7.14, 132 => 5.2), 'height' => array(17), 'size' => array(80 => 6.054, 132 => 5.2)),
		'Chrome' => array('width' => array(80 => 7.14, 132 => 5.2), 'height' => array(17), 'size' => array(80 => 6.054, 132 => 6.14)),
		'Firefox' => array('width' => array(80 => 7.14, 132 => 5), 'height' => array(17), 'size' => array(80 => 'auto', 132 => 'auto')),
		'IE' => array('width' => array(80 => 7.14, 132 => 5.2), 'height' => array(17), 'size' => array(80 => 6.14, 132 => 6.14)),
		'default' => array('width' => array(80 => 7.14, 132 => 5.2), 'height' => array(17), 'size' => array(80 => 6.14, 132 => 6.14))
	);
	private $griglia = array();
	private $resolution="24x80";
	public function __construct($id_display, $clean=False) {
		global $db;
		$this->id_display=$id_display;
		if ($clean==True) {
			$this->clearDisplayFile();
		}
	}
	public function clearScreen() {
		$this->fields=Null;
	}
	public function saveScreen() {
		$this->saved = $this->fields;
	}
	public function setCursorPosition($x, $y) {
		$this->cursorPosition = array($x, $y);
	}
	public function cleanCursorPosition() {
		$this->cursorPosition = Null;
	}
	public function getCursorPosition() {
		return $this->cursorPosition;
	}
	public function restoreScreen() {
		$this->fields = $this->saved;
		$this->forceWindow=False;
	}
	public function getResolutionRow() {
		$res = explode("X", strtoupper($this->resolution));
		return $res['0'];
	}
	public function getResolutionCol() {
		$res = explode("X", strtoupper($this->resolution));
		return $res['1'];
	}
	public function setFields($fields) {
		$this->fields =$fields;
	}
	public function setMessage($message) {
		$this->message=$message;
	}
	public function getMessage() {
		return $this->message;
	}
	public function setBlock($block) {
		$this->block = $block;
	}
	public function getBlock() {
		return $this->block;
	}
	public function setInfo($info) {
		$this->info = $info;
	}
	public function getInfo() {
		return $this->info;
	}
	public function getFields($field="") {
		if ($field!="") {
			return $this->fields[$field];
		} else {
			return $this->fields;
		}
	}
	/**
	 * Setta l'id dell'estrazione effettuata
	 *
	 * @param string/number $id
	 */
	public function setIdExtraction($id) {
		$this->idExtraction = $id;
	}
	/**
	 * Ritorna l'id dell'estrazione effettuata
	 *
	 * @return string/number
	 */
	public function getIdExtraction() {
		return $this->idExtraction;
	}
	
	/**
	 * Abilita o meno la stampa dei bottoni di sistema a video
	 * 
	 * @param bool $val
	 */
	public function setDisposeFunctionButton($val) {
		$this->disposeFunctionButton = $val;
	}
	
	/**
	 * Abilita o meno la stampa dei contenitore 5250 questo per via della chiamata ajax
	 * che non ha bisogno del contenitore ma devi ritornare solo i dati
	 * 
	 * @param bool $val
	 */
	public function setDisposeContainer($val) {
		$this->disposeContainer = $val;
	}
	
	/**
	 * Aggiunge l'html per contenere i dati proveniente dall'ajax 
	 * 
	 * @param string $html
	 * @return string
	 */
	public function addHtmlContainer($html) {
		$display_zoom = "";
		
		$browser = get_browser(null, true);
		$browser = $browser['browser'];
		
		if(isset($_SESSION['TELNET_5250_ZOOM'])) {
			$zoom = $_SESSION['TELNET_5250_ZOOM'];
		}else {
			$zoom = "1.3";
		}
		
		if($browser == "Chrome") {
			$display_zoom = "style='zoom: $zoom;'";
		}else {
			$display_zoom = "style='transform: matrix($zoom, 0, 0, $zoom, 0, 0);'";
		}
		
		//Il div #tableWidth serve per ridimensionare il lookup con l'auto resize
		$containerHtml = '<div id="tableWidth" style="width: 900px;"></div>
						<div style="position: relative">
								<div id="CLIENT_5250_DIV" class="display" '.$display_zoom.'>';
		$containerHtml .= 			$html;
		$containerHtml .= "		</div>
								<div class='cont_block' ".$display_zoom.">
									<span id='x_system'>X SYSTEM</span>
								</div>
								<div class='cont_block2' ".$display_zoom.">
									<span id='x_error'>X II</span>
								</div>
								<div class='cont_block_export' ".$display_zoom.">
									<div class='cont_model_export'>
										<i class='fa fa-spinner fa-pulse'></i>&nbsp;Esportazione lista in corso<br/><br/>
										<span onClick='chiudiEstrazione(false)'>Annulla</span>
									</div>
								</div>
						</div>
						<script>var session_display = '".$this->id_display."';
								var browser = '".$browser."';
						</script>";
		
		return $containerHtml;
	} 
		
	/*public function getFieldsById($id) {
		//return $this->streamObj->getFields();
		foreach ($fields as $key => $field) {
			// Verifico se ci sono campi
			$field=$value->getFields($id);
			if ($field != Null) {
					return $field;	
			}
		}	
		return false;
	}*/
	public function getFieldsById($id) {
		//return $this->streamObj->getFields();
		foreach ($this->fields as $key => $field) {
			// Verifico se ci sono campi
			if ($id==$field->getId()) {
				return $field;
			}
			//$field=$value->getFields($id);
			//if ($field != Null) {
			//		return $field;
			//}
		}
		return false;
	}
	public function getHtmlRightAdjust($value) {
		$html = "";
		switch($value) {
			case '110': $html = "rightAdjust(this, ' ');"; break;
			case '101': $html = "rightAdjust(this, 0);"; break;
		}
			
		return $html;
	}
	public function getHtmlFieldShift($value, $field) {
		$html = "";
		//$this->fieldShift = '101';
		$id = $field->getId();
		switch ($value) {
			case '001': $html = '<script>rules.push("'.$id.'|mask|alphabeticOnly");</script>'; break;
			case '011': $html = '<script>rules.push("'.$id.'|mask|numericOnly");</script>'; break;
			case '101': $html = '<script>rules.push("'.$id.'|mask|123456790");</script>'; break;
			case '111': $html = "<script>rules.push('".$id."|mask|1234567890-')</script>";
			break;
		}
			
		return $html;
	}
	
	public function saveDisplay() {
		global $settings;
		$filename = wi400File::getUserFile("sessioni5250", $this->id_display.".dat");
		file_put_contents($filename, serialize($this));
	}
	public function clearDisplayFile() {
		global $settings;
		$filename = wi400File::getUserFile("sessioni5250", $this->id_display.".dat");
		if (file_exists($filename)) {
			unlink($filename);
		}
	}
	public function setStreamObj($streamObj, $forceWindow=False) {
		$this->streamObj=$streamObj;
		$this->forceWindow=$forceWindow;
	}              
	public function getStreamObj() {
		return $this->streamObj;
	}
	// Eseguo i comandi passati con lo STREAM OBJ
	public function executeCommand() {
		global $firephp;
		
		$comandi = $this->getStreamObj()->getCommands();
		// Reset del display
		$this->setMessage("");
		$this->setBlock(False);
		// Verifico i comandi passati
		if ($comandi!=Null) {
			//showArray($comandi);die();
			$comandRestore = $this->getStreamObj()->getCommandByType("12");
			if(is_array($comandRestore) && count($comandRestore) == 1) {
				$this->restoreScreen();
				
				$index = array_keys($comandRestore);
				//unset($comandi[$index[0]]);
			}
			// Verifico se ho una finestra da visualizzare
			// Erase ORDER
			foreach ($comandi as $key => $comando) {
				if ($comando->getEraseOrder()!==Null) {
					$erase = $comando->getEraseOrder();
					// Cancello tutti i campi a video fino alla posizione se non sono un finestra
					if ($this->forceWindow!=True && $comando->getWindow()!=True) {
						//if ($erase->attribute[0]=="FF" || $erase->attribute[0]=="01") {
						if (in_array("FF", $erase->attribute)) {
							//error_log("ERASE:!!!:/");
							foreach ($this->fields as $key => $value) {
								$myx = $value->getXposition();
								$myy = $value->getYposition();
								//error_log("ERASE:!!!:/ $myx - $myy ".$erase->row." / ".$erase->fromColumn);
								if ($myx<=$erase->row && $myx>=$erase->fromRow) {
									if ($myy<=$erase->column && $myy>=$erase->fromColumn) {
										//error_log("ERASE UNSET:!!!:/ $myx - $myy");
										unset($this->fields[$key]);
									}
								}
							}
						}
					}
					$comando->setEraseORder(Null);
				}
			}
			
			$countSoHeader = 0;
			// Adesso butto fuori i campi a video
			foreach ($comandi as $key => $comando) {
				//error_log("Comando Eseguito:".$comando->getType());
				// Se c'è un comando di cancellazione rimuovo tutte i vecchi campi
				if (in_array($comando->getType(), array("20", "40", "50"))) {
					//echo "<br>CANCELLO TUTTO!!";
					$this->fields=Null;
					continue;
				}
				// Messaggio di errore
				if ($comando->getType()=="21") {
					$x=0;
					$y=0;
					$this->setMessage($comando->getErrorMessage());
					if ($comando->getCursorOrder()!=="") {
						$cursor = $comando->getCursorOrder();
						$x = $cursor->row;
						$y = $cursor->column;
						$this->setCursorPosition($x,$y);
					}
					$this->setBlock(True);
					continue;
				}
				if ($comando->getCursorOrder()!==Null) {
					$cursor = $comando->getCursorOrder();
					$cursor = $comando->getCursorOrder();
					$x = $cursor->row;
					$y = $cursor->column;
					$this->setCursorPosition($x,$y);
				}
				// Salvataggio dello schermo
				if ($comando->getType()=="02") {
					$this->saveScreen();
					continue;
				}
				// Ripristino dello schermo
				if ($comando->getType()=="12") {
					$this->restoreScreen();
					continue;
				}
				// Messaggio di errore Finestra
				if ($comando->getType()=="22") {
					$this->setMessage($comando->getErrorMessage());
					$this->setBlock(True);
					continue;
				}
				
				//Al secondo SOHeader che trovo metto tutte le fields prima protette
				if($comando->getSOHHeader()) {
					$countSoHeader++;
				
					if($countSoHeader == 2) {
						foreach($this->getFields() as $id => $field) {
							$field->setBypass('1');
						}
				
						$countSoHeader = 0;
					}
				}
				
				// Combino gli array dei comandi OLD + TRUE
				//$this->forceWindow=True;
				//echo "PASSO DI QUA!";
				if (count($comando->getFields()) > 0) {
					if ($this->fields!=Null) {
						// Cancello i campi sovrapposti
						if ($this->forceWindow!=True && $comando->getWindow()!=True) {
							$this->compareFields($comando);
						}
						// Come fare .. ...
						//echo "VALUTO I CAMPI!";
						// Simulo la preseza di una finestra aggiungendo una struct window
						if ($this->forceWindow==True) {
							$this->forceWindow = false; //disattivo il flag per non continuare a creare ulteriori window
							$bi = $comando->getBoxInfo();
							$newfield = wi400AS400Func::getWindowField("WIN_1", $bi['ROW'],$bi['COL'],$bi['TL'],$bi['LL']);
							//$newfield = wi400AS400Func::getWindowField("WIN_1", '08','20',10,35);
							$this->addFields(array("0"=>$newfield));
						}
						$this->addFields($comando->getFields());
					} else {
						if ($this->forceWindow==True) {
							$this->forceWindow = false; //disattivo il flag per non continuare a creare ulteriori window
							$bi = $comando->getBoxInfo();
							$newfield = wi400AS400Func::getWindowField("WIN_1", $bi['ROW'],$bi['COL'],$bi['TL'],$bi['LL']);
							$this->addFields(array("0"=>$newfield));
						}
						
						//showArray($comando->getFields());
						$this->addFields($comando->getFields());
						//$this->fields = $comando->getFields();
					}
				}
			}
		}
	}
	public function addFields($fields) {
		foreach ($fields as $key => $value) {
			$this->fields[]=$value;
		}
	}
	public function compareFields($comando) {
		global $firephp;
		//showArray($this->fields);die();
		foreach ($this->fields as $key => $value) {
			//echo "<br>KEY:".$key;
			// Faccio ritornare l'oggetto
			$unset=False;
			$newfield = $comando->getFieldByXY($value->getXposition(), $value->getYposition(), $value->getLength(), True);

			// Cancello la vecchia Field
			if ($newfield!="") {
				// Verifico se per caso è un attributo modificato
				if ($newfield->getLength()==0) {
					$this->fields[$key]->setColour($newfield->getColour());
					$comando->unsetField($newfield->getId());
					//unset($newfield);
				} else {
					// Se la variabile è vuota verifico se per caso la vecchia ha un testo
					if ($newfield->getVacum()==True) {
						$newfield->setText($this->fields[$key]->getText());
						$newfield->setVacum(False);
					}
					// @todo DEVO CANCELLARE TUTTI I CAMPI COLLEGATI SE E UN CAMPO SPLIT!
					unset($this->fields[$key]);
					$unset=True;
				}	
			}
			// Controllo il campo -1 per vedere se è cambiato qualche attributo.
			if ($unset==False) {
				$myx = $value->getXposition();
				$myy = $value->getYposition();
				$myy--;
				if ($myy==0) {
					$myx--;
					$myy=$this->getResolutionCol();
				}
				$newfield = $comando->getFieldByXY($value->getXposition(), ($value->getYposition()-1), $value->getLength(), True, -1);
				// Cancello la vecchia Field
				if ($newfield!="") {
					// Verifico se per caso è un attributo modificato
					if ($newfield->getLength()==0) {
						$this->fields[$key]->setColour($newfield->getColour());
						$comando->unsetField($newfield->getId());
					}
					
				}
			}
			
		}
	}
	
	/**
	 * Setta se il display è una window oppure no
	 * 
	 * @param boolean
	 */
	public function setIsWindow($val) {
		$this->isWindow = $val;
	}
	
	/**
	 * Ritorna se il display è una window oppure no
	 * 
	 * @return string
	 */
	public function getIsWindow() {
		return $this->isWindow;
	}
	
	/**
	 * Ritorna la posizione della window
	 *
	 * @return string
	 */
	public function getPosWindow() {
		return $this->posWindow;
	}
	
	/**
	 * Ritorna la posizione della window in esadecimale
	 *
	 * @return string
	 */
	public function getPosWindowHex() {
		return $this->getPositionToHex($this->posWindow['row']+1, $this->posWindow['col']+2);
	}
	
	
	//Setto la risoluzione dello schermo
	public function setResolution($resolution) {
		//$resolution = "27x132";
		$this->resolution = $resolution;
		
		//Costruisco la griglia
		list($rows, $cols) = explode("x", $this->resolution);
		//echo $rows."__".$cols."_<br/>";
		for($i=0; $i<$rows; $i++) {
			$this->griglia[] = array();
			for($j=0; $j<$cols; $j++) {
				//echo $j."_";
				$this->griglia[$i][$j] = "&nbsp;";
			}
			//echo "<br/>";
		}
	}
	
	//Ritorno la risoluzione dello schermo
	public function getResolution() {
		return $this->resolution;
	}
	
	public function getValoriGriglia() {
		return $this->griglia;
	}
	
	public function stampaValoriGriglia() {
		list($rows, $cols) = explode("x", $this->resolution);
		for($i=0; $i<$rows; $i++) {
			echo "<label riga='$i' style='font-family: monospace;'>";
			for($j=0; $j<$cols; $j++) {
				echo $this->griglia[$i][$j];
			}
			echo "</label><br>";
		}
		
		//showArray($this->griglia[3]);
	}
	
	public function stampaGriglia() {
		list($rows, $cols) = explode("x", $this->resolution);
		
		$html = "<div class='griglia'><div id='segnalino'></div>
					<table>";
		for($i=0; $i<$rows; $i++) {
			$html .= "<tr riga='$i'>";
			for($j=0; $j<$cols; $j++) {
				list($char, $stile) = explode("|", $this->griglia[$i][$j]);
				$html .= "<td class='$stile'>".$char."</td>";
			}
			$html .= "</tr>";
		}
		$html .= "</table></div>";
		
		return $html;
	}
	
	public function display() {
		global $settings, $firephp;
		
		list($rows, $cols) = explode("x", $this->resolution);
		
		$browser = get_browser(null, true);
		$browser = $browser['browser'];
		if(isset($this->display[$browser])) {
			$b_display = $this->display[$browser];
		}else {
			$b_display = $this->display['default'];
		}
		$i=0;
		$first = true;
		$addX = 17;
		$moltplic_y = $b_display['width'][$cols];
		$html="";
		$first=True;
		$firstField=True;
		$flag_isWindow = false;
		$cursor=false;
		$txt=0;
		$var=1;
		$val_size = $b_display['size'][$cols];
		
		wi400Detail::cleanSession("DISPOSE_5250");
		// Carico le personalizzazioni sul video
		//echo $browser['browser']."__<br/>";
		//showArray($browser);
		// Verifico se c'è un posizionamento cursore altrimenti andrò sul primo campo libero
		//
		if ($this->getCursorPosition()!=Null) {
			$cursor = $this->getCursorPosition();
			$firstField=False;
		}
		
		//showArray($this->fields);
			
		if ($this->fields!=Null) {
			$html = '<div class="cols'.$cols.'">';
			foreach ($this->fields as $key=>$value) {
				$x = $value->getXposition()*$addX;
				$y = $value->getYposition();
				$xx =$value->getXposition();
				// Elimino tutti i campi spuri (LUNHGEZZA 0)
				if ($value->getIO()==False && $value->getText()=="" && $value->getLength()==0) {
					unset($value);
					continue;
				}
				// Rimappo i nomi
				$id="";
				if ($value->getIO()==True) {
					$id="VAR_".$var;
					$var++;
				} else {
					$id="TXT_".$txt;
					$txt++;
				}
				$value->setId($id);
				
				if($first) {
					$addX = 17;
					$first = false;
				}
				$stile = $value->getColour();
				if($value->getHighLightAttribute()) {
					$stile .= " focus".strtoupper($value->getHighLightAttribute());
				}
				$attributi = " color='".$value->getColour()."' row='".$xx."' column='".$y."' len='".$value->getLength()."'";
				$style_is_window = $this->isWindow ? "z-index: 11;" : "";
				if($flag_isWindow) {
					//$firephp->fb($this->posWindow['row']+$window_row);
					$regular_express = "/[a-zA-Z0-9\d$!^(){}?\[\]<>~%@&*+=]/i";
					if(($this->posWindow['row'] == $xx || ($this->posWindow['row']-1)+$window_row == $xx)
						&& !preg_match($regular_express, $value->getText()) && !$value->getStructuredData()) {
						//$firephp->fb($value);
						continue;
					}
					if($this->posWindow['col'] == $y && !preg_match($regular_express, $value->getText())) { 
						continue;
					}
					
					$x = abs($xx - $this->posWindow['row'])*$addX;
					$y = abs($y - $this->posWindow['col']);
					
					//$firephp->fb($window_column, $window_row);
					/*echo $xx."___".$this->posWindow['row']."<br>";
					 echo $y."___".$this->posWindow['col']."<br>";
					echo $window_x."__".$window_y."_<br>";*/
					//$css_style = 'top:'.$x.'px;left:'.(($y-1)*$moltplic_y).'px;';
				}
				
				if ($value->getIO()==False) {
					//echo "<br>ATTRIBUTO:".$stile." - ".$value->getText();
					$css_style = 'top:'.$x.'px;left:'.(($y-1)*$moltplic_y).'px;';
					$t5info="";
					$my = $value->getYposition();
					$mx =$value->getXposition();
					$campo = wi400AS400Func::getCampoXY($this->info, $mx, $my);
					$t5info="$my-$mx {$campo['OT5FLD']}";
					
					$html.='<div '.$attributi.' class="component f'.$stile.'" t5info="'.$t5info.'" style="'.$css_style.' '.$style_is_window.'">';
					if ($value->getColour()!="27") {
						$html.= str_replace(" ", "&nbsp;", _t($value->getText(), Null, True));
						
						/*foreach (str_split($value->getText()) as $i => $char) {
							if($char == " ") $char = "&nbsp;";
							
							//echo ($y + $i)."_";
							if($xx == 6) $stile = 24;
							$this->griglia[$xx-1][($y-1) + $i] = $char."|f".$stile;
						}*/
						//echo $xx."_".$y."_".strlen($value->getText())."_".$value->getText()."<br/>";
					}
					
					$html .= "</div>";
				} else {
					$structData = $value->getStructuredData();
					
					if(isset($structData->struct->type) && in_array($structData->struct->type, array(53))) {
						continue;
					}
					
					$css_style = 'top:'.($x-2).'px;left:'.(($y-1)*$moltplic_y).'px;';

					if(isset($structData->struct->type) && $structData->struct->type == 51) {
						$this->isWindow = true;
						$flag_isWindow = true;
						//$this->posWindow = str_pad($y, 2, "0", STR_PAD_LEFT).str_pad($xx, 2, "0", STR_PAD_LEFT);
						$this->posWindow = array('row' => $xx, 'col' => $y);
						
						$window_row = $structData->struct->windowRow;
						$window_column = $structData->struct->windowColumn;
						
						$window_row += 1; //Siccome su alcune window è presente il titolo
						
						if(isset($structData->struct->minorStruct['01'])) {
							$window_row += 1;
						}
						//$firephp->fb($structData->struct->minorStruct['01']);
						
						//echo $window_row."__x__".$window_column."_<br/>";

						$width = ($window_column+4) * $this->display[$browser]['width'][80];
						$height = ($window_row) * $addX;
						//showArray($value);
						//$width = $this->
						$html.= "<div class='blocco_window'></div>";
						$html.='<div '.$attributi.' class="component window" style="'.$css_style.' width: '.$width.'px; height: '.$height.'px;">';
						$i++;
						$firstField = true;
						
						//$html .= "sono una finestra";
					}else {
						$t5info="";
						$my = $value->getYposition();
						$mx =$value->getXposition();
						$campo = wi400AS400Func::getCampoXY($this->info, $mx, $my);
						$t5info="$my-$mx {$campo['OT5FLD']}";
						
						//echo $campo['OT5FLD']."___".$campo['OT5EDT']."_<br>";
						//$firephp->fb($campo);
						
						// @todo se $campo['OT5EDT]=="Y" e campo di INPUT/OUTPUT metto fuori icona calendario
						// @todo se $campo['OT5FLD]=='MDACDA' lookup e controlli su articolo
						/*
								$decodeParameters = array(
										'TYPE' => 'articolo',
										'AJAX' => true,
										'COMPLETE' => true,
										'COMPLETE_MIN' => 2,
										'COLUMN' => 'MDADSA',
										'COMPLETE_MAX_RESULT' => 15,
										'JS_PARAMETERS'=>array("DATA_VALIDITA"=>"PIPPO_CIPPO")
										);
								$myField->setDecode($decodeParameters);	
								// Lookup
								$myLookUp =new wi400LookUp("LU_ARTICOLI");
								$myLookUp->addField("codart");
								$myLookUp->addField("DATA_VALIDITA");
								$myField->setLookUp($myLookUp);
						 */
	 					$html.='<div '.$attributi.' class="component input i'.$stile.'" t5info="'.$t5info.'" style="'.$css_style.' '.$style_is_window.'">';
						$i++;
						
						if(!$value->getStructured()) {
							$myField = new wi400InputText($value->getId());
							$myField->setLabel("");
							$myField->setValue(rtrim($value->getText()));
							$myField->setCleanable(false);
							$myField->setTabIndex($i+1);
							$myField->setStyle5250(true);
							// Verifico se è un non display
							if ($stile=="27") {
								$myField->setType('PASSWORD');
							}
							// Verifico se è un campo con AUTOENTER
							if ($value->getAutoEnter()==True) {
								$myField->setOnKeyUp("autoEnterField(event, this,'".$this->id_display."');");
							}
						
							if ($firstField==True && !$value->getBypass()) {
								$myField->setAutoFocus(True);
								$firstField=False;
							} else if ($cursor!=False) {
								$my = $value->getYposition();
								$mx =$value->getXposition();
								if ($cursor[0]==$mx && $cursor[1]==$my) {
									$myField->setAutoFocus(True);
								}
							}
							if ($value->getMonocase()==True) {
								$myField->setCase("UPPER");
							}
							//Controllo riempimento spazi o zeri
							if($value->getRightAdjust()) {
								$myField->setOnBlur($this->getHtmlRightAdjust($value->getRightAdjust()));
							}
							$myField->setOnFocus("focusedField(this);");
							$myField->setOnKeyDown("keyDownField(this);");
							$size = $value->getLength();
							
							$val_size = $b_display['size'][$cols];
							if($val_size == 'auto') {
								$myField->setSize($size);
							}else {
								$html .= "<style>#".$value->getId()." { width: ".($size * $val_size)."px;}</style>";
							}
							//
							$myField->setMaxLength($size);
							$classeField = "";
							if($value->getMonocase()) {
								$classeField .= "monocase";
							}

							if($value->getBypass()) {
								if($stile == 27) {
									$classeField .= " nascosto";
								}else {
									//$myField->setReadonly(true);
									//if ($value->getVacum()!=True) {
										$myField->setDisabled(true);
										$myField->setTabIndex("-2");
									//} else {
									//	$classeField .= " nascosto";
									//}
								}
							}
							if($classeField) $myField->setStyleClass("$classeField inputtext");
							// Caledario se il campo è di tipo DATA
							if($campo['OT5EDT'] == 'Y' || ($xx == 3 && $y == 73)) {
								$myField->addValidation("date");
							}
							if(isset($campo['OT5DEC']) && $campo['OT5DEC']!='') {
								$decodeParameters = wi400AS400Func::getT5DecodeParameters($campo['OT5DEC']);
								$myField->setDecode($decodeParameters);
							}
							if(isset($campo['OT5LOK']) && $campo['OT5LOK']!='') {
								$myLookUp =new wi400LookUp($campo['OT5LOK']);
								$myLookUp->addField($value->getId());
								$myField->setLookUp($myLookUp);
								$_SESSION['DISPOSE_ONLY_INPUT_LOOKUP'][$value->getId()] = $myLookUp;
							}
							/*if($campo['OT5FLD']=='MDACDA' || ($xx == 3 && $y == 25)) {
								$decodeParameters = array(
									'TYPE' => 'articolo',
									'AJAX' => true,
									'COMPLETE' => true,
									'COMPLETE_MIN' => 2,
									'COLUMN' => 'MDADSA',
									'COMPLETE_MAX_RESULT' => 15,
									//'JS_PARAMETERS'=>array("DATA_VALIDITA"=>"PIPPO_CIPPO")
								);
								$myField->setDecode($decodeParameters);
								// Lookup
								$myLookUp =new wi400LookUp("LU_ARTICOLI");
								$myLookUp->addField($value->getId());
								$myField->setLookUp($myLookUp);
								
								$_SESSION['DISPOSE_ONLY_INPUT_LOOKUP'][$value->getId()] = $myLookUp;
								
								/*$file = fopen("/www/zendsvr/htdocs/WI400_pasin/zElimina.txt", "w");
								fwrite($file, $myField->getHtml());
								fclose($file);*/
							//}
							
							//$myDetail->addField($myField);
							wi400Detail::setDetailField("DISPOSE_5250", $myField);
							
							$html.=$myField->getHtml();
							
							//Controllo caratteri validi per il campo
							if($value->getFieldShift()) {
								$html .= $this->getHtmlFieldShift($value->getFieldShift(), $value);
							}
						}else {
							if(isset($structData->struct->typeSelection)) {
								$typeSelection = $structData->struct->typeSelection;
							}else {
								$typeSelection = "-1";
							}
							
							if(isset($structData->struct->type)) {
								$type = $structData->struct->type;
							}else {
								$type = "-1";
							}
							
							if($typeSelection == 11) {
								$html .= '<label class="container"><span class="label-radio">'.$structData->struct->minorStruct[0]->choiceText.'</span>
											  <input type="radio" onFocus="campoSelezionato=this.name;" onClick="doFunctionKey(this, \''.$this->id_display.'\', \'CHOICE\');" name="'.$value->getId().'">
											  <span class="checkmark"></span>
											</label>';
							}else if($typeSelection == 41) {
								$html_button = array();
	
								foreach ($structData->struct->minorStruct as $index => $button) {
									$myButton = new wi400InputButton($value->getId()."_".$index);
									$myButton->setLabel($button->choiceText);
									//$myButton->setStyleClass("prova");
									$myButton->setOnClick('doFunctionKey(this, "'.$this->id_display.'", "'.$button->AIDData.'");');
									//echo $button->choiceText."__<br/>";
									
									$html_button[] = $myButton->getHtml();
								}
								$html .= implode("&nbsp;&nbsp;&nbsp;", $html_button);
							}else if($typeSelection == "01") {
								//showArray($value);
								
								$html_button = array();
								foreach ($structData->struct->minorStruct as $index => $button) {
									/*$myButton = new wi400InputButton($value->getId()."_".$index);
									$myButton->setLabel($button->choiceText);
									$myButton->setStyleClass("select");*/ 
									//$myButton->setOnClick('doFunctionKey(this, "'.session_id().'", "'.$button->AIDData.'");');
									//echo $button->choiceText."__<br/>";
									$h = "<button id='".($value->getId()."_".$index)."' class='select'>".($button->choiceText)."</button>";
										
									//$html_button[] = $myButton->getHtml();
									$html_button[] = $h;
								}
								$html .= implode("&nbsp;&nbsp;&nbsp;", $html_button);
							}else if($type == 53) { //Togliere il continue sopra
								//TODO Scroll Bar
							}
						}
						$html.="</div>";
					}
				}
			}
			
			if($this->isWindow) {
				$html .= "</div>"; // chiudo la window
			}
			//$this->stampaValoriGriglia();
			
			$errore="";
			if ($this->getMessage()!="") {
				$errore=$this->getMessage();
			}
			$html.="</div>
					<div class='cont_error'><div class='error'>$errore</div>
					<div class='info'>".$this->getInfo()."</div>
 					<div class='cont_cmd_line'>
 						<div class='cmd_line component input i30'>
 							<input type='text' id='input_system_line' class='inputtext' maxlength='90' ".($val_size == 'auto' ? "size='90'" : "style='width: ".(90 * $val_size)."px;'")."/>
 						</div>
 					</div>
					<div class='debug' id='debug'></div>
					</div>
					<script>
						if(rules.length) {
							yav.init('wi400Form', rules);
						}
						enableOverwrite();
						activeCaretHorizontal(jQuery);
						onClickMonitor();
						var moltiplic=$moltplic_y;
						var addX = $addX;
						setTimeout(function() { x_error(".($errore ? "true" : "false")."); }, 0);";
			$html .= $flag_isWindow ? "disableTab();startWindowDrag();" : ""; 
			$html .= "</script>";
		}
		
		if($this->disposeContainer) {
			$html = $this->addHtmlContainer($html);
		}
		if($this->disposeFunctionButton) {
			$html .= $this->disposeFunctionButton();
		}
		// Salvataggio del display
		$this->setStreamObj(Null);
		$this->cleanCursorPosition();
		$this->forceWindow=False;
		$this->saveDisplay();
		return $html;
	}
	public function waitReply() {
		
	}
	public function getPositionToHex($riga, $colonna) {
		$riga = sprintf('%02s',dechex($riga));
		$colonna = sprintf('%02s',dechex($colonna));
		$hex = strtoupper($riga.$colonna);
		
		return $hex;
	}
	public function getFocusedField($selezionato, $caratteri="") {
		$hex="";
		if ($selezionato!="") {
			$campo = $this->getFieldsById($selezionato);
			if ($campo) {
				$riga = $campo->getXposition();
				$colonna = $campo->getYposition();
				if (isset($caratteri) && is_numeric($caratteri)) {
					$colonna = $colonna + $caratteri;
				}
				
				$hex = $this->getPositionToHex($riga, $colonna);
			}
		}
		return $hex;
	}
	/**
	 * @desc Ritorna una stringa con i dati modificati a Video rispetto allo schermo precedente
	 * @param unknown $dati
	 */
	public function getModifiedString($dati="") {
		global $routine_path;
		$stringa ="";
		require_once $routine_path."/generali/conversion.php";
		foreach ($this->getFields() as $campo => $oggetto) {
			if ($oggetto->getIO()==True ) {
				/*if (isset($dati[$oggetto->getId()])) {
					if (rtrim($oggetto->getText())!=rtrim($dati[$oggetto->getId()]) || $oggetto->getModified()==True) {
						$oggetto->setModified(True);
						echo "<br>SPOSTO A TRUE";
						// Trasformo in dati in HEX AS400
						$riga = wi400AS400Func::num2hex($oggetto->getXposition());
						$colonna = wi400AS400Func::num2hex($oggetto->getYposition());
						$stringa.="11".$riga.$colonna.wi400AS400Func::str2hex(a2e(rtrim($dati[$oggetto->getId()])));
					}
				}*/
				if (isset($dati[$oggetto->getId()])) {
					if (rtrim($oggetto->getText())!=rtrim($dati[$oggetto->getId()]) || $oggetto->getModified()==True) {
						$oggetto->setModified(True);
						$riga = wi400AS400Func::num2hex($oggetto->getXposition());
						$colonna = wi400AS400Func::num2hex($oggetto->getYposition());
						$stringa.="11".$riga.$colonna.wi400AS400Func::str2hex(a2e(rtrim($dati[$oggetto->getId()])));
					}
				} else {
					if ($oggetto->getModified()==True) {
						$riga = wi400AS400Func::num2hex($oggetto->getXposition());
						$colonna = wi400AS400Func::num2hex($oggetto->getYposition());
						$stringa.="11".$riga.$colonna.wi400AS400Func::str2hex(a2e(rtrim($oggetto->getText())));
					}
				}	
				/*	if (rtrim($oggetto->getText())!=rtrim($dati[$oggetto->getId()]) || $oggetto->getModified()==True) {
						$oggetto->setModified(True);
						// Trasformo in dati in HEX AS400
						$riga = wi400AS400Func::num2hex($oggetto->getXposition());
						$colonna = wi400AS400Func::num2hex($oggetto->getYposition());
						$stringa.="11".$riga.$colonna.wi400AS400Func::str2hex(a2e(rtrim($dati[$oggetto->getId()])));
					}
				}*/
			}
		}
		return strtoupper($stringa);
	}
	/**
	 * Funzione per macro, cerca se a video c'è una determinata stringa
	 * @param unknown $cerca
	 * @return boolean
	 */
	public function findText($cerca) {
		foreach ($this->getFields() as $campo => $oggetto) {
			$testo = $oggetto->getText();
			//echo "<br>TESTO $cerca:".$testo;
			if (strpos($testo, $cerca)!==False) {
				return True;
			}
		}
		return False;
	}
	/**
	 * Funzione pr macro, riempi una variabile in una certa posizione con del testo
	 * @param unknown $testo
	 * @param unknown $x
	 * @param unknown $y
	 */
	public function fillText($testo, $x, $y) {
		foreach ($this->getFields() as $campo => $oggetto) {
			if ($oggetto->getIO()==True) {
				$myx = $oggetto->getXposition();
				$myy = $oggetto->getYposition();
				//echo "<br>TESTO $cerca:".$testo;
				if ($myx==$x && $myy==$y) {
					$oggetto->setText($testo);
					$oggetto->setModified(True);
				}
			}
		}
	}
	/**
	 * Funzione per macro, invia un comando tipo ENTER o altro
	 * @param unknown $comando
	 */
	public function sendCommand($comando) {
		
	}
	
	public function disposeVirtualKey() {
		$html = "";
		if(count(wi400AS400Constant::virtualKey) > 0) {
			$html .= "<script>
						var virtualKey = ".json_encode(wi400AS400Constant::virtualKey).";
						jQuery(document).keydown(function(event) {
							if(typeof(virtualKey[event.keyCode]) == 'object') {
								var objKey = virtualKey[event.keyCode];
								if(objKey.location == event.originalEvent.location) {
									console.log(objKey.function);
									eval(objKey.function);
								}
							}
						});
					</script>";
		}
		return $html;
	}
	/**
	 * @desc Ritorna la tastierina con i tasti di funzione ed altro
	 */
	public function disposeFunctionButton($onlyJs=False) {
		$html="";
		$html='<div id="5250_KEY_FUNCTION">';		
		foreach (wi400AS400Constant::key as $key => $value) {
			//$html.="<div>";
			if ($onlyJs==False) {
				if ($value['KEYBOARD']==True) {
					//showArray($value);
					
					if ($value['DESC']=="Cmd01") $html.="<br>";
					if ($value['DESC']=="Cmd13") $html.="<br>";
					$myButton = new wi400InputButton($key);
					$myButton->setLabel($value['DESC']);
					if(isset($value['SCRIPT'])) {
						$myButton->setScript($value['SCRIPT']);
					}else {
						$myButton->setScript("doFunctionKey(this, '".$this->id_display."', '$key');");
					}
					$myButton->setButtonStyle("background-color:#66FF33");
					$myButton->setButtonClass("ccq-button-active button_{$key}");
					
					if(isset($value['KEY'])) {
						$html.="<script>setKeyAction('{$value['KEY']}' , 'button_{$key}');</script>";
					}
					$html.=$myButton->getHtml();
				}
			}
			
			//$html.="</div>";
		}
		
 		$html .= $this->disposeVirtualKey();
		
		//Select cambio tema
		$select = new wi400InputSelect("TEMA");
		foreach($_SESSION['LISTA_TEMI_5250'] as $dati) {
			$select->addOption($dati['label'], $dati['file']);
		}
		$select->setOnChange("resubmitPage('&CAMBIO_TEMA=1')");
		if(isset($_SESSION['TEMA_5250']) && $_SESSION['TEMA_5250']) {
			$select->setValue($_SESSION['TEMA_5250']['file']);
		}
		$html .= "<br/> Tema ".$select->getHtml()."&nbsp;";
		
		//Aggiungi tag
		$button = new wi400InputButton("ADD_TAG");
		$button->setLabel("Aggiungi tag");
		$button->setAction("DEBUG_SCHERMATE_5250");
		$button->setForm("ADD_TAG");
		$button->setTarget("WINDOW");
		$button->setButtonStyle("background-color:#00eaff;");
		$button->setButtonClass("ccq-button-active");
		$html .= $button->getHtml()."&nbsp;";
		
		//Scollega
		if(!isset($_REQUEST['WI400_IS_WINDOW'])) {
			$button = new wi400InputButton("SCOLLEGA");
			$button->setLabel("Scollega");
			$button->setScript("closeSession5250();");
			$button->setButtonStyle("background-color: #ff5f5f;");
			$button->setButtonClass("ccq-button-active");
			$html .= $button->getHtml()."&nbsp;";
		}
		
		//Zoom -
		$button = new wi400InputButton("ZOOM_MENO");
		$button->setLabel("Zoom -");
		$button->setScript("zoomSchermata(false);");
		$button->setButtonStyle("background-color:#66FF33");
		$button->setButtonClass("ccq-button-active");
		$html .= $button->getHtml();
		
		//Zoom +
		$button = new wi400InputButton("ZOOM_PIU");
		$button->setLabel("Zoom +");
		$button->setScript("zoomSchermata(true);");
		$button->setButtonStyle("background-color:#66FF33");
		$button->setButtonClass("ccq-button-active");
		$html .= $button->getHtml();
		
		//Estrazione subfile
		$button = new wi400InputButton("ESTRAI_SUBFILE");
		$button->setLabel("Estrai");
		$button->setScript("estraiSubfile();");
		$button->setButtonStyle("background-color:#66FF33");
		$button->setButtonClass("ccq-button-active");
		$html .= $button->getHtml();
		
		$html.="</div>";
		return $html;
	}
}