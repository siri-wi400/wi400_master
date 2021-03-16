<?php

	function getTipoScheda($argomento) {
		global $db;
		
		$query = "SELECT FLD_TYPE, FLD_TYPED FROM ZFLDARGD WHERE FLD_TYPE<>'****' AND FLD_ARGO='$argomento' ORDER BY FLD_TYPE";
		
		$rs = $db->query($query);
		$type_scheda = array();
		while($row = $db->fetch_array($rs)) {
			$type_scheda[$row['FLD_TYPE']] = $row['FLD_TYPED'];
		}
		
		return $type_scheda;
	}
	
	function getCssButton($width = "auto", $topColor = "#FFFFFF", $bottomColor = "#A6A6A6", $color = "black", $border = "#A8A8A8") {
		if(!$color) {
			$color = "black";
		}
	
		$cssButton = "border: solid 1px $border;
		color: $color;
		text-shadow: none;
		width: $width;
		background-color: $topColor;
		background: -webkit-linear-gradient($topColor, $bottomColor);
		background: linear-gradient($topColor, $bottomColor);
		background: -o-linear-gradient($topColor, $bottomColor);
		background: -moz-linear-gradient($topColor, $bottomColor);";
	
		return $cssButton;
	}