<?php
// Anagrafiche gestite in data
$gestione_date = array();
// FMDAANAR
$gestione_date['FMDAANAR'] = 
   array("DATA"=> array(
   "KEY"=>"MDACDA",
   "ANNO"=>"MDAAVA",
   "MESE"=>"MDAMVA",
   "GIORNO"=>"MDAGVA",
   "INDEX"=>"LMDAANAR"
   ),
   "LINK_EVAL"=>array(
   "MDAFOR"=>array(
   "FUNCTION"=>"interlocutore"
   ),
   "MDACON"=>array(
   "FUNCTION"=>"table",
   "TABLE"=>"0005",
   ),
   "MDACDA"=>array(
   "LOOKUP_TEMPLATE"=>"articolo_lookup"
   )				   
   ));
// FMAFENTI
$gestione_date['FMAFENTI'] = 
   array("DATA"=> array(
   "KEY"=>"MAFCDE",
   "ANNO"=>"MAFAVA",
   "MESE"=>"MAFMVA",
   "GIORNO"=>"MAFGVA",
   "INDEX"=>"LMAFENTI" 
   ));
// INTERLOCUTORI
$gestione_date['FMEBINTL'] = 
   array("DATA"=> array(
   "KEY"=>"MEBCDF",
   "ANNO"=>"MEBAVA",
   "MESE"=>"MEBMVA",
   "GIORNO"=>"MEBGVA",
   "INDEX"=>"LMEBINTL"      
   )); 
// Assortimento
$gestione_date['FMHCASSR'] = 
   array("DATA"=> array(
   "KEY"=>"MHCCDE",
   "ANNO"=>"MHCAVA",
   "MESE"=>"MHCMVA",
   "GIORNO"=>"MHCGVA",
   "INDEX"=>"LMHCASSR"
   ),
   "LINK_EVAL"=>array(
   "MHCCDA"=>array(
   "FUNCTION"=>"articolo"
   ),
   "MHCCDE"=>array(
   "FUNCTION"=>"ente"
   )
   ));     
?>