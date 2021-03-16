/***********************************************************************
 * YAV - Yet Another Validator  v2.0                                   *
 * Copyright (C) 2005-2008                                             *
 * Author: Federico Crivellaro <f.crivellaro@gmail.com>                *
 * WWW: http://yav.sourceforge.net                                     *
 ***********************************************************************/

var yav_config = {

// CHANGE THESE VARIABLES FOR YOUR OWN SETUP

// if you want yav to highligh fields with errors
inputhighlight : false,
// if you want to use multiple class names
multipleclassname : false,
// classname you want for the inner error highlighting
innererror : 'innerError',
// classname you want for the inner help highlighting
innerhelp : 'innerHelp',
// div name where errors (and help) will appear (or where jsVar variable is dinamically defined)
errorsdiv : 'errorsDiv',
// if you want yav to alert you for javascript errors (only for developers)
debugmode : false,
// if you want yav to trim the strings
trimenabled : true,

// change to set your own decimal separator and your date format
DECIMAL_SEP : _DECIMAL_SEPARATOR,
THOUSAND_SEP : _THOUSAND_SEPARATOR,
DATE_FORMAT : 'dd/MM/yyyy',
DATE_ESCAPE : '99/99/9999',
TIME_FORMAT : 'hh:mm',

// change to set your own rules based on regular expressions
alphabetic_regex : "^[A-Za-z]*$",
alphanumeric_regex : "^[A-Za-z0-9]*$",
alnumhyphen_regex : "^[A-Za-z0-9\-_]*$",
alnumhyphenat_regex : "^[A-Za-z0-9\-_@]*$",
alphaspace_regex : "^[A-Za-z0-9\-_ \n\r\t]*$",
//email_regex : "^(([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}){0,1}$",
email_regex : "^$|^([-_.!#$%&\'*=?+\/{|}~a-zA-Z0-9]+)@([-_!#$%&\'*=?+\/{|}~a-zA-Z0-9]+)([.]{0,1})([-_!#$%&\'*=?+\/{|}~a-zA-Z0-9]+)$",
url_regex : "^$|^(http|https|ftp):\/\/([0-9]{1,3}\.){3}([0-9]{1,3}):([0-9]{1,3})\/([-_.!#$%&\'*=?+\/{|}~a-zA-Z0-9]+)$",
IPcmp_regex : "^$|^([0-9]{1,3}\.){3}([0-9]{1,3})$",

// change to set your own rule separator
RULE_SEP : '|',

// change these strings for your own translation (do not change {n} values!)
HEADER_MSG : 'Dati non validi:',
FOOTER_MSG : 'Correggi e riprova.',
DEFAULT_MSG : 'I dati non sono validi.',
REQUIRED_MSG : 'Il campo {1} deve essere valorizzato.',
ALPHABETIC_MSG : '{1} non valido. Caratteri ammessi: A-Za-z',
ALPHANUMERIC_MSG : '{1} non valido. Caratteri ammessi: A-Za-z0-9',
ALNUMHYPHEN_MSG : '{1} non valido. Caratteri ammessi: A-Za-z0-9\-_',
ALNUMHYPHENAT_MSG : '{1} non valido. Caratteri ammessi: A-Za-z0-9\-_@',
ALPHASPACE_MSG : '{1} non valido. Caratteri ammessi: A-Za-z0-9\-_space',
MINLENGTH_MSG : '{1} deve essere lungo almeno {2} caratteri.',
MAXLENGTH_MSG : '{1} non deve essere lungo al massimo {2} caratteri.',
NUMRANGE_MSG : '{1} deve essere un numero in {2}.',
DATE_MSG : '{1} data non valida (gg/mm/aaaa).',
TIME_MSG : 'Orario non valido. Utilizzare formato 24h, es.14:20).',
NUMERIC_MSG : '{1} deve essere un numero.',
INTEGER_MSG : '{1} deve essere un intero.',
DOUBLE_MSG : '{1} deve essere un numero decimale.',
REGEXP_MSG : '{1} non valido. Formato ammesso: {2}.',
EQUAL_MSG : '{1} deve essere uguale a {2}.',
NOTEQUAL_MSG : '{1} non deve essere uguale a {2}.',
DATE_LT_MSG : '{1} deve essere precedente a {2}.',
DATE_LE_MSG : '{1} deve essere non successiva a {2}.',
EMAIL_MSG : '{1} deve essere una e-mail valida.',
URL_MSG : '{1} deve essere un url valido (YAV).',
IPCMP_MSG : '{1} deve essere un indirizzo IP valido.',
EMPTY_MSG : '{1} deve essere vuoto.',
MODULE_ERROR: 'Vi sono errori nel modulo. Controllare dove indicato.',	
LOADING: 'Caricamento in corso ...',
DUPLICATE_VALUE: "Valore gia' presente tra quelli inseriti.",
WAITING: 'Operazione in corso, attendere prego ...',
BOOKMARKS_ADD: "Aggiungere ai preferiti l'azione corrente?",
FILTER_SEL: "Inserire un nome per il filtro o selezionarne uno esistente!",
FILTER_SEL_ONE: "Selezionare almeno un elemento della lista.",
REMOVE: _REMOVE,
ADD: _ADD,
REQUIRED_VAL: "E' necessario inserire un valore!",
NOT_VALID_VAL: "Valore {1} non valido!"
}//end