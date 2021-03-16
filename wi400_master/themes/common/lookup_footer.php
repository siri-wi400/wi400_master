</div><!--  CLOSE CONTAINER -->
<?
if (sizeof($buttonsBar) > 0){
?>
<div id="lookup_footer" class="wi400-button-bar" style="">
<? 
		foreach($buttonsBar as $button) {
			$button->dispose(true);
		}
?>
</div>
<? }
$menuContext->dispose();
require $base_path."/includes/messagesList.php";
?>	
</form>
</body>
</html>