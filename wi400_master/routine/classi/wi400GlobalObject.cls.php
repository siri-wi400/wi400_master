<?php
/**
 * @name wi400GlobalObject
 * @desc Classe per il salvataggio di oggetti wi400Detail
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 22/05/2018
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */
class wi400GlobalObject {
	private $object = array();

	public function __construct() {
		
	}
	
	/**
	 * Aggiungo un oggetto
	 * 
	 * @param string $idDetail
	 * @param object $obj
	 */
	public function addObject($id, $obj, $clone=False) {
		if ($clone == False) {
			$this->object[$id] = $obj;
		} else {
			$this->object[$id] = clone $obj;
		}
	}
	
	/**
	 * Ritorna un oggetto
	 * 
	 * @param string $id
	 * @return object
	 */
	public function getObject($id) {
		if(isset($this->object[$id])) {
			return $this->object[$id];
		}else {
			return null;
		}
	}
	
	
 }