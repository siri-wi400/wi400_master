<?
	$keyArray = explode("|",$_GET['DETAIL_KEY']);
  $sql = "SELECT * FROM FPDFCONV WHERE MAIREC ='".$keyArray[0]."'";
  $result = $db->query($sql);
  $row = $db->fetch_array($result);
   	
?>