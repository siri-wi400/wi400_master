<?php

	$wi400ImageZoom = new wi400Image("ZOOM");
	$wi400ImageZoom->setImgType($_GET["IMAGE_TYPE"]);
	$wi400ImageZoom->setObjType($_GET["OBJ_TYPE"]);
	$wi400ImageZoom->setUrl($_GET["IMAGE_NAME"]);
	$wi400ImageZoom->setDirectUrl($_GET["DIRECT_URL"]);
	$wi400ImageZoom->setWidth(600);
	$wi400ImageZoom->dispose();
	
?>