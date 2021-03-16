<?php
require_once p13nPackage("common");
								
class menu extends common {

	public function decode(){
			
		if (strpos($this->getFieldValue(), "WIZARD") === 0){
			return "WIZARD";
		}else{
			return parent::decode();	
		}
	}
}	


?>