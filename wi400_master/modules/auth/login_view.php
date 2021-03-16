<!--[if lte IE 8]>	
<table width="400"  border="0" cellpadding="10" cellspacing="0" class="work-area">
<?
$user = "";
if (isset($_POST['user'])) {
	$user = filter_input(INPUT_POST, $_POST['user'], FILTER_SANITIZE_STRING);
}
if (isset($settings['as_environment']) && $settings['as_environment']!="") {
echo '<tr><td class="work-area" colspan="2" align="center"><div class="title">'.$settings['as_environment']."</div></td></tr>";
}
?>
	<tr>
		<td width="150" class="loginLeftBox"><img
			 src="<?=  $temaDir ?>images/logo_login.png"></td>
		<td width="350" valign="top" class="loginRightBox">
			<div class="title"><? echo _t("AREA_RISERVATA") ?></div>
			<div class="text"><? echo _t("INSERISCI_DATI_LOGIN")?></div>
			<br />
            <div class="label"><? echo _t("NOME_UTENTE")?>:</div>
            <input id="userField" name="user" onKeyUp="wi400_check_login_digit()" onChange="this.value=this.value.toUpperCase();" type="text" value="<? echo $user ?>" class="inputtext" size="25"><br>
            <div class="label"><? echo _t("PASSWORD")?>:</div>
			 <input id="passField" name="password" onKeyUp="wi400_check_login_digit()" type="password" value="" class="inputtext" size="25">
			 <input type="checkbox" onclick="myFunction()">Show Password
			 <br>
			 
         </td>
         <td width="50" class="cont_button" style="padding: 0px;">
         	<input class="button-login" type="submit" disabled id="nextImage" onClick="doSubmit('CHECK_LOGIN')" style="width: 50px; height: 155px; font-size: 30px; border: 0px; font-weight: bold" value=">"/>
         </td>
	</tr>
</table>
<![endif]-->


<!--[if !IE]> -->
<div class="body-area" style="position: absolute; width: 100%; top: 0px; bottom: 71px; left: 0px; padding: 0px;">
	<div class="work-area" style="position: absolute;width: 450px; height: 180px; left: 50%; margin-left: -225px; top: 50%; margin-top: -90px;">
			<div class="loginLeftBox" style="position: absolute; left: 0px; width: 200px; height: 100%;">
				<img class="loginLeftBoxLogo" src="<?=  $temaDir ?>images/logo_login.png">
			</div>
			<div class="loginRightBox" style="position: absolute; right: 50px; padding: 10px; width: 180px; height: 160px;">
				<?
if (isset($_POST['user'])) {
	$user = filter_input(INPUT_POST, $_POST['user'], FILTER_SANITIZE_STRING);
}
if (isset($settings['as_environment']) && $settings['as_environment']!="") {
	echo '<div class="title" >'.$settings['as_environment']."</div>";
}
?>
				<div class="title"><? echo _t("AREA_RISERVATA") ?></div>
				<div class="text"><? echo _t("INSERISCI_DATI_LOGIN")?></div>
				<br />
            	<div class="label"><? echo _t("NOME_UTENTE")?>:</div>
				<input id="userField" name="user" onKeyUp="wi400_check_login_digit()" onChange="this.value=this.value.toUpperCase();" type="text" value="<? echo $user ?>" class="inputtext" size="23"><br>
				<div class="label"><? echo _t("PASSWORD")?>:</div>
				<div>
				<input id="passField" name="password" onKeyUp="wi400_check_login_digit()" type="password" value="" class="inputtext" size="23">
				<? if (isset($settings['showpassword']) && $settings['showpassword']==True) { ?>
				<img id="NEWPWD_custom_tool_0" title="Clicca per visulizzare/nascondere la passoword" onclick="showMyPassword('passField');" hspace="1" src="themes/common/images/eye.png" style="width:10px;height:10px">
				<? } ?>
				</div>
				<br>
				<!-- <input type="image" disabled src="<?=  $temaDir ?>images/next_disabled.gif" align="right" id="nextImage" onClick="doSubmit('CHECK_LOGIN')"><br>-->
			</div>
			<div class="cont_button" style="position: absolute; width: 50px; height: 100%; right: 0px;">
				<input class="button-login" type="submit" disabled id="nextImage" onClick="loading();doSubmit('CHECK_LOGIN')" style="width: 100%; height: 100%; border: 0px; font-size: 30px; font-weight: bold;" value=">"/>
			</div>
	</div>
	 <div class="spinner spinner--color spinner-none">
      <div class="spinner__item1"></div>
      <div class="spinner__item2"></div>
      <div class="spinner__item3"></div>
      <div class="spinner__item4"></div>
    </div>
	<div style="position: absolute; left: 50%; margin-left: -225px; top: 50%; margin-top: 92.5px; width: 450px; height: auto; background-color: red;">
		 <div id="messageArea" onclick="resizeMessageArea()" class="messageArea_ERROR" style="position: absolute; top: 0px; left: 0px; width: 450px; height: auto; display: none;">
		</div>
	</div>
	<?php require $base_path."/includes/messagesList.php"; ?>
</div>
<!-- <![endif]-->