<?php

require_once '../lib/common.php';



if (isset($_GET['k']) && $_GET['k'] != '') {
	$err = clicklink($_GET['k']);
	//die();
} else {
	$err = 'Sorry, this is not a valid download link';
}
?>

<html>
	<head>
		<title>failed</title>
	</head>
	<body>
		<?echo "<pre>$err</pre>"; ?>
		<br/>Contact codemonkeysaveworld@jonathancoulton.com with any additional questions or for support.
	</body>
</html>
