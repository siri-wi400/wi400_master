<?php

class wi400AnnounceContent {

	private $id;
	private $message = array();
	private $target;
	private $vista = "*HOME";
	private $azione = null;
	private $stato_mess = array("1");

	/**
	 * Costruttore della classe
	 *
	 */
	public function __construct($key) {
		$this->id = $key;
	}
	
	public function addMessage($mess) {
		$this->message[] = $mess;
	}
	
	public function getMessage() {
		return $this->message;
	}

	public function getId() {
		return $this->id;
	}
	
	public function setTarget($val) {
		$this->target = $val;
	}
	
	public function getTarget() {
		return $this->target;
	}
	
	public function setVista($vis) {
		$this->vista = $vis;
	}
	
	public function getVista() {
		return $this->vista;
	}
	
	public function setAzione($azione) {
		$this->azione = $azione;
	}
	
	public function getAzione() {
		return $this->azione;
	}
	
	public function setStatoMess($stato) {
		$this->stato_mess = $stato;
	}
	
	public function getStatoMess() {
		return $this->stato_mess;
	}
	
	public function getHtml() {
		global $appBase;
		
		$html = "";
		
		//echo showArray($this->getMessage());
		$par_ajax = "";
		if($this->vista == "*ACTION" && $this->azione) {
			$par_ajax = "&VISTA=".$this->vista."&AZIONE=".$this->azione;
		}
	
		if(count($this->stato_mess) == 2) {
			$par_ajax .= "&STATO=ALL_MESS";
		}
		
		//Creo il script javascript con jquery
		$html .= '<script>
					jQuery(function() {
						jQuery( "#'.$this->getId().'" ).accordion({
							collapsible: true,
							heightStyle: "content",
							//header: "font",
							activate: function( event, ui ) {
								//console.log(ui.newHeader);
								var obj = jQuery(ui.newHeader).find("span")[1];
								if(obj) {
									var id_mess = jQuery(obj).attr("cod_mess");
									//console.log("id messaggio: "+id_mess);
								
									jQuery.ajax({
										type: "GET",
										url: _APP_BASE + APP_SCRIPT + "?t=ANNOUNCE_MESSAGE&DECORATION=clean&f='.$par_ajax.'&CODICE="+id_mess
									}).done(function ( response ) {
										if(response) {
											jQuery(ui.newPanel).html(response);
											jQuery("#new_mess_"+id_mess).remove();
										}
									}).fail(function ( data ) {
										console.log("errore!");
									});
								}
							},
							//heightStyle: "fill"
							active: false
						});';
		if($this->target) {
			$html .= 'jQuery("#'.$this->getId().'").dialog({
							resizable: true,
							title: "Message",
							modal: true,
							width: 700,
							height: 500,
							show: { 
								effect: "slideDown", 
								duration: 200 , 
								complete: function() {
									blockBrowser(false);
						        }
							},
							open: function(event, ui) {
								wi400top.wi400_window_counter++;
							},
							close: function(event, ui) {
								wi400top.wi400_window_counter--;
							}
						});';
		}
						
		$html .= '});
				</script>';
		
		//Creo il contenitore dell'accordion
		$html .= "<div id='{$this->getId()}'>";
		
		//Ci inserisco le testare e il contenuto
		foreach($this->getMessage() as $key => $mess) {
			$html .= "<h1";
			if($mess->getColorHeader()) {
				$html .= " style='background: {$mess->getColorHeader()}'";
			}
			
			//echo getDb2Timestamp("*INZ")." == ".$mess->getLogNot()."<br/>";
			$new = "";
			if($mess->getLogNot() && $mess->getLogNot() == getDb2Timestamp("*INZ")) {
				$new = "<font id='new_mess_{$mess->getId()}' color='black'>New</font>";
			}
			$icon = "";
			if($mess->getRightIcon()) {
				$icon = "<span class='{$mess->getRightIcon()} icon_mess'></span>";
			}
			$html .= ">".$mess->getHeader()."&nbsp;&nbsp;".$new."<span cod_mess='{$mess->getId()}'></span>$icon</h1>";
			
			$html .= "<div>Loading <img src='".$appBase."themes/common/images/decode_loading.gif'/>";
			if($mess->getBody()) {
				if(is_array($mess->getBody())) {
					$html .= "<p>";
						$html .= implode("<br/>", $mess->getBody());
					$html .= "</p>";
				}else {
					$html .= $mess->getBody();
				}
			}
			$html .= "</div>";
			
		}
		
		//Chiudo il contenitore
		$html .= "</div>";
		
		return $html;
	}
	
 	/**
     * Visualizzazione del campo di testo
     *
     */
    public function dispose(){
    	echo $this->getHtml();
    }
	
}

?>