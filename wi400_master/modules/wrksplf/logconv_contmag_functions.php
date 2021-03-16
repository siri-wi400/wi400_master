<?php

	function wi400_format_STRING_TO_DOUBLE_2($value){
		$dec = substr($value, -2);
		$int = substr($value, 0, -2);
		$val = $int.".".$dec;
		$val = (double) $val;
	
		$res = wi400_format_DOUBLE_2($val);
	
		return $res;
	}