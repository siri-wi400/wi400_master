 <a href="/software/WI-Commander.zip" download="WI-Commander.zip" style="color:red">Download Commander installer</a> 
<table>
<tr>
<td>
<img src="modules/socket_server/img/5250.png" alt="Open 5250" height="200" width="200" onclick="open5250()">
</td>
<td> 
<img src="modules/socket_server/img/excel.png" alt="Open Excel" height="200" width="200" onclick="openExcel()">
</td>
<td> 
<img src="modules/socket_server/img/message.png" alt="Send Message" height="200" width="200" onclick="openBox()">
</td>
<td> 
<img src="modules/socket_server/img/mail.png" alt="Send Mail" height="200" width="200" onclick="sendMail()">
</td>
</tr>
<tr>
<td>
OPEN 5250
</td>
<td> 
OPEN EXCEL
</td>
<td> 
SEND MESSAGE
</td>
<td> 
SEND MAIL
</td>
</tr>
<tr>
<td colspan="4">
In progress ..
</td>
</tr>
<tr>
<td>
<img src="modules/socket_server/img/usb.png" alt="Open Excel" height="200" width="200">
</td>
<td>
<img src="modules/socket_server/img/active.png" alt="Open Excel" height="200" width="200">
</td>
<td>
</td>
<td>
</td>
</tr>
</table>
<script>
function open5250() {
	console.log("passo open 5250");
jQuery.ajax({  
	type: "GET",
	url: _APP_BASE + APP_SCRIPT + "?t=OPEN_5250"
	}).done(function ( response ) {  
		//alert(response);
		//document.getElementById(div).innerHTML = response;
	}).fail(function ( data ) {  
		
	}); 
}	
</script> 
<script>
function openExcel() {
	console.log("passo Excel");
jQuery.ajax({  
	type: "GET",
	url: _APP_BASE + APP_SCRIPT + "?t=OPEN_EXCEL"
	}).done(function ( response ) {  
		//alert(response);
		//document.getElementById(div).innerHTML = response;
	}).fail(function ( data ) {  
		
	}); 
}	
</script> 
<script>
function openBox() {
	console.log("passo Box");
jQuery.ajax({  
	type: "GET",
	url: _APP_BASE + APP_SCRIPT + "?t=OPEN_MSG"
	}).done(function ( response ) {  
		//alert(response);
		//document.getElementById(div).innerHTML = response;
	}).fail(function ( data ) {  
		
	}); 
}	
</script> 
<script>
function sendMail() {
	console.log("passo Mail");
jQuery.ajax({  
	type: "GET",
	url: _APP_BASE + APP_SCRIPT + "?t=OPEN_EMAIL"
	}).done(function ( response ) {  
		//alert(response);
		//document.getElementById(div).innerHTML = response;
	}).fail(function ( data ) {  
		
	}); 
}	
</script> 
<?php
