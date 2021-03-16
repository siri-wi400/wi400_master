<?php

	$wi400CurrentStep = $wi400Wizard->getCurrentStep();

	$wi400WizardHeader = new wi400Detail("WIZARD_HEADER",true);
	$wi400WizardHeader->setTitleCss("wizard-header");
	$wi400WizardHeader->setTitle($wi400CurrentStep["title"]);
	$wi400WizardHeader->setButtons($wi400Wizard->getFooterButtons());
	
	$wi400Text = new wi400Text("INFO");
	$wi400Text->setValue($wi400CurrentStep["info"]);
	$wi400WizardHeader->addField($wi400Text);
	
	$wi400WizardHeader->dispose();
	
	$wi400Spacer = new wi400Spacer();
	$wi400Spacer->dispose();
?>