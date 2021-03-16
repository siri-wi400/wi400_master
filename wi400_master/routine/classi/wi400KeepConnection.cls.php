<?php

//declare(ticks=2); va fatto prima della creazione della classe

class wi400KeepConnection {
	private $ticks="60";
	private $message="<!--Commento-->";
	private $showCont = false;
	private $cont = 0;
	
	public function __construct($ticks="60", $message="", $showCont = false) {
		if ($message!="") {
			$this->message = $message;
		}
		$this->showCont = $showCont;
		
		//eval('declare(ticks='.$ticks.');');
	}
	
	public function start() {
		// Turn off output buffering
		ini_set('output_buffering', 'off');
		// Turn off PHP output compression
		ini_set('zlib.output_compression', false);
		//Flush (send) the output buffer and turn off output buffering
		//ob_end_flush();
		while (@ob_end_flush());
		//declare(ticks=(string)$this->ticks);
		// Implicitly flush the buffer(s)
		ini_set('implicit_flush', true);
		ob_implicit_flush(true);
		$do = register_tick_function(array($this, 'tick'), true);
	}
	
	public function stop() {
		unregister_tick_function(array($this, 'tick'));
	}
	
	public function tick() {
		//prevent apache from buffering it for deflate/gzip
		@header("Content-type: text/plain");
		@header('Cache-Control: no-cache'); // recommended to prevent caching of event data.
		$message =  $this->message;
		if($this->showCont) {
			$message .= ": ".$this->cont;
			$this->cont++;
		}
		echo $message;
		@ob_flush();
		@flush();
	}
}