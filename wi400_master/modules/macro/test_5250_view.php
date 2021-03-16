<?php
	$azioniDetail = new wi400Detail('TEST_5250', false);


	$myButton = new wi400InputButton('5250_OPEN');
	$myButton->setLabel("SESSIONE i5");
	$myButton->setAction("I5_SESSION&TYPE=test_5250");
	$myButton->setTarget("WINDOW", 270, 240);
	$myButton->setValidation(False);
	$azioniDetail->addButton($myButton);
	
	$azioniDetail->dispose();
?>
		