<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once $routine_path."/classi/wi400Session5250.cls.php";
$open = new wi400Session5250();
$open->setAzione("@127");
/*$open->addKey("SENDKEY", $_SESSION['user']."{TAB}");
$open->addKey("SENDKEY", getUserPassword()."{TAB}");
$open->addKey("SENDKEY", "zgetidc"."{TAB}");
$open->addKey("SENDKEY", "{ENTER}");
$open->addKey("SENDKEY", "{ENTER}");*/

$open->addKey("SENDKEY", $open->getId()."{TAB}");
$open->addKey("SENDKEY", "{TAB}");

$open->addKey("SENDKEY", "0000018{TAB}");
$open->addKey("SENDKEY", "{ENTER}");

$open->open();


