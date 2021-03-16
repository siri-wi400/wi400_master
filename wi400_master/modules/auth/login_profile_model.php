<?php

			// Recupero librerie normalmente
			loadUserLibraries($_SESSION['user']);
			
			// REDIRECT HOME
			goHeader($appBase."index.php");
			//header("Location: ".$appBase."index.php");
			exit();
		
?>