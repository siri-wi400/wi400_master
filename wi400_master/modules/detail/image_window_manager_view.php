<?php
//$azioniDetail = new wi400Detail('IMAGE_WINDOW_MANAGER_DETAIL', false);

$myImage = new wi400Image('detailImage');
$myImage->setShowContenitore(true);
$myImage->setSizeContenitore(150);
$myImage->setManager(true);
$myImage->setShowZoom(true);
$myImage->setObjCode($codice);
$myImage->setObjType($tipo);
$myImage->setMaxCount(100);
$myImage->setHorizontalView(true);
$myImage->setImgType(1);
$myImage->dispose();

//$azioniDetail->addImage($myImage);
//$azioniDetail->dispose();