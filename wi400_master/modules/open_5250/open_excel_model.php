<?php

require_once $routine_path."/classi/wi400Websocket.cls.php";
$ws = new WebsocketClient('127.0.0.1','8080');
require_once $moduli_path."/socket_server/rachet_server_function.php";
$id =webs_create_token();
$connection_string=array("action"=>"INIT", "token"=>"$id", "ip"=>"10.0.40.1", "user"=>"PHPMAN", "request"=>"CLIENT", "id"=>"dfsljflksj", "sender"=>"CLIENT");
$connection_string=base64_encode(json_encode($connection_string));
$result = $ws->sendData($connection_string);
//$result = json_decode(base64_decode($result));
// Devo Reperire il tipo di emulatore presente sul PC
$messaggio[] = array("operazione"=>"DOWNLOAD_LINK", "dati"=>"http://10.0.40.1:89/software/mio.xls", "extra"=>"mio.xls");
$messaggio[] = array("operazione"=>"EXCELOPEN", "dati"=>"mio.xls");
$connection_string2=array("sender"=>"CONSOLE", 'action'=>"MSG", "from" => array("connid" =>"me"), "to"=>array("session"=>session_id()), "msg"=>$messaggio);
//echo base64_encode(json_encode($connection_string2));
//die();
$connection_string2=base64_encode(json_encode($connection_string2));
$result = $ws->sendData($connection_string2);