<?= $menuContext->dispose(); ?><?php require $base_path."/includes/messagesList.php"; ?></td></tr>
<? if (!$show_footer){ ?><tr><td  class="body-area" valign="bottom"><center><? getMicroTimeStep("Tempo Totale"); ?><img src="<?=  $temaDir ?>images/logo_siri.png" vspace="10"></center></td></tr><? } ?>
</table></td></tr></table></form><script>cacheOff();</script>
<?
// Funzionalità di google maps
if (isset($gm)){
	echo $gm->InitJs();
	echo $gm->UnloadMap();
}


if(($settings['debug']  || isset($_SESSION["DEBUG"]))&& sizeof($debugContext)>0) {
?>
<div ID="debugHeader" onClick="openClose('debugConsole')"><b># Debug console</b></div>
<DIV id="debugConsole" style="display:none;">
<div>
<?
	foreach ($debugContext as $debugLine) {
		
		$debugVariable = $debugLine["VARIABLE"];
		$debugTime     = date('d M y - H:i:s', $debugLine["TIME"]);
		echo "<div style='border-bottom:1px solid green;padding-bottom:10px;padding-top:10px'>".$debugTime." > ";
		if (is_string($debugVariable)) {
			echo $debugVariable;
		}else{
			var_dump($debugVariable);
		}
		echo "</div>";
	}
?></div></DIV>
<?}?>
</body>
</html>
