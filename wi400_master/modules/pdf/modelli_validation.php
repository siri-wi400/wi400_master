<?php 

	if($actionContext->getForm()=="COPY") {
		$sql = "select * from SIR_MODULI where MODNAM ='".$_POST['codmod']."'";
//		echo "SQL: $sql<br>";
		$result = $db->query($sql);
		if($row = $db->fetch_array($result)) {
//			echo "ROW:<pre>"; print_r($row); echo "</pre><br>";
			$messageContext->addMessage("ERROR", "Codice già presente.", "codmod",true);
		}
	}

?>