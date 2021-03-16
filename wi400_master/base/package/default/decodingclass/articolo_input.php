<?php

	class articolo_input extends wi400Decoding {

	public function decodeFields(){			
			$articolo  = $this->getDecodeObject();
			return array($this->getFieldId() => $articolo ["CDARTI1A"]);		
		}
		
		public function decode(){
			global $connzend, $decodeMemory;
			 
			$classe = get_class($this); 			
			
			$g323rg01 = new wi400Routine('G323RG01', $connzend);
			$g323rg01->load_description("g323");
			$g323rg01->prepare();
				     
		    // Reperisco parametri input
		      $decodeParameters = $this->getDecodeParameters();
		        
		    // Imposto la colonna da ritornare come descrizione 
		    $decodeColumn = "DSARTI1A";
			if (isset($decodeParameters["COLUMN"])){
				$decodeColumn = $decodeParameters["COLUMN"];
			}	

		    // Imposto la data di validità da utilizzare
		    $dataValidita = "";
		    if (isset($decodeParameters['DATA_VALIDITA'])){
		    	$dataValidita = $decodeParameters['DATA_VALIDITA'];
		    }else if (isset($_POST['DATA_VALIDITA'])){
		    	$dataValidita = formattaData($_POST['DATA_VALIDITA']);
		    }else{
		    	$dataValidita = $_SESSION['data_validita'];
		    }
		    $dataValiditaL = dateFormat(substr($dataValidita,6,2), substr($dataValidita,4,2), substr($dataValidita,0,4), "-", "S") ;

		    // Controllo se ho già decodificato il codice/data di validità
		    if (isset($decodeMemory[$classe][$this->getFieldValue()][$dataValidita])) {
		    	$row = $decodeMemory[$classe][$this->getFieldValue()][$dataValidita];
		    	//echo "<br>Found in Memory:".$classe;
		    } else {
		    	
		    // Setto parametri DS per call
		        $g323rg01->clearDS("G323DSIN");
		        $g323rg01->clearDS("G323DSOU");
		        $g323rg01->clearDS("ANART1AP");
		        
		        $g323rg01->setDSParm("G323DSIN", "G323CDARTI", $this->getFieldValue());
		        $g323rg01->setDSParm("G323DSIN", "G323DTVALI", $dataValiditaL);
		        $g323rg01->setDSParm("G323DSIN", "G323CDAZPR", $_SESSION['codice_azienda']);
		        
		        // Chiamo il programma
		        $do = $g323rg01->call();
		    	if (!$do) {
		             echo "call g323rg01 fallito!!";
		        }
		        // Recupero il tracciato record del file anagrafico
				$row = $g323rg01->get('ANART1AP');		
				
				// Controllo il flag di ritorno
		    	if ($g323rg01->getDSParm('G323DSOU', 'G323ERRORE')=='S') {
					$this->setFieldMessage(_t("VALORE_DI").$this->getFieldLabel()._t("NON_VALIDO"));
					return false;				
				}	
				// Valorizzo la memoria tampone con le decodifiche			
				$decodeMemory[$classe][$this->getFieldValue()][$dataValidita] = $row;
						    	//echo "<br>Decoded:".$classe;
		    }
		    // Controllo se c'è la colonna da ritornare
			if (!isset($row[$decodeColumn])){ 
			    // Setto il messaggio di errore da mostrare a video
				$this->setFieldMessage(_t("VALORE_DI").$this->getFieldLabel()._t("NON_VALIDO"));
				// Ritorno false per dire che è andata male
				return false;
			// Se tutto bene ...	
			}else{
			    // Inserisco nella classe il tracciato del file
				$this->setDecodeObject($row);
				// Ritorno la descrizione da mostrare a video
				return $row[$decodeColumn];
			}
		}
		
	}

	?>