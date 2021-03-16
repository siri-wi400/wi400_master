<?php 
$tracciato = array(
	array("Name"=>"BYTEAVL", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Bytes available 
	array("Name"=>"BYTERET", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Bytes returned
	array("Name"=>"CURDATE", "IO"=>I5_INOUT, "Type"=>I5_TYPE_BYTE, "Length"=>"8") , // Current date and time
	array("Name"=>"SYSNAME", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"8") , // System name 
	array("Name"=>"ELAPSED", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"6") , // Elapsed time
	array("Name"=>"RESTRICTED", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1") , // Restricted state flag	
	array("Name"=>"RESERVED", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1") , // Reserved
	array("Name"=>"PPROCESU", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // % processing unit used				
	array("Name"=>"JOBSYS", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Jobs in system
	array("Name"=>"PPERMADD", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // % permanent addresses
	array("Name"=>"PTEMPADD", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // % temporary addresses
	array("Name"=>"SYSASP", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // System ASP
	array("Name"=>"PSYSASP", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // % system ASP used
	array("Name"=>"TOTAUX", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Total auxiliary storage
	array("Name"=>"CURUNPSU", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Current unprotected storage used
	array("Name"=>"MAXUNPSU", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Maximum unprotected storage used
	array("Name"=>"DBCAP", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // % DB capability
	array("Name"=>"MAINSTG", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Main storage size
	array("Name"=>"NUMBPART", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Number of partitions
	array("Name"=>"IDPART", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Partition identifier
	array("Name"=>"RESERVED2", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Reserved
	array("Name"=>"CURPROCAP", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Current processing capacity
	array("Name"=>"PROCSHRA", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1") , // Processor sharing attribute
	array("Name"=>"RESERVED3", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"3") , // Reserved
	array("Name"=>"NUMPROC", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Number of processors
	array("Name"=>"ACTIVEJOB", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Active jobs in system
	array("Name"=>"ACTIVETHD", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Active threads in system
	array("Name"=>"MAXJOB", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Maximum jobs in system
	array("Name"=>"TEMP256MB", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // % temporary 256MB segments used
	array("Name"=>"TEMP4GB", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // % temporary 4GB segments used
	array("Name"=>"TEMP256MB_2", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // % temporary 256MB segments used
	array("Name"=>"TEMP4GB_2", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // % temporary 4GB segments used
	array("Name"=>"INTPERF", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // % current interactive performance
	array("Name"=>"CPUUNCAP", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // % uncapped CPU capacity used
	array("Name"=>"SHARPROC", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // % shared processor pool used
);
