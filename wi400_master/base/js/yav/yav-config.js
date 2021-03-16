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
multipleclassname : true,
//classname you want for the inner error highlighting
innererror : 'innerError',
// classname you want for the inner help highlighting
innerhelp : 'innerHelp',
// div name where errors (and help) will appear (or where jsVar variable is dinamically defined)
errorsdiv : 'errorsDiv',
// if you want yav to alert you for javascript errors (only for developers)
debugmode : false,
// if you want yav to trim the strings
trimenabled : false,

// change to set your own decimal separator and your date format
DECIMAL_SEP : ".",
THOUSAND_SEP : ",",
DATE_ESCAPE : '99/99/9999',
DATE_FORMAT : 'mm/dd/yyyy',
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
HEADER_MSG : 'Invalid data',
FOOTER_MSG : 'Fix and Retry',
DEFAULT_MSG : 'The data is invalid.',
REQUIRED_MSG : 'Enter {1}.',
ALPHABETIC_MSG : '{1} is not valid. Characters allowed: A-Za-z',
ALPHANUMERIC_MSG : '{1} is not valid. Characters allowed: A-Za-z0-9',
ALNUMHYPHEN_MSG : '{1} is not valid. Characters allowed: A-Za-z0-9\-_',
ALNUMHYPHENAT_MSG : '{1} is not valid. Characters allowed: A-Za-z0-9\-_@',
ALPHASPACE_MSG : '{1} is not valid. Characters allowed: A-Za-z0-9\-_space',
MINLENGTH_MSG : '{1} must be at least {2} characters long.',
MAXLENGTH_MSG : '{1} must be no more than {2} characters long.',
NUMRANGE_MSG : '{1} must be a number in {2} range.',
DATE_MSG : '{1} is not a valid date, using the format MM-dd-yyyy.',
TIME_MSG : 'Time is not valid. using the format 24h, es.14:20).',
NUMERIC_MSG : '{1} must be a number.',
INTEGER_MSG : '{1} must be an integer',
DOUBLE_MSG : '{1} must be a decimal number.',
REGEXP_MSG : '{1} is not valid. Format allowed: {2}.',
EQUAL_MSG : '{1} must be equal to {2}.',
NOTEQUAL_MSG : '{1} must be not equal to {2}.',
DATE_LT_MSG : '{1} must be previous to {2}.',
DATE_LE_MSG : '{1} must be previous or equal to {2}.',
EMAIL_MSG : '{1} must be a valid e-mail.',
URL_MSG : '{1} must be a valid url.',
IPCMP_MSG : '{1} must be a valid IP address.',
EMPTY_MSG : '{1} must be empty.'
MODULE_ERROR: 'There is error(s). Chekc detail messages.',	
LOADING: 'Loading ...',
DUPLICATE_VALUE: "Duplicate data not allowed.",
WATING: 'Waiting ...',
BOOKMARKS_ADD: "Add this Action to BookMarks ?",
FILTER_SEL: "Choose a Name for the Filter or select an existing one!",
FILTER_SEL_ONE: "Select at least an item on the list.",
REMOVE: _REMOVE,
ADD: _ADD,
REQUIRED_VAL: "Must insert a value!",
NOT_VALID_VAL: "Invalid {1} value!"
}//end