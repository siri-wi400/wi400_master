<?php

	require_once 'console_giwi400_commons.php';

	if($actionContext->getGateway() == 'FROM_MENU') {
		$nome_programma = $_REQUEST['AZIONE_5250'];
		
		unset($_SESSION['GIWI400_FILE']);
		$_SESSION['GIWI400_CURRENT_FORM'] = '';
		// Verifico se l'ID sessione esiste ed è quello giusto per il programma
		if(isset($_SESSION['GIWI400_ID'])) {
			$id = $_SESSION['GIWI400_ID'];
			// Se c'è un ID di riferimento provo a usarlo
			if (isset($_SESSION['GIWI400_MULTI_PGM'][$nome_programma])) {
				$id = $_SESSION['GIWI400_MULTI_PGM'][$nome_programma];
				$_SESSION['GIWI400_ID']=$id;
			} else {
				unset($id);
				unset($_SESSION['GIWI400_ID']);
			}
			// Controllo che l'ID sia utilizzabile
			if ($id) {
			$esito = checkIdGiwi($id, $nome_programma);
				if ($esito == False) {
					unset($_SESSION['GIWI400_ID']);
					unset($_SESSION['GIWI400_MULTI_PGM'][$nome_programma]);
					// Dovrei anche ammazzare il lavoro ..
				}
			}
		}
		if(!isset($_SESSION['GIWI400_ID'])) {
			list($id, $id_file) = getGiwi400Id($nome_programma);
			$_SESSION['GIWI400_ID'] = $id;
			$output = readCoda($id);
			//$_SESSION['GIWI400_FILE'] = $id_file;
		}
		
		if($id) {
			if(!isset($_SESSION['GIWI400_MULTI_FILE'][$id])) {
				//Start program
				$_SESSION['GIWI400_CURRENT_FORM'] = '';
				$_SESSION['GIWI400_NOME_AZIONE'] = $nome_programma;
				
				$progressivo = '';
				$operazione  = 'RUNAZI';
				
				$aziGiwi = rtvAzione5250($nome_programma);
				
				$output = writeCoda($progressivo, $id, $operazione, $aziGiwi['AZIONE']);
				//error_log('OUTPUT START PROGRAM WRITE: '.$output);
				
				//LETTURA CODA
				$output = readCoda($id);
				
				//error_log('READ_OUTPUT: '.$output);
				
				$dati = getDatiOutput($output);
				
				$file = $dati['FILE_PATH'];
				
				$_SESSION['GIWI400_MULTI_FILE'][$id] = $file;
			}else {
				$file = $_SESSION['GIWI400_MULTI_FILE'][$id];
			}
				
			$_SESSION['GIWI400_FILE'] = $file;
			
		}else {
			unset($_SESSION['GIWI400_FILE']);
		}
		
		$actionContext->gotoAction('CONSOLE_GIWI400', 'DEFAULT', '', true, false);
	}