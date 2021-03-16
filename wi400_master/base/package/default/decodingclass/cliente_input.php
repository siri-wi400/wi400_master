<?php

	class cliente_input extends wi400Decoding {

		public function decodeFields(){
			$cliente = $this->getDecodeObject();
			return array($this->getFieldId() => $cliente["CDCLIE7C"]);
		}
		
		public function decode(){
			global $connzend, $decodeMemory;
			 
			$classe = get_class($this); 			
			
			$g326rg01 = new wi400Routine('G326RG01', $connzend);
			$g326rg01->load_description("g326");
			$g326rg01->prepare();
				     
		    // Reperisco parametri input
		      $decodeParameters = $this->getDecodeParameters();
		        
		    // Imposto la colonna da ritornare come descrizione 
		    $decodeColumn = "RAGSO17C";
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
		        $g326rg01->clearDS("G326DSIN");
		        $g326rg01->clearDS("G326DSOU");
		        $g326rg01->clearDS("ANCLI7CP");
		        $g326rg01->clearDS("ANCLD0DP");
		        
		        $g326rg01->setDSParm("G326DSIN", "G326CDCLIE", $this->getFieldValue());
		        $g326rg01->setDSParm("G326DSIN", "G326DTVALI", $dataValiditaL);
		        $g326rg01->setDSParm("G326DSIN", "G326CDAZPR", $_SESSION['codice_azienda']);
		        
		        // Chiamo il programma
		        $do = $g326rg01->call();
		    	if (!$do) {
		             echo "call g326rg01 fallito!!";
		        }
		        // Recupero il  record del file anagrafico
				$row = $g326rg01->get('ANCLI7CP');		
					    	
				// Controllo il flag di ritorno
		    	if ($g326rg01->getDSParm('G326DSOU', 'G326ERRORE')=='S') {
					$this->setFieldMessage(_t("VALORE_DI").$this->getFieldLabel()._t("NON_VALIDO"));
					return false;				
				}	
				// Valorizzo la memoria tampone con le decodifiche			
				$decodeMemory[$classe][$this->getFieldValue()][$dataValidita] = $row;
						    	
		    }

	    
		    // Imposto ritorno
			if (!isset($row[$decodeColumn])){ 
				$this->setFieldMessage(_t("VALORE_DI").$this->getFieldLabel()._t("NON_VALIDO"));
				return false;	
			}else{			    
				$this->setDecodeObject($row); // Inserisco nella classe il tracciato del file
				return $row[$decodeColumn];// Ritorno la descrizione da mostrare a video
			}
		}
		
	}

	?>