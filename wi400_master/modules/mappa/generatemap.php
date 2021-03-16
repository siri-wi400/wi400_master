<?php 
$conn = db2_connect("DBSIRI", "QSECOFR", "GALLA07", array('i5_naming'=>DB2_I5_NAMING_ON, 'i5_libl'=>"QGPL VAGAFIL"));
echo db2_conn_errormsg();
$sql="SELECT MadZOD, madcor, madbay, madlat, madlar FROM VEGAFIL/fmadstoc WHERE MADCDE ='5001' AND MADSTA = '1' AND MADPPA IN ('L1', 'P1', '01')
AND MADTPP = 'DI'  
GROUP BY madzod, madcor, madbay, madlat, madlar ORDER BY madzod,madcor,madbay, madlat, madlar";
$stmt = db2_exec($conn , $sql);
echo "SQL:".$sql;

$i=0;
$k=5;
$c=0;
$dx=5;
$sx=5;
$saveZona="";
$saveCorridoio="";
$corridoi = array();
$firstAR=False;
$firstPA=False;
$first1A=False;
$halfe=False;
while ($result = db2_fetch_assoc($stmt)) {
$changeCorridoio=False;

// Valorizzo variabili
$zona=$result['MADZOD'];
$corridoio=$result['MADCOR'];
$lato = $result['MADLAT'];
$bay = $result['MADBAY'];
$posto = $result['MADCDP'];
$larghezza = $result['MADLAR'];

if ($zona != $saveZona) {
  $k=0;
  $saveCorridoio="";
}
if ($corridoio != $saveCorridoio) {
  //echo "<br>Corridoio:".$corridoio." salvaCorridoio:".$saveCorridoio;
  $changeCorridoio=True;
}
$saveZona = $zona;
$saveCorridoio = $corridoio;
//echo "<br>Result:".$result['MADZOD']." ".$result['MADCOR'];
if ($result['MADZOD']== 'AR' && $firstAR==False) {
            $corridoi[0][50]="AR";
            $corridoi[0][51]="AR";
            $corridoi[0][52]="AR";
            $corridoi[0][53]="AR";
            $corridoi[0][54]="AR";
            $corridoi[0][55]="AR";
            $corridoi[0][56]="AR";
            $corridoi[0][57]="AR";
            $corridoi[0][58]="AR";
            $corridoi[0][59]="AR";
            $firstAR=True;
} elseif($result['MADZOD']== 'PA' && $firstPA==False) {
            $corridoi[99][50]="PA";
            $corridoi[99][51]="PA";
            $corridoi[99][52]="PA";
            $corridoi[99][53]="PA";
            $corridoi[99][54]="PA";
            $corridoi[99][55]="PA";
            $corridoi[99][56]="PA";
            $corridoi[99][57]="PA";
            $corridoi[99][58]="PA";
            $corridoi[99][59]="PA";
            $firstPA=True;
                        
} elseif($zona=='01') {

			if ($first1A==False) {
                $first1A=True;
                $c=0;
            }   
            if ($changeCorridoio==True) {
                $dx=5;
                $sx=5;
                $c=$c+5;
            }
            if ($larghezza<70) {
                if ($half==False){  
                $half = True;
                } else {
                $half = False;
                }
            }
            if ($half==False){ 
            if ($lato=='S'){
				$corridoi[$c][$dx]=$zona."-".$corridoio."-".$bay."-".$lato."-".$posto;
				$dx++;
            } else {
				$corridoi[$c+4][$sx]=$zona."-".$corridoio."-".$bay."-".$lato."-".$posto;
				$sx++;
            }
            }
}
$k++;
}
$corridoi[5][21]="5D-20-067-AA";
$posti = array("5D-20-067-AA");
for ($j=0; $j < 80; $j++) {
  for ($i=0; $i < 150; $i++) {
    $roomblock = 0;
    if (isset($corridoi[$i][$j])) {
      $posto = $corridoi[$i][$j];
      // Controllo se inserito nell'array dei posti segnalati
      if (in_array($corridoi[$i][$j], $posti)){
	      $out .= "<img src=greendot2.png onClick=\"window.open('scaffale.php', 'windowname1', 'width=400, height=600'); return false;\" style='cursor:hand' title='prova'>";
	      $map[$i][$j] = 1;
	      $out1 .= "\$map[" . $i . "][" . $j . "] = 1;\n";
	      $out2 .= "\$map[" . $i . "][" . $j . "] = 1;\n";
      } else {
	      $out .= "<img src=orangedot2.gif title='$posto'>";
	      $map[$i][$j] = 1;
	      $out1 .= "\$map[" . $i . "][" . $j . "] = 1;\n";
	      $out2 .= "\$map[" . $i . "][" . $j . "] = 1;\n";
    }
    } else {
      $out .= "<img src=greydot2.gif>";
      $map[$i][$j] = 0;
      $out1 .= "\$map[" . $i . "][" . $j . "] = 0;\n";
    }
  }
  $out .= "<br>";
}
echo $out;
?>