<?php

	if($form == "DEFAULT") {
		disableInputFocusStyle();
		echo '<link rel="stylesheet" type="text/css" href="modules/giwi400/css/giwi400.css?rand='.rand().'"  media="screen">';
		//echo '<script src="modules/giwi400/console_giwi400_script.php?rand='.rand().'"></script>';
		require_once 'console_giwi400_script.php';
		
		if(isset($string_xml) && $string_xml) {
			//echo "<div class='contGiwi400'>";
				//showArray(htmlentities( $string_xml));
				
				/*$xml = simplexml_load_string($string_xml, "SimpleXMLElement", LIBXML_NOCDATA);
				$json = json_encode($xml);
				$array = json_decode($json,TRUE);
				showArray($array);*/
			
			$giwi400Cmd->display();
				
			//echo "</div>";
			
			if(isset($_REQUEST['GIWI400_WINDOW'])) {
				$myField = new wi400InputHidden('GIWI400_WINDOW');
				$myField->setValue('si');
				$myField->dispose();
				
?>
				<script>
					/*var giwiInterval = '';
					function resizeWindowGiwi() {
						giwiInterval = setTimeout(function() {
							//se è 0 vuol dire che non c'è più la scrollBar
							//document.body.scrollWidth-document.body.clientWidth
						}, 0);
					}*/
				</script>
<?php 
			}
			
		}else {
			if(isset($_REQUEST['GIWI400_WINDOW'])) {
?>
				<script>
					//console.log('DEVO CHIUDERE LA FINESTRA CON WI400TOP');
					wi400top.doSubmit('CONSOLE_GIWI400', 'DEFAULT');
				</script>
<?php 
			}else {
				if(isset($_SESSION['GIWI400_ENDJOB']) && $_SESSION['GIWI400_ENDJOB']) {
					$cod_end = $_SESSION['GIWI400_ENDJOB'];
					
					echo "<center><label style='color: red; font-size: 23px;'>".$end_job_desc[$cod_end]." (".$cod_end.")</label></center>";
					unset($_SESSION['GIWI400_ENDJOB']);
				}else {
					echo "Non c'è il file<br>";
				}
			}
		}
		
	}else if($form == 'SYSTEM_BUTTON') {
		require_once 'console_giwi400_script.php';
		//showArray($_REQUEST);
		$useClass = getUseClass();
		$giwi400 = new $useClass($string_xml, 'SYSYEM_BUTTON', '');
		$giwi400->displayButton(true);
		
		
	}else if($form == 'INFO_MASCHERA') {
		
		echo '<link rel="stylesheet" type="text/css" href="modules/giwi400/css/giwi400.css?rand='.rand().'"  media="screen">';
		
		$useClass = getUseClass();
		$giwi400 = new $useClass($string_xml, 'INFO_MASCHERA', '');
		
		$datiTestata = $giwi400->getDatiTestata();

		$name_forms = $giwi400Cmd->getNameFormFromFiles($files);
		$datiTestata['NAME_FORMS'] = $name_forms;

		$detail = $giwi400Cmd->getDetailTitolo('GIWI400_TITOLO', $datiTestata);
		// Bottone Pulizia Cache e Reload Maschera sottostante
		$myButton = new wi400InputButton('PULISCI_RELOAD');
		$myButton->setLabel("Clear&Reload");
		$myButton->setAction($azione);
		$myButton->setForm("CLEAR_RELOAD");
		$detail->addButton($myButton);
		
		$myButton = new wi400InputButton('CUSTOM_TOOL_FIELD');
		$labelButton = "Visualizza";
		if(isset($_SESSION['GIWI400_CUSTOM_TOOL_FIELD'])) {
			$labelButton = 'Nascondi';
		}
		$myButton->setLabel($labelButton." dati field");
		$myButton->setAction($azione);
		$myButton->setForm("CUSTOM_TOOL_FIELD");
		$detail->addButton($myButton);
	
		$detail->dispose();
		
		echo "<br>";
		// Creazione SQL per lista
		$unionAll = "";
		$query = "";
		// Istanzio il manager
		$datix = explode("_",$name_forms[0]);
		$manager = new giwi400Manager($datix[1], $datix[2]);
		
		foreach ($name_forms as $key => $value) {
			$dati = explode("_",$value);
			$query .= $unionAll." SELECT ";
			$virg='';
			$form = $dati[0];
			$fill = $dati[1];
			$libl = $dati[2];
			foreach ($dati as $key1 => $value1) {
				$query .=$virg." '$value1' AS COL$key1";
				$virg=" , ";
			}
			$unionAll = " UNION ALL ";
			$query .=" FROM SYSIBM/SYSDUMMY1 ";
			// Se c'è un FORM collegato attacco anche quello
			$collegato = $manager->getDisplay()->getForm($form)->getFormatCollegato();
			if ($collegato!="") {
				$query.= " UNION ALL SELECT '$collegato' AS COL1, '$fill' AS COL2, '$libl' AS COL3  FROM SYSIBM/SYSDUMMY1 ";
			}
		}
		// Genera Lista
		$miaLista = new wi400List($azione."_FORM_LIST", !$isFromHistory);
		$miaLista->setQuery($query);
		// Colonne per manutenzione
		// FORMATO
		$col_param = new wi400Column("RECORD", "Attr.Formato", "", "CENTER");
		$col_param->setDecorator("ICONS");
		$col_param->setDefaultValue("MODIFICA");
		$col_param->setSortable(false);
		$col_param->setExportable(false);
		$col_param->setActionListId($azione."_RECORD");
		// CAMPI
		$col1_param = new wi400Column("CAMPI", "Attr.Campi", "", "CENTER");
		$col1_param->setDecorator("ICONS");
		$col1_param->setDefaultValue("MODIFICA");
		$col1_param->setSortable(false);
		$col1_param->setExportable(false);
		$col1_param->setActionListId($azione."_CAMPI");
		// Attributi PROTEZIONE/PERS.
		$col2_param = new wi400Column("CONFIGURAZIONE", "Configurazione WI400", "", "CENTER");
		$col2_param->setDecorator("ICONS");
		$col2_param->setDefaultValue("MODIFICA");
		$col2_param->setSortable(false);
		$col2_param->setExportable(false);
		$col2_param->setActionListId($azione."_CONFIGURAZIONE");
		
		// Genero Colonne
		$miaLista->setCols(array(
			new wi400Column("COL0","Formato"),
			new wi400Column("COL1","File"),
			new wi400Column("COL2","Libreria"),
			$col_param,
			$col1_param,
			$col2_param,
		));
		
		$miaLista->addKey("COL0");
		$miaLista->addKey("COL1");
		$miaLista->addKey("COL2");
		// Azioni
		// Attributi Record
		$action = new wi400ListAction();
		$action->setLabel("Attributi Record");
		$action->setId($azione."_RECORD");
		$action->setAction("DATI_GIWI400");
		$action->setGateway("TO_DETTAGLIO_CAMPI");
		$action->setForm("RECORD");
		$action->setTarget('WINDOW');
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		// Attributi Campi
		$action = new wi400ListAction();
		$action->setLabel("Attributi Campi");
		$action->setId($azione."_CAMPI");
		$action->setAction("DATI_GIWI400");
		$action->setGateway("TO_DETTAGLIO_CAMPI");
		$action->setForm("DETTAGLIO_CAMPI");
		$action->setTarget('WINDOW');
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		// Configurazione WI400
		$action = new wi400ListAction();
		$action->setLabel("Configurazione");
		$action->setId($azione."_CONFIGURAZIONE");
		$action->setAction("ABILITAZIONI_CAMPI_DETAIL");
		$action->setForm("UTENTI");
		$action->setGateway("GIWI400");
		$action->setTarget('WINDOW', 1000, 600);
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		$miaLista->dispose();
	}
	
	if($form == 'DEFAULT2') {
		
		if(isset($string_xml) && $string_xml) {
			//showArray(htmlentities( $string_xml));
				
			/*$xml = simplexml_load_string($string_xml, "SimpleXMLElement", LIBXML_NOCDATA);
			 $json = json_encode($xml);
			$array = json_decode($json,TRUE);
			showArray($array);*/
			
			$param = array(
				'V1AZIE' => '9000',
				'V1DATA' => '010519'
			);
				
			$giwi400 = new giwi400($string_xml);
			//$giwi400->display();
			//$giwi400->getDatiMaschera($maschera, $form)
			/*$rs = $giwi400->createFileXml();
			if(!$rs) {
				echo 'Errore scrittura file<br/>';
			}else {
				echo 'ok scrittura file<br/>';
			}*/
			//$giwi400->display();
		}else {
			echo "non c'è il file<br>";
		}
		//showArray($xml);
	}else if($form == 'DEFAULT3') {
		if(isset($string_xml) && $string_xml) {
			
			$giwi400Cmd->display();
			
		}
	}else if($form == 'DEFAULT4') {
		$file_path = '/www/993354FORMATO1.xml';
		if(file_exists($file_path)) echo "siiii";
		else echo "noooo";
		
		echo "<br>";
		
		$string_xml = file_get_contents($file_path, 0);
		
		$string_xml = utf8_encode($string_xml);
		
		//echo 'xml'.$string_xml;
		showArray(htmlentities($string_xml));
		
		$xml = new SimpleXMLElement($string_xml);
		echo "sono qua<br>";
		showArray($xml);
	}else if($form == "MAPPA") {
?>
		<style>
			#map {
				height: 610px;
			}
		</style>
		
		<div id="map"></div>

	    <script>
		    function initMap() {
		    	const map = new google.maps.Map(document.getElementById('map'), {
		    		zoom: 16, // Più alto è il numero e più zooma 
		    		gestureHandling: 'greedy' //Per togliere lo zoom usando il tasto ctrl
		    	});
		    	const geocoder = new google.maps.Geocoder();
		    		geocodeAddress(geocoder, map);
		    }
	
	        function geocodeAddress(geocoder, resultsMap) {
	        	const address = "<?=$indirizzo?>";
	        	geocoder.geocode({ address: address }, (results, status) => {
	        		if (status === "OK") {
	        			resultsMap.setCenter(results[0].geometry.location);
	        			new google.maps.Marker({
	        				map: resultsMap,
	        				position: results[0].geometry.location
	        			});
	        		} else {
	        			alert("Posizione non trovata: " + status);
	        		}
	        	});
	        }
	    </script>
	    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA13K4HDYe3YtsqjQZ9bxEWiYGmKtVUViI&callback=initMap"></script>
<?php 
	}