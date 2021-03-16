<?php
/*************************************************************************************
 * jcl.php
 * -----------
 * Author: Ramesh Vishveshwar (info@siri-informatica.it)
 * Copyright: (c) 2016 Siri Informatica (http://www.siri-informatica.it)
 * Release Version: 1.0.0.0
 * Date Started: 2016/01/28
 *
 * CLP (AS400), DFSORT, IDCAMS language file for GeSHi.
 *
 * CHANGES
 * -------
 *
 *************************************************************************************
 *
 *     This file is part of GeSHi.
 *
 *   GeSHi is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   GeSHi is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with GeSHi; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ************************************************************************************/

$language_data = array (
		'PARSER_CONTROL' => array(
				'ENABLE_FLAGS' => array(
						'BRACKETS' => GESHI_NEVER,
						'SYMBOLS' => GESHI_NEVER,
		//				'NUMBERS' => GESHI_NEVER,
		//				'STYLES' => GESHI_NEVER,
						'STRINGS' => GESHI_NEVER,
				)
		),
    'LANG_NAME' => 'RPG',
  //  'COMMENT_SINGLE' => array(1 => '*', 2 => ''),
    'COMMENT_SINGLE' => array(1 => '* ', 2 => '', 3 => '**',4 => '*_*',5=> '*-'),
  //  'COMMENT_MULTI' => array('*' => ''),
    'COMMENT_REGEXP' => array(
        //Multiline-continued single-line comments
     //   1 => '/\/\/(?:\\\\\\\\|\\\\\\n|.)*$/m',
        //Multiline-continued preprocessor define
     //   2 => '/#(?:\\\\\\\\|\\\\\\n|.)*$/m'
        // Comments identified using REGEX
        // Comments start with //* but should not be followed by % (TWS) or + (some JES3 stmts)
        //3 => "\/\/\*[^%](.*?)(\n)"
        ),
		'NUMBERS' =>
		GESHI_NUMBER_INT_BASIC | GESHI_NUMBER_INT_CSTYLE | GESHI_NUMBER_BIN_PREFIX_0B |
		GESHI_NUMBER_OCT_PREFIX | GESHI_NUMBER_HEX_PREFIX | GESHI_NUMBER_FLT_NONSCI |
		GESHI_NUMBER_FLT_NONSCI_F | GESHI_NUMBER_FLT_SCI_SHORT | GESHI_NUMBER_FLT_SCI_ZERO,
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array("'", '"'),
    'ESCAPE_CHAR' => '',
    'KEYWORDS' => array(
        1 => array(
            'COMMAND', 'EXEC','IFEQ','IFGT','IFGE', 'IFLT', 'IFNE','IFLE', 'THEN', 'ELSE',
            'ENDIF', 'JOB', 'OUTPUT', 'SET','ENDSL','    IF','KLIST','PARM','KFLD','ENTRY'
            ),
        2 => array (
            'LIKE','ENDSR'
            ),
        // Keywords set 3: DFSORT, ICETOOL
        3 => array (
            'DEBUG','END','SORT','SUM', 'DO', 'ENDDO',
            'COUNT','SELECT',
            ),
        // Keywords set 4: IDCAMS
        4 => array (
            'SETON','SETOF'
            ),
    		5 => array (
    				'RETURN','RETRN'
    				)
        ),
    'SYMBOLS' => array(
        '(',')','=',',','>','<'
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => false,
        2 => false,
        3 => false,
        4 => false
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #FF0000;',
            2 => 'color: #42D6BC;',
            3 => 'color: #FF00FF;',
            4 => 'color: #FFB833;',
  	        5 => 'color: white;'
            ),
        'COMMENTS' => array(
       //     0 => 'color: #0000FF;',
            //1 => 'color: #0000FF;',
            //2 => 'color: #0000FF;',
       //     3 => 'color: #0000FF;'
        		1 => 'color: #666666; font-style: italic;',
        		2 => 'color: #339933;',
        		3 => 'color: #666666; font-style: italic;',
        		4 => 'color: #666666; font-style: italic;',
        		5 => 'color: #666666; font-style: italic;',
        		'MULTI' => 'color: #808080; font-style: italic;'
        ),
        'ESCAPE_CHAR' => array(
            0 => ''
            ),
        'BRACKETS' => array(
            0 => 'color: #FF7400;'
            ),
        'STRINGS' => array(
            0 => 'color: #66CC66;'
            ),
        'NUMBERS' => array(
        //    0 => 'color: #336633;'
        //    ),
    		0 => 'color: #0000dd;',
    		GESHI_NUMBER_BIN_PREFIX_0B => 'color: #208080;',
    		GESHI_NUMBER_OCT_PREFIX => 'color: #208080;',
    		GESHI_NUMBER_HEX_PREFIX => 'color: #208080;',
    		GESHI_NUMBER_FLT_SCI_SHORT => 'color:#800080;',
    		GESHI_NUMBER_FLT_SCI_ZERO => 'color:#800080;',
    		GESHI_NUMBER_FLT_NONSCI_F => 'color:#800080;',
    		GESHI_NUMBER_FLT_NONSCI => 'color:#800080;'
    				),
        'METHODS' => array(
            1 => '',
            2 => ''
            ),
        'SYMBOLS' => array(
            0 => 'color: #FF7400;'
            ),
        'REGEXPS' => array(
            0 => 'color: #6B1F6B;',
            1 => 'color: #6B1F6B;',
            2 => 'color: #6B1F6B;'
            ),
        'SCRIPT' => array(
            0 => ''
            )
        ),
    'URLS' => array(
        1 => '',
        // JCL book at IBM Bookshelf is http://publibz.boulder.ibm.com/cgi-bin/bookmgr_OS390/handheld/Connected/BOOKS/IEA2B680/CONTENTS?SHELF=&DT=20080604022956#3.1
        2 => '',
        3 => '',
        4 => ''
        ),
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(),
    'REGEXPS' => array(
        // The following regular expressions solves three purposes
        // - Identify Temp Variables in JCL (e.g. &&TEMP)
        // - Symbolic variables in JCL (e.g. &SYSUID)
        // - TWS OPC Variables (e.g. %OPC)
        // Thanks to Simon for pointing me to this
       // 0 => '&amp;&amp;[a-zA-Z]{1,8}[0-9]{0,}',
        //1 => '&amp;[a-zA-Z]{1,8}[0-9]{0,}',
        //2 => '&amp;|\?|%[a-zA-Z]{1,8}[0-9]{0,}',
      0 => array(//CALL RPG/RPGLE
        GESHI_SEARCH => "(CALL\s*'*|CALL\s*)(.*?)('|\s*\s)",
        GESHI_REPLACE => '<a id=\'font_pgm\' href=\'javascript:open("index.php?t=WI_PDM&f=LCOM&OGGETTO=\2&TIPO=*PGM","Contenuto")\'>\2</a>',
        GESHI_MODIFIERS => 'Si',
        GESHI_BEFORE =>  '<font color="red">\1</font>',
        GESHI_AFTER => '<font color="red">\3</font>',
      		),
    		1 => array(//FILE FISICO/LOGICO/PRINTER
    				GESHI_SEARCH => '(F\s*)(.*?)(\s*(IF\s|UF\s|OF\s|O\s|UP\s).*(DISK|PRINTER)\s*)',
    				GESHI_REPLACE => '<a href=\'javascript:open("index.php?t=WI_PDM&f=FCON&OGGETTO=\2&TIPO=*FILE","Contenuto")\'><u><font color="red">\2</font></u></a>',
    				GESHI_MODIFIERS => 'Si',
    				GESHI_BEFORE => '<font color="red">\1</font>',
    				GESHI_AFTER => '\3'
    		),
    		2 => array(//FILE DISPLAY 
    				GESHI_SEARCH => '(F\s*)(.*?)(\s*CF.*WORKSTN\s*)',
    				GESHI_REPLACE => '<a href=\'javascript:open("index.php?t=WI_PDM&f=LCOM&OGGETTO=\2&TIPO=*FILE","Contenuto")\'><u><font color="red">\2</font></u></a>',
    				GESHI_MODIFIERS => 'Si',
    				GESHI_BEFORE => '<font color="red">\1</font>',
    				GESHI_AFTER => '\3'
    		),
    		3 => array(//EXSR
    				GESHI_SEARCH => /*'(EXSR )(.*)( )[ ]',*/'(\bEXSR\s*)(.*?)(\s*\s)',
    				GESHI_REPLACE => '<a href="#\2"><u><font color="#42D6BC">\2</font></u></a>',
    				GESHI_MODIFIERS => 'Si',
    				GESHI_BEFORE => '<font color="#42D6BC">\1</font>',
    				GESHI_AFTER => '\3',
    		),
    		4 => array(//BEGSR
    				GESHI_SEARCH => /*'(C           )(.*)(BEGSR)',*//*'(C)(.*)(BEGSR)',*/'(SR\s*|C \s*)(.*?)(\s*BEGSR\b|\s*begsr\b)',
    				GESHI_REPLACE => '<font color="#42D6BC"><h id=\'\2\'>\2</font></h>',
    				GESHI_MODIFIERS => '',
    				GESHI_BEFORE => '\1',
    				GESHI_AFTER => '<font color="#42D6BC">\3</font>',
    		),
    		5 => array(//DS	ESTERNE FILE
    				GESHI_SEARCH => '( I\s*| D \s*)(.*?)(\s*E DS)',
    				GESHI_REPLACE => '<a href=\'javascript:open("index.php?t=WI_PDM&f=FCON&OGGETTO=\2&TIPO=*FILE","Contenuto")\'><u><font color="blue">\2</font></u></a>',
    				GESHI_MODIFIERS => 'Si',
    				GESHI_BEFORE => '<font color="blue">\1</font>',
    				GESHI_AFTER => '\3',
    		),
    		6 => array(//DS ESTERNE FILE DICHIARATO
    				GESHI_SEARCH => /*'(E DS)(.*)()',*/'(EXTNAME\(\s*)(.*?)(\))',
    				GESHI_REPLACE => '<a href=\'javascript:open("index.php?t=WI_PDM&f=FCON&OGGETTO=\2&TIPO=*FILE","Contenuto")\'><u><font color="red">\2</font></u></a>',
    				GESHI_MODIFIERS => 'Si',
    				GESHI_BEFORE => '\1',
    				GESHI_AFTER => '\3',
    		),
    		7 => array(//DS	ESTERNE
    				GESHI_SEARCH => '( I\s*| D \s*)(.*?)(\s*EIDS)',
    				GESHI_REPLACE => '<font color="blue">\2</font>',
    				GESHI_MODIFIERS => 'Si',
    				GESHI_BEFORE => '<font color="blue">\1</font>',
    				GESHI_AFTER => '\3',
    		),
    		8 => array(//COPY	
    				GESHI_SEARCH => '(QCOPY,|QCOPYILE,)(.*?)(\s*\s|$)',
    				GESHI_REPLACE => '<a href=\'javascript:open("index.php?t=WI_PDM&f=LCOM&OGGETTO=QCOPY&MEMBRO=\2&TIPO=*FILE","Contenuto")\'><u><font color="blue">\2</font></u></a>',
    				GESHI_MODIFIERS => 'Si',
    				GESHI_BEFORE => '\1',
    				GESHI_AFTER => '\3',
    		),
    		9 => array(//Z-ADD|MOVEL|MOVE|ADD
    				GESHI_SEARCH => '(Z-ADD|MOVEL|MOVE| ADD)',
    				GESHI_REPLACE => '<font color="#8F00FF">\1</font>',
    				GESHI_MODIFIERS => 'Si',
    				GESHI_BEFORE => '',
    				GESHI_AFTER => '',
    		),
    		10 => array(//DS interne + SPECIFICHE DS
    				GESHI_SEARCH => '(D\s*?)(.*?)(\s  DS|\s  SDS)',
    				GESHI_REPLACE => '<font color="yellow">\2</font>',
    				GESHI_MODIFIERS => 'Si',
    				GESHI_BEFORE => '<font color="yellow">\1</font>',
    				GESHI_AFTER => '\3',
    		),
    		11 => array(//GOT
    				GESHI_SEARCH => '(GOTO\s*)(.*?)(\s*\s)',
    				GESHI_REPLACE => '<a href="#\2"><u><font color="white">\2</font></u></a>',
    				GESHI_MODIFIERS => 'Si',
    				GESHI_BEFORE => '<font color="white">\1</font>',
    				GESHI_AFTER => '\3',
    		),
    		12 => array(//TAG
    				GESHI_SEARCH => '(C \s*)(.*?)(\s*TAG)',
    				GESHI_REPLACE => '<font color="white"><h id=\'\2\'>\2</font></h>',
    				GESHI_MODIFIERS => '',
    				GESHI_BEFORE => '\1',
    				GESHI_AFTER => '<font color="white">\3</font>',
    		)
    		),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array()
);
