<?php
	if (!isset($_SESSION['appBase'])) {
		$arr = explode("&", filter_input(INPUT_SERVER, 'REQUEST_URI'));
		$name2 = $arr[0];
		
		$name = explode('/',$name2);
		if (count($name)<=2) {
			$appBase = "/";
		} else {
			$name = explode('/',filter_input(INPUT_SERVER, 'REQUEST_URI'));
			$appBase = "/".$name[1]."/";
		}
		$_SESSION['appBase']=$appBase;
	}
	$appBase = $_SESSION['appBase'];
?>
