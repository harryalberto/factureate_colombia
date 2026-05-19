<?
if (!isset($_SESSION['user']['nombre'])){
	echo "	<script>
				alert('Su sesion a caducado debe loguearse nuevamente !!');
				location.href = '../bo-index.php';
			</script>";
}
?>