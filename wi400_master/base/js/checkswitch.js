function checkSwitch() {
	/*jQuery('input.checkSwitch').each(function() {
		
		var SWITCH_LABELS = eval(jQuery(this).attr('id') + "_LABELS");
		if(jQuery(this).attr('checked') == 'checked'){
			jQuery(this).wrap('<span class="checkSwitch on"/>');
		} else {
			jQuery(this).wrap('<span class="checkSwitch off" />');
		}
				
		jQuery(this).parent('span.checkSwitch').append('<div class="checkSwitchInner"><div class="checkSwitchOn">' + SWITCH_LABELS[0] + '</div><div class="checkSwitchHandle"></div><div class="checkSwitchOff">' + SWITCH_LABELS[1] + '</div></div>');
		
		// Disabilitazione
		if(jQuery(this).attr('disabled') == 'disabled') {
			jQuery(this).parent().append('<div style=" position: absolute; width: 100%; height: 100%; left: 0px; top: 0px; background: rgba(192,192,192, 0.4);">');
		}
	});*/
}

jQuery(document).on('click', 'span.checkSwitch', function() {
	var $this = jQuery(this);
	var input = $this[0].childNodes[0];
	
	if(jQuery('#'+input.id).attr('disabled') != 'disabled') {
		if($this.hasClass('off')){
			$this.addClass('on');
			$this.removeClass('off');
			$this.children('#'+input.id).attr('checked', 'checked');
		} else if($this.hasClass('on')){
			$this.addClass('off');
			$this.removeClass('on');
			$this.children('#'+input.id).removeAttr('checked');
		}
	}
	
});