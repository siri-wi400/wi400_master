<?php

/**
 * @name wi400History 
 * @desc Classe per la generazione della history delle azioni seguite
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.01 30/11/2009
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400History {

	private $steps;
	private $actions;
	private $tabletAction;
	private $fromModel = false;
	
	/**
	 * Costruttore della classe
	 *
	 */
    public function __construct(){
    	$this->steps = array();
    	$this->actions = array();
    }
    
    /**
	 * Aggiunta di un'azione alla history delle azioni seguite
	 * 
	 * @param wi400Action $wi400Action	: l'oggetto azione da aggiungere alla history
	 * @param string $stepName			: nome dello step da aggiungere alla history
	 */
    public function add(wi400Action $wi400Action, $stepName = ""){
    	if ($stepName == ""){
    		$stepName = $wi400Action->getAction();
    		if ($wi400Action->getForm() != ""){
    			$stepName = $stepName."_".$wi400Action->getForm();
    		}
    	}
    	
    	$this->steps[] = $stepName;
    	
    	if (isset($this->actions[$stepName])){
    		$searchKey = 0;
    		foreach($this->steps as $actionName){
    			$searchKey++;
    			if ($actionName == $stepName){
    				array_splice($this->steps, $searchKey);
    				array_splice($this->actions, $searchKey);
    				wi400Session::save("history", "BREAD_CRUMBS", $this);
    				//$_SESSION["WI400_HISTORY"] = $this;
    				break;
    			}
    		}
    	}
    	else{
    		$this->actions[$stepName] = $wi400Action;
    		wi400Session::save("history", "BREAD_CRUMBS", $this);
    		//$_SESSION["WI400_HISTORY"] = $this;
    	}
    }
    public function delete() {
    	wi400Session::delete("history", "BREAD_CRUMBS");
    	$this->steps = array();
    	$this->actions = array();
    }
    
	public function update(wi400Action $wi400Action){
    	
    	$stepName = $wi400Action->getAction()."_".$wi400Action->getForm();
    	
    	if (isset($this->actions[$stepName])){
    		$this->actions[$stepName] = $wi400Action;
    		wi400Session::save("history", "BREAD_CRUMBS", $this);
    	}
    	
    }
    /**
     * Rimozione dell'ultimo step inserito
     */
    public function removeLast(){
    	 
    	array_pop($this->steps);
    	array_pop($this->actions);
   		wi400Session::save("history", "BREAD_CRUMBS", $this);
   	 
    }
    /**
     * Aggiunta dell'azione corrente alla history delle azioni seguite
     *
     */
    public function addCurrent($label = ""){
    	global $actionContext;
    	
    	if ($label != "") $actionContext->setLabel($label);
    	if ((isset($_REQUEST["DECORATION"]) && $_REQUEST["DECORATION"] == "clean") || isset($_REQUEST['WI400_IS_WINDOW'])
    			|| $actionContext->getType() == "B"){
    		// Prevent addCurrent
    	} elseif (isset($_REQUEST['WI400_IS_IFRAME'])) {
    		$this->add($actionContext);
    	} else{
    		$this->add($actionContext);
    	}
    }
    
    /**
	 * Ottenere il nome uno specifico step della history
	 * (costituito dalla concatenazione del nome dell'azione e di quello del form con _)
	 * 
	 * @param int $index : indice progressivo dello step a cui si vuole fare riferimento
	 * 
	 * @return string 	Ritorna il nome di uno specifico step della history
	 */
    public function getStep($index){
    	return $this->steps[$index];
    }
    
    /**
	 * Ottenere l'elenco degli steps della history
	 * 
	 * @return array 	Ritorna l'elenco degli steps della history
	 */
    public function getSteps() {
    	return $this->steps;
    }
    /**
     * addTabletAction() Aggiunge una azione iniziale per tornare al menu tablet
     * @param unknown $tabletAction
     */
    public function addTabletAction($tabletAction) {
    	$this->tabletAction = $tabletAction;
    }
    /**
     * getTabletAction() Restituisce l'azione iniziale del menu tablet
     * @return unknown $tabletAction
     */
    public function getTabletAction() {
    	return $this->tabletAction;
    }
    /**
	 * Ottenere l'azione di un determinato step
	 * 
	 * @param string $stepName : nome dello step della history a cui si vuole fare riferimento
	 * 
	 * @return Object 	Ritorna l'azione dello step a cui si è fatto riferimento
	 */
    public function getAction($stepName) {
    	return $this->actions[$stepName];
    }
    
    /**
     * Se è a true vengo mantenuti i valori settati nel model
     * 
     * @param boolean $val
     */
    public function setFromModel($val) {
    	$this->fromModel = $val;
    }
    
    /**
     * Se è a true vengo mantenuti i valori settati nel model
     *
     * @param boolean $val
     */
    public function getFromModel() {
    	return $this->fromModel;
    }
    public function getEntry() {
    	return count($this->steps);
    }
    /**
	 * Visualizzazione della history delle azioni seguite
	 * 
	 */
    public function dispose($noHome=False){
    	global $actionContext,$breadCrumbs, $wi400Wizard, $settings;

    	$class_mobile = "";
    	if(isset($_SESSION['NAVIGAZIONE_TABLET_ATTIVA'])) {
    		$class_mobile = "mobile";
    	}
    	echo '<div class="breadCrumbHolder module"><div id="breadCrumb0" class="breadCrumb module '.$class_mobile.'"><ul id="wi400_navbar" class="'.$class_mobile.'">';
    	if ($noHome==True && count($this->steps)==0) {
    		return false;
    	}
    	if ($noHome==False) {
	    	if ($this->getTabletAction()!="") {
	    		$onClick = "doSubmit('".$this->getTabletAction()."');";
	    		echo "<li><span class=\"active $class_mobile\" style=\"cursor: pointer;\" onClick=\"".$onClick."\"></span></li>";
	    	} else {
	    		if(isset($_SESSION['NAVIGAZIONE_TABLET_ATTIVA'])) {
	    			$onClick = "doSubmit('MENU_TABLET','&LCK_DLT=true', false, true);";
	    		}else {
	    			$onClick = "doSubmit('','&LCK_DLT=true', false, true);";
	    		}
	    		echo "<li><span class=\"active $class_mobile\" style=\"cursor: pointer;\" onClick=\"".$onClick."\"></span></li>";
	    	}
    	}
    	
    	if (count($this->steps)>0){
    		$actionCounter = 0;
    		foreach ($this->steps as $actionName){
    			$actionCounter++;
    			/*if ($noHome==True && $actionCounter==1) {
    				continue;
    			}*/
    			$action = $this->actions[$actionName];
    			 
    			// Aggiungo parametro per identificare navigazione history
    			if ($actionCounter < count($this->steps)){
    				$gatewaySuffix = "";
    				if ($action->getGateway() != ""){
    					$gatewaySuffix = "&g=".$action->getGateway();
    				}
    				$onClick = 'doSubmit("'.$action->getAction().$gatewaySuffix.'&HST_NAV=true","'.$action->getForm().'", false, true)';
    				echo "<li><a class='$class_mobile' style=\"cursor: pointer;\" onClick='javascript:".$onClick."'>".$action->getLabel()."</a></li>";
    			}
    			else{
    				echo '<li><a class="label '.$class_mobile.'" style=\"cursor: pointer;\">'.$action->getLabel().'</a></li>';
    			}
    		}
    	
    	}
    	/*else{
    		echo '<li><a class="label" style="cursor: pointer;">'.$actionContext->getLabel().'</a></li>';
    	}*/
    	$endEle="1";
    	$begEle="0";
    	if (isset($settings['history_endeleopen']) && $settings['history_endeleopen']!="") {
    		$endEle = $settings['history_endeleopen'];
    	}
    	if (isset($settings['history_begeleopen']) && $settings['history_begeleopen']!="") {
    		$begEle = $settings['history_begeleopen'];
    	}
    	echo '</ul></div></div>';
    	echo '<script>jQuery(document).ready(function()
            {
                jQuery("#breadCrumb0").jBreadCrumb({endElementsToLeaveOpen: "'.$endEle.'", beginingElementsToLeaveOpen: "'.$begEle.'"});
    	});</script>';
    }

}

?>