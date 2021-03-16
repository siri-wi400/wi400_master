<?php

/**
 * @name wi400Sms
 * @desc Classe generica Invio SMS
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 13/09/2016
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400Sms {

	private $address;
	private $priority;
	private $sender;
	private $encoding;
	private $body;
	private $key;
	private $user;
	private $password;
	private $classe;
	private $keyid;
	private $senddate;
	private $log = True;
	private $valperiod;
	private $url;
	private $port;

    /**
	 * @return the $valperiod
	 */
	public function getValperiod() {
		return $this->valperiod;
	}

	/**
	 * @return the $url
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @return the $port
	 */
	public function getPort() {
		return $this->port;
	}

	/**
	 * @param field_type $valperiod
	 */
	public function setValperiod($valperiod) {
		$this->valperiod = $valperiod;
	}

	/**
	 * @param field_type $url
	 */
	public function setUrl($url) {
		$this->url = $url;
	}

	/**
	 * @param field_type $port
	 */
	public function setPort($port) {
		$this->port = $port;
	}

	/**
	 * @return the $address
	 */
	public function getAddress() {
		return $this->address;
	}

	/**
	 * @return the $priority
	 */
	public function getPriority() {
		return $this->priority;
	}

	/**
	 * @return the $sender
	 */
	public function getSender() {
		return $this->sender;
	}

	/**
	 * @return the $codifica
	 */
	public function getEncoding() {
		return $this->enconding;
	}

	/**
	 * @return the $body
	 */
	public function getBody() {
		return $this->body;
	}

	/**
	 * @return the $key
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * @return the $user
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @return the $password
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * @return the $classe
	 */
	public function getClasse() {
		return $this->classe;
	}

	/**
	 * @return the $keyid
	 */
	public function getKeyid() {
		return $this->keyid;
	}

	/**
	 * @return the $senddate
	 */
	public function getSenddate() {
		return $this->senddate;
	}

	/**
	 * @param field_type $address
	 */
	public function setAddress($address) {
		$this->address = $address;
	}

	/**
	 * @param field_type $priority
	 */
	public function setPriority($priority) {
		$this->priority = $priority;
	}

	/**
	 * @param field_type $sender
	 */
	public function setSender($sender) {
		$this->sender = $sender;
	}

	/**
	 * @param field_type $codifica
	 */
	public function setEncoding($encoding) {
		$this->encoding = $enconding;
	}

	/**
	 * @param field_type $body
	 */
	public function setBody($body) {
		$this->body = $body;
	}

	/**
	 * @param field_type $key
	 */
	public function setKey($key) {
		$this->key = $key;
	}

	/**
	 * @param field_type $user
	 */
	public function setUser($user) {
		$this->user = $user;
	}

	/**
	 * @param field_type $password
	 */
	public function setPassword($password) {
		$this->password = $password;
	}

	/**
	 * @param field_type $classe
	 */
	public function setClasse($classe) {
		$this->classe = $classe;
	}

	/**
	 * @param field_type $keyid
	 */
	public function setKeyid($keyid) {
		$this->keyid = $keyid;
	}

	/**
	 * @param field_type $senddate
	 */
	public function setSenddate($senddate) {
		$this->senddate = $senddate;
	}

	public function __construct(){
		
    }
    public function writeLog($msg) {
    	$file_path = wi400File::getLogFile("sms", "sms_log_".date("Ymd")."txt");
    	$handle = fopen($file_path, "a+");
    	fwrite($handle, $msg."\r\n");
    	fclose($handle);
    }
}
?>