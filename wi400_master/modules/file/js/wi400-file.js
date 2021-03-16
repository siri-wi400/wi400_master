function loadImage(img){
	var formObj = document.getElementById(APP_FORM);
	formObj.encoding = "multipart/form-data";
}

function setFormEncoding(formEncoding){
	if (typeof(formEncoding) == "undefined"){
		formEncoding = "multipart/form-data";
	}
	var formObj = document.getElementById(APP_FORM);
	formObj.encoding = formEncoding;
}

function manageImage(objCode, objType, imgType, imgContainer, confirmDelete, colsNum, maxCount, sizeContenitore){
	w = 600;
	h = 370;
	
	openWindow(_APP_BASE + APP_SCRIPT + "?DECORATION=lookUp&t=IMAGEMANAGER&IMG_CONTAINER=" + imgContainer + "&OBJ_CODE="+objCode+"&OBJ_TYPE=" + objType+"&IMG_TYPE=" + imgType+"&CONFIRM_DELETE=" + confirmDelete + "&IMG_COLS_NUM=" + colsNum + "&IMG_COUNT=" + maxCount + "&SIZE_CONTENITORE=" + sizeContenitore, "manageImage", w, h);
}
function deleteImage(imgCode, objCode, objType, imgType, imgContainer, confirmDelete, colsNum, maxCount, sizeContenitore){
	if (checkBlockBrowser()){
		if (!confirmDelete || confirm("Cancellare l'immagine selezionata?")){
			blockBrowser(true);
			
			jQuery.ajax({  
				type: "POST",
				url: _APP_BASE + "index.php?t=IMAGEDELETE&f=DELETE&DECORATION=clean&IMG_CODE="+imgCode+"&OBJ_CODE="+objCode+"&OBJ_TYPE=" + objType+"&IMG_TYPE=" + imgType + "&IMG_CONTAINER="+ imgContainer +"&IMG_COLS_NUM=" + colsNum + "&IMG_COUNT=" + maxCount + "&SIZE_CONTENITORE=" + sizeContenitore
					
				}).done(function ( data ) {
					blockBrowser(false);
					jQuery("#" + imgContainer).html(data);
				}).fail(function ( data ) {
					alert("SERVER ERROR:"+data);
			 		blockBrowser(false);
				}); 
		}
	}
}

function reloadImage(objCode, objType, imgType, imgContainer, confirmDelete, colsNum, maxCount, sizeContenitore){

	if (checkBlockBrowser()){
		blockBrowser(true);
		jQuery.ajax({  
			type: "POST",
			url: _APP_BASE + "index.php?t=IMAGEDELETE&f=DELETE&DECORATION=clean&IMG_CODE=&OBJ_CODE="+objCode+"&OBJ_TYPE=" + objType+"&IMG_TYPE=" + imgType + "&IMG_CONTAINER="+ imgContainer +"&IMG_COLS_NUM=" + colsNum +"&IMG_COUNT=" + maxCount + "&SIZE_CONTENITORE=" + sizeContenitore
				
			}).done(function ( data ) {  
				blockBrowser(false);
				jQuery("#" + imgContainer).html(data);
			}).fail(function ( data ) {  
				alert("SERVER ERROR:"+data);
		 		blockBrowser(false);
			}); 
	}
}
/*function deleteImage(imgCode, objCode, objType, imgType, imgContainer, confirmDelete, colsNum){
	if (checkBlockBrowser()){
		if (!confirmDelete || confirm("Cancellare l'immagine selezionata?")){
			blockBrowser(true);
			new Ajax.Request(_APP_BASE + "index.php?t=IMAGEDELETE&f=DELETE&DECORATION=clean&IMG_CODE="+imgCode+"&OBJ_CODE="+objCode+"&OBJ_TYPE=" + objType+"&IMG_TYPE=" + imgType + "&IMG_COLS_NUM=" + colsNum, {encoding:'UTF-8',method: 'post',evalScripts: true,
			 onSuccess: function(response) {
			 		blockBrowser(false);
					jQuery(imgContainer).update(response.responseText);
			 	},
			 	onFailure: function(response) {
					alert("SERVER ERROR:"+response.responseText);
			 		blockBrowser(false);
			 	}  
			});
		}
	}
}

function reloadImage(objCode, objType, imgType, imgContainer, confirmDelete, colsNum){

	if (checkBlockBrowser()){
		blockBrowser(true);
		new Ajax.Request(_APP_BASE + "index.php?t=IMAGEDELETE&DECORATION=clean&IMG_CODE=&OBJ_CODE="+objCode+"&OBJ_TYPE=" + objType+"&IMG_TYPE=" + imgType+"&CONFIRM_DELETE=" + confirmDelete + "&IMG_COLS_NUM=" + colsNum, {encoding:'UTF-8',method: 'post',evalScripts: true,
		 onSuccess: function(response) {
		 		blockBrowser(false);
				jQuery(imgContainer).update(response.responseText);
		 	},
		 	onFailure: function(response) {
				alert("SERVER ERROR:"+response.responseText);
		 		blockBrowser(false);
		 	}  
		});
	}
}*/