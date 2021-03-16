<?= $menuContext->dispose(); ?><?php require $base_path."/includes/messagesList.php"; ?></td></tr>
<? if ($show_footer){ ?><tr><td  class="body-area" valign="bottom"><center><? getMicroTimeStep("Tempo Totale"); ?><img src="<?=  $temaDir ?>images/logo_siri.png" vspace="10"></center></td></tr><? } ?>
</table></td>
<?	if(isset($settings['widget']) && $settings['widget'] && isset($_SESSION['WIDGET_ENABLE']) && $_SESSION['WIDGET_ENABLE']) {
		$lock = "un";
		$open = "";
		if(isset($_SESSION['RIGHT_MENU_STATUS']) && $_SESSION['RIGHT_MENU_STATUS']) {
			$lock = "";
			$open = "openRightMenu";
		}
?>
			<td>
				<div class="contBodyWidget <?=  $open?>">
					<script>document.write('<div class="contWidget" style="height: '+(jQuery(window).height())+'px;">');</script>
						<div class='openWidget' onmouseover="openCloseRightMenu(1)" onmouseout="openCloseRightMenu(<?=(!$lock ? 1 : 0)?>)">
							<div class="menuWidget">
								<?php
									$from_menu = true;
									echo "<i id='right-menu-lock' class='fa fa-{$lock}lock' aria-hidden='true' lock=".(!$lock ? "true" : "false")." onClick='lockRightMenu(this)'></i>";
									include $moduli_path."/announce/widget_model.php";
									include $moduli_path."/announce/widget_view.php";
								?>
							</div>
						</div>
					</div>
				</div>
			</td>
<?	}
?>
</tr></table></form><script>cacheOff();</script>
<?
// Funzionalità di google maps
if (isset($gm)){
	echo $gm->InitJs();
	echo $gm->UnloadMap();
}


if(($settings['debug']  || isset($_SESSION["DEBUG"]))&& is_array($debugContext) && sizeof($debugContext)>0) {
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
<?}
if (isset($settings['enable_ws_client']) && $settings['enable_ws_client']==True) {
$connection_string=array("type"=>"INIT", "token"=>"TOKEN01", "request"=>"CLIENT", "id"=>"dfsljflksj");
$connection_string=session_id();
$connection_string=base64_encode($connection_string);
$connection_string2=array("sender"=>"CONSOLE", 'action'=>"MSG", "to"=>"142", "msg"=>"Prova invio messaggio tra client prova 2!");
$connection_string2=base64_encode(json_encode($connection_string2));
//$_SESSION['DEVELOPER_DOC_INCLUDED'] = get_included_files();
$script="
</body>
<script>
var conn = new WebSocket('ws://127.0.0.1:8006');
conn.onopen = function(e) {
    console.log(\"Connection established!\");
	conn.send('$connection_string');
};</script>";
//echo $script;
}
/*
conn.onmessage = function(e) {
    console.log(e.data);
    //alert(e.data);
};
conn.onclose = function(e) {
    console.log("Closing connection to server ..");
};
conn.onerror = function(){
       console.log("errore nella connessione");
    }*/
?>
<html>