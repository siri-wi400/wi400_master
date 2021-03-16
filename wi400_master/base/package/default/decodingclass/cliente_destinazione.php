<?php

	class cliente_destinazione extends wi400Decoding {
	
	public function decode() {
		global $connzend, $decodeMemory;
		
		$classe = get_class ( $this );
		
		// Reperisco array parametri
		$decodeParameters = $this->getDecodeParameters ();
		
		// Se codice destinazione blank = Tutte
		if ($this->getFieldValue () == '') {
			return 'Tutte le destinazioni';
		} else {
			
			$ancld00i = new wi400Routine ( 'ANCLD00I', $connzend );
			$do = $ancld00i->load_description ( "file", "ANCLD0DP" );
			if (! $do) {
				echo "descrittore  non trovato!!  <br>";
			}
			$do = $ancld00i->prepare ();
			if (! $do) {
				echo "prepare  fallito!!  <br>";
			}
			
			// Imposta campo decodifica
			$decodeColumn = "RAGSO10D";
			if (isset ( $decodeParameters ["COLUMN"] )) {
				$decodeColumn = $decodeParameters ["COLUMN"];
			}
			
			// Imposta data di validità
			$dataValidita = "";
			if (isset ( $decodeParameters ['DATA_VALIDITA'] )) {
				$dataValidita = $decodeParameters ['DATA_VALIDITA'];
			} else if (isset ( $_POST ['DATA_VALIDITA'] )) {
				$dataValidita = formattaData ( $_POST ['DATA_VALIDITA'] );
			} else {
				$dataValidita = $_SESSION ['data_validita'];
			}
			$dataValiditaL = dateFormat ( substr ( $dataValidita, 6, 2 ), substr ( $dataValidita, 4, 2 ), substr ( $dataValidita, 0, 4 ), "-", "S" );
			
			// Reperisco da memoria tampone o da chiamata
			if (isset ( $decodeMemory [$classe] [$this->getFieldValue ()] [$dataValidita] )) {
				$row = $decodeMemory [$classe] [$this->getFieldValue ()] [$dataValidita];
			} else {
				// imposto paraemtri
				$ancld00i->clearDS ( "FILEDSIN" );
				$ancld00i->clearDS ( "FILEDSOU" );
				$ancld00i->setDSParm ( "ANCLD0DP", "CDAZPR0D", $_SESSION ['codice_azienda'] );
				$ancld00i->setDSParm ( "ANCLD0DP", "CDCLIE0D", $decodeParameters ["CDCL"] );
				$ancld00i->setDSParm ( "ANCLD0DP", "CDDEST0D", $this->getFieldValue () );
				$ancld00i->setDSParm ( "ANCLD0DP", "DTINSE0D", $dataValiditaL );
				
				$ancld00i->setDSParm ( "FILEDSIN", "FILEMDLEDB", "01" );
				$ancld00i->setDSParm ( "FILEDSIN", "FILELEVNDB", "N" );
				$ancld00i->setDSParm ( "FILEDSIN", "FILENUMKEY", 3 );
				$ancld00i->setDSParm ( "FILEDSIN", "FILEPROGRA", 'ANCLD00I' );
				
				// echo "chiamo rooutine ANCLD00I !!  <br>";
				// echo $decodeParameters ["CDCL"];
				//die();

				//Eseguo chiamata
				$do = $ancld00i->call ();
				if (! $do) {
					echo "call ancld00i fallito!!  <br>";
				}
				
				//Lettura output
				$row = $ancld00i->get ( 'ANCLD0DP' );
				if ($ancld00i->getDSParm ( 'FILEDSOU', 'FILECDRILT' ) != '0') {
					$this->setFieldMessage ( _t ( "VALORE_DI" ) . $this->getFieldLabel () . _t ( "NON_VALIDO" ) );
					return false;
				}
				$decodeMemory [$classe] [$this->getFieldValue ()] [$dataValidita] = $row;
			}
			
			// Ritorno
			if (! isset ( $row [$decodeColumn] )) {
				$this->setFieldMessage ( _t ( "VALORE_DI" ) . $this->getFieldLabel () . _t ( "NON_VALIDO" ) );
				return false;
			} else {
				$this->setDecodeObject ( $row );
				return $row [$decodeColumn];
			}
		
		}
	}

}

?>