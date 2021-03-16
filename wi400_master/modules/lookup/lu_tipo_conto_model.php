<?php

	require_once $moduli_path."/costoProdotto/costoProdotto_commons.php";
	
	$subfile = new wi400Subfile($db, "LU_TIPO_CONTO_LST", $settings['db_temp'], 20);
	    
    $array = array();
    $array['COD_TIPO_CONTO']=$db->singleColumns("1", "2");
	$array['DES_TIPO_CONTO']=$db->singleColumns("1", "100");

	$subfile->inz($array);
	
	// creazione riga
	foreach($tipo_conto_array as $key => $val) {
		$dati = array(
		    $key,
		    $val
		);
		$subfile->write($dati);
	}
	$subfile->finalize();

?>