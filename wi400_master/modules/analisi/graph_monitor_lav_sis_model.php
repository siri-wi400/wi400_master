<?php

	require_once 'monitor_lavori_sistema_commons.php';
	
	$azione = $actionContext->getAction();
	$form = $actionContext->getForm();
	
	$filename = $tipo_dati_array[$form]."_monitor_lavori_sistema.png";
	
	$history->addCurrent();
	
//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
	
	$steps = $history->getSteps();
//	echo "STEPS:<pre>"; print_r($steps); echo "</pre><br>";
	$last_step = $steps[count($steps)-2];
//	echo "LAST STEP: $last_step<br>";

	$last_action_obj = $history->getAction($last_step);
	if (isset($last_action_obj)) {
		$last_action = $last_action_obj->getAction();
		$last_form = $last_action_obj->getForm();
	}
//	echo "LAST_ACTION: $last_action - LAST FORM: $last_form<br>";
	
	$data_ini = wi400Detail::getDetailValue($last_action."_SRC","DATA_INI");
	$data_fin = wi400Detail::getDetailValue($last_action."_SRC","DATA_FIN");
	$ora_ini = wi400Detail::getDetailValue($last_action."_SRC","ORA_INI");
	$ora_fin = wi400Detail::getDetailValue($last_action."_SRC","ORA_FIN");
	$tipo_int = wi400Detail::getDetailValue($last_action."_SRC","TIPO_INT");
	
	$sel_subsys = $_SESSION['MONITOR_LAVORI_SISTEMA_SUBSYS_ARRAY'];
//	echo "SOTTOSISTEMI:<pre>"; print_r($sel_subsys); echo "</pre>";
	
	if(in_array($form,array("MEDIA","PICCO"))) {
		$actionContext->setLabel('Grafico');
		
		// content="text/plain; charset=utf-8"
		require_once $routine_path.'/jpgraph/jpgraph.php';
		require_once $routine_path.'/jpgraph/jpgraph_line.php';
//		require_once $routine_path.'/jpgraph/jpgraph_colormap.inc.php';
//		require_once $routine_path.'/jpgraph/jpgraph_rgb.inc.php';
		
		// Size of the overall graph
		$width = 900;
		$height = 600;
		
		// Create the graph and set a scale.
		// These two calls are always required
		$graph = new Graph($width,$height);
		$graph->img->SetImgFormat('png');
//		$graph->SetShadow();
		$graph->SetFrame(false);
/*		
		$cm = new ColorMap();
		$cm->InitRGB($graph->img->rgb);
		$cm->SetMap(2);
		$n = $cm->SetNumColors(64);
		$colbuckets = $cm->GetBuckets();
//		$colbuckets = array_values($graph->img->rgb->rgb_table);		
//		echo "COL BUCKETS:<pre>"; print_r($colbuckets); echo "</pre>";
*/
		$graph->SetScale('textlin');

		// Setup margin and titles
		$graph->img->SetMargin(70,20,30,120);
		
		// Setup the X-axis
		$graph->xaxis->HideLine(false);
		$graph->xaxis->HideTicks(false,false);
		$graph->xaxis->SetWeight(1);
		$graph->xaxis->SetColor('black');
		
		// Setup the Y-axis
		$graph->yaxis->HideLine(false);
		$graph->yaxis->HideTicks(false,false);
		$graph->yaxis->SetWeight(2);
		$graph->yaxis->SetColor('black');
		
		// Setup the Y-grid
		$graph->ygrid->Show(true,true);
//		$graph->ygrid->SetColor('brown@0.7','gray');
		$graph->ygrid->SetWeight(1,1);
		$graph->ygrid->SetStyle('solid','dotted');
		 
		// Setup the X-grid
		$graph->xgrid->Show(true,true);
//		$graph->xgrid->SetColor('darkgreen@0.8','gray');
		$graph->xgrid->SetWeight(1,1);
		$graph->xgrid->SetStyle('solid','dashed');
		 
		// Draw the grid on top to the line plot
		$graph->SetGridDepth(DEPTH_BACK);
		
		// Use 50% blending 
		$graph->ygrid->SetFill(true,'#EFEFEF@0.9','#BBCCFF@0.9');
			
//		$graph->SetBackgroundGradient('green:0.8','orange:0.97',GRAD_HOR,BGRAD_PLOT);
			
		$graph->title->Set("Monitor dei lavori di sistema");
		$graph->title->SetFont(FF_FONT2, FS_BOLD);
		
		$graph->subtitle->Set("Dal $data_ini al $data_fin - Dalle $ora_ini alle $ora_fin");
		$graph->subtitle->SetFont(FF_FONT1, FS_NORMAL);
		
		$graph->xaxis->title->Set($int_axis_array[$tipo_int]);
		$graph->xaxis->SetTitlemargin(85); 
		$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);
		// Make sure that the X-axis is always at the bottom of the scale
		// (By default the X-axis is alwys positioned at Y=0 so if the scale
		// doesn't happen to include 0 the axis will not be shown)
		$graph->xaxis->SetPos('min');
		
		$graph->yaxis->title->Set('Lavori');
		$graph->yaxis->SetTitlemargin(40); 
		$graph->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
		$graph->yaxis->SetPos(0);

		$int_ini = time_to_timestamp($data_ini, $ora_ini.":00");
		$int_fin = time_to_timestamp($data_fin, $ora_fin.":00");
//		echo "INT INI: $int_ini - INT FIN: $int_fin<br>";

		$sql = get_sql($int_ini, $int_fin, $sel_subsys, $tipo_int, array($form));
//		echo "SQL: $sql<br>";
		
		$result = $db->query($sql,false,0);
		
		$labels = array();
		$lavori = array();
		$i = -1;
		while($row = $db->fetch_array($result)) {
			if(!array_key_exists($row['DATA'],$labels)) {
				$labels[$row['DATA']] = interpret_data($row['DATA'], $tipo_int);
				
				$i++;
				
				foreach($sel_subsys as $subsys) {
					$lavori[$subsys][$i] = 0;
				}
			}
			
			if($row[$form]!=null)
				$lavori[$row['MONSBS']][$i] = $row[$form];
		}
//		echo "LABELS:<pre>"; print_r($labels); echo "</pre>";
//		echo "LAVORI:<pre>"; print_r($lavori); echo "</pre>";

		if(count($labels)<2) {
			$messageContext->addMessage("ERROR","Serve un numero di risultati più grande per poter creare il grafico");
			$actionContext->onError($last_action,"LIST","","",true);
		}
		
		// Linee dei lavori
		$i = 0;
		foreach($lavori as $key => $val) {
			// Create the data series
			$lineplot = new LinePlot($val);
			
			// Add the plot to the graph
			$graph->Add($lineplot);
			
			$lineplot->SetLegend($key);
			$lineplot->SetWeight(2);   // Two pixel wide
			
//			$lineplot->SetColor($colbuckets[$i]);
//			$i += $n/count($lavori);
//			$lineplot->SetColor($colbuckets[$i++]);
		}
		
		// Adjust the legend position
//		$graph->legend->Pos(0.05,0.5,'right','center');
		// Adjust the legend position
		$graph->legend->SetLayout(LEGEND_HOR);
		$graph->legend->SetColumns(count($sel_subsys)/2);
		$graph->legend->Pos(0.5,0.9999,"center","bottom");
			
		$graph->xaxis->SetFont(FF_FONT0);
		$graph->xaxis->SetTickLabels(array_values($labels));
		
		// Set the angle for the labels to 90 degrees
		$graph->xaxis->SetLabelAngle(90);		
	}

?>