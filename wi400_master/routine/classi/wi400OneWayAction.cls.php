<?php

/**
 * @name wi400OneWayAction
 * @desc Controlla che l'azione venga eseguita una sola volta
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 21/07/2016
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400OneWayAction {
	private $azione;
	private $status="";
	private $reset = False;
	private $leave = False;
	private $nextAction = "ANNOUNCE";
	private $nextForm = "";
	/**
	 * @return the $status
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @param string $status
	 */
	public function setStatus($status) {
		$this->status = $status;
	}

	/**
	 * @desc Costruttore della classe
	 * @param string $azione: codice azione da controllare
	 * @param bool $leave: uscita diretta se azione già bloccata
	 * @param bool $reset: rilascia stato di lock se leave a True
	 */
	function __construct($azione, $leave=True, $reset=False, $nextAction="ANNOUNCE", $nextForm="") {
		global $db, $actionContext;
		$this->azione = $azione;
		$this->leave = $leave;
		$this->reset = $reset;
		$this->nextAction = $nextAction;
		$this->nextForm = $nextForm;
	}
	function check() {
		global $db, $actionContext, $messageContext;
		// START LOCK
		$lock = startLock("AZIONE", $this->azione."_".session_id());
		if ($lock>"0") {
			$this->status = "E";
			error_log("AZIONE:".$this->azione. " già in esecuzione, USER".$_SESSION['user']);
			$messageContext->addMessage("ERROR", "Azione già eseguita. Scegliere da menu l'azione desiderata");
			if ($this->reset) {
				//$this->resetLock();
			}
			if ($this->leave) {
				//echo "STRINGA:".$this->nextAction.$this->nextForm. " FINE ".die("FINE LEAVE!!". $this->nextAction);
				$actionContext->gotoAction($this->nextAction, $this->nextForm, "",True);
			}
		}
	}
	/**
	 * @desc Resetto il lock
	 */
	function resetLock() {
		endLock("AZIONE", $this->azione."_".session_id());
	}
	
}
