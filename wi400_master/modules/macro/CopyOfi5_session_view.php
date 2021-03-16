<?php
	$starter = new wi400Applet();
	$starter->setCode("Wi400Macro.class");
	$starter->setArchive("modules/macro/WI400MACRO.jar");
	$starter->addParam("zipName", $zipName);
	$starter->addParam("userName", $_SESSION["user"]);
	
	$myUrl = "http://".$_SERVER['HTTP_HOST'].$appBase;
    $myUrl = substr($myUrl, 0, strlen($myUrl)-1);
    
	$starter->addParam("serverUrl", $myUrl."/modules/macro/download.php?");
	$starter->addParam("closeUrl", $myUrl."/modules/macro/close.php?I5_SESSION=");
	$starter->addParam("i5Session", "PO5".substr($macroClass->getId(),3,7));
	

	$starter->setWidth(240);
	$starter->setHeight(190);

	$starter->dispose();	
		
	if (isset($_GET["PARENT_ID"]) && $_GET["PARENT_ID"] != ""){
		$myButton = new wi400InputButton("CANCEL_BUTTON");
		$myButton->setScript('document.body.innerHTML="";closeLookUpAndReloadList("'.$_GET["PARENT_ID"].'")');
		$myButton->setLabel(_t('CHIUDI'));
		$buttonsBar[] = $myButton;
	}
			
?>
