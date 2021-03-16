<?php 
$tracciato = array(
		array("Name"=>"BYTERETURN", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Bytes Return 
		array("Name"=>"BYTEAVAILABLE", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Bytes Avail 
		array("Name"=>"JOBNAME", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Job Name 
		array("Name"=>"JOBUSER", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // User Name 
		array("Name"=>"JOBNBR", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"6") , // Job Number 
		array("Name"=>"INTID", "IO"=>I5_INOUT, "Type"=>I5_TYPE_BYTE, "Length"=>"16") , // Internal Job Id 
		array("Name"=>"JOBSTATUS", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Job Status 
		array("Name"=>"JOBTYPE", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1") , // Job Type 
		array("Name"=>"JOBSUBTYPE", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1") , // Job Subtype 
		array("Name"=>"JOBSUBSYS", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Subsys Name 
		array("Name"=>"JOBPRTY", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Run Priority 
		array("Name"=>"JOBSYSPOOL", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // System Pool ID 
		array("Name"=>"JOBCPUU", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // CPU Used 
		array("Name"=>"JOBAUXIO", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Aux IO Request 
		array("Name"=>"JOBINTTR", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Interact Trans 
		array("Name"=>"JOBRT", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Response Time 
		array("Name"=>"JOBFUNCT", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1") , // Function Type 
		array("Name"=>"JOBFUNCN", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Function Name 
		array("Name"=>"JOBACTST", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"4") , // Active Job Stat 
		array("Name"=>"QUSD2Y", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Num DBase Lock Wts 
		array("Name"=>"QUSD2Z", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Num Internal Mch Lck 
		array("Name"=>"QUSD20", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Num Non DBase Lock W 
		array("Name"=>"QUSD21", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Wait Time DBase Lock 
		array("Name"=>"QUSD22", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Wait Time Internal M 
		array("Name"=>"QUSD23", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Wait Time Non DBase 
		array("Name"=>"QUSD26", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1") , // Reserved 
		array("Name"=>"QUSD24", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Current System Pool 
		array("Name"=>"QUSD25", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Thread Count 
		array("Name"=>"QUSD27", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // CPU Used Long 
		array("Name"=>"QUSD28", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Aux IO Request Long 
		array("Name"=>"QUSD29", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // CPU Used DB Long 
		array("Name"=>"QUSLTB", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Page Faults Long 
		array("Name"=>"QUSLTC", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"4") , // Active Job Stat Endi 
		array("Name"=>"QUSLTD", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Memory Pool Name 
		array("Name"=>"QUSLTF", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1") , // Message Reply 
		array("Name"=>"QUSLTG", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"4") , // Message Key 
		array("Name"=>"QUSLTH", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Message Queue 
		array("Name"=>"QUSLTJ", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Message Queue Librar 
		array("Name"=>"QUSLTK", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Message Queue Lib AS
	);
                       