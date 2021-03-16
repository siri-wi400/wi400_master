<?php

	require_once 'ws_abilita_sys_inf_commons.php';

	$azione = $actionContext->getAction();
	
	$file_exists = false;
	if(file_exists($file_path)) {
		$file_exists = true;
	
		$parameters = array();
	
		$lines = file($file_path, FILE_SKIP_EMPTY_LINES);
		if(!empty($lines)) {
			foreach($lines as $line) {
				$line = trim($line);
	
				if($line=="")
//					break;
					continue;
	
				$elements = explode("=", $line);
	
				$val = trim($elements[1]);
				$val = str_replace('"', "'", trim($val));
				$parameters[$elements[0]] = prepare_string($val);
			}
//			echo "PARAMETERS:<pre>"; print_r($parameters); echo "</pre>";
		}
	}
	
	if($actionContext->getForm()=="DEFAULT") {

	}
	else if($actionContext->getForm()=="SAVE") {
		$sys_inf_array = array();
		if(wi400Detail::getDetailValue($azione."_PARAMS_DET","SYS_INF")!="")
			$sys_inf_array = wi400Detail::getDetailValue($azione."_PARAMS_DET","SYS_INF");
		
		if(!empty($sys_inf_array)) {
			$sys_inf_array = array_flip($sys_inf_array);
//			echo "SYS INF ARRAY:<pre>"; print_r($sys_inf_array); echo "</pre>";
			
			foreach($parameters as $key => $val) {
				if(array_key_exists($key, $sys_inf_array)) {
					$sys_inf_array[$key] = $val;
				}
			}
//			echo "SYS INF ARRAY:<pre>"; print_r($sys_inf_array); echo "</pre>";
			
			$c = 1001;
			foreach($sys_inf_array as $key => $val) {
				if($val<1001) {
					for($c;in_array($c, $sys_inf_array);$c++) {
//						echo "C: $c<br>";
					}
					$sys_inf_array[$key] = $c;
//					echo "KEY: $key - C: $c<br>";
					$c++;
				}
			}
			
			asort($sys_inf_array);
//			echo "SYS INF ARRAY:<pre>"; print_r($sys_inf_array); echo "</pre>";
			
			$params_txt = "";
			foreach($sys_inf_array as $key => $val) {
				$params_txt .= $key."=".$val."\r\n";
			} 			
//			echo "TXT: $params_txt<br>";
				
			if($params_txt!="") {
				// 'w': sovrascrive il file ; 'a': scrive in coda al testo già esistente nel file
				$file_handle = fopen($file_path, 'w');
				fwrite($file_handle, $params_txt);
				fclose($file_handle);
//				echo "WRITE<br>";
					
				$messageContext->addMessage("SUCCESS","Parametri salvati nel file");
			}
		}
		else {
			$messageContext->addMessage("ERROR","L'array dei parametri è vuoto");
		}
		
		$actionContext->onSuccess($azione, "DEFAULT");
		$actionContext->onError($azione, "DEFAULT", "", "", true);
	}