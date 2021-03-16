<?php
//require_once('ClassGrepSearch.inc.php');

// The extentions to be searched (in this example, the extentions are comma seperated)
//$filesWithExtentionsToBeSearched = "php, css, js,txt";
$ExtentionsToBeSearched = array("php", "css", "js", "txt");
$ExtentionsToBeIgnored = array("php");

$array_SearchTypes = array(
	"all" => "Match ALL words",
	"any" => "Match ANY word",
	"phrase" => "Match EXACT phrase",
	"allInFile" => "Match ALL words in FILE"
);

/**
 *
 * function to retrieve text lines from file
 * highlighting the search string
 *
 * @param $filePath string
 * @param $classGrepSearch class Instance
 *
 */  
function createLinesFromFile($filePath,$classGrepSearch) {
	$linesArray = file($filePath); 
	$htmlLines="";
	$classGrepSearch->setGlobalCount(0);
	$newLine = "";
	for($i=0;$i<count($linesArray);$i++) {
		$newLine = $classGrepSearch->allStrReplaceTag($linesArray[$i],"<b><font color='green'>","</font></b>" )."<br>";
		if($classGrepSearch->getGlobalResult()) {
//			$htmlLines=$htmlLines."line no:".$i.":".$newLine; 
			$htmlLines .= "Linea N° $i: ".$newLine;
		} 
	} 
	$globalSearchCount = $classGrepSearch->getGlobalCount(); 
	return	 $htmlLines; 
}

/**
*
* function to return the Chapter Names
*  from start and end index
*
* @param $startSpan string
* @param $endSpan string
*
*	return array
*/ 
/* 
function getBookNames($startSpan,$endSpan) {
	$Book = array();
	$Book[1]="Chapter_1_3";
	$Book[2]="Chapter_4_6";
	$Book[3]="Chapter_7_9";
	$Book[4]="Chapter_10_12"; 
	$resultArray=array();
	for($i=$startSpan;$i<=$endSpan;$i++) {
		array_push($resultArray,$Book[$i]);
	}

	return $resultArray;
}
*/