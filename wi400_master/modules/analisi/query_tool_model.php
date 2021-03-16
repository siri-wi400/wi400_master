<?php 

	require_once 'query_tool_commons.php';

	$azione = $actionContext->getAction();
//	echo "FORM: ".$actionContext->getForm()."<br>";
	
	if($actionContext->getForm()=="DEFAULT") {
		wi400Detail::cleanSession($azione."_SRC");
	}
	else if($actionContext->getForm()=="DEFAULT_LIBERO") {
		wi400Detail::cleanSession($azione."_LIBERO_SRC");
	}
	
	if(in_array($actionContext->getForm(),array("DEFAULT","EXECUTE","DEFAULT_LIBERO"))) {
		if(in_array("QUERY_FILTRO", $_SESSION ["WI400_GROUPS_BACKUP"])) {
//			echo "SESSION:<pre>"; print_r($_SESSION); echo "</pre>";
//			echo "SESSION:<pre>"; print_r($_SESSION['QUERY_TOOL_SRC_LOAD_DETAIL']); echo "</pre>";
			if(isset($_SESSION['QUERY_TOOL_SRC_LOAD_DETAIL'])) {
				$_SESSION['QUERY_TOOL_SRC_QUERY_FILTRO'] = $_SESSION['QUERY_TOOL_SRC_LOAD_DETAIL'];
			}
			
			foreach($array_campi as $campo) {
				$field = strtolower($campo);
					
				$$field = "";
				if(isset($_SESSION['QUERY_TOOL_SRC_QUERY_FILTRO'])) {
					$field_obj = $_SESSION['QUERY_TOOL_SRC_QUERY_FILTRO'][$campo];
			
					$$field = $field_obj->getValue();
				}
					
//				echo "$campo: ".$$field."<br>";
			}
		}
		else {
			foreach($array_campi as $campo) {
				$field = strtolower($campo);
/*			
				if(isset($_SESSION[$campo.'_CAMPI'])) {
					$$field = trim($_SESSION[$campo.'_CAMPI']);
					unset($_SESSION[$campo.'_CAMPI']);
				}
				else {
					$$field = trim(wi400Detail::getDetailValue($azione."_SRC",$campo));
				}
*/			
				$$field = "";
				if(!is_null(wi400Detail::getDetailValue($azione."_SRC",$campo)))
					$$field = trim(wi400Detail::getDetailValue($azione."_SRC",$campo));
/*			
				if(isset($_SESSION[$campo.'_CAMPI'])) {
					if(trim($$field!=""))
						$$field .= ", ";
					$$field .= trim($_SESSION[$campo.'_CAMPI']);
					unset($_SESSION[$campo.'_CAMPI']);
				}
*/			
//				echo "$campo: ".$$field."<br>";
			}
		}
//		$from = trim(wi400Detail::getDetailValue($azione."_SRC","FROM"));
//		echo "FROM: $from<br>";
	}
	
	if(in_array($actionContext->getForm(),array("DEFAULT_LIBERO","EXECUTE_LIBERO"))) {
		$sql_query = wi400Detail::getDetailValue($azione."_LIBERO_SRC","SQL_QUERY");
		
		if($actionContext->getForm()=="EXECUTE_LIBERO" && $sql_query=="")
			$actionContext->gotoAction($azione, "DEFAULT_LIBERO", "", true);
	}
	
	if($actionContext->getForm()=="EXECUTE" ||
		($actionContext->getForm()=="DEFAULT_LIBERO" && $sql_query=="" && $select!="")
	) {
		if($actionContext->getForm()=="EXECUTE" && ($select=="" || $from=="")) {
			$actionContext->gotoAction($azione, "DEFAULT", "", true);
		}
		else {
			$sql_query = "SELECT $select FROM $from";
			
			if(isset($where) && trim($where)!="")
				$sql_query .= " WHERE $where";
			if(isset($group_by) && trim($group_by)!="")
				$sql_query .= " GROUP BY $group_by";
			if(isset($order_by) && trim($order_by)!="")
				$sql_query .= " ORDER BY $order_by";
			
//			echo "SQL: $sql_query<br>";
		}
	}
	
	if(in_array($actionContext->getForm(),array("EXECUTE","EXECUTE_LIBERO"))) {
		// Impostazione formato tabella
		$queries = explode(";\x0d\x0a", $sql_query);
		foreach ($queries as $key => $value) {
			if ($value !="") {
				$res = $db->query($value);
				if(!$res) {	
					$messageContext->addMessage("ERROR","Query errata: ".$value);
//					$messageContext->addMessage("ERROR","Query errata");
				} else {	
//					$messageContext->addMessage("SUCCESS","Query OK: ".$value);
					$messageContext->addMessage("SUCCESS","Query OK");
				}
			}
		}	
		// Metto a video il risultato dell'ultima query
		if ($riga = $db->fetch_array($res)) {		
			$col_token = join("_", array_keys($riga));
			if(!isset($_SESSION["analisi_query_columns_list"]) ||
				$_SESSION["analisi_query_columns_list"]!=$col_token
			){			
				$_SESSION["analisi_query_columns_list"] = $col_token;
				
				$listConfig = wi400File::getUserFile("list", $azione."_LIST.lst");
				
				if(file_exists($listConfig)) 
					unlink($listConfig);
			}
		}
		else {
			$messageContext->addMessage("INFO", "Non è stata trovata alcuna riga");
		}
	}

?>