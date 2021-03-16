<?php
$mess_icon = array("MSG_CED" => "fa-laptop",
					"MSG_SEGRETERIA" => "fa-shopping-cart"
);
$type_icon = array("SEGRETERIA"=> "fa-pencil"
		
);



/**
 * function getAnnouncements():
 * 		Takes no arguments.  Prints out the announcements from the announcement table in the database in
 * 	an easy to read format.
 */
function getAnnouncements($flag) {
	global $announcements_limit, $announcement_table, $a, $db, $lang_delete, $lang_edit, $lang_dateformat, $delimiter;
	global $enable_announcement_security_filtering;
	
	if ($a == 1) {
		$sql = "select * from $announcement_table order by id desc";
	} else {
		$sql = "select * from $announcement_table order by id desc";
	}
	
	$result = $db->query ( $sql );
	$i = 0;
	$uGroupArray = getUGroupsID ( $_SESSION ['user'] );
	if ($flag == 'user' || $flag == 'supporter') {
		while ( $row = $db->fetch_array ( $result ) ) {
			if ($flag = 'user') {
				if (strstr ( $row ['ALLOWED_GROUPS'], $delimiter )) {
					$groups = explode ( $delimiter, $row ['ALLOWED_GROUPS'] );
				} else {
					$groups = array ($row ['ALLOWED_GROUPS'] );
				}
				if (is_array ( $uGroupArray )) {
					foreach ( $uGroupArray as $a ) {
						if (in_array ( $a, $groups )) {
							$visible = true;
						}
					}
				}
			
			}
			if ($row ['ALLOWED_USER'] == $_SESSION ['user'] or $row ['ALLOWED_USER'] == 'All Users' or $enable_announcement_security_filtering == 'Off') {
				//echo "<b>" . date ( "$lang_dateformat G:i", $row ['TIME'] ) . "</b>";
				if ($i == $announcements_limit - 1) {
					echo "<a name=place></a>";
				}
				
				echo "\n";
				echo "\n&nbsp;&nbsp;&nbsp;&nbsp;" . nl2br ( $row ['MESSAGE'] ) . "\n";
			}
			$visible = false;
			$i ++;
		}
	}

}

/**
 * function getUGroups():
 *
 * Takes		 the username and returns an array containing the list of
 * user groups id number
 */
function getUGroupsID($user) {
	global $ugroups_table, $db, $settings;
	$groupArray = array ();
	$sql = "select id, group_name from $ugroups_table";
	$result = $db->query ( $sql );
	$i = 0;
	while ( $row = $db->fetch_array ( $result ) ) {
		$sql = "select id from " . $settings['table_prefix'] . "ugroup" . $row [0] . " WHERE user_name = '$user'";
		$resultGroup = $db->query ( $sql );
		$count = $db->num_rows ( $resultGroup );
		if ($count > 0) {
			$groupArray [$i] = $row [0];
		}
		$i ++;
	}
	// now list contains a list of all the groups
	return $groupArray;
}
/**
 * function getMessage():
 * 		Takes an integer value as input.  Queries the announcement table and returns the announcement
 * 	associated with the given id number.
 */
function getMessage($id) {
	global $announcement_table, $db;
	
	$sql = "select message from $announcement_table where id=$id";
	$result = $db->singleQuery ( $sql );
	$row = $db->fetch_array ( $result );
	
	return $row ['MESSAGE'];
}
