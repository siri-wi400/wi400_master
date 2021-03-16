<?php

	// anagraphic customers list with search filter
   	$_myList = new wi400List("EXAMPLE1_LIST", true);
	$_myList->setFrom("ZWIDEMO".$settings['i5_sep']."ANCL200F");
    // show all columns from tabel
	$cols = getColumnListFromTable("ANCL200F", "ZWIDEMO");
	$_myList->setCols($cols);

	// Add fast filter
	$_myFilter = new wi400Filter("RASCL","Nominativo","STRING");
	$_myFilter->setFast(true);
	$_myList->addFilter($_myFilter);
	// Add advanced filter
	$_myFilter = new wi400Filter("CDCLI", "Code","STRING");
	$_myList->addFilter($_myFilter);
	$_myFilter= new wi400Filter("LOCCL", "City","STRING");
	$_myList->addFilter($_myFilter);
		
	// data rendering on HTML 
	$_myList->dispose();
	
	$phpCode = new wi400PhpCode();
	$phpCode->addFile($moduli_path."/example/example2_view.php");
	$phpCode->dispose();