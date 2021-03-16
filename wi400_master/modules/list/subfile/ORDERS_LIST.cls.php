<?php 

class ORDERS_LIST extends wi400CustomSubfile {
	

	public function __construct($parameters){
		global $db, $connzend;
		
	}
	
	public function getArrayCampi() {	
		global $db;
		
		$array = array();
		$array['ID']=$db->singleColumns("1", "20", "", "Id");
		$array['COLONNA']=$db->singleColumns("1", "100", "", "Colonna");
		$array['TYPE']=$db->singleColumns("1", "4", "", "Ordinamento");
		
		return $array;
	}
	
	public function init($parameters){
		global $db;
		
		$this->setCols($this->getArrayCampi());
	}
	

	public function body($campi, $parameters){	
		global $db, $connzend;
		
		$parentList = getList($parameters["ORDLIST"]);
		$orders = $parentList->getOrder();
		
		if (is_array($orders) && sizeof($orders)>0){
			$field = array("ID", "COLONNA", "TYPE");
			$stmtinsert = $db->prepare("INSERT", $this->getFullTableName(), null, $field);
			
			foreach ($orders as $key => $type){
				$col = $parentList->getCol($key);
				
				$colGroup = "";
				if ($col->getGroup() != "") $colGroup = " (".$parentList->getGroupDescription($col->getGroup()).")";
				
				$campi = array($key, str_replace("<br>"," ",$col->getDescription().$colGroup), $type);
				$db->execute($stmtinsert, $campi);
			}
			
		}
		
	    return false;
	}
	
}

?>