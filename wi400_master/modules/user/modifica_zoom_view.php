<?php

	if($form == "DEFAULT") {
?>
		<style>
			.zoom_scale {
				font-weight: normal;
				font-size: 17px;
			}
		</style>
		<script>
			jQuery(function() {
				jQuery("#slider-range-min").slider({
					range: "min",
					value: <?= $init_scale?>,
					min: 1,
					max: 2,
					step: 0.1,
					slide: function(event, ui) {
						jQuery("#ZOOM").val(ui.value);
					}
				});
				jQuery("#ZOOM").val("" + jQuery( "#slider-range-min" ).slider( "value" ) );
			});
			jQuery("#zoom_scale").on('click', function() {
				console.log("ciao");
			});
		</script>
		<p>
			<label >Zoom:</label>
			<input type="text" id="ZOOM" class="body-area" name="ZOOM" readonly style="border:0; color:#f6931f; font-weight:bold; font-size: 17px;">
		</p>
 
		<div id="slider-range-min"></div><br/><br/>
<?php 
		
		$button = new wi400InputButton("SAVE_BUTTON");
		$button->setLabel("Salva");
		$button->setAction($azione);
		$button->setForm("SALVA");
		$button->setStyleClass("zoom_scale");
		$button->dispose();
	}