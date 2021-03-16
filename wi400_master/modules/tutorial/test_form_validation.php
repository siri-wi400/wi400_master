<?php

	/*
	* 
	* Tutorial esempio per validazione personalizzata form 
	* 
	*  
	* */


	if (isset($_POST["NOME"]) &&  strtoupper($_POST["NOME"]) != "MASSIMILIANO"){
		
		$messageContext->addMessage("ERROR", "Il campo deve essere Massimiliano", "NOME");
		$messageContext->addMessage("ERROR", "Ci sono errori nel form. Correggere", "");

	}

?>