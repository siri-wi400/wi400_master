<?php
function checkFurto($targa) {
	// Chiamo il Web Services
	try{
		$url = 'http://coordinamento.mininterno.it/servpub/ver2/SCAR/ricerca_targa.asp?numeroTarga1='.$targa;
		$curl = curl_init();
		
		// setup headers - used the same headers from Firefox version 2.0.0.6
		// below was split up because php.net said the line was too long. :/
		$header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
		$header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
		$header[] = "Cache-Control: max-age=0";
		$header[] = "Connection: keep-alive";
		$header[] = "Keep-Alive: 300";
		$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
		$header[] = "Accept-Language: en-us,en;q=0.5";
		$header[] = "Pragma: "; //browsers keep this blank.
		
		//$proxy ="cvl:baldoria@10.0.69.12:3128";
		curl_setopt($curl, CURLOPT_URL, $url);
		//set options
		curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3'); 
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		//curl_setopt($curl, CURLOPT_PROXY, $proxy);
		curl_setopt($curl, CURLOPT_REFERER, 'http://www.google.com'); 
		curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate'); 
		curl_setopt($curl, CURLOPT_AUTOREFERER, true); 
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($curl, CURLOPT_TIMEOUT, 10); 
		//set data to be posted
		//curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);
		//perform our request
		$result = curl_exec($curl);
		
		/*$file = fopen('/home/crm/targhe/codiceHTML.txt', 'w');
		fwrite($file, "\r\nTesto: ".$result."\r\n");
		fclose($file);*/
		//echo $result;
		$isRubata = strpos($result, 'risulta una denuncia di</font> <font size="5" color="#ff0080">
				Furto
			</font>');
		$isRubata = intval($isRubata);
		
		$file = fopen('/home/crm/targhe/targa.txt', 'a');
		fwrite($file, "\r\nisRubata: ".$isRubata."\r\n");
		fclose($file);
		
		if($isRubata) {
			//echo $isRubata."<br/>";
			
			//Recupero la data del furto
			$stringa = '</font>
			<br>
			<font size="5" color="#000080">presso </font><font size="5" color="#ff0080">';
			$inizio = strpos($result, $stringa);
			//echo $inizio." // length: ".strlen($stringa)."<br/>";
			$giorno = substr($result, $inizio-15, 10);
			$giorno = str_replace("	", "", $giorno);
			
			//Recupero la stazione di polizia
			$stringa2 = '</font><font size="5" color="#000080">di </font><font size="5" color="#ff0080">';
			$fine = strpos($result, $stringa2);
			$stazione = substr($result, $inizio+(strlen($stringa)), $fine-($inizio+(strlen($stringa))));
			$stazione = str_replace(array("<br>","<br/>", "\r\n", "	"), array("","","", ""), $stazione);
			
			//Recupero il paese
			$inizio = $fine+(strlen($stringa2));
			$stringa = '</font>
		</p>
		<hr width="80%" color="#000080">';
			$fine = strpos($result, $stringa);
			$paese = substr($result, $inizio, $fine-$inizio);
			$paese = str_replace(array("<br>","<br/>", "\r\n", "	"), array("","","", ""), $paese);
			
			
			//Recupero il modello
			$stringa = 'Modello</font></p>
					</td>
					<td width="50%" height="21" align="middle"><p align=left valign=top ><font size=\'4\' color=\'#FF0000\'>';
			$inizio = strpos($result, $stringa);
			$inizio = $inizio+(strlen($stringa));
			
			$stringa2 = '</font></p></td>
				</tr>
				<tr>
					<td width="31%" nowrap height="21" align="middle"><p align="left"><font color="#000080" size="4">Fabbrica';
			$fine = strpos($result, $stringa2);
			$modello = substr($result, $inizio, $fine-$inizio);
			$modello = str_replace(array("<br>","<br/>", "\r\n", "	"), array("","","", ""), $modello);
			
			
			//Recupero la fabbrica
			$stringa = 'Fabbrica</font></p>
					</td>
					<td width="50%" height="21" align="middle"><p align=left valign=top ><font size=\'4\' color=\'#FF0000\'>';
			$inizio = strpos($result, $stringa);
			$inizio = $inizio+(strlen($stringa));
			$stringa2 = '</font></p></td>
				</tr>
				<tr>
					<td width="31%" nowrap height="21" align="middle"><p align="left"><font color="#000080" size="4">Tipo';
			$fine = strpos($result, $stringa2);
			//$o = substr($result, $fine, 200);
			//echo "<script>console.log('".$o."');</script>";
			$fabbrica = substr($result, $inizio, $fine-$inizio);
			$fabbrica = str_replace(array("<br>","<br/>", "\r\n", "	"), array("","","", ""), $fabbrica);
			//echo $fabbrica."<br/>";
			/*$file = fopen('/home/crm/targhe/targa.txt', 'a');
			fwrite($file, "\r\n".$giorno."-".$stazione."-".$paese."-".$modello."-".$fabbrica."\r\n");
			fclose($file);*/

			return array('giorno' => $giorno,
						'stazione' => $stazione,
						'paese' => $paese,
						'modello' => $modello,
						'fabbrica' => $fabbrica);
		}else {
			return -1;
		}
		//echo $result;
	} catch (SoapFault $exception) {
		return 0;
	}
}
?>