<?php
	class wi400DropArea {
		
		private $id;
		private $innerHTML;
		private $callBack;
		private $width;
		
		public function getId(){
	    	return $this->id;
	    }
	    
	    public function setId($id){
	    	$this->id = $id;
	    }
		public function setWidth($width){
	    	$this->width = $width;
	    }
		public function getWidth($width){
	    	return $this->width;
	    }
		public function getInnerHTML(){
	    	return $this->innerHTML;
	    }
	    public function setInnerHTML($innerHTML){
	    	$this->innerHTML = $innerHTML;
	    }
		public function getCallBack(){
	    	return $this->callBack;
	    }
	    public function setCallBack($callBack){
	    	$this->callBack = $callBack;
	    }
	    
		public function dispose(){
		if (isset($this->width) && $this->width !="") {
			//
		} else {
			$this->width = "100%";
		}
			
?>
<style type="text/css">

div#<?=$this->id ?> {
	background: #fff;
}

div#<?=$this->id ?>.hover {
	border: 1px dashed #aaa;
}
</style>
<div id="<?=$this->id ?>" style="width: <?=$this->width ?>;height:90px;overflow: auto;" class="work-area">
	<?=$this->innerHTML ?>
</div>
<script type="text/javascript">
jQuery(function (){
jQuery("#<?=$this->id ?>").droppable({
	drop: function( event, ui ) {
		//codice = ui.draggable.html();
		//riga = jQuery(ui.draggable).attr('title');
	//$( this )
	//.addClass( "ui-state-highlight" )
	//.find( "p" )
	//.html( "Dropped!" );
		<?=$this->callBack ?>
		//jQuery(ui.draggable).remove();
	}
	});
});
</script>
<?	
    //disable_text_selection();	
		}
		
	}
?>