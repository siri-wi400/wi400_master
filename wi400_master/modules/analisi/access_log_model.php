<?php

	$azione = $actionContext->getAction();
	
	if($actionContext->getForm()!="DOWNLOAD_GRAFICO")
		$history->addCurrent();
		
	$steps = $history->getSteps();
//	echo "STEPS:<pre>"; print_r($steps); echo "</pre><br>";
		
	if(in_array($azione."_PERCENTUALE_ACCESSI",$steps)) {
		$filename = "grafico_percentuale_log_accessi.png";
	}
	if(in_array($azione."_ANDAMENTO_ACCESSI",$steps)) {
		$filename = "grafico_andamento_log_accessi.png";
	}
//	echo "FILENAME: $filename<br>";

	// Versione dei grafici senza link nell'immagine
	if($actionContext->getForm()=="PERCENTUALE_ACCESSI") {
		// content="text/plain; charset=utf-8"
		require_once $routine_path.'/jpgraph/jpgraph.php';
		require_once $routine_path.'/jpgraph/jpgraph_pie.php';
		require_once $routine_path.'/jpgraph/jpgraph_pie3d.php';
		
		// Azione corrente
		$actionContext->setLabel("Percentuale accessi");
		
		$data_ini = wi400Detail::getDetailValue("PERCENTUALE_ACCESSI_DETAIL","DATA_INI");
		$data_fin = wi400Detail::getDetailValue("PERCENTUALE_ACCESSI_DETAIL","DATA_FIN");
		
		$data_i = dateToTimestamp($data_ini, true);
		$data_f = dateToTimestamp($data_fin, true);
		
//		echo "DATA INI: $data_ini - DATA FIN: $data_fin<br>";
		if($data_ini!="" && $data_fin!="") {
/*			
			$sql = "select ZSUTE, count(*) NUM_ACC from ZSLOG 
			where ZTIME between '".dateToTimestamp($data_ini)."' and '".dateToTimestamp($data_fin)."'
			group by ZSUTE";
*/
			$data_i = dateFormat(getGiorno($data_ini), getMese($data_ini), getAnno($data_ini), '-', 'S');
			$data_f = dateFormat(getGiorno($data_fin), getMese($data_fin), getAnno($data_fin), '-', 'S');
			
			$sql = "select ZSUTE, count(*) NUM_ACC from ZSLOG
			where substr(char(ZTIME), 1, 10) between '$data_i' and '$data_f'
			group by ZSUTE";
			$result = $db->query($sql);
			
			$label = array();
			$data = array();
			$i = 0;
			
			while($row = $db->fetch_array($result)) {
//				echo "ROW: "; print_r($row); echo "<br>";
			
				$label[$i] = $row['ZSUTE'];
				$data[$i] = $row['NUM_ACC'];
				$i++; 
			}
			
			// Size of the overall graph
			$width=700;
			$height=500;
			
			// A new graph
			$graph = new PieGraph($width,$height);
			$graph->img->SetImgFormat('png');
//			$graph->SetShadow();
			$graph->SetFrame(false);
		
			// Setup title
			$graph->title->Set("Grafico delle percentuali del log degli accessi");
//			$graph->title->SetFont(FF_FONT1,FS_BOLD);
			$graph->title->SetFont(FF_FONT2, FS_BOLD);
			
			$graph->subtitle->Set("Dal $data_ini al $data_fin");
			$graph->subtitle->SetFont(FF_FONT1, FS_NORMAL);

			// Create the plot
			$p1 = new PiePlot3D($data);
			$p1->SetAngle(50);
			$p1->SetSize(0.5);
			
			// Position the pie
			$p1->SetCenter(0.45);
	
			// Hare/Niemeyer Integer compensation for Pie Plots
//			$p1->SetLabelType(PIE_VALUE_ADJPERCENTAGE);
		
//			$p1->ExplodeSlice(1);
			// Explode all slices
			$p1->ExplodeAll(10);
		
			// Format the border around each slice
//			$p1->SetEdge('black', 1);
			$p1->SetEdge(false);
		
			// Setup slice labels and move them into the plot
			$p1->value->SetFont(FF_FONT1,FS_BOLD);
			$p1->value->SetColor("black");
			$p1->value->SetFormat('%01.2f%%');
			// Show the percetages for each slice
			$p1->value->Show();
//			$p1->SetLabelPos('auto');

			$p1->SetLegends($label);
			$graph->legend->Pos(0.02,0.2,'right','center');
			$graph->legend->SetLayout(LEGEND_VERT);
			$graph->legend->SetShadow('gray@0.4',2);
		 
			$graph->Add($p1);
		}
	}	
	else if($actionContext->getForm()=="ANDAMENTO_ACCESSI") {
		// content="text/plain; charset=utf-8"
		require_once $routine_path.'/jpgraph/jpgraph.php';
		require_once $routine_path.'/jpgraph/jpgraph_bar.php';
		
		// Azione corrente
		$actionContext->setLabel("Andamento accessi");
		
		$user = wi400Detail::getDetailValue("ANDAMENTO_ACCESSI_DETAIL","UTENTE");
		$data_ini = wi400Detail::getDetailValue("ANDAMENTO_ACCESSI_DETAIL","DATA_INI");
		$data_fin = wi400Detail::getDetailValue("ANDAMENTO_ACCESSI_DETAIL","DATA_FIN");
		
		$data_i = dateToTimestamp($data_ini, true);
		$data_f = dateToTimestamp($data_fin, true);
		
//		echo "USER: $user - DATA INI: $data_ini - DATA FIN: $data_fin<br>";
		if($user!="" && $data_ini!="" && $data_fin!="") {
			// Size of the overall graph
			$width=1000;
			$height=500;
		
			// Create the graph and set a scale.
			// These two calls are always required
			$graph = new Graph($width,$height);
			$graph->img->SetImgFormat('png');
//			$graph->SetShadow();
			$graph->SetFrame(false);
		
			$graph->SetScale('textlin');
			
			// Setup margin and titles
			$graph->img->SetMargin(100,20,50,50);
			
			$graph->title->Set('Andamento accessi');
			$graph->title->SetFont(FF_FONT2, FS_BOLD);
			$graph->subtitle->Set("Utente $user dal $data_ini al $data_fin");
			$graph->subtitle->SetFont(FF_FONT1, FS_NORMAL);
			
			$graph->xaxis->title->Set("Mesi");
			$graph->xaxis->SetTitlemargin(15); 
			$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);
			
			$graph->yaxis->title->Set('Accessi');
			$graph->yaxis->SetTitlemargin(60); 
			$graph->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
			$graph->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
/*			
			$sql = "select ZSUTE, count(*) NUM_ACC, substr(char(ZTIME), 1, 7) as MESE 
				from ZSLOG 
				where ZSUTE='$user' and 
					ZTIME between '".dateToTimestamp($data_ini)."' and '".dateToTimestamp($data_fin)."'	
				group by ZSUTE, substr(char(ZTIME), 1, 7)";
*/			
			$sql = "select ZSUTE, count(*) NUM_ACC, substr(char(ZTIME), 1, 7) as MESE
			from ZSLOG
			where ZSUTE='$user' and
			substr(char(ZTIME), 1, 10) between '$data_i' and '$data_f'
			group by ZSUTE, substr(char(ZTIME), 1, 7)";
			
			$result = $db->query($sql);
			
			$label = array();
			$data = array();
			$i = 0;
			
			while($row = $db->fetch_array($result)) {
//				echo "ROW: "; print_r($row); echo "<br>";
			
				$label[$i] = $row['MESE'];
				$data[$i] = str_replace("-","/",$row['NUM_ACC']);
				$i++; 
			}
		
			// Setup labels
			$graph->xaxis->SetTickLabels($label);
	
			$bplot = new BarPlot($data);
			
			// ...and add it to the graph
			$graph->Add($bplot);
			
			$bplot->SetFillColor("orange");
					 
			// ...and add it to the graph
//			$graph->Add($bplot);
		}
	}

	// Versione dei grafici con link nell'immagine
/*	
	if($actionContext->getForm()=="PERCENTUALE_ACCESSI") {
		// content="text/plain; charset=utf-8"
		require_once $routine_path.'/jpgraph/jpgraph.php';
		require_once $routine_path.'/jpgraph/jpgraph_pie.php';
		require_once $routine_path.'/jpgraph/jpgraph_pie3d.php';
		
		// Azione corrente
		$actionContext->setLabel("Percentuale accessi");
		
		$data_ini = wi400Detail::getDetailValue("PERCENTUALE_ACCESSI_DETAIL","DATA_INI");
		$data_fin = wi400Detail::getDetailValue("PERCENTUALE_ACCESSI_DETAIL","DATA_FIN");
		
//		echo "DATA INI: $data_ini - DATA FIN: $data_fin<br>";
		if($data_ini!="" && $data_fin!="") {
			$sql = "select ZSUTE, count(*) NUM_ACC from ZSLOG 
			where ZTIME between '".dateToTimestamp($data_ini)."' and '".dateToTimestamp($data_fin)."'
			group by ZSUTE";
			$result = $db->query($sql);
			
			$label = array();
			$data = array();
			$targets = array();
			$alts = array();
			$i = 0;
			
			$target_url = "index.php?t=".$azione."&f=ANDAMENTO_ACCESSI";
			
			while($row = $db->fetch_array($result)) {
//				echo "ROW: "; print_r($row); echo "<br>";
			
				$label[$i] = $row['ZSUTE'];
				$data[$i] = $row['NUM_ACC'];
				$targets[$i] = $target_url."#".$i;
				$alts[$i] = "val=%d";
				$i++; 
			}
			
			// Size of the overall graph
			$width=700;
			$height=500;
			
			// A new graph
			$graph = new PieGraph($width,$height);
			$graph->img->SetImgFormat('png');
//			$graph->SetShadow();
			$graph->SetFrame(false);
		
			// Setup title
			$graph->title->Set("Grafico delle percentuali del log degli accessi");
//			$graph->title->SetFont(FF_FONT1,FS_BOLD);
			$graph->title->SetFont(FF_FONT2, FS_BOLD);
			
			$graph->subtitle->Set("Dal $data_ini al $data_fin");
			$graph->subtitle->SetFont(FF_FONT1, FS_NORMAL);

			// Create the plot
			$p1 = new PiePlot3D($data);
			$p1->SetAngle(50);
			$p1->SetSize(0.5);
			
			$p1->SetCSIMTargets($targets,$alts);
			
			// Position the pie
			$p1->SetCenter(0.45);
	
			// Hare/Niemeyer Integer compensation for Pie Plots
//			$p1->SetLabelType(PIE_VALUE_ADJPERCENTAGE);
		
//			$p1->ExplodeSlice(1);
			// Explode all slices
			$p1->ExplodeAll(10);
		
			// Format the border around each slice
//			$p1->SetEdge('black', 1);
			$p1->SetEdge(false);
		
			// Setup slice labels and move them into the plot
			$p1->value->SetFont(FF_FONT1,FS_BOLD);
			$p1->value->SetColor("black");
			$p1->value->SetFormat('%01.2f%%');
			// Show the percetages for each slice
			$p1->value->Show();
//			$p1->SetLabelPos('auto');

			$p1->SetLegends($label);
			$graph->legend->Pos(0.02,0.2,'right','center');
		 
			$graph->Add($p1);
		}
	}
	else if($actionContext->getForm()=="ANDAMENTO_ACCESSI") {
		// content="text/plain; charset=utf-8"
		require_once $routine_path.'/jpgraph/jpgraph.php';
		require_once $routine_path.'/jpgraph/jpgraph_bar.php';
		
		// Azione corrente
		$actionContext->setLabel("Andamento accessi");
		
		$user = wi400Detail::getDetailValue("ANDAMENTO_ACCESSI_DETAIL","UTENTE");
		$data_ini = wi400Detail::getDetailValue("ANDAMENTO_ACCESSI_DETAIL","DATA_INI");
		$data_fin = wi400Detail::getDetailValue("ANDAMENTO_ACCESSI_DETAIL","DATA_FIN");
		
//		echo "USER: $user - DATA INI: $data_ini - DATA FIN: $data_fin<br>";
		if($user!="" && $data_ini!="" && $data_fin!="") {
			// Size of the overall graph
			$width=1000;
			$height=500;
		
			// Create the graph and set a scale.
			// These two calls are always required
			$graph = new Graph($width,$height);
			$graph->img->SetImgFormat('png');
//			$graph->SetShadow();
			$graph->SetFrame(false);
		
			$graph->SetScale('textlin');
			
			// Setup margin and titles
			$graph->img->SetMargin(100,20,50,50);
			
			$graph->title->Set('Andamento accessi');
			$graph->title->SetFont(FF_FONT2, FS_BOLD);
			$graph->subtitle->Set("Utente $user dal $data_ini al $data_fin");
			$graph->subtitle->SetFont(FF_FONT1, FS_NORMAL);
			
			$graph->xaxis->title->Set("Mesi");
			$graph->xaxis->SetTitlemargin(15); 
			$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);
			
			$graph->yaxis->title->Set('Accessi');
			$graph->yaxis->SetTitlemargin(60); 
			$graph->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
			$graph->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
			
			$sql = "select ZSUTE, count(*) NUM_ACC, substr(char(ZTIME), 1, 7) as MESE 
				from ZSLOG 
				where ZSUTE='$user' and 
					ZTIME between '".dateToTimestamp($data_ini)."' and '".dateToTimestamp($data_fin)."'	
				group by ZSUTE, substr(char(ZTIME), 1, 7)";
	
			$result = $db->query($sql);
			
			$label = array();
			$data = array();
			$targets = array();
			$alts = array();
			$i = 0;
			
			$target_url = "index.php?t=".$azione."&f=PERCENTUALE_ACCESSI";
			
			while($row = $db->fetch_array($result)) {
//				echo "ROW: "; print_r($row); echo "<br>";
			
				$label[$i] = $row['MESE'];
				$data[$i] = str_replace("-","/",$row['NUM_ACC']);
				$targets[$i] = $target_url."#".$i;
				$alts[$i] = "val=%d";
				$i++; 
			}
		
			// Setup labels
			$graph->xaxis->SetTickLabels($label);
	
			$bplot = new BarPlot($data);
			$bplot->SetCSIMTargets($targets,$alts);
			$bplot->SetFillColor("orange");
					 
			// ...and add it to the graph
			$graph->Add($bplot);
		}
	}
*/
?>