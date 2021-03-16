<?php
/*if(mb_check_encoding($decodeResult,'UTF-8')===True) 
	$decodeResult= utf8_decode($decodeResult);*/
echo json_encode(array("decode" => $decodeResult, "fieldValue" => $fieldValue, "fieldId" => $fieldId, "fieldMessage" => $fieldMessage, "decodeFields" => $decodeFields));
?>