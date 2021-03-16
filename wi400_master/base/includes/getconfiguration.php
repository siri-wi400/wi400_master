<?php
function wi400GetSettings($appBase1="") {
	global $appBase;

	$conffile = "wi400.conf.php";
	// Caricamento file di configurazione
	if (php_sapi_name() == 'cli') {
		//require_once dirname(filter_input(INPUT_SERVER, 'PHP_SELF'))."/conf/$conffile";
		$dircli = dirname(dirname(dirname(__FILE__)));
		require_once $dircli."/conf/$conffile";
		$root_dir = dirname($dircli);
		if (is_file("$root_dir/settings/wi400CustomerBase.conf.php")) {
			require_once "$root_dir/settings/wi400CustomerBase.conf.php";
			$settings = array_merge($settings, $customerBaseSettings);
		}
		// Carico eventuali configurazioni sensibili fuori root
		if (isset($settings['more_config']) && $settings['more_config']!="") {
			if (file_exists($settings['more_config'])) {
				require_once $settings['more_config'];
			}
		}
	} else {
		$file = dirname(dirname(dirname(__FILE__)))."/conf/$conffile";
		if (is_file($file)) {
			require $file;
			// Carico configurazione custom installat dal cliente sulla directory settings
			$file = dirname(dirname(dirname(dirname(__FILE__))))."/settings/wi400CustomerBase.conf.php";
			if (is_file($file)) {
				require $file;
				$settings = array_merge($settings, $customerBaseSettings);
			}
			// Carico eventuale parametri aggiunti in override per previsti in sessione
			if (isset($_SESSION['override_conf_parm_file']) && is_file("conf/".$_SESSION['override_conf_parm_file'])) {
			 require_once "conf/".$_SESSION['override_conf_parm_file'];
			 array_merge($settings, $override_conf_parm_file);
			}
			// Carico eventuali configurazioni sensibili fuori root
			if (isset($settings['more_config']) && $settings['more_config']!="") {
				if (file_exists($settings['more_config'])) {
					require $settings['more_config'];
				}
			}
			// Verifico se esiste un environment particolare
			if (isset($_GET['WI400_ENV'])) {
				if ($_GET['WI400_ENV']!="") {
					$_SESSION['WI400_ENV_FILE']=$_GET["WI400_ENV"];
				} else {
					unset($_SESSION['WI400_ENV_FILE']);
				}
			}
			if (isset($_SESSION['WI400_ENV_FILE'])) {
				// Carico e faccio l'override dei parametri
				if (file_exists($settings['path_envfiles'].$_GET['WI400_ENV'].".conf.php")) {
					require $settings['path_envfiles'].$_GET['WI400_ENV'].".conf.php";
				}
			}
			// Per ultimo controllo parametri specifici per PATH
			$file = dirname(dirname(dirname(__FILE__)))."/conf/".trim(str_replace("/","",$appBase)).".conf.php";
			if (strpos(strtoupper($appBase), "WI400")===False) {
			    if (file_exists($file)) {
					require $file;
					$settings = array_merge($settings, $env_settings);
				}
			}
		} else {
			header("Location: ".$appBase."conf/installer.php");
			exit();
		}
	}
	// Applico Macro per sistemare i percorsi
	$settings['doc_root']=wi400ResolvePath($settings['doc_root']);
	$settings['data_path']=wi400ResolvePath($settings['data_path']);
	$settings['log_sql']=wi400ResolvePath($settings['log_sql']);
	$settings['message_path']=wi400ResolvePath($settings['message_path']);
	$settings['template_path']=wi400ResolvePath($settings['template_path']);
	$settings['log_root']=wi400ResolvePath($settings['log_root']);

	return $settings;
}
function wi400ResolvePath($path) {
	global $appBase;
	if(strpos($path, '##REAL_DIR##')!==false) {
		$dir = dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR;
		$path= str_replace('##REAL_DIR##', $dir, $path);
	}
	if(strpos($path, '##REAL_DIR-1##')!==false) {
		$dir = dirname(dirname(dirname(dirname(dirname(__FILE__))))).DIRECTORY_SEPARATOR;
		$path= str_replace('##REAL_DIR-1##', $dir, $path);
	}
	if(strpos($path, "##APP_BASE##")!==false) {
		$path = str_replace("##APP_BASE##", $appBase, $path);
	}
	
	if(strpos($path, "##USER##")!==false) {
		$path = str_replace("##USER##", $_SESSION['user'], $path);
	}
	return $path;
}