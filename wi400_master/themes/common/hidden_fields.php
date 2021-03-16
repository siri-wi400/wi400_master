<input type="hidden" id="LEFT_MENU_STATUS" disabled name="LEFT_MENU_STATUS" value="<?= $leftMenuOpen ?>">
<input type="hidden" id="LEFT_MENU_ROWS_STATUS" name="LEFT_MENU_ROWS_STATUS" value="<?= $settings['leftMenuRows'] ?>">
<input type="hidden" id="CURRENT_ACTION" name="CURRENT_ACTION" value="<?= $actionContext->getAction() ?>">
<input type="hidden" id="CURRENT_FORM" 	 name="CURRENT_FORM"   value="<?= $actionContext->getForm() ?>">
<input type="hidden" id="UPDATE_STATUS" disabled name="UPDATE_STATUS" value="<?= $_SESSION["UPDATE_STATUS"]; ?>">