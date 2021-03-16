<?php

	$spacer = new wi400Spacer(); 

	if(in_array($form,array("MEDIA","PICCO"))) {
		$actionDetail = new wi400Detail($azione."_DET");
		$actionDetail->setColsNum(2);
		
		$myField = new wi400Text("PERIODO");
		$myField->setLabel("Periodo");
		$myField->setValue("Dal $data_ini al $data_fin");
		$actionDetail->addField($myField);
		
		$myField = new wi400Text("FASCIA_ORARIA");
		$myField->setLabel("Fascia oraria");
		$myField->setValue("Dalle $ora_ini alle $ora_fin");
		$actionDetail->addField($myField);
		
		$myField = new wi400Text("TIPO_INT");
		$myField->setLabel("Tipo intervallo");
		$myField->setValue($intervalli_array[$tipo_int]);
		$actionDetail->addField($myField);
		
		$myField = new wi400Text("TIPO_DATI");
		$myField->setLabel("Tipo dati");
		$myField->setValue($tipo_dati_array[$form]);
		$actionDetail->addField($myField);
		
		$myField = new wi400Text("SUBSYS");
		$myField->setLabel("Sottosistemi");
		$myField->setValue(implode("<br>",$sel_subsys));
		$actionDetail->addField($myField);
		
		// Download grafico
		$myButton = new wi400InputButton('DOWNLOAD_BUTTON');
		$myButton->setLabel("Download grafico");
		$myButton->setScript("openWindow(_APP_BASE + APP_SCRIPT + '?DECORATION=lookUp&t=$azione&f=DOWNLOAD_GRAFICO', 'Download grafico')");
		$actionDetail->addButton($myButton);
		
		$hiddenField = new wi400InputHidden("IDLIST");
		$hiddenField->setValue($_REQUEST['IDLIST']);
		
		$actionDetail->dispose();
		
		$spacer->dispose();
		
		if(!empty($lavori) || $lavori!="") {
			// Get the handler to prevent the library from sending the
			// image to the browser
			$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
			 
			// Stroke image to a file and browser
			 
			// Default is PNG so use ".png" as suffix
			$graphFile = wi400File::getUserFile("tmp", $filename);
			
			$graph->img->Stream($graphFile);
	
//			echo "<img src='".$graphFile."'>";
//			echo "<img name='myimage' id='myimage' src='".$appBase."index.php?DECORATION=clean&t=FILEDWN&CONTEST=tmp&FILE_NAME=".$filename."' style='background=#FFFFFF;border: 1px solid #CCCCCC'>";
			$link = create_file_download_link($filename, "tmp");
			echo "<img name='myimage' id='myimage' src='".$link."' style='background=#FFFFFF;border: 1px solid #CCCCCC'>";
		}
	}
	else if($form=="DOWNLOAD_GRAFICO") {
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
		
		$temp = "tmp";
		$TypeImage = "png.gif";
				
		downloadDetail($TypeImage, $filename, $temp, "Esportazione completata");
	}

?>