<?php

	$from = "info@siri-informatica.it";
	
	$lookup_email = true;
	
	$tipo_evento_array = array(
		"EMAIL" => "EMAIL",
		"FATAL ERROR" => "FATAL ERROR"
	);
	
	$tipo_evento_color = array(
		"EMAIL" => "wi400_grid_green",
		"FATAL ERROR" => "wi400_grid_red"
	);
	
	$stato_evento_array = array(
		"N" => "Da notificare",
		"S" => "Notificato"
	);
	
	$stato_evento_vals = array(
		"N" => "0",
		"S" => "1"
	);
	
	$tipo_dest_array = array(
		"TO" => "TO",
		"CC" => "CC",
		"BCC" => "BCC",
		"RPYTO" => "Reply To",
		"CONTO" => "Confirm Reading TO"
	);
	
	$tipo_dest_colors = array(
		"TO" => "wi400_grid_green",
		"CC" => "wi400_grid_yellow",
		"BCC" => "wi400_grid_orange",
		"RPYTO" => "wi400_grid_blue",
		"CONTO" => "wi400_grid_red"
	);
	
	$des_icons_array = array(
		"DESTINATARI" => "Destinatari",
		"NOTIFICA" => "Notifica",
	);
	
	$type_icons_array = array(
		"DESTINATARI" => "BOOK",
		"NOTIFICA" => "ESECUZIONE",
		"INOLTRA" => "SEND_MAIL"
	);
	
	$action_icons_array = array(
		"DESTINATARI",
//		"NOTIFICA",
//		"INOLTRA"
	);