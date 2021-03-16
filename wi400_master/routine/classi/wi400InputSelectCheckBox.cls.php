<?php

class wi400InputSelectCheckBox extends wi400InputSelect {

	private $colsNumber;
	
	public function setColsNumber($colsNumber){
    	$this->colsNumber = $colsNumber;
    }
    
    public function getColsNumber(){
    	return $this->colsNumber;
    } 
   
    public function __construct($id){
    	$this->setId($id);
//    	echo "ID: ".$this->getId()."<br>";
    	$this->setType("SELECT_CHECKBOX");
    	$this->setMultiple(true);
    	$this->colsNumber = 1;
    }
    

    public function dispose(){
?>
	<td>
		<table>
			<tr>
<?
			$colCounter = 0;
			$array = $this->getOptions();
			if (isset($array)) {	
			foreach ($array as $optionKey => $optionValue){ 
				$selectCheckBox = new wi400InputCheckbox($optionKey);
//				$check_id = $this->getId()."_".$optionKey;
//				$selectCheckBox = new wi400InputCheckbox($check_id);
//				echo "CHECK_BOX_ID: ".$selectCheckBox->getId()."<br>";
				$selectCheckBox->setName($optionKey);
				$selectCheckBox->setChecked(false);
				$selectCheckBox->setLabel($optionValue);
				$selectCheckBox->setValue($optionKey);
				$selectCheckBox->setCheckUpdate($this->getCheckUpdate());
				if($this->getReadonly())
					$selectCheckBox->setReadonly($this->getReadonly());
				if($this->getDisabled())
					$selectCheckBox->setReadonly($this->getDisabled());
				$selectCheckBox->setType("SELECT_CHECKBOX");
				// Controllare che il valore sia contenuto nell'array value
				$myValue = $this->getValue();
				
				if ($myValue!="") {
					if (in_array($optionKey,$this->getValue())){
						$selectCheckBox->setChecked(true);
					}
				}
				if ($colCounter == $this->colsNumber){
					echo "</tr><tr>";
					$colCounter = 0;
				}
				echo "<td>";
				$selectCheckBox->dispose();
				echo "</td>";
				
				$colCounter++;
			}
			}
?>
			</tr>
		</table>
	</td>
<?
    }
}
?>