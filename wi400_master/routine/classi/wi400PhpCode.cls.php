<?php

/**
 * @name wi400PhpCode 
 * @desc Classe per la visualizzazione del codice php di un file
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 13/09/2010
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400PhpCode {

    private $files;
   
     /**
     * Costruttore della classe
     *
     * @param string $files	: ID del campo di inserimento di testo da creare
     * @param unknown_type $result
     */
    public function __construct($files = array()){
    	$this->files = $files;
    }

    public function addFile($file){
    	$this->files[] = $file;
    }
    
    public function getHtml($hidePhpCode = true){
    	
    	$fileCounter = 0;
    	$html = "";
    	foreach ($this->files as $file){
    		
    		$filename = $file;
    		if( preg_match('/^\/.*\/([^\/]+)$/', $filename, $matches )) {  
				$filename = $matches[1];
			}
			
			$phpCode = highlight_file ( $file, true);
			if ($hidePhpCode){
	    		$cutPos = strripos ($phpCode, "wi400PhpCode");
				if ($cutPos !== false){
					$phpCode = substr($phpCode, 0, $cutPos);
					$cutPos = strripos($phpCode, "$");
					if ($cutPos !== false){
						$phpCode = substr($phpCode, 0, $cutPos);
					}
				}
			}
            $html.='
			<br>
			<div style="width:100%">
				<div onClick="openClose(\'phpCode_'.$fileCounter.'\')">
					<div class="detail-header-cell"><img border="0" src="themes/common/images/php.png" alt="Visualizza Sorgente" />'.basename($filename).'
					</div>
				</div>
			</div>
			<div id="phpCode_'.$fileCounter.'" class="detail-header-cell" style="display:none;">'.$phpCode.'</code></div>';
    		$fileCounter++;
    	}
    	return $html;
    }
    public function dispose($hidePhpcode=True) {
    	echo $this->getHtml();
    }
    /*public function dispose($hidePhpCode = true){
      
    $fileCounter = 0;
    echo "<br>";
    foreach ($this->files as $file){
    
    $filename = $file;
    if( preg_match('/^\/.*\/([^\/]+)$/', $filename, $matches )) {
    $filename = $matches[1];
    }
    	
    $phpCode = highlight_file ( $file, true);
    if ($hidePhpCode){
    $cutPos = strripos ($phpCode, "wi400PhpCode");
    if ($cutPos !== false){
    $phpCode = substr($phpCode, 0, $cutPos);
    $cutPos = strripos($phpCode, "$");
    if ($cutPos !== false){
    $phpCode = substr($phpCode, 0, $cutPos);
    }
    }
    }
    ?>
    <br>
    <div style="width:100%">
    <div onClick="openClose('phpCode_<?= $fileCounter ?>')">
    <div class="detail-header-cell"><img border="0" src="themes/common/images/php.png" alt="Visualizza Sorgente" /> <?= $filename ?>
    </div>
    </div>
    </div>
    <div id="phpCode_<?= $fileCounter ?>" class="detail-header-cell" style="display:none;">
    <?php	echo $phpCode."</code>";	?>
    </div>
    <?
    $fileCounter++;
    }
    }*/
    
    
}
?>