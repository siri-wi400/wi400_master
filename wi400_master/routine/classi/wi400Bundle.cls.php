<?php

class wi400Bundle {

	private $type;
	private $files;
	
	public function __construct($type){
    	$this->type = $type;	
    	$this->files = array();    
    }
    
    public function add($filePath){
    	$this->files[] = $filePath;
    }
    
	public function dispose(){
    	global $appBase;
		switch ($this->type) {
		case 'javascript':
			//foreach ($this->files as $key=>$value) {
				//$file = $appBase.substr($value, 1);
				//echo '<script src="'.$file.'"></script>';
			//}
			echo htmlspecialchars_decode('<script src="'.$appBase.'bundle/combine.php?type=javascript&amp;files='.implode(",", $this->files).'" type="text/javascript"></script>');
			break;
		case 'css':

			echo '<link rel="stylesheet" type="text/css" href="'.$appBase.'bundle/combine.php?appBase='.$appBase.'&amp;type=css&amp;files='.implode(",", $this->files).'" />';
			break;
		default:
			exit;
		};
    }
}