<?php
class MONITOR_SISTEMA_WIDGET extends wi400Widget {
	private $result = "SUCCESS";
	
	function __construct($progressivo) {
		$this->progressivo = $progressivo;
		$this->parameters['TITLE'] = "SISTEMA";
		$this->parameters['ONCLICK'] = true;
		$this->parameters['INTERVAL'] = 3000;
	}
	
	public function getHtmlBody() {
		global $connzend, $routine_path;
		
		require_once $routine_path."/os400/APIFunction.php";
		
		$rtv_spc = new wi400Routine('QWCRSSTS', $connzend);
		$tracciato = getApiDS("QWCRSSTS", "SSTS0200");
		$rtv_spc->load_description(null, $tracciato, True);
		$do = $rtv_spc->prepare();
		$rtv_spc->set('FORMAT',"SSTS0200");
		$rtv_spc->set('RESET',"*NO");
		$rtv_spc->set('SIZEDATA', 148);
		$do = $rtv_spc->call(True);
		$dati = $rtv_spc->get('DATI');
		
		$rtv_spc = new wi400Routine('QWCRSSTS', $connzend);
		$tracciato = getApiDS("QWCRSSTS", "SSTS0100");
		$rtv_spc->load_description(null, $tracciato, True);
		$do = $rtv_spc->prepare();
		$rtv_spc->set('FORMAT',"SSTS0100");
		$rtv_spc->set('RESET',"*NO");
		$rtv_spc->set('SIZEDATA', 148);
		$do = $rtv_spc->call(True);
		$dati2 = $rtv_spc->get('DATI');
		$dati = array_merge($dati, $dati2);
		
		$diskTot = $dati['SYSASP']/1000;
		$diskUse = ($dati['SYSASP']*($dati['PSYSASP']/10000))/100000;
		$diskFree = ($diskTot - $diskUse);
		$diskUse = number_format($diskUse, 1);
		$diskFree = number_format($diskFree, 1);
		
		//$dati = $this->parameters['BODY'];
		$html = "<div style='font-size: 15px;'>
					CPU: ".($dati['PPROCESU']/10)."%<br/>
					Disco utilizzato: ".$diskUse." GB<br/>
					Disco libero: ".$diskFree." GB
				</div>";
		
		return $html;
	}
	
	function run() {
		global $db;
		
		$this->parameters['TITLE'] = "SISTEMA";
	
		return $this->result;
	}
}
