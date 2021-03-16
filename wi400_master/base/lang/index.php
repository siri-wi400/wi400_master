<?php

/*
* Copyright 2008 Siri Informatica
* info@siri-informatica.it
* http:://www.siri-informatica.it
* 
****************************************************************************/ 

// Come misura di sicurezza reindirizzo su pagina iniziale ogni tentativo di accesso da questa folder
$site = eregi_replace("/lang", "", $_SERVER['PHP_SELF']);
header("Location: $site");
?>