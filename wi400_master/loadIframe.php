<!DOCTYPE html>
<html>
<head>
	<style type="text/css">
		.contenitore {
			position: absolute;
			top: 50%;
			left: 0px;
			margin-top: -30px;
			width: 100%;
			height: 60px;
			text-align: center;
		}
		img {
			cursor: pointer;
		}
	</style>
	<script type="text/javascript">
		function go(url, id) {
			var a = parent.document.getElementById(id);
			a.src = atob(url);
		}
	</script>
</head>
<body>
	<div class="contenitore">
		<img id="img_tab_active" src="themes/common/images/blue_play.png" width="60" height="60" onClick="go('<?=$_REQUEST['GOTOURL']?>', '<?=$_REQUEST['ID']?>');">
	</div>
</body>
</html>