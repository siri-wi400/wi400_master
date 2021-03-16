<?php
/**
	Fasi del messaggio
	1 Inserimento (Client)
		* Ricevuta di inserimento (Controllo se sono abilitato)
	2 Inoltro a Server(Push server legge coda dati ed inoltra)
		* Ricevuta di Inoltro (Controllo se sono abilitato)
	3 Invio a Destinazione/Destinazioni (Server riceve i messaggi dal push server e li inoltra)
		* Ricevuta al Server Push di avvenuta consegna al server per inoltro, controllo abilitazioni.
		* Ricevuta di avvenuta ricezione del messaggio da parte del destinatario
	4 Risposta
		* Risposta del destinatario con eventuali errori o esecuzione di operazioni o restituzione di dati
	Strutture dei file
		* TESTATA MESSAGGIO
		     * TESTATE OPERAZIONI DA ESEGUIRE
		     	* CONTENUTO OPERAZIONI DA ESEGUIRE CONTENUTE NEL MESSAGGIO
	
	SERVER SOCKET
	
	INVIA E AGGIORNA I LOG
	
	ESEMPI JSON DEI MESSAGGI
	
	SERVER PUSH LEGGE CODE ED INOLTRA
	
	FUNZIONE PER ATTENDERE LA RISPOSTA AL MESSAGGIO
	* Faccio query sul DB finchè non risulta risposto ...
	* 	     			
**/
