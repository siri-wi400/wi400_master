<?php
class wi400JobQueue {
	private $jobQueue="";
	private $tabella ="ZJOBQUEE";
	private $cache;
	private $conf;
	// Costruttore della classe
	public function __construct($jobQueue, $load=True) {
		// Carico tutti i dati in cache dal file serializzato
		if ($load==True) {
			$this->loadCache();
		}
		$this->jobQueue=$jobQueue;
		$this->conf = $this->cache[$jobQueue];
	}
	/*
	 * Tutte le operazioni sulla tabella devono passare di qu
	 * per fare il reset della cache
	 */
	public function saveConfigurazione($dati) {
		deleteCache();
		loadCache();
	}
	// Lettura Configurazione
	public function getConfigurazione() {
		return $this->cache[$this->jobQueue];
	}
	// Lettura elemento in JOBQ
	public function getElemento($delete=True) {
		// Verifico il tipo di JOBQ
		switch ($this->conf['TIPO']) {
			case "DB":
				// Devo leggere il DB
				break;
			case "ASJOBQ":
				// Leggo la coda con l'API
				break;
			case "REDIS":
				// Leggo da REDIS
				break;
		}
		
	}
	// Scrittura elemento in JOBQ
	public function setElemento($dati) {
		switch ($this->conf['TIPO']) {
			case "DB":
				// Devo scrivere su DB
				break;
			case "ASJOBQ":
				// Scrivo la coda con l'API
				break;
			case "REDIS":
				// Scrivo su REDIS
				break;
		}
	}
	// Pulizia JOBQ
	public function cleartJobq() {
		switch ($this->conf['TIPO']) {
			case "DB":
				// Devo scrivere su DB
				break;
			case "ASJOBQ":
				// Scrivo la coda con l'API
				break;
			case "REDIS":
				// Scrivo su REDIS
				break;
		}
	}
	// Reperimento numero di elementi
	public function getCount() {
		switch ($this->conf['TIPO']) {
			case "DB":
				// Devo scrivere su DB
				break;
			case "ASJOBQ":
				// Scrivo la coda con l'API
				break;
			case "REDIS":
				// Scrivo su REDIS
				break;
		}
	}
	/**
	 * Legge la cache
	 * @param boolean $reset
	 */
	private function loadCache($reset=False) {
		global $db, $settings;
			if ($this->cache==Null || $reset==True) {
				$filename = wi400File::getCommonFile("serialize", "JOBQUEUE_CACHE.dat");
				$this->cache=fileSerialized($filename);
				if ($this->cache== Null) {
					$sql = "select * from $this->tabella";
					$this->cache = make_serialized_file($sql, $filename, array("ID"), True);
				}
			}
	}
	/**
	 * Cancellazione della cache
	 */
	public function deleteCache() {
		$filename = wi400File::getCommonFile("serialize", "JOBQUEUE.dat");
		
		if(file_exists($filename)) {
			unlink($filename);
		}
	}
}