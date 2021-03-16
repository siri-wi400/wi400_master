 <?php
   require_once $routine_path."/os400/wi400Os400Spool.cls.php";
    
   $jobname=   $keyArray[0];    //job
   $jobuser=   $keyArray[1];    //user
   $jobnumber= $keyArray[2];    //nbr
   $splfname=  $keyArray[3];    //splf
   $splfnbr=   $keyArray[4];    //splfnbr
   $ifsfile='/home/test.txt';  // *not used here but optional last parm

 // string i5_spool_get_data(string spool_name, string jobname, string username, 
 //                           integer job_number, integer spool_id [,string filename]) 
 
//$str = i5_spool_get_data($splfname,$jobname,$jobuser,$jobnumber,$splfnbr);
$jobqual = str_pad($jobname, 10).str_pad($jobuser, 10).str_pad($jobnumber, 6);
$dati = wi400Os400Spool::getData($jobqual, $splfname, $splfnbr, "*HTML");
//echo "DATI:<pre>"; print_r($dati); echo "</pre>";
$str = implode("<br>", $dati);
     if ($str === false) {
       print("<br>Command failed");
     }
$str = utf8_encode($str);     
// Tolgo l'ultimo carattere
//$str= substr($str, 0, strlen($str)-1);
//$ultimo = substr($str, strlen($str)-1, 1);
//$replace = "<br><FONT COLOR='#de0021'><-------------------------------------- "._t('SPOOL_PAGE_SKIP')." -----------------------------------------></FONT><br>";
//$str = str_replace  ( chr(12)  , $replace  , $str  , $count );
//$str = str_replace

$out_spool= '<pre><code>';
$out_spool.="<FONT COLOR='#FF00FF'><-------------------------------------- "._t('SPOOL_PAGE_BEGIN')." ----------------------------------------></font><br>";     
$out_spool.=$str;
$out_spool.="<br><FONT COLOR='#FF00FF'><--------------------------------------- "._t('SPOOL_PAGE_END')." -----------------------------------------></font>";
echo $out_spool;