<?php
$out = "";
$out1 = "";
$out2 = "";

?>

<html>
<body>

<?php
$corridoi[10][4]="5D-21-067-AA";
$corridoi[11][4]="5D-21-067-AA";
$corridoi[12][4]="5D-21-067-AA";
$corridoi[13][4]="5D-21-067-AA";
$corridoi[14][4]="5D-21-067-AA";
$corridoi[15][4]="5D-21-067-AA";
$corridoi[16][4]="5D-21-067-AA";
$corridoi[17][4]="5D-21-067-AA";
$corridoi[18][4]="5D-20-067-AA";
$corridoi[19][4]="5D-21-067-AA";
$corridoi[20][4]="5D-21-067-AA";
$corridoi[21][4]="5D-21-067-AA";
$corridoi[22][4]="5D-21-067-AA";
$corridoi[23][4]="5D-21-067-AA";
$corridoi[24][4]="5D-24-067-AA";
$posti = array("5D-20-067-AA");
for ($i=0; $i < 70; $i++) {
  for ($j=0; $j < 100; $j++) {
    $roomblock = 0;
    if (isset($corridoi[$i][$j])) {
      // Controllo se inserito nell'array dei posti segnalati
      if (in_array($corridoi[$i][$j], $posti)){
	      $out .= "<img src=greendot2.png onClick='location.href=\"scaffale.php\"' style='cursor:hand'>";
	      $map[$i][$j] = 1;
	      $out1 .= "\$map[" . $i . "][" . $j . "] = 1;\n";
	      $out2 .= "\$map[" . $i . "][" . $j . "] = 1;\n";
      } else {
	      $out .= "<img src=orangedot2.gif>";
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
?>

<table>
<tr>
<td align="center">
<?php echo $out;?>
</td>
</tr>
</table>
</body>
</html>

