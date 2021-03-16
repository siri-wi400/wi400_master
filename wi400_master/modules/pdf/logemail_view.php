<?php

$miaLista = new wi400List("LOG_EMAIL");
$miaLista->setFrom("FPDFCONV");
$miaLista->setOrder("MAIINS DESC");


// Filtro veloce
$mioFiltro = new wi400Filter("MAITOR","Destinatario","STRING");
$mioFiltro->setFast(true);
$miaLista->addFilter($mioFiltro);

// Filtri avanzati
$mioFiltro = new wi400Filter("MAIFRM","Mittente","STRING");
$miaLista->addFilter($mioFiltro);
$mioFiltro = new wi400Filter("MAISBJ","Oggetto","STRING");
$miaLista->addFilter($mioFiltro);




$articoloDesc = new wi400Column("MAISBJ","Oggetto");
$articoloDesc->setDetailAction("TMAILDETA");

$miaLista->setCols(array(
						new wi400Column("MAIFRM","Mittente"),
						new wi400Column("MAITOR","Destinatario"),
						$articoloDesc,
						new wi400Column("MAIINS","Data Inserimento", "TIMESTAMP"),
						new wi400Column("MAIELA","Elaborazione", "TIMESTAMP"),												
						new wi400Column("MAIERR","Codice Elaborazione"),
						new wi400Column("MAIDER","Descrizione Elaborazione"),												
						)
					);

// aggiunta chiavi di riga
$miaLista->addKey("MAIREC");
$miaLista->addKey("MAIFRM");

listDispose($miaLista);
?>