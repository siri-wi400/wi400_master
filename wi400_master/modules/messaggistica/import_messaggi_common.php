<?php

	function getDesDecode($valore, $decodeType) {
		require_once p13nPackage($decodeType);
	
		$decodeClass = new $decodeType();
		$decodeClass->setFieldValue($valore);
		
		$des = $decodeClass->decode();
//		echo "TIPO: $decodeType - VAL: $valore - DES: "; var_dump($des); echo "<br>";
	
		return $des;
	}
	
	function getDescrizioneUser($valore) {
		$decodeType = "common";
		
		require_once p13nPackage($decodeType);
		
		$decodeParameters = array(
			'COLUMN' => 'EMAIL',
			'TABLE_NAME' => 'SIR_USERS',
			'KEY_FIELD_NAME' => 'USER_NAME',
			'SPECIAL_VALUE' => array('*ALL' => "Tutti gli utenti"),
		);
		
		$decodeClass = new $decodeType();
		$decodeClass->setFieldValue($valore);
		$decodeClass->setDecodeParameters($decodeParameters);
	
		$des = $decodeClass->decode();
//		echo "TIPO: $decodeType - VAL: $valore - DES: "; var_dump($des); echo "<br>";
	
		return $des;
	}