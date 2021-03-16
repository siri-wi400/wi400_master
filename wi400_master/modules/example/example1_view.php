<?php
/*getMicroTimeStep("Inizio");
system('qsh -c "datarea -r -s 66-100 -l /QSYS.LIB/PHPLIB.LIB/DTAZEMA.DTAARA"',  $retval);
//echo "<br>Seriale del sistema";
//system('sysval QSRLNBR',  $retval);
//echo "<br>Risultato: ".$retval;
getMicroTimeStep("Lettura classica");

$ret = data_area_read("PHPLIB/DTAZEMA", 66, 50);
echo $ret;
getMicroTimeStep("Fine");
exit();*/
/* Risultato
Inizio: 1370948064.9623 (1370948064.9623 dallo step precedente)
INFO@SIRI-INFORMATICA.IT 
Lettura classica: 1370948065.1983 (0.23606586456299 dallo step precedente)
INFO@SIRI-INFORMATICA.IT
Fine: 1370948065.2687 (0.070353031158447 dallo step precedente)
*/

getMicroTimeStep("Inizio");
$mda = getUniqueFile('FMAFENTI');

echo "<br>Tabelle per FMAFENTI: $mda";
$query = "SELECT * FROM PHPTEMP/$mda";
$result = $db->query($query);
$row = $db->fetch_array($result);
print_r($row);

getMicroTimeStep("Fine");

exit();


executeCommand("CPYF FROMFILE(ZWIDEMO/ANCL200F) TOFILE(QTEMP/ANCL200F) MBROPT(*REPLACE) CRTFILE(*YES)");
executeCommand("CPYF FROMFILE(PHPLIB/SIR_USERS) TOFILE(QTEMP/SIR_USERS) MBROPT(*REPLACE) CRTFILE(*YES)");


/*$query="CREATE table phptemp/pippo_pluto_paperino as (select * from phplib/sir_users) definition only";
$sql="insert into phptemp/pippo_pluto_paperino  
select * from QTEMP/sir_users            
with nc                                   ";
	$InputXML   = "<?xml version='1.0'?>
	<script>
	<sql>
	<query>$query</query>
	</sql>
	<script>";
	$OutputXML = callXMLService($InputXML);
	$InputXML   = "<?xml version='1.0'?>
	<script>
	<sql>
	<query>$sql</query>
	</sql>
	<script>";
	$OutputXML = callXMLService($InputXML);*/
    

	// anagraphic customers list
   	$_myList = new wi400List("EXAMPLE1_LIST", true);

   	
	$_myList->setFrom("QTEMP".$settings['i5_sep']."ANCL200F");
    // show all columns from tabel
	//$cols = getColumnListFromTable("ANCL200F", "ZWIDEMO");
	$_myList->setCols(getColumnListFromTable("ANCL200F", "ZWIDEMO"));
    // data rendering on HTML 
	$_myList->dispose();
   /*$phpCode = new wi400PhpCode();
   $phpCode->addFile($moduli_path."/example/example1_view.php");
   $phpCode->dispose();*/

	// anagraphic customers list
   	$_myList = new wi400List("EXAMPLE1_LIST2", true);
    $table ="SIR_USERS";
   	$qtemptablename = getQTEMPTable($table);
	$_myList->setFrom("PHPTEMP".$settings['i5_sep'].$qtemptablename);
    // show all columns from tabel
	//$cols = getColumnListFromTable("SIR_USERS", "ZWIDEMO");
	$_myList->setCols(getColumnListFromTable("SIR_USERS", "PHPLIB"));
    // data rendering on HTML 
	$_myList->dispose();
