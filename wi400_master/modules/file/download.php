<?php
    // Recupero il nome del file dell'ID passato
	if (!isset($_SESSION['user'])) {
		die("Permission denied!!");
	}
    $id = filter_input(INPUT_GET, 'ID', FILTER_SANITIZE_STRING);
/*    
    $sql = "SELECT * FROM ZDLOGFIL WHERE ZDID='".trim($id)."'";
    $result = $db->singleQuery($sql);
    $row = $db->fetch_array($result);
 	$file_name = $row['ZDFILE'];
*/ 	
    $file_name = get_file_from_id($id);
// 	echo "FILE NAME: $file_name<br>";
/* 	   
	if ($actionContext->getForm()=="COMMON"){
		$filename = wi400File::getCommonFile($_GET['CONTEST'], $_GET['FILE_NAME']);
	}
	else{
		if(isset($_GET['CONTEST']) && !empty($_GET['CONTEST'])) {
			$filename = wi400File::getUserFile($_GET['CONTEST'], $_GET['FILE_NAME']);
		}
		else {
			$filename = $_GET['FILE_NAME'];
		}
	}
*/	
 	if ($actionContext->getForm()=="COMMON"){
 	 $filename = wi400File::getCommonFile($_GET['CONTEST'], $file_name);
 	}
 	else{
	 	if(isset($_GET['CONTEST']) && !empty($_GET['CONTEST']) && trim($_GET['CONTEST'])!="") {
	 		$filename = wi400File::getUserFile($_GET['CONTEST'], $file_name);
	 	}
	 	else {
	 		$filename = $file_name;
	 	}
 	}
// 	echo "FILE: $filename<br>";
 	
	// Controllo se il file arriva solamente dalle directory abilitate al download
	$abilitato = check_download_file_abil($filename);
	
	if($abilitato!==true) {
//		$msg = _t("Download Bloccato");
		$msg = _t("NOT_AUTHORIZED_DOWNLOAD");
?>
		<script>
			alert('<?= $msg?>');
			self.close();
		</script>
<?php
	}
	else {	
//		echo "URL: https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."<br>";
		$filename = rawurldecode($filename);
//		echo "FILENAME: $filename<br>";

//		$fileDownloadName = str_ireplace(" ", "_", $filename);

		if (file_exists($filename)) {
			header("Content-Type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"".basename($filename)."\";");
			header("Content-Length: ".filesize($filename));	
//			header('Content-transfer-encoding: binary');
		
/*		
			$file_handle = fopen($filename, "rb");
			
			while(!feof($file_handle)) {
				echo fread($file_handle, 65536);
				flush();
				if (connection_status () != 0) {
					@fclose($file_handle);
					die();
				}
			}
*/
			// Pulisco il buffer prima di inserire lo stream del file
			ob_clean();
			flush();
			readfile($filename);
		} 
		else {
?>
			<script>
				alert("File inesistente.");		
				self.close();
			</script>
<?
		}
	}
?>