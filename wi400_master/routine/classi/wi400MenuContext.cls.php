<?php
class wi400MenuContext {

	private $actions;
	private $id;
    
    public function __construct($id=""){
    	$this->actions = array();
    	$this->id = $id;
    }

    
	private $contextList = array("LIST_ACTIONS","LIST","BUTTONS","DEFAULT");
	
    public function addAction($wi400Action, $context= "DEFAULT"){
    	if (!isset($this->actions[$context])){
    		$this->actions[$context] = array();
    	}
     	$this->actions[$context][] = $wi400Action;
     }
     
     public function getActions(){
     	return $this->actions;
     }
     
     public function dispose($context = "ALL"){
     	
     	if ($context == "ALL") $context = $this->contextList;
     	
     	$actionsList = $this->getActions();

     	if (sizeof($actionsList)>0){
			echo "<ul id=\"CM1\" class=\"SimpleContextMenu\" style=\"height:auto\">";
			$cc = 0;
			
			$currentIdList = "";
			$listCounter = 0;
			foreach ($context as $contextArea){
				if (isset($actionsList[$contextArea])){

					if ($cc > 0 && !function_exists($menuAction->getIdList())){
						echo "<li class=\"separator\"><img src='themes/common/images/spacer.gif' height=1></li>";
					}

					foreach ($actionsList[$contextArea] as $menuAction) {
//						echo "ACTION:<pre>"; print_r($menuAction); echo "</pre>";
						if($menuAction->getShow()===false)
							continue;
	
						if ($menuAction->getIdList() !== "" &&  $menuAction->getIdList() !== $currentIdList){
							$listCounter++;
							$currentIdList = $menuAction->getIdList();
							echo "<li><span>"._t('AZIONI DELLA LISTA')." ".(($listCounter>1)?$listCounter:"")."</span></li>";
						}
						
						echo "<li><a href=\"javascript:".$menuAction->getScript()."\"";
						if ($menuAction->getIco() != "") {
	    					echo "style=\"background-image: url('".$menuAction->getIco()."');\"";
						}
						echo ">".$menuAction->getLabel()."</a></li>";
	
					}
					
					$cc++;
				}
			}
			echo "</ul>";
		}
     }
    
}
?>