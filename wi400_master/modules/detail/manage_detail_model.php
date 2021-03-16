<?php
	
	if ($actionContext->getForm() == "SAVE"){
		
		if (isset($_REQUEST["IDDETAIL"])){
			
			if (isset($_REQUEST["DEFAULT_DETAIL"])){
				
				$idDetail = $_REQUEST["IDDETAIL"];
				$defaultDetail = $_REQUEST["DEFAULT_DETAIL"];
				
				if (isset($_REQUEST["DELETE_DETAIL"])){
					
					if ($defaultDetail != ""){
						// Cancellazione valori di default
						$wi400DetailValues = wi400Detail::loadCustomValues($idDetail);
						unset ( $wi400DetailValues [$defaultDetail] );
						if (isset($wi400DetailValues["DEFAULT_DETAIL"])
								&& $wi400DetailValues["DEFAULT_DETAIL"] == $defaultDetail){
							unset($wi400DetailValues["DEFAULT_DETAIL"]);
						}
						wi400Detail::saveCustomValues($idDetail, $wi400DetailValues);
					}
					
				}else{
					
					// Selezione di default
					$wi400DetailValues = wi400Detail::loadCustomValues($idDetail);
					
					if ($defaultDetail != ""){
						$wi400DetailValues["DEFAULT_DETAIL"] = $defaultDetail;
					}else{
						unset($wi400DetailValues["DEFAULT_DETAIL"]);
					}
					wi400Detail::saveCustomValues($idDetail, $wi400DetailValues);
				}
				
			} // isset DEFAULT_DETAIL

		} // isset IDDETAIL
		
	} // ACTION CONTEXT
?>