<?php

/**
 * @name wi400ActionContext
 * @desc Classe per la gestione delle azioni
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.01 08/09/2009
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400ActionContext extends wi400Action {


    public function setLabel($label){
    	global $history;
    	parent::setLabel($label);
    	if(isset($history))
    		$history->update($this);
    }
    
}
?>