<?php
	function getDescrizioneEnte($ente) {
		$decodeType ="ente";
		require_once p13nPackage($decodeType);
	
		$decodeClass = new $decodeType();
		$decodeClass->setFieldValue($ente);
	
		return $decodeClass->decode();
	
	}
	
	function getDescrizioneCliente($cliente) {
		$decodeType = "cliente";
		require_once p13nPackage ( $decodeType );
		$decodeClass = new $decodeType ();
		$decodeClass->setFieldValue ( $cliente );
		return $decodeClass->decode ();
	}