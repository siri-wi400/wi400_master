<?php

	/**
	* file:	announce.php
	* 
	****************************************************************************/ 
	
	if(!isset($_REQUEST['ALL_MESS']) && (isset($_SESSION['NAVIGAZIONE_TABLET_ATTIVA']) || (isMobile() && (isset($settings['mobile_init']) && $settings['mobile_init']==True)))) {
		$actionContext->gotoAction("MENU_TABLET", "DEFAULT", true, true);
	}
	
	if(!isset($area))
		$area = "";
	
	if(isset($settings['messages_enable']) && $settings['messages_enable']) {
		require_once "announce_commons.php";
		
		$announce = new wi400AnnounceMessage();
		$accordion = new wi400AnnounceContent("accordion");
		
		$count = $announce->getNewMessageHome();
		$stato_mess = array("1");
		$tipo_mess = array("*HOME");
		if(isset($_GET['AZIONE'])) {
			//echo "Azione: ".$_GET['AZIONE']."<br/>";
			$id = $announce->getMessages(array("*ACTION"), $stato_mess, $_GET['AZIONE']);
			$accordion->setVista("*ACTION");
			$accordion->setAzione($_GET['AZIONE']);
		}else {
			$mess_scaduti = false;
			if(isset($_GET['ALL_MESS'])) {
				$stato_mess[] = "2";
				$tipo_mess[] = "*ACTION";
				
				if($_GET['ALL_MESS'] == 2) {
					$mess_scaduti = true;
				}
				$sql = "DELETE FROM zmsgnot where notusr='{$_SESSION['user']}'";
				$db->query($sql);
			}else {
				echo "<script>
						if(typeof(nuovo_mess) != 'undefined') {
							clearInterval(nuovo_mess);
						}
						jQuery('#icon_announce_mess').attr('src', 'themes/common/images/message.png');
					</script>";
			}
			$id = $announce->getMessages($tipo_mess, $stato_mess, null, null, $mess_scaduti, $area);
		}
		
		$accordion->setStatoMess($stato_mess);
		
		
		$debug = false;
		
		if($debug) {
			echo "<br>Nuovi Messaggi:".$count;
			echo "<pre>";
			print_r($id);
			echo "</pre>";
		}
		
		
		//$accordion->setTarget("");
		
		foreach ($id as $key => $valore) {
			$titolo = $valore['TITOLO'];
/*			
			// IMMAGINI NEL TITOLO DEL MESSAGGIO
			$img_atc = $announce->getAllegati($key);
			
			if(isset($img_atc) && !empty($img_atc)) {
				$img = "";
				
				foreach($img_atc as $row_atc) {
//					echo "IMG: ".$row_atc['ATCATC']."<br>";
					
					$format = explode(".", $row_atc['ATCATC']);
					$format = $format[(count($format))-1];
//					echo "FORMAT: $format<br>";
					
					if(in_array(strtoupper($format), array("JPG"))) {
						$img .= get_image_base64($row_atc['ATCATC'], 80);
						$img .= " ";
					}
				}
				
				if(!empty($img))
					$titolo .= " ".$img;
			}
*/			
			$mess = new wi400AccordionMessage($key);
			$mess->setHeader($titolo);
			$mess->setFormatoTesto($valore['TESFMT']);
			$mess->setType($valore['TESTYP']);
			$mess->setLogNot($valore['LOGNOT']);
			if(isset($mess_icon[$valore['TESGRP']])) {
				$mess->setRightIcon($mess_icon[$valore['TESGRP']]);
			} 
			if (isset($type_icon[$valore['TESTYP']])) {
				$mess->setRightIcon($type_icon[$valore['TEPTYP']]);
			}
			/*if($valore['TESFMT'] == "HTML") {
				$mess->setBody($announce->getHtmlContent($key));
			}else {
				$mess->setBody($announce->getTxtContent($key));
			}*/
			//$mess->setRisposta($valore['TESRPY']);
			//$mess->setAllegati($announce->getAllegati($key));
			$accordion->addMessage($mess);
			
			if($debug) {
				echo "<br>HTML:".$announce->getHtmlContent($key);
				echo "<pre>";
				print_r($announce->getTxtContent($key));
				echo "</pre>";
			
				showArray($announce->getAllegati($key));
			}
		}

		if(count($id)) {
			if(isset($_GET['ALL_MESS'])) {
				//Mostra anche i messaggi scaduti
				$myButton = new wi400InputButton('SHOW_ALL_MESS');
				$myButton->setLabel("Messaggi scaduti");
				$myButton->setAction("ANNOUNCE");
				$myButton->setForm("ACTION&ALL_MESS=2");
				$myButton->dispose();
			}
			
			$accordion->dispose();
		}else {
			if(isset($_GET['ALL_MESS'])) {
				echo "<center><h1>Non ci sono messaggi</h1></center>";	
			}
		}
	}
	$from_announce="";
	if(isset($settings['widget_announce']) && $settings['widget_announce'] && !isset($_GET['ALL_MESS'])) {
		//$from_announce = "style='position: absolute'";
		$from_announce = "style='position: absolute; top: 0px;'";
		echo "<br/><div id='announceWidget'>";
			echo '<img src="'.$temaDir.'images/welcome.png" alt="WI400">';
			include $moduli_path."/announce/widget_model.php";
			include $moduli_path."/announce/widget_view.php";
			
			//echo '<center><img src="'.$temaDir.'/images/welcome.png" alt="WI400"></center>';
			
		echo "</div>";
	}
 
	if($actionContext->getForm()=="DEFAULT" && !$from_announce) {?>
		<table width="100%" border="0">
			<tr>
				<td align="center"><img src="<?= $temaDir ?>/images/welcome.png" alt="WI400"></td>
			</tr>
		</table>	
	<?}?>
