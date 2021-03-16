<?php
$tabella = $_REQUEST["TABELLA"];
$sql = "SELECT * FROM FTABTABE WHERE TABSIG='" . $tabella ."'";
$row = $db->fetch_array($db->singleQuery($sql));
$actionContext->setLabel("Elementi tabella ".$tabella. " ".$row['TABTIT']);
?>

