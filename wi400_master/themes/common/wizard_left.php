			<div class="left-menu-row">
	        	<table cellpadding="0" cellspacing="0">
	        		<tr>
	            		<td><img id="left_menu_tag_0" src="<?=  $temaDir."images/tag_yellow.gif" ?>" hspace="10"></td>
	                	<td id="left_menu_row_0" class="left-menu-label-selected">Wizard Steps</td>
	                </tr>
				</table>
			</div>
			<div id="left_menu_content_0" class="left-menu-row-content">
				<table>
<?
	$wizardSteps = $wi400Wizard->getSteps();

	$stepCounter = 0;
	foreach ($wizardSteps as $wizardStep){
		if ($wi400Wizard->getCounter() == $stepCounter){
			$wizardStyle = "left-menu-label-selected";
		}else{
			$wizardStyle = "left-menu-content-text";
		}
?>                                            
        <tr>
			<td class="<?=$wizardStyle ?>"><?= $stepCounter + 1 ?>.&nbsp;<?echo $wizardStep["title"] ?></td>
		</tr>
<?
		$stepCounter++;
	}
?>
<tr><td><?$myButton = new wi400InputButton("END_BUTTON");
		$myButton->setAction("WIZARD");
		$myButton->setForm("END");
		$myButton->setLabel("Abbandona");
		$myButton->setValidation(false);
		$myButton->setStyleClass("wizard-exit-button");
		$myButton->setConfirmMessage("Abbandonare il wizard? Tutti i dati inseriti verranno cancellati!");
		$myButton->dispose();?></td></tr>
				</table>
			</div>