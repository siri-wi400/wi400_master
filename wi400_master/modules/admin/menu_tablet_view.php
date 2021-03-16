<?
if($actionContext->getForm()=="DEFAULT") {
	$actionContext->setLabel("Navigazione con Tablet");
	// Parametro globale per informare il sistema che stiamo lavorando con un tablet.
	$_SESSION['NAVIGAZIONE_TABLET_ATTIVA']="SI";
	// Esplodo il menu con la azioni presenti
	if (isset($_GET['HISTORYCLEAN'])) {
		$history->delete();
	}
	if (isset($_GET['NEXT']) && $_GET['NEXT']!="") {
		$codmenu=$_GET['NEXT'];
		// Salvo in schiera il menu precedente
		$_SESSION[$_GET['NEXT']]=$_GET['PREVIOUS'];
	} else {
		$dati = rtvUserInfo($_SESSION['user']);
		$codmenu=$dati['MENU'];
		$_SESSION['NAVIGAZIONE_TABLET_PREVIOUS']="";
		$_SESSION['NAVIGAZIONE_TABLET_NEXT']="";
	}
	$menu = rtvMenu ( $codmenu );
	$stmt="";
	if ($stmt == "") {
		$mylanguage = $_SESSION['USER_LANGUAGE'];
		if (isset($_SESSION['CUSTOM_LANGUAGE'])) {
			$mylanguage = $_SESSION['CUSTOM_LANGUAGE'];
		}
		$extra_language = getLanguageID($mylanguage);
		if (isset($settings['multi_language']) && $settings['multi_language']==True && $extra_language) {
			$sql = "select * from " . $AS400_azioni . " left join FLNGTRST ON AZIONE=KEY AND LANG='".$extra_language."' AND ARGO='AZIONI' where AZIONE=?";
			$stmt = $db->prepareStatement ( $sql );
		} else {
			$sql = "select * from " . $AS400_azioni . " where AZIONE=?";
			$stmt = $db->prepareStatement ( $sql );
		}

	}
	// Verifico se e' un menu
	if ($menu) {
		//	Leggo le azioni del menu
		$componenti = array ();
		$componenti = explode ( ";", $menu ['AZIONI'] );
		echo "<table><tr>";
		$count=0;
		foreach ( $componenti as $k => $v ) {
			// verifico se c'è un form particolare
			$arrayAzione = explode(":", $v);
			$azione = $arrayAzione[0];
			$des_agg = "";
			$form="";
			if (isset($arrayAzione[1])) {
				$des_agg = " ".str_replace("_", " ", $arrayAzione[1]);
				$form = $arrayAzione[1]; 
			}
			$result = $db->execute ( $stmt, array (trim ( $azione ) ) );
			$azioni = $db->fetch_array ( $stmt, Null, True );
			$count++;
			if ($count==8){
				$count=1;
				echo "</tr><tr>";
			}
			// Testo il tipo per evitare possibili dump o costruzioni anomale del menu
			if (($azioni ['TIPO'] == "A") || ($azioni ['TIPO'] == "M") || ($azioni ['TIPO'] == "T") || $azioni['TIPO'] == 'L') {
				if ($azioni ['TIPO'] == "A" || $azioni['TIPO'] == "T" || $azioni['TIPO'] == 'L') {
					$options = array ();
					$script = array ();
					$des_azione = $azioni['DESCRIZIONE'];
					if (isset($azioni['STRING']) && $azioni['STRING']!="") {
						$des_azione = $azioni['STRING'];
					}
					
					if(!isset($azioni['TABLETICO']) || !$azioni['TABLETICO']) {
						$class_link = '';
						// Salvo tracciato azione in sessione per ottimizzazione accessi DB
						$myButton = new wi400InputButton($azioni ['AZIONE']);
						$myButton->setLabel($des_azione);
						if($azioni['TIPO'] == 'T') {
							$azioni['AZIONE'] = "TELNET_5250&g=FROM_MENU&AZIONE_5250=".$azioni['AZIONE'];
						}
						if($azioni['TIPO'] == 'L') {
							$myButton->setTarget('WINDOW');
							
							$win = '';
							if($azioni['URL_OPEN'] == '_popup') {
								$win = '&TYPE=popup';
							}
							$azioni['AZIONE'] = "OPEN_LINK&LINK=".$azioni['AZIONE'].$win;
							
							$class_link = 'action-link';
						}
						//$myButton->setScript('window.open("index.php?t='.$azioni ['AZIONE'].'&LEFT_MENU_STATUS=close","_self")');
						$myButton->setAction($azioni['AZIONE'].'&LEFT_MENU_STATUS=close');
						$myButton->setButtonClass("cssbutton-active $class_link");
						$myButton->setButtonStyle("background-image: url(./themes/common/images/blue_play.png); ");
						$myButton->setValidation(true);
						echo "<td>";
						$myButton->dispose();
						echo "</td>";
					}else {
					?>
						<td>
							<button class="cssbutton-active" onclick="doSubmit('<?=$azioni['AZIONE']?>&LEFT_MENU_STATUS=close', '', true, false, '', true)">
								<i class="fa <?=$azioni['TABLETICO']?>" style='color: #<?=$azioni['TABLETCOL']?>'></i><label><?=$des_azione?></label>
							</button>
						</td>
					<?php 
					}
				} else {
					$myButton = new wi400InputButton($azioni ['AZIONE']);
					$menu = rtvMenu ($azioni ['AZIONE']);
					$myButton->setLabel($menu['DESCRIZIONE']);
					//$myButton->setScript('window.open("index.php?t=MENU_TABLET&LEFT_MENU_STATUS=close&PREVIOUS='.$codmenu.'&NEXT='.$azioni ['AZIONE'].'","_self")');
					$myButton->setAction('MENU_TABLET&LEFT_MENU_STATUS=close&PREVIOUS='.$codmenu.'&NEXT='.$azioni ['AZIONE']);
					$myButton->setButtonClass("cssbutton-active");
					$myButton->setButtonStyle("background-color:#0B3CEE;background-image: url(./themes/common/images/view_tree.png); ");
					$myButton->setValidation(true);
					echo "<td>";
					$myButton->dispose();
					echo "</td>";
				}
			}
		
		}
		echo "</tr>";
		// Tasto torno indietro
		if (isset($_GET['PREVIOUS']) && $_GET['PREVIOUS']!="") {
			$myButton = new wi400InputButton($_GET['PREVIOUS']);
			$menu = rtvMenu ($_GET['PREVIOUS']);
			$myButton->setLabel($menu['DESCRIZIONE']);
			$previous = "";
			if (isset( $_SESSION[$_GET['PREVIOUS']])) {
				$previous = $_SESSION[$_GET['PREVIOUS']];
			}
			//$myButton->setScript('window.open("index.php?t=MENU_TABLET&LEFT_MENU_STATUS=close&PREVIOUS='.$previous.'&NEXT='.$_GET['PREVIOUS'].'","_self")');
			$myButton->setAction('MENU_TABLET&LEFT_MENU_STATUS=close&PREVIOUS='.$previous.'&NEXT='.$_GET['PREVIOUS']);
			$myButton->setButtonClass("cssbutton-active");
			$myButton->setButtonStyle("background-color:#01DF3A;background-image: url(./themes/common/images/go-back.png); ");
			
			$myButton->setValidation(true);
			echo "<td>";
			$myButton->dispose();
			echo "</td>";			
		}
		echo "</table>";
		// Metto in sessione il menu corrente e quello precedente
		if (isset($_GET['PREVIOUS'])) {
			$_SESSION['NAVIGAZIONE_TABLET_PREVIOUS']=$_GET['PREVIOUS'];
		} 		
		if (isset($_GET['NEXT'])) {
			$_SESSION['NAVIGAZIONE_TABLET_NEXT']=$_GET['NEXT'];
		} else {
			$_SESSION['NAVIGAZIONE_TABLET_NEXT']=$codmenu;
		}
		//db2_free_stmt ( $result );
	}
}	
	?>
	<script>
	jQuery( document ).ready(function() {
		slideMenu("close");
	});
	//setTimeoutFunction("slideMenu();", 300);
	</script>
	<style>
		button i.fa {
			position: absolute;
		    color: #50a1d1;
			text-shadow: 1px 1px 0px white;
		    font-size: 50px;
		    top: 9px;
		    left: 9px;
		}
		
		button.cssbutton-active {
			position: relative;
			background-image: none;
		}
	</style>
