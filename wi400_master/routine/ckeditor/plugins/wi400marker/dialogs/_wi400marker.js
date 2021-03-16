/*
 Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or http://ckeditor.com/license
*/
//Aggiunta Plugin
CKEDITOR.plugins.add("wi400marker", {
icons: 'wi400marker',
requires : "dialog",
init : function (a) {
	a.config.smiley_path = a.config.smiley_path || this.path + "images/";
	a.addCommand("wi400marker", new CKEDITOR.dialogCommand("wi400marker", {
			allowedContent : "img[alt,height,!src,title,width]",
			requiredContent : "img"
		}));
	a.ui.addButton && a.ui.addButton("wi400marker", {
		label : "wi400Marker",
		command : "wi400marker",
		toolbar : "insert,50"
	});
	CKEDITOR.dialog.add("wi400marker", this.path + "dialogs/wi400marker.js")
},
onLoad: function() {
	jQuery.get( "routine/ckeditor/plugins/wi400marker/marker.html").done(function (data) {
	    // Do something with the data
	    wi400MarkerHtml = data;
	});    
}
});
CKEDITOR.dialog.add("wi400marker", function (editor) {
	return {
        title: 'wi400Marker',
        width: 300,
        height: 400,
        contents : [
                    {
                        id : 'tab1',
                        label : 'First Tab',
                        title : 'First Tab Title',
                        accessKey : 'Q',
                        elements : [
                            {
                                type : 'html',
                                html :  wi400MarkerHtml,
                            }
                        ],
                    }
                ],

        onOk: function() {
            var dialog = this;
            jQuery(".chk:checked").each(function() {
            	editor.insertText(jQuery(this).val()+" ");
            });
        }
    };

});