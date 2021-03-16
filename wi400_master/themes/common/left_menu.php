<?php

	if (isset($_SESSION["WI400_WIZARD"])){
		
		require_once $themes_path."/common/wizard_left.php";
		
	}else{
		
		$settings['leftMenuRows'] = explode(",",$settings['leftMenuRows']);
		


		$leftMenuRowContentStyle = "";
		$leftMenuRowsStyle = "left-menu-label-selected";
		$leftMenuRowTag = "themes/common/images/preferiti_on.png";
		if ($settings['leftMenuRows'][0] == "false"){
			$leftMenuRowContentStyle = "style=\"display:none;\"";
			$leftMenuRowsStyle = "left-menu-label-active";
			$leftMenuRowTag = "themes/common/images/preferiti_off.png";
		}
?>										<div class="left-menu-row" onClick="slideRowMenu(0, 'themes/common/images/preferiti_on.png', 'themes/common/images/preferiti_off.png')">
                                        	<table cellpadding="0" cellspacing="0"><tr>
                                            <td><img id="left_menu_tag_0" src="<?= $leftMenuRowTag ?>" hspace="10"></td>
                                            <td id="left_menu_row_0" class="<?= $leftMenuRowsStyle ?>"><?=_t("PREFERITI")?></td>
                                            </tr></table></div>
                                            <div id="left_menu_content_0" <?= $leftMenuRowContentStyle ?>><div id="preferitiDiv"><?= getPreferitiHtml(); ?></div></div>
<?
$leftMenuRowContentStyle = "";
$leftMenuRowsStyle = "left-menu-label-selected";
$leftMenuRowTag = $temaDir."images/tag_yellow.gif";

$canClose = true;
$session_tree_menu = wi400Session::load(wi400Session::$_TYPE_TREE, "TREE_MENU");
$session_user_menu = wi400Session::load(wi400Session::$_TYPE_TREE, "USER_MENU");
if (!is_object($session_tree_menu) || !isset($_SESSION['sistema_informativo'])) {
	// REDIRECT LOGOUT
	echo "<script>document.location.href='".$appBase."index.php?t=LOGOUT';</script></body></html>";
	exit();
}	
if ($session_tree_menu->getAc() == 1){
	$canClose = false;
}

if (isset($settings['leftMenuRows'][1]) && $settings['leftMenuRows'][1] == "false" && $canClose){
	$leftMenuRowContentStyle = "style=\"display:none;\"";
	$leftMenuRowsStyle = "left-menu-label-active";
	$leftMenuRowTag = $temaDir."images/tag.gif";
}
?>											<div class="left-menu-row" <? if ($canClose){?>onClick="slideRowMenu(1)"<?}else{?>style="cursor:default"<?}?>>
	                                        	<table cellpadding="0" cellspacing="0">
	                                        		<tr>
	                                            		<td><? if ($canClose){?><img id="left_menu_tag_1" src="<?= $leftMenuRowTag ?>" hspace="10"><?}else{?><img height="23" width="15" id="left_menu_tag_1" src="themes/common/images/spacer.gif" hspace="10"><?}?></td>
	                                           			<td id="left_menu_row_1" class="<?= $leftMenuRowsStyle ?>"><?echo _t("MENU_FUNZIONALITA")?></td>
	                                            	</tr>
	                                            </table>
                                            </div>
                                            <div id="left_menu_content_1" <?= $leftMenuRowContentStyle ?>>
                                            	<input type="text" id="search_menu" class="input-search-action" autocomplete="off" placeholder="Cerca..." onkeyup="search(this.value)"/>
                                            	<div id="print_menu_1" style="padding:5px;padding-right:10px;">
                                            		<?php $session_tree_menu->printMenu(); ?>
                                            	</div>
                                            </div>
<?php
$leftMenuRowContentStyle = "";
$leftMenuRowsStyle = "left-menu-label-selected";
$leftMenuRowTag = $temaDir."images/tag_yellow.gif";
if (isset($settings['leftMenuRows'][2]) && $settings['leftMenuRows'][2] == "false"){
	$leftMenuRowContentStyle = "style=\"display:none;\"";
	$leftMenuRowsStyle = "left-menu-label-active";
	$leftMenuRowTag = $temaDir."images/tag.gif";
}
?>										<div class="left-menu-row" onClick="slideRowMenu(2)">
                                        	<table cellpadding="0" cellspacing="0"><tr>
                                            <td><img id="left_menu_tag_2" src="<?= $leftMenuRowTag ?>" hspace="10"></td>
                                            <td id="left_menu_row_2" class="<?= $leftMenuRowsStyle ?>"><?echo _t("MENU_WI400")?></td>
                                            </tr></table></div>
                                            <div id="left_menu_content_2" <?= $leftMenuRowContentStyle ?>><div style="padding:5px;padding-right:10px;"><?php $session_user_menu->printMenu(); ?></div></div>
<?php
			} // END WIZARD OFF
$leftMenuRowContentStyle = "";
$leftMenuRowsStyle = "left-menu-label-selected";
$leftMenuRowTag = $temaDir."images/tag_yellow.gif";
$jobInfo = getJobInfo();
if (isset($settings['leftMenuRows'][3]) && $settings['leftMenuRows'][3] == "false"){
	$leftMenuRowContentStyle = "style=\"display:none;\"";
	$leftMenuRowsStyle = "left-menu-label-active";
	$leftMenuRowTag = $temaDir."images/tag.gif";
}

$gruppo = "";
if (isset($_SESSION["LOGIN_PROFILE"]["GROUP"])) {
	$gruppo = $_SESSION["LOGIN_PROFILE"]["GROUP"];
	$des_gruppo = "";
	if($gruppo!="") {
		if(isset($settings['des_user_groups']) && array_key_exists($gruppo, $settings['des_user_groups']))
			$des_gruppo = strtoupper($settings['des_user_groups'][$gruppo]);
		else
			$des_gruppo = $gruppo;
	}
}
$desCod="";
if (isset($userData['DES_COD'])) $desCod=$userData['DES_COD'];
?>									<div class="left-menu-row" onClick="slideRowMenu(3)">
                                        	<table cellpadding="0" cellspacing="0"><tr>
                                            <td><img id="left_menu_tag_3" src="<?= $leftMenuRowTag ?>" hspace="10"></td>
                                            <td id="left_menu_row_3" class="<?= $leftMenuRowsStyle ?>"><?echo _t("UTENTE")?></td>
                                            </tr></table></div>
                                            <div id="left_menu_content_3" <?= $leftMenuRowContentStyle ?>><div><table>
                                          <tr>
                                            <td class="left-menu-content-label"><?echo _t("UTENTE")?>:</td>
                                           </tr>
                                           <tr>
                                            <td class="left-menu-content-text"><?= $userData['NOME'] ?> (<?=$userData['DESCRIZIONE']?>)</td>
                                          </tr>
<?php 
										if(isset($_SESSION['user_admin']) && $_SESSION['user_admin']==true) {
?>                                         
											<tr>
                                           <td class="left-menu-content-label"><?echo "Tipo utente"?>:</td>
                                           </tr>  
                                           <tr>
                                            <td class="left-menu-content-text"><?echo _t("AMMINISTRATORE")?></td>
                                           </tr>
 <?php
										} 
?>
                                          <tr>
                                            <td class="left-menu-content-label"><?echo _t("ENTE")?>:</td>
                                           </tr>
                                           <tr>
                                            <td class="left-menu-content-text"><?= $userData['CODICE']." ".$desCod?></td>
                                          </tr>
<?php 
									if($gruppo!="") {
?>                                          
                                          <tr>
                                            <td class="left-menu-content-label"><?echo ucfirst(_t("GRUPPO"))?>:</td>
                                           </tr>
                                           <tr>
                                            <td class="left-menu-content-text"><?= $des_gruppo?></td>
                                          </tr>
<?php 
									}
?>
                                          <tr>
                                            <td class="left-menu-content-label"><?echo _t("ULTIMO_ACCESSO")?>:</td>
                                            </tr>
                                           <tr><td class="left-menu-content-text"><?= date('d-m-Y-H:i:s',$_SESSION['last_login']) ?></td>
                                          </tr>
                                          <?
                                          if (isset($_SESSION['current_asp']) && $_SESSION['current_asp']!="" && $_SESSION['current_asp']!="*NONE") {
                                          ?><tr>
                                            <td class="left-menu-content-label"><?echo "ASP"?>:</td>
                                            </tr>
                                           <tr><td class="left-menu-content-text"><?= $_SESSION['current_asp'] ?></td>
                                          </tr><?
                                          }
                                          if (isset($settings['platform']) && $settings['platform']=="AS400") {
                                          ?>
                                          <tr>
                                            <td class="left-menu-content-label"><?echo "Job AS400"?>:</td>
                                            </tr>
                                           <tr><td class="left-menu-content-text"><?= trim($jobInfo['JOB'])."/".trim($jobInfo['USR'])."/".trim($jobInfo['NBR']) ?></td>
                                          </tr><?
                                          }?>
                                          <tr>
                                            <td class="left-menu-content-label"><?echo _t("SISTEMA_INFORMATIVO")?>:</td>
                                            </tr>
                                           <tr><td class="left-menu-content-text"><?= $_SESSION['sistema_informativo'] ?></td>
                                          </tr>
                                          <tr>
                                            <td class="left-menu-content-label"><?echo "PACKAGE"?>:</td>
                                          </tr>
                                           <tr><td class="left-menu-content-text"><?echo $settings['package'] ?></td>
                                          </tr>                                                                                    
                                          <tr>
                                            <td class="left-menu-content-label"><?echo _t("LANGUAGE")?>:&nbsp;&nbsp;<span><img alt="<?= $language ?>" border="0" src="themes/common/images/flags/<?= strtolower($language) ?>.png" /></span></td>
                                            </tr>
                                           <tr><td class="left-menu-content-text"><span style="font-weight:bold"><?echo _t("VERSION")?>:</span>&nbsp;<span><?= $settings["version"]?></span></td>
                                          </tr>
                                                                                     <tr><td class="left-menu-content-text"><span style="font-weight:bold"><?echo "SERVER"?>:</span>&nbsp;<span><?= $_SERVER["SERVER_ADDR"]?></span></td>
                                          </tr>
                                          </table></div></div>