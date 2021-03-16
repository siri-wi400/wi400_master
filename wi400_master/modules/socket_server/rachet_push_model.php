<?php
//showArray($_SERVER);
/* START:/usr/local/zendphp7/bin/php /www/zendsvr/htdocs/WI400_LZOVI/cli.php appBase=WI400_LZOVI user=PHPMAN action=RACHET_PUSH
**/
// Connessione a RACHET SERVER 

// Ciclo infinito sulla coda dei processi PUSH ed invio dei messaggi

// Handling dei messaggi ...


/*
 based on: http://stackoverflow.com/questions/7160899/websocket-client-in-php/16608429#16608429
 FIRST EXEC PYTHON SCRIPT TO GET HEADERS
 */
/*$ws = new ws(array
		(
				'host' => '127.0.0.1',
				'port' => 8080,
				'path' => ''
		));*/
echo "<br>INIZIO SERVER PUSH!<br>";
try {
	$ws = new WebsocketClient('127.0.0.1','8080');
} catch (Exception $e) {
	die("ERRORE WEBSOCKET:".$e->getMessage());
}
// @todo 
require_once $moduli_path."/socket_server/rachet_server_function.php";
$id =webs_create_token();
$connection_string=array("action"=>"INIT", "token"=>"$id", "ip"=>"10.0.40.1", "user"=>"PHPMAN", "request"=>"CLIENT", "id"=>"dfsljflksj", "sender"=>"CLIENT");
$connection_string=base64_encode(json_encode($connection_string));
$result = $ws->sendData($connection_string);
$result = json_decode(base64_decode($result));
// Directory di lavoro
$file = wi400File::getCommonFile("process", "");
while (True) {
	if ($handle = opendir($file)) {
		//echo "Directory handle: $handle\n";
		//echo "Entries:\n";
		
		/* This is the correct way to loop over the directory. */
		while (false !== ($entry = readdir($handle))) {
			//echo "$entry\n";
			if ($entry!=".." && $entry!=".") {
				$raw = file_get_contents($file.$entry);
				$dati = json_decode($raw);
				if (isset($dati['CONNID'])) {
					$connection_string2=array("sender"=>"CONSOLE", 'action'=>"MSG", "to"=>array("connid"=>$dati['CONNID']), "msg"=>$dati['MSG']);
					$connection_string2=base64_encode(json_encode($connection_string2));
					$result = $ws->sendData($connection_string2);
				} else {
					// Se è definito il processo devo recuperare l'id connessione
					if (isset($dati['PROCESSID'])) {
						$connection_string2=array("sender"=>"CONSOLE", 'action'=>"GET_CLIENT_BY_ID_PROCESS", "processId"=>$dati['PROCESSID'], "msg"=>$dati['MSG']);
						$connection_string2=base64_encode(json_encode($connection_string2));
						$result = $ws->sendData($connection_string2);
						$reply = json_decode($result, True);
						$connId = $reply["MSG"];						
						$connection_string2=array("sender"=>"CONSOLE", 'action'=>"MSG", "to"=>array("connid"=>$dati['CONNID']), "msg"=>$dati['MSG']);
						$connection_string2=base64_encode(json_encode($connection_string2));
						$result = $ws->sendData($connection_string2);
					}
				}
				unlink($file.$entry);
			}
		}
		closedir($handle);
	}
	sleep(1);
}
$ws->close();
echo $result;
/**
 * Very basic websocket client.
 * Supporting handshake from drafts:
 *	draft-hixie-thewebsocketprotocol-76
 *	draft-ietf-hybi-thewebsocketprotocol-00
 *
 * @author Simon Samtleben
 * @version 2011-09-15
 */

class WebsocketClient
{
	private $_Socket = null;
	
	public function __construct($host, $port)
	{
		$this->_connect($host, $port);
	}
	
	public function __destruct()
	{
		$this->_disconnect();
	}
	
	public function sendData($data)
	{
		// send actual data:
		if (fwrite($this->_Socket, "\x00" . $data . "\xff" )) {
			//
		} else	{
			throw new Exception('Error:' . $errno . ':' . $errstr);
		}
		$wsData = fread($this->_Socket, 2000);
		$retData = trim($wsData,"\x00\xff");
		return $retData;
	}
	
	private function _connect($host, $port)
	{
		$key1 = $this->_generateRandomString(32);
		$key2 = $this->_generateRandomString(32);
		$key3 = $this->_generateRandomString(8, false, true);
		
		$header = "GET /echo HTTP/1.1\r\n";
		$header.= "Upgrade: WebSocket\r\n";
		$header.= "Connection: Upgrade\r\n";
		$header.= "Host: ".$host.":".$port."\r\n";
		$header.= "Origin: http://foobar.com\r\n";
		$header.= "Sec-WebSocket-Key1: " . $key1 . "\r\n";
		$header.= "Sec-WebSocket-Key2: " . $key2 . "\r\n";
		$header.= "\r\n";
		$header.= $key3;
		
		
		$this->_Socket = fsockopen($host, $port, $errno, $errstr, 2);
		if (fwrite($this->_Socket, $header)) {
			//	
		} else	{
			throw new Exception('Error:' . $errno . ':' . $errstr);
		}
		$response = fread($this->_Socket, 2000);
		
		/**
		 * @todo: check response here. Currently not implemented cause "2 key handshake" is already deprecated.
		 * See: http://en.wikipedia.org/wiki/WebSocket#WebSocket_Protocol_Handshake
		 */
		
		return true;
	}
	
	private function _disconnect()
	{
		fclose($this->_Socket);
	}
	
	private function _generateRandomString($length = 10, $addSpaces = true, $addNumbers = true)
	{
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"§$%&/()=[]{}';
		$useChars = array();
		// select some random chars:
		for($i = 0; $i < $length; $i++)
		{
			$useChars[] = $characters[mt_rand(0, strlen($characters)-1)];
		}
		// add spaces and numbers:
		if($addSpaces === true)
		{
			array_push($useChars, ' ', ' ', ' ', ' ', ' ', ' ');
		}
		if($addNumbers === true)
		{
			array_push($useChars, rand(0,9), rand(0,9), rand(0,9));
		}
		shuffle($useChars);
		$randomString = trim(implode('', $useChars));
		$randomString = substr($randomString, 0, $length);
		return $randomString;
	}
}
class ws
{
	private $params;
	private $head;
	private $instance;
	
	public function __construct($params)
	{
		foreach($params as $key => $value)
			$this->params[$key] = $value;
			$local = "http://".$this->params['host'];
			if(isset($_SERVER['REMOTE_ADDR']))
				$local = "http://".$_SERVER['REMOTE_ADDR'];
				$this->head =	"GET / HTTP/1.1\r\n" .
						"Upgrade: websocket\r\n" .
						"Connection: Upgrade\r\n" .
						"Host: ".$this->params['host']."\r\n" .
						"Origin: ".$local."\r\n" .
						"Sec-WebSocket-Key: TyPfhFqWTjuw8eDAxdY8xg==\r\n" .
						"Sec-WebSocket-Version: 13\r\n";
	}
	public function send($method)
	{
		$this->head .= "Content-Length: ".strlen($method)."\r\n\r\n";
		$this->connect();
		fwrite($this->instance, $this->hybi10Encode($method));
		$wsdata = fread($this->instance, 2000);
		return $this->hybi10Decode($wsdata);
	}
	public function close()
	{
		if($this->instance)
		{
			fclose($this->instance);
			$this->instance = NULL;
		}
	}
	
	public function connect()
	{
		$sock = fsockopen($this->params['host'], $this->params['port'], $errno, $errstr, 2);
		//fwrite($sock, $this->head);
		//$headers = fread($sock, 2000);
		$this->instance = $sock;
	}
	
	private function hybi10Decode($data)
	{
		$bytes = $data;
		$dataLength = '';
		$mask = '';
		$coded_data = '';
		$decodedData = '';
		$secondByte = sprintf('%08b', ord($bytes[1]));
		$masked = ($secondByte[0]=='1') ? true : false;
		$dataLength = ($masked===true) ? ord($bytes[1]) & 127 : ord($bytes[1]);
		if ($masked===true)
		{
			if ($dataLength===126)
			{
				$mask = substr($bytes, 4, 4);
				$coded_data = substr($bytes, 8);
			}
			elseif ($dataLength===127)
			{
				$mask = substr($bytes, 10, 4);
				$coded_data = substr($bytes, 14);
			}
			else
			{
				$mask = substr($bytes, 2, 4);
				$coded_data = substr($bytes, 6);
			}
			for ($i = 0; $i<strlen($coded_data); $i++)
				$decodedData .= $coded_data[$i] ^ $mask[$i % 4];
		}
		else
		{
			if ($dataLength===126)
				$decodedData = substr($bytes, 4);
				elseif ($dataLength===127)
				$decodedData = substr($bytes, 10);
				else
					$decodedData = substr($bytes, 2);
		}
		return $decodedData;
	}
	private function hybi10Encode($payload, $type = 'text', $masked = true)
	{
		$frameHead = array();
		$frame = '';
		$payloadLength = strlen($payload);
		switch ($type)
		{
			case 'text' :
				// first byte indicates FIN, Text-Frame (10000001):
				$frameHead[0] = 129;
				break;
			case 'close' :
				// first byte indicates FIN, Close Frame(10001000):
				$frameHead[0] = 136;
				break;
			case 'ping' :
				// first byte indicates FIN, Ping frame (10001001):
				$frameHead[0] = 137;
				break;
			case 'pong' :
				// first byte indicates FIN, Pong frame (10001010):
				$frameHead[0] = 138;
				break;
		}
		// set mask and payload length (using 1, 3 or 9 bytes)
		if ($payloadLength>65535)
		{
			$payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
			$frameHead[1] = ($masked===true) ? 255 : 127;
			for ($i = 0; $i<8; $i++)
				$frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
				// most significant bit MUST be 0 (close connection if frame too big)
				if ($frameHead[2]>127)
				{
					$this->close(1004);
					return false;
				}
		}
		elseif ($payloadLength>125)
		{
			$payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
			$frameHead[1] = ($masked===true) ? 254 : 126;
			$frameHead[2] = bindec($payloadLengthBin[0]);
			$frameHead[3] = bindec($payloadLengthBin[1]);
		}
		else
			$frameHead[1] = ($masked===true) ? $payloadLength + 128 : $payloadLength;
			// convert frame-head to string:
			foreach (array_keys($frameHead) as $i)
				$frameHead[$i] = chr($frameHead[$i]);
				if ($masked===true)
				{
					// generate a random mask:
					$mask = array();
					for ($i = 0; $i<4; $i++)
						$mask[$i] = chr(rand(0, 255));
						$frameHead = array_merge($frameHead, $mask);
				}
				$frame = implode('', $frameHead);
				// append payload to frame:
				for ($i = 0; $i<$payloadLength; $i++)
					$frame .= ($masked===true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
					return $frame;
	}
}
