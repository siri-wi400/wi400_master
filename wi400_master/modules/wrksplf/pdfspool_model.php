<?php
	ini_set("memory_limit","1000M");
	$myAction = new wi400Action();
	
	$fields = $db->columns('FLOGCONV', Null, True);
//	echo "FIELDS:<pre>"; print_r($fields); echo "</pre><br>";
	
	$dati_conv = array();
	if(count($wi400List->getSelectionArray())==1) {
		$keys = $wi400List->getSelectionArray();
		foreach ($keys as $key => $value) {
			break;
		}
		$keyArray = explode("|",$key);
		//$keyArray = getListKeyArray("WRKSPLF");
		
		$jobname=   $keyArray[0];    //job
		$jobuser=   $keyArray[1];    //user
		$jobnumber= $keyArray[2];    //nbr
		$splfname=  $keyArray[3];    //splf
		$splfnbr=   $keyArray[4];    //splfnbr
		$spluserdata = $keyArray[5];
		$splmodulo = $keyArray[6];
		$splpagenbr = $keyArray[7];
		
		$values = array(
			$jobuser,
			$jobname,
			$jobnumber,
			$dbTime,
			$dbTime,
			"",
			"",
			$splmodulo,
			$spluserdata,
		);
		
		for($i=1; $i<=$settings['modelli_pdf_keys']; $i++) {
			$values[] = "";
		}
		
		for($i=1; $i<=$settings['modelli_pdf_user_keys']; $i++) {
			$values[] = "";
		}
		
		$values[] = "";
		$values[] = "";
		$values[] = "";
		$values[] = 1;
		$values[] = $_SESSION['user'];
		$values[] = "";
		
//		echo "VALUES:<pre>"; print_r($values); echo "</pre><br>";

		$dati_conv = array_combine($fields, $values);
//		echo "FIELD VALUES:<pre>"; print_r($dati_conv); echo "</pre><br>";
	}

?>