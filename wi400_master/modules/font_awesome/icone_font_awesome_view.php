<?php
	
	if($form == 'DEFAULT') {
		//showArray($_REQUEST);
		echo '<link rel="stylesheet" type="text/css" href="modules/font_awesome/icone_font_awesome_style.css">';
		
		/*$myButton = new wi400InputButton('TESTATA_ANALITICA');
		$myButton->setLabel("In finestra");
		$myButton->setAction($azione);
		$myButton->setForm("DEFAULT");
		$myButton->setTarget("WINDOW", "700", "500", TRUE);
		$myButton->dispose();*/
		
?>		<input type='text' class='input-search-action search_prod' name='SEARCH_PROD' placeholder='Cerca articoli' onkeyUp='search_art(event, this)' value=''/><br/><br/>
		<script type="text/javascript">
			var oldSearch = '';
			function search_art(e, obj) {
				if(!oldSearch) { 
					oldSearch = obj.value;
				}else if(oldSearch != obj.value) {
					oldSearch = obj.value;

				}else {
					return;
				}

				jQuery('.contDatiIcona').css('display', 'inline-block');

				if(obj.value.length < 2)  {
					return;
				}

				//console.log(jQuery('.contDatiIcona i[class*="'+obj.value+'"]:not()'));
				jQuery('.contDatiIcona i:not([class*="'+obj.value+'"])').parent().css('display', 'none');
			}
<?php 		if(isset($_REQUEST['RETURN_ID']) && isset($_REQUEST['RETURN_DETAIL']) ) { ?>
				jQuery(document).ready(function () {
					jQuery('.contDatiIcona').click(function(e) {
						var classe = jQuery(this).find('i').attr('class');
						classe = classe.split(' ');
						passValue(classe[1], '<?=$_REQUEST['RETURN_ID']?>');
		
						closeLookUp();
					});
				});
<?php 		} ?>
		</script>
<?php 
		
		foreach ($icone as $codice => $valore) {
			echo creaIcona($codice, $valore, true);
		}
	}