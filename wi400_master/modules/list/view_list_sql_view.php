<?php

	$spacer = new wi400Spacer();

	$sql_detail = new wi400Detail($azione, true);
	$sql_detail->setTitle('Query SQL');
	$sql_detail->isEditable(true);

	$myField = new wi400TextPanel('SQL_BODY');
	$myField->setHeight(250);
	$myField->setValue(wordwrap($query, 90));
	$sql_detail->addField($myField);
/*	
	$myField = new wi400InputTextArea('SQL_BODY');
	$myField->setReadonly(true);
	$myField->setSize(145);
	$myField->setRows(25);
	$myField->setValue($query);
	$sql_detail->addField($myField);
*//*	
	$myButton = new wi400InputButton("CLOSE_BUTTON");
	$myButton->setScript('closeLookUp()');
	$myButton->setLabel("Chiudi");
	$sql_detail->addButton($myButton);
*/	
	$sql_detail->dispose();
	
	if(isset($settings['count_query']) && $settings['count_query']===true) {
		$spacer->dispose();
		
		$query = "";
		if(!($wi400List->getQuery()!="" && strtoupper(substr(trim($wi400List->getQuery()), 0, 4))=="WITH")) {
//			$query = trim($wi400ListSql->get_query(false, true));

			// CON PULIZIA DEL FROM
			$wi400ListSql->set_cleanCount(true);
			$query = $wi400ListSql->get_query(false, true);
//	    	echo "<font color='blue'>QUERY CLEAN:</font> $query<br>";
			
			$do = $db->singleQuery($query);
			
			if($wi400List->getQuery()!==true) {
				if(!$do) {
					// SENZA PULIZIA DEL FROM
					$wi400ListSql->set_cleanCount(false);
					$query = $wi400ListSql->get_query(false, true);
//		    		echo "<font color='blue'>QUERY:</font> $query<br>";
			
					$do = $db->singleQuery($query);
				}
			}
		}
//		echo "QUERY: $query<br>";
		
		$sql_detail = new wi400Detail($azione."_COUNT", true);
		$sql_detail->setTitle('Count Query SQL');
		$sql_detail->isEditable(true);
		
		$myField = new wi400TextPanel('SQL_BODY');
		$myField->setHeight(250);
		$myField->setValue(wordwrap($query, 90));
		$sql_detail->addField($myField);
		
		$sql_detail->dispose();
	}
	
	if(isset($settings['current_query']) && $settings['current_query']===true) {
		$spacer->dispose();
	
		$query = trim($wi400List->getCurrentQuery());
//		echo "QUERY: $query<br>";
	
		$sql_detail = new wi400Detail($azione."_CURRENT", true);
		$sql_detail->setTitle('Current Query SQL');
		$sql_detail->isEditable(true);
	
		$myField = new wi400TextPanel('SQL_BODY');
		$myField->setHeight(250);
		$myField->setValue(wordwrap($query, 90));
		$sql_detail->addField($myField);
	
		$sql_detail->dispose();
	}
	
	if(isset($settings['subfile_query']) && $settings['subfile_query']===true) {
		$subfile_name = $wi400List->getSubfile();
//  	echo "SUBFILE NAME: $subfile_name<br>";
		
		if($subfile_name!="") {		
			$spacer->dispose();
	
			$subfile = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $subfile_name);
			
			$query = trim($subfile->getSql());
//			echo "QUERY: $query<br>";
	
			$sql_detail = new wi400Detail($azione."_SUBFILE", true);
			$sql_detail->setTitle('Subfile Query SQL');
			$sql_detail->isEditable(true);
		
			$myField = new wi400TextPanel('SQL_BODY');
			$myField->setHeight(250);
			$myField->setValue(wordwrap($query, 90));
			$sql_detail->addField($myField);
		
			$sql_detail->dispose();
		}
	}
	
	$myButton = new wi400InputButton("CLOSE_BUTTON");
	$myButton->setScript('closeLookUp()');
	$myButton->setLabel("Chiudi");
	$buttonsBar[] = $myButton;