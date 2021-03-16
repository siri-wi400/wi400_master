<?php

/**
 * @name wi400BreadCrumbs 
 * @desc Classe per la visualizzazione nella history del percorso delle azioni seguite
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 26/08/2008
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400BreadCrumbs {

	private $breadCrumbs;
	
	/**
	 * Costruttore della classe
	 * 
	 */
    public function __construct(){
    	$this->breadCrumbs = array();
    }
    
    /**
	 * Aggiunta di un'azione alla lista
	 * 
	 * @param wi400Action $wi400Action	: l'oggetto azione da aggiungere alla lista
	 */
    public function addBreadCrumb(wi400Action $wi400Action){
    	$this->breadCrumbs[] = $wi400Action;
    }
    public function dispose(){
    	echo $this->getHtml();
    }
    /**
	 * Visualizzazione nella history della lista delle azioni seguite
	 * 
	 */
    public function getHtml(){
    	/*foreach ($this->breadCrumbs as $action){
    		$onClick = 'doSubmit("'.$action->getAction().'","'.$action->getForm().'",false,true)';
    		echo "<a class='breadCrumbs' href='javascript:".$onClick."'>".$action->getLabel()."</a> : ";
    	}*/
    	$bcHtml = '<ul class="breadcrumbsn">';
    	foreach ($this->breadCrumbs as $action){
    		$onClick = 'doSubmit(\''.$action->getAction().'\',\''.$action->getForm().'\',false,true)';
    		$bcHtml = $bcHtml.'<li><a href="#">'.$action->getLabel().'</a></li>';
    	}
    	$bcHtml = $bcHtml.'</ul>';
    	 
    	return $bcHtml;
    	 
    }

}

?>