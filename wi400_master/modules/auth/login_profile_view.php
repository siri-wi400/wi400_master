<!--[if lte IE 8]>
<table width="400"  border="0" cellpadding="3" cellspacing="0" >
		<tr>
			<td width="300">&nbsp;</td>
			<td width="30" class="top-area" style="border-top:1px solid #cccccc;border-left:1px solid #cccccc;border-bottom:0px" align="center"><div class="label" style="text-align:center">Logout:</div></td>
			<td width="30" class="top-area" style="border-top:1px solid #cccccc;border-right:1px solid #cccccc;border-bottom:0px" align="center"><a title="Logout" onClick="logout()" href="javascript:doSubmit('LOGOUT')"><img border="0" src="<?=  $temaDir ?>images/exit.png"></a></td>
			<td width="52">&nbsp;</td>
		</tr>
</table>
<?
	$form = $actionContext->getForm();
	//if ($actionContext->getForm() == "GROUP"){
	//Country ******************************************************************************
?>
		<table width="400"  border="0" cellpadding="10" cellspacing="0" class="work-area">
			<tr>
				<td width="150" class="loginLeftBox"><img
					 src="<?=  $temaDir ?>images/logo_login.png"></td>
				<td width="350" class="loginRightBox" valign="top">
				<div class="title"><? echo _t($settings['login_grup_des'][$form])?></div>
				<div class="text"><? echo _t($settings['login_grup_scel'][$form])?></div>
				<br />
	            <div class="label"><? echo _t($settings['login_grup_field'][$form])?>:</div>
<?
					$selectCountry = new wi400InputSelect($form);
					$selectCountry->setOptions($selectArray);
					$selectCountry->dispose();
?>
					<br>
		            <!--<input type="image" src="<?=  $temaDir ?>images/next.gif" align="right" id="nextImage" onClick="doSubmit('LOGIN_PROFILE')"><br>-->
	         	</td>
	         	<td width="50" class="cont_button" style="padding: 0px;">
		        	<input class="button-login" type="submit" id="nextImage" onClick="doSubmit('LOGIN_PROFILE')" style="width: 50px; height: 155px; font-size: 30px; border: 0px; font-weight: bold" value=">"/>
				</td>
			</tr>
		</table>
<?
	//}
?>
<![endif]-->

<!--[if !IE]> -->
<div class="body-area" style="position: absolute; width: 100%; top: 0px; bottom: 71px; left: 0px; padding: 0px;">
	<div style="position: absolute; width: 450px; height: 152px; left: 50%; margin-left: -225px; top: 50%; margin-top: -76px;">
		<div style="position: absolute; width: 86px; height: 26px; right: 49px;">
			<div class="top-area label" style="position: absolute; left: 0px; top: 0px; bottom: 0px; width: 52px; border-top: 1px solid #cccccc; border-left: 1px solid #cccccc; border-bottom: 0px; padding-top: 5px;" align="center">Logout:</div>
			<div class="top-area" style="position: absolute; right: 0px; top: 0px; bottom: 0px; width: 32px; border-top: 1px solid #cccccc; border-right: 1px solid #cccccc; border-bottom: 0px; padding-top: 4px;" align="center"><a title="Logout" onClick="logout()" href="javascript:doSubmit('LOGOUT')"><img border="0" src="<?=  $temaDir ?>images/exit.png"></a></div>
		</div>
		<div class="work-area" style="position: absolute; width: 450px; height: 126px; left: 0px; bottom: 0px;">
				<div class="loginLeftBox" style="position: absolute; left: 0px; width: 200px; height: 100%;">
					<img class="loginLeftBoxLogo" src="<?=  $temaDir ?>images/logo_login.png">
				</div>
				<div class="loginRightBox" style="position: absolute; right: 50px; padding: 10px; width: 180px; height: 106px;">
				<div class="title"><? echo _t($settings['login_grup_des'][$form])?></div>
				<div class="text"><? echo _t($settings['login_grup_scel'][$form])?></div>
				<br />
	            <div class="label"><? echo _t($settings['login_grup_field'][$form])?>:</div>
<?
					$selectCountry = new wi400InputSelect($form);
					$selectCountry->setOptions($selectArray);
					$selectCountry->dispose();
?>
					<!-- <input type="image" disabled src="<?=  $temaDir ?>images/next_disabled.gif" align="right" id="nextImage" onClick="doSubmit('CHECK_LOGIN')"><br>-->
				</div>
				<div class="cont_button" style="position: absolute; width: 50px; height: 100%; right: 0px;">
					<input class="button-login" type="submit" id="nextImage" onClick="loading();doSubmit('LOGIN_PROFILE')" style="width: 100%; height: 100%; border: 0px; font-size: 30px; font-weight: bold;" value=">"/>
				</div>
				<div class="spinner spinner--color spinner-none">
			      <div class="spinner__item1"></div>
			      <div class="spinner__item2"></div>
			      <div class="spinner__item3"></div>
			      <div class="spinner__item4"></div>
			    </div>
		</div>
	</div>
	<!-- <div style="position: absolute; left: 50%; margin-left: -225px; top: 50%; margin-top: 92.5px; width: 450px; height: auto; background-color: red;">
		 <div id="messageArea" onclick="resizeMessageArea()" class="messageArea_ERROR" style="position: absolute; top: 0px; left: 0px; width: 450px; height: auto; display: none;">
		</div>
	</div>-->
	<?php //require $base_path."/includes/messagesList.php"; ?>
</div>
<!-- <![endif]-->