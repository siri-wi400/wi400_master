<?php

/**
 * @name wi400Ajax
 * @desc Classe per il lancio di un'azione via ajax
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.01 08/09/2009
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400Ajax {

	private $debug;
	
	private $id;
    private $action;  // t=
    private $form;    // f=
    private $gateway; // g=
    
    private $successToken;
    private $confirmMessage;
    private $successMessage;
    private $errorMessage;
    private $successCallBack;
    private $errorCallBack;
    
    private $noticeLayer;
    
    private $buttons;
    /**
	 * Costruttore della classe
	 *
	 * @param string $id	: ID dell'ajax
	 */
	public function __construct($id){
    	$this->id = $id;
    	$this->action   = "";
    	$this->form  	= "DEFAULT";
    	$this->gateway  = "";
    	
    	$this->successMessage = "Azione eseguita con successo!";
    	$this->errorMessage = "Errore durante l'esecuzione dell'azione!";
    	
    	$this->noticeLayer = "notice";
    	$this->confirmMessage = "";
     	$this->successToken = "SUCCESS";
    	
	    $this->buttons = array();
	    
    }
    
    public function getId(){
    	return $this->id;
    }

	public function setDebug($db){
    	$this->debug = $db;
    }
    
    public function isDebug(){
    	return $this->debug;
    }
    
	public function setNoticeLayer($nl){
    	$this->noticeLayer = $nl;
    }
    
    public function getNoticeLayer(){
    	return $this->noticeLayer;
    }
    
    public function addButton($idButton){
    	$this->buttons[] = $idButton;
    }
    
    function getButtons(){
    	return $this->buttons;	
    }
    
    public function setSuccessToken($st){
    	$this->successToken = $st;
    }
    
    public function getSuccessToken(){
    	return $this->successToken;
    }
    
    public function setConfirmMessage($cm){
    	$this->confirmMessage = $cm;
    }
    
    public function getConfirmMessage(){
    	return $this->confirmMessage;
    }

    public function setSuccessMessage($sm){
    	$this->successMessage = $sm;
    }
    
    public function getSuccessMessage(){
    	return $this->successMessage;
    }
    
    public function setErrorMessage($em){
    	$this->errorMessage = $em;
    }
    
    public function getErrorMessage(){
    	return $this->errorMessage;
    }
    /**
     * Impostazione del nome del gateway da utilizzare
     *
     * @param string $gateway	: Nome del gateway da utilizzare
     */
    public function setGateway($gateway){
    	$this->gateway = $gateway;
    }
    
 
    /**
     * Recupero del nome del file di gateway
     * 
     * @return string
     */
    public function getGateway(){
    	return $this->gateway;
    }
    
   
    /**
	 * Impostazione del nome dell'azione da eseguire
	 * 
	 * @param string $action	: il nome dell'azione
	 */
    public function setAction($action){
    	$this->action = $action;
    }
    
	/**
	 * Ritorna il nome dell'azione corrente
	 * 
	 * @return string
	 */
    public function getAction(){
    	return $this->action;
    }
    
    /**
	 * Impostazione del nome del form dell'azione da eseguire
	 * 
	 * @param string $form	: il nome del form
	 */
	public function setForm($form){
    	$this->form = $form;
    }
    
    /**
	 * Ritorna il nome del form dell'azione corrente
	 * 
	 * @return string
	 */
    public function getForm(){
    	return $this->form;
    }
    
    
    /**
	 * @return the $successCallBack
	 */
	public function getSuccessCallBack() {
		return $this->successCallBack;
	}

	/**
	 * @return the $errorCallBack
	 */
	public function getErrorCallBack() {
		return $this->errorCallBack;
	}

	/**
	 * @param field_type $successCallBack
	 */
	public function setSuccessCallBack($successCallBack) {
		$this->successCallBack = $successCallBack;
	}

	/**
	 * @param field_type $errorCallBack
	 */
	public function setErrorCallBack($errorCallBack) {
		$this->errorCallBack = $errorCallBack;
	}

	public function dispose(){
?>
	<script>

		function doAjax_<?= $this->getId() ?>(){
			url = _APP_BASE + "?t=<?= $this->getAction() ?>&f=<?= $this->getForm() ?>&g=<?= $this->getGateway() ?>&DECORATION=clean";

<?
				foreach ($this->getButtons() as $buttonId){
?>
					jQuery("#<?= $buttonId ?>").disabled = true;
<?
				}
?> 
			jQuery('#<?= $this->getNoticeLayer() ?>').html('<img src="' + _APP_BASE + 'themes/common/images/page_loading.gif">');
			 
			//new Ajax.Request(url, {
			//	method: 'post', encoding:'UTF-8', parameters: jQuery("#"+APP_FORM).serialize(),
			//		onComplete: function(transport) {
			jQuery.ajax({  
				type: "POST",
				url: url,
				data: jQuery("#" + APP_FORM).serialize()
				}).done(function ( transport ) {  
					blockBrowser(false);
					var notice = jQuery('#<?= $this->noticeLayer ?>');
					<? if ($this->isDebug()){?>
						alert(transport.responseText);
					<?}?>
						if (checkSuccessToken(transport)){
							<?
							// SuccCallBack
							if ($this->getSuccessCallBack() != ""){
								echo $this->getSuccessCallBack().";";
    						} else {
    						?>
							notice.html("<?= $this->getSuccessMessage() ?>").addClass("messageLabel_SUCCESS");
							notice.css({
									border: '1px solid #9eb78f',
									padding: '10px',
									margin: '10px',
									backgroundColor: '#cde5be'
								});
							<? } ?>
						} else {
							<?
							// ErrorCallBack
							if ($this->getErrorCallBack() != ""){
								echo $this->getErrorCallBack().";";
    						} else {
    						?>
							notice.html("<?= $this->getErrorMessage() ?>").
							notice.html.addClass("messageLabel_ERROR");
							notice.css({
									border: '1px solid #ecafb8',
									margin: '10px',
									padding: '10px',
									backgroundColor: '#ffebe8'
								});
							<? } ?>
						}


<?
						foreach ($this->getButtons() as $buttonId){
?>
							jQuery("<?= $buttonId ?>").attr("disabled", false);
<?
						}
?> 
						
					});
			
			//});
		}

	
	</script>

<?    	
    	
    }
}
?>