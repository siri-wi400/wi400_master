<?php
    global $settings, $routine_path, $base_path;
    
    //getMicroTimeStep("Inizio Loader");
	require_once $moduli_path."/list/wi400List.php";
	function my_autoload ($pClassName) {
		global $settings, $routine_path, $base_path;
		$pack = $settings['package'];
		if (file_exists($routine_path."/classi/$pClassName.cls.php")) {
			require_once $routine_path."/classi/$pClassName.cls.php";
		} elseif(file_exists($base_path."/package/$pack/classi/$pClassName.cls.php")) {
            require_once $base_path."/package/$pack/classi/$pClassName.cls.php";
		} 
	}
	spl_autoload_register("my_autoload");
	require_once $routine_path.'/classi/wi400Spacer.cls.php';
	require_once $routine_path.'/classi/wi400ConfigManager.cls.php';
	require_once $routine_path.'/classi/wi400Tab.cls.php';
	require_once $routine_path.'/classi/wi400Detail.cls.php';
	require_once $routine_path.'/classi/wi400LookUp.cls.php';
	require_once $routine_path.'/classi/wi400Image.cls.php';
	require_once $routine_path.'/classi/wi400Table.cls.php';
	require_once $routine_path.'/classi/wi400Text.cls.php';
	require_once $routine_path.'/classi/wi400Input.cls.php';
	require_once $routine_path.'/classi/wi400InputHidden.cls.php';
	require_once $routine_path.'/classi/wi400InputText.cls.php';
	require_once $routine_path.'/classi/wi400InputTextArea.cls.php';
	require_once $routine_path.'/classi/wi400TextPanel.cls.php';
	require_once $routine_path.'/classi/wi400InputEditor.cls.php';
	require_once $routine_path.'/classi/wi400InputFile.cls.php';
	require_once $routine_path.'/classi/wi400InputSelect.cls.php';
	require_once $routine_path.'/classi/wi400InputCheckBox.cls.php';
	require_once $routine_path.'/classi/wi400InputSwitch.cls.php';
	require_once $routine_path.'/classi/wi400InputRadio.cls.php';
	require_once $routine_path.'/classi/wi400InputSelectCheckBox.cls.php';
	require_once $routine_path.'/classi/wi400InputButton.cls.php';
	require_once $routine_path.'/classi/wi400ButtonsBar.cls.php';
	require_once $routine_path.'/classi/wi400Wizard.cls.php';
	require_once $routine_path.'/classi/wi400ZoomPan.cls.php';
	require_once $routine_path."/classi/wi400Graphs.cls.php";
	require_once $routine_path.'/classi/wi400ProgressBar.cls.php';
	require_once $routine_path.'/classi/wi400Applet.cls.php';
	require_once $routine_path.'/classi/wi400Ajax.cls.php';
	require_once $routine_path.'/classi/wi400PhpCode.cls.php';
	require_once $routine_path.'/classi/wi400AnnounceContent.cls.php';
	require_once $routine_path.'/classi/wi400Otm.cls.php';
	require_once $routine_path.'/classi/wi400Forms.cls.php';
	require_once $routine_path.'/classi/wi400Widget.cls.php';
	require_once $routine_path."/generali/common.php";
	//require_once $routine_path."/firephp/lib/FirePHPCore/FirePHP.class.php";
	require_once $routine_path."/classi/wi400ExitPoint.cls.php";
	require_once $routine_path."/classi/wi400GlobalObject.cls.php";
	//$firephp = FirePHP::getInstance(true);
	//require_once $routine_path."/generali/dateTime.php";
	require_once $routine_path."/generali/wrap_js_function.php";
	// Abilitazione comandi di sistema
	$systemdbpers="OS400"; // DEFAULT AS400
	if (isset($settings['systemdbpers'])) {
		$systemdbpers = trim($settings['systemdbpers']);
	} else {
		$settings['systemdbpers']='OS400';
	}
	$systemdbpers = strtolower($systemdbpers);
	require_once $routine_path."/generali/{$systemdbpers}command.php";
	// WYSIWYG - Editor
	//if (file_exists($routine_path.'/ckeditor/ckeditor.php')) {
	// require_once $routine_path.'/ckeditor/ckeditor.php';
	//}
	/*if (file_exists($routine_path.'/fckeditor/fckeditor_php5.php')) {
		require_once $routine_path.'/fckeditor/fckeditor_php5.php';
	}*/
	require_once $moduli_path."/tree/treeMenu.php";
	
	/*require_once $routine_path.'/classi/wi400DragAndDrop.cls.php';
	require_once $routine_path.'/classi/wi400DropArea.cls.php';
	require_once $routine_path."/classi/wi400DragList.cls.php";
	
	require_once $routine_path.'/classi/wi400Messages.cls.php';
	require_once $routine_path.'/classi/wi400ValuesContainer.cls.php';
	require_once $routine_path.'/classi/wi400MenuContext.cls.php';
	require_once $routine_path.'/classi/wi400Action.cls.php';
	require_once $routine_path.'/classi/wi400ActionContext.cls.php';
	require_once $routine_path.'/classi/wi400BreadCrumbs.cls.php';
	require_once $routine_path.'/classi/wi400History.cls.php';
	require_once $routine_path.'/classi/wi400ListAction.cls.php';
	
	require_once $routine_path.'/classi/wi400SortList.cls.php';
	require_once $routine_path.'/classi/wi400Tree.cls.php';
	require_once $routine_path.'/classi/wi400Node.cls.php';*/
	
	//require_once $routine_path."/classi/wi400Bundle.cls.php";
	require_once $routine_path."/database/".strtolower($settings['database']).".cls.php";
	require_once $routine_path."/generali/validation.php";
	require_once $routine_path."/generali/formatting.php";
	require_once $routine_path."/generali/decorators.php";
	require_once $routine_path."/generali/decoding.php";
	require_once $routine_path."/generali/blowfish.php";
	//require_once $routine_path.'/classi/wi400CustomSubfile.cls.php';
	//require_once $routine_path.'/classi/wi400Subfile.cls.php';
	require_once $routine_path."/generali/sessions.php";
	//require_once $routine_path.'/classi/wi400Control.cls.php';
	//require_once $routine_path.'/classi/wi400Session.cls.php';
	if (isset($settings['xmlservice'])) {
		require_once $routine_path.'/classi/wi400RoutineXML.cls.php';
		require_once $routine_path."/generali/xmlsupport.php";		
	} else {
	    //require_once $routine_path.'/classi/wi400Routine.cls.php';
	}
 
	/*require_once $routine_path.'/classi/wi400Tabelle.cls.php';

	require_once $routine_path.'/classi/wi400Pallet.cls.php';
	require_once $routine_path.'/classi/wi400Bay.cls.php';*/
	
	// Funzioni comuni
	require_once $routine_path."/generali/dateTime.php";
	require_once $routine_path."/generali/wi400File.php";
//	require_once $routine_path."/generali/common.php";
	
	
	require_once $routine_path."/PHPMailer/class.phpmailer.php";
	require_once $base_path."/includes/errorHandler.php";
	
	//require_once $routine_path.'/classi/wi400Decoding.cls.php';
    // Funzioni di caching

	require_once $base_path.'/caching/'.strtolower($settings['caching_type']).'.php';	
	// Funzioni di architettura
	require_once $base_path."/arch/default.php";	
	require_once $base_path."/arch/".strtolower($settings['architettura']).".php";
	// Funzioni di packages. Pacchetto applicativo installato sulla macchina
	if (isset($_SESSION['my_package']) && $_SESSION['my_package']!="") {
		$settings['package']=$_SESSION['my_package'];
	}
	require_once $base_path."/package/".strtolower($settings['package'])."/".strtolower($settings['package']).".php";
	// Cerco eventuali settaggi di installazione da overizzare in settings
	$packageConfig = p13nPackage("config", "config");
	if ($packageConfig) {
		require $packageConfig;
		$settings = array_merge($settings, $packageSettings);
		// Patch Riespodo i gruppi che sono stati inizializzati prima
		$wi400_groups = array();
		if (isset($settings['wi400_groups']) && !empty($settings['wi400_groups'])){
			$wi400_groups = explode(";",$settings['wi400_groups']);
//			echo "GROUPS:<pre>"; print_r($wi400_groups); echo "</pre>";
		}
		if (isset($settings['wi400_sel_groups']) && !empty($settings['wi400_sel_groups'])){
			$wi400_sel_groups = explode(";",$settings['wi400_sel_groups']);
//			echo "SEL GROUPS:<pre>"; print_r($wi400_sel_groups); echo "</pre>";
		
			$wi400_groups = array_merge($wi400_groups, $wi400_sel_groups);
//			echo "WI400 GROUPS:<pre>"; print_r($wi400_groups); echo "</pre>";
		}
	}
	if (isset($_SESSION['override_conf_parm_file']) && is_file("conf/".$_SESSION['override_conf_parm_file'])) {
		require_once "conf/".$_SESSION['override_conf_parm_file'];
		$settings = array_merge($settings, $override_conf_parm_file);
	}
	// Carico configurazione custom sul package installata dal cliente sulla directory settings
	if (php_sapi_name() == 'cli') {
		//require_once dirname(filter_input(INPUT_SERVER, 'PHP_SELF'))."/conf/$conffile";
		$dircli = dirname(dirname(dirname(__FILE__)));
		$root_dir = dirname($dircli);
		if (is_file("$root_dir/settings/wi400Customer.conf.php")) {
			require_once "$root_dir/settings/wi400Customer.conf.php";
			$settings = array_merge($settings, $customerSettings);
		}
	} else { 
		if (is_file("../settings/wi400Customer.conf.php")) {
			require_once "../settings/wi400Customer.conf.php";
			$settings = array_merge($settings, $customerSettings);
		}
	}
	// Carico Funzioni Specifiche dei moduli abilitati
	require_once p13n("/base/includes/modules.php");	
?>