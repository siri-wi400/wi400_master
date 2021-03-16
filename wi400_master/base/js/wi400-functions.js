function listCheckRange(which, min, max){
	var valore = which.value.replace(_THOUSAND_SEPARATOR,"");
	valore = valore.replace(_DECIMAL_SEPARATOR, "."); 
	if (valore < min || valore > max){
		alert("Il valore del campo deve essere compreso tra " + min + " e " + max + ".");
		which.value = "";
	}
}

function listCheckMax(which, max){
	var valore = which.value.replace(_THOUSAND_SEPARATOR,"");
	valore = valore.replace(_DECIMAL_SEPARATOR, "."); 
	if (valore > max){
		alert("Il valore del campo deve essere minore di " + max + ".");
		which.value = "";
	}
}
function listConfirmMax(which, max){
	var valore = which.value.replace(_THOUSAND_SEPARATOR,"");
	valore = valore.replace(_DECIMAL_SEPARATOR, "."); 
	if (valore > max){
		if (!confirm("Il valore del campo dovrebbe essere minore di " + max + ".\r\nInserire comunque la quantità indicata?")){
			which.value = "";	
		}
	}
}
function decodeValidation(which, message){
	if (document.getElementById(which)){
		if (yav.serverErrors.get(which) != null){
			if (document.getElementById(which).value == yav.serverErrors.get(which)){
				return message;
			}
		}
	}
	return null;
}
function checkActionType(which){
	doSubmit("TAZIONI", "DETAIL&TIPO_AZI="+which.value);
}

function checkMinMaxValues(id, label, min, max) {
	var num_multi_field = jQuery('li[id^="'+id+'"]').length;
	
	if(num_multi_field < min) {
		var par = min == 1 ? "o" : "i";
		return "Il campo "+label+" non pu&ograve; avere meno di "+min+" parametr"+par;
	}
	
	if(num_multi_field > max) {
		var par = max == 1 ? "o" : "i";
		return "Il campo "+label+" non pu&ograve; avere pi&ugrave; di "+max+" parametr"+par;
	}
	
	return null;
}

function checkRequiredShowMultiple(id, label) {
	if(jQuery('input[id*='+id+'_]').length <= 1) {
		return yav.getDefaultMessage("", label, "required");
	}
}