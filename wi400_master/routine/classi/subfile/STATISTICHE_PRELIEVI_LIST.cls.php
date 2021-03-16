<?php 

require_once $routine_path."/classi/wi400Graphs.cls.php";

class STATISTICHE_PRELIEVI_LIST extends wi400CustomSubfile {
	
	private $rtlt0703;
	
	public function __construct($parameters){
		global $db, $connzend;

		$this->rtlt0703 = new wi400Routine('RTLT0703', $connzend);
		$this->rtlt0703->load_description();
	}
	
	public function getArrayCampi() {	
		global $db;
		
		$array = array();
		$array['RSTRIN']=$db->singleColumns("1", "5", "", "Cod. Operatore");
		$array['OPERATORE']=$db->singleColumns("1", "100", "", "Nome Operatore");
		$array['COD_SQUADRA']=$db->singleColumns("1", "2", "", "Cod. Squadra");
		$array['DES_SQUADRA']=$db->singleColumns("1", "50", "", "Descrizione Squadra");
		$array['NUM_MIS']=$db->singleColumns("3", "9", 0, "Numero missioni");
		$array['COLLI_ASS']=$db->singleColumns("3", "9", 0, "Colli assegnati");
		$array['ROLL_ASS']=$db->singleColumns("3", "9", 0, "Roll assegnati");
		$array['COLLI_PREL']=$db->singleColumns("3", "9", 0, "Colli prelevati");
		$array['COLLI_MANC']=$db->singleColumns("3", "9", 0, "Colli mancanti");
		$array['ART_MANC']=$db->singleColumns("3", "9", 0, "Articoli mancanti");
		$array['PERC_PREL']=$db->singleColumns("3", "9", 2, "% Prelievo colli");
		$array['TEMPO']=$db->singleColumns("1", "50", "", "Tempo lavorato");
		$array['MEDIA']=$db->singleColumns("3", "20", 2, "Media collo/ora");
		$array['AREA']=$db->singleColumns("1", "2", "", "Area");
			$array['PESO_PREL']=$db->singleColumns("3", "15", 3, "Peso Colli prelevati");
			$array['MEDIA_PALLET']=$db->singleColumns("3", "20", 2, "Media pallet/ora");
		$array['PRE_COLLO_RIGA']=$db->singleColumns("3", "20", 2, "Prelievo collo riga");
		$array['ART_PRELEVATI']=$db->singleColumns("3", "20", 2, "Articoli prelevati");
		
		return $array;
	}
	
	public function init($parameters){
		global $db, $persTable;
		
		$this->setCols($this->getArrayCampi());
	}
	
	public function body($campi, $parameters){
		global $db, $persTable;

		$descr = "";
		if(isset($campi["RSTRIN"]) && !empty($campi["RSTRIN"])) {
//			echo "OPERATORE:".$campi["RSTRIN"]."<br>";
/*			
			$operatori = $persTable->decodifica('0703', $campi["RSTRIN"]);
			// $operatori['TABELLA']['T703DE'] e $operatori['DESCRIZIONE'] sono la stessa cosa
			// solo che TABELLA potrebbe non esserci, mentre DESCRIZIONE c'è sempre anche se vuota
//			$descr = $operatori['TABELLA']['T703DE'];
			$descr = $operatori['DESCRIZIONE'];
//			echo "DESC_1:$descr<br>";
*/
			$this->rtlt0703->prepare();
			$this->rtlt0703->set('COD_OPE',$campi['RSTRIN']);
			$this->rtlt0703->set('DATINV', date("Ymd"));
			$this->rtlt0703->call();
			$tab_0703 = $this->rtlt0703->get('TAB_0703');
			$descr = $tab_0703['T703DE'];
//			echo "DESC_2:$descr<br>";
		}
		
		if(isset($campi["T703SA"]) && !empty($campi["T703SA"])) {
			$squadra = $persTable->decodifica('0709', $campi["T703SA"]);
			// $squadra['TABELLA']['T709DE'] e $squadra['DESCRIZIONE'] sono la stessa cosa
			// solo che TABELLA potrebbe non esserci, mentre DESCRIZIONE c'è sempre anche se vuota
//			$des_squadra = $squadra['TABELLA']['T703DE'];
			$des_squadra = $squadra['DESCRIZIONE'];
		}
		
/*				
		$tempo = "";
		$time = mills_to_time($campi['TEMPO']);
*/	
		$tempo = $campi['TEMPO'];
		
		$media = 0;	
		if($campi['TEMPO']!=0) {
			// secondi totali
			$st = floor($campi['TEMPO']/1000000);
			// colli al secondo
			$ms = 0;
			if($st!=0)
				$ms = $campi['COLLI_PREL']/$st;
			// colli all'ora
			$media = $ms*(60*60);
			$media = round($media, 2);
		}
		
		$media_pallet = 0;
		if($campi['TEMPO']!=0) {
			// secondi totali
			$st = floor($campi['TEMPO']/1000000);
			// colli al secondo
			$mp = 0;
			if($st!=0)
				$mp = $campi['ROLL_ASS']/$st;
			// colli all'ora
			$media_pallet = $mp*(60*60);
			$media_pallet = round($media_pallet, 2);
		}
		
		$perc_prel = 0;
		if($campi["COLLI_ASS"]!=0) {
			$perc_prel = ($campi["COLLI_PREL"]*100)/$campi["COLLI_ASS"];
			$perc_prel = round($perc_prel, 2);
		}
		
		$writeRow = array(
			$campi['RSTRIN'],
			$descr,
			$campi['T703SA'],
			$des_squadra,
			$campi['NUM_MIS'],
			$campi['COLLI_ASS'],
			$campi['ROLL_ASS'],
			$campi['COLLI_PREL'],
			$campi['COLLI_MANC'],
			$campi['ART_MANC'],
//			$campi['PERC_PREL'],
				$perc_prel,
			$tempo,
			$media,
			$campi['T703ZN'],
				$campi['PESO_PREL'],
				$media_pallet,
			$campi['PRELIEVO_COLLO_RIGA'],
			$campi['ART_PRELEVATI']
		);
		
		return $writeRow;
	}
	
	public function end($subfile) {
		global $db;
	
		$totali = $subfile->getTotals();
	
		$sql = "SELECT sum(COLLI_PREL) SUM_COLLI_PREL, SUM(ART_PRELEVATI) SUM_ART_PRELEVATI FROM ".$subfile->getTable();
		$result = $db->query($sql);
		$row_sum = $db->fetch_array($result);
	
		$totali['PRE_COLLO_RIGA'] = $row_sum['SUM_COLLI_PREL']/$row_sum['SUM_ART_PRELEVATI'];
		//showArray($totali);
	
		$subfile->setTotals($totali);
	
		$subfile->setFinalized(True);
		wi400Session::save(wi400Session::$_TYPE_SUBFILE, $subfile->getIdTable(), $subfile);
	
		/*$sql = "SELECT SUM(PERCENTUALE) AS PERCENTUALE FROM ".$subfile->getTable()." WHERE TARTAA<>'N'";
			//echo $sql."__<br/>";
		$result = $db->query($sql);
		$row_sum = $db->fetch_array($result);
		$totals['PERCENTUALE'] = $row_sum['PERCENTUALE'];
		//$totals['TOLLERANZA'] = $row_sum['TOLLERANZA'];
		$subfile->setTotals($totals);
	
		$subfile->setFinalized(True);
		wi400Session::save(wi400Session::$_TYPE_SUBFILE, $subfile->getIdTable(), $subfile);*/
	}

}

?>