// Ritorno alla HOME
shortcut.add("CTRL+ALT+H",function(e) {
    jQuery('body').scrollTo(0,0);
    //jQuery('form').find('input[type=text],textarea,select').first().filter(':visible:not(:disabled):not([readonly])').focus();
    jQuery('form').find('input[type=text],textarea,select').filter(':visible:not(:disabled):not([readonly])').each(function (index){
    	var name = this.id;
    	if (name.indexOf("_FILTER")==-1) {
    		jQuery(this).focus();
    		return false;
    	}
    }) ;
});
// Posizionamento sull'elemento della lista selezionato
shortcut.add("CTRL+ALT+C",function(e) {
	jQuery('body').scrollTo('#'+currentPaginationListKey + "-" + window[currentPaginationListKey+'_SELECT']+"-checkbox");
});

	