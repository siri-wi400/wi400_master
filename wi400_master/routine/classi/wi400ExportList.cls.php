<?php 

/**
 * @name wi400ExportList 
 * @desc Classe per l'esportazione di liste
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author Valeria Porrazzo
 * @version 1.00 26/05/2009
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400ExportList {
	
	private $exportTarget;
	protected $wi400List;
	protected $exportFilters;
	private $exportOrientation;
	
	private $exportFormat;
	
	private $export_batch = false;

	private $wi400ListSql;
	
	private $startFrom;
	private $pageRows;
	private $selection;
	private $rowsSelectionArray;
	
	private $filename;
	private $filepath;
	
	private $temp;
	private $TypeImage;
	
	protected $start;
	private $colonna;
	private $identificativo;
	public	$oldPage;
	
	private $idDetails = array();
	private $parameters = array();
	
	private $logo_array = array();
	private $template = "";
	
	
	private $targets = array();
	private $formats = array();
	private $orientation = array();
	private $notifica = array();
	
	private $filters = true;
	private $zip = false;
	private $can_batch = true;
	private $batch = false;
	
	private $print_order = true;
	
	/**
	 * Costruttore della classe
	 *
	 * @param string $exportTarget	: tipo di selezione (PAGE, SELECTED, ALL)
	 * @param Object $wi400List		: oggetto lista
	 */
	public function __construct($exportTarget=null, $wi400List=null, $exportFilters=false, $exportOrientation="L") {
		global $settings;
		
		$this->exportTarget = $exportTarget;
		$this->wi400List = $wi400List;
		$this->exportFilters = $exportFilters;
		$this->exportOrientation = $exportOrientation;
		
		// Default Targets
		$this->targets["ALL"] 	   = array("VAL" => "ALL", "DES" => _t('TUTTI'));
//		$this->targets["ALL"] 	   = array("VAL" => "ALL", "DES" => _t('TUTTI'), "CHECK" => true);
		$this->targets["PAGE"] 	   = array("VAL" => "PAGE", "DES" => _t("PAGINA_CORRENTE"));
		$this->targets["SELECTED"] = array("VAL" => "SELECTED", "DES" => _t("SELEZIONATI"));
		
		// Default Formats
		$this->formats["PDF"]  = array("VAL" => "pdf", "DES" => "Portable Document Format (PDF)");
		$this->formats["XLS"]  = array("VAL" => "excel5", "DES" => "Excel 5 (XLS)");
		$this->formats["XLSX"] = array("VAL" => "excel2007", "DES" => "Excel 2007 (XLSX)");
//		$this->formats["XLSX"] = array("VAL" => "excel2007", "DES" => "Excel 2007 (XLSX)", "CHECK" => true);
//		$this->formats["XLS"]  = array("VAL" => "excel5", "DES" => "Excel 5 (XLS)");
		$this->formats["CSV"]  = array("VAL" => "csv", "DES" => _t('LIST_EXPORT_CSV'));
//		$this->formats["PDF"]  = array("VAL" => "pdf", "DES" => "Portable Document Format (PDF)");
		$this->formats["XML"]  = array("VAL" => "xml", "DES" => "Format XML");
		
		// Orientamento
		$this->orientation["LANDSCAPE"]	= array("VAL" => "L", "DES" => "Orizzontale");
//		$this->orientation["LANDSCAPE"]	= array("VAL" => "L", "DES" => "Orizzontale", "CHECK" => true);
		$this->orientation["PORTRAIT"]	= array("VAL" => "P", "DES" => "Verticale");
		
		// Notifiche BATCH
		$this->notifica["ALLEGATO"]	= array("VAL" => "ALLEGATO", "DES" => _t('INVIA IL FILE CON UN E-MAIL'));
//		$this->notifica["ALLEGATO"]	= array("VAL" => "ALLEGATO", "DES" => _t('INVIA IL FILE CON UN E-MAIL'), "CHECK" => true);
		$this->notifica["NOTIFICA"]	= array("VAL" => "NOTIFICA", "DES" => _t('INVIA UN EMAIL DI NOTIFICA'));
		$this->notifica["NO_NOTIFICA"]	= array("VAL" => "NO_NOTIFICA", "DES" => _t('NON NOTIFICARE'));
		
		$default_params = array(
			"targets" => "ALL",
			"formats" => "XLSX",
			"orientation" => "LANDSCAPE",
			"notifica" => "ALLEGATO"
		);
		
		foreach($default_params as $key => $val) {
//			echo "<font color='blue'>PARAMETRI</font> $key:<pre>"; print_r($this->$key); echo "</pre>";
			
			if(isset($settings["default_export_".$key])) {
				$cmp_def = $settings["default_export_".$key];
//				echo "<font color='red'>CMP DEF $key</font>: $cmp_def<br>";
				
//				if(isset($this->$key[$cmp_def])) {
				if(array_key_exists($cmp_def, $this->{$key})) {
					$default_params[$key] = $cmp_def;
					$val = $cmp_def;
				}
			}
//			echo "CHIAVE: "; var_dump($val); echo "<br>";
			
			if(array_key_exists($val, $this->{$key})) {
				$this->{$key}[$val]['CHECK'] = true;
			}
			
//			echo "PARAMETRI $key:<pre>"; print_r($this->$key); echo "</pre>";
		}
		
		$default_sels = array(
			"filters" => true,
			"zip" => false,
			"batch" => false,
		);
		
		foreach($default_sels as $key => $val) {
			if(isset($settings["default_export_".$key])) {
				$cmp_def = $settings["default_export_".$key];
//				echo "<font color='blue'>CMP DEF $key</font>: $cmp_def<br>";
			
				if(isset($this->$key)) {
					$default_sels[$key] = $cmp_def;
					$val = $cmp_def;
				}
			}
				
			$this->$key = $val;
			
//			echo "SEL $key: "; var_dump($this->$key); echo "<br>";
		}
		
		if((isset($settings['export_batch']) && $settings['export_batch']===true) || $this->get_export_batch()===true) {
			$this->can_batch = true;
		}
	}
	
	/**
	 * Distruttore della classe
	 *
	 */
	public function __destruct() {
 
	}
	
	public function getExportTarget() {
		return $this->exportTarget;
	}
	
	public function get_wi400List() {
		return $this->wi400List;
	}
	
	public function getExportFilters() {
		return $this->exportFilters;
	}
	
	public function setExportFormat($format) {
		$this->exportFormat = $format;
	}
	
	public function getExportFormat() {
		return $this->exportFormat;
	}
	
	public function setExportOrientation($orientation) {
		$this->exportOrientation = $orientation;
	}
	
	public function getExportOrientation() {
		return $this->exportOrientation;
	}
	
	/**
	 * Recupera la posizione della lista da cui partire per la selezione
	 * Serve per sapere da dove iniziare per l'esportazione della pagina corrente quando non si è a pagina 1 
	 *
	 * @return unknown
	 */
	public function getStartFrom(){
		return $this->startFrom;
	}
	
	public function set_export_batch($batch) {
		$this->export_batch = $batch;
	}
	
	public function get_export_batch() {
		return $this->export_batch;
	}
	
	public function get_filters() {
		$filterWhere = $this->wi400ListSql->get_filterWhere();
		
		return $filterWhere;
	}
	
	/**
	 * Preparazione dell'esportazione
	 *
	 */
	public function prepare($order=true) {
		$this->startFrom = 0;
		if($this->exportTarget=="PAGE") {
			$this->startFrom = $this->wi400List->getStartFrom();
		}
		
		$this->wi400ListSql = new wi400ListSql($this->wi400List, $this->wi400List->getAutoFilter());		
//		$this->wi400ListSql->prepare_query_parts();
		$this->query = $this->wi400ListSql->get_query($order);
		
		$this->pageRows  = $this->wi400List->getPageRows();
		$this->selection = $this->wi400List->getSelection();
		$this->rowsSelectionArray = $this->wi400List->getSelectionArray();
	}
	
	public function setFilename($filename="") {
		$this->filename = $filename;
	}
	
	/**
	 * Ritorna il nome del file che si sta generando
	 *
	 * @return string
	 */
	public function getFilename() {
		return $this->filename;
	}
	
	public function setFilepath($filepath="") {
		if($filepath=="")
			$this->filepath = wi400File::getUserFile($this->temp, $this->filename);
		else
			$this->filepath = $filepath;
	}
/*	
	public function getFilepath() {
		return $this->filepath;
	}
*/		
	public function getTemp() {
		return $this->temp;
	}
	
	/**
	 * Ritorna il nome del file immagine associato al tipo del file
	 *
	 * @return string
	 */
	public function getTypeImage() {
		return $this->TypeImage;
	}
	
	/**
	 * Ritorna la query associata alla lista
	 * (ricavata dal precedente uso del metodo prepare())
	 *
	 * @return string
	 */
	public function get_query() {
		return $this->query;
	}
	
	/**
	 * Ritorna le righe selezionate della lista
	 *
	 * @return array
	 */
	public function get_rowsSelectionArray() {
		return $this->rowsSelectionArray;
	}
	
	/**
	 * Ritorna il numero di righe di cui è composta una pagina della lista
	 *
	 * @return integer
	 */
	public function get_PageRows() {
		return $this->pageRows;
	}
	
	/**
	 * Ritorna il path del file che si sta generando
	 *
	 * @return string
	 */
	public function get_filepath() {
		return $this->filepath;
	}
	
	/**
	 * Ritorna l'altezza da cui cominciare a far riferimento
	 * (utilizzato nell'esportazione in PDF)
	 *
	 * @return integer
	 */
	public function getStart() {
		return $this->start;
	}
	
	/**
	 * Ritorna la posizione orizzontale da cui cominciare a far riferimento
	 *
	 * @return integer
	 */
	public function getColonna() {
		return $this->colonna;
	}
	
	/**
	 * Impostazione dei dati necessari per la creazione del file
	 *
	 * @param string $filename			: nome del file che si sta generando 
	 * @param unknown_type $temp		: 
	 * @param unknown_type $TypeImage	: nome del file immagine associata al tipo del file che si sta generando
	 */
	public function setDatiExport($filename="", $temp="", $TypeImage="", $filepath="") {
		$this->temp = $temp;
		$this->TypeImage = $TypeImage;
		
		$this->setFilename($filename);
		$this->setFilepath($filepath);
	}
	
	/**
	 * Impostazione degli id dei details da stampare
	 *
	 * @param array $idDetail	: ids dei details da stampare
	 */
	public function setIdDetails($idDetails) {
		$this->idDetails = $idDetails;
		
//		echo "ID_DETAILS: "; print_r($this->idDetails); echo "<br>";
	}
	
	/**
	 * Recupero degli id dei details da stampare
	 *
	 * @return array
	 */
	public function getIdDetails() {
		return $this->idDetails;
	}
	
	/**
	 * Impostazione di parametri extra da passare
	 *
	 * @param string $idParams		: nome del parametro da passare
	 * @param unknown_type $params	: valore del parametro da passare
	 */
	public function setParameters($idParams, $params) {
		$this->parameters[$idParams] = $params;
	}
	
	/**
	 * Recupero dei parametri extra da passare
	 *
	 * @return array
	 */
	public function getParameters() {
		return $this->parameters;
	}
	
	/**
	 * Recupero di un parametro extra specifico da passare
	 *
	 * @param string $idParams : nome del parametro da passare
	 *
	 * @return unknown_type
	 */
	public function getParameter($idParam) {
		return $this->parameters[$idParam];
	}
	
	public function addNotificationSelections($table=true, $zip=false, $check_zip=false) {
		$extras['TITLE'] = _t('NOTIFICA FINE ESPORTAZIONE');
		
		if($table===true)
			$code = "<table border='0' cellpadding='2'>";
/*		
		$code .= "<tr>
				<td><input type='radio' name='NOTIFICA' value='ALLEGATO' checked></td>
				<td class='text'>"._t('INVIA IL FILE CON UN E-MAIL')."</td>
			</tr>
			<tr>
				<td><input type='radio' name='NOTIFICA' value='NOTIFICA'></td>
				<td class='text'>"._t('INVIA UN EMAIL DI NOTIFICA')."</td>
			</tr>
			<tr>
				<td><input type='radio' name='NOTIFICA' value='NO_NOTIFICA'></td>
				<td class='text'>"._t('NON NOTIFICARE')."</td>
			</tr>";
*/
		foreach($this->notifica as $key => $val) {
			$checked = "";
			if(isset($val['CHECK']) && $val['CHECK']==true)
				$checked = "checked";
			
			$code .= "<tr>
					<td><input type='radio' name='NOTIFICA' value='".$val['VAL']."' $checked></td>
					<td class='text'>".$val['DES']."</td>
				</tr>";
		}
		
		if($zip===true) {
			$code .= "<tr>
					<td>&nbsp;</td>
				</tr>";
			$zip = self::addZipSelection(false, $check_zip);
			$code .= $zip['BODY'];
		}
		
		if($table===true)
			$code .= "</table>";
		
		$extras['BODY'] = $code;
		
		return $extras;
	}
	
	public function addNotificationSelections_NEW($exportDetail, $zip=false, $check_zip=false) {		
		$field_options = array();
		$check_val = "";
		foreach($this->notifica as $key => $val) {
			if(isset($val['CHECK']) && $val['CHECK']==true)
				$check_val = $val['VAL'];

			$field_options[$val['VAL']] = $val['DES'];
		}
/*		
		$mySelect = new wi400InputSelect("NOTIFICA");
		$mySelect->setLabel(_t('NOTIFICA FINE ESPORTAZIONE'));
//		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($field_options);
		$mySelect->setValue($check_val);
		$exportDetail->addField($mySelect);
*/
		$this->cellFieldSel($exportDetail, "NOTIFICA", _t('NOTIFICA FINE ESPORTAZIONE'), $field_options, $check_val);
		
		if($zip===true) {
			self::addZipSelection_NEW($exportDetail, $check_zip);
		}
	}
	
	public function addZipSelection($table=true, $is_checked=false) {
		$zip['TITLE'] =  _t("SELEZIONI_AGGIUNTIVE");
		
		$code = "";
		
		if($table===true)
			$code = "<table border='0' cellpadding='2'>";
		
		$checked = "";
		if($is_checked===true)
			$checked = "checked";
		
		$code .= "<tr>
				<td><input type='checkbox' name='ZIP' value='ZIP' $checked></td>
				<td class='text'>"._t("FILE_COMPRESSO")."</td>
			</tr>";
		
		if($table===true)
			$code .= "</table>";
		
		$zip['BODY'] =	$code;
		
		return $zip;
	}
	
	public function addZipSelection_NEW($exportDetail, $is_checked=false) {	
/*		
		$myField = new wi400InputSwitch("ZIP");
		$myField->setLabel(_t("FILE_COMPRESSO"));
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
//		$myField->setChecked($is_checked);
//		$myField->setValue(1);
		$myField->setValue("ZIP");
		$exportDetail->addField($myField);
*/		
		$this->cellFieldCheck($exportDetail, "ZIP", _t("FILE_COMPRESSO"), "ZIP", $is_checked);
	}

	public function viewDefault($idList=null, $cell_1=null, $cell_2=null, $cell_3=null, $cell_4=null) {
		global $settings;
		
		// BATCH
		$batch = "";
		if($this->can_batch===true) {
			$notifica = $this->addNotificationSelections(true);
			
			$check_batch = "";
			if($this->batch===true)
				$check_batch = "checked";
			
			$batch = "<tr>
					<td><input type='checkbox' name='BATCH' value='BATCH' $check_batch></td>
					<td class='text'>BATCH</td>
				</tr>";
			
			$batch .= "<tr>
					<table border='0' cellpadding='2'>
						<tr>
							<td>".$notifica['TITLE']."</td>
						</tr>
						<tr>
							<td>".$notifica['BODY']."</td>
						</tr>
					</table>
				</tr>";
		}
		
		// FILTRI DI SELEZIONE
		$filtri_sel = true;
		
		$check_filtri = "";
		if($this->filters===true)
			$check_filtri = "checked";
		
		if(wi400Session::exist(wi400Session::$_TYPE_LIST, $idList)){
			$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
			
			if($wi400List->getExportFilterSel()===false) {
				$filtri_sel = false;
			}
			
			if($wi400List->getExportFilterSelChecked()===false) {
				$check_filtri = "";
			}
		}
		
		if($filtri_sel===true) {
			$filtri_selezione = "<tr>
					<td><input type='checkbox' name='FILTERS' value='FILTERS' $check_filtri></td>
					<td class='text'>"._t("STAMPA_FILTRI_SELEZIONE")."</td>
				</tr>";
		}
		
		// ZIP
/*
		$check_zip = "";
		if($this->zip===true)
			$check_zip = "checked";
		
		$zip = "<tr>
				<td><input type='checkbox' name='ZIP' value='ZIP' $check_zip></td>
				<td class='text'>"._t("FILE_COMPRESSO")."</td>
			</tr>";
*/
		$zip_array = self::addZipSelection(false, $this->zip);
		$zip = $zip_array['BODY'];
		
		$array_celle = array(
			"cell_1" => array(
				"TITLE" => _t("SCEGLI_TARGET"),
				"NAME" => "TARGET",
				"BODY" => array()
			),
			"cell_2" => array(
				"TITLE" => _t("SCEGLI_FORMATO"),
				"NAME" => "FORMAT",
				"BODY" => array()
			),
			"cell_3" => array(
				"TITLE" => "Orientamento pagina (PDF)",
				"NAME" => "ORIENTATION",
				"BODY" => array()
			),
			"cell_4" => array(
				"TITLE" => _t("SELEZIONI_AGGIUNTIVE"),
				"NAME" => "",
				"BODY" => "<table border='0' cellpadding='2'>
								$filtri_selezione
								$zip
								$batch
							</table>"
			)
		);
		
		// @todo sistemare: in alcuni casi non compaiono i settori TARGET e FORMAT perchè dice che 
		// $exportArray['TARGET'] e $exportArray['TARGET'] non sono degli array
		// Es: Dettaglio promozioni in ABATE
		
		// ECCEZIONI TARGET FORMAT
		if (wi400Session::exist(wi400Session::$_TYPE_LIST, $idList)){
			$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
			$exportArray = $wi400List->getCanExport();
//			echo "EXPORT ARRAY:<pre>"; print_r($exportArray); echo "</pre>";
			
			if (is_array($exportArray) && isset($exportArray["TARGET"])){
//				echo "UNSET TARGETS<br>";
				foreach ($this->targets as $k => $t){
					if (!in_array($k, $exportArray["TARGET"])) unset($this->targets[$k]);
				}
			}
			if (is_array($exportArray) && isset($exportArray["FORMAT"])){
//				echo "UNSET FORMATS<br>";
				foreach ($this->formats as $k => $f){
					if (!in_array($k, $exportArray["FORMAT"])) unset($this->formats[$k]);
				}
			}
			if (is_array($exportArray) && isset($exportArray["ORIENTATION"])){
//				echo "UNSET ORIENTATION<br>";
				foreach ($this->orientation as $k => $f){
					if (!in_array($k, $exportArray["ORIENTATION"])) unset($this->orientation[$k]);
				}
			}
		}
		
		// TARGET LIST
		foreach ($this->targets as $k => $t){
			$array_celle["cell_1"]["BODY"][] = $t;
		}
		//FORMAT LIST
		foreach ($this->formats as $k => $f){
			$array_celle["cell_2"]["BODY"][] = $f;
		}
		//ORIENTATION LIST
		foreach ($this->orientation as $k => $o){
			$array_celle["cell_3"]["BODY"][] = $o;
		}
		
		$titles_1 = "";
		$body_1 = "";
		$titles_2 = "";
		$body_2 = "";

		$cells = array();
//		$c = -1;
		$c = 0;
		for($i=1; $i<=4; $i++) {
			$campo = "cell_$i";
//			$c++;
			
//			echo "CELL $i ($campo) - VAL: ${$campo}<pre>"; print_r($array_celle[$campo]); echo "</pre>";
			
			if(isset($$campo) && !empty($$campo)) {
				if(isset(${$campo}['TITLE'])) {
					$cells[$c]['TITLE'] = ${$campo}['TITLE'];
				}
				else {
					$cells[$c]['TITLE'] = $array_celle[$campo]['TITLE'];
				}
				
				$cella = array();
				$cella['TITLE'] = $cells[$c]['TITLE'];
				if(isset(${$campo}['NAME'])) {
					$cella['NAME'] = ${$campo}['NAME'];
				}
				else {
					$cella['NAME'] = $array_celle[$campo]['NAME'];
				}
					
				if(isset(${$campo}['BODY'])) {	
					if(is_array(${$campo}['BODY'])) {
						$cella['BODY'] = ${$campo}['BODY'];
						
						$codice = $this->cell_table($cella);
						
						$cells[$c]['BODY'] = $codice;
					}
					else {
						$cells[$c]['BODY'] = ${$campo}['BODY'];
					}
				}
				else {
					if(is_array($array_celle[$campo]['BODY'])) {
						$cella['BODY'] = $array_celle[$campo]['BODY'];
						
						$codice = $this->cell_table($cella);
				
						$cells[$c]['BODY'] = $codice;
					}
					else {
						$cells[$c]['BODY'] = $array_celle[$campo]['BODY'];
					}
				}
			}
			else {
				$cells[$c] = "";
			}
			
			$c++;
		}
//		echo "CELLS:<pre>"; print_r($cells); echo "</pre>";
		
		$g = 1;
		foreach($cells as $key => $cell) {
			$t = "titles_$g";
			$b = "body_$g";
			
			if($$t!="")
				$g++;

			if($cell=="") {
				continue;
			}
			else if($$t!="") {
				$$t .= "<td>&nbsp;</td>";
				$$b .= "<td>&nbsp;</td>";
			}
			
			$$t .= "<td class='detail-header-cell'>".$cell['TITLE']."</td>";
			$$b .= "<td class='detail-header-cell'>".$cell['BODY']."</td>";
		}
		
		$table = array();
		$table[] = "<table width='100%' border='0'>";
		$table[] = "<tr>";
		$table[] = $titles_1;
		$table[] = "</tr>";
		$table[] = "<tr>";
		$table[] = $body_1;
		if(isset($titles_2) && !empty($titles_2)) {
			$table[] = "</tr>";
			$table[] = "<tr><td>&nbsp;</td></tr>";
			$table[] = "<tr>";
			$table[] = $titles_2;
			$table[] = "</tr>";
			$table[] = "<tr>";
			$table[] = $body_2;
		}
		$table[] = "</tr>";
		$table[] = "</table>";
		
		if(!empty($idList))
			$table[] = "<input name='IDLIST' type='hidden' value='$idList'>";
			
		if(!empty($this->idDetails)) {
			$details = serialize($this->idDetails);
			$table[] = "<input name='ID_DETAILS' type='hidden' value='$details'>";
		}
		
		foreach($this->parameters as $key => $val) {
			$table[] = "<input name='$key' type='hidden' value='$val'>";
		}
		
		$codice = implode("",$table);
		echo $codice;
	}
	
	private function cell_table($cell=array()) {
		$table = array();
		$codice = "";
		
//		echo "CELLA:<pre>"; print_r($cell); echo "</pre>";
		
		if(!empty($cell['BODY'])) {
			$check = 0;
			foreach($cell['BODY'] as $key => $val) {
				if(isset($val['CHECK']) && $val['CHECK']===true) {
					$check = $key;
				}
			}
			
			$table[] = "<table border='0' cellpadding='2'>";
			
			foreach($cell['BODY'] as $key => $val) {
				$table[] = "<tr>";
				$table[] = "<td>";
				$table[] = "<input type='radio' name='".$cell['NAME']."' value='".$val['VAL']."' ";
				
				if($check==$key) {
					$table[] = "checked";
				}
				
				$table[] = ">";
				$table[] = "</td>";
				$table[] = "<td class='text'>".$val['DES']."</td>";
				$table[] = "</tr>";
			}
			
			$table[] = "</table>";
			
			$codice = implode("",$table);
		}
		
		return $codice;
	}
	
//	public function viewDefault_DET_OLD($idList=null, $cell_1=null, $cell_2=null, $cell_3=null, $cell_4=null) {
	public function viewDefault_DET_OLD($idList=null, $cell_1=null, $cell_2=null, $cell_3=null, $cell_4=null, $idDet="") {
		global $settings;
		global $actionContext;
		
		if(!isset($idDet) || empty($idDet)) {
//			$idDet = "EXPORT_LIST_".$idList."_DET";
			$idDet = $actionContext->getAction()."_DET";
		}
//		echo "ID DETAIL: $idDet<br>";		
		
		$exportDetail = new wi400Detail($idDet);
		$exportDetail->setColsNum(2);
		$exportDetail->isEditable(true);
//		$exportDetail->setSaveDetail(true);		
	
		// BATCH
		$batch = "";
		if($this->can_batch===true) {
			$notifica = $this->addNotificationSelections(true);

			$check_batch = "";
			if($this->batch===true)
				$check_batch = "checked";
								
			$batch = "<tr>
					<td><input type='checkbox' name='BATCH' value='BATCH' $check_batch></td>
					<td class='text'>BATCH</td>
				</tr>";
				
			$batch .= "<tr>
					<table border='0' cellpadding='2'>
						<tr>
							<td>".$notifica['TITLE']."</td>
						</tr>
						<tr>
							<td>".$notifica['BODY']."</td>
						</tr>
					</table>
				</tr>";
		}
	
		// FILTRI DI SELEZIONE
		$filtri_sel = true;
		
		$check_filtri = "";
		if($this->filters===true)
			$check_filtri = "checked";
	
		if(wi400Session::exist(wi400Session::$_TYPE_LIST, $idList)){
			$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
				
			if($wi400List->getExportFilterSel()===false) {
				$filtri_sel = false;
			}
				
			if($wi400List->getExportFilterSelChecked()===false) {
				$check_filtri = "";
			}
		}
	
		if($filtri_sel===true) {
			$filtri_selezione = "<tr>
					<td><input type='checkbox' name='FILTERS' value='FILTERS' $check_filtri></td>
					<td class='text'>"._t("STAMPA_FILTRI_SELEZIONE")."</td>
				</tr>";
		}
		
		// ZIP
/*			
		$check_zip = "";
		if($this->zip===true)
			$check_zip = "checked";
		
		$zip = "<tr>
				<td><input type='checkbox' name='ZIP' value='ZIP' $check_zip></td>
				<td class='text'>"._t("FILE_COMPRESSO")."</td>
			</tr>";
*/			
		$zip_array = self::addZipSelection(false, $this->zip);
		$zip = $zip_array['BODY'];
	
		$array_celle = array(
			"cell_1" => array(
				"TITLE" => _t("SCEGLI_TARGET"),
				"NAME" => "TARGET",
				"BODY" => array()
			),
			"cell_2" => array(
				"TITLE" => _t("SCEGLI_FORMATO"),
				"NAME" => "FORMAT",
				"BODY" => array()
			),
			"cell_3" => array(
				"TITLE" => "Orientamento pagina (PDF)",
				"NAME" => "ORIENTATION",
				"BODY" => array()
			),				
			"cell_4" => array(
				"TITLE" => _t("SELEZIONI_AGGIUNTIVE"),
				"NAME" => "",
				"BODY" => "<table border='0' cellpadding='2'>
						$filtri_selezione
						$zip
						$batch
					</table>"
			)			
		);	
	
		// @todo sistemare: in alcuni casi non compaiono i settori TARGET e FORMAT perchè dice che
		// $exportArray['TARGET'] e $exportArray['TARGET'] non sono degli array
		// Es: Dettaglio promozioni in ABATE
	
		// ECCEZIONI TARGET FORMAT
		if (wi400Session::exist(wi400Session::$_TYPE_LIST, $idList)){
			$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
			$exportArray = $wi400List->getCanExport();
//			echo "EXPORT ARRAY:<pre>"; print_r($exportArray); echo "</pre>";
					
			if (is_array($exportArray) && isset($exportArray["TARGET"])){
//				echo "UNSET TARGETS<br>";
				foreach ($this->targets as $k => $t){
					if (!in_array($k, $exportArray["TARGET"])) unset($this->targets[$k]);
				}
			}
			if (is_array($exportArray) && isset($exportArray["FORMAT"])){
//				echo "UNSET FORMATS<br>";
				foreach ($this->formats as $k => $f){
					if (!in_array($k, $exportArray["FORMAT"])) unset($this->formats[$k]);
				}
			}
			if (is_array($exportArray) && isset($exportArray["ORIENTATION"])){
//				echo "UNSET ORIENTATION<br>";
				foreach ($this->orientation as $k => $f){
					if (!in_array($k, $exportArray["ORIENTATION"])) unset($this->orientation[$k]);
				}
			}
		}
		
		// TARGET LIST
		foreach ($this->targets as $k => $t){
			$array_celle["cell_1"]["BODY"][] = $t;
		}
		//FORMAT LIST
		foreach ($this->formats as $k => $f){
			$array_celle["cell_2"]["BODY"][] = $f;
		}
		//ORIENTATION LIST
		foreach ($this->orientation as $k => $o){
			$array_celle["cell_3"]["BODY"][] = $o;
		}

		$titles_1 = "";
		$body_1 = "";
		$titles_2 = "";
		$body_2 = "";

		$cells = array();
//		$c = -1;
		$c = 0;
		for($i=1; $i<=4; $i++) {
			$campo = "cell_$i";
//			$c++;
						
//			echo "CELL $i ($campo) - VAL: ${$campo}<pre>"; print_r($array_celle[$campo]); echo "</pre>";
						
			if(isset($$campo) && !empty($$campo)) {
				if(isset(${$campo}['TITLE'])) {
					$cells[$c]['TITLE'] = ${$campo}['TITLE'];
				}
				else {
					$cells[$c]['TITLE'] = $array_celle[$campo]['TITLE'];
				}

				$cella = array();
				$cella['TITLE'] = $cells[$c]['TITLE'];
				
				if(isset(${$campo}['NAME'])) {
					$cella['NAME'] = ${$campo}['NAME'];
				}
				else {
					$cella['NAME'] = $array_celle[$campo]['NAME'];
				}

				if(isset(${$campo}['BODY'])) {					
					if(is_array(${$campo}['BODY'])) {
						$cella['BODY'] = ${$campo}['BODY'];

						$codice = $this->cell_table($cella);

						$cells[$c]['BODY'] = $codice;
					}
					else {
						$cells[$c]['BODY'] = ${$campo}['BODY'];
					}
				}
				else {					
					if(is_array($array_celle[$campo]['BODY'])) {
						$cella['BODY'] = $array_celle[$campo]['BODY'];

						$codice = $this->cell_table($cella);

						$cells[$c]['BODY'] = $codice;
					}
					else {
						$cells[$c]['BODY'] = $array_celle[$campo]['BODY'];
					}
				}
				
				$myField = new wi400Text($cella['NAME']."_FIELD");
				$myField->setLabel($cella['TITLE']);
				$myField->setValue($cells[$c]['BODY']);
				$exportDetail->addField($myField);
			}
			else {
				$cells[$c] = "";
				
				$myField = new wi400Text("VUOTO");
				$myField->setLabel("");
				$myField->setValue("");
				$exportDetail->addField($myField);
			}
			
			$c++;
		}
//		echo "CELLS:<pre>"; print_r($cells); echo "</pre>";
/*		
		$g = 1;
		foreach($cells as $key => $cell) {
			$t = "titles_$g";
			$b = "body_$g";
				
			if($$t!="")
				$g++;

			if($cell=="") {
				continue;
			}
			else if($$t!="") {
				$$t .= "<td>&nbsp;</td>";
				$$b .= "<td>&nbsp;</td>";
			}
				
			$$t .= "<td class='detail-header-cell'>".$cell['TITLE']."</td>";
			$$b .= "<td class='detail-header-cell'>".$cell['BODY']."</td>";
		}

		$table = array();
		$table[] = "<table width='100%' border='0'>";
		$table[] = "<tr>";
		$table[] = $titles_1;
		$table[] = "</tr>";
		$table[] = "<tr>";
		$table[] = $body_1;
		if(isset($titles_2) && !empty($titles_2)) {
			$table[] = "</tr>";
			$table[] = "<tr><td>&nbsp;</td></tr>";
			$table[] = "<tr>";
			$table[] = $titles_2;
			$table[] = "</tr>";
			$table[] = "<tr>";
			$table[] = $body_2;
		}
		$table[] = "</tr>";
		$table[] = "</table>";

		if(!empty($idList))
			$table[] = "<input name='IDLIST' type='hidden' value='$idList'>";
			
		if(!empty($this->idDetails)) {
			$details = serialize($this->idDetails);
			$table[] = "<input name='ID_DETAILS' type='hidden' value='$details'>";
		}

		foreach($this->parameters as $key => $val) {
			$table[] = "<input name='$key' type='hidden' value='$val'>";
		}

		$codice = implode("", $table);
		
		echo $codice;
*/		
		if(!empty($idList)) {
			$hiddenField = new wi400InputHidden("IDLIST");
			$hiddenField->setValue($idList);
			$exportDetail->addField($hiddenField);
		}
		
		if(!empty($this->idDetails)) {
			$details = serialize($this->idDetails);
			
			$hiddenField = new wi400InputHidden("ID_DETAILS");
			$hiddenField->setValue($details);
			$exportDetail->addField($hiddenField);
		}
		
		foreach($this->parameters as $key => $val) {
			$hiddenField = new wi400InputHidden($key);
			$hiddenField->setValue($val);
			$exportDetail->addField($hiddenField);
		}
		
		$exportDetail->dispose();		
//		return $exportDetail;
	}
	
//	public function viewDefault_DET_NEW($idList=null, $cell_1=null, $cell_2=null, $cell_3=null, $cell_4=null) {
	public function viewDefault_DET_NEW($idList=null, $cell_1=null, $cell_2=null, $cell_3=null, $cell_4=null, $idDet="") {
		global $settings;
		global $actionContext;
	
		if(!isset($idDet) || empty($idDet)) {
//			$idDet_1 = "EXPORT_LIST_".$idList."_DET";
			$idDet_1 = $actionContext->getAction()."_DET";
		}
//		echo "ID DETAIL: $idDet_1<br>";
	
		$exportDetail = new wi400Detail($idDet_1);
		$exportDetail->setColsNum(2);
		$exportDetail->isEditable(true);
//		$exportDetail->setSaveDetail(true);

		// FILTRI DI SELEZIONE
		$filtri_sel = true;
//		$check_filtri = "checked";
//		$check_filtri = true;
		$check_filtri = $this->filters;

		if(wi400Session::exist(wi400Session::$_TYPE_LIST, $idList)){
			$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);

			if($wi400List->getExportFilterSel()===false) {
				$filtri_sel = false;
			}

			if($wi400List->getExportFilterSelChecked()===false) {
//				$check_filtri = "";
				$check_filtri = false;
			}
		}
/*		
		if($filtri_sel===true) {
			$filtri_selezione = "<tr>
					<td><input type='checkbox' name='FILTERS' value='FILTERS' $check_filtri></td>
					<td class='text'>"._t("STAMPA_FILTRI_SELEZIONE")."</td>
				</tr>";
		}
*/
		$array_celle = array(
			"cell_1" => array(
				"TITLE" => _t("SCEGLI_TARGET"),
				"NAME" => "TARGET",
				"BODY" => array()
			),
			"cell_2" => array(
				"TITLE" => _t("SCEGLI_FORMATO"),
				"NAME" => "FORMAT",
				"BODY" => array()
			),
			"cell_3" => array(
				"TITLE" => "Orientamento pagina (PDF)",
				"NAME" => "ORIENTATION",
				"BODY" => array()
			),
/*				
			"cell_4" => array(
				"TITLE" => _t("SELEZIONI_AGGIUNTIVE"),
				"NAME" => "",
				"BODY" => "<table border='0' cellpadding='2'>
						$filtri_selezione
						<tr>
							<td><input type='checkbox' name='ZIP' value='ZIP'></td>
							<td class='text'>"._t("FILE_COMPRESSO")."</td>
						</tr>
					</table>"
			)
*/			
			"cell_4" => array(
				"BODY" => "SEL_AGG"
			),
		);	
	
		// @todo sistemare: in alcuni casi non compaiono i settori TARGET e FORMAT perchè dice che
		// $exportArray['TARGET'] e $exportArray['TARGET'] non sono degli array
		// Es: Dettaglio promozioni in ABATE

		// ECCEZIONI TARGET FORMAT
		if (wi400Session::exist(wi400Session::$_TYPE_LIST, $idList)){
			$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
			$exportArray = $wi400List->getCanExport();
//			echo "EXPORT ARRAY:<pre>"; print_r($exportArray); echo "</pre>";
				
			if (is_array($exportArray) && isset($exportArray["TARGET"])){
//				echo "UNSET TARGETS<br>";
				foreach ($this->targets as $k => $t){
					if(!in_array($k, $exportArray["TARGET"])) 
						unset($this->targets[$k]);
				}
			}
			
			if (is_array($exportArray) && isset($exportArray["FORMAT"])){
//				echo "UNSET FORMATS<br>";
				foreach ($this->formats as $k => $f){
					if(!in_array($k, $exportArray["FORMAT"])) 
						unset($this->formats[$k]);
				}
			}
			
			if (is_array($exportArray) && isset($exportArray["ORIENTATION"])){
//				echo "UNSET ORIENTATION<br>";
				foreach ($this->orientation as $k => $f){
					if(!in_array($k, $exportArray["ORIENTATION"])) 
						unset($this->orientation[$k]);
				}
			}
		}
	
		// TARGET LIST
		foreach ($this->targets as $k => $t){
			$array_celle["cell_1"]["BODY"][] = $t;
		}
		
		//FORMAT LIST
		foreach ($this->formats as $k => $f){
			$array_celle["cell_2"]["BODY"][] = $f;
		}
		
		//ORIENTATION LIST
		foreach ($this->orientation as $k => $o){
			$array_celle["cell_3"]["BODY"][] = $o;
		}
		
		$i = 1;
		foreach($array_celle as $campo => $vals) {
//			echo "CELL ($campo) - VAL: ${$campo}<pre>"; print_r($array_celle[$campo]); echo "</pre>";
	
			$cella = array();
			if(isset($$campo) && !empty($$campo)) {
				$field_options = array();
				$check_val = "";
				
				if(isset(${$campo}['BODY'])) {
					if(is_array(${$campo}['BODY'])) {
						foreach(${$campo}['BODY'] as $k => $v) {
							$field_options[$v['VAL']] = $v['DES'];
							
							if(isset($v['CHECK']) && $v['CHECK']==true) {
								$check_val = $v['VAL'];
							}
						}
					}
					else {
						$cella['BODY'] = ${$campo}['BODY'];
					}
				}
				else {
					if(is_array($array_celle[$campo]['BODY'])) {
						foreach($array_celle[$campo]['BODY'] as $k => $v) {
							$field_options[$v['VAL']] = $v['DES'];
							
							if(isset($v['CHECK']) && $v['CHECK']==true) {
								$check_val = $v['VAL'];
							}
						}
					}
					else {
						$cella['BODY'] = $array_celle[$campo]['BODY'];
					}
				}
				
				if($cella['BODY']==="SEL_AGG") {
					if($i%2==0) {
						$myField = new wi400Text("VUOTO");
						$myField->setLabel("");
						$myField->setValue("");
						$exportDetail->addField($myField);
					}
						
					if($filtri_sel===true) {
/*					
						$myField = new wi400InputSwitch("FILTERS");
						$myField->setLabel(_t("STAMPA_FILTRI_SELEZIONE"));
						$myField->setOnLabel(_t('LABEL_YES'));
						$myField->setOffLabel(_t('LABEL_NO'));
//						$myField->setChecked($check_filtri);
//						$myField->setValue(1);
						$myField->setValue("FILTERS");
						$exportDetail->addField($myField);
*/						
						$this->cellFieldCheck($exportDetail, "FILTERS", _t("STAMPA_FILTRI_SELEZIONE"), "FILTERS", $check_filtri);
					}
						
					self::addZipSelection_NEW($exportDetail, $this->zip);
					
					continue;
				}
				
				if(isset(${$campo}['TITLE'])) {
					$cella['TITLE'] = ${$campo}['TITLE'];
				}
				else {
					$cella['TITLE'] = $array_celle[$campo]['TITLE'];
				}
				
				if(isset(${$campo}['NAME'])) {
					$cella['NAME'] = ${$campo}['NAME'];
				}
				else {
					$cella['NAME'] = $array_celle[$campo]['NAME'];
				}
				
				if(!empty($field_options)) {
/*					
					$fieldDetail = new wi400InputSelectCheckBox($cella['NAME']);
					$fieldDetail->setLabel($cella['TITLE']);
					$fieldDetail->setOptions($field_options);
//					$fieldDetail->setValue($check_val);
					$exportDetail->addField($fieldDetail);
*//*					
					$outHtml = "<table>";
					foreach($field_options as $k => $v) {
//						$selectRadio = new wi400InputRadio($k);
						$selectRadio = new wi400InputRadio($cella['NAME']);
						$selectRadio->setLabel($v);
						$selectRadio->setValue($k);
						if($k==$check_val)
							$selectRadio->setChecked(true);
							
						$outHtml .= "<tr><td>".$selectRadio->getHtml()."</td></tr>";
							
						$selectRadio->setValue($k);
					}
					$outHtml .= "</table>";
//					echo "CELL: $outHtml<br>";
						
					$myField = new wi400Text($cella['NAME']."_FIELD");
					$myField->setLabel($cella['TITLE']);
					$myField->setValue($outHtml);
					$exportDetail->addField($myField);
*//*					
					$mySelect = new wi400InputSelect($cella['NAME']);
					$mySelect->setLabel($cella['TITLE']);
//					$mySelect->setFirstLabel("Seleziona...");
					$mySelect->setOptions($field_options);
					$mySelect->setValue($check_val);
					$exportDetail->addField($mySelect);
*/					
					$this->cellFieldSel($exportDetail, $cella['NAME'], $cella['TITLE'], $field_options, $check_val);
				}
				else {
					$myField = new wi400Text($cella['NAME']."_FIELD");
					$myField->setLabel($cella['TITLE']);
					$myField->setValue($cella['BODY']);
					$exportDetail->addField($myField);
				}
			}
			else {
				$myField = new wi400Text("VUOTO");
				$myField->setLabel("");
				$myField->setValue("");
				$exportDetail->addField($myField);
			}
/*
			$fieldDetail = new wi400InputSelectCheckBox($cella['NAME']);
			$fieldDetail->setLabel($cella['TITLE']);
			$fieldDetail->setOptions($field_options);
//			$fieldDetail->setValue("TUTTE LE E-MAIL");
			$exportDetail->addField($fieldDetail);
*/			
			$i++;
		}

		if(!empty($idList)) {
			$hiddenField = new wi400InputHidden("IDLIST");
			$hiddenField->setValue($idList);
			$exportDetail->addField($hiddenField);
		}
		
		if(!empty($this->idDetails)) {
			$details = serialize($this->idDetails);
				
			$hiddenField = new wi400InputHidden("ID_DETAILS");
			$hiddenField->setValue($details);
			$exportDetail->addField($hiddenField);
		}
		
		foreach($this->parameters as $key => $val) {
			$hiddenField = new wi400InputHidden($key);
			$hiddenField->setValue($val);
			$exportDetail->addField($hiddenField);
		}
		
		$exportDetail->dispose();	

		if($this->can_batch===true) {
			$check_batch = $this->batch;
			
			$spacer = new wi400Spacer();
			$spacer->dispose();
			
			if(!isset($idDet) || empty($idDet)) {
//				$idDet_2 = "EXPORT_LIST_".$idList."_BATCH_DET";
				$idDet_2 = $actionContext->getAction()."_BATCH_DET";
			}
//			echo "ID DETAIL - BATCH: $idDet_2<br>";
			
			$exportDetail = new wi400Detail($idDet_2);
			$exportDetail->setTitle("BATCH");
			$exportDetail->setColsNum(2);
			$exportDetail->isEditable(true);
//			$exportDetail->setSaveDetail(true);

			$myField = new wi400InputSwitch("BATCH");
			$myField->setLabel("BATCH");
			$myField->setOnLabel(_t('LABEL_YES'));
			$myField->setOffLabel(_t('LABEL_NO'));
			$myField->setChecked($check_batch);
//			$myField->setValue(1);
			$myField->setValue("BATCH");
			$exportDetail->addField($myField);
			
			$this->addNotificationSelections_NEW($exportDetail);
			
			$exportDetail->dispose();
		}
	}
	
	function cellFieldSel($exportDetail, $id, $label, $field_options, $check_val=null) {
/*
		$fieldDetail = new wi400InputSelectCheckBox($id);
		$fieldDetail->setLabel($label);
		$fieldDetail->setOptions($field_options);
//		$fieldDetail->setValue($check_val);
		$exportDetail->addField($fieldDetail);
*//*
		$outHtml = "<table>";
		foreach($field_options as $k => $v) {
//			$selectRadio = new wi400InputRadio($k);
			$selectRadio = new wi400InputRadio($id);
			$selectRadio->setLabel($v);
			$selectRadio->setValue($k);
			if($k==$check_val)
				$selectRadio->setChecked(true);
			
			$outHtml .= "<tr><td>".$selectRadio->getHtml()."</td></tr>";
			
			$selectRadio->setValue($k);
		}
		$outHtml .= "</table>";
//		echo "CELL: $outHtml<br>";
		
		$myField = new wi400Text($id."_FIELD");
		$myField->setLabel($label);
		$myField->setValue($outHtml);
		$exportDetail->addField($myField);
*/
//		echo "ID: $id - LABEL: $label - <font color='red'>SEL VAL: "; var_dump($check_val); echo "</font> - OPTIONS:<pre>"; print_r($field_options); echo "</pre>";
		
		$mySelect = new wi400InputSelect($id);
		$mySelect->setLabel($label);
//		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($field_options);
		$mySelect->setValue($check_val);
		$exportDetail->addField($mySelect);		
	}
	
	function cellFieldCheck($exportDetail, $id, $label, $value, $check_val=null) {
//		echo "ID: $id - LABEL: $label - <font color='red'>CHECK VAL: "; var_dump($check_val); echo "</font> - YES VALUE: $value<br>";
		
		$myField = new wi400InputSwitch($id);
		$myField->setLabel($label);
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setChecked($check_val);
//		$myField->setValue(1);
		$myField->setValue($value);
		$exportDetail->addField($myField);
	}
	
	/**
	 * Funzione per la scrittura di una riga di CSV
	 *
	 * @param unknown_type $fp	: file handler
	 * @param unknown_type $content	: dati da scrivere nel file CSV
	 * @param unknown_type $deliminator	: carattere di delimitazione dei dati CSV da utilizzare
	 * @param boolean $byKey : 	false se vengono scritti tutti gli elementi dell'array di dati (nell'ordine in cui si trovano),
	 * 							true se vengono scritti i dati dell'array secondo l'ordine di chiave numerica (da 0 alla chiave max presente, 
	 * 								con l'aggiunta automatica di campi vuoti nel caso di chiavi saltate)
	 * @return unknown		Ritorna il risultato della scrittura della riga sul file
	 */
	public function writeCsv($fp, $content=array(), $deliminator=";", $byKey=false) {
		$line = "";
		
		if(!empty($content)) {
			if($byKey===false) {
				foreach($content as $val) {
					$val = prepare_string_CSV($val);
					
					$line = $this->CsvLine($line, $val, $deliminator);
				}
			}
			else {
				$max_key = max(array_keys($content));
//				print_log("MAX KEY COLS: $max_key");
//				echo "CONTENT: "; print_r($content); echo "\r\n";
				for($i=0; $i<=$max_key; $i++) {
					if(array_key_exists($i, $content)) {
//						$valore = $content[$i];
						$valore = prepare_string_CSV($content[$i]);
						
						$line = $this->CsvLine($line, $valore, $deliminator);
					}
					else
						$line = $this->CsvLine($line, "", $deliminator);
				}
			}
			
			$line = substr($line, 0, (strlen($deliminator) * -1));
		}
		$line .= "\r\n";
//		print_log("RIGA: $line");
		return fwrite($fp, $line);
	}
	
	public function writeCsvRows($fp, $rows=array(), $deliminator=";", $byKey=true) {
		if(!empty($rows)) {
			if($byKey===false) {
				foreach($rows as $row) {
					$this->writeCsv($fp, $row, $deliminator, $byKey);
				}
			}
			else {
				$max_key = max(array_keys($rows));
//				print_log("MAX KEY ROWS: $max_key");
				for($j=0; $j<=$max_key; $j++) {
					if(array_key_exists($j, $rows))
						$this->writeCsv($fp, $rows[$j], $deliminator, $byKey);
					else
						$this->writeCsv($fp, "", $deliminator, $byKey);
				}
			}
		}
	}
	
	private function CsvLine($line="", $data="", $deliminator=";") {
		$val = str_replace("\r\n", "\n", html_entity_decode($data));
		$val = '"'.str_replace('"', '""', $val).'"';
		$line .= $val.$deliminator;
//		print_log("LINE: $line");
		
		return $line;
	}
	
	/**
	 * Stampa l'intestazione della prima pagina di un PDF
	 *
	 * @param Object $pdf		: oggetto FPDI
	 * @param integer $pagina	: posizionamento orizziontale del numero della pagina
	 * @param integer $char		: dimensione del carattere di stampa principale
	 */
	public function printNewPagePdf($pdf, $pagina, $char) {
		global $settings;
		
		if($this->template!==false) {
			if($this->template!="") {
				$tamplte = $this->template;
			}
			else {
//				$template = $settings['template_path'].'standard.pdf';
				$standard = _t('STANDARD_TEMPLATE_FILE');
				
				$orient = "";
				if($this->getExportOrientation()=="P")
					$orient = "_P";
				
				$template = $settings['template_path'].$standard.$orient.'.pdf';
			}
			
			$pdf->setSourceFile($template);
			$tplIdx = $pdf->importPage(1);
			$pdf->useTemplate($tplIdx,0,0);
		}

		if(!empty($this->logo_array)) {
			$this->print_logo($pdf);
		}
			
		$pdf->SetFont('Helvetica', 'B' ,4);
		//$pdf->setXY($this->colonna, 5);
		$pdf->setXY($this->start, 5);
		$pdf->write(2, prepare_string_PDF($this->identificativo));
		$pdf->SetFont('Courier', '' ,$char);	
		$pdf->setXY($pagina, 5);
		$pdf->write(4, "Pag.".$this->oldPage);
	}
	
	public function set_template($template) {
		$this->template = $template;
	}
	
	public function set_logo($logo_vals=array()) {
		$this->logo_array[] = array_combine(array("FILE","X","Y","W","H"), $logo_vals);
	}
	
	public function get_logos() {
		return $this->logo_array;
	}
	
	public function get_logo($num) {
		return $this->logo_array[$num];
	}
	
	private function print_logo($pdf,$logo_array=array()) {
		if(empty($logo_array)) {
			$logo_array = $this->logo_array;
		}
		
		foreach($logo_array as $logo) {
			$img_parts = pathinfo($logo['FILE']);
			$type = "";
			if(isset($img_parts['extension']))
				$type = strtolower($img_parts['extension']);
		
			$pdf->Image($logo['FILE'], $logo['X'], $logo['Y'], $logo['W'], $logo['H'], $type);
		}
	}
	
	public function print_page($pdf, $colonna, $start, $pagina, $char, $oldPage, $identificativo=null) {
		if(!isset($identificativo) || $identificativo=="") {
			if(isset($this->identificativo) && $this->identificativo!="") 
				$identificativo = $this->identificativo;
		}
		
		$pdf->SetFont('Courier', '' ,5);
		
		if(isset($identificativo) && $identificativo!="") {
			$pdf->setXY($colonna, $start);
			$pdf->write(5, prepare_string_PDF($identificativo));
		}
		
		$pdf->SetFont('Courier', '' ,$char);
		
		$pdf->setXY($pagina, $start);
		$pdf->write($char, "Pag. ".$oldPage);
		
		return $pdf;
	}
	
	/**
	 * Impostazione del PDF e stampa della prima pagina con i dati riepilogativi
	 *
	 * @param integer $char		: dimensione del carattere di stampa principale
	 * @param integer $pagina	: tipo formato della pagina
	 * 
	 * @return 	Ritorna l'oggetto FPDI
	 */
	public function createPDF($char, $pagina="A4", $direzione='L', $margin=15) {
		global $db, $routine_path, $settings;
			
		require_once $routine_path."/FPDF/tcpdf/tcpdf.php";
		require_once $routine_path."/FPDF/fpdi.php";			

//		$this->TypeImage = "pdf.png";
		$riga = $this->start;
		$this->oldPage = 1;
		
//		echo "PAGINA: "; var_dump($pagina); echo "<br>";
		
		$this->setExportOrientation($direzione);
		
		$pdf = new FPDI($direzione,"mm",$pagina);
		$pdf->SetFont('Courier', '' ,$char);
		$pdf->SetRightMargin(0);
		$pdf->SetAutoPageBreak(True, $margin);
		
//		$pdf->SetStartRowY($this->start);
		$pdf->SetTopMargin($this->start);

		$pdf->setPrintHeader(False);
		$pdf->setPrintFooter(False);

		return $pdf;
	}
	
	/**
	 * Impostazione standard dei dai iniziali del file PDF
	 *
	 */
	public function impostaDatiPDF() {
		$filename  = date("YmdHis")."_".$this->wi400List->getIdList().".pdf";
		$this->setDatiExport($filename,'export','pdf.png');
		
		$this->start = 14;
		$this->colonna = 4;
//		$this->identificativo = "Stampato con WI400 il ".date("d/m/Y"). " alle ore ".date("H:i:s") ." da utente ". $_SESSION['user'];	
		$this->identificativo = prepare_string_PDF(_t('W400019', array(date("d/m/Y"), date("H:i:s"), $_SESSION['user'])));
	}
	
	/**
	 * Impostazione dei dati iniziali del file PDF
	 *
	 * @param integer $start			: posizionamento veriticale da cui si comincia a fare riferimento
	 * @param integer $colonna			: posizionamento orizzontale da cui si comincia a fare riferimento
	 * @param string $identificativo	: nota da stampare (di solito al margine della pagina e conenente informazioni sul file)
	 */
	public function setDatiPDF($start, $colonna, $identificativo=null) {	
		$this->start = $start;
		$this->colonna = $colonna;
		if(isset($identificativo))
			$this->identificativo = $identificativo;
	}
	
	/**
	 * Impostazione dei dettagli del file PDF
	 *
	 * @param Object $pdf		: oggetto FPDI
	 * @param string $subject	: argomento del file che si sta generando
	 */
	public function dettagliPDF($pdf, $subject) {
		global $settings;
		
		$pdf->SetCreator('WI400-Siri Informatica s.r.l.');
		$pdf->SetAuthor($settings['cliente_installazione']);
		$pdf->SetSubject($subject);
	}
	
	public function set_print_order($print) {
		$this->print_order = $print;
	}
	
	public function get_print_order() {
		return $this->print_order;
	}
	
	/**
	 * Impostazione della prima pagina (copertina) del file PDF
	 *
	 * @param Object $pdf		: oggetto FPDI
	 * @param integer $pagina	: posizionamento orizziontale del numero della pagina
	 * @param integer $char		: dimensione del carattere di stampa principale
	 */
	public function stampaPrimaPaginaPDF($pdf, $char, $pagina, $listTitle="", $addPage=true) {
		$pag_or = $this->getExportOrientation();

//		$pdf->setPageOrientation($pag_or);
		
		// Stampo la prima pagina con i dati reipilogativi
		$pdf->AddPage($pag_or);
		$this->oldPage = $pdf->PageNo();
		$this->printNewPagePdf($pdf, $pagina, $char);
		
		$char_title = 14;
		$char_det = 10;

        // ******************************************
		// TITOLO
		// ******************************************
		$start2 = 30;
		$x_start = 20;
	    $pdf->SetFont('Courier', 'B' ,18);
		$pdf->SetXY($x_start,$start2);
		
		if($listTitle=="")
			$listTitle = $this->wi400List->getTitle();
		
	    $pdf->write(6, prepare_string_PDF($listTitle));
	    $first = True;
	    
	    $params = $this->wi400List->getParameters();
	    if(isset($params['HEADER_PARAM']) && !empty($params['HEADER_PARAM'])) {
	    	$start2 =$start2 + 10;
	    	foreach($params['HEADER_PARAM'] as $val) {
	    		if($start2>150) {
	    			$pdf->AddPage($pag_or);
	    			$start2 = 30;
	    		}
	    		else 
	    			$start2 =$start2 + 10;
				$pdf->SetXY($x_start,$start2);   
				$pdf->write(6, prepare_string_PDF($val));
	    	}
	    	$start2 =$start2 + 10;
	    }

	    // ******************************************
	    // FILTRI
	    // ******************************************   
		foreach ($this->wi400List->getFilters() as $filter){
			$filterValue = $filter->getValue();
			
//			if ($filter->getValue() != ""){
			if((is_array($filterValue) && !empty($filterValue)) || (!is_array($filterValue) && $filterValue!="")) {

				$filterLabel = $filter->getDescription();
				$filterCondition = "";
//				$filterValue = $filter->getValue();
//				echo "FILTER VALUE:<pre>"; print_r($filterValue); echo "</pre>";
			
				$filterOption = $filter->getOption();
				if ($filter->getType() == "STRING"){
					if ($filterOption == "EQUAL")   
						$filterCondition = " ".prepare_string_PDF(_t("UGUALE_A"))." ";
					if ($filterOption == "START")   
						$filterCondition = " ".prepare_string_PDF(_t("INIZIA_PER"))." ";
					if ($filterOption == "INCLUDE") 
						$filterCondition = " ".prepare_string_PDF(_t("CONTIENE"))." ";
				}else if ($filter->getType() == "NUMERIC"){
					$filterCondition = " "._t("UGUALE_A")." ";
				}else if ($filter->getType() == "CHECK_STRING"){
					$filterCondition = " ";
					$filterValue = "";
				}else if ($filter->getType() == "CHECK_NUMERIC"){
					$filterCondition = " ";
					$filterValue = "";
				}
				
				if(!is_array($filterValue))
					$filterValue = array($filterValue);
				
				foreach($filterValue as $val) {				
					if ($first) {
						if($start2>140) {
			    			$pdf->AddPage($pag_or);
			    			$start2 = 30;
			    		}
						else
							$start2 =$start2 + 10;
						$pdf->SetXY($x_start,$start2);   
						$pdf->SetFont('Courier', '' ,$char_title);
						$pdf->write(6, prepare_string_PDF(_t('FILTRI:')));
						$pdf->SetFont('Courier', '' ,$char_det);
						$first = False;
					}
					if($start2>150) {
		    			$pdf->AddPage($pag_or);
		    			$start2 = 30;
		    		}
		    		else
						$start2 =$start2 + 10;
					$pdf->SetXY($x_start+10,$start2);   
//					$filter = $filterLabel." ".$filterCondition." ".$filterValue;
					$filter = $filterLabel." ".$filterCondition." ".$val;
					$pdf->write(6, prepare_string_PDF($filter));
				}
			}
		}
		
		//***************************************************
		// ORDER BY
		//***************************************************	
		$order = $this->wi400List->getOrder();
		if ($order !="" && $this->get_print_order()===true) {
			if($start2>140) {
    			$pdf->AddPage($pag_or);
    			$this->oldPage = $pdf->PageNo();
				$this->printNewPagePdf($pdf, $pagina, $char);
    			$start2 = 30;
    		}
			else
				$start2 =$start2 + 10;
			$pdf->SetXY($x_start,$start2);
			$pdf->SetFont('Courier', '' ,$char_title);   
			$pdf->write(5, prepare_string_PDF(_t('ORDINAMENTO:')));
			$pdf->SetFont('Courier', '' ,$char_det);
			$start2 =$start2 + 10;
			$pdf->SetXY($x_start+10,$start2);   
//			$pdf->write(5, prepare_string_PDF($order));

			$col_label = "";

//			echo "ORDER: $order<br>";
			$order_array = explode(",", $order);
//			echo "ORDER ARRAY:<pre>"; print_r($order_array); echo "</pre>";

			if(!empty($order_array)) {
//				echo "COLS:<pre>"; print_r($this->wi400List->getCols()); echo "</pre>";
				$col_label_array = array();
				foreach($order_array as $ord) {
					$ord = trim($ord);
					$parts_ord = explode(" ", $ord);
					$campo = $parts_ord[0];
					if(count($parts_ord)>1)
						$sort = $parts_ord[1];
					
//					echo "ORD: $ord<br>";
//					$ord_col = $this->wi400List->getCol($ord);
					$cols = $this->wi400List->getCols();
					if(array_key_exists($campo, $cols)) {
						$ord_col = $cols[$campo];
//						echo "COL:<pre>"; print_r($ord_col); echo "</pre>";
						$des = $ord_col->getDescription();
//						echo "DES: $des<br>";die();
						$col_label_array[] = prepare_string_PDF($des);
					}
					else {
						$col_label_array[] = prepare_string_PDF($ord);
					}
				}
				
				if(!empty($col_label_array)) {
					$col_label = implode(", ", $col_label_array);
				}
			}
			
			$pdf->write(5, prepare_string_PDF($col_label));
		}
		
		// Stampo i filtri di selezione
		if($this->exportFilters===true)
			$this->printFilters($pdf, $char, $pagina, $start2, $pag_or);
		
		// Preparo la pagina da stampare
		if($addPage===true) {
			$pdf->SetFont('Courier', '' ,$char);
			$pdf->AddPage($pag_or);
		    $pdf->SetFillColor(198,226,255);		
		}
	}
	
	/**
	 * Stampa dei filtri di selezione utilizzati
	 *
	 * @param Object $pdf		: oggetto FPDI
	 * @param integer $pagina	: posizionamento orizziontale del numero della pagina
	 * @param integer $char		: dimensione del carattere di stampa principale
	 */
	public function printFilters($pdf, $char, $pagina, $start2, $pag_or="") {
		if(!empty($this->idDetails)) {
			$firstFilter = true;
			$char_title = 14;
			$char_det = 10;
			$x_start = 20;
			
			foreach($this->idDetails as $idDetail) {
				$detailFields = wi400Detail::getDetailFields($idDetail);
			
				foreach($detailFields as $idField => $fieldObj){
					$label = $fieldObj->getLabel(); // Etichetta
					$label = trim(prepare_string_PDF($label, true));
					if($label[strlen($label)-1]!=":") {
						$label .= ":";
					}

					$value = $fieldObj->getValue(); // Valore
//					$value = "TO PUB 2010<font color='#9999FF'> (Aperto per ordini ricorsivi) </font>";

//					$value = prepare_string($value);
						
//					echo $label." - ".$value."<br>";
		
					if($label!="" || $value!="") {
						if($start2>160 || $firstFilter===true) {
							$start2 = $start2 + 10;
							
							if($start2>160) {
				    			$pdf->AddPage($pag_or);
				    			$this->oldPage = $pdf->PageNo();
								$this->printNewPagePdf($pdf, $pagina, $char);
								$start2 = 30;
//			    				$pdf->SetFont('Courier', '' ,$char_det);
							}
			    			
			    			$pdf->SetXY($x_start,$start2);
			    			 
			    			$pdf->SetFont('Courier', '' ,$char_title);
			    			if($firstFilter===true) 
								$pdf->write(5, prepare_string_PDF(_t('PARAMETRI:')));
							else 
								$pdf->write(5, prepare_string_PDF(_t('PARAMETRI_CONTINUA:')));
							$pdf->SetFont('Courier', '' ,$char_det);
							
							$firstFilter = false;
							$start2 += 10;
			    		}
							
						$pdf->SetXY($x_start+10,$start2);
						
						if (strpos($value, "<br>")!==False) {
//							echo "LABEL: $label<br>";
							$stringhe = explode("<br>", $value);
							$isFirst = true;
							foreach($stringhe as $key => $val) {
								$val = trim(prepare_string_PDF($val, true));
								if($val=="")
									continue;
								if($isFirst===true) {
									$isFirst = false;
									$pdf->write(5, $label." ".prepare_string_PDF($val, true));
									$start2 += 10;
								}
								else {
									if($start2>160) {
						    			$pdf->AddPage($pag_or);
						    			$this->oldPage = $pdf->PageNo();
										$this->printNewPagePdf($pdf, $pagina, $char);
										$start2 = 30;
										$pdf->SetFont('Courier', '' ,$char_det);
									}
					
									$pdf->SetXY($x_start+10,$start2);
									$pdf->write(5, str_pad(' ',strlen($label))." ".prepare_string_PDF($val, true));
									
									$start2 += 10;
								}
							}
						}
						else {
							$pdf->write(5, $label." ".prepare_string_PDF($value, true));
							$start2 += 10;
						}
					}
				}
			}
		}
	}
	
}

?>