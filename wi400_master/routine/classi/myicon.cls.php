<?php

class myicon {
	
    private $iconam;
    private $icofil;
    private $icobre; 
    private $db;
        
	public function __construct($db) {
	    $this->iconam = array();
	    $this->icofil = array();
	    $this->icobre = array(); 
	    $this->db = $db;
	    $this->totico = 0;
	}
    
	public function carica_icone($piatt) {
		$query= "SELECT * FROM ICONE WHERE DEPOSITO='$piatt'";
		$result = $this->db->query($query);
		while ($campi = $this->db->fetch_array($result)) {
			$this->totico=$this->totico+1;
			$this->icofil[]=$campi['ICONFILE'];
			$this->iconam[]=$campi['NOME'];
			$this->icobre[]=$campi['BREVE'];
  	  	}
	}

	public function retrive_icon($des, $piatt='****') {
		for ( $i = 0; $i < $this->totico; $i++)  {
			if (strpos($des, trim(substr($this->iconam[$i],0,strlen($this->iconam[$i])-1)))!==False)
				return $this->icofil[$i];
   		}
   		
   		for ( $i = 0; $i < $this->totico; $i++) {
  			if (strpos($des, trim($this->icobre[$i]))!==False)
  				return $this->icofil[$i];
  		} 

  		//return "homeInt_dec.gif";
		if ($piatt == '5004')
  			return "default_frutta.gif";
		else
			return "default_item.png";
	}
  
}

?>