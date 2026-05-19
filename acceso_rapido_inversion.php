<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Factureate Acceso Express</title>
</head>
<body>
	<form name="frm_express" id="frm_express" method="post" enctype="multipart/form-data" action="index.php">
		<input type="hidden" name="certificado" id="certificado" value="<?=$_GET['tk']?>">
		<input type="hidden" name="factura_id" id="factura_id" value="<?=$_GET['fid']?>">
	</form>

	<script>
		window.onload = function() {
		    document.getElementById("frm_express").submit();
		};
</script>

</body>
</html>