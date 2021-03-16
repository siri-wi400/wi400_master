<?php

$subfile = new wi400Subfile($db, "OBJLIST", $settings['db_temp'], 10);

$array = array();
$array['NAME']=$db->singleColumns("1", "10", "", "Nome" );
$array['LIBRE']=$db->singleColumns("1", "10", "", "Libreria" );
$array['DESCRIZIONE']=$db->singleColumns("1", "50", "", "Descrizione" );

$subfile->inz($array);

require_once $routine_path."/os400/wi400Os400Object.cls.php";

if(isset($_REQUEST['LU_FROM_QUERY']) && !empty($_REQUEST['LU_FROM_QUERY'])) {
	$sql = $_REQUEST['LU_FROM_QUERY'];
	$res = $db->query($sql, false, 0);
	while($row = $db->fetch_array($res)) {
		$nome = $row['NAME'];
		$libreria = $row['LIBRARY'];
		
		$list = new wi400Os400Object($tipoOggetto, $libreria, $nome);
		$list->getList();
		
		while ($obj_read = $list->getEntry()) {
			$dati = array(
				$obj_read['NAME'],
				$obj_read['LIBRARY'],
				$obj_read['DESCRIP']
			);
		
			$subfile->write($dati);
		}
	}
}
else {
	$list = new wi400Os400Object($tipoOggetto, $libreria, $nome);
	$list->getList();
	
	while ($obj_read = $list->getEntry()) {
		$dati = array( 
			$obj_read['NAME'],
			$obj_read['LIBRARY'],
			$obj_read['DESCRIP']
		);
		
		$subfile->write($dati);
	}
}

$subfile->finalize();

$miaLista = new wi400List("OBJLIST", true);

$miaLista->setFrom($subfile->getTable());
$miaLista->setOrder("NAME, LIBRE");

$where = "";
if(isset($_REQUEST['LU_WHERE']) && $_REQUEST['LU_WHERE']!="") {
	$where = $_REQUEST["LU_WHERE"];
}
$miaLista->setWhere($where);

$cols = getColumnListFromTable($subfile->getTableName(), $settings['db_temp']);
$miaLista->setCols($cols);
$miaLista->setPassKey('codobj');
// Numero lavoro lo voglio a Destra

// aggiunta chiavi di riga
$miaLista->addKey("NAME");

// Aggiunta filtri
$toListFlt = new wi400Filter("NAME");
$toListFlt->setDescription("Nome");
$toListFlt->setFast(true);
$miaLista->addFilter($toListFlt);

// Verifico se mi è stato passato in $_REQUEST un eventuale onchange
if (isset($_REQUEST["ONCHANGE"]) AND $_REQUEST["ONCHANGE"] != ""){
	$str = addslashes($_REQUEST["ONCHANGE"]);
	$miaLista->setPassKeyJsFunction($str);
}

$miaLista->dispose();