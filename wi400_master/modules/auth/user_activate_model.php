<?php
	require_once $moduli_path."/siri_vega_utility/fornitori_set_psw_functions.php";

	$user = $trigger_param['user'];
	
	$email = getUserMail($user);
	
	$to_array = array($email);
		
	$dati = array(
		'MEBRAG' => $user,
		'HREF_LINK' => 'https://vega400.vegalab.net'.$appBase."index.php"
	);
	
	$argomento = 'ACT_FORN';
	$htmlEmail = get_template_html($argomento, "BODY");
	$htmlInfo = get_template_html('PSW_FORN', "INFO");
	$htmlEmail .= $htmlInfo;
	
	$htmlEmail = substituteFolderArray($htmlEmail, $dati);
	
	$sent = wi400invioEmail::invioEmail('', $to_array, array(), 'Utente WI400 '.$user.' attivato', $htmlEmail, array(), array(), true);
	//$htmlEmail = substituteFolderArray($htmlEmail, $dati);
	
	