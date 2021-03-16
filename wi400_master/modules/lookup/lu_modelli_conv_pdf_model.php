<?php

	require_once $moduli_path.'/pdf/modelli_conv_pdf_common.php';
	
//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
	$azione = $actionContext->getAction();
	
	$classi_conv_array = get_classi_conv();
	
	subfileDelete("LU_MODELLI_CONV_PDF_LIST");
	
	$subfile = new wi400Subfile($db, "LU_MODELLI_CONV_PDF_LIST", $settings['db_temp'], 20);
	$subfile->setConfigFileName("LU_MODELLI_CONV_PDF_LIST");
	$subfile->setModulo("lookup");
	
	$subfile->addParameter("CLASSI_CONV", $classi_conv_array);
	
	$subfile->setSql("*AUTOBODY");