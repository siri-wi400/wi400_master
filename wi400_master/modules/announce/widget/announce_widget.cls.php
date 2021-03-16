<?php
class ANNOUNCE_WIDGET extends wi400Widget {
	private $result = "SUCCESS";
	
	function __construct($progressivo) {
		$this->progressivo = $progressivo;
		$this->parameters['TITLE'] = "Promozioni";
		$this->parameters['ONCLICK'] = true;
		$this->parameters['RELOAD'] = true;
		$this->parameters['INTERVAL'] = 1000*60*5;
	}
	
	public function getHtmlBody() {
		global $db, $settings;
		
		//$this->removeColor = true;
		$dati = $this->parameters['BODY'];
		$html = "Nuove comunicazioni: ".$dati[0];
		//$this->removeColor = true;
/*		
		// IMMAGINI NEL WIDGET
		if(isset($dati[1]) && !empty($dati[1])) {
			$html .= "<br>";
				
			$img_array = array();
			
			foreach($dati[1] as $id => $vals) {
				foreach($vals as $row_atc) {
//					$html .= $row_atc['ATCATC']."<br>";

					$format = explode(".", $row_atc['ATCATC']);
					$format = $format[(count($format))-1];
//					$html .= "FORMAT: $format<br>";
						
					if(in_array(strtoupper($format), array("JPG"))) {
						$html .= get_image_base64($row_atc['ATCATC'], 80);
					}
				}
			}			
		}
*/		
		return $html;
	}
	
	function run() {
		global $db, $settings;
		
		$this->parameters['TITLE'] = "Prodotti a marchio";
		
		$announce = new wi400AnnounceMessage();
		
		$msg_array = $announce->getMessages(array(), array(), null, null, false, "PROMO");
		
		$num_msg = count($msg_array);
		
		$img_array = array();
		
		// IMMAGINI NEL WIDGET
		foreach($msg_array as $id => $vals) {
			$img_array[$id] = $announce->getAllegati($id);
		}
		
		$this->parameters['BODY'] = array($num_msg, $img_array);
		
		$this->removeColor = true;
	
		return $this->result;
	}
}
