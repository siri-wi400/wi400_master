<script>
	if (window.opener != null){
		self.close();
	}else{
		document.location.href="<?=$appBase ?>index.php";
	}
</script>