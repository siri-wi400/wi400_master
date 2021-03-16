<?php 
$tracciato = array(
	array("Name"=>"QSPBCB", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Bytes Return 
	array("Name"=>"QSPBCC", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Bytes Avail 
	array("Name"=>"QSPBCD", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Output Queue Name 
	array("Name"=>"QSPBCF", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Output Queue Lib 
	array("Name"=>"QSPBCG", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Order of Files 
	array("Name"=>"QSPBCH", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Display Any File 
	array("Name"=>"QSPBCJ", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Job Separators 
	array("Name"=>"QSPBCK", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Operator Control 
	array("Name"=>"QSPBCL", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Data Queue Name 
	array("Name"=>"QSPBCM", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Data Queue Lib 
	array("Name"=>"QSPBCN", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Authority to Check 
	array("Name"=>"QSPBCP", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Number of Files 
	array("Name"=>"QSPBCQ", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Output Queue Status 
	array("Name"=>"QSPBCR", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Writer Job Name 
	array("Name"=>"QSPBCS", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Writer Job User 
	array("Name"=>"QSPBCT", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"6") , // Writer Job Number 
	array("Name"=>"QSPBCV", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Writer Status 
	array("Name"=>"QSPBCW", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Printer Device Name 
	array("Name"=>"QSPBCX", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"50") , // Output Queue Descr 
	array("Name"=>"QSPBCY", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"2") , // Reserved2 
	array("Name"=>"QSPBCZ", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Number Of Page Sizes 
	array("Name"=>"QSPBC0", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Number Of Writers St 
	array("Name"=>"QSPBC1", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Writers To Autostart 
	array("Name"=>"QSPBC2", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1") , // Remote System Name T 
	array("Name"=>"QSPBC3", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"255") , // Remote System Name 
	array("Name"=>"QSPBC4", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"128") , // Remote Printer Queue 
	array("Name"=>"QSPBC5", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Message Queue 
	array("Name"=>"QSPBC6", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Message Queue Librar 
	array("Name"=>"QSPBC7", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Connection Type 
	array("Name"=>"QSPBC8", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Destination Type 
	array("Name"=>"QSPBC9", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1") , // VMMVS Class 
	array("Name"=>"QSPBDB", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"8") , // FCB 
	array("Name"=>"QSPBDC", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1") , // Transform SCS To ASC 
	array("Name"=>"QSPBDD", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"17") , // Manufacturer Type Mo 
	array("Name"=>"QSPBDF", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Workstation Cust Obj 
	array("Name"=>"QSPBDG", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Workstation Cust Obj 
	array("Name"=>"QSPBDY", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1") , // Splf Aux Storage Poo 
	array("Name"=>"QSPBDJ", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Spooled File Size Of 
	array("Name"=>"QSPBDK", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Number Of Size Entri 
	array("Name"=>"QSPBDL", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Length Of Each Size 
	array("Name"=>"QSPBDN", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"128") , // Destination Options 
	array("Name"=>"QSPBDS", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1") , // Writer Type 
	array("Name"=>"QSPBDT", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1") , // Separator Page 
	array("Name"=>"QSPBDV", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"255") , // Long Remote Printer 
	array("Name"=>"QSPBDW", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Image Configuration 
	array("Name"=>"QSPBDX", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Image Configuration 
	array("Name"=>"QSPBD1", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1") , // Network Directory Pu 
	array("Name"=>"QSPBDZ", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"2") , // Reserved3 
	array("Name"=>"QSPBD0", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Aux Storage Pool ID 
	array("Name"=>"QSPBD2", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // ASP Device Name 
	);
                       