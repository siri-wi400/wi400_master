<?php
class wi400Session {

	public static $_TYPE_SUBFILE = "subfile";
	public static $_TYPE_DETAIL  = "detail";
	public static $_TYPE_LIST = "list";
	public static $_TYPE_TREE = "tree";
	public static $_TYPE_HISTORY = "history";
	public static $_TYPE_GENERIC = "generic";
	
	public static function getFileName($type, $id){
		// Nome file sessione
		// Personalizzazioni per sessione
		if ($type=='history' && isset($_REQUEST['WI400_IS_IFRAME'])) {
			$id= $id.$_REQUEST['WI400_IS_IFRAME'];
		}
		return wi400File::getSessionFile(session_id(), $id."." . $type);
	}
	
	public static function exist($type, $id){
	
		$sessionFile = wi400Session::getFileName($type, $id);
		if (!file_exists($sessionFile)){
			return false;
		}else{
			return true;
		}
	}
	
	public static function loadFile($session_id, $filename){
		$sessionFile = wi400File::getSessionFile($session_id, $filename);
//		echo "SESSION FILE: $sessionFile<br>";
		if (!file_exists($sessionFile)){
			return false;
		}else{
	       	$contents = file_get_contents($sessionFile);
			return unserialize($contents);
		}
	}
	
	public static function load($type, $id){
		$sessionFile = wi400Session::getFileName($type, $id);
		if (!file_exists($sessionFile)) 
			return false;
		$handle = fopen($sessionFile, "r");
		if ($handle===False) {
			$contents="";
		}
		if (flock($handle, LOCK_SH)){
			$contents = file_get_contents($sessionFile);
			flock($handle, LOCK_UN);
			fclose($handle);
		} else {
			fclose($handle);
			$contents="";
		}
		
		//$contents = fread($handle, filesize($sessionFile));
		/*$try = True;
		$count = 0;
		while ($try) {
			$count++;
			$contents = file_get_contents($sessionFile);
			if ($contents===False) {
				usleep(100);
			} else {
				break;
			}
			if ($count ==10) {
				break;
			}
			
		}*/
		
		//fclose($handle);
		return unserialize($contents);
	}
	
	public static function delete($type, $id){
		if (file_exists(wi400Session::getFileName($type, $id))){
			return unlink(wi400Session::getFileName($type, $id));
		}
	}
	
	public static function destroy(){
		wi400File::deleteSessionFiles(session_id());
	}
	
	public static function destroyBySession($session){
		wi400File::deleteSessionFiles($session); 
	}
	public static function save($type, $id, $object){
		
		$sessionFile = wi400Session::getFileName($type, $id);

		$handle = fopen($sessionFile, "w");
		if (!$handle) {
			die("file $sessionFile non scritto!!");
		}
		if (flock($handle, LOCK_EX)){
			ftruncate($handle, 0);
//			echo "OBJECT:<pre>"; print_r($object); echo "</pre>";
	    	$contents = serialize($object);
		    fputs($handle, $contents);
    		flock($handle, LOCK_UN);
		    fclose($handle);
		} else {
//			die("file $sessionFile non scritto perchè locked!!");
	        fclose($handle);
	        return false;
	    }
		
	}
	
	
	
	
	
	
}

