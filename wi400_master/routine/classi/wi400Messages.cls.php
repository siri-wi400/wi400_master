<?php
class wi400Messages {

	private $messages;
    private $messageTypes;
    private $severity;
    
    public function __construct(){
    	
    	if (isset($_SESSION["MESSAGES_CONTEXT"])){
    		$this->messages = $_SESSION["MESSAGES_CONTEXT"];
    	}else{
    		$this->messages = array();
    		$_SESSION["MESSAGES_CONTEXT"] = $this->messages;
    	}
    	
    	$this->messageTypes = array(
    						"INFO" =>  0,
    						"SUCCESS" => 1,
    						"ALERT" => 2,
    						"ERROR" => 3
    					);

    }
    
    public function getMessageType($mt){
    	if (isset($this->messageTypes[$mt])){
    		return $this->messageTypes[$mt];
    	}else{
    		return 0;
    	}
    }
    
    public function addMessage($messageType, $messageLabel, $fieldId = "", $saveSession = true){
    	
    	
    	if ($messageType == "LOG"){
    		$errorLogString = $messageLabel;
    	
    		if (isset($_REQUEST["t"])){
				$errorLogString .= " [ACTION:".$_REQUEST["t"]."]";
			}
			if (isset($_REQUEST["f"])){
				$errorLogString .= " [FORM:".$_REQUEST["f"]."]";
				
			}
			if (isset($_SESSION['user'])){
				$errorLogString .= " [USER:".$_SESSION['user']."]";
			}
			
			$errorLogString .= "\r\n";
			
			foreach(debug_backtrace() as $k=>$v){ 
		        if($v['function'] == "include" || $v['function'] == "include_once" || $v['function'] == "require_once" || $v['function'] == "require"){ 
		            $errorLogString .= "#".$k." ".$v['function']."(".$v['args'][0].") called at [".$v['file'].":".$v['line']."] \r\n"; 
		        }else{ 
		            $errorLogString .= "#".$k." ".$v['function']."() called at [".$v['file'].":".$v['line']." \r\n"; 
		        } 
		    } 
						
			error_log($errorLogString);
    	}else{
    	
	    	$newMessage = array($messageType,$messageLabel,$fieldId);
	    	if (trim($fieldId) == ""){
	    		$this->messages[] = $newMessage;
	    	}else{
	    		$this->messages[$fieldId] =  $newMessage;
	    	}
	    	if ($saveSession){
	    		$_SESSION["MESSAGES_CONTEXT"][] = $newMessage;
	    	}
    	}
    }
    
    // $errorsContext comunica che tipo di errori valutare per calcolare la
    // gravità complessiva della pagina. Default "ALL_ERROR" valuta tutti gli errori.
    public function getSeverity($fieldsError = "ALL_ERROR"){
    	$this->severity = 0;
    	foreach ($this->messages as $messageObj){
    		
    		if ($fieldsError == "ALL_ERROR" || trim($messageObj[2]) == ""){
	    		foreach ($this->messageTypes as $messageType => $messageSeverity){
	    			if ($messageObj[0] == $messageType){
	    				if ($this->severity < $messageSeverity){
	    					$this->severity = $messageSeverity;
	    				}
	    				break;
	    			}
	    		}
    		}
    	}
    	
		$messageValues = array_keys($this->messageTypes);
    	return $messageValues[$this->severity];
    	
    }

    
    public function getMessages($fieldsError = "ALL_ERROR"){
    	if ($fieldsError == "ALL_ERROR"){
    		return $this->messages;
    	}else{
    		// Not field messages
    		$messagesList = array();
    		foreach ($this->messages as $messageObj){
    			if (trim($messageObj[2]) == ""){
    				$messagesList[] = $messageObj;
    			}
    		}
    		return $messagesList;
    	}
    }
    
    
    public function removeMessages(){
    	unset($_SESSION['MESSAGES_CONTEXT']);
    }
    
}
?>