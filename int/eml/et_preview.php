<?

require_once '../../lib/common.php';

ini_set('max_execution_time', 77);

date_default_timezone_set('America/New_York');

function sendTemplateByIDWithMysqlResult($etid, $qid) {
	dbcon();
	$q = mysql_fetch_object(mysql_query(sprintf("SELECT * FROM EmailSelectionQueries where qid=%d", $qid)));
	$recipients = new MySQLResultEmailRecipientIterator($q -> q, dbcon(), $q -> email_field, $q -> toname_field, explode(',',$q -> addl_term_func));
	sendTemplatedEmail(getTemplateByID($etid), $recipients);
}

function previewTemplateByIDWithMysqlResult($etid, $qid, $niter = 10) {
	dbcon();
	$q = mysql_fetch_object(mysql_query(sprintf("SELECT * FROM EmailSelectionQueries where qid=%d", $qid)));
	$recipients = new MySQLResultEmailRecipientIterator($q -> q, dbcon(), $q -> email_field, $q -> toname_field, empty($q -> addl_term_func)?null:explode(',',$q -> addl_term_func));
	$etid = safe($etid);

	$et = getTemplateByID($etid);

	$n = 1;
	$str = "";
	foreach ($recipients as $rec) {
		if ($n > $niter)
			break;
		$message = createSwiftMessage($et, $rec);
		$str .= sprintf("-----------------------%d--<br><strong>HEADERS:</strong><br><pre>%s</pre><br><strong>BODY (html):</strong><br>%s-----------------------%d--<br><br><br>", $n, $message -> getHeaders() -> toString(), $message -> getBody(), $n);
		$n++;
	}
	return $str;
}

if (isset($_GET['etid']) && strlen($_GET['etid']) > 0 && isset($_GET['qid']) && strlen($_GET['qid']) > 0) {
	$etid = $_GET['etid'];
	$qid = $_GET['qid'];

	if (isset($_POST['send'])) {
		sendTemplateByIDWithMysqlResult($etid, $qid, false);
		header("Location: #");
	}

	if (isset($_GET['niter']) && strlen($_GET['niter']) > 0) {
		$niter = $_GET['niter'];
		echo previewTemplateByIDWithMysqlResult($etid, $qid, $niter);
	} else {
		echo previewTemplateByIDWithMysqlResult($etid, $qid);
	}

}
?>

<br>
<br>
<br>
<form method='post'>
	<input type='submit' name='send' value='SEND EM!'>
</form>
