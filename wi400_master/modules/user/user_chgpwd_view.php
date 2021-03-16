<?php
	require_once "user_commons.php";
	
	$classDivCont = '';
	if($actionContext->getForm()=="LOGIN") {
		$classDivCont = 'contLogin';
	}
	
	echo "<div class='$classDivCont'>";
	if(in_array($actionContext->getForm(),array("DEFAULT","LOGIN"))) {
		$modifyAction = new wi400Detail('chgpwd');
		$modifyAction->setTitle(_t('PW_TITLE'));
		$modifyAction->isEditable(true);
		if($actionContext->getForm()=="LOGIN")
			$modifyAction->setWidth('30%');
		
		$auth_method = retriveAuthMethod($user);
		// not necessary but is here to see if we correctly added the libraries
		//if ($settings['platform']=='AS400') {
		if ($auth_method=='AS') {
			executeCommand("rtvusrprf",array("USRPRF"=> $user),array("PWDCHGDAT"=>"PWDCHGDAT"));
			// Su questo campo troviamo la data di ultima modifica della password in formato YYMMDD
			$data = substr($PWDCHGDAT,4,2)."/".substr($PWDCHGDAT,2,2)."/".substr($PWDCHGDAT,0,2);
		} else {
			// Cerco sui parametri di advanced parameters
			require_once $routine_path."/classi/wi400AdvancedUserSecurity.cls.php";
			$advanced_security = new wi400AdvancedUserSecurity($user);
			$sec = $advanced_security->getUserViewSecurityParam();
			$ds = $sec['LSTCHP'];
			$data = substr($ds,0,2)."/".substr($ds,3,2)."/".substr($ds,8,2);
		}
		
		if($actionContext->getForm()=="LOGIN") {
			// Utente
			$myField = new wi400InputText('USER');
			$myField->setLabel(_t('USER_CODE'));
			$myField->setValue($user);
			$myField->setReadonly(true);
			$myField->setSize(10);
			$modifyAction->addField($myField);
		}
		
		// Data ultima modifica della password
		$myField = new wi400InputText('LAST_MOD');
		$myField->setLabel(_t('DATE_LAST_MODIFY'));
		if (isset($settings['advanced_security']) && $settings['advanced_security']==True) {
			require_once $routine_path."/classi/wi400AdvancedUserSecurity.cls.php";
			$advanced_security = new wi400AdvancedUserSecurity($user);
			$sec = $advanced_security->getSecurityParam();
			$myField->setDescription("Complessità password:".$advanced_security->getDescComplex($sec['COMPLEX']));
		}
		$myField->setValue($data);
		$myField->setReadonly(true);
		$myField->setSize(8);
		$modifyAction->addField($myField);
		
		// Password corrente
		if($actionContext->getForm() != "LOGIN") {
			$myField = new wi400InputText('CURPWD');
			$myField->setLabel(_t('PW_CUR'));
			$myField->setInfo(_t('PW_CUR_INFO'));
			$myField->setType('PASSWORD');
			$myField->setSize(10);
			$myField->setMaxLength(10);
			$myField->addValidation("required");
			$modifyAction->addField($myField);
		}
		
		// Nuova password
		$myField = new wi400InputText('NEWPWD');
		$myField->setLabel(_t('PW_NEW'));
		$myField->setInfo(_t('PW_NEW_INFO'));
		$myField->setType('PASSWORD');
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$customTool = new wi400CustomTool();
		$customTool->setScript("showMyPassword('NEWPWD');");
		$customTool->setToolTip("Clicca per visulizzare/nascondere la passoword");
		$customTool->setIco("themes/common/images/eye.png");
		$myField->addCustomTool($customTool);
		//if (isset($settings['advanced_security']) && $settings['advanced_security']==True) {
		//	$myField->setOnKeyDown("checkPasswordStrength('".$sec['COMPLEX']."');");
		//}
		$myField->addValidation("required");
		$modifyAction->addField($myField);
		
		// Verifica della nuova password
		$myField = new wi400InputText('VERPWD');
		$myField->setLabel(_t('PW_VFY'));
		$myField->setInfo(_t('PW_VFY_INFO'));
		$myField->setType('PASSWORD');
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$customTool = new wi400CustomTool();
		$customTool->setScript("showMyPassword('VERPWD');");
		$customTool->setToolTip("Clicca per visulizzare/nascondere la passoword");
		$customTool->setIco("themes/common/images/eye.png");
		$myField->addCustomTool($customTool);
		$myField->addValidation("required");
		$modifyAction->addField($myField);
		
		$myButton = new wi400InputButton('MODIFY_BUTTON');
		$myButton->setLabel(_t('UPDATE'));
		$myButton->setButtonClass("ccq-button-active");
		$myButton->setButtonStyle(wi400GetCssButton('200px', "#6899bb", "#2c658b", "white", "black"));
		$myButton->setAction("CHGPWD");
		$myButton->setForm("MODIFICA");
		$myButton->setValidation(true);
		$modifyAction->addButton($myButton);
		
		if($actionContext->getForm()=="LOGIN") {
			/*$myButton = new wi400InputButton('BACK_BUTTON');
			 $myButton->setLabel("Indietro");
			 $myButton->setAction("LOGIN");
			 $modifyAction->addButton($myButton);*/
		}
		
		$modifyAction->dispose();		
	}

?>
		<br>Per la tua sicurezza, la password dovrà:
		<br>1. Avere una lunghezza di minimo 6 caratteri ed un massimo di 10
		<br>2. Contenere almeno un numero
		<br>3. Contenere almeno uno di questi caratteri speciali: $,£,§,_
		<br>4. Non dovrà contenere caratteri uguali consecutivi
		<br>5. Non potrà essere uguale alle ultime 4 password
	</div>
<?php if($actionContext->getForm()=="LOGIN") {?>
	<div style="position: absolute; left: 0px; top: 0px; width: 100%; height: auto;">
		 <?php require $base_path."/includes/messagesContainer.php"; ?>
	</div>
<?php }
require $base_path."/includes/messagesList.php"; ?>	
<style>
.contLogin {
	position: absolute;
	left: 50%;
	top: 50%;
	width: 500px;
	height: 315px;
	margin-top: -157.5px;
	margin-left: -250px;
	/*background-color: red;*/
}
#frmCheckPassword {border-top:#F0F0F0 2px solid;background:#FAF8F8;padding:10px;}
.demoInputBox{padding:7px; border:#F0F0F0 1px solid; border-radius:4px;}
#password-strength-status {padding: 5px 10px;color: #FFFFFF; border-radius:4px;margin-top:5px;}
.medium-password{background-color: #E4DB11;border:#BBB418 1px solid;}
.weak-password{background-color: #FF6600;border:#AA4502 1px solid;}
.strong-password{background-color: #12CC1A;border:#0FA015 1px solid;}
.complex-password{background-color: #00FF00;border:#0FA015 1px solid;}
</style>
<script type="text/javascript">
<?php 
if (isset($settings['advanced_security']) && $settings['advanced_security']==True) {
	echo "checkPasswordStrength('".$sec['COMPLEX']."');";
}?>
function checkPasswordStrength(complex) {

	jQuery( "#NEWPWD" ).keyup(function() {
		var number = /([0-9])/;
		var alphabets = /([a-zA-Z])/;
		var mai = /([A-Z])/;
		var howcomplex = "";
		var peso = 0;
		if (complex =="*WEAK") peso = 1;
		if (complex =="*NONE") peso = 0;
		if (complex =="*MEDIUM") peso = 2;
		if (complex =="*STRONG") peso = 3;
		if (complex =="*COMPLEX") peso = 4;
		 
		var special_characters = /([~,!,@,#,$,%,^,&,*,-,_,+,=,?,>,<])/;
		if(jQuery('#NEWPWD').val().length<6) {
			jQuery('#NEWPWD_DESCRIPTION').removeClass();
			jQuery('#NEWPWD_DESCRIPTION').addClass('weak-password');
			jQuery('#NEWPWD_DESCRIPTION').html("Troppo debole (Digitare almeno 6 caratteri.)");
			howcomplex = 0;
		} else {
			howcomplex =1;  
			jQuery('#NEWPWD_DESCRIPTION').addClass('weak-password');
			jQuery('#NEWPWD_DESCRIPTION').html("Debole (Almeno 6 caratteri.)");
		    if(jQuery('#NEWPWD').val().match(number) && jQuery('#NEWPWD').val().match(alphabets)) {            
		        jQuery('#NEWPWD_DESCRIPTION').removeClass();
	        	jQuery('#NEWPWD_DESCRIPTION').addClass('medium-password');
	        	jQuery('#NEWPWD_DESCRIPTION').html("Medium (include caratteri e numeri.)");
	    		howcomplex = 2;
	        } 	
		    if(jQuery('#NEWPWD').val().match(number) && jQuery('#NEWPWD').val().match(alphabets) && jQuery('#NEWPWD').val().match(special_characters)) {            
		    	jQuery('#NEWPWD_DESCRIPTION').removeClass();
		    	jQuery('#NEWPWD_DESCRIPTION').addClass('strong-password');
		    	jQuery('#NEWPWD_DESCRIPTION').html("Strong (caratteri, numeri e caratteri speciali");
				howcomplex = 3;
	        } 
		    if(jQuery('#NEWPWD').val().match(number) && jQuery('#NEWPWD').val().match(alphabets) && jQuery('#NEWPWD').val().match(mai) && jQuery('#NEWPWD').val().match(special_characters)) {            
		    	jQuery('#NEWPWD_DESCRIPTION').removeClass();
		    	jQuery('#NEWPWD_DESCRIPTION').addClass('complex-password');
		    	jQuery('#NEWPWD_DESCRIPTION').html("Complessa (caratteri, numeri, caratteri speciali, maiuscole/minuscole");
				howcomplex = 4;
	        } 
		}
		// Abilito o disabilito il tasto aggiorna in base a cosa ho scritto
		if (howcomplex>=peso) {
			jQuery('#MODIFY_BUTTON').prop("disabled",false);	
		} else {
			jQuery('#MODIFY_BUTTON').prop("disabled",true);		
		}
		var label = jQuery('#chgpwd_NEWPWD_LABEL').html();
		var newlabel = "";
		if (label.indexOf('(')>0) {
			newlabel = label.substring(0, label.indexOf('(')-1);
		} else {
			newlabel = label;
		}	
		var style="";
		if (jQuery('#NEWPWD').val().length>=10) {
			style=" style='color:red;'";
		}	
		jQuery('#chgpwd_NEWPWD_LABEL').html(newlabel+ " <span"+style+">("+jQuery('#NEWPWD').val().length+")</span>");
	});
}		
</script>