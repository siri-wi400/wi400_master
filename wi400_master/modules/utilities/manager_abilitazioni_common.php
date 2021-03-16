<?php

//	echo "AZIONE: ".$actionContext->getAction()."<br>";

	if($actionContext->getAction()=="GEST_ABIL_LIST_VEGA") {
		$tabella = "ZUSRLISA";

		$des_cols = array(
//			"USRCOS" => "Visualizzazione<br>Costi",
			"USRPRZ" => "Visualizzazione<br>Cessione",
			"USRMAR" => "Visualizzazione<br>Margine",
			"USRCES" => "Prezzo Cessione<br>standard",
			"USREXP" => "Abilitazione<br>Esportazione",		// abilita l'esportazione della lista
			"USRLGO" => "Abilitazione<br>Esportazione LGO",	// abilita l'esportazione del listino con barcode
			"USRSTCS" => "Cessione in<br>Esportazione LGO<br>Piattaforme",	// abilita la visione della cessione nell'esportazione del listino con barcode
			"USRSTCS_DP" => "Cessione in<br>Esportazione LGO",	// abilita la visione della cessione nell'esportazione del listino con barcode
			"USRLGG" => "Abilitazione<br>Esp. LGO Globale",	// se disabilitato l'esportazione del listino con barcode necessita di almeno un parametro di ricerca, altrimenti l'esportazione può essere anche globale
			"USRCFG" => "Configurazione<br>Lista",			// abilita la configurazione della lista
			"USRNRI" => "Modifica<br>numero righe",	 		// se la configurazione della lista è disabilitata, è possibile abilitare solo la gestione del numero di righe da visualizzare
			"USRFIL" => "Abilitazione<br>Filtri",			// abilita la possibilità di impostare dei filtri
			"USRCAR" => "Abilitazione<br>Carrello",
//			"USRPRE" => "Filtro<br>Preferiti",
//			"USRPUS" => "Utente riferimento<br>preferiti",
			"USRLIB" => "Listino con barcode<br>(Scanner)",		// @todo abilita lo Scanner, NON l'esportazione listino con barcode, sarebbe meglio cambiare il nome
			"USREXA" => "Esportazione<br>Listini Offerte AP",	// abilita l'esportazione PDF personalizzata dei listini offerte
			"USRACAG" => "Accesso<br>standard",					// accesso standard (si passa attraverso la pagina dei parametri), altrimenti accesso agevolato ai listini (filtri di default già impostati)
			"USREXR72" => "Esportazione Record 72 Bolle",		// abilita l'esportazione del record 72 delle bolle (Lista dettaglio bolle)
			"USRCESPR" => "Visualizzazione<br>Cessione<br>Precedente",	// abilita la visione della cessione precedente (Lista dettaglio bolle)
			"USRPRZV" => "Visualizzazione<br>Prezzi di Vendita",		// abilita la visione dei prezzi di vendita in Listini Vega
			"USRECSV" => "Esportazione Cessioni Variate",		// abilta l'esportazione delle cessioni variate in Listini Vega
			"USREIMG" => "Esportazione foto"					//Abilita l'esportazione delle foto degli articoli presenti nel listino
		);
	}
	else if($actionContext->getAction()=="GEST_ABIL_STAT_IMM") {
		$tabella = "ZUSRIMMA";
		
		$des_cols = array(
			"USRGRA" => "Abilitazione<br>Grafici",	// se abilitato togliere la barra in formato grafico sul margine (ultima colonna) e i grafici
			"USRLOC" => "Ente<br>utente",			// se abilitato il campo Negozio sarà bloccato e sarà impostato con l’ente legato al profilo
			"USRGRP" => "Negozi in<br>società",		// se abilitato l'ente potrà interrogare solo negozi appartenenti alla proprio società
			"USRCFG" => "Configurazione<br>Lista",	// abilita la configurazione della lista
			"USRNRI" => "Modifica<br>numero righe",	// se la configurazione della lista è disabilitata, è possibile abilitare solo la gestione del numero di righe da visualizzare
		);	
	}
//	echo "AZIONE: ".$actionContext->getAction()." TABELLA: $tabella - COLS:<pre>"; print_r($des_cols); echo "</pre>";
	
	$abil_cols = array_keys($des_cols);
//	echo "ABIL COLS:<pre>"; print_r($abil_cols); echo "</pre>";
	
	function notNull($value, $valoreOriginale=null) {
//		echo "NEW VAL: $value - VAL ORIG: $valoreOriginale";
	
		if (!isset($value) || is_null($value) || $value =="") {
			if (isset($valoreOriginale) && !is_null($valoreOriginale) && $valoreOriginale !="") {
				return $valoreOriginale;
			}
			return "N";
		}
		else {
			return $value;
		}
	}