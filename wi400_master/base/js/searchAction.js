//Questo script serve per la ricerca dell'azione all'interno del menù
var oldValue = "";
var closeMenu = 0;
var after = 400;
var go = "";
function replaceNotCaseSensitive( line, word) {
     var regex = new RegExp( '(' + word + ')', 'gi' );
     return line.replace( regex, "<span style='background: black;'>$1</span>" );
}

function closeAllFolderMenu() {
	jQuery("#print_menu_1").html(objTreeMenu_1.output);
	for(elemento in objTreeMenu_1.branches) {
		objTreeMenu_1.branchStatus[objTreeMenu_1.branches[elemento]] = false;
	}
}

function searchAction(obj, findText) {
	findTextUpper = findText.toLocaleUpperCase();
	var span = "";
	var b = jQuery(obj).find("b");
	text = b.html();
	
	if(!text) {
		span = jQuery(obj).find("span");
		text = span.html();
	}

	textUpper = text.toLocaleUpperCase();

	if(textUpper.indexOf(findTextUpper) != -1) {
		//Evidenzio la stringa cercata
		if(b.html()) {
			b.html(replaceNotCaseSensitive(b.html(), findText));
			//b.html((b.html()).replace(findText, "<span style='background: black;'>"+findText+"</span>"));
		}else if(span.html()) {
			span.html(replaceNotCaseSensitive(span.html(), findText));
			//span.html(span.html().replace(findText, "<span style='background: black;'>"+findText+"</span>"));
		}
		return [obj, obj.id, text];
	}else {
		return false;
	}
}

function openParent(id) {
	if(id) {
		openParent(objTreeMenu_1.childParents[id]);
		if(!objTreeMenu_1.branchStatus[id]) {
			jQuery("#"+id).css("display", "inline");
			
			// Cambio da + a -
			var img = jQuery('img[name="img_'+id+'"]');
			var src = img.attr("src");
			src = src.replace("plus", "minus");
			img.attr("src", src);
			
			// Cambio img folder
			var img = jQuery('#icon_'+id);
			var src = img.attr("src");
			src = src.replace("folder", "folder-expanded");
			img.attr("src", src);
			objTreeMenu_1.branchStatus[id] = true;
			
			//objTreeMenu_1.toggleBranch(id, true);
		}
	}
}

function search(text) {
	clearTimeout(go);
	if(text.length > 2 && oldValue != text) {
		go = setTimeout(function() {
			closeAllFolderMenu();
			oldValue = text;
			closeMenu = 0;
			
			var obj_find = {};
		
			jQuery('div[id*="objTreeMenu_1_node_"]').each(function(i, obj) {
				var rs = searchAction(obj, text);
				if(rs) {
					obj_find[rs[1]] = rs[0];
				}
			});

			//console.log(obj_find);
			
			jQuery('div[id*="objTreeMenu_1_node_"]').each(function(i, obj) {
				if(!obj_find[obj.id]) {
					jQuery("#"+obj.id).css("display", "none");
				}else {
					jQuery("#"+obj.id).css("display", "inline");
					var parentTree = objTreeMenu_1.childParents[obj.id];
					openParent(parentTree);
				}
			});
		}, after);
	}else {
		if(!closeMenu) {
			if(oldValue != text) {
				closeAllFolderMenu();
				closeMenu = 1;
				oldValue = text;
			}
		}
	}
}