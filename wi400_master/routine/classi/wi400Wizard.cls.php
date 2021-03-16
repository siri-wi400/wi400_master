<?php
class wi400Wizard {
	
	private $currentStep;
	private $counter;
	private $steps;
	private $title;
	private $name;
	private $end;
	
	public function setCurrentStep($currentStep){
		$this->currentStep = $currentStep;
	}
	
	public function getCurrentStep(){
		return $this->currentStep;
	}
	
	public function setSteps($steps){
		$this->steps = $steps;
	}
	
	public function getSteps(){
		return $this->steps;
	}
	
	public function getEnd(){
		return $this->end;
	}
	
	public function getCounter(){
		return $this->counter;
	}
	
	public function setCounter($counter){
		$this->counter = $counter;
	}
	
	public function getTitle(){
		return $this->title;
	}
	
	public function setTitle($title){
		$this->title = $title;
	}
	
	public function isFirst(){
		if ($this->counter == 0){
			return true;
		}else{
			return false;
		}
	}
	
	public function isLast(){
		if (($this->counter + 1) < sizeof($this->steps)){
			return false;
		}else{
			return true;
		}
	}
	
	public function getFooterButtons(){
		
		$footerButtons = array();

		if (!$this->isFirst() && !$this->isLast()){
			$myButton = new wi400InputButton("PREV_BUTTON");
			$myButton->setAction("WIZARD");
			$myButton->setForm("PREV");
			$myButton->setLabel("Precedente");
			$myButton->setValidation(false);
			$myButton->setDisabled($this->isFirst());
			$footerButtons[] = $myButton;
		}

		
		$myButton = new wi400InputButton("NEXT_BUTTON");
		if (!$this->isLast()) {
			$myButton->setAction("WIZARD");
			$myButton->setForm("NEXT");			
			$myButton->setLabel("Successivo");
		} else {
			$myButton->setAction("WIZARD");
			$myButton->setForm("END");			
			$myButton->setLabel("Fine");
		} 
		$myButton->setValidation(true);
		//$myButton->setDisabled($this->isLast());
		$footerButtons[] = $myButton;
		
		return $footerButtons;
	}
	
	public function __construct($wizardName){
		global $moduli_path, $routine_path;
		if ($wizardName != ""){
			require_once $routine_path."/generali/xmlfunction.php";
			$handle = fopen($moduli_path."/wizard/xml/".$wizardName.".xml", "rb");
			$content = stream_get_contents($handle);
			fclose($handle);	
			$wizardArray=xml2array($content);
			$this->counter = 0;
			$this->name  = $wizardName;
			$stepsList   = $wizardArray["wizard"]["steps"];
			$this->steps = $stepsList["step"];
			$this->title = $wizardArray["wizard"]["title"];
			$this->end   = $wizardArray["wizard"]["end"];
		}
	}
	
	
}
?>