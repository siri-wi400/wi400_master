<?php

	require_once $routine_path.'/classi/ClassGrepSearch.inc.php';
	require_once "search_files_common.php";

	$azione = $actionContext->getAction();
	
	if($actionContext->getForm()=="SEARCH" && $messageContext->getSeverity()=="ERROR") {
		$actionContext->gotoAction($azione, "DEFAULT", "", true);
	}
	
//	$history->addCurrent();
	
	$scanDir = wi400Detail::getDetailValue($azione."_SRC", 'SEARCH_DIR');
	$searchString = wi400Detail::getDetailValue($azione."_SRC", 'SEARCH_WORDS');
	$searchType = wi400Detail::getDetailValue($azione."_SRC", 'SEARCH_TYPE');
	
	$caseSensitive = false;	
	$caseSensitive_obj = wi400Detail::getDetailField($azione."_SRC","CASE_SENSITIVE");
	if($caseSensitive_obj!="") {
		$caseSensitive = $caseSensitive_obj->getChecked();
	}
//	echo "CASE SENSITIVE: $caseSensitive<br>";
	
	$searchCountOnly = false;
	$searchCountOnly_obj = wi400Detail::getDetailField($azione."_SRC","SEARCH_COUNT_ONLY");
	if($searchCountOnly_obj!="") {
		$searchCountOnly = $searchCountOnly_obj->getChecked();
	}
//	echo "SEARCH COUNT ONLY: $searchCountOnly<br>";
	
//	$limit = "";
	$startSpan = wi400Detail::getDetailValue($azione."_SRC", 'SUBDIR_SPAN_INI');
	$endSpan = wi400Detail::getDetailValue($azione."_SRC", 'SUBDIR_SPAN_FIN');
	
	$subDir_array = array();
	if(wi400Detail::getDetailValue($azione."_SRC", "SUBDIR_ARRAY")!="")
		$subDir_array = wi400Detail::getDetailValue($azione."_SRC", 'SUBDIR_ARRAY');
/*	
	$file_name = "";
	$file_tmp = "";
	if(isset($_FILES['SEARCH_FILE'])) {
		echo "FILE:<pre>"; print_r($_FILES); echo "</pre>";
		
		$file_name = $_FILES['SEARCH_FILE']['name'];
		$file_tmp = $_FILES['SEARCH_FILE']['tmp_name'];
	}
	echo "FILE NAME: $file_name - FILE TMP: $file_tmp<br>";
*/
	$file_name_array = array();
	if(wi400Detail::getDetailValue($azione."_SRC", "SEARCH_FILE")!="")
		$file_name_array = wi400Detail::getDetailValue($azione."_SRC", 'SEARCH_FILE');
	
	if(wi400Detail::getDetailValue($azione."_SRC", "SEARCH_EXTENSIONS")!="")
		$ExtentionsToBeSearched = wi400Detail::getDetailValue($azione."_SRC", 'SEARCH_EXTENSIONS');
	
	$ExtentionsToBeIgnored = array();
	if(wi400Detail::getDetailValue($azione."_SRC", "IGNORE_EXTENSIONS")!="")
		$ExtentionsToBeIgnored = wi400Detail::getDetailValue($azione."_SRC", 'IGNORE_EXTENSIONS');
	
	$search_all_ext = false;
	$search_all_ext_obj = wi400Detail::getDetailField($azione."_SRC","SEARCH_ALL_EXT");
	if($search_all_ext_obj!="") {
		$search_all_ext = $search_all_ext_obj->getChecked();
	}
//	echo "SEARCH ALL EXT: $search_all_ext<br>";
	
	if($actionContext->getForm()=="DEFAULT") {
		
	}
	else if($actionContext->getForm()=="CLEAR") {
		wi400Detail::cleanSession($azione."_SRC");
		
		$actionContext->gotoAction($azione, "DEFAULT", "", true);
	}
	else if($actionContext->getForm()=="SEARCH") {
		// time when this script starts
		$startTime = time();
		
		// create object of the class
		$classGrepSearch = ClassGrepSearch::getInstance();

		// creates an array of all the provided extentions
//		$classGrepSearch->createArrayOfExtentions(",",$filesWithExtentionsToBeSearched);
		$classGrepSearch->setArrayOfExtensions($ExtentionsToBeSearched);
		$classGrepSearch->setIgnoredExtensions($ExtentionsToBeIgnored);
		$classGrepSearch->setSearchAllExt($search_all_ext);
		// Sets the search type
		$classGrepSearch->setSearchType($searchType);
		$classGrepSearch->setSearchString($searchString);
		$classGrepSearch->setScanDir($scanDir);
//		$classGrepSearch->setCaseSensitive(($caseSensitive=="yes")?true:false);
		$classGrepSearch->setCaseSensitive($caseSensitive);
		
		$search = true;
		
		if(!empty($file_name_array)) {
			foreach($file_name_array as $file_name) {
				$not_file = strpos($file_name, "*");
				echo "NOT FILE: $not_file<br>";
				
				if($not_file===false) {
					if($scanDir=="") {
						$fileCounter = $classGrepSearch->readFile($file_name);
						$search = false;
					}
					else {
						$classGrepSearch->setFileSearch($file_name);
						echo "DIRECTORY: $scanDir - CON FILE SEARCH: $file_name<br>";
					}
				}
				else if($not_file!==false) {
					$classGrepSearch->setFileSearch(substr($file_name, 0, -1));
					echo "* FILE SEARCH: $file_name<br>";
				}
			}
		}

		if($search===true) {
			if(!empty($subDir_array)) {
				echo "ARRAY SUBDIRS:<pre>"; print_r($subDir_array); echo "</pre>";
				foreach($subDir_array as $key => $sub) {
					$subDir_array[$key] = str_replace("/", "", $sub);
				}
				$fileCounter = $classGrepSearch->readDirSubdirs($scanDir,$subDir_array);
			}
			else if($startSpan!="") {
				$startSpan = str_replace("/", "", $startSpan);
				$endSpan = str_replace("/", "", $endSpan);
				echo "START SPAN: $startSpan - END SPAN: $endSpan<br>";
				$fileCounter = $classGrepSearch->readDir($scanDir, $startSpan, $endSpan);
			}
			else {
				echo "DIRECTORY: $scanDir<br>";
				$fileCounter = $classGrepSearch->readDir($scanDir);
			}
/*		
			else if($limit=="span") {
				$fileCounter = $classGrepSearch->readDirSubdirs($scanDir,getBookNames($startSpan,$endSpan));
			}
*/
		}
				
		// print information
		$risultati = "";
		$risultati .= "<hr>";
		$risultati .= "The files with <font color='green'><b>";
//		$risultati .= $classGrepSearch->lastStrReplace(",", "and", $filesWithExtentionsToBeSearched);
		$risultati .= $classGrepSearch->lastStrReplace(",", "and", implode(", ", $ExtentionsToBeSearched));
		$risultati .= "</b></font> extentions";
		$risultati .= "<br>";
		$risultati .= " are scanned in <font color='red'><b>";
		$risultati .= $classGrepSearch->getScanDir();
		$risultati .= "</b></font> Directory<br>";
		$risultati .= "<hr>";
		$risultati .= "The pattern/string '<font color='red'><b>".$classGrepSearch->getSearchString()."</font></b>'";
		$risultati .= " was found in following <font color='Green'><b>$fileCounter</b></font> file(s):<br><br>";
		
		$arrayOfFilenames = $classGrepSearch->getarrayOfFilenames();
		
		$array_results = array();
		for($i=0,$j=0;$i<sizeof($arrayOfFilenames);$i++) {
		    $fileName = str_replace($_SERVER['DOCUMENT_ROOT'],"",$arrayOfFilenames[$i]);
		    $linkName = str_replace($_SERVER['DOCUMENT_ROOT'],"Z:",$arrayOfFilenames[$i]);
		    $classGrepSearch->setGlobalCount(0);
//		    if($searchCountOnly !="yes") {
		    if($searchCountOnly === false) {
		       $htmlLines = createLinesFromFile($scanDir.$fileName,$classGrepSearch);
		    }
		    else {
//		    	echo "SEARCH COUNT ONLY<br>";
				$classGrepSearch->setGlobalCount( $classGrepSearch->getSearchCount($scanDir.$fileName));
		    }
//			if($htmlLines !=""||$searchCountOnly =="yes") {
			if($htmlLines !=""||$searchCountOnly === true) {
				$risultati .= "<b> # ".(($j++)+1).") </b>";
				$risultati .= "<font color='green'><b> <a href='"."getFileContents.php?file_path=".urlencode($classGrepSearch->getScanDir().$fileName)."' target='_blank' style='color:green;text-decoration:none'> ".$fileName." </a> </font></b>";
				$risultati .= "[".$classGrepSearch->getGlobalCount(). " time(s)]";
				$risultati .= "<br>".$htmlLines."<br>";
			}
			
			$array_results[$fileName] = array(
				"FILE_NAME" => $fileName,
				"NUM_RES" => $classGrepSearch->getGlobalCount(),
				"RESULTS" => $htmlLines
			); 
		}
		
		if(!empty($array_results))
			ksort($array_results);

		//calulate the time (in seconds) to execute this script
		$endTime = time();
		$totalTime = ($endTime - $startTime);
		
		// total time taken to execute this script
		$timeTaken = $classGrepSearch->convertSecToMins($totalTime);
		
		$num_search_files = sizeof($classGrepSearch->getDirFile());
		$num_search_dirs = sizeof($classGrepSearch->getDirArray())+1;

		$risultati .= "<hr><center><h4 >Info: Searched in <font color='blue'>$num_search_files</font> Files";
		$risultati .= " in <font color='blue'>$num_search_dirs</font> directories. </h4>";
		$risultati .= "<HR>";
		$risultati .= "Total time taken: <font color='blue'> $timeTaken </font> </center>";
		$risultati .= "<HR>";
	}