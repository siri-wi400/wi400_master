<?php
/**
 * @name wi400AS400Field
 * @desc Gestione della sessione 5250
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author
 * @version 0.02B 01/04/2018
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */
class wi400AS400Field {
	private $id;
	private $bypass="0";
	private $duplicate="0";
	private $modified="0";
	private $fieldShift="000";
	private $autoEnter="0";
	private $fieldExit="0";
	private $monocase="1";
	private $mandatoryEnter="0";
	private $rightAdjust="000";
	private $xposition=0;
	private $yposition=0;
	private $length=0;
	private $vacum=False;
	private $columnSeparator="0";
	private $blinkField="0";
	private $underscore="0";
	private $highIntensity="0";
	private $reverseImage="0";
	private $colour="20";
	private $text="";
	private $fieldSplit=False;
	private $nextFieldSplit="";
	private $fieldSplitLast=True;
	private $fieldSplitFirst="";
	private $structured=False;
	private $structuredData=Null;
	private $control="";
	private $highLightAttribute="";
	public $data="";
	public $start=0;
	private $IO = False;
	private $forceByPass;
	
	public function __construct($id="") {
		$this->id =$id;
	}
	public function setForceByPass($forceByPass) {
		$this->forceByPass=$forceByPass;
	}
	public function getForceByPass() {
		return $this->forceByPass;
	}
	public function setFieldSplit($split) {
		$this->fieldSplit=$split;
	}
	public function getFieldSplit() {
		return $this->fieldSplit;
	}
	public function setStructured($structured) {
		$this->structured=$structured;
	}
	public function getStructured() {
		return $this->structured;
	}
	public function setStructuredData($structuredData) {
		$this->structuredData=$structuredData;
	}
	public function getStructuredData() {
		return $this->structuredData;
	}
	
	public function setNextFieldSplit($nexFieldSplit) {
		$this->nextFieldSplit=$nexFieldSplit;
	}
	public function getNextFieldSplit() {
		return $this->nextFieldSplit;
	}
	public function setFieldSplitLast($fieldSplitLast) {
		$this->fieldSplitLast=$fieldSplitLast;
	}
	public function getFieldSplitLast() {
		return $this->fieldSplitLast;
	}
	public function setFieldSplitFirst($fieldSplitFirst) {
		$this->fieldSplitFirst=$fieldSplitFirst;
	}
	public function getFieldSplitFirst() {
		return $this->fieldSplitFirst;
	}
	public function setIO($io) {
		$this->IO=$io;
	}
	public function getIO() {
		return $this->IO;
	}
	public function setVacum($vacum) {
		$this->vacum=$vacum;
	}
	public function getVacum() {
		return $this->vacum;
	}
	public function setControl($control) {
		$this->control=$control;
		// Campo in highlight quando selezionato
		if (substr($control,0,2)=="89") {
			$binary = sprintf('%08b',  hexdec(substr($control,2,2)));
			$binary2 = sprintf('%08b',  hexdec("20"));
			$lower = substr($binary,3,5);
			$result = ("001".$lower);
			//echo $result;
			$this->highLightAttribute = dechex(bindec($result));
			//echo $result;
		}
	}
	public function getControl() {
		return $this->control;
	}
	
	public function setId($id) {
		$this->id=$id;
	}
	public function getId() {
		return $this->id;
	}
	public function setHighLightAttribute($id) {
		$this->highLightAttribute=$highLightAttribute;
	}
	public function getHighLightAttribute() {
		return $this->highLightAttribute;
	}
	public function setBypass($bypass) {
		$this->bypass=$bypass;
	}
	public function getBypass() {
		return $this->bypass;
	}
	public function setDuplicate($duplicate) {
		$this->duplicate=$duplicate;
	}
	public function getDuplicate() {
		return $this->duplicate;
	}
	public function setModified($modified) {
		$this->modified=$modified;
	}
	public function getModified() {
		return $this->modified;
	}
	public function setFieldShift($fieldShift) {
		$this->fieldShift=$fieldShift;
	}
	public function getFieldShift() {
		return $this->fieldShift;
	}
	public function setFieldExit($fieldExit) {
		$this->fieldExit=$fieldExit;
	}
	public function getFieldExit() {
		return $this->fieldExit;
	}
	public function setAutoEnter($autoEnter) {
		$this->autoEnter=$autoEnter;
	}
	public function getAutoEnter() {
		return $this->autoEnter;
	}
	public function setMonocase($monocase) {
		$this->monocase=$monocase;
	}
	public function getMonocase() {
		return $this->monocase;
	}
	public function setMandatoryEnter($mandatoryEnter) {
		$this->mandatoryEnter=$mandatoryEnter;
	}
	public function getMandatoryEnter() {
		return $this->mandatoryEnter;
	}
	public function setRightAdjust($rightAdjust) {
		$this->rightAdjust=$rightAdjust;
	}
	public function getRightAdjust() {
		return $this->rightAdjust;
	}
	public function setXposition($xposition) {
		$this->xposition=$xposition;
	}
	public function getXposition() {
		return $this->xposition;
	}
	public function setYposition($yposition) {
		$this->yposition=$yposition;
	}
	public function getYposition() {
		return $this->yposition;
	}
	public function setLength($length) {
		$this->length=$length;
	}
	public function getLength() {
		return $this->length;
	}
	public function setColumnSeparator($columnSeparator) {
		$this->columnSeparator=$columnSeparator;
	}
	public function getColumnSeparator() {
		return $this->columnSeparator;
	}
	public function setBlinkField($blinkField) {
		$this->blinkField=$blinkField;
	}
	public function getBlinkField() {
		return $this->blinkField;
	}
	public function setUnderscore($underscore) {
		$this->underscore=$underscore;
	}
	public function getUnderscore() {
		return $this->underscore;
	}
	public function setHighIntensity($highIntensity) {
		$this->highIntensity=$highIntensity;
	}
	public function getHighIntensity() {
		return $this->highIntensity;
	}
	public function setReverseImage($reverseImage) {
		$this->reverseImage=$reverseImage;
	}
	public function getReverseImage() {
		return $this->reverseImage;
	}
	public function setColour($colour) {
		$this->colour=$colour;
	}
	public function getColour() {
		return $this->colour;
	}
	public function setText($text) {
		$this->text=$text;
	}
	public function getText() {
		return $this->text;
	}
	public function setFFW($ffw) {
		if (substr($ffw,0,2)=="01") {
			$this->setBypass(substr($ffw,2,1));
			$this->setDuplicate(substr($ffw,3,1));
			$this->setModified(substr($ffw,4,1));
			$this->setFieldShift(substr($ffw,5,3));
			$this->setAutoEnter(substr($ffw,8,1));
			$this->setFieldExit(substr($ffw,9,1));
			$this->setMonocase(substr($ffw,10,1));
			$this->setMandatoryEnter(substr($ffw,12,1));
			$this->setRightAdjust(substr($ffw,13,3));
		}
	}
	public function setAttribute($attr) {
		if (substr($attr,0,3)=="001") {
			$this->setColumnSeparator(substr($attr,3,1));
			$this->setBlinkField(substr($attr,4,1));
			$this->setUnderscore(substr($attr,5,1));
			$this->setHighIntensity(substr($attr,6,1));
			$this->setReverseImage(substr($attr,7,1));
		}
	}
}