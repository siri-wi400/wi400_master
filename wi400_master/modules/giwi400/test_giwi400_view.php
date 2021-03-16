<?php

	if($form == "DEFAULT") {
		
		if(!isset($_REQUEST['GIWI400_WINDOW'])) {
			$detail = new wi400Detail("CONSOLE_GIWI400_NOME_PROGRAM");
			$detail->setColsNum(3);
			$detail->setTitle('Init programma');
			if(isset($_SESSION['GIWI400_OPEN_CLOSE'])) {
				$detail->setStatus(!$_SESSION['GIWI400_OPEN_CLOSE']);
			}
				
			$myField = new wi400InputText('NOME_PROGRAM');
			$myField->setLabel("Nome programma");
			$myField->setSize(20);
			$detail->addField($myField);
				
			$myField = new wi400InputCheckbox('GIWI400_GET_FIELD');
			$myField->setLabel("Attiva get Field");
			$myField->setChecked((isset($param['GIWI400_GET_FIELD']) && $param['GIWI400_GET_FIELD']) ? true : false);
			$myField->setOnChange("risottomettiForm('".$form."')");
			$detail->addField($myField);
				
			$myField = new wi400InputCheckbox('GIWI400_AFR');
			$myField->setLabel("Riconoscimento Campi");
			$myField->setChecked((isset($param['GIWI400_AFR']) && $param['GIWI400_AFR']) ? true : false);
			$myField->setOnChange("risottomettiForm('".$form."')");
			$detail->addField($myField);
				
			$myButton = new wi400InputButton('INIT_BUTTON');
			$myButton->setLabel("INIT");
			$myButton->setAction($azione);
			$myButton->setForm("INIT_JOB");
			$detail->addButton($myButton);
				
			$myButton = new wi400InputButton('START_PROGRAM');
			$myButton->setLabel("START_PROGRAM");
			$myButton->setAction($azione);
			$myButton->setForm("START_PROGRAM");
			$detail->addButton($myButton);
				
			if(isset($_SESSION['GIWI400_ID']) && $_SESSION['GIWI400_ID']) {
				//echo "ID_UNIVOCO LAVORO:".$_SESSION['GIWI400_ID']." ";
					
				//END JOB
				$myButton = new wi400InputButton('END_JOB');
				$myButton->setLabel("END_JOB");
				$myButton->setAction($azione);
				$myButton->setForm("END_JOB");
				$detail->addButton($myButton);
			}
				
			$detail->dispose();
				
			echo "<br>";
		}
		
		
	}
	
	require_once 'console_giwi400_view.php';