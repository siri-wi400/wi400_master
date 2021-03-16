<?php
// RICHIAMO RTLER
getMicroTimeStep("INIZIO ->Richiamo RTLENT");
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once("Java.inc");
require_once("instance_field_standar.php");
//$System = java("java.lang.System");
//echo $System->getProperties();
//
$AS400 = new java("com.ibm.as400.access.AS400", "10.0.40.1", "QPGMR", "ALDEBARAN");
$PROGRAM = new java("com.ibm.as400.access.ProgramCall", $AS400);
// COMANDO PER LIBRERIE
$COMMAND = new java("com.ibm.as400.access.CommandCall", $AS400);
$COMMAND->run("CHGLIBL LIBL(QTEMP DABAN VEGABASE VEGAFIL VEGAPOBJ VEGA_OBJ VEGAPSRC VEGASRV AASLIB3000 AASSTP3000 AASUTL3000 QGPL)");
// Istanzio i parametri
//$ARRAY = new java("java.lang.reflect.Array");
//$PARAMETER = new java("com.ibm.as400.access.ProgramParameter");
//$myint=new java("java.lang.Integer", 4);
//$PARAMETERS = $ARRAY->newInstance($PARAMETER, $myint);
// Istanzio oggetto chiamata programma
$PROGRAMCALL = new java("com.ibm.as400.access.ProgramCall");
// PRIMO PARAMETRO NRORIC
$mydouble = java("java.lang.Double")->parseDouble(1);
$PARAMETERS[0] = new java("com.ibm.as400.access.ProgramParameter", $AS400_PACKED_1_0->toBytes($mydouble));
// SECONDO PARAMETRO ENTE
$PARAMETERS[1] = new java("com.ibm.as400.access.ProgramParameter", $AS400_TEXT_4->toBytes("0002"));
// TERZO PARAMETRO CLASSE
$PARAMETERS[2] = new java("com.ibm.as400.access.ProgramParameter", $AS400_TEXT_4->toBytes("0002"));
// QUATO PARAMETRO DATA
$mydouble = java("java.lang.Double")->parseDouble(20170824);
$PARAMETERS[3] = new java("com.ibm.as400.access.ProgramParameter", $AS400_PACKED_8_0->toBytes($mydouble));
// QUINTO PARAMETRO TRACCIATO
$PARAMETERS[4] = new java("com.ibm.as400.access.ProgramParameter", 221);
// SESTO PARAMETRO NREL
$mydouble = java("java.lang.Double")->parseDouble(0);
$PARAMETERS[5] = new java("com.ibm.as400.access.ProgramParameter", $AS400_PACKED_9_0->toBytes($mydouble));
// SETTIMO PARAMETRO FLAG
$PARAMETERS[6] = new java("com.ibm.as400.access.ProgramParameter", 1);
$PARAMETERS[6]->setInputData($AS400_TEXT_1->toBytes("0"));
//$myint=new java("java.lang.Integer", 1);
//$PARAMETERS[6]->setParameterType($myint);
// Richiamo del programma -> come impostare *LIBL
$PROGRAM->setProgram("/QSYS.LIB/VEGA_OBJ.LIB/RTLENT.PGM");
$pages=998;
for($i=1; $i<=$pages; $i++) {
	//$mydouble = new java("java.lang.Double", $i);
	//$mydouble = java("java.lang.Double")->parseDouble($i);
	$PROGRAM->setParameterList($PARAMETERS);
	if (java_isfalse($PROGRAM->run()))  {
		echo "erore!";
	}
}
$AS400_TEXT_221 = new java("com.ibm.as400.access.AS400Text", 221);
echo "<br>". (String) $AS400_TEXT_221->toObject($PARAMETERS[4]->getOutputData());
$AS400->disconnectAllServices();
getMicroTimeStep("<br>FINE");
die();
// Primo Esempio Funzionante
getMicroTimeStep("INIZIO");
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once("Java.inc");
require_once("instance_field_standar.php");
//$System = java("java.lang.System");
//echo $System->getProperties();
//
$AS400 = new java("com.ibm.as400.access.AS400", "10.0.40.1", "QPGMR", "ALDEBARAN");
$PROGRAM = new java("com.ibm.as400.access.ProgramCall", $AS400);
// Istanzio i parametri
//$ARRAY = new java("java.lang.reflect.Array");
//$PARAMETER = new java("com.ibm.as400.access.ProgramParameter");
//$myint=new java("java.lang.Integer", 4);
//$PARAMETERS = $ARRAY->newInstance($PARAMETER, $myint);
// Istanzio oggetto chiamata programma
$PROGRAMCALL = new java("com.ibm.as400.access.ProgramCall");

// PRIMO PARAMETRO NOME
$AS400_TEXT = new java("com.ibm.as400.access.AS400Text", 20);
//$mystr=new java("java.lang.String","LUCA");
$PARAMETERS[0] = new java("com.ibm.as400.access.ProgramParameter", $AS400_TEXT->toBytes("LUCA"));

// SECONDO PARAMETRO COGHOME
$AS400_TEXT = new java("com.ibm.as400.access.AS400Text", 20);
$PARAMETERS[1] = new java("com.ibm.as400.access.ProgramParameter", $AS400_TEXT->toBytes("ZOVI"));

// TERZO PARAMETRO ETA
//$AS400_DECIMAL = new java("com.ibm.as400.access.AS400ZonedDecimal", 3, 0);
//$mydouble = new java("java.lang.Double", 45.0);
$PARAMETERS[2] = new java("com.ibm.as400.access.ProgramParameter");
// QUARTO PARAMETRO OUTPUT
$PARAMETERS[3] = new java("com.ibm.as400.access.ProgramParameter", 100);
//echo $System->getTime();


// Richiamo del programma -> come impostare *LIBL
$PROGRAM->setProgram("/QSYS.LIB/PHPLIB.LIB/TESTJAVA.PGM");
$pages=998;
// OUTPUT BUFFER
$AS400_TEXT = new java("com.ibm.as400.access.AS400Text", 100);
for($i=1; $i<=$pages; $i++) {
	//$mydouble = new java("java.lang.Double", $i);
	$mydouble = java("java.lang.Double")->parseDouble($i);
	//$PARAMETERS[2] = new java("com.ibm.as400.access.ProgramParameter", $AS400_DECIMAL->toBytes(4.0));
	$PARAMETERS[2]->setInputData($AS400_DECIMAL_3_0->toBytes($mydouble));
	$PROGRAM->setParameterList($PARAMETERS);
	$PROGRAM->run();
	
	// Echo del parametro di ritorno
	
	//echo "<br>". (String) $AS400_TEXT->toObject($PARAMETERS[3]->getOutputData());
	//echo "<br>". (String) $PARAMETERS[3]->getOutputData();
}
echo "<br>". (String) $AS400_TEXT->toObject($PARAMETERS[2]->getOutputData());
$AS400->disconnectAllServices();
getMicroTimeStep("<br>FINE");
?>

