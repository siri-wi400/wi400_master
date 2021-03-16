<?php
if(in_array($actionContext->getForm(),array("DEFAULT"))) {
	//showArray($row);
	if($row) {
		$modifyAction = new wi400Detail('chglng');
		$modifyAction->setTitle("Modifica lingua");
		$modifyAction->isEditable(true);
		// Language
		$mySelect = new wi400InputSelect('LANGUAGE');
		$mySelect->setLabel(_t('USER_LANGUAGE'));
		$mySelect->setFirstLabel(_t('TYPE_SELECT'));
		createLanguageMenu($mySelect);
		$mySelect->setValue($row['LANGUAGE']);
		$mySelect->addValidation("required");
		$modifyAction->addField($mySelect);
				
		$myButton = new wi400InputButton('MODIFY_BUTTON');
		$myButton->setLabel(_t('UPDATE'));
		$myButton->setAction($azione);
		$myButton->setForm("MODIFICA");
		$myButton->setValidation(true);
		$modifyAction->addButton($myButton);
		
		$modifyAction->dispose();
	}else {
		echo "Utente non trovato";
	}		
}
function createLanguageMenu($mySelect) {
	// scan the lang directory and create the menu based on the language files present
	global $base_path;
	
	$path = "$base_path/lang";
	
	$dir = opendir ( "$path" );
	while ( $thafile = readdir ( $dir ) ) {
		if (is_file ( "$path/$thafile" ) && preg_match ( "/.lang\.php$/", "$path/$thafile" )) {
			$thafile = str_replace ( ".lang.php", "", $thafile );
			$mySelect->addOption ( "$thafile", "$thafile" );
		}
	}
}
?>