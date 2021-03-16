<?php
$settings=array(
		// New versione 10
		'showpassword' => True,
		'data_area_with_udft'=>True, //Lettura Data Area con UDFT DB2
		//'log_diag_with_udft' => True, // Scrittura log con DB
		//
		
		'enable_multi_session_byappbase'=>True, 
		'enable_db_user' => True,
		'security_password_hash' => 'md5',
//'more_config' 			=> "/home/wi400/config/moreconfig.php",
'log_paths' => array(
		"LOG_ERROR" => "logs/error/",
		"LOG_DEBUG" => "logs/debug/",
		"LOG_EMAIL" => "logs/email/",
		"LOG_CLEAN" => "logs/clean/",
),
'log_file_names' => array(
		"LOG_ERROR" => "php_error_".date("Ymd").".log",
		"LOG_DEBUG" => "php_debug_".date("Ymd").".log",
		"LOG_EMAIL" => "cvtspool_invio_".date("Ymd").".log",
		"LOG_CLEAN" => "log_clean_".date("Ymd").".log",
),
'exit_point'=> True,
'advanced_security'=>True,
'matomo_api_url' => 'http://10.0.40.2/piwik/piwik.php',
'matomo_auth_token' => '9da9de8b9398cd6ba7d6f7a22068ecde',
'matomo_site_id' => 1,
'matomo_site_url' => 'http://10.0.40.1:89',
'user_statistics' => True,
'user_security' => True,
// Se il DB fosse gestito in UTF8 queste operazioni non servirebbero
'db_encode_input' => True,
'db_encode_output' => True,
'xmlservice_init_each_prepare' => True, // Da versione Zend Server 7.1.6 in su obbligatorio
//'db_encode_input_library' => True // Attivare solo in casi eccezionali
'enable_ws_client' => True,
'widget_announce' => true,	
// NEW 9.6.0
'ws_log_xml' => True,
'ws_log_file' => True,	
'ws_log_enable' => True,
'ws_server_down' => False,
'ws_timeout' => 600,
'default_export_orientation'=>"PORTRAIT",
'default_export_formats'=>"XLSX",
// NEW 8.5.1
'cleanable_input' => True, // Settto se i campi di input possono essere pulitti
'ajax_decoding_default' => True, // Setto se tutti i campi input di default hanno la decodifica AJAX		
'ajax_complete_default' => True, // Setto se tutti i campi con decodifica AJAX hanno l'autocomplete
'ajax_complete_min_char_default' => 2, //Minimo dei caratteri richiesti per il complete
'ajax_complete_max_result_default' => 15, //Numero massimo di risultati
'ajax_complete_mask_field_default' => "##FIELD##%", //Maschera di editazione di default
'link_target_default' => "_BLANK",
'security_on' => True,		
'security_autocomplete_password' => True,	
'developer_debug' => True,
'mobile_init' => True,	
		'rtvlibre_withdb' => True,
		'delay_library_list' => True,
'mod_developer_debug' => "1",
'log_diag_message' => True,	// Scrittura Del Messaggio di DIAG su JOBLOG	
'log_user_action' => True, // Log delle azioni lanciate dall'utente
'log_user_action_profile' => array (
	"VEGANEW"
), // Utenti da loggare
'log_user_action_req' => False, // Log della request associata all'azione
'security_x_frame_options' => True,
'security_parse_post' => True,
'siad_euro' => "DOUBLE",
'mobile_init' => True, // ABILITAZIONE RICONOSCIMENTO MOBILE		
'count_query'  => true,
'current_query' => true,
'subfile_query' => true,
'http_port' => "89",
		'http_server' => "127.0.0.1",
		'http_user' => 'PHPMAN',
'security_crypt_url' => True,
'check_field_enable_on_detail'=>True,
'security_private_key' => "29340sdf09f0asf",				
'security_parse_get' => True,
'security_check_url' => True, // Disabilitare se https e certificato autogenerato
'security_allow_direct_url' => True, //Permette di richiamare direttamente un url di WI400 dopo il login
'security_allow_site' => array(
		"http://*.gstatic.com/",
		"http://*.google.com/",
		"http://maps.gstatic.com/",
		"http://*.googleapis.com", 
		"'unsafe-eval'",
		"'unsafe-inline'"
),
'doc_root' => '##REAL_DIR##',
'pid_monitor'=>False, // Attivazione LOG job attivi .. Performance scadono su AJAX
'data_path' => '##REAL_DIR-1##data/',
'log_sql' => '##REAL_DIR-1##data/SQL/',
'widget' => True,		
'messages_enable' => True,		
'tmp_clean_logout' => True, // Puliiza della tmp all'uscita dell'utente se non settato il parametro è true
'message_path' => '##REAL_DIR-1##data/message/',
//'log_root'=> '/www/zendsvr/htdocs/',
'sess_path' => '/var/tmp/wi400/',
'template_path' => '##REAL_DIR-1##data/Template/',
'log_root' 	=> '##REAL_DIR-1##data##APP_BASE##', 		// ##APP_BASE##, ##USER##
'download_dir' => array(
		'/www/zendphp7/data/',
		'/www/zendphp7/logs/',
),		
'debug' => True,
'db_debug' => false,
'db_log' => True,
'db_dft_not_null' => False, // Per classe PDO con ODBC permette di creare tutte le tabelle temporanee senze campi nullabili
'db_retry_prepare' => False, // Dat attivare con driver PDO .. i driver ODBC perdono occasinalement .. la prepare
'check_net' => True,// Controllo se connessione attiva
'check_net_mon' => True,
'check_net_users' => array("PHP2MAN"),
'query_pagination_between' => False, // La paginazione viene fatta tramite query
'xmlservice' => True,
'xmlservice_lib' => 'XMLSERVICE',
'xmlservice_plug' => 'iPLUGR65K',		
'xmlservice_jobd_lib' =>'ZENDPHP7',
'xmlservice_jobd' =>'ZSVR_JOBD',	
'xmlservice_jobd_lib_ws' =>'',
'xmlservice_jobd_ws' =>'',
'xmlservice_jobd_lib_batch' =>'',
'xmlservice_jobd_batch' =>'',
'xmlservice_userspace_fast' => false,
'pdo_odbc_support_library' => True,
'pdo_fix_truncation' => True,
'xmlservice_cdata' => True,	// Abilita la richiesta di CDATA per i campi tornati da XMLSERVICE e di conseguenza abilita le classi a fare il parsing	
'init_db_routine' =>'',//Disabilitare da 7.1 in poi. Setta il QAQQINI da usare
'convert_special_char_xml' => False, // Cerca di convertire l'XML in un XML leggibile da simplexml
'width_breadCrumb'  => 600,  //Larghezza barra history		
//'pdo_connection_string' => '"odbc:DRIVER={iSeries Access ODBC Driver};SYSTEM=10.0.40.1;UID=QPGMR;PWD=ALDEBARAN;PREFETCH=1;CLIENTAPPLNAME=WI400"',
//'pdo_connection_string' => '"ibm:*LOCAL',
'pdo_connection_string' => 'odbc:DRIVER={IBM i Access ODBC Driver};CMT=0;NAMING=1;SYSTEM=10.0.40.1;UID=WI400;PWD=WI400',
// ZENDSVR/ZSVR_JOBD *DEFAULT		
//'xmlservice_jobd_lib' => 'ZENDSVR6',		
//'xmlservice_jobd' => 'ZSVR_JOBD',	
'xmlservice_driver' =>"PDO", //DB utilizzo iplug, diverso utilizzo memoria diretta IBMITOOL
'display_errors' =>	'E_ALL & E_STRICT & ~E_DEPRECATED',
'caching_type'=> 'wi400',
'caching_persistent' => False,
// <nome asp>, oppure *ARCH da funzione architettura oppure *P13N da /includes/asp.php 
'base_asp' => '', 
'ccsid' => '280',
'timeout' => '600',	
'memory_limit' => "1000M",		
'session_life' => '1440', //MINUTI DURATA SESSIONE			
//'as_environment'=>'SEDE PRODUZIONE',
'OS400'=>'V6R1M0',
'multi_language'=>true,
'subfile_time_limit'=>600,
'version' => '9.3.0 RC1',
'timezone' => 'Europe/Rome',
'database' => 'DB2_PDO',
//'database' => 'DB2I5',
'language_tree' => 3,
'server_zend_ip' => '10.0.40.1',
'jobname' => '*USER',
'active_jobs' => 'PHPSIRI,PHPWEBSRVS,XTOOLKIT',
'active_jobs_subsys' => 'ZENDSVR',		
'platform' => 'AS400',
//'i5_toolkit' => 'internal',
'i5_dec_separator' => ',',
'db_user' => 'WI400',
'db_pwd' => 'WI400',
'db_name' => 'PHPLIB',
'db_temp' => 'PHPTEMP',
'db_log' => True,
'db_lib_list' => 'QTEMP;ZENDPHP7;PHPTEMP;QGPL',
'db_post_lib_list' => 'LIBHTTP;SIRIUTYM;OPENTERM;PHPLIB;GIWI400',
'db_separator' => '/',
'i5_sep' => '/',
'architettura' => 'GAAS',
'package' => 'SIAD',
'db_host' => 'D7041180',
'pathToInstall' => '',
'installerPath' => '',
'uploadPath' => '/upload/',
'lib_architect' => 'AASSTP3000',
'table_prefix' => 'SIR_',
'cliente_installazione' => 'Cliente di Test SIRI s.r.l',
'i5_conn_type' => 'P',
'db_conn_type' => 'T',
'only_wi400_user' => true,
'sbmjob_with_user' => true,
'wiki_url' => '/modules/luca_test/helpWI400/wi400.html',
'leftMenuOpen' => true,
'leftMenuRows' => 'false,false,false,true',
'temaDefault' => 'sd',
'p13n' => 'siri',
'wi400_grid_rows' => 10,
'wi400_lookup_rows' => 10,
'thousand_separator' => '.',
'decimal_separator' => ',',
'date_format' => 'dd/mm/YYYY',
'date_php_format' => 'd/m/Y',
'time_stamp_format' => 'dd/mm/YYYY HH:MM',
'time_stamp_complete_format' => 'dd/mm/YYYY HH:MM:ss',
'time_stamp_separator_format' => 'dd/mm/YYYY - [ HH:MM:ss ]',
'hour_format' => 'HH:MM:ss',
'short_hour_format' => 'HH:MM',
'time_format' => 'h:i A',
'tree_root' => 'settori',
'window_title' => 'wi400 - Web interface AS400',
'admin_email' => 'info@wi400.com',
'jobq' => 'QINTER',

'default_language' => 'Italian',
'default_locale' =>	'it_IT',
'auth_method' => 'AS',
//'wi400_groups' => 'SPOOL_ADMIN',
'wi400_groups' 			=> 'QUERY_ADMIN;QUERY_USER;QUERY_FILTRO;PROMO_MASTER;PROMO_BUY;PROMO_ORDIN;PROMO_MARKET;VIEW_SQL;MSG_CED;MSG_SEGRETERIA',		// abate

'campiEURO' => '0',

// Customize depend customer policy
'login_profile' => 'GROUP',
'login_grup_des' => array("GROUP" =>"SELEZIONE_GRUPPO"),
'login_grup_scel' => array("GROUP" => "SEL_GRUPPO"),
'login_grup_field' => array("GROUP" => "GRUPPO"),		
			            
'ldap_host' => '',
'ldap_port' => 389,
'ldap_domain' => '',
'ldap_binddn' => 'wi400',
'ldap_bindpwd' => '',
'ldap_rootdn' => '',
'ldap_searchattr' => '',

// customize depend on customer mail server setting
'smtp_host' => 'smtp.siri-informatica.it',
'smtp_user' => 'as400@siri-informatica.it',
'smtp_pass' => 'as400siri',
'smtp_from' => '',
'smtp_alias' => "WI400@",		
'smtp_auth' => true,
'smtp_secure' => "",	// "", "ssl", "tls"
'smtp_port' => "",	// g-mail: 465
'show_system_error' => true,
'mail_system_error' => false,
'mail_export' => true,
'self_export' => true,
'zip_compress' => "ZipArchive",		// "ZipArchive", "zip_lib"

// salvataggio delle informazioni di log
'save_email_log' => true,

// MPX settings
'mpx_ZCode' => 'ZCode_01',
'mpx_username' => 'User_01',
'mpx_password' => 'Password_01',
'mpx_WorkProcessID' => 'WPD1',
// Parametri di connessione al server di MPX per inviare e ricevere i dati
'mpx_test' => true,
'mpx_server' => '10.0.40.1',
'mpx_port' => 89,
'mpx_uri' => '',
// salvataggio dello script XML da inviare ad MPX
'save_mpx_xml' => true,
// salvataggio del file PDF inviato decodificato
'save_mpx_pdf' => true,

'pdf_write_metod' => 'text',
'pdf_rpg' => "200", // Limite righe oltre il quale viene usata la routine RPG(FAST!!!) per convertire in PDF SOLO INTERATTIVO 
'modelli_pdf_keys'   => 9,
'modelli_pdf_user_keys' => 5,
'max_export_rows_xls'	=> 12000,
'export_list_batch' => true, // Attivazione esportazione lista batch
// folder. Gestione scehde dinamiche
'folder_type' => array("TO" => "Prenotazione TO", "ART" => "Articolo"),	
'folder_language' => false, // Schede gestite in lingua			
// google maps settings
'google_map_key' => 'ABQIAAAAmiag7sjtDKGksv0q_TBc1BRLe-1ghZI0wrX9FytkuF0UrJ1rOhTBErks4J3adCnAKk4PkhFWP09yhg',
// Help settings
'wi400_help'			=> array('url' => '/WI400_GUIDE/index.html',
		'width' => 900,
		'height' => 500),

		'version' => '6.9.0 Beta',
		'articoli_descrizione_format'=>'LUNGA',

		'testate_ordini'=>'FODCORDI',
		'righe_ordini'=>'FODGRIGO',
		'sconti_righe'=>'FODSRIGO',
		'prefix_ordini'=>'FOD',
		'campo_valore_ordini' => 'OAGVAE',
		'tipo_ricerca_ordini' => "CLIENTE",		// ENTE/CLIENTE

		//	'liv_merc_table_array' => array("FTAB001","FTAB002","FTAB004"),
'liv_merc_table_array' => array("FTAB127","FTAB127","FTAB127"),

// Descrizione dei gruppi di appartenenza degli utenti
/*
 'des_user_groups' => array(
 		"TOW_AGENTI" => "Agente",
 		"TOW_AREA" => "Responsabile Area/Canale",
 		"TOW_SEGRETERIA" => "Segreteria Commerciale",
 		"TOW_CLIENTI" => "Cliente",
 );
*/
'des_user_groups' => array(
		"PROMO_MASTER" => "Master",
		"PROMO_BUY" => "Buyer",
		"PROMO_ORDIN" => "Ordinatore",
		"PROMO_MARKET" => "Marketing",
),
		'automatic_field_add' => true,
//		'security_trust_ip' => array("10.0.40.1", "10.0.15.208"),
); 

?>
