<?php

/**
 * @name wi400InputFile 
 * @desc Classe per la creazione del campo di input di un file
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 30/10/2008
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

require_once $routine_path.'/classi/wi400InputText.cls.php';

class wi400InputFile extends wi400InputText {
   
	/**
	 * Costruttore della classe
	 *
	 * @param string $id	: ID del campo da creare
	 */
    public function __construct($id){
    	$this->setId($id);
    	$this->setType("FILE");
    }

}

?>