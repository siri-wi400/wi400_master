	<?php 
	
	// Recuperare dati tramite API
	    require_once $routine_path."/os400/APIFunction.php";
	    $userSpace = "SPOOLDATA LUCALAV";        
	    // Apro lo spool
		$job_lsg = new wi400Routine('QSPOPNSP', $connzend);
	    $job_lsg->load_description();
		$job_lsg->prepare();
		$job_lsg->set('JOBQUAL',$jobQual);
	    $job_lsg->set('SPOOLNAME', $spoolName);
	    $job_lsg->set('SPOOLNBR', $spoolNbr);
	    $job_lsg->set('BUFFER', -1);	    
	    $job_lsg->call(True);
	    $openSpool = $job_lsg->get('HANDLER');	
	    // Creazione della USERSPACE
		$usr_spc = new wi400Routine('QUSCRTUS', $connzend);
	    $usr_spc->load_description();
		$usr_spc->prepare();
		$usr_spc->set('USERSPACE', $userSpace);
	    $usr_spc->set('INITSIZE', 1000);
	    $usr_spc->set('PUBAUT',"*ALL");
	    $usr_spc->set('REPLACE',"*YES");
	    $usr_spc->set('DESC',"USER SPACE DATI SPOOL");
	    $usr_spc->call(True);	
	    // Leggo lo spool e lo metto nella user space
		$job_lsg = new wi400Routine('QSPGETSP', $connzend);
	    $job_lsg->load_description();
		$job_lsg->prepare();
		$job_lsg->set('HANDLER',$openSpool);
	    $job_lsg->set('FORMAT', $formato);
	    $job_lsg->set('BUFFER', -1);
	    $job_lsg->set('USERSPACE', $userSpace);	    
		$job_lsg->set('END', "*WAIT");
		$job_lsg->call(True);	 
		// Chiusura Spool
		$job_lsg = new wi400Routine('QSPCLOSP', $connzend);
	    $job_lsg->load_description();
		$job_lsg->prepare();
		$job_lsg->set('HANDLER',$openSpool);
		$job_lsg->call(True);	
	    // Recupero dati di Header dello spool
		$rtv_spc = new wi400Routine('QUSRTVUS', $connzend);
	    $tracciato = getApiDS("QSPGETSP", "QSPBQ");	
	    $rtv_spc->load_description(null, $tracciato, True);
		$do = $rtv_spc->prepare();
		$rtv_spc->set('USERSPACE',$userSpace);
	    $rtv_spc->set('OFFSET', 1);
	    $rtv_spc->set('SIZE',128);
	    $do = $rtv_spc->call(True);
	    $header = $rtv_spc->get('DATI');
	    echo "<pre>";
	    print_r($header);
	    echo "</pre>";
	    // Recupero i dati del buffer dello spool
		$rtv_spc = new wi400Routine('QUSRTVUS', $connzend);
	    $tracciato = getApiDS("QSPGETSP", "QSPBR");	
	    $rtv_spc->load_description(null, $tracciato, True);
		$do = $rtv_spc->prepare();
		$rtv_spc->set('USERSPACE',$userSpace);
	    $rtv_spc->set('OFFSET', $header['QSPBQL']+1);
	    $rtv_spc->set('SIZE',40);
	    $do = $rtv_spc->call(True);
	    $buffer = $rtv_spc->get('DATI');
	    echo "<pre>";
	    print_r($buffer);
	    echo "</pre>";
	    //	
	    $partenza = $buffer['QSPBRD']+1;	
	    for ($i=1; $i<= $header['QSPBQN']; $i++) {
	    	// Lettura del GENERAL .. sembra non servire
			$rtv_spc = new wi400Routine('QUSRTVUS', $connzend);
		    $tracciato = getApiDS("QSPGETSP", "QSPBS");	
		    $rtv_spc->load_description(null, $tracciato, True);
			$do = $rtv_spc->prepare();
			$rtv_spc->set('USERSPACE',$userSpace);
		    $rtv_spc->set('OFFSET', $partenza);
		    $rtv_spc->set('SIZE',40);
		    $do = $rtv_spc->call(True);
		    $general = $rtv_spc->get('DATI');
		    echo "<pre>";
		    print_r($general);
		    echo "</pre>";
		    $partenza = $partenza + $buffer['QSPBSG'];
		    $start = $buffer['QSPBRL'];
		    // Recupero i dati da stampare
			$rtv_spc = new wi400Routine('QUSRTVUS', $connzend);

		    $tracciato = array(
				array("Name"=>"QSPBTB", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Text Data Start 
				array("Name"=>"QSPBTC", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Any Data Start 
				array("Name"=>"QSPBTD", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Page Offset
			);
		    $rtv_spc->load_description(null, $tracciato, True);
			$do = $rtv_spc->prepare();
			$rtv_spc->set('USERSPACE',$userSpace);
		    $rtv_spc->set('OFFSET', 224);
		    $rtv_spc->set('SIZE',12);
		    $do = $rtv_spc->call(True);
		    $dati = $rtv_spc->get('DATI');		    
		    echo "<pre>";
		    print_r($dati);
		    echo "</pre>";
		    $start = $start + $buffer["QSPBRM"];
	    }
		$tutto = file_get_contents('/QSYS.lib/lucalav.lib/spooldata.usrspc');
		echo e2a(substr($tutto, 224, 100));
       	
//	}
    function e2a ($e) { 
	$e2a = array(0, 1, 2, 3,156, 9,134,127,151,141, 
	142, 11, 12, 13, 14, 15,16, 17, 18, 
	19,157,133, 8,135, 24, 25,146,143, 
	28, 29, 30, 31,128,129,130,131,132, 10, 23, 27,136,137,138,139,140, 5, 6, 7,144,145, 22,147,148,149,150, 4,152,153,154,155, 20, 21,158, 26,32,160,161,162,163,164,165,166, 
	167,168, 91, 46, 60, 40, 43, 33,38,169,170,171,172,173,174,175, 
	176,177, 93, 36, 42, 41, 59, 94,45, 47,178,179,180,181,182,183,184,185, 
	124, 44, 37, 95, 62, 63,186,187,188, 
	189,190,191,192,193, 
	194, 96, 58, 35, 64, 39, 61, 34,195, 
	97, 98, 99,100,101,102,103,104,105, 
	196,197, 
	198,199,200,201,202,106,107,108,109, 
	110,111,112,113,114,203,204,205,206, 
	207,208,209,126,115,116,117,118,119, 
	120,121,122,210,211,212,213,214,215, 
	216,217,218,219,220,221,222,223,224, 
	225,226,227,228,229,230,231,123, 65, 
	66, 67, 68, 69, 70, 71, 72, 73,232,233, 
	234,235,236,237,125, 74, 75, 76, 77, 
	78, 79, 80, 81, 82,238,239,240,241,242, 
	243,92,159, 83, 84, 85, 86, 87, 88, 89, 90,244,245,246,247,248,249,48, 49, 
	50, 51, 52, 53, 54, 55, 56, 57,250,251, 
	252,253,254,255); 
	$a = ''; 
	for ($i = 0 ; $i < strlen($e) ; $i++) { $a .= chr($e2a[ord(substr($e,$i,1))]); } 
	return $a; 
	}