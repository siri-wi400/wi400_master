<?php
if($actionContext->getForm()=="DEFAULT") {
	// Aggiungo i file di log SQL
	$sessionId = wi400Detail::getDetailValue("DEVELOPER_DOC_SRC", "SESSIONE");
	$USERSESSION = getSessionUser($sessionId);
	
	$user ="NONE";
	if (isset($USERSESSION['user']) && $USERSESSION['user']!="") {
		$user = $USERSESSION['user'];
	}
	
	if ($sessionId =="") $sessionId = "batch";
	$filesql = $settings['log_sql'].$user."_".$sessionId.".txt";
	if (strtoupper(substr(php_uname('s'), 0, 3)) === 'WIN')$filesql = str_replace("\\", '/', $filesql);
	// Aggiungo file di log generale
//	$path = $log_path."logs/error";
//	$filelog = $path."/php_error_".date("Ymd").".log";
	$filelog = ini_get("error_log");
	if (strtoupper(substr(php_uname('s'), 0, 3)) === 'WIN')$filelog = str_replace("\\", '/', $filelog);
	// LOG ZEND
	if (strtoupper(substr(php_uname('s'), 0, 5)) === 'OS400'){
//	$path_php = PHP_BINDIR;
//	$log_php_zend = str_replace("/bin", "", $path_php)."/var/log/php.log";
	$log_php_zend = get_cfg_var('error_log');
	$parts= explode("/", PHP_BINDIR);
	$dove = $parts[3];
	// LOG APACHE
	$log_apache_access = "/www/$dove/logs/access_log.Q1".date("ymd")."00";
	$log_apache_error  = "/www/$dove/logs/error_log.Q1".date("ymd")."00";
		}else{
		// Test prove
		$log_php_zend = "";
		$log_apache_error = "";
		$log_apache_access = "";
	// Windows
	if (strtoupper(substr(php_uname('s'), 0, 3)) === 'WIN'){
		$log_php_zend = str_replace("\\", '/', get_cfg_var('error_log'));
		// Se installato Apache
		if (strtoupper(substr($_SERVER['SERVER_SOFTWARE'],0,6)) == 'APACHE'){}
		// Se installato IIS
		if (strtoupper(substr($_SERVER['SERVER_SOFTWARE'],0,9)) == 'MICROSOFT'){
		$log_apache_error = "c:/inetpub/logs/LogFiles/W3SVC".$_SERVER['INSTANCE_ID']."/u_ex".date("ymd").".log";
		}
	}
	// Linux Apache log	- reperisco la distro installata
	if (strtoupper(substr(php_uname('s'), 0, 5)) === 'LINUX')
	{
		$vars = array();
		$files = glob('/etc/*-release');
	
		foreach ($files as $file)
		{
			$lines = array_filter(array_map(function($line) {
	
				// split value from key
				$parts = explode('=', $line);
	
				// makes sure that "useless" lines are ignored (together with array_filter)
				if (count($parts) !== 2) return false;
	
				// remove quotes, if the value is quoted
				$parts[1] = str_replace(array('"', "'"), '', $parts[1]);
				return $parts;
	
			}, file($file)));
	
				foreach ($lines as $line)
					$vars[$line[0]] = $line[1];
		}
	
	//	print_r($vars);
	// Linux Apache error log
	switch (trim(strtoupper($vars['ID']))) {
		case "FREEBSD":
			$log_apache_error = "/var/log/httpd-error.log";
			$log_apache_access = "/var/log/httpd-access.log";
		break;
		case "UBUNTU":
			$log_apache_error = "/var/log/apache2/error.log";
			$log_apache_access = "/var/log/apache2/access.log";
		break;
		case "DEBIAN":
			$log_apache_error = "/var/log/apache2/error.log";
			$log_apache_access = "/var/log/apache2/access.log";
		break;
		case "CENTOS":
			$log_apache_error = "/var/log/httpd/error_log";
			$log_apache_access= "/var/log/httpd/access_log";
		break;
		case "FEDORA":
			$log_apache_error = "/var/log/httpd/error_log";
			$log_apache_access= "/var/log/httpd/access_log";
		break;
		case "REDHAT":
			$log_apache_error = "/var/log/httpd/error_log";
			$log_apache_access = "/var/log/httpd/access_log";
		break;
				}
			}
		}

	$FileDetail = new wi400Detail($azione."_DET",true);
	$FileDetail->setColsNum(4);
	
	// SQL
	$labelDetail = new wi400Text("SQL");
	$labelDetail->setLabel("Log SQL");
	$labelDetail->setValue($filesql);
	$labelDetail->setLink(create_file_download_link($filesql));
	$FileDetail->addField($labelDetail);
	$labelDetail = new wi400Text("FILESIZE");
	$labelDetail->setLabel(_t('FILE_SIZE'));
	$labelDetail->setValue(@filesize($filesql));
	$FileDetail->addField($labelDetail);
	$myButton = new wi400InputButton('CLEAR_SQL');
	$myButton->setLabel("Clear SQL");
	$myButton->setAction($azione);
	$myButton->setForm("ELIMINA_FILE");
	$myButton->addParameter("FILE", $filesql);
	$FileDetail->addField($myButton);
	$myButton = new wi400InputButton('VIEW_SQL');
	$myButton->setLabel("View");
	$myButton->setAction($azione);
	$myButton->setTarget("WINDOW", 1000, 800);
	$myButton->setForm("FILE_PRV");
	$myButton->addParameter("FILE", $filesql);
	$FileDetail->addField($myButton);
	// ERROR LOG
	$labelDetail = new wi400Text("LOG");
	$labelDetail->setLabel("Log Error");
	$labelDetail->setValue($filelog);
	$labelDetail->setLink(create_file_download_link($filelog));
	$FileDetail->addField($labelDetail);
	$labelDetail = new wi400Text("FILESIZE");
	$labelDetail->setLabel(_t('FILE_SIZE'));
	$labelDetail->setValue(@filesize($filelog));
	$FileDetail->addField($labelDetail);
	$myButton = new wi400InputButton('CLEAR_LOG');
	$myButton->setLabel("Clear LOG");
	$myButton->setAction($azione);
	$myButton->setForm("ELIMINA_FILE");
	$myButton->addParameter("FILE", $filelog);
	$FileDetail->addField($myButton);
	$myButton = new wi400InputButton('VIEW_LOG');
	$myButton->setLabel("View");
	$myButton->setAction($azione);
	$myButton->setForm("FILE_PRV");
	$myButton->setTarget("WINDOW", 1000, 800);
	$myButton->addParameter("FILE", $filelog);
	$FileDetail->addField($myButton);
	// ERROR LOG DI ZEND
	$labelDetail = new wi400Text("ZEND");
	$labelDetail->setLabel("Log PHP Zend");
	$labelDetail->setValue($log_php_zend);
	$labelDetail->setLink(create_file_download_link($log_php_zend));
	$FileDetail->addField($labelDetail);
	$labelDetail = new wi400Text("FILESIZE");
	$labelDetail->setLabel(_t('FILE_SIZE'));
	$labelDetail->setValue(@filesize($log_php_zend));
	$FileDetail->addField($labelDetail);
	$myButton = new wi400InputButton('CLEAR_LOG_ZEND');
	$myButton->setLabel("Clear LOG Z");
	$myButton->setAction($azione);
	$myButton->setForm("ELIMINA_FILE");
	$myButton->addParameter("FILE", $log_php_zend);
	$FileDetail->addField($myButton);
	$myButton = new wi400InputButton('VIEW_ZEND');
	$myButton->setLabel("View");
	$myButton->setAction($azione);
	$myButton->setForm("FILE_PRV");
	$myButton->setTarget("WINDOW", 1000, 800);
	$myButton->addParameter("FILE", $log_php_zend);
	$FileDetail->addField($myButton);
	// ERROR LOG DI APACHE / IIS
	$labelDetail = new wi400Text("APACHE_ERROR");
	if (strtoupper(substr($_SERVER['SERVER_SOFTWARE'],0,6)) == 'APACHE'){
	$labelDetail->setLabel("Log Error APACHE");
	}else{
	$labelDetail->setLabel("Log ISS");
	}
	$labelDetail->setValue($log_apache_error);
	$labelDetail->setLink(create_file_download_link($log_apache_error));
	$FileDetail->addField($labelDetail);
	$labelDetail = new wi400Text("FILESIZE");
	$labelDetail->setLabel(_t('FILE_SIZE'));
	$labelDetail->setValue(@filesize($log_apache_error));
	$FileDetail->addField($labelDetail);
	$labelDetail = new wi400Text("VUOTO1");
	$labelDetail->setLabel("");
	$FileDetail->addField($labelDetail);
	$myButton = new wi400InputButton('VIEW_AL');
	$myButton->setLabel("View");
	$myButton->setAction($azione);
	$myButton->setForm("FILE_PRV");
	$myButton->setTarget("WINDOW", 1000, 800);
	$myButton->addParameter("FILE", $log_apache_error);
	$FileDetail->addField($myButton);
	// ACCESS LOG DI APACHE
	if (strtoupper(substr($_SERVER['SERVER_SOFTWARE'],0,6)) == 'APACHE'){
	$labelDetail = new wi400Text("APACHE_ACCESS");
	$labelDetail->setLabel("Log Access APACHE");
	$labelDetail->setValue($log_apache_access);
	$labelDetail->setLink(create_file_download_link($log_apache_access));
	$FileDetail->addField($labelDetail);
	$labelDetail = new wi400Text("FILESIZE");
	$labelDetail->setLabel(_t('FILE_SIZE'));
	$labelDetail->setValue(@filesize($log_apache_access));
	$FileDetail->addField($labelDetail);
	$labelDetail = new wi400Text("VUOTO2");
	$labelDetail->setLabel("");
	$FileDetail->addField($labelDetail);
	$myButton = new wi400InputButton('VIEW_AA');
	$myButton->setLabel("View");
	$myButton->setAction($azione);
	$myButton->setForm("FILE_PRV");
	$myButton->setTarget("WINDOW", 1000, 800);
	$myButton->addParameter("FILE", $log_apache_access);
	$FileDetail->addField($myButton);
	}
	

	$myButton = new wi400InputButton('RELOAD_BUTTON');
	$myButton->setLabel(_t('REFRESH'));
	$myButton->setAction($azione);
	$myButton->setForm($actionContext->getForm());
	$FileDetail->addButton($myButton);
	
	$FileDetail->dispose();
	
}
else if ($form=="FILE_PRV") {

	$FileDetail = new wi400Detail($azione."_DET",true);
	$FileDetail->setColsNum(2);
	if (isset($_REQUEST['FILE'])) {
		$file_path=$_REQUEST['FILE'];
		$_SESSION['LAST_FILE_REQUEST']=$_REQUEST['FILE'];
	} else {
		$_REQUEST['FILE']=$_SESSION['LAST_FILE_REQUEST'];
	}
	$file_path=$_REQUEST['FILE'];
	$size="";
	if(file_exists($file_path)) {
		$size = filesize($file_path);
		if($size<2000000) {
			$path_parts = pathinfo($file_path);
			//				if(isset($path_parts['extension']) && $path_parts['extension']=="log")
			$lines = file_get_contents($file_path);
		} else {
			$path_parts = pathinfo($file_path);
			$lines = read_backward_line($file_path, 1000);
		}
	}
	$labelDetail = new wi400Text("FILENAME");
	$labelDetail->setLabel(_t('FILE_PATH'));
	$labelDetail->setValue($file_path);
	$labelDetail->setLink(create_file_download_link($file_path));
	$FileDetail->addField($labelDetail);

	if(file_exists($file_path) && $size>2000000) {
		?>
				<script>
					alert("Il file à troppo grande. Verranno visualizzate le ultime 1000 righe.");
				</script>
	<?						
			}
			
	$labelDetail = new wi400Text("FILESIZE");
	$labelDetail->setLabel(_t('FILE_SIZE'));
	$labelDetail->setValue($size);
	$FileDetail->addField($labelDetail);
	$FileDetail->dispose();
	
	$LogDetail = new wi400Detail('LOG_BODY_DET', true);
	$LogDetail->setTitle('Log error');
	$LogDetail->isEditable(true);
			if(isset($path_parts['extension'])) {
				if($path_parts['extension']=="xml") {
					$myField = new wi400InputTextArea('LOG_BODY');
					$myField->setReadonly(true);
	//				$myField->setSaveSession(false);
					$myField->setSize(190);
					$myField->setRows(25);
					$myField->setValue($lines);
					$LogDetail->addField($myField);
				}			
				else {
					// Testo del log del lavoro
					$myField = new wi400TextPanel('LOG_BODY');
					$myField->setHeight(400);
					$myField->setValue($lines);
					$myField->setWidthParent(true);
					$LogDetail->addField($myField);
				}
			}
			
			$myButton = new wi400InputButton('RELOAD_BUTTON');
			$myButton->setLabel(_t('REFRESH'));
			$myButton->setAction($azione);
			$myButton->setForm($actionContext->getForm());
			$LogDetail->addButton($myButton);
			
			$LogDetail->dispose();
}