<?php 
function _dbslayer_map() 
{ 
    $substs = array( 
        'db2_connect' => 'dbslayer_db2_connect($host, $user, $pass, $options)', 
        'db2_fetch_array' => 'dbslayer_db2_fetch_array($result, $row)', 
        'db2_exec' => 'dbslayer_db2_exec($link_identifier, $query, $options)' 
            ); 

    $args = array( 
        'db2_connect' => '$host = NULL, $user = NULL, $pass = NULL, $options = NULL', 
        'db2_fetch_array' => '$result, $row = NULL', 
        'db2_exec' => '$link_identifier, $query, $options=NULL' 
            ); 

    foreach ($substs as $func => $ren_func) { 
        override_function($func, $args[$func], "echo '<br>$func'; return $substs[$func];"); 
        rename_function("__overridden__", $ren_func); 
      } 
} 
?> 