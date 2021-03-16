<?php
	if ($actionContext->getForm() == "DEFAULT"){
		// Stato sottosistema
		?>
		<table border="1">
			<tr>
				<td>
					Ver. Server <?=$versione ?><br>
					Ver. Client <?=$dati['versione'] ?>
				</td>
				<td>
					Sottositema OPENTERM: <?=$stato_subsystem?>
				</td>
				<td>
					<img title="Off Line" width="32" height="32" border="0" src="<?= $image_subsystem ?>">
				</td>
				<td>
					<a href="#" class="buttonstop" onClick="jQuery.blockUI({ message: '<h1><img src=\'./themes/common/images/busy.gif\' /> Prego attendere ...</h1>' });doSubmit('<?=$azione?>', 'STOP_SUBSYSTEM');">Stop</a>
				</td>
				<td>
					<a href="#" class="buttonstart" onClick="jQuery.blockUI({ message: '<h1><img src=\'./themes/common/images/busy.gif\' /> Prego attendere ...</h1>' });doSubmit('<?=$azione?>', 'START_SUBSYSTEM');">Start</a>
				</td>
				<td>
					<a href="#" class="buttonrefresh" onClick="jQuery.blockUI({ message: '<h1><img src=\'./themes/common/images/busy.gif\' /> Prego attendere ...</h1>' });doSubmit('<?=$azione?>', 'DEFAULT');">Refresh</a>
				</td>
			</tr>
		</table>
		<?
		// Sessioni Attive
		if ($stato_subsystem=="*ACTIVE") {
			echo '<table border ="1">';
			$query = "SELECT * FROM ZOPNSESS WHERE SESSST='1'";
			$result =  $db->query($query);
			require_once $routine_path."/os400/wi400Os400Job.cls.php";
	
			$rowhtml="
				<tr>
					<td>Device</td>
					<td>Inizio Collegamento</td>
					<td>Ultima Attività</td>
					<td>Stato Lavoro</td>
					<td></td>
					<td>ID</td>
					<td>Preview</td>
				</tr>";
			echo $rowhtml;
			while ($row = $db->fetch_array($result)) {
				$list = new wi400Os400Job($row['SESDEV'],$row['SESUSR'],$row['SESNBR']);
				$list->getList();
				$ret = $list->getEntry();
				$status="*DISCONNECT";
				if ($ret) {
					$status = $ret['JOBSTATUS'];	
					$rowhtml="
					<tr>
						<td>".$row['SESDEV']."</td>
						<td>".$row['SESSTI']."</td>
						<td>".$row['SESSTU']."</td>
						<td>".$status."</td>
						<td><a href='#' class='buttonstop' onClick='stopTelnet(\"".$row['SESSID']."\")'>KILL</a></td>
						<td>".$row['SESSID']."</td>
						<td><a href='#' class='buttonstart' onClick='previewTelnet(\"".$row['SESSID']."\", \"".$row['SESSTU']."\")'>PREVIEW</a></td>
					</tr>";
					echo $rowhtml;
				}
			}
			echo '</table>';
		}
	}	
	if ($actionContext->getForm() == "PREVIEW_TELNET") {
		echo getTema5250();
		//echo '<link rel="stylesheet" type="text/css" href="modules/emulatore5250/telnet_5250_griglia.css">';
		
		echo "<script>var disableAjax = true;</script>";
		require_once "telnet_5250_script.php";
		//echo '<script type="text/javascript" src="modules/emulatore5250/jquery.ba-hashchange.js"></script>';
		
		disableInputFocusStyle();
		
		$Sessione5250 = new wi400AS400Session(session_id());
		$obj = $Sessione5250->parseDataStream($dati);
		
		//showArray($obj);
		
		$resolution = $Sessione5250->getResolutionRow()."x".$Sessione5250->getResolutionCol();
		$display = new wi400AS400Display(session_id());
		$display->setDisposeContainer(true);
		$display->setDisposeFunctionButton(true);
		$display->setResolution($resolution);
		//$display->setStreamObj($obj, true);
		$display->setStreamObj($obj);
		$display->executeCommand();
		$html = $display->display();
		
		echo $html;
	}
?>
<script>
	function stopTelnet(handle) {
	    	jQuery.blockUI({ message: '<h1><img src="./themes/common/images/busy.gif" /> Prego attendere ...</h1>' });
			var formObj = document.getElementById(APP_FORM);
			formObj.action = _APP_BASE + APP_SCRIPT + "?t=<?= $azione ?>&f=STOP_TELNET&HANDLE="+handle;
			formObj.submit();
	}
	function previewTelnet(handle, timestamp) {
		//jQuery.blockUI({ message: '<h1><img src="./themes/common/images/busy.gif" /> Prego attendere ...</h1>' });
		//var formObj = document.getElementById(APP_FORM);
		//formObj.action = _APP_BASE + APP_SCRIPT + "?t=<?= $azione ?>&f=PREVIEW_TELNET&HANDLE="+handle;
		//formObj.submit();
		// Aprire la finestra passando l'azione
		openWindow(_APP_BASE+ APP_SCRIPT + "?t=<?=$azione?>&f=PREVIEW_TELNET&DECORATION=lookup&HANDLE="+handle+"&TIMESTAMP="+timestamp, "telnet_preview", 1100, 800);
	}
</script>
<style type="text/css">
	.buttonstop {
		-moz-box-shadow:inset 0px 1px 0px 0px #f5978e;
		-webkit-box-shadow:inset 0px 1px 0px 0px #f5978e;
		box-shadow:inset 0px 1px 0px 0px #f5978e;
		background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #f24537), color-stop(1, #c62d1f) );
		background:-moz-linear-gradient( center top, #f24537 5%, #c62d1f 100% );
		filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#f24537', endColorstr='#c62d1f');
		background-color:#f24537;
		-webkit-border-top-left-radius:0px;
		-moz-border-radius-topleft:0px;
		border-top-left-radius:0px;
		-webkit-border-top-right-radius:0px;
		-moz-border-radius-topright:0px;
		border-top-right-radius:0px;
		-webkit-border-bottom-right-radius:0px;
		-moz-border-radius-bottomright:0px;
		border-bottom-right-radius:0px;
		-webkit-border-bottom-left-radius:0px;
		-moz-border-radius-bottomleft:0px;
		border-bottom-left-radius:0px;
		text-indent:0;
		border:1px solid #d02718;
		display:inline-block;
		color:#ffffff;
		font-family:Arial;
		font-size:15px;
		font-weight:bold;
		font-style:normal;
		height:40px;
		line-height:40px;
		width:100px;
		text-decoration:none;
		text-align:center;
		text-shadow:1px 1px 0px #810e05;
	}
	.buttonstop:hover {
		background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #c62d1f), color-stop(1, #f24537) );
		background:-moz-linear-gradient( center top, #c62d1f 5%, #f24537 100% );
		filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#c62d1f', endColorstr='#f24537');
		background-color:#c62d1f;
	}
	.buttonstop:active {
		position:relative;
		top:1px;
	}
	.buttonstart {
		-moz-box-shadow:inset 0px 1px 0px 0px #c1ed9c;
		-webkit-box-shadow:inset 0px 1px 0px 0px #c1ed9c;
		box-shadow:inset 0px 1px 0px 0px #c1ed9c;
		background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #9dce2c), color-stop(1, #8cb82b) );
		background:-moz-linear-gradient( center top, #9dce2c 5%, #8cb82b 100% );
		filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#9dce2c', endColorstr='#8cb82b');
		background-color:#9dce2c;
		-webkit-border-top-left-radius:0px;
		-moz-border-radius-topleft:0px;
		border-top-left-radius:0px;
		-webkit-border-top-right-radius:0px;
		-moz-border-radius-topright:0px;
		border-top-right-radius:0px;
		-webkit-border-bottom-right-radius:0px;
		-moz-border-radius-bottomright:0px;
		border-bottom-right-radius:0px;
		-webkit-border-bottom-left-radius:0px;
		-moz-border-radius-bottomleft:0px;
		border-bottom-left-radius:0px;
		text-indent:0;
		border:1px solid #83c41a;
		display:inline-block;
		color:#ffffff;
		font-family:Arial;
		font-size:15px;
		font-weight:bold;
		font-style:normal;
		height:40px;
		line-height:40px;
		width:100px;
		text-decoration:none;
		text-align:center;
		text-shadow:1px 1px 0px #689324;
	}
	.buttonstart:hover {
		background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #8cb82b), color-stop(1, #9dce2c) );
		background:-moz-linear-gradient( center top, #8cb82b 5%, #9dce2c 100% );
		filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#8cb82b', endColorstr='#9dce2c');
		background-color:#8cb82b;
	}.buttonstart:active {
		position:relative;
		top:1px;
	}

	.buttonrefresh {
		-moz-box-shadow:inset 0px 1px 0px 0px #0B3CEE;
		-webkit-box-shadow:inset 0px 1px 0px 0px #0B3CEE;
		box-shadow:inset 0px 1px 0px 0px #0B3CEE;
		background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #0B3CEE), color-stop(1, #0B3CEE) );
		background:-moz-linear-gradient( center top, #9dce2c 5%, #0B3CEE 100% );
		filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#0B3CEE', endColorstr='#0B3CEE');
		background-color:#0B3CEE;
		-webkit-border-top-left-radius:0px;
		-moz-border-radius-topleft:0px;
		border-top-left-radius:0px;
		-webkit-border-top-right-radius:0px;
		-moz-border-radius-topright:0px;
		border-top-right-radius:0px;
		-webkit-border-bottom-right-radius:0px;
		-moz-border-radius-bottomright:0px;
		border-bottom-right-radius:0px;
		-webkit-border-bottom-left-radius:0px;
		-moz-border-radius-bottomleft:0px;
		border-bottom-left-radius:0px;
		text-indent:0;
		border:1px solid #83c41a;
		display:inline-block;
		color:#ffffff;
		font-family:Arial;
		font-size:15px;
		font-weight:bold;
		font-style:normal;
		height:40px;
		line-height:40px;
		width:100px;
		text-decoration:none;
		text-align:center;
		text-shadow:1px 1px 0px #689324;
	}
	.buttonrefres:hover {
		background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #8cb82b), color-stop(1, #9dce2c) );
		background:-moz-linear-gradient( center top, #8cb82b 5%, #9dce2c 100% );
		filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#8cb82b', endColorstr='#9dce2c');
		background-color:#8cb82b;
	}.buttonrefres:active {
		position:relative;
		top:1px;
	}
</style>