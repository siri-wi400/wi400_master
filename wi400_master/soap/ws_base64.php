<?php
if (!isset($_POST['call'])){
?>
<html>
<head></head>
<BODY>
<form method=post target="result">
    	<TABLE class=border cellSpacing=0 cellPadding=0 width="100%" align=center border=0>
				<TR>
				<TD>
					<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>
						<TR>
						<TD class=back2 align=right width=10%>textBase64: </td>
						<td class=back>
							<textarea name="xmlinput" rows="20" cols="120"></textarea>
						</td>
						</tr>
						</TABLE>
						</td>
						</tr>
						</TABLE>
			<input type=submit value="Decode Base 64" name=call class=back2>
			</form>
			</BODY>
</html>
<?php 
}else{
     $dati =$_POST['xmlinput'];
     $dati = base64_decode($dati);
     str_replace("\r\n", "<br>", $dati);
     echo "<pre>";
     echo $dati;
}
?>
