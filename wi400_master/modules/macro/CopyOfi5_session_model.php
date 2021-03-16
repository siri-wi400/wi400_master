<?php
    
	$type = $_GET['TYPE'];
	
	
	
	$path = "c:\\5250OPEN\\";
	$classFile = p13n('/modules/macro/class/'.$type.'.php');
	require_once $classFile;
    $macroClass = new $type();
    $macroClass->setType($type);
    $macroClass->setPath($path);
    
    
    foreach ($_GET as $key => $value){
	    $macroClass->addParameter($key, $value);
	}
    
	$zipName = $macroClass->generate();