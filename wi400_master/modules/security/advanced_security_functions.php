<?php

	require_once $routine_path."/classi/wi400Otm.cls.php";

	function getErrorConLink($user) {
		global $routine_path, $appBase;
		
		$_SESSION['RESET_PSW_USER'] = $user;
		
		/*$otm = new wi400Otm();
		$key = $otm->getOtmPassword("FORN001", "TEXT", "action=CHGPWD", "");
		
		$html = "<br><a href='{$appBase}index.php?OTM=$key'>Reset password</a>";*/
		//$html = "<br><a onClick='openWindow(_APP_BASE + APP_SCRIPT + \"?t=RESET_PSW&DECORATION=lookup\", \"resetPsw\", \"900\", \"700\");' style='text-decoration: underline; color: gray; cursor: pointer;'>
		$html = "<br><a href='{$appBase}resetPassword.php?user=$user' style='text-decoration: underline; color: gray; cursor: pointer;'>
						Reset password
					</a>";
		
		return $html;
	}