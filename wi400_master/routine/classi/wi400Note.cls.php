<?php
/**
 * @name wi400Note
 * @desc Classe per la gestione delle Note
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author Valeria Porrazzo
 * @version 1.00 15/01/2019
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400Note {
	private $ID = "";
	private $tipo = "";
	private $righe_nota = array();
	private $nota = "";
	
	private $user_ins;
	private $time_ins;
	private $user_mod;
	private $time_mod;
	
	private $tabella_note = "TRI_NOTE";
	
	private $idUser;
	private $timeStamp;
	
	private $max_len = 200;
	
	/**
	 * Costruttore della classe
	 *
	 * @param string $ID		: Codice identificativo della nota
	 * @param string $tipo		: Tipo della nota
	 */
	public function __construct($ID="") {
		$this->ID = $ID;
		
		$this->idUser = $_SESSION['user'];
		$this->timeStamp = getDb2Timestamp();
	}
	
	public function set_ID($ID) {
		$this->ID;
	}
	
	public function get_ID() {
		return $this->ID;
	}
	
	public function set_tipo($tipo) {
		$this->tipo = $tipo;
	}
	
	public function get_tipo() {
		return $this->tipo;
	}
	
	public function set_righe_nota($righe_nota) {
		$this->righe_nota = $righe_nota;
	}
	
	public function get_righe_nota() {
		return $this->righe_nota;
	}
	
	public function set_nota($nota) {
		$this->nota = $nota;
	}
	
	public function get_nota() {
		return $this->nota;
	}
	
	public function set_tabella_note($tabella) {
		$this->tabella_note = $tabella;
	}
	
	public function get_tabella_note() {
		return $this->tabella_note;
	}
	
	public function set_max_len($len) {
		$this->max_len = $len;
	}
	
	public function get_max_len() {
		return $this->max_len;
	}
	
	private function get_new_id_nota() {
		$ID = getSequence('WI_NOTE');
//		$ID = getSysSequence("WI_NOTE");
		echo "NEW ID: $ID<br>";
		
		return $ID;
	}
	
	public function prepare_nota_parts($newLine="\r\n") {
		global $db;
		
		static $stmt_rn;
		
		if(empty($this->ID)) {
			return false;
		}
		
		if(!isset($stmt_rn)) {
			$sql_rn = "select * from ".$this->tabella_note." where ID_NOTA=? order by ID_RIGA";
			
			$stmt_rn = $db->prepareStatement($sql_rn, 0, false);
		}
		
		$res_rn = $db->execute($stmt_rn, array($this->ID));
		
		$has_nota = false;
		
		while($row_rn = $db->fetch_array($stmt_rn)) {
			$has_nota = true;
			
			if($this->tipo=="") {
				$this->tipo = $row_rn['ARGOMENTO'];
				
				$this->user_ins = $row_rn['USRINS'];
				$this->time_ins = $row_rn['TMSINS'];
				$this->user_mod = $row_rn['USRMOD'];
				$this->time_mod = $row_rn['TMSMOD'];
			}
			
			$this->righe_nota[$row_rn['ID_RIGA']] = $row_rn['NOTATXT'];
		}
//		echo "RIGHE NOTE:<pre>"; print_r($this->righe_nota); echo "</pre>";
		
		if(!empty($this->righe_nota)) {
//			echo "NEW LINE: "; var_dump($newLine); echo "<br>";
			
			$this->nota = implode($this->righe_nota, $newLine);
		}
		
//		echo "NOTA: ".$this->nota."<br>";
		
		return $has_nota;
	}
	
	public function read_nota($newLine="\r\n") {
		$has_nota = $this->prepare_nota_parts($newLine);
		
		if($has_nota===false)
			return false;
		
		$nota_parts = array(
			"ID_NOTA" => $this->ID,
			"ARGOMENTO" => $this->tipo,
//			"RIGHE" => $this->righe_nota,
			"NOTA" => $this->nota,
			"USER_INS" => $this->user_ins,
			"TIME_INS" => $this->time_ins,
			"USER_MOD" => $this->user_mod,
			"TIME_MOD" => $this->time_mod
		);
		
		return $nota_parts;
	}	
	
	public function write_nota($tipo, $nota, $wrap=null, $show_msg=false) {
		global $db;
		
		if(!empty($this->ID)) {
			// Recupero utente e data di inserimento
			$has_nota = $this->prepare_nota_parts();
			
			if($has_nota===false)
				return false;
			
			$user_ins = $this->user_ins;
			$time_ins = $this->time_ins;
			
			// Delete della nota già esistente
			$res_del = $this->delete_nota();
			
			if($res_del===false)
				return false;
		}
		else {
			$this->ID = $this->get_new_id_nota();
			echo "NEW ID: $this->ID<br>";
			
			$user_ins = $this->idUser;
			$time_ins = $this->timeStamp;
		}
//		echo "ID: $this->ID<br>";

		$user_mod = $this->idUser;
		$time_mod = $this->timeStamp;
		
		$fieldsValue = getDs($this->tabella_note);
		
		$fieldsValue['ID_NOTA'] = $this->ID;
		$fieldsValue['ARGOMENTO'] = $tipo;
		$fieldsValue['USRMOD'] = $user_mod;
		$fieldsValue['TMSMOD'] = $time_mod;
		$fieldsValue['USRINS'] = $user_ins;
		$fieldsValue['TMSINS'] = $time_ins;
		
		echo "NOTA: "; var_dump($nota); echo "<br>";
		
		$nota_array = array();
		
		echo "WRAP: $wrap<br>";
		
		if(!isset($wrap) || empty($wrap)) {
			$nota_parts = explode("\r\n", $nota);
			
			foreach($nota_parts as $v) {
				$np = str_split($v, $this->max_len);
				
				$nota_array = array_merge($nota_array, $np);
			}
		}
		else {
			if(is_numeric($wrap)) {
				$nota_array = str_split($nota, $wrap);
			}
			else {
				$nota_array = explode($nota, $wrap);
			}
		}
		
		echo "NOTA ARRAY:<pre>"; print_r($nota_array); echo "</pre>";
//die("HERE");		
		$num_riga = 0;
		
		if(!empty($nota_array)) {
			foreach($nota_array as $riga_nota) {
				$num_riga++;
				echo "NUM RIGA: $num_riga<br>";
				
				echo "RIGA NOTA: "; var_dump($riga_nota); echo "<br>";
				
				$fieldsValue['ID_RIGA'] = $num_riga;
				$fieldsValue['NOTATXT'] = $riga_nota;
				
				echo "CAMPI:<pre>"; print_r($fieldsValue); echo "</pre>";
//continue;				
				$res_ins = $this->insert_nota($fieldsValue);
				
				if($res_ins===false)
					return false;
			}	
		}
//die("HERE");		
		return $this->ID;
	}
	
	public function delete_nota($show_msg=false) {
		global $db;
		global $messageContext;
		
		static $stmt_del;
		
		echo "<font color='pink'>DELETE</font><br>";
		
		if(empty($this->ID))
			return false;
		
		// Delete della nota già esistente
		if(!isset($stmt_del)) {
			$keyDel = array("ID_NOTA");
			$stmt_del = $db->prepare("DELETE", $this->tabella_note, $keyDel, null);
		}
		
		$res_del = $db->execute($stmt_del, array($this->ID));
			
		if(!$res_del) {
			if($show_msg===true)
				$messageContext->addMessage("ERROR","Errore durante la cancellazione della nota ".$this->ID);
		
			return false;
		}
		
		return true;
	}
	
	public function insert_nota($campi, $show_msg=false) {
		global $db;
		global $messageContext;
		
		static $stmt_ins;
		
		if(empty($this->ID))
			return false;
		
		if(!isset($stmt_ins)) {
			$fieldsValue = getDs($this->tabella_note);
			
			$stmt_ins = $db->prepare("INSERT", $this->tabella_note, null, array_keys($fieldsValue));
		}
		
		$res_ins = $db->execute($stmt_ins, $campi);
		
		if(!$res_ins) {
			if($show_msg===true)
				$messageContext->addMessage("ERROR","Errore durante il salvataggio della nota ".$this->ID);
		
			return false;
		}
		else {
			if($show_msg===true)
				$messageContext->addMessage("SUCCESS","Nota ".$this->ID." salvata con successo");
		}
		
		return true;
	}
	
	public function nota_detail($idDetail, $ID, $tipo, $nota, $azione_save, $gateway="", $show_tipo=true, $readonly_tipo=true, $show_id=true, $readonly_nota=false) {
//		echo "ID DETAIL: $idDetail<br>";
//		echo "ID: $ID - TIPO: $tipo - NOTA: $nota<br>";
//		echo "AZIONE SAVE: $azione_save - GATEWAY: $gateway<br>";
//		echo "SHOW TIPO: "; var_dump($show_tipo); echo "<br>";
//		echo "READONLY TIPO: "; var_dump($readonly_tipo); echo "<br>";
//		echo "SHOW ID: "; var_dump($show_id); echo "<br>";
		
		$actionDetail = new wi400Detail($idDetail, true);
		
		// ID
		if($ID!="" && $show_id===true) {
			$myField = new wi400InputText('ID_NOTA');
			$myField->setLabel('ID');
			$myField->addValidation('required');
			$myField->setReadonly(true);
			$myField->setSize(10);
			$myField->setMaxLength(10);
			$myField->setValue($ID);
			$actionDetail->addField($myField);
		}
		
		if($show_tipo===true) {
			$myField = new wi400InputText('ARGOMENTO');
			$myField->setLabel('Argomento');
			$myField->addValidation('required');
			$myField->setSize(10);
			$myField->setMaxLength(10);
			$myField->setReadonly($readonly_tipo);
			$myField->setValue($tipo);
			$actionDetail->addField($myField);
		}
		
		// Contenuto
		$myField = new wi400InputTextArea('NOTATXT');
		$myField->setLabel("Nota");
		$myField->setSize(180);
		$myField->setRows(15);
		$myField->setValue($nota);
		$myField->setReadonly($readonly_nota);
		$actionDetail->addField($myField);
		
		// Salva
		if($azione_save!="") {
			$myButton = new wi400InputButton('SAVE_BUTTON');
			$myButton->setLabel("Salva");
			$myButton->setAction($azione_save);
			if($ID!="")
				$myButton->setForm("UPDT_NOTA");
			else
				$myButton->setForm("INS_NOTA");
			$myButton->setGateway($gateway);
			$myButton->setConfirmMessage("Salvare?");
			$myButton->setValidation(true);
			$actionDetail->addButton($myButton);
		}
/*		
		$fieldObj = new wi400InputSwitch("SHOW_ID");
		$fieldObj->setChecked(true);
//		$fieldObj->setValue(1);
		wi400Detail::setDetailField($idDetail, $fieldObj);
*/		
		$actionDetail->dispose();
		
		return $actionDetail;
	}
}