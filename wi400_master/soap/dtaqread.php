<?php
if (!isset($_POST['call'])){
?>
<html>
<head></head>
<BODY>
<form method=post target="result1">
    	<TABLE class=border cellSpacing=0 cellPadding=0 width="100%" align=center border=0>
				<TR>
				<TD>
					<TABLE cellSpacing=1 cellPadding=5 width="100%" border=0>
						<TR>
						<TD class=back2 align=right width=27%>Libreria: </td>
						<td class=back>
						<input name=libre value="">
						</td>
						</tr>
						<TR>
						<TD class=back2 align=right width=27%>DtaQ: </td>
						<td class=back>
							<input name=dtaq value="">
						</td>
						</tr>
						</TABLE>
						</td>
						</tr>
						</TABLE>
			<input type=submit value="Reperisci primo elemento coda FIFO" name=call class=back2>
			</form>
</BODY>
</html>
<?php 
}else{

$i5_server_ip = "localhost";
$i5_uname = "SINDLABIT";
$i5_pass = "SINDLABIT";

$conn = i5_connect($i5_server_ip, $i5_uname, $i5_pass);
if (!$conn) {
die(i5_errormsg());
}
$description = array("Name"=>"DATA", "Type"=>I5_TYPE_CHAR,
"Length"=>"1000");
$queue = i5_dtaq_prepare(trim($_POST['libre'])."/".trim($_POST['dtaq']), $description);

$ret = i5_dtaq_receive($queue);
echo $ret;
i5_dtaq_close($queue);
i5_close($conn);
}
?>