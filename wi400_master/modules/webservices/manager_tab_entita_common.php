<?php

	require_once 'manager_tab_entita_function.php';

	$enable_fields = 1;
	
	$enable_scheda_parametri = true;
	
	$parametri_testata = array(	'WSDL' => array('SIZE' => 90, 'TOOL' => wsdlTool('WSDL')), 
								'SoapAction' => array('TOOL' => soapActionTool('WSDL')),
								'FunctionGET' => array(),
								'SAVELogExtended' => array(),
								'RETURNParm' => array());
	$parametri_dettaglio = array(	'WSDL' => array('SIZE' => 90, 'TOOL' => wsdlTool('WSDL')), 
								'SoapAction' => array('TOOL' => soapActionTool('WSDL')),
								'FunctionGET' => array(),
								'SAVELogExtended' => array(),
								'RETURNParm' => array());
	
	$size_lista_io = array(
		"ASESEQ" => 2, 
		"ASENAM" => 5,
		"ASENA2" => 5,
		"ASEDES" => 10,
		"ASEGET" => 12,
		"ASEDFT" => 4
	);
	
	$width_window = 850;
	$height_window = 670;
	
	$onChange = "if(this.checked) {
					jQuery('#ASERIN').attr({
						class: 'inputtextDisabled',
						readonly: true
					});
					jQuery('#ASEPHP').attr({
						class: 'inputtext',
						readonly: false
					});
					
				}else {
					jQuery('#ASERIN').attr({
					  class: 'inputtext',
					  readonly: false
					});
					jQuery('#ASEPHP').attr({
						class: 'inputtextDisabled',
						readonly: true
					});
				}";
	
	$onChange_dati = "if(this.checked) {
						jQuery('#ASERSD').attr({
							class: 'inputtextDisabled',
							readonly: true
						});
					}else {
						jQuery('#ASERSD').attr({
						  class: 'inputtext',
						  readonly: false
						});
					}";