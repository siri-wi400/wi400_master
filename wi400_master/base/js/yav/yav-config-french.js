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
DECIMAL_SEP : '.',
THOUSAND_SEP : ',',
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
HEADER_MSG : 'Erreur d\'entrée de données:',
FOOTER_MSG : 'Veuillez essayer à nouveau.',
DEFAULT_MSG : 'Certaines valeurs ne sont pas valables.',
REQUIRED_MSG : 'Ce champ est requis: {1}.',
ALPHABETIC_MSG : '{1} n\'est pas une valeur valable. Caractères permis: A-Za-z',
ALPHANUMERIC_MSG : '{1} n\'est pas une valeur valable. Caractères permis: A-Za-z0-9',
ALNUMHYPHEN_MSG : '{1} n\'est pas une valeur valable. Caractères permis: A-Za-z0-9\-_',
ALNUMHYPHENAT_MSG : '{1} n\'est pas une valeur valable. Caractères permis: A-Za-z0-9\-_@',
ALPHASPACE_MSG : '{1} n\'est pas une valeur valable. Caractères permis: A-Za-z0-9\-_espace',
MINLENGTH_MSG : '{1} doit comporter au moins {2} caractères.',
MAXLENGTH_MSG : '{1} doit comporter au plus {2} caractères.',
NUMRANGE_MSG : '{1} doit être un nombre compris dans cet intervalle: {2}.',
DATE_MSG : '{1} n\'est pas une date valable. Format requis: dd-MM-yyyy.',
TIME_MSG : 'Horaire invalide. Utiliser le format 24-heures, es.14:20).',
NUMERIC_MSG : '{1} doit être un nombre.',
INTEGER_MSG : '{1} doit être un nombre entier.',
DOUBLE_MSG : '{1} doit être un nombre décimal.',
REGEXP_MSG : '{1} n\'est pas une valeur valable. Format requis: {2}.',
EQUAL_MSG : '{1} doit être égal à {2}.',
NOTEQUAL_MSG : '{1} ne doit pas être égal à {2}.',
DATE_LT_MSG : '{1} doit précéder cette date: {2}.',
DATE_LE_MSG : '{1} doit précéder ou être égal à cette date: {2}.',
EMAIL_MSG : '{1} doit être une adresse email valable.',
URL_MSG : '{1} doit être un url valable.',
IPCMP_MSG : '{1} doit être un adresse IP valable.',
EMPTY_MSG : '{1} doit être vide.',
MODULE_ERROR: 'Il y a des fautes dans le formulaire. Vérifiez où il est indiqué.',	
LOADING: 'Chargement en cours ...',
DUPLICATE_VALUE: "valeur déjà présente chez ces entré.",
WAITING: "Opération en cours, patientez s'il vous plaît ...",
BOOKMARKS_ADD: "Ajouter cette action à vos Favoris ?",
FILTER_SEL: "Choisir un nom pour le filtre ou sélectionner un modèle existant!",
FILTER_SEL_ONE: "Sélectionnez au moins un élément de la liste.",
REMOVE: _REMOVE,
ADD: _ADD,
REQUIRED_VAL: "Il est nécessaire de saisir une valeur!",
NOT_VALID_VAL: "Valeur {1} invalide!"
}//end