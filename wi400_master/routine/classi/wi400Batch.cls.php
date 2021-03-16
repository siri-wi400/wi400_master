<?php

/**
 * @name wi400Batch 
 * @desc Classe per il lancio delle azioni batch
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 19/02/2010
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400Batch {

	private $id="";
    private $name;
    private $parameters=array();
    private $user;
    private $jobd;
    private $url;
	private $batchIdPath;
    private $action;
    private $form;

    /**
	 * Costruttore della classe
	 *
	 * @param string $user	: ID dell'utente
	 * @param string $jobd	: codice del lavoro
	 * @param string $name	: nome del processo batch da chiamare
	 * @param string $url	: 
	 */
    public function __construct($user="*JOBD" , $jobd="*JOBD" , $name="BATCHPHP", $url=""){
    	global $appBase, $settings;
    	
    	$this->user = $user;
    	$this->jobd = $jobd;
    	$this->name = $name;
    	
    	$this->batchIdPath = $settings['data_path']."batch/ID/";

    	if ($url=="") {
    		$url = "http://".$_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT'].$appBase;
    	}
    	$url .="batch.php";
    	$this->url  = $url;
    }
    
    /**
     * Impostazione del nome dell'azione e del form da eseguire
     *
     * @param strin $action	: il nome dell'azione
     * @param strin $form	: il nome del form
     */
    public function setAction($action, $form=""){
    	$this->action = $action;
    	$this->form   = $form;
    }
    
    /**
     * Impostazione dell'ID del processo batch
     *
     * @param string $id
     */
    public function setId($id){
    	$this->id = $id;
    }
    
    /**
     * Recupero dell'ID del processo batch
     *
     * @return string
     */
    public function getId(){
    	global $db, $settings, $connzend;
    	if ($this->id =="") {
	    	$numero = getSysSequence("BATCHPHP");
	    	$this->id = "B_".$numero;
	    	wi400_mkdir($this->batchIdPath.$this->id);
    	}
    	return $this->id;
    }
    
    public function setname($name){
    	$this->name = $name;
    }
	public function getname(){
    	return $this->name;
    }
    public function addParameter($parm, $value) {
    	$this->parameters[$parm]=$value;
    }
    public function setJobd($jobd){
    	$this->jobd = $jobd;
    }
    public function getJobd(){
    	return $this->jobd;
    }
    public function duplicateFileBatch($fileSource, $key, $type) {
    	static $i;
    	if (!isset($i)) $i=0;
    	$file = basename($fileSource);
    	$target = $this->batchIdPath.$this->id."/".$file;
    	copy($fileSource, $target);
    	$this->parameters['OBJECT'][]=array($key, $type, $target);
    	$i++;
    }
    /**
     * Chiamata del processo batch
     *
     * @param unknown_type $connzend	: Connessione a Zend
     */
    public function call($connzend) {
        global $db, $appBase, $messageContext, $settings;
        
        // Recupero un ID progressivo univoco
        //$numero = getSysSequence("BATCHPHP");
        //$this->id = "B_".$numero;
        $this->id = $this->getId();
        // Creazione dell'XML e del file più la cartella dei contenuti
        //wi400_mkdir($this->batchIdPath.$this->id, 777, true);
        wi400_mkdir($this->batchIdPath.$this->id);
        $file = $this->batchIdPath.$this->id."/".$this->id.".txt";
    	$dom = new DomDocument('1.0', 'UTF-8');
		$parameters = $dom->appendChild($dom->createElement('parametri'));
		$this->parameters['action']=$this->action;
		$this->parameters['form']=$this->form;
		$this->parameters['id']=$this->id;
		$this->parameters['file']=$file;
		$this->parameters['jobd']=$this->jobd;
		$this->parameters['name']=$this->name;
		$this->parameters['user']=$this->user;
		$this->parameters['base_path']=$this->url;
		$this->parameters['appBase']=$appBase;		
		$this->parameters['lista_librerie']=implode(";",$_SESSION["lista_librerie"]);
		if (isset($this->parameters['OBJECT'])) {
			$this->parameters['OBJECT']=base64_encode(serialize($this->parameters['OBJECT']));
		}
		// Attacco tutti gli altri parametri
		foreach ($this->parameters as $key=>$value) {
			$parameter = $parameters->appendChild($dom->createElement('parametro'));
	        $field_name = $dom->createAttribute('id'); 
	        $parameter->appendChild($field_name);        
	        $name = $dom->createTextNode($key);
	        $field_name->appendChild($name);
	        $field_name = $dom->createAttribute('value'); 
	        $parameter->appendChild($field_name);        
	        $name = $dom->createTextNode($value);
	        $field_name->appendChild($name);
		}
	    $dom->formatOutput = true;
	    $returnValue = $dom->saveXML();
	    //$file = $file;
		$handle = fopen($file, "w+");
		fwrite($handle, $returnValue);
		fclose($handle);	
		if ($settings['platform']=="WINDOWS" && !isset($settings['xmlservice'])) {
			$file2 = $file.".bch";
			$handle = fopen($file2, "w+");
			fwrite($handle, "POSTXML=".$returnValue);
			fclose($handle);
			$ip = getServerAddress(True);
			$script = "c:\\trilog\\curl\\curl.exe -X POST -d @$file2 http://$ip/$appBase/batch.php -o $file.out";
			$shell = new COM("WScript.Shell");
			$shell->run($script, 0, false);
			$flag = "0";
		} else if ($settings['platform']=="LINUX" && !isset($settings['xmlservice'])) {
			$file2 = $file.".bch";
			$handle = fopen($file2, "w+");
			fwrite($handle, "POSTXML=".$returnValue);
			fclose($handle);
			$ip = getServerAddress(True);
			$script = "curl.exe -X POST -d @$file2 http://$ip/$appBase/batch.php -o $file.out /dev/null 2>/dev/null &";
			system($script);
			$flag = "0";
		} else {
	    	$ZBATCHELA = new wi400Routine('ZBATCHELA', $connzend );
			$ZBATCHELA->load_description ();
			$ZBATCHELA->prepare ();
			$ZBATCHELA->set('ID', $file );
			$ZBATCHELA->set('BASE_PATH', $this->url);
			$ZBATCHELA->set('JOBD', $this->jobd );
			$ZBATCHELA->set('NAME', $this->name );
			$ZBATCHELA->set('USER', $this->user );
			$ZBATCHELA->call ();
			$flag = $ZBATCHELA->get('FLAG');
		}
		
		$name_job = "";
		if(isset($this->parameters['name_job']))
			$name_job = $this->parameters['name_job'];
			
		$des_job = "";
		if(isset($this->parameters['des_job']))
			$des_job = $this->parameters['des_job'];
		
        // inserimento del record di elaborazione
	       if (!isset($this->parameters['nodb'])) {
		       $sql = "INSERT INTO FBATCHJB (ID, AZIONE, SESSIONE, UTENTE, TIMESUB, TIMECOMPLETE, TIMESTART, STATO, NOME_LAVORO, DES_LAVORO)
		        VALUES('$this->id', '$this->action', '".session_id()."', '".$_SESSION['user']."' ,'".$db->getTimestamp()."','".$db->getTimestamp("*INZ")."','".$db->getTimestamp("*INZ")."', '1', '".$name_job."', '".$des_job."')";
		        $db->query($sql);
	       }
        // Emetto il messaggio
		if ($flag=='0') {
			$messageContext->addMessage("SUCCESS", _t("W400009", array( $this->id)));	
		} else {
			$messageContext->addMessage("ERROR", _t("W400010", array( $flag , $this->decode_error($flag))));	
		}
    }
    
    /**
	 * Decodifica dell'errore
	 * 
	 * @param string $error	: il codice di errore
	 * 
	 * @return string Ritorna il messaggio di errore associato al codice di errore
	 */
    public function decode_error($error) {
	    switch ($error) {
		case '0':
			   $message="";
			   break;
		case '1':
			   $message="JOBD NON TROVATA";
			   break;
		case '2':
			   $message="L'UTENTE NON ESISTE";
			   break;			   
	    }
	    return $message;
    }
    	   
}
?>