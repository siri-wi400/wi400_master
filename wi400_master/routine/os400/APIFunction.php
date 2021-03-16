<?php
global $api_err; 
$api_err = array(
    array("Name"=>"ERRBYTE", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0"),
    array("Name"=>"ERRBYTEAV", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0", "SetLen"=>"recerr"),
    array("Name"=>"EXCEPTION", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"7"),	    
    array("Name"=>"INITVALUE", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),    
    array("Name"=>"RESERVED", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
    //array("Name"=>"DATI", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"255"),	    
);

	    
function getApiDS($api, $formato) {
   global $base_path;
   $file = $base_path."/package/default/OS400ApiDS/".$api."_".$formato.".php";
   if (file_exists($file)) 
   {
             require $file;
   			 return $tracciato;
   } else return false;			 
}

