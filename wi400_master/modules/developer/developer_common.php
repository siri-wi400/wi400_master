<?php
/*
 * Funzione per sviluppatori per debug variabili applicativi
 */
function developer_add_variable($var, $value) {
	$_SESSION['DEVELOPER_RUNTIME_FIELD']['CUSTOM'][$var]=$value;
}
function developer_clean_runtime_field() {
	unset($_SESSION['DEVELOPER_RUNTIME_FIELD']);
}
/* 
 * Funzione usata da WI400 per debug interni
 */
function developer_add_system_var($var, $value, $key="WI400") {
	$_SESSION['DEVELOPER_RUNTIME_FIELD'][$key][$var]=$value;
}