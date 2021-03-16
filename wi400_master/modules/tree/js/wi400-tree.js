

// riceve nodo e campo a cui passare il valore
function passNodeField(node, description, field){

		/*var parentObj = "";
		if (IFRAME_LOOKUP){
			var lookUpParent = document.getElementById("LOOKUP_PARENT");
			if (lookUpParent && lookUpParent.value != ""){
				// Sono in un lookup aperto da un altro lookup
				IFrameObj = parent.window.frames;
				for (x = 0; x < IFrameObj.length; x++){
					if(IFrameObj[x].name == lookUpParent.value){
						parentObj = IFrameObj[x];
						break;
					}
				}
			}else{
				parentObj = parent;
			}
		}else{
			parentObj = window.opener;
		}
		if (parentObj=="") {
			parentObj = parent;
		}*/
	    parentObj = getParentObj();
		parentObj.document.getElementById(field).value = node;
		if (parentObj.document.getElementById(field + "_DESCRIPTION")){
			parentObj.document.getElementById(field + "_DESCRIPTION").innerHTML = "&nbsp;" + description;
		}
		
		// Per lanciare gli eventuali controlli client sul campo
		// @todo Gestire il fatto di essere dentro ad un iframe!!!!
		parentObj.document.getElementById(field).focus();
		parentObj.document.getElementById(field).blur();
		parentObj.jQuery( "#"+field ).trigger("change");
		
		if(!parentObj.jQuery("#"+field+"_MULTIPLE").length) { 
			if (IFRAME_LOOKUP){
				top.closeLookUp();
			}else{
				self.close();
			}
		}
}


// riceve un filtro da un tree e lo aggiunge ai filtri della lista
function filterByNodeAndLevel(idList, node, filter){
		document.getElementById("FAST_FILTER_" + filter).value = node;
		if (document.getElementById(idList + "_SEARCH")){
			document.getElementById(idList + "_SEARCH").value = "SEARCH";
			doPagination(idList, _PAGE_FIRST, true);
		}
}

function loadTreeChild(idTree, functionName, nodeId, parentFunction, parentNode, treeLevel){

	// carica lista child da server
	
	// Folder
	var folderTreeImg = document.getElementById(idTree + "_" + treeLevel + "_" + nodeId + "_folder");
	var lineTreeImg   = document.getElementById(idTree + "_" + treeLevel + "_" + nodeId + "_line");
	

	if ((lineTreeImg.src).indexOf("plus") >= 0){
		if (checkBlockBrowser(idTree)){
			
			var checkChild = document.getElementById(idTree + "_" + treeLevel + "_" + nodeId + "_childs");
			if (checkChild){
				
				checkChild.style.display = "block";
				treeChildOpen(folderTreeImg,lineTreeImg);
				
				// **************************************
				// AVVISO LATO SERVER CHE IL NODO E' STATO APERTO
				// **************************************
				/*new Ajax.Request(_APP_BASE + "index.php?t=TREECHILD&DECORATION=clean&IDTREE=" + idTree + "&TREE_PARAMETERS=" + treeParameters, {encoding:'UTF-8',method: 'post',evalScripts: true, 
					 onSuccess: function(response) {
					},
				 	onFailure: function(response) {
						alert("SERVER ERROR:"+response.responseText);
				 	}
			 	});*/
				jQuery.ajax({ 
					type: "POST",
					cache: false,
					url: _APP_BASE + "index.php?t=TREECHILD&DECORATION=clean&IDTREE=" + idTree + "&TREE_PARAMETERS=" + treeParameters 
				}).done(function ( response ) {  
				}).fail(function ( data ) { 
					alert("SERVER ERROR:"+response);
			 	  
			});
				
			}else{
				var treeParameters = functionName + "|" + nodeId + "|" + parentFunction + "|" + parentNode + "|" + treeLevel;
				blockBrowser(true, "", idTree);
				jQuery.ajax({ 
					type: "POST",
					cache: false,
					url: _APP_BASE + "index.php?t=TREECHILD&DECORATION=clean&IDTREE=" + idTree + "&TREE_PARAMETERS=" + treeParameters 
				//new Ajax.Request(_APP_BASE + "index.php?t=TREECHILD&DECORATION=clean&IDTREE=" + idTree + "&TREE_PARAMETERS=" + treeParameters, {encoding:'UTF-8',method: 'post',evalScripts: true, 
				// onSuccess: function(response) {
					}).done(function ( response ) {  
					 	blockBrowser(false, "", idTree);
	
			 			var childsDiv=document.createElement('div');
			 			childsDiv.setAttribute("id", idTree + "_" + treeLevel + "_" + nodeId + "_childs");
			 			childsDiv.innerHTML = response;
						document.getElementById(idTree + "_" + treeLevel + "_" + nodeId).appendChild(childsDiv);
						
						treeChildOpen(folderTreeImg,lineTreeImg);
		
				 	//},
				 	//onFailure: function(response) {
					}).fail(function ( data ) { 
						alert("SERVER ERROR:"+response);
						blockBrowser(false, "", idTree);
				});
			}
		}
	}else{
	
		// **************************************
		// Già aperto.
		// **************************************
		var childsDiv=document.getElementById(idTree + "_" + treeLevel + "_" + nodeId + "_childs");
		//document.getElementById(idTree + "_" + treeLevel + "_" + nodeId).removeChild(childsDiv);
		childsDiv.style.display = "none";
		
		lineImgSrc = "plus";
		if ((lineTreeImg.src).indexOf("top")>0){
			lineImgSrc = lineImgSrc + "top";
		}else if ((lineTreeImg.src).indexOf("bottom")>0){
			lineImgSrc = lineImgSrc + "bottom";
		}
		
		lineTreeImg.src = _THEME_DIR + "images/treeDark/" +lineImgSrc + ".gif";
		folderTreeImg.src = _THEME_DIR + "images/treeDark/folder.gif";
		
		// **************************************
		// Chiudo anche lato server
		// **************************************
		var treeParameters = functionName + "|" + nodeId + "|" + parentFunction + "|" + parentNode + "|" + treeLevel;
		jQuery.ajax({ 
			type: "POST",
			cache: false,
			url: _APP_BASE + "index.php?t=TREECHILD&f=CLOSE_NODE&DECORATION=clean&IDTREE=" + idTree + "&TREE_PARAMETERS=" + treeParameters 
		//new Ajax.Request(_APP_BASE + "index.php?t=TREECHILD&DECORATION=clean&IDTREE=" + idTree + "&TREE_PARAMETERS=" + treeParameters, {encoding:'UTF-8',method: 'post',evalScripts: true, 
		// onSuccess: function(response) {
			}).done(function ( response ) {  
			}).fail(function ( data ) { 
				alert("SERVER ERROR:"+response);
				blockBrowser(false, "", idTree);		 	  
			});
		
				/*new Ajax.Request(_APP_BASE + "index.php?t=TREECHILD&f=CLOSE_NODE&DECORATION=clean&IDTREE=" + idTree + "&TREE_PARAMETERS=" + treeParameters, {encoding:'UTF-8',method: 'post',evalScripts: true, 
				 onSuccess: function(response) {
				 	},
				 	onFailure: function(response) {
						alert("SERVER ERROR:"+response.responseText);
						blockBrowser(false, "", idTree);
				 	}  
				});*/
		// **************************************
	}

}

function treeChildOpen(folderTreeImg,lineTreeImg){

	lineImgSrc = "minus";
	if ((lineTreeImg.src).indexOf("top")>0){
		lineImgSrc = lineImgSrc + "top";
	}else if ((lineTreeImg.src).indexOf("bottom")>0){
		lineImgSrc = lineImgSrc + "bottom";
	}
	
	lineTreeImg.src = _THEME_DIR + "images/treeDark/" +lineImgSrc + ".gif";
	folderTreeImg.src = _THEME_DIR + "images/treeDark/folder-expanded.gif";

}
