<?
	$keyArray = explode("|",$_GET['DETAIL_KEY']);
 	
	//$sql="SELECT * FROM ZLLISCLI WHERE ZLLNEG='".$_SESSION['locale']."' AND ZLLCDA = '".$keyArray[0]."'";

	$sql="SELECT * FROM ZLLISCLI WHERE ZLLCDA = '".$keyArray[0]."'";	
	
	$result = $db->query($sql);
	
	$row = $db->fetch_array($result);
/*
	if (!isset($row["ZLLCDA"])){
?>
<script>
	alert("Articolo non trovato!");
	if (window.opener){
		self.close();
	}else{
		top.f_dialogClose();
	}
</script>
<?		
	exit();
	}
?>
*/