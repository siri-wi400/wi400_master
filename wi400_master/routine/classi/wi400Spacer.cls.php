<?php
class wi400Spacer {

	private $numSpace;
	
	public function __construct($numSpace = 1){
		$this->numSpace = $numSpace;
	}	
	
	public function getHtml(){
		$htmlOut = "";
		for($i=0; $i < $this->numSpace; $i++) {
			 $htmlOut .="<br>";
		}
		return $htmlOut;
	}
	
	public function dispose(){
		echo $this->getHtml();
	}
	
	public static function disposeSpaces($numSpace = 1){
		$htmlOut = "";
		for($i=0; $i < $numSpace; $i++) {
			 $htmlOut .="<br>";
		}
		echo $htmlOut;
	}

}
?>