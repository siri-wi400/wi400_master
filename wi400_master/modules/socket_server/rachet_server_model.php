<?php

/*
 * START:/usr/local/zendphp7/bin/php /www/zendsvr/htdocs/WI400_LZOVI/cli.php appBase=WI400_LZOVI user=PHPMAN action=RACHET_SERVER
 * 
 * STRUTTURA MESSAGGI:
 * <xml>
 * <sender ="sender"> CONSOLE, CLIENT, WORKER, PCCLIENT // Solo IN FASE DI REGISTRAZIONE
 * <action='MSG'/> // INIT, OPEN_PROCESS, GETPROCESS, AJAX_REQUEST, LIST_CLIENT
 * <id="DFKSJLFDJSLJ"/>
 * <user="DSDKJSLFJ"/> // SOLO IN FASE DI REGISTRAZIONE
 * <ip="xxx.xxx.xxx.xxx" // Solo in fase di registrazione
 * <token="DLSAJFLDSKFJAFDLJ"/> // Chiave di sicurezza
 * <from>
 *    <connid=resource connection/> // Only for worker ?
 * </from>
 * <to>
 *    <ip="xxx.xxx.xxx.xxx"/>
 *    <user="yyyyyyyyyyyy"/>
 *    <connid=resrouce connection/> // Only for worker ?
 *    <uid=wwwwwwwwwwwwwww/> // Opzionale identificativo interno messaggio
 * </to>
 * <msg>
 *  raw data
 * </msg>
 * </xml>
 *  .. or JSON
 *  @todo
 *   * PUSH Server per processi, invio a chi sta in attesa il messaggio usiamo DTAQ?
 *   * OPEN Apertura di un processo su PC
 *   * CHAT Test di chat tra PC/CLIENT e CONSOLE per verificare le performance
 *   * ???? Come assegnare un TOKEN all'applicazione su PC
 *   * WORKER Gestione dei processi worker che eseguono elaborazioni BATCH
 */
namespace MyApp;
require $routine_path.'/vendor/autoload.php';
require_once $moduli_path."/socket_server/rachet_server_function.php";
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
	protected $clients;
	private $loop;
	private $users;
	private $errorCode;
	private $errorMessage;
	private $process = array();
	private $workers = array();
	protected $clients_data = array();
	private $messaggi = array(
			"0001"=> "Identificativo messaggio INIT non trovato",
			"0002"=> "Token non presente",
			"0003"=> "Id non valido",
			"0004"=> "Utente non valido",
			"0005"=> "Indirizzo IP non valido",
			"0006"=> "Tipo SENDER non previsto",
			"0007"=> "Token non Valido",
			"0008"=> "Token Generato, abilitare lato server"
	);

	public function setLastErrorCode($code) {
		$this->errorCode=$code;
		if (isset($this->messaggi[$code])) {
			$this->errorMessage = $this->messaggi[$code];
		} else {
			$this->errorMessage="";
		}
	}
	public function getLastErrorCode() {
		return $this->errorCode;
	}
	public function setLastErrorMessage($errorMessage) {
		$this->errorMessage=$errorMessage;
	}
	public function getLastErrorMessage() {
		return $this->errorMessage;
	}
	public function __construct(\React\EventLoop\LoopInterface $loop) {
		$this->clients = new \SplObjectStorage;
		$this->loop = $loop;
		$this->loop->addPeriodicTimer(5, function() {
			echo "\r\nNumero di client:".count($this->clients);
		    foreach ($this->clients as $client) { 
		    if (isset($this->clients_data[$client->resourceId])) {                 
		    	$client->send(webs_formatReply("CONSOLE", "PING", "OK", "1.0.0"));          
		    } else {
		    	// @todo Gestire la mancaza di dati .. se mi sto connettendo può essere che il processo non è ancora inizializzato
		    	//$client->send(webs_formatReply("KO", "NO SERVER DATA"));
		    	//unset($this->clients_data[$client->resourceId]);
		    	//unset($this->users[$client->resourceId]);
		    	//$this->clients->detach($client);
		    }
    }
		});
	}
	public function onOpen(ConnectionInterface $conn) {
		// Store the new connection to send messages to later
		$this->clients->attach($conn);
		$this->users[$conn->resourceId] = $conn;
		echo "\r\nNew connection! ({$conn->resourceId})\n";
	}

	public function onMessage(ConnectionInterface $from, $msg) {
	$numRecv = count($this->clients) - 1;
	//echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
    //       , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

            //foreach ($this->clients as $client) {
            //if ($from !== $client) {
            // ECHO Anche a se stesso ...
            // The sender is not the receiver, send to each client connected
                // La prima connessione deve mandare il messaggio di INIT altrimenti non posso proseguire
                if (!isset($this->clients_data[$from->resourceId])) {
                	// La stringa di inizializzazione sarà criptata ...
                	$msg = json_decode(base64_decode($msg));
 	                $message = $this->parseInitMessage($from, $msg);
	                if ($message==False) {
		                $replymsg="\r\nCONNESSIONE NON PERMESSA. VERIFICARE I PARAMETRI:".$this->getLastErrorCode()." - ".$this->getLastErrorMessage();
		                //$sessionId = $from->WebSocket->request->getCookies()['PHPSESSID'];
		                //echo "Session ID Client:".$sessionId;
		                echo "\r\n$replymsg";
		                $from->send(webs_formatReply("CONSOLE", "INIT", "KO", $replymsg));
		            	$this->clearArrayByResourceId($from->resourceId);
		            	$from->close();
	                } else {
	                	$replymsg="INIZIALIZZAZIONE CONNESSIONE EFFETTUATA";
	                	$from->send(webs_formatReply("CONSOLE", "INIT", "OK", $replymsg));
	                }
                } else {
                	echo "\r\nElaborazione ... ";
                	$message = json_decode(base64_decode($msg));
                	echo "\r\n----->".base64_decode($msg);
                	
                	//echo var_dump($message);
                	if (!isset($message->id)) {
                		$message->id=getCounterMessage();
                	}
                	log_message($message);
                	// Verifica messaggi arrivati la connessione è gia stata inoltrata
                	switch ($message->sender) {
                		case 'CONSOLE':
                			$this->consoleRequest($from, $message);
                			break;
                		case 'CLIENT':
                			$this->clientRequest($from, $message);
                			break;  
                		case 'PCCLIENT':
                			$this->clientRequest($from, $message);
                			break;  
                		case 'WORKER':
                			$this->workerRequest($from, $message);
                			break;
                		default:
                			$from->send(webs_formatReply("CONSOLE", "REPLY", "KO", "NO SENDER"));
                			break;
                	}
                }
            //}
            
            //}
            }

            public function onClose(ConnectionInterface $conn) {
            // The connection is closed, remove it, as we can no longer send it messages
            	$this->clients->detach($conn);
            	$this->clearArrayByResourceId($conn->resourceId);
            	echo "\r\nConnection {$conn->resourceId} has disconnected\n";
            }

            public function onError(ConnectionInterface $conn, \Exception $e) {
	            echo "\r\nAn error has occurred: {$e->getMessage()}\n";
	        	$conn->close();
		    }
		    public function consoleRequest($conn, $message) {
		    	//
		    	switch ($message->action) {
		    		case 'MSG':
		    			// Verifico a chi deve essere mandato il messaggio
		    			$client_conn = array();
		    			// Se è specificato connid connessione puntuale
		    			if (isset($message->to->log)) {
		    				echo "\r\nCONSOLE LOG->".$message->msg;
		    			}
		    			if (isset($message->to->connid)) {
		    				$client_conn[] = $message->to->connid;
		    			} else {
		    				// @todo rovesciare il ciclo e partire dai clients e verificare utenti e IP
		    				// Cerco per IP
		    				if (isset($message->to->ip)) {
		    					foreach ($this->clients_data as $key =>$value) {
		    						if ($value['IP']==$message->to->ip && $value['SENDER']=="CLIENT") {
		    							$cliet_conn[] = $key;
		    						}
		    					}
 		    				}
 		    				// Cerco per utente
 		    				if (isset($message->to->user)) {
 		    					foreach ($this->clients_data as $key =>$value) {
 		    						if ($value['USER']==$message->to->user && $value['SENDER']=="CLIENT") {
 		    							$client_conn[] = $key;
 		    						}
 		    					}
 		    				}
 		    				// Cerco X Sessione @todo da ottimizzare ma puntuale su quello che cerco
 		    				if (isset($message->to->session)) {
 		    					foreach ($this->clients_data as $key =>$value) {
 		    						if (isset($value['REGISTER'])) {
	 		    						foreach ($value['REGISTER'] as $key2 => $value2) {
	 		    							if ($value2==$message->to->session) {
	 		    								$client_conn[] = $key;
	 		    								break;
	 		    							}	
	 		    						}
 		    						}
 		    					}
 		    				}
 		    				
		    			}
		    			//echo var_dump($client_conn);
		    			//echo var_dump($this->users);
		    			if (count($client_conn)>0) {
			    			foreach ($client_conn as $key => $value) {
		 		    			$client_conn = $this->users[$value];
				    			if (isset($client_conn)) {
				    				echo "\r\nINVIO MESSAGGIO a $value";
				    				//$client_conn->send(webs_formatReply("OK", $message->msg, array("FROM"=>$message->from->connid)));
				    				$client_conn->send(base64_encode(json_encode($message)));
				    			} else {
				    				return false;
				    			}
			    			}
		    			}
		    			// Invio risposta
		    			//$conn->send("MESSAGGI INVIATI:".count($client_conn));
		    			$conn->send(webs_formatReply("CONSOLE", "MSG", "OK", $message->id));
		    			break;
	    			case 'LIST_CLIENT':
	    				echo "\r\nLISTS CLIENT .....";
	    				showArray($this->clients_data);
	    				$conn->send(webs_formatReply("CONSOLE", "LIST_CLIENT", "OK", $this->clients_data));
	    				break;
	    			// Registro la sessione arrivata	
	    			case 'REGISTER':
	    				echo "\r\nREGISTRO LA SESSIONE";
	    				$sessione = base64_decode($message->register);
	    				$telnet = base64_decode($message->telnet_type);
	    				$this->clients_data[$conn->resourceId]['REGISTER'][$sessione]=$sessione;
	    				$this->clients_data[$conn->resourceId]['CONFIG']["TELNET_TYPE"]=$telnet;
	    				$conn->send(webs_formatReply("CONSLE", "REGISTER", "OK", $message->id));
	    				break;
	    			case 'LIST_PROCESS':
	    				$conn->send(webs_formatReply("CONSOLE", "LIST_PROCESS", "OK", $this->process));
	    				break;
	    			case 'GET_CLIENT_BY_ID_PROCESS':
	    				$msg="";
	    				if (isset($this->process[$message->processId])) {
	    					$code = "OK";
	    					$msg = $this->process[$message->processId];
	    				} else {
	    					$code = "KO";
	    				}
	    				$conn->send(webs_formatReply("CONSOLE", "GET_CLIENT_BY_PROCESS_ID", $code, $msg));
	    				break;
	    			case 'GETPROCESS':
	    				// Reperisco percorso processi
	    				$fileName = wi400File::getCommonFile("process", $message.msg.".proc");
	    				echo "\r\nFILENAME:".$fileName;
	    				$dati = file_get_contents($fileName);
	    				// ?? $conn->send(array("MSG"=> $dati));
	    				break;
	    			case 'PUSH_SERVER':
	    				// Registrazione per ricevere messaggi PUSH in base ad un ID
	    				$this->process[$message->processId]=$conn->resourceId;
	    				echo var_dump($this->process);
	    				$conn->send(webs_formatReply("CONSOLE", "PUSH_SERVER", "OK", "REGISTER OK"));
	    				break;
	    			default:
	    				$from->send(webs_formatReply("CONSOLE", "ACTION", "KO", "NO VALID ACTION"));
	    				break;
		    	}
		    	return true;
		    }
		    public function workerRequest($conn, $message) {
		    	//
		    }
		    public function clientRequest($conn, $message) {
		    	//
		    	switch ($message->action) {
		    		case 'UPDATE_LIST_ROW':
		    			// Reperisco l'id della connessione a cui mandare il messaggio
		    			$client_conn = $this->users[$message->to];
		    			if (isset($client_conn)) {
		    				$client_conn->send($message->msg);
		    			} else {
		    				return false;
		    			}
		    			break;
		    			// Registro la sessione arrivata
		    		case 'REGISTER':
		    			/*echo "\r\nREGISTRO LA SESSIONE";
		    			$sessione = base64_decode($message->register);
		    			$this->clients_data[$conn->resourceId]['REGISTER'][$sessione]=$sessione;
		    			//$conn->send(webs_formatReply("OK", "", $this->clients_data));
		    			break;*/
		    			echo "\r\nREGISTRO LA SESSIONE";
		    			$sessione = base64_decode($message->register);
		    			$telnet = base64_decode($message->telnet_type);
		    			$this->clients_data[$conn->resourceId]['REGISTER'][$sessione]=$sessione;
		    			$this->clients_data[$conn->resourceId]['CONFIG']["TELNET_TYPE"]=$telnet;
		    			$conn->send(webs_formatReply("CONSLE", "REGISTER", "OK", $message->id));
		    			break;
		    		case 'REQUEST_UPGRADE':
		    			 echo "\r\nRICHIESTA UPGRADE DEL PROGRAMMA";
		    			 $conn->send(webs_upgradeReply());
		    		case 'LIST_CLIENT':
		    			break;
		    	}
		    	return true;
		    }
    public function parseInitMessage($conn, $message) {
    	global $db;
    	// Verifico se il messaggio di inizializzazione è conforme agli standard
    	echo var_dump($message);
    	//echo "\r\nDato:".$dato;
    	//$message_parts = explode("||", $dato);
    	//print_r($message_parts);
    	// Verifico se suffisso corretto
    	if ($message->action!="INIT") {
    		$this->setLastErrorCode("0001");
    		return false;
    	}
    	// Verifico Informazioni Passate come parametro
    	if (isset($message->token)) {
    		// Verifico se il TOKEN è autentico e valido
    		$dati = webs_check_token($message->token);
    		if ($dati['RESULT']==False) {
    			echo var_dump($dati);
    			// Se non trovato TOKEN ed è un PCCLIENT creo un TOKEN AUTOMATICO DISABILITATO
    			if ($message->sender=="PCCLIENT" && $dati['STATO']!="*DISABLED") {
    				$note="IP=".$message->ip."-"."INFO=".$message->info;
    				webs_create_token("*NOSCAD", "PCCLIENT", "STATIC", $message->token, "D", $note);
    				$this->setLastErrorCode("0008");
    				return false;
    			}
    			$this->setLastErrorCode("0007");
    			$this->setLastErrorMessage($dati['MSG']);
    			return false;
    		}
    		$this->clients_data[$conn->resourceId]['TOKEN']=$message->token;
    		$token_auto = $message->token;
    	} else {
    		$this->setLastErrorCode("0002");
    		return false;
    	}
    	// Verifico Informazioni Passate come parametro
    	if (isset($message->id)) {
    		$this->clients_data[$conn->resourceId]['ID']=$message->id;
    	} else {
    		$this->setLastErrorCode("0003");
    		return false;
    	}
    	// Utente
    	if (isset($message->user)) {
    		$this->clients_data[$conn->resourceId]['USER']=$message->user;
    	} else {
    		$this->setLastErrorCode("0004");
    		return false;
    	}
    	// Indirizzo IP
    	if (isset($message->ip)) {
    		$this->clients_data[$conn->resourceId]['IP']=$message->ip;
    	} else {
    		$this->setLastErrorCode("0005");
    		return false;
    	}
    	// @todo Verifico se il TOKEN Arrivato è conferme è può essere accettato, segnare con un flag che il record del DB è stato usato
    	$this->clients_data[$conn->resourceId]['CONNECTION']="ON";
    	// Se è un WORKER lo segnalo nell'array dei worker
    	if (isset($message->sender)) {
	    	$this->clients_data[$conn->resourceId]['SENDER']=$message->sender;
	    	if ($message->sender=="WORKER") {
	    		$this->worker[$conn->resourceId]=$conn->resourceId;
	    	}
    	} else {
    		$this->setLastErrorCode("0006");
    		return false;
    	}
    	return $message;
    }
    private function clearArrayByResourceId($id) {
    	// Pulizia WORKER
    	if (isset($this->worker[$id])) {
    		unsert($this->worker[$id]);
    	}
    	// Pulizia CLIENTS_DATA
    	if (isset($this->clients_data[$id])) {
    		unset($this->clients_data[$id]);
    	}
    	// Pulizia USERS
    	if (isset($this->users[$id])) {
    		unset($this->users[$id]);
    	}
    	// Pulizia Processi registrati per PUSH
    	if(($key = array_search($id, $this->process)) !== false) {
    		unset($process[$key]);
    	}
    }

}

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\Socket\Server as Reactor;
use MyApp\Chat;

$loop = \React\EventLoop\Factory::create();

/*$server = IoServer::factory(
		new HttpServer(
				new WsServer(
						new Chat()
				)
		),
		8080
);*/
$socket = new Reactor($loop);
$socket->listen(8080, "0.0.0.0");

$server = new IoServer(new HttpServer(new WsServer(new Chat($loop))), $socket, $loop);
/*$server->loop->addPeriodicTimer(5, function () use ($server) {
	$numcli = count($server->app->clients);
    if ($numcli>0) {
	    foreach ($server->app->clients as $client) {                  
	            $client->send("hello client");          
	    }
    }
});*/
echo "Starting Server ...\r\n";
$server->run();