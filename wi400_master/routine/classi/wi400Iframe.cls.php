<?php

class wi400Iframe {

	private $id;
	private $azione;
	private $form;
	private $gateway;
	private $decoration;
	private $url;
	private $parametri = array();
	private $style;
	private $class;
	private $autoResize = false;
	private $autoLoad = true;

	/**
	 * Costruttore della classe
	 *
	*/
	public function __construct($key, $azione = "", $form = "", $gateway = "") {
		$this->id = $key;
		$this->azione = $azione;
		$this->form = $form;
		$this->gateway = $gateway;
	}
	
	
	/**
	 * Setta l'attributo src dell'iframe con il parametro url passato
	 * 
	 * @param string $value
	 */
	public function setUrl($value) {
		$this->url = $value;
	}
	
	/**
	 * Reperisce l'attributo url passato all'iframe
	 * 
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}
	
	/**
	 * Viene settata l'azione a cui andrà a fare rifermento l'iframe
	 * 
	 * Se non viene passato l'url viene creato l'indirizzo composto dalla t e f + altri parameti 
	 * passati con le funzioni addParametro e setParametri
	 * 
	 * @param string $value
	 */
	public function setAzione($value) {
		$this->azione = $value;
	}
	
	/**
	 * Ritorna l'azione in cui andrà a fare riferimento l'iframe
	 * 
	 * @return string
	 */
	public function getAzione() {
		return $this->azione;
	}
	
	/**
	 * Viene settato il form a cui andrà a fare rifermento l'iframe
	 * 
	 * Se non viene passato l'url viene creato l'indirizzo composto dalla t e f + altri parameti
	 * passati con le funzioni addParametro e setParametri
	 * 
	 * @param string $value
	 */
	public function setForm($value) {
		$this->form = $value;
	}
	
	/**
	 * Ritorna il form in cui andrà a fare riferimento l'iframe
	 * 
	 * @return string
	 */
	public function getForm() {
		return $this->form;
	}
	
	/**
	 * Viene settato il gateway a cui andrà a fare rifermento l'azione
	 *
	 * @param string $value
	 */
	public function setGateWay($value) {
		$this->gateway = $value;
	}
	
	/**
	 * Ritorna il gateway in cui andrà a fare riferimento l'azione
	 *
	 * @return string
	 */
	public function getGateWay() {
		return $this->gateway;
	}
	
	/**
	 * Viene settato il decoration nel url
	 *
	 * @param string $value
	 */
	public function setDecoration($value) {
		$this->decoration = $value;
	}
	
	/**
	 * Ritorna il decoration
	 *
	 * @return string
	 */
	public function getDecoration() {
		return $this->decoration;
	}
	
	/**
	 * Setta l'attributo style con la stringa di valori passati alla funzione
	 * 
	 * es.. "border: 1px solid black;"
	 * 
	 * @param string $value
	 */
	public function setStyle($value) {
		$this->style = $value;
	}
	
	/**
	 * Ritorna lo stile inlinea dell'iframe
	 * 
	 * @return string
	 */
	public function getStyle() {
		return $this->style;
	}
	
	/**
	 * Aggiunta di un parametro all'array "parametri" che poi verrà aggiunto
	 * nell'url
	 * 
	 * @param string $value
	 */
	public function addParametro($value) {
		array_push($this->parametri, $value);
	}
	
	/**
	 * Setta tutto l'array "parametri" che poi verrà aggiunto nell'url
	 * 
	 * @param string $array
	 */
	public function setParametri($array) {
		$this->parametri = $array;
	}
	
	/**
	 * Reperisce l'array di tutti i parametri che poi vengono inseriti nell'url
	 * 
	 * @return array
	 */
	public function getParametri() {
		return $this->parametri;
	}
	
	/**
	 * Setta l'attributo class nel tag iframe
	 * 
	 * @param string $value
	 */
	public function setClass($value) {
		$this->class = $value;
	}
	
	/**
	 * Recupera il valore dell'attributo class dell'iframe
	 * 
	 * @return string
	 */
	public function getClass() {
		return $this->class;
	}
	
	/**
	 * L'iframe si autoResiza ogni mezzo secondo
	 *
	 * @param boolean
	 */
	public function setAutoResize($value = false) {
		$this->autoResize = $value;
	}
	
	/**
	 * Recupera il valore dell'attributo class dell'iframe
	 *
	 * @return string
	 */
	public function getAutoResize() {
		return $this->autoResize;
	}
	
	/**
	 * true: l'iframe carica subito l'url passato
	 * false: bisogna premere un bottone per avviare il caricamento
	 * 
	 * @param boolean $val
	 */
	public function setAutoLoad($val) {
		$this->autoLoad = $val;
	}
	
	/**
	 * Ritorna true se l'iframe viene caricato subito altrimenti false
	 * 
	 * @return boolean
	 */
	public function getAutoLoad() {
		return $this->autoLoad;
	}
	
	public function getHtml() {
		global $appBase, $currentHMAC;
		
		$t = $this->azione;
		$f = $this->form;
		$g = "";
		if($this->gateway) {
			$g = "&g=".$this->gateway;
		}
		$decoration = "&DECORATION=lookup";
		if($this->decoration) {
			$decoration = "&DECORATION=".$this->decoration;
		}
		
		
		$style = "style='border: 0px; width: 100%; height: auto;";
		if($this->getStyle()) {
			$style .= $this->getStyle();
		}
		$style .= "'";
		
		$class = "";
		if($this->class) {
			$class = "class='{$this->class}'";
		}
		
		$autoResize = "";
		if($this->autoResize) {
			$autoResize = "onload=\"AdjustIframeHeightOnLoad('{$this->id}')\"";
		}
		
		if($this->getUrl()) {
			$url = $this->getUrl()."&WI400_IS_IFRAME=".$this->id.$decoration;
		}else {
			$url = $appBase."index.php?t=$t&f=$f".$g."".$decoration."&WI400_IS_IFRAME=".$this->id;
		}
		$url .= "&WI400_HMAC=".$currentHMAC;
		
		$name = "name='{$this->id}'";
		if($this->autoLoad) {
			$html = "<iframe id='{$this->id}' $name src='$url' $class $style overflow=\"hidden\" $autoResize></iframe>";
		}else {
			$html = "<iframe id='{$this->id}' $name src='loadIframe.php?GOTOURL=".base64_encode($url)."&ID={$this->id}' $class $style overflow=\"hidden\" $autoResize></iframe>";
		}
		$script="<script>jQuery('#{$this->id}').load(function(){
        var iframe = jQuery('#{$this->id}').contents();
		jQuery(iframe).on('click', function(event) {
		    	wi400SetCookie('WI400_LAST_IFRAME', '{$this->id}');
		});
        });</script>";
		
		$html .=$script;
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