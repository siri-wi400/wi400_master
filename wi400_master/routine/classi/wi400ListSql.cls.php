<?php

/**
 * @name wi400ExportListSql
 * @desc Classe per il recupero della query di una lista
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author Valeria Porrazzo
 * @version 1.00 30/04/2014
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400ListSql  {
	
	private $wi400List;
	private $totalFromSubfile;
	
	// DEFAULT true: filtro aggiunto normalmente (direttamente sulla query)
	// false: per permettere di filtrare campi non esistenti in tabella (ridenominati AS), viene creata una query con WITH, da impostare per query libere ($wi400List->setQuery();) tranne che per query con WITH
	private $autoFilter = true;
	
	// DEFAULT false
    // true: per permettere in caso di $autofilter = false; di eseguire la query senza trasformarla in una query con with
	private $execute_like_query = false;
	
	// DEFAULT false: tratta la query come al solito (il from rimane invariato)
	// true: per permettere l'alleggerimento della query di count delle righe (e di conseguenza delle pagine), si pulisce il from nella query da 'left join'
	private $cleanCount = false;
	
	private $hasFilter = false;
	
	private $logicalOperator = false;
	private $logicalOperatorGroup = "";
	
	private $select = "";
	private $from = "";
	private $filterWhere = "";
//	private $filterWhere_cond = "";
	private $where = "";
	private $group_by = "";
	private $order_by = "";
	
	private $query_parts = false;
	
	public function __construct($wi400List, $autoFilter=true, $totalFromSubfile=false) {
		$this->wi400List = $wi400List;
		$this->totalFromSubfile = $totalFromSubfile;		

		$this->autoFilter = $autoFilter;
		$this->execute_like_query = $wi400List->get_execute_like_query();		
//		echo "AUTO_FILTER: "; var_dump($this->autoFilter); echo "<br>";
//		echo "EXECUTE_LIKE_QUERY:"; var_dump($this->execute_like_query); echo "<br>";
	}
	
	public function prepare_query_parts($filters=array()) {
		$this->query_parts = true;
//		echo "PREPARE QUERY PARTS<br>";
		
		// FILTRI
		$filter = $this->get_filters($filters);
//		echo "<font color='green'>FILTER_WHERE:</font> $this->filterWhere<br>";
		
		// ORDINAMENTO ?????
		
		// COSTRUZIONE QUERY
		if($this->wi400List->getQuery()=="") {
//			echo "<font color='red'>NOT_QUERY</font><br>";
			
			// WHERE
			$this->where = "";
			
//			echo "GET WHERE: ".$this->wi400List->getWhere()."<br>";
			if($this->wi400List->getWhere()!="") {
				if($this->filterWhere=="" || $this->autoFilter===false) {
					$this->where = " WHERE ";
				}
				else {
/*					
//					echo "<font color='green'>LOGIGAL_OPERATOR</font>:"; var_dump($filter->getLogicalOperator()); echo "<br>";
//					echo "<font color='green'>LOGIGAL_OPERATOR_GROUP</font>:"; var_dump($filter->getLogicalOperatorGroup()); echo "<br>";
					if(!empty($filter) && $filter->getLogicalOperator()===wi400Filter::$LOGICAL_OPERATOR_OR) {
						$this->where = " WHERE (".$this->filterWhere;
						
						$this->logicalOperator = true;
					}
					else {
						$this->where = " WHERE ".$this->filterWhere;
					}
*/					
					$this->where = " WHERE ".$this->filterWhere;
			
					$this->where .= " AND ";
					
					$this->totalFromSubfile = False;
				}
				
				$this->where .= $this->wi400List->getWhere();
			}
			else if($this->filterWhere!="") {
/*				
				if(!empty($filter) && $filter->getLogicalOperator()===wi400Filter::$LOGICAL_OPERATOR_OR) {
					if($this->autoFilter===true)
						$this->where = " WHERE (".$this->filterWhere;
//					else
//						$this->filterWhere_cond = " WHERE (".$this->filterWhere;
					
					$this->logicalOperator = true;
				}
				else {
					if($this->autoFilter===true)
						$this->where = " WHERE ".$this->filterWhere;
//					else
//						$this->filterWhere_cond = " WHERE ".$this->filterWhere;
				}
*/				
				if($this->autoFilter===true)
					$this->where = " WHERE ".$this->filterWhere;
			}
/*
			// ORDER BY
			$this->order_by = "";
				
			if(is_array($this->wi400List->getOrder())) {		
				$firstTime = true;
				foreach($this->wi400List->getOrder() as $key => $type) {
					if($firstTime) {
						$firstTime = false;
						$this->order_by = " ORDER BY ";
					}
					else {
						$this->order_by .= ",";
					}
					
					$this->order_by .= " ".$key." ".$type;
				}
		
			}
			else if($this->wi400List->getOrder()!="") {
				$this->order_by = " ORDER BY ".$this->wi400List->getOrder();
			}
*/
		}
		else {
//			echo "<font color='query'>QUERY</font><br>";
			
			if($this->filterWhere!="") {
/*				
				if(!empty($filter) && $filter->getLogicalOperator()===wi400Filter::$LOGICAL_OPERATOR_OR) {
					if($this->autoFilter===true)
						$this->where = " WHERE (".$this->filterWhere;
//					else
//						$this->filterWhere_cond = " WHERE (".$this->filterWhere;
						
					$this->logicalOperator = true;
				}
				else {
					if($this->autoFilter===true)
						$this->where = " WHERE ".$this->filterWhere;
//					else
//						$this->filterWhere_cond = " WHERE ".$this->filterWhere;
				}
*/				
				if($this->autoFilter===true)
					$this->where = " WHERE (".$this->filterWhere;
			}
		}
		
		$this->where = trim($this->where);
		
		// ORDER BY
		$this->order_by = "";
		
		if(is_array($this->wi400List->getOrder())) {
			$firstTime = true;
			foreach($this->wi400List->getOrder() as $key => $type) {
				if($firstTime) {
					$firstTime = false;
					$this->order_by = " ORDER BY ";
				}
				else {
					$this->order_by .= ",";
				}
					
				$this->order_by .= " ".$key." ".$type;
			}
		
		}
		else if($this->wi400List->getOrder()!="") {
			$this->order_by = " ORDER BY ".$this->wi400List->getOrder();
		}
		
//		echo "<font color='orange'>WHERE:</font> ".$this->where."<br>";
//		echo "FILTER_WHERE:".$this->filterWhere_cond."<br>";
	}
/*	
	public function get_query($order=true, $count=false) {
		if($this->query_parts===false)
			$this->prepare_query_parts();
		
		// COSTRUZIONE QUERY
		if($this->wi400List->getQuery()!="") {
			$query = $this->wi400List->getQuery();
			
			// Se la query originale contiene già un WITH non è possibile eseguire la query per il conteggio delle righe totali,
			// quindi questa funzione non viene mai chiamata con $count=true (in wi400Pagination.php)
			if($count===true) {
				if($this->where!="") {				
					$query = "with SQL_QUERY_TABLE as ($query)
						select COUNT(*) as COUNTER
						from SQL_QUERY_TABLE ";
//					$query .= $this->filterWhere_cond;
//					$query .= $this->where;
					if($this->filterWhere!="")
						$query .= " where ".$this->filterWhere;
				}
				else {
					$query = "select COUNT(*) as COUNTER from ($query) AS SQL_QUERY_TABLE";
				}
			}
			else {
				if($this->autoFilter===false) {
//					if($this->where!="" || $this->order_by!="") {
					if($this->filterWhere!="" || $this->order_by!="") {
						$query = "with SQL_QUERY_TABLE as ($query)
							select *
							from SQL_QUERY_TABLE ";
//						$query .= $this->filterWhere_cond;
//						$query .= $this->where;
						if($this->filterWhere!="")
							$query .= " where ".$this->filterWhere;
						$query .= $this->order_by;
					}
				}
				else {
					foreach($this->wi400List->getCols() as $columnObj) {
						$columnObj->setSortable(false);
					}
				}
			}
		}
		else {
//			echo "COUNT:".$count."_AUTOFILTER:".$this->autoFilter."<br>";
			if($count===true && $this->autoFilter===true)
				$select = "SELECT COUNT(*) AS COUNTER";
			else {
				$select = "SELECT ".$this->wi400List->getField();
				$this->select = $select;
			}
				
			$this->from = " FROM ".$this->wi400List->getFrom();				
				
			if($this->wi400List->getGroup()!=""){
				$this->group_by = " GROUP BY ".$this->wi400List->getGroup();
			}
			
			$query = $select.$this->from;
			
//			if($this->autoFilter===true)
				$query .= " ".$this->where;
			
			$query .= $this->group_by;
			
//			if($order===true && $this->autoFilter===true && $count!==true)
			if(($order===true && $this->autoFilter===true && $count!==true) || $this->execute_like_query===true)
				$query .= $this->order_by;	
			
//			echo "<font color='green'>QUERY: </font>$query<br>";
//die();
			
			if($this->execute_like_query===true) {
				$this->wi400List->setQuery($query);
				$this->wi400List->setOrder("");
				$this->query_parts = false;
				$query = $this->get_query($order, $count);
			}
			else {
			if($count===true) {
				if($this->autoFilter===false) {
					$query = "with SQL_QUERY_TABLE as ($query)
						select COUNT(*) as COUNTER from SQL_QUERY_TABLE ";
//					$query .= $this->filterWhere_cond;
//					$query .= $this->where;
					if($this->filterWhere!="")
						$query .= " where ".$this->filterWhere;
				}
				else {
					if($this->wi400List->getGroup()!="") {
						$query = "select COUNT(*) as COUNTER from ($query) AS SQL_QUERY_TABLE";
					}
				}
			}
			else {
				if($this->autoFilter===false) {
					$query = "with SQL_QUERY_TABLE as ($query)
						select * from SQL_QUERY_TABLE ";
//					$query .= $this->filterWhere_cond;
//					$query .= $this->where;
					if($this->filterWhere!="")
						$query .= " where ".$this->filterWhere;
					$query .= $this->order_by;
				}
			}
			}			
		}
	
//		echo "<font color='green'>QUERY: </font>$query<br>";

		return $query;
	}
*/
	public function get_query($order=true, $count=false, $tot=false) {
		if($this->query_parts===false)
			$this->prepare_query_parts();
	
		$select_fields = "";
		if($count===true) {
			$select_fields = "COUNT(*) as COUNTER";
//			$this->order_by = "";
		}
		else if($tot!==false) {
			$select_fields = $tot;
		}
	
		// COSTRUZIONE QUERY
		if($this->wi400List->getQuery()!="") {
			$query = $this->wi400List->getQuery();
	
			// Se la query originale contiene già un WITH non è possibile eseguire la query per il conteggio delle righe totali,
			// quindi questa funzione non viene mai chiamata con $count=true (in wi400Pagination.php)
			if($count===true || $tot!==false) {
				if($this->where!="") {
					$query = "with SQL_QUERY_TABLE as ($query)
						select $select_fields
						from SQL_QUERY_TABLE ";
//					$query .= $this->filterWhere_cond;
//					$query .= $this->where;
/*
					// Aggiunta filtri di lista
					if($this->filterWhere!="")
						$query .= " where ".$this->filterWhere;
*/						
				}
				else {
					$query = "select $select_fields from ($query) AS SQL_QUERY_TABLE";
/*					
					// Aggiunta filtri di lista 
					if($this->filterWhere!="" && $this->execute_like_query===true)
						$query .= " where ".$this->filterWhere;
*/						
				}
				
				// Aggiunta filtri di lista
				if($this->filterWhere!="")
					$query .= " where ".$this->filterWhere;
			}
			else {
				if($this->autoFilter===false) {
//					if($this->where!="" || $this->order_by!="") {
					if($this->filterWhere!="" || $this->order_by!="") {
						$query = "with SQL_QUERY_TABLE as ($query)";
						$query .= " select *";
//						$query .= " SELECT ".$this->wi400List->getField();
						$query .= " from SQL_QUERY_TABLE ";
//						$query .= $this->filterWhere_cond;
//						$query .= $this->where;
						if($this->filterWhere!="")
							$query .= " where ".$this->filterWhere;
						$query .= $this->order_by;
					}
				}
				else {
					foreach($this->wi400List->getCols() as $columnObj) {
						$columnObj->setSortable(false);
					}
				}
			}
		}
		else {
//			echo "COUNT:".$count."_AUTOFILTER:".$this->autoFilter."<br>";
			if(($count===true || $tot!==false) && $this->autoFilter===true) {
				$select = "SELECT $select_fields";
			}
			else {
				$select = "SELECT ".$this->wi400List->getField();
				$this->select = $select;
			}
	
//			$this->from = " FROM ".$this->wi400List->getFrom();
	
			$from = $this->wi400List->getFrom();
			
			if($count===true) {
				if($this->get_cleanCount()===true) {				
					$pos = strpos($from, "left join");
					if($pos!==false) {
						$from = substr($from, 0, $pos);
					}
				}
			}
			
			$this->from = " FROM ".$from;
	
			if($this->wi400List->getGroup()!=""){
				$this->group_by = " GROUP BY ".$this->wi400List->getGroup();
			}
					
			$query = $select;
			if($count===true) {
				if($this->execute_like_query===true) {
					$query = $this->select;
				}
				else {
					if($this->autoFilter===false) {
						$query = $this->select;
					}
					else {
						if($this->wi400List->getGroup()!="") {
							$query = $this->select;
						}
					}
				}
			}
			
			$query .= $this->from;
	
//			if($this->autoFilter===true)
				$query .= " ".$this->where;
	
			$query .= $this->group_by;
	
//			if($order===true && $this->autoFilter===true && $count!==true)
//			if(($order===true && $this->autoFilter===true && $count!==true) || $this->execute_like_query===true)
			if(($order===true && $this->autoFilter===true && $count!==true && $tot===false) || $this->execute_like_query===true)
				$query .= $this->order_by;
					
//			echo "<font color='orange'>QUERY: </font>$query<br>";
//die();
					
			if($this->execute_like_query===true) {
				$this->wi400List->setQuery($query);
				$this->wi400List->setOrder("");
				$this->query_parts = false;
				$query = $this->get_query($order, $count);
			}
			else {
				if($count===true || $tot!==false) {
					if($this->autoFilter===false) {
						$query = "with SQL_QUERY_TABLE as ($query)
							select $select_fields from SQL_QUERY_TABLE ";
//						$query .= $this->filterWhere_cond;
//						$query .= $this->where;
						if($this->filterWhere!="")
							$query .= " where ".$this->filterWhere;
					}
					else {
						if($this->wi400List->getGroup()!="") {
							$query = "select $select_fields from ($query) AS SQL_QUERY_TABLE";
							
							// Aggiunta filtri di lista
							if($this->filterWhere!="")
								$query .= " where ".$this->filterWhere;
						}
					}
				}
				else {
					if($this->autoFilter===false) {
						$query = "with SQL_QUERY_TABLE as ($query)
							select * from SQL_QUERY_TABLE ";
//						$query .= $this->filterWhere_cond;
//						$query .= $this->where;
						if($this->filterWhere!="")
							$query .= " where ".$this->filterWhere;
						$query .= $this->order_by;
					}
				}
			}
		}
	
//		echo "<font color='green'>QUERY: </font>$query<br>";
		if ($count==True) {
			$findoff = strpos($query, "OFFSET ");
			if ($findoff > 0) {
				$findoffend = strpos($query, "ROWS", $findoff);
				$query = substr($query,0,$findoff-1).substr($query, $findoffend+4);
			}
		}
		return $query;
	}
						
	// Recupero query per paginazione con between (non possibile se la query originale contiene WITH, la lista va quindi settata $wi400List->setPagBetween(false))
	public function get_query_start($start, $end) {
		if($this->wi400List->getQuery()!="") {
			$query_no_order = $this->wi400List->getQuery();
			
//			echo "<font color='blue'>QUERY_LIST: </font>$query_no_order<br>";
		}
		else {
			$query_no_order = $this->select.$this->from;
			if($this->autoFilter===true)
				$query_no_order .= " ".$this->where;
			$query_no_order .= $this->group_by;
			
//			echo "<font color='blue'>QUERY_NO_ORDER: </font>$query_no_order<br>";
		}
		
		$query = "with SQL_QUERY_TABLE as ($query_no_order), ";
//		$query .= "SQL_QUERY_FILTER as (select * from SQL_QUERY_TABLE ".$this->filterWhere_cond."), ";
		$query .= "SQL_QUERY_FILTER as (select * from SQL_QUERY_TABLE ";
		if($this->autoFilter===false)
			$query .= $this->where;
		$query .= "), ";
		$query .= "TAB_RIGHE as (select * from SQL_QUERY_FILTER) ";
		$query .= "select *
			from (select row_number() over() as LIST_NREL, a.*
				from TAB_RIGHE a) as RN
			where RN.LIST_NREL between $start and $end";		
		$query .= $this->get_order_by();
		
//		echo "<font color='pink'>QUERY_START:</font> $query<br>";
		
		return $query;
	}
	
	public function get_query_limit_offset($start, $limit) {
		if($this->wi400List->getQuery()!="") {
			$query = $this->wi400List->getQuery();
//			echo "<font color='blue'>QUERY_LIST: </font>$query<br>";
		}
		else {
			$query = $this->get_query();
//			echo "<font color='blue'>QUERY: </font>$query_no_order<br>";
		}
	
		$query .= " limit $limit offset $start";	
//		echo "<font color='pink'>QUERY_START:</font> $query<br>";
	
		return $query;
	}
	
	public function get_filters($filters=array()) {
		$filterWhere = "";
		
		$this->hasFilter = "false";
		$this->logicalOperator = false;
		
		$filter = "";
		$filterWhere = "";
		
		$list_filters = $this->wi400List->getFilters();
		
		if(!empty($list_filters)) {
			foreach($list_filters as $filter) {
				if(isset($filters) && !empty($filters)) {
					$key_f = $filter->getId();
					
					if(!in_array($key_f, $filters))
						continue;
				}			
	
				$option = $filter->getOption();
				$valueToSearch = $filter->getValue();
				$filterType = $filter->getType();
					
				$filterKey = $filter->getkey();
				if($filter->getSqlKey()!=""){
					$filterKey = $filter->getSqlKey();
				}
/*				
				if(!empty($valueToSearch)) {
					echo "<font color='pink'>FILTER_WHERE:</font>$filterWhere<br>";
					echo "<font color='red'>FILTER_KEY:$filterKey</font><br>";
//					echo "FILTER_OPTION:$option<br>";
					echo "FILTER_VALUE:<pre>"; print_r($valueToSearch); echo "</pre>";
					echo "FILTER_TYPE:$filterType<br>";
					echo "LOGICAL_OPERATOR_FILTER:"; var_dump($filter->getLogicalOperator()); echo "<br>";
					echo "LOGICAL_OPERATOR_WHERE:"; var_dump($this->logicalOperator); echo "<br>";
					echo "LOGICAL_GROUP_FILTER:".$filter->getLogicalOperatorGroup()."<br>";
					echo "LOGICAL_GROUP_WHERE:".$this->logicalOperatorGroup."<br>";
				}
*/
				if((is_array($valueToSearch) && !empty($valueToSearch)) ||
					(!is_array($valueToSearch) && $valueToSearch!="") ||
					in_array($option, array("EMPTY", "NOT_EMPTY"))
				) {
					$this->totalFromSubfile = False;
					
					// Segnalo la presenza di filtri
					if(!$filter->getFast())
						$this->hasFilter = "true";
					
					if($filterWhere!="") {
						if(!$this->logicalOperator) {
							if($filter->getLogicalOperator()===wi400Filter::$LOGICAL_OPERATOR_OR) {
//								echo "<font color='blue'>OR</font><br>";
								$filterWhere .= " AND (";
								$this->logicalOperator = true;
								$this->logicalOperatorGroup = $filter->getLogicalOperatorGroup();
							}
							else {
//								echo "<font color='blue'>AND</font><br>";
								$filterWhere .= " AND ";
							}
						}
						else {
							if($filter->getLogicalOperator()===wi400Filter::$LOGICAL_OPERATOR_OR) {
//								echo "<font color='blue'>OR</font><br>";

								if($this->logicalOperatorGroup!=$filter->getLogicalOperatorGroup()) {
									$filterWhere .= ") AND (";
									$this->logicalOperatorGroup = $filter->getLogicalOperatorGroup();
								}
								else {								
									$filterWhere .= " OR ";
								}
							}
							else {
//								echo "<font color='blue'>AND</font><br>";
								$filterWhere .= ") AND ";
								$this->logicalOperator = false;
							}
						}
					}				
					else {				
						if($filter->getLogicalOperator()===wi400Filter::$LOGICAL_OPERATOR_OR) {
							$filterWhere = " (";
							$this->logicalOperator = true;
							$this->logicalOperatorGroup = $filter->getLogicalOperatorGroup();
						}
					}
					
					if($filter->getFunction()!="") {
						$filterWhere .= call_user_func("wi400_filter_".$filter->getFunction(),$filterKey, $valueToSearch, $option);
						continue;
					}

					if($filterType=="STRING") {
						if(!is_array($valueToSearch))
							$valueToSearch_array = array($valueToSearch);
						else
							$valueToSearch_array = $valueToSearch;
							
						$filterWhere .= "(";
						
						$isFirst = true;
						foreach($valueToSearch_array as $valueToSearch) {
							if($isFirst===true) {
								$isFirst = false;
							}
							else {
								$filterWhere .= " or ";
							}
/*						
							if(($filter->getCaseSensitive()==wi400Filter::$CASE_SENSITIVE_NONE ||
									$filter->getCaseSensitive()==wi400Filter::$CASE_SENSITIVE_INPUT)
							) {
								$filterKey = "UPPER(".$filterKey.")";
							}
						
							$filterWhere .= $filterKey;
						
							if(in_array($option, array("EQUAL", "EMPTY")))
								$filterWhere .= " = ";
								
							if(in_array($option, array("START", "INCLUDE", "END")))
								$filterWhere .= " LIKE ";
								
							if(in_array($option, array("NOT_START", "NOT_INCLUDE")))
								$filterWhere .= " NOT LIKE ";
								
							if(in_array($option, array("NOT_EQUAL","NOT_EMPTY")))
								$filterWhere .= " <> ";
						
							$filterWhere .= "'";
						
							if(in_array($option, array("INCLUDE","NOT_INCLUDE", "END")))
								$filterWhere .= "%";
						
							if(in_array($option, array("EMPTY","NOT_EMPTY"))) {
								$valueToSearch = "";
								$filter->setValue("");
							}
						
							$filterWhere .= $valueToSearch;
						
							if(in_array($option, array("START","INCLUDE","NOT_START","NOT_INCLUDE")))
								$filterWhere .= "%";
						
							$filterWhere .= "'";
*/							
							$case_sen = false;
							if(($filter->getCaseSensitive()==wi400Filter::$CASE_SENSITIVE_NONE ||
								$filter->getCaseSensitive()==wi400Filter::$CASE_SENSITIVE_INPUT)
							) {
								$case_sen = true;
							}
							
							if(in_array($option, array("EMPTY","NOT_EMPTY"))) {
								$filter->setValue("");
							}
							
							$filterWhere .= where_text_condition($option, $valueToSearch, $filterKey, $case_sen);
//							echo "OPTION_COND:".where_text_condition($option, $valueToSearch, $filterKey, $case_sen)."<br>";
						}
						
						$filterWhere .= ")";
					}
					else if($filterType=="NUMERIC") {
						$filterWhere .= $filterKey;
						$filterWhere .= $option.$valueToSearch;
					}
					else if($filterType=="CHECK_STRING") {
						$filterWhere .= $filterKey;
						$filterWhere .= " ".$valueToSearch." ";
					
					}
					else if($filterType=="CHECK_NUMERIC") {
						$filterWhere .= $filterKey;
						$filterWhere .= " ".$valueToSearch." ";
					}
					else if($filterType=="LOOKUP") {
						if(is_array($valueToSearch)) {
							$filterWhere .= $filterKey." in ('".implode("', '", $valueToSearch)."')";
						}
						else {
							$filterWhere .= $filterKey;
							$filterWhere .= " = ";
							$filterWhere .= "'";
							$filterWhere .= $valueToSearch;
							$filterWhere .= "'";
						}
					}
					else if($filterType=="SELECT") {
						$filterValuesCounter = 0;
						foreach($filter->getSource() as $keyFilter => $valueFilter) {
							if($filterValuesCounter."" == $valueToSearch.""){
								$valueToSearch = $keyFilter;
								break;
							}
							$filterValuesCounter++;
						}
							
						$filterWhere .= " ".$valueToSearch;
					}
					else if($filter->getType()=="TEXT_AREA") {
						$filterWhere .= $valueToSearch;
					}
					else if($filter->getType()=="USER_WHERE") {
						$filterWhere .= $valueToSearch;
					} 
					else if($filterType=="DATE" && $filter->getFunction()==""){
//						echo "FILTER DATE<br>";
				
						$filterWhere .= $filterKey;
						$filterWhere .= $option.dateViewToModel($valueToSearch);				
					}
					else if($filter->getFunction()!="") {
						$filterWhere .= call_user_func("wi400_filter_".$filter->getFunction(),$filterKey, $valueToSearch, $option);
					}		
				}
			}
			
			$filterWhere = $this->replace_markers($filterWhere);
		}
		
		// Chiusura eventuale gruppo logico
		if($this->logicalOperator) {
			$filterWhere .= ")";
			$this->logicalOperator = false;
		}
		
		$this->filterWhere = $filterWhere;
		
//		echo "<font color='orange'>FILTER_WHERE:</font>$filterWhere<br>";
		
		return $filter;
	}
	
	public function get_filterWhere() {
		return $this->filterWhere;
	}
	
	public function get_where() {
		return $this->where;
	}
	
	public function get_order_by() {
		return $this->order_by;
	}
	
	public function get_hasFilter() {
		return $this->hasFilter;
	}
	
	public function get_totalFromSubfile() {
		return $this->totalFromSubfile;
	}
	
	public function set_autoFilter($autoFilter) {
		$this->autoFilter = $autoFilter;
	}
	
	public function get_autoFilter() {
		return $this->autoFilter;
	}
	
	public function set_cleanCount($cleanCount) {
		$this->cleanCount = $cleanCount;
	}
	
	public function get_cleanCount() {
		return $this->cleanCount;
	}
	
	public function set_execute_like_query($like_query) {
		$this->execute_like_query = $like_query;
	}
	
	public function get_execute_like_query() {
		return $this->execute_like_query;
	}
	
	private function get_markers_array() {
		$array_markers = array(
			"SESSION[user]",
			"SESSION[cliente]",
			"SESSION[locale]",
		);
//		echo "LISTA_MARKERS:<pre>"; print_r($array_markers); echo "</pre>";

		return $array_markers;
	}
	
	private function check_markers($string, $markers=array()) {	
		$array_markers = $this->get_markers_array();
		
//		echo "STRING: $string<br>";
		
		$str_len = strlen($string);
		
		$pos_m = strpos($string, "##");
		if($pos_m!==false) {
			$pos_f = strpos($string, "##", $pos_m+2);
		
			$len = $pos_f-($pos_m+2);
//			echo "POS M: $pos_m - POS F: $pos_f - LEN: $len<br>";
		
			$mark = substr($string, $pos_m+2, $len);
//			echo "MARK:$mark<br>";

			if(in_array($mark, $array_markers)) {
				$markers[] = $mark;
			}
		
			$string = substr($string, $pos_f+2);
		
			$markers = $this->check_markers($string, $markers);
		}
		
		return $markers;
	}
	
	function replace_markers($string) {
		$markers = $this->check_markers($string);
//		echo "MARKERS:<pre>"; print_r($markers); echo "</pre>";
		
		if(!empty($markers)) {
			foreach($markers as $mark) {
				$arg = substr($mark, strlen("SESSION_"), -1);
				$value = $_SESSION["$arg"];
//				echo "SESSION_ARG:".$arg."_VALUE:".$value."<br>";
				
				$string = str_replace("##".$mark."##", $value, $string);
			}
			
//			echo "REPLACE_MARKERS:$string<br>";
		}
		
		return $string;
	}
	
}