<?php
/*if(mb_check_encoding($decodeResult,'UTF-8')===True) 
	$decodeResult= utf8_decode($decodeResult);*/
echo "REPLY:".json_encode(array("outputHtmlRow" => $outputHtml, "fixedHtmlRow" => $fixedHtml, "action"=>$action, "message"=>$message)).":END-REPLY";
?>