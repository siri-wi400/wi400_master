<?php
	require_once 'announce_message_common.php';
	
	$azione = $actionContext->getAction();
	
	if($actionContext->getForm()=="DEFAULT") {
		$id = $_REQUEST['CODICE'];
		//$id = 2;
		
		$announce = new wi400AnnounceMessage();
		$announce->updateLogNotifica($id);
		
		$mess = $announce->getTestataWithLog($id);
		
		if($mess) {
			$html = "";
			$body = "";
			if($mess['TESFMT'] == "HTML") {
				$body = $announce->getHtmlContent($id);
			}else {
				$body = $announce->getTxtContent($id);
			}
			
			$allegati = $announce->getAllegati($id);
			
			//showArray($allegati);
			
			if($body) {
				if(is_array($body)) {
					$html .= "<p style='word-wrap: break-word; display: inline;'>";
					$html .= implode("<br/>", $body);
					$html .= "</p>";
				}else {
					$html .= $body;
				}
			}
			
			if($allegati) {
				
				
				$array_allegati = array();
				$html .= "<br/><br/>";
				foreach($allegati as $chiave => $alleg) {
					
					$path_info = pathinfo($alleg['ATCATC']);
					//showArray($path_info);
					$formato = $path_info['extension'];
					$path_allegato = $path_info['basename'];
					
					$id_file = create_download_id($alleg['ATCATC'], $formato, 'MESSAGGI');
					
					//showArray($path_images);
					$image_format = "txt.png";
					if(isset($path_images[$formato])) {
						$image_format = $path_images[$formato];
					}
					
					if(strtoupper($formato)=="PDF") {
						// Anteprima PDF
						$array_allegati[] = "<a class='allegati_mess' onClick='openWindow(_APP_BASE + APP_SCRIPT + \"?t=FILEVIEW&DECORATION=clean&APPLICATION=pdf&ID=$id_file\", \"buttonAction\", \"900\", \"700\");' style='color: blue; cursor: pointer; background-image: url(\"/upload/{$image_format}\");background-size: 22px 100%;'>
								{$path_allegato}
							</a>";
					}
										
					else if(in_array(strtoupper($formato), array("JPG"))) {
						// ANTEPRIMA IMMAGINE
						$img_html = get_image_base64($alleg['ATCATC'], 80);
						
						// Download File
						$array_allegati[] = "<a class='allegati_mess' onClick='openWindow(_APP_BASE + APP_SCRIPT + \"?t={$azione}&f=EXPORT_FILE&FILENAME=$id_file&FORMATO={$formato}\", \"buttonAction\", \"700\", \"500\", true, true, false, \"closeLookUp()\");' style='color: blue; cursor: pointer;'>
								{$img_html}
							</a>";
					}	
									
					else {
						// Download File
						$array_allegati[] = "<a class='allegati_mess' onClick='openWindow(_APP_BASE + APP_SCRIPT + \"?t={$azione}&f=EXPORT_FILE&FILENAME=$id_file&FORMATO={$formato}\", \"buttonAction\", \"700\", \"500\", true, true, false, \"closeLookUp()\");' style='color: blue; cursor: pointer; background-image: url(\"/upload/{$image_format}\");background-size: 22px 100%;'>
								{$path_allegato}
							</a>";
					}
				}
				$html .= implode("&nbsp;&nbsp;&nbsp;", $array_allegati);				
				//$html .= "</div>";
			}
			
			if($mess['LOGRIS'] != 'S') {
				if($mess['TESRPY'] != "N") {
					if($mess['TESRPYT'] != 'SI-NO') {
						$html .= "<br/><br/><div id='cont_risp_{$id}'>
												<input id='txt_risp_{$id}' type='text'/>
												<button id='button_mess_{$id}' onClick='update_read_message(\"$id\", jQuery(\"#txt_risp_{$id}\").val(), \"txt\")'>Rispondi</button>
											</div>";
					}else {
						$html .= "<br/><br/>
									<div id='cont_risp_{$id}'>
										<button class='confirm_mess_button' onClick='update_read_message(\"$id\", \"SI\")' style='background-image: url(\"themes/common/images/yav/valid.gif\");'>Si</button>&nbsp;&nbsp;
										<button class='confirm_mess_button' onClick='update_read_message(\"$id\", \"NO\")' style='background-image: url(\"themes/common/images/button_cancel.png\");'>No</button>
									</div>";
					}
				}else {
					if($mess['LOGLET'] == getDb2Timestamp("*INZ")) {
						$html .= "<br/><br/><button id='button_conf_read_{$id}' class='confirm_mess_button' onClick='update_read_message(\"{$id}\")' style='background-image: url(\"themes/common/images/yav/valid.gif\");'>Ho letto</button>";
					}else {
						$html .= "<br/><br/><span class='confirm_mess_button' style='background-image: url(\"themes/common/images/yav/valid.gif\");'>Ho letto</span>";
					}
				}
			}else {
				$html .= "<br/><br/><font id='risposta_{$id}'>Risposta: {$mess['LOGRPT']}</font>";
			}
			//sleep(5);
			echo $html;
			// LZ Bottone con Link Ad Azione, se finestra devo chiudere lookup
			$datiExtra = $announce->getExtraParm($id);
			if (isset($datiExtra['BUTTON_ENABLE']) && $datiExtra['BUTTON_ENABLE']=='S') {
				$spacer = new wi400Spacer(); 
				$spacer->dispose();
				$myButton = new wi400InputButton("ANNOUNCE_$id");
				$myButton->setLabel($datiExtra['BUTTON_LABEL']);
				// costruzione SCRIPT
				$myScript = "closeWindow();";
				$azione="";
				$form="";
				$gateway="";
				if (isset($datiExtra['BUTTON_AZIONE'])) {
					$azione = $datiExtra['BUTTON_AZIONE'];
				}
				if (isset($datiExtra['BUTTON_FORM'])) {
					$form = $datiExtra['BUTTON_FORM'];
				}
				if (isset($datiExtra['BUTTON_GATEWAY'])) {
					$azione .= '&g='.$datiExtra['BUTTON_GATEWAY'];
				}
				if (isset($datiExtra['BUTTON_EXTRAPARM'])) {
					$azione .= $datiExtra['BUTTON_EXTRAPARM'];
				}
				$myScript .= "reloadSelAction('".$azione."','$form');"; 
				$myButton->setScript($myScript);
				//die($myScript);
				$myButton->dispose();
			}
		}else {
			echo "<center>Errore contenuto messaggio</center>";
		}
		
		die();
	}
	if($actionContext->getForm()=="CONFIRM_READ") {
		$id = $_REQUEST['CODICE'];
		
		$announce = new wi400AnnounceMessage();
		
		if(isset($_REQUEST['TESTO_RISP'])) {
			echo $_REQUEST['TESTO_RISP'];
			
			$announce->updateLogLetto($id);
			$announce->updateLogRisposta($id, $_REQUEST['TESTO_RISP']);
		}else {
			$announce->updateLogLetto($id);
		}
		
		die();
	}
	if($actionContext->getForm()=="EXPORT_FILE") {
		$export = new wi400ExportList();
		$key_allegati = getListKeyArray("MANAGER_MESSAGES_LIST_ALLEGATI");
		
		if(count($key_allegati)) {
			$format = explode(".", $key_allegati['ATCATC']);
			$format = $format[(count($format))-1];
			$TypeImage = $format;
			$filename = $key_allegati['ATCATC'];
		}else {
			$TypeImage = $_REQUEST['FORMATO'];
			$filename = get_file_from_id($_REQUEST['FILENAME']);
		}
/*		
		// ANTEPRIMA IMMAGINE
		if(in_array(strtoupper($TypeImage), array("JPG"))) {
			$img_html = get_image_base64($filename, 80);
				
			echo $img_html;
		}
*/		
		$temp = "";
		
		//echo $TypeImage."<br/>";
		//echo $filename."<br/>";
			
		$export->setDatiExport($filename, $temp, $TypeImage, "");
				
		downloadDetail($TypeImage, $filename, $temp, _t('ESPORTAZIONE_COMPLETATA'));
//		downloadDetail($TypeImage, $filename, $temp, _t('ESPORTAZIONE_COMPLETATA'), "", "", array(), array(), true);
	}
	
	
	