<?php
/**
 * @name wi400AS400Constant
 * @desc Gestione della sessione 5250
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author
 * @version 0.02B 01/04/2018
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */
class wi400JobInfo {
	public $pgm="";
	public $pgm_lib="";
	public $display="";
	public $disaply_lib="";
	public $display_form="";
	public $display_form2="";
	public $display_form3="";
	
	public function __construct($data="") {
		if ($data!="") {
			$this->display =substr($data,0, 10);
			$this->display_form =substr($data,10, 10);
			$this->display_form2 =substr($data,20, 10);
			$this->display_form3 =substr($data,30, 10);
			$this->display_lib =substr($data,40, 10);
			$this->pgm =substr($data,50, 10);
			$this->pgm_lib =substr($data,60, 10);
		}
	}
}	
class wi400AS400Constant {
	const QRCVMSG = array("DSName"=>"QDATA", "DSParm"=>array(
			array("Name"=>"VTQTYP", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"10"),
			array("Name"=>"VTQID", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"2"),
			array("Name"=>"VTQHAN", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"16"),
			array("Name"=>"VTOID", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"40"),
			array("Name"=>"VTOPER", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"10"),
			array("Name"=>"FILLER", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"22"),
			array("Name"=>"VTCHAR", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"7900")));

	const QSNDMSG = array("DSName"=>"QDATA", "DSParm"=>array(
			array("Name"=>"CREADOP", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"3"),
			array("Name"=>"CERRORE", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"12"),
			array("Name"=>"CLEN", "IO"=>I5_INOUT,"Type"=>I5_TYPE_ZONED, "Length"=>"4.0"),
			array("Name"=>"CJOBDET", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"90"),
			array("Name"=>"CFILLER", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"91"),
			array("Name"=>"CDATA", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"9800")));
	
	const key = array(
			"BD"=>array("DESC"=>"Clear","HEX"=>"BD","KEYBOARD"=>False),
			"F1"=>array("DESC"=>"EnterorRecordAdv","HEX"=>"F1","KEYBOARD"=>True, "KEY" => "ENTER"),
			"F3"=>array("DESC"=>"Help","HEX"=>"F3","KEYBOARD"=>True),
			"ATTN"=>array("DESC"=>"Attn","HEX"=>"ATTN","KEYBOARD"=>True),
			"F4"=>array("DESC"=>"RollUp","HEX"=>"F5","KEYBOARD"=>True, "KEY" => "PAGEUP"),
			"F5"=>array("DESC"=>"RollDown","HEX"=>"F4","KEYBOARD"=>True, "KEY" => "PAGEDOWN"),
			"D9"=>array("DESC"=>"RollLeft","HEX"=>"D9","KEYBOARD"=>False),
			"D9"=>array("DESC"=>"RollRight","HEX"=>"DA","KEYBOARD"=>False),
			"F6"=>array("DESC"=>"Print","HEX"=>"F6","KEYBOARD"=>True),
			"F8"=>array("DESC"=>"RecordBackspace","HEX"=>"F8","KEYBOARD"=>True),
			"b98"=>array("DESC"=>"System line","HEX"=>"99","KEYBOARD"=>True, "KEY"=>'SHIFT+ESC', "SCRIPT" => "showSystemLine();"),
			"b99"=>array("DESC"=>"Nascondi System line","HEX"=>"99","KEYBOARD"=>True, "KEY"=>'ESC', "SCRIPT" => "hideSystemLine();"),
			"3F"=>array("DESC"=>"SLPAutoEnter","HEX"=>"3F","KEYBOARD"=>False),
			"50"=>array("DESC"=>"ForwardEdgeTriggerAuto","HEX"=>"50","KEYBOARD"=>False),
			"6C"=>array("DESC"=>"PA1","HEX"=>"6C","KEYBOARD"=>False),
			"6E"=>array("DESC"=>"PA2","HEX"=>"6E","KEYBOARD"=>False),
			"6B"=>array("DESC"=>"PA3","HEX"=>"6B","KEYBOARD"=>False),
			"31"=>array("DESC"=>"Cmd01","HEX"=>"31","KEYBOARD"=>True, "KEY"=>'F1'),
			"32"=>array("DESC"=>"Cmd02","HEX"=>"32","KEYBOARD"=>True, "KEY"=>'F2'),
			"33"=>array("DESC"=>"Cmd03","HEX"=>"33","KEYBOARD"=>True, "KEY"=>'F3'),
			"34"=>array("DESC"=>"Cmd04","HEX"=>"34","KEYBOARD"=>True, "KEY"=>'F4'),
			"35"=>array("DESC"=>"Cmd05","HEX"=>"35","KEYBOARD"=>True, "KEY"=>'F5'),
			"36"=>array("DESC"=>"Cmd06","HEX"=>"36","KEYBOARD"=>True, "KEY"=>'F6'),
			"37"=>array("DESC"=>"Cmd07","HEX"=>"37","KEYBOARD"=>True, "KEY"=>'F7'),
			"38"=>array("DESC"=>"Cmd08","HEX"=>"38","KEYBOARD"=>True, "KEY"=>'F8'),
			"39"=>array("DESC"=>"Cmd09","HEX"=>"39","KEYBOARD"=>True, "KEY"=>'F9'),
			"3A"=>array("DESC"=>"Cmd10","HEX"=>"3A","KEYBOARD"=>True, "KEY"=>'F10'),
			"3B"=>array("DESC"=>"Cmd11","HEX"=>"3B","KEYBOARD"=>True, "KEY"=>'F11'),
			"3C"=>array("DESC"=>"Cmd12","HEX"=>"3C","KEYBOARD"=>True, "KEY"=>'F12'),
			"B1"=>array("DESC"=>"Cmd13","HEX"=>"B1","KEYBOARD"=>True, "KEY"=>'SHIFT+F1'),
			"B2"=>array("DESC"=>"Cmd14","HEX"=>"B2","KEYBOARD"=>True, "KEY"=>'SHIFT+F2'),
			"B3"=>array("DESC"=>"Cmd15","HEX"=>"B3","KEYBOARD"=>True, "KEY"=>'SHIFT+F3'),
			"B4"=>array("DESC"=>"Cmd16","HEX"=>"B4","KEYBOARD"=>True, "KEY"=>'SHIFT+F4'),
			"B5"=>array("DESC"=>"Cmd17","HEX"=>"B5","KEYBOARD"=>True, "KEY"=>'SHIFT+F5'),
			"B6"=>array("DESC"=>"Cmd18","HEX"=>"B6","KEYBOARD"=>True, "KEY"=>'SHIFT+F6'),
			"B7"=>array("DESC"=>"Cmd19","HEX"=>"B7","KEYBOARD"=>True, "KEY"=>'SHIFT+F7'),
			"B8"=>array("DESC"=>"Cmd20","HEX"=>"B8","KEYBOARD"=>True, "KEY"=>'SHIFT+F8'),
			"B9"=>array("DESC"=>"Cmd21","HEX"=>"B9","KEYBOARD"=>True, "KEY"=>'SHIFT+F9'),
			"BA"=>array("DESC"=>"Cmd22","HEX"=>"BA","KEYBOARD"=>True, "KEY"=>'SHIFT+F10'),
			"BB"=>array("DESC"=>"Cmd23","HEX"=>"BB","KEYBOARD"=>True, "KEY"=>'SHIFT+F11'),
			"BC"=>array("DESC"=>"Cmd24","HEX"=>"BC","KEYBOARD"=>True, "KEY"=>'SHIFT+F12'),
			"70"=>array("DESC"=>"ApplicationUse1","HEX"=>"70","KEYBOARD"=>False),
			"71"=>array("DESC"=>"ApplicationUse2","HEX"=>"71","KEYBOARD"=>False),
			"72"=>array("DESC"=>"ApplicationUse3","HEX"=>"72","KEYBOARD"=>False),
			"73"=>array("DESC"=>"ApplicationUse4","HEX"=>"73","KEYBOARD"=>False),
			"74"=>array("DESC"=>"ApplicationUse5","HEX"=>"74","KEYBOARD"=>False),
			"75"=>array("DESC"=>"ApplicationUse6","HEX"=>"75","KEYBOARD"=>False),
			"76"=>array("DESC"=>"ApplicationUse7","HEX"=>"76","KEYBOARD"=>False),
			"77"=>array("DESC"=>"ApplicationUse8","HEX"=>"77","KEYBOARD"=>False),
			"78"=>array("DESC"=>"ApplicationUse9","HEX"=>"78","KEYBOARD"=>False),
			"79"=>array("DESC"=>"ApplicationUse10","HEX"=>"79","KEYBOARD"=>False),
			"7A"=>array("DESC"=>"ApplicationUse11","HEX"=>"7A","KEYBOARD"=>False),
			"7B"=>array("DESC"=>"ApplicationUse12","HEX"=>"7B","KEYBOARD"=>False),
			"7C"=>array("DESC"=>"ApplicationUse13","HEX"=>"7C","KEYBOARD"=>False),
			"7D"=>array("DESC"=>"ApplicationUse14","HEX"=>"7D","KEYBOARD"=>False),
			"7E"=>array("DESC"=>"ApplicationUse15","HEX"=>"7E","KEYBOARD"=>False),
			"7F"=>array("DESC"=>"ApplicationUse16","HEX"=>"7F","KEYBOARD"=>False)
	);
	
	//Bottoni della tastiera che non hanno un bottone fisico a video
	const virtualKey = array(
		"17" => array("function" => "checkTastoCtrl(event)", "location" => 2) //Tasto Ctrl (destro)
	);
	
	const wtd = array(
		"11" => array("DESC"=>"Set buffer address order", "SIGLA" => "SBA"),
		"13" => array("DESC"=>"Insert cursor order", "SIGLA" => "IC"),
		"14" => array("DESC"=>"Move cursor order", "SIGLA" => "MC"),
		"02" => array("DESC"=>"Repeat to address order", "SIGLA" => "RA"),
		"03" => array("DESC"=>"Erase to address order", "SIGLA" => "EA"),
		"01" => array("DESC"=>"Start of header order", "SIGLA" => "SOH"),
		"10" => array("DESC"=>"Transparent data order", "SIGLA" => "TD"),
		"12" => array("DESC"=>"Write extended attribute order", "SIGLA" => "WEA"),
		"1D" => array("DESC"=>"Start of field order", "SIGLA" => "SF"),
		"15" => array("DESC"=>"Write to Display Structured Field Order", "SIGLA" => "WSDF")
	);
	const colour = array (
			'20'  => 'Green',
			'21'  => 'Green/Reverse image',
			'22'  => 'White',
			'23'  => 'White/Reverse image',
			'24'  => 'Green/Underscore',
			'25'  => 'Green/Underscore/Reverse image',
			'26'  => 'White/Underscore',
			'27'  => 'Nondisplay',
			'28'  => 'Red',
			'29'  => 'Red/Reverse image',
			'2A'  => 'Red/Blink',
			'2B'  => 'Red/Reverse image/Blink',
			'2C'  => 'Red/Underscore',
			'2D'  => 'Red/Underscore/Reverse image',
			'2E'  => 'Red/Underscore/Blink',
			'2F'  => 'Nondisplay',
			'30'  => 'Turquoise/Column separators',
			'31'  => 'Turquoise/Column separators/Reverse image',
			'32'  => 'Yellow/Column separators',
			'33'  => 'Yellow/Column separators/Reverse image',
			'34'  => 'Turquoise/Underscore',
			'35'  => 'Turquoise/Underscore/Reverse image',
			'36'  => 'Yellow/Underscore',
			'37'  => 'Nondisplay',
			'38'  => 'Pink',
			'39'  => 'Pink/Reverse image',
			'3A'  => 'Blue',
			'3B'  => 'Blue/Reverse image',
			'3C'  => 'Pink/Underscore',
			'3D'  => 'Pink/Underscore/Reverse image',
			'3E'  => 'Blue/Underscore',
			'3F'  => 'Nondisplay'                      
	);
	
	/*const temi = array(
		'telnet_5250_style.css' => array('LABEL' => "Default", 'CURSORE' => 'white'),
		'telnet_5250_html.css' => array('LABEL' => "Stile html", 'CURSORE' => 'black')
	);*/
}