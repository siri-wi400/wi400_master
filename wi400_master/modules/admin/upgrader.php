<?php
	
	if ($actionContext->getForm() == "DEFAULT"){
		$upgrader = new wi400Detail("UPGRADER");
		
		$upgradeFile = new wi400InputFile("updateFile");
		$upgradeFile->setLabel("File di aggiornamento");
		$upgradeFile->setInfo("Caricare un file ZIP contenente gli aggiornamenti.");
		$upgradeFile->addValidation("required");
		$upgrader->addField($upgradeFile);
		
		$backupFile = new wi400InputCheckbox("BACKUP_FILE");
		$backupFile->setLabel("Backup versione corrente");
		$backupFile->setChecked(true);
		$upgrader->addField($backupFile);
		
		$myButton = new wi400InputButton('UPDATE_BUTTON');
		$myButton->setLabel("Aggiorna");
		$myButton->setConfirmMessage("La versione corrente di WI400 verrà aggiornata. Continuare?");
		$myButton->setAction("UPGRADER");
		$myButton->setForm("UPLOAD");
		
		$upgrader->addButton($myButton);
		$upgrader->dispose();
	}else if ($actionContext->getForm() == "UPLOAD"){
		$doBackup = "";
		if (!isset($_REQUEST["BACKUP_FILE"])){
			$doBackup = "?BACKUP_FILE=NONE";
		}
?>
	<script>
		openWindow("/<?= $settings['installerPath'] ?>/index.php<?= $doBackup ?>", "installer", 600, 300);
	</script>		
<?
	}
?>