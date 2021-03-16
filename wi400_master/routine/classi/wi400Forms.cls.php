<?php

/**
 * @name wi400Forms.php
 * @desc Check struttura forms->azione->model/view->nome_form.php 
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 16/09/2016
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400Forms {

	private $azione;
	private $form;
	private $modulo;
	private $checkModel = false;
	private $checkView = false;

	public function __construct($azione, $form, $modulo){
		$this->azione = strtolower($azione);
		$this->form = strtolower($form);
		$this->modulo = $modulo;
	}
	
	/**
	 * Controlla la presenza del file
	 * 
	 * @param string $tipo -> model/view
	 * @return string|boolean
	 */
	public function checkExitFile($tipo) {
		global $moduli_path;
		
		$path = $moduli_path."/".$this->modulo."/forms/".$this->azione."/$tipo/".$this->form.".php";
		if(file_exists($path)) {
			return $path;
		}else {
			return false;
		}
	}
	
	/**
	 * Ritorna il require_once del model da chiamare
	 * 
	 * @return string
	 */
	public function callModel() {
		$file = $this->checkExitFile("model");
		if($file) {
			$this->checkModel = true;
			return "require_once '$file';";
		}else {
			$file = $this->checkExitFile("view");
			if(!$file) {
				developer_debug("FORM '".strtoupper($this->form)."' SPECIFICATO NON ESISTENTE");
				echo 'FORM "'.strtoupper($this->form).'" NON ESISTENTE!';
			}else {
				$this->checkView = true; 
			}
		}
		
		return "";
	}
	
	/**
	 * Ritorna il require_once del view da chiamare
	 *
	 * @return string
	 */
	public function callView() {
		if(!$this->checkModel && !$this->checkView) return;
		
		$file = $this->checkExitFile("view");
		if($file) {
			$this->checkView = true;
			return "require_once '$file';";
		}
		
		return "";
	}
}