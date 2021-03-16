<?php

$debugDetail = new wi400Detail($azione."_DETAIL");
$debugDetail->setTitle("CONSOLE DEBUG");

$debugCheck = new wi400InputSwitch("DEBUG_ON");
$debugCheck->setLabel("Abilita debug");
if (isset($_SESSION["DEBUG"])) {
	$debugCheck->setChecked(true);
}else{
	$debugCheck->setChecked(false);
}
$debugDetail->addField($debugCheck);

$debugCheck = new wi400InputSwitch("XMLSERVICE_DEBUG");
$debugCheck->setLabel("Abilita xmlservice debug");
if (isset($_SESSION["XMLSERVICE_DEBUG"])) {
	$debugCheck->setChecked(true);
}else{
	$debugCheck->setChecked(false);
}
$debugDetail->addField($debugCheck);

$debugButton = new wi400InputButton("SAVE_BUTTON");
$debugButton->setAction("DEBUG");
$debugButton->setForm("SAVE");
$debugButton->setLabel("Salva");
$debugDetail->addButton($debugButton);

$debugDetail->dispose();