<?php
	if (isset($_REQUEST["LOOKUP"])){
		$lookupId = $_REQUEST["LOOKUP"];
		
		if(isset($_REQUEST['DETAIL_ID']) && $_REQUEST['DETAIL_ID']) {
			$fieldObj = wi400Detail::getDetailField($_REQUEST['DETAIL_ID'], $_REQUEST['FIELD_ID']);
			
			$look = $fieldObj->getLookUp();
			
			$lookUpContext->setFields($look->getFields());
			$_REQUEST[$lookupId."_FIELDS"] = join("|", $look->getFields());
			
			foreach ($look->getParameters() as $key => $valore) {
				$_REQUEST[$key] = $valore;
			}
		}else if(isset($_REQUEST['COLONNA']) && $_REQUEST['COLONNA']){
			$fromList = $_REQUEST['FROM_LIST'];
			$fromRow =  $_REQUEST['FROM_ROW'];
			$colonna = $_REQUEST['COLONNA'];
			$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $fromList);
			$cols = $wi400List->getCols();
			$col = $cols[$colonna];
			$fieldObj = $col->getInput();
			$look = $fieldObj->getLookUp();
			
			$fields = $look->getFields();
			foreach($fields as $key => $valore) {
				$array = explode("-", $valore);
				$array[1] = $fromRow;
				
				$fields[$key] = implode("-", $array);
			}
			$lookUpContext->setFields($fields);
			$_REQUEST[$fromList."-".$fromRow."-".$colonna."_".$_REQUEST['t']."_FIELDS"] = join("|", $fields);
			
			foreach ($look->getParameters() as $key => $valore) {
				$_REQUEST[$key] = $valore;
			}
			if (isset($_REQUEST['WI400_LIST_KEY'])) {
				$list_key=array();
				$keys = $wi400List->getKeys();
				$valore = explode("|", $_REQUEST['WI400_LIST_KEY']);
				$ii=0;
				foreach ($keys as $kkk => $vvv) {
					$list_key[$kkk]=$valore[$ii];
					$ii++;
				}
				$_REQUEST['WI400_LIST_KEY_ARRAY']=$list_key;
			}
		}
		
		//showArray($_REQUEST);
		
		//TODO:$lookUpContext->setJsParameters(explode("|",$_REQUEST[$lookupId."_JS_PARAMETERS"]));
	}

?>