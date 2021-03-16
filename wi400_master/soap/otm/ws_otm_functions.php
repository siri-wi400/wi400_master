<?php
function retriveMessage($state)
{
	$message = '0';
	switch ($state) {
		case '0':
			$message="File ricevuto in modo corretto";
			break;
		case '1':
			$message="PARAMETRI ERRATI";
			break;
		case '2':
			$message="NESSUN DATO REPERITO";
			break;
		case '3':
			$message="SERVER DOWN";
			break;
		case '4':
			$message="ERRORE ALLOCAZIONE MEMORIA";
			break;
		case '5':
			$message="CODICE NON TROVATO";
			break;
		case '6':
			$message="ERRORE RICHIAMO ROUTINE SERVIZIO RPG";
			break;
		case '7':
			$message="ENTITA E/O SEGMENTO NON TROVATI";
			break;
		case '8':
			$message="XML PASSATO NON VALIDO";
			break;
		case '9':
			$message="ERRORE GENERICO";
			break;
		case '10':
			$message="XML NON CONTIENE PARAMETRI VALIDI";
			break;
		case '11':
			$message="SERVER OCCUPATO. RITENTARE L'OPERAZIONE PIU TARDI";
			break;
		case '12':
			$message="SERVIZIO DISABILITATO. RITENTARE L'OPERAZIONE PIU TARDI";
			break;
		case '13':
			$message="IMPOSSIBILE CARICARE IL SISTEMA INFORMATIVO CONTROLLARE I PARAMETRI";
			break;
		case '14':
			$message="DATI PASSATI INCOMPLETI. VERIFICARE IL CONEUTO DELL'XML";
			break;
		case '15':
			$message="VERIFICARE GLI ERRORI E LIVELLO DI DETTAGLIO";
			break;
		case '16':
			$message="SISTEMA INFORMATIVO NON ABILITATO PER WS";
			break;
		case '17':
			$message="ERRORE DI AUTENTICAZIONE";
			break;
		case '18':
			$message="CREDENZIALI DI ACCESSO NON FORNITE";
			break;
		case '19':
			$message="MANCA ID ORDINE";
			break;
		case '20':
			$message="ID ORDINE GIA ELABORATO";
			break;
		case '21':
			$message="ERRORE AGGIORNAMENTO LOG";
			break;
		case '22':
			$message="ERRORE SCRITTURA FILE ORDINI";
			break;
		case '23':
			$message="ERRORE SUL CONTEGGIO DEI BYTE";
			break;
		case '24':
			$message="ERRORE RICHIAMO PROGRAMMA ELABORAZIONE ORDINE";
			break;
		case '25':
			$message="ERRORE TRA I CONTROLLI DEI PARAMETRI";
			break;
		case '26':
			$message="ID GIA' ELABORATO";
			break;
		case '27':
			$message="HASHCODE NON PRESENTE TRA I PARAMETRI";
			break;
		case '28':
			$message="SERIALE NON PRESENTE TRA I PARAMETRI";
			break;
		case '29':
			$message="TIMESTAMP NON PRESENTE TRA I PARAMETRI";
			break;
		case '30':
			$message="IP NON PRESENTE TRA I PARAMETRI";
			break;
		case '31':
			$message="MD5 DI SERIALE+TIMESTAMP NON CORRISPONDE CON HASHCODE";
			break;
		case '32':
			$message="NEGOZIO NON PRESENTE NELLA LISTA DELLE ABILITAZIONI";
			break;
		case '33':
			$message="NEGOZIO NON ABILITATO ALLA TRASMISSIONE ORDINI";
			break;
		case '34':
			$message="IP NON PRESENTE NEL RANGE DELLE ABILITAZIONI";
			break;
		case '35':
			$message="SERIALE NON ABILITATO PER IL CLIENTE";
			break;
		case '37':
			$message="AGGIORNAMENTO NON ANCORA ESEGUIUTO";
			break;
		case '38':
			$message="PARAMETRI MOBY DI VERSIONE NON PRESENTI, LOG DI VERSIONE NON AGGIORNATO";
			break;
		case '98':
			$message="TRASMISSIONE DI TEST - FORZATA";
			break;
		case '99':
			$message="TRASMISSIONE DI TEST";
			break;
		default:
			$message="ERRORE NON CODIFICATO";
			break;
	}
	return $message;
}