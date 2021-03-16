<?php
   // Test Performance
   $wi400Debug=True;
   $time_start = getMicroTime();   
   $sql = 'SELECT * FROM FMHPLOAR';
   getMicroTimeStep('Inizio Query!');
   $result = $db->query($sql, False, 0);
   // Routine RTLART
   $rtlart = new wi400Routine('RTLART', $connzend);
   $rtlart->load_description();
   //$rtlart->prepare();   
   getMicroTimeStep('Inizio Ciclo!'); 
   $i=0;  
   while ($row = $db->fetch_array($result)) {
   	
   	    $i++;
   		$rtlart->prepare();
	    $rtlart->set('NUMRIC',1);
	    $rtlart->set('DATINV', date("Ymd"));
		$rtlart->set('ARTICOLO',$row['MHPCDA']);
	    $rtlart->call();
	    $arti = $rtlart->get('ARTI');	
	    //echo "<br>Articolo:".$row['MHPCDA']."-".$arti['MDADSA'];
	    getMicroTimeStep('Decoded:'.$row['MHPCDA']."-".$arti['MDADSA']);
	    if ($i==100) break;   
   	
   }
   getMicroTimeStep('Fine Ciclo!');    

   
   $phpCode = new wi400PhpCode();
   $phpCode->addFile($moduli_path."/example/example0_view.php");
   $phpCode->dispose();
   
?>