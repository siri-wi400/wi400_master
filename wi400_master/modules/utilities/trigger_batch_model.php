<?php
$parametri = $batchContext->TRIGGER_PARM;
$parametri = unserialize(base64_decode($parametri));
$azione = $parametri['azione'];
$form = $parametri['form'];
$get = $parametri['get'];
$post = $parametri['post'];
$request = $parametri['request'];
$session_id = $parametri['session_id'];
$trigger_param = $parametri['trigger_param'];
require_once $routine_path."/generali/encryption.php";
wi400_runAction($azione,$form, $get, $post,$request,$session_id, $trigger_param, False);