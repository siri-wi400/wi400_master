<?php

	$tipoOggetto = $_REQUEST["OBJTYPE"];
	
    // Libreria Facoltativa
    $libreria="";
	if (isset($_REQUEST["LIBRERIA"])) {
		$libreria = $_REQUEST["LIBRERIA"];    	
    }
    
    if ($libreria =="") {
    	$libreria = '*ALL';
    }
    
    // Nome Oggetto
    $nome='';
	if (isset($_REQUEST["NOME"])) {
		$nome = $_REQUEST["NOME"];    	
    }
    
    if ($nome =="") {
    	$nome = '*ALL';
    }
    
	$actionContext->setLabel(_t('LIST').$tipoOggetto._t('OFIBMI'));