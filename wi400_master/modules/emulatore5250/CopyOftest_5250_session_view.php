<link rel="stylesheet" type="text/css" href="modules/emulatore5250/test_5250_session_style.css">

<?php
if($actionContext->getForm()=="DEFAULT") {
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	
	require_once $routine_path."/classi/wi400_5250Session.cls.php";
	$Sessione5250 = new wi400_5250Session("PIPPO");
	
	$rtlent = new wi400Routine('RUNVIRT', $connzend);
	$rtlent->load_description();
	$rtlent->prepare(True);
	$rtlent->set('OPER',"OPEN");
	$rtlent->set('DATA',"");
	$rtlent->call();
	
	$rtlent->set('OPER',"READ");
	$rtlent->set('DATA',"");
	$rtlent->call();
	
	$dati = $rtlent->get('DATA');
	
	//echo $dati;
	require_once $routine_path."/classi/wi400_5250Session.cls.php";
	$Sessione5250 = new wi400_5250Session("PIPPO");
	$fields = $Sessione5250->parseDataStream($dati);
	
	$display = new wi400_5250Display("PIPPO");
	$display->setFields($fields);
	$html = $display->display();
	//echo '<label>Zoom:</label> <div id="slider"></div><br/>';
	echo $html;
	echo "<div>";
	$myButton = new wi400InputButton('ENTER');
	$myButton->setLabel("Login");
	$myButton->setAction($azione);
	$myButton->setForm("ENTER");
	$myButton->addParameter("UNO", "TANTO");
	$myButton->setButtonClass("ccq-button-active ENTER");
	$myButton->setButtonStyle("background-color:#66FF33");
	$myButton->dispose();
	echo "</div>";
	setKeyAction('ENTER', 'LOGIN');
}
if($actionContext->getForm()=="ENTER") {
	/*$rtlent = new wi400Routine('ZVRTWRIT', $connzend);
	$rtlent->load_description();
	$rtlent->prepare(True);
	$rtlent->set('ID',session_id());
	$rtlent->set('OPZIONI',"03");
	$hex ="0A35F1110635D7C8D7D4C1D5110735D7C8D7D4C1D5";
	$rtlent->set('DATA',$hex);
	$rtlent->call();
	
	// Attesa
	$rtlent = new wi400Routine('ZVRTREAD', $connzend);
	$rtlent->load_description();
	$rtlent->prepare(True);
	$rtlent->set('ID',session_id());
	$rtlent->set('DATA',"");
	$rtlent->call();
	
	$rtlent = new wi400Routine('ZVRTREAD', $connzend);
	$rtlent->load_description();
	$rtlent->prepare(True);
	$rtlent->set('ID',session_id());
	$rtlent->set('DATA',"");
	$rtlent->call();
	
	$dati = $rtlent->get('DATA');*/
    // Verifico i dati modificati
    showArray($_REQUEST);
	require_once $routine_path."/classi/wi400_5250Session.cls.php";
	$display = new wi400_5250Display("PIPPO");
	$modifystring = $display->getModifiedString($_POST);
	
	//echo "<br>STRINGA MODIFICATA:".$modifystring;
	$rtlent = new wi400Routine('RUNVIRT', $connzend);
	$rtlent->load_description();
	$rtlent->prepare(True);
	$rtlent->set('OPER',"WRITE");
	//$hex ="0A35F1110635D7C8D7D4C1D5110735D7C8D7D4C1D5";
	//echo "<br>$hex";
	$hex ="0A35F1".$modifystring;
	echo "<br>$hex";
	$rtlent->set('DATA',$hex);
	$rtlent->call();
	
	$rtlent->set('OPER',"READ");
	$rtlent->set('DATA',"");
	$rtlent->call();
	
	$dati = $rtlent->get('DATA');
	
	$rtlent->set('OPER',"WRITE");
	$hex ="0000880044D9708006000302000000000000000000000000000000000001F3F1F7F9F0F0F20101000000701201F40000007B3100000FC800000000000000000000000000000000";
	$rtlent->set('DATA',$hex);
	$rtlent->call();
	
	$rtlent->set('OPER',"READ");
	$rtlent->set('DATA',"");
	$rtlent->call();
	
	$dati = $rtlent->get('DATA');
	
	
	//echo $dati;
	//require_once $routine_path."/classi/wi400_5250Session.cls.php";
	$Sessione5250 = new wi400_5250Session("PIPPO");
	$fields = $Sessione5250->parseDataStream($dati);
	
	$display = new wi400_5250Display("PIPPO");
	$display->setFields($fields);
	$html = $display->display();
	//echo '<label>Zoom:</label> <div id="slider"></div><br/>';
	echo $html;
	echo "<div>";
	//echo getFunctionKey();
	$myButton = new wi400InputButton('ENTER');
	$myButton->setLabel("ENTER");
	$myButton->setAction($azione);
	$myButton->setForm("ENTER");
	$myButton->addParameter("ENTER", "1");
	$myButton->setButtonClass("ccq-button-active ENTER");
	$myButton->setButtonStyle("background-color:#66FF33");
	$myButton->dispose();
	echo "</div>";
	setKeyAction('ENTER', 'ENTER');
	
}
/*
?>
	<script>
	  	jQuery( function() {
			jQuery("#slider").slider();
		});
	</script>
<?php 
*/
/*$file = fopen("/www/zendsvr/htdocs/wi400_pasin/zAlberto.txt", "w");
fwrite($file, $html);
fclose($file);*/
die("");