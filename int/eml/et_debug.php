<?

require_once '../../lib/common.php';

date_default_timezone_set('America/New_York');

if (isset($_GET['etid']) && strlen($_GET['etid']) > 0) {
	$etid = $_GET['etid'];
	$et = getTemplateByID($etid);
}


$db = dbobj();

if (isset($_GET['qid'])) {
	$qid = $_GET['qid'];
	$prev = previewTemplateByIDWithMysqlResult($etid, $qid, 1);
} 


?>
<html>
	<head>
		<style>
			#prev {
				font-family: Arial, Helvetica, sans-serif;
				width: 600px;
				background-color: #f8dff1;
				padding: 25px;
				font-size: 14px;
			}
		</style>

	</head>
	<body>
		<h1>MERGE TERMS</h1>
		<pre>
	<?
	print_r(getEtMergeToks($et -> ethtml));
	?>
	</pre>
		<h1>PREVIEW: <?=(isset($mtarr) ? 'MERGED' : 'NO MERGE'); ?></h1>
		<div id="prev">
			<?=(isset($prev) ? $prev : $et -> ethtml); ?>
		</div>

		<form method='post'>
			<input type='submit' name='send' value='SEND IT!'><br/>
			<br/>
			<a href="<?='et_preview.php?' . $_SERVER['QUERY_STRING']; ?>">PREVIEW</a>
&nbsp;&nbsp;			<a href="<?='et_edit.php?' . $_SERVER['QUERY_STRING']; ?>">EDIT</a>
			<br/>
		</form>

		<h1>MARKUP</h1>
		<pre>
		<?="<br>" . htmlentities($et -> ethtml); ?>
		
	</pre>
		<br>
		<br>
		<br>
	</body>
</html>