<?php

	// In ATG gli utenti si repirscono in modo diverso, per cui è stata creata una personalizzazione in p13n
	function get_user_sql($user_array=array(), $in_menu=array()) {
		global $users_table;
		
		$select = "select USER_NAME, MENU, USER_MENU, '' as NAZIONE, '' as AREAFUN";
		$from = " from $users_table";
		$where = "";
		$order_by = " order by USER_NAME, NAZIONE, AREAFUN";
		
		$where_array = array();
		
		if(!empty($user_array)) {
			$where_array[] = "USER_NAME in ('".implode("', '", $user_array)."')";
		}
		
		if(!empty($in_menu)) {
			$where_array[] = "MENU in ('".implode("', '", $in_menu)."') or USER_MENU in ('".implode("', '", $in_menu)."')";
		}
		
		if(!empty($where_array)) {
			$where = " where ".implode(" and ", $where_array);
		}
		
		$sql_user = $select.$from.$where.$order_by;
//		echo "SQL USER: $sql_user<br>";

		return $sql_user;
	}
	
	function get_menu_array($search=array()) {
		global $db;
		
		static $menu_array = array();
		
		if(!isset($menu_array) || empty($menu_array)) {
//			echo "CARICA MENU ARRAY<br>";
			
			$sql_menu = "select MENU, DESCRIZIONE, AZIONI from FMNUSIRI";
			if(!empty($search)) {
				$sql_menu .= " where MENU in ('".implode("', '", $search)."')";
			}
			$res_menu = $db->query($sql_menu, false, 0);
			
			$sql_azi = "select AZIONE, DESCRIZIONE, TIPO from FAZISIRI where AZIONE=?";
			$stmt_azi = $db->singlePrepare($sql_azi, 0, true);
			
			// Caricamento struttura di ogni Menu
			$menu_array = array();
			while($row_menu = $db->fetch_array($res_menu)) {
				$azioni_array = explode(";", $row_menu['AZIONI']);
					
				if(!empty($azioni_array)) {
					$azioni_array = array_flip($azioni_array);
//					echo "AZIONI ARRAY:<pre>"; print_r($azioni_array); echo "</pre>";
			
					foreach($azioni_array as $azione => $val) {
						$azi = array();
						
						$azione = strtoupper($azione);
							
						$res_azi = $db->execute($stmt_azi, array($azione));
						if($row_azi = $db->fetch_array($stmt_azi)) {
							$azi = array(
								"DES" => $row_azi['DESCRIZIONE'],
								"TIPO" => $row_azi['TIPO']
							);
						}
							
						$azioni_array[$azione] = $azi;
					}
				}
//				echo "AZIONI ARRAY:<pre>"; print_r($azioni_array); echo "</pre>";
					
				$menu_array[$row_menu['MENU']] = array(
					"DES" => $row_menu['DESCRIZIONE'],
					"AZIONI" => $azioni_array
				);
			}
		}
//		echo "MENU ARRAY:<pre>"; print_r($menu_array); echo "</pre>";
		
		return $menu_array;
	}
	
	function print_menu($sheet, $menu, $col, $rec=false, $user_info=array(), $search="") {
		$menu_array = get_menu_array();
		
		static $riga = 1;
		static $colCount = 0;
		
//		echo "<font color='green'>SEARCH: $search</font><br>";
		
		if(!empty($user_info)) {
			$sheet->setCellValueByColumnAndRow(0, $riga, $user_info['USER_NAME']);
			$sheet->setCellValueByColumnAndRow(1, $riga, $user_info['NAZIONE']);
			$sheet->setCellValueByColumnAndRow(2, $riga, $user_info['AREAFUN']);
			
			// Font Blue Style
			$FontBlueStyle = array(
				'font' => array(
					'color' 	=> array('argb' => PHPExcel_Style_Color::COLOR_BLUE)
				)
			);
			
//			$sheet->getStyleByColumnAndRow(0, $riga)->applyFromArray($FontBlueStyle);
			setRangeStyle($sheet, 0, $riga, 2, $riga, $FontBlueStyle);
			
			$col++;
			$riga++;
		}
		
		if(array_key_exists($menu, $menu_array)) {
			$menu_vals = $menu_array[$menu];
			
//			echo "<font color='red'>MENU: $menu</font><br>";
//			echo "DES: ".$menu_vals['DES']."<br>";
			
			$sheet->setCellValueByColumnAndRow($col, $riga, $menu);
			$sheet->setCellValueByColumnAndRow($col+1, $riga, $menu_vals['DES']);
			$sheet->setCellValueByColumnAndRow($col+2, $riga, "M");
			
			// Font Red Style
			$FontRedStyle = array(
				'font' => array(
					'color' 	=> array('argb' => PHPExcel_Style_Color::COLOR_RED)
				)
			);
				
			// Fill Yellow
			$Filler_Yellow = array(
				'fill' 	=> array(
					'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
//					'color' 	=> array('argb' => PHPExcel_Style_Color::COLOR_YELLOW)
					'startcolor' 	=> array('argb' => 'FFFF00'),
					'endcolor'		=> array('argb' => 'FFFF00')
				)
			);
			
			if($menu==$search) {
//				echo "MENU: $menu = SEARCH: $search<br>";
				setRangeStyle($sheet, $col, $riga, $col+2, $riga, $Filler_Yellow);
			}
			
			setRangeStyle($sheet, $col, $riga, $col+2, $riga, $FontRedStyle);
			
			$riga++;
			
			if(!empty($menu_vals['AZIONI'])) {
				foreach($menu_vals['AZIONI'] as $azione => $vals) {
					if(!empty($vals)) {
//						echo "AZIONE: $azione - TIPO: ".$vals['TIPO']."<br>";
						
						if($vals['TIPO']=="M") {
							if($rec===true) {
								print_menu($sheet, $azione, $col+1, true, "", $search);
							}
							else {
								$sheet->setCellValueByColumnAndRow($col+1, $riga, $azione);
								$sheet->setCellValueByColumnAndRow($col+2, $riga, $menu_array[$azione]['DES']);
								$sheet->setCellValueByColumnAndRow($col+3, $riga, $vals['TIPO']);
								
								setRangeStyle($sheet, $col+1, $riga, $col+3, $riga, $FontRedStyle);
								
								$riga++;
							}
						}
						else {
							$sheet->setCellValueByColumnAndRow($col+1, $riga, $azione);
							$sheet->setCellValueByColumnAndRow($col+2, $riga, $vals['DES']);
							$sheet->setCellValueByColumnAndRow($col+3, $riga, $vals['TIPO']);
							
							$riga++;
						}
						
						if($azione==$search) {
//							echo "AZIONE: $azione = SEARCH: $search<br>";
							setRangeStyle($sheet, $col+1, $riga-1, $col+3, $riga-1, $Filler_Yellow);
						}
						
						if($col+3>$colCount)
							$colCount = $col+3;
					}
				}
			}
			
			return $colCount;
		}
	}
	
	function print_user_menu($sheet, $user_info, $user_menu, $search) {
		static $colCount = 0;
		
		$is_first = true;
		foreach($user_menu as $key => $menu) {
//			echo "<font color='green'>$key</font><br>";
			if($is_first===true) {
				$cols = print_menu($sheet, $menu, 0, true, $user_info, $search);
				$is_first = false;
			}
			else {
				$cols = print_menu($sheet, $menu, 1, true, "", $search);
			}
			
			if($cols>$colCount)
				$colCount = $cols;
		}
		
		return $colCount;
	}
	
	function search_action_in_menu($azione) {
		static $in_menu = array();
		
		$menu_array = get_menu_array();
		
		if(!empty($menu_array)) {
			foreach($menu_array as $menu => $vals) {
				if($azione==$menu && !in_array($menu, $in_menu)) {
//					echo "L'azione cercata $azione è essa stessa un MENU<br>";
					$in_menu[] = $menu;
					
					search_action_in_menu($menu);
				}
				else {
					foreach($vals['AZIONI'] as $azi => $azi_vals) {
						if($azione==$azi && !in_array($menu, $in_menu)) {
							$in_menu[] = $menu;
//							echo "L'azione $azione si trova NEL MENU: $menu<br>";
							
							search_action_in_menu($menu);
						}
					}
				}
			}
		}
//		echo "IN MENU:<pre>"; print_r($in_menu); echo "</pre>";	
		
		return $in_menu;
	}
	
	function setRangeStyle($sheet, $col_ini, $row_ini, $col_fin, $row_fin, $style=null, $bold=false) {
		$range_ini = PHPExcel_Cell::stringFromColumnIndex($col_ini).$row_ini;
		$range_fin = PHPExcel_Cell::stringFromColumnIndex($col_fin).$row_fin;
	
		if(!empty($style))
			$sheet->getStyle($range_ini.":".$range_fin)->applyFromArray($style);
	
		if($bold===true)
			$sheet->getStyle($range_ini.":".$range_fin)->getFont()->setBold($bold);
	
		return $sheet;
	}