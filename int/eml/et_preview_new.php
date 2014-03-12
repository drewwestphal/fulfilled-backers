<?

date_default_timezone_set('America/New_York');

error_reporting(E_ALL);
ini_set('display_errors', '1');

// IF YOU'D RATHER USE THIS FILE YOU NEED TO LOAD IT INSTEAD OF THE OTHER ET_PREVIEW OR LINK TO ET_CODE V3 some other way... 

setlocale(LC_ALL, 'en_US.utf8');

function dbobj() {
	return new mysqli($GLOBALS['cmsw_DBhost'], $GLOBALS['cmsw_DBusr'], $GLOBALS['cmsw_DBpwd'], $GLOBALS['cmsw_DBname']);
}

// this function for legacy use only
function dbcon() {
	$link = mysql_connect($GLOBALS['cmsw_DBhost'], $GLOBALS['cmsw_DBusr'], $GLOBALS['cmsw_DBpwd']);
	mysql_select_db($GLOBALS['cmsw_DBname'], $link);
	return $link;
}

// redirect to a page, then kill the current script
function redirKill($addr, $rsecs = 0, $estatus = 0) {
	header("refresh:$rsecs; url=$addr");
	exit(0);
}

function variableToJS($jsName, $var) {
	return sprintf('<script type="text/javascript">%s=%s;</script>', $jsName, json_encode($var));
}

function patternImplode($string, $array) {
	$ct = 0;
	$rv = '';
	$fmt = str_replace('%%%', '%s', $string, $ct);
	foreach ($array as $item) {
		$rv .= vsprintf($fmt, array_fill(0, $ct, $item));
	}
	return $rv;
}

ini_set('max_execution_time', 77);

date_default_timezone_set('America/New_York');

function sendTemplateByIDWithMysqlResult($etid, $qid) {
	dbcon();
	$q = mysql_fetch_object(mysql_query(sprintf("SELECT * FROM EmailSelectionQueries where qid=%d", $qid)));
	$recipients = new MySQLResultEmailRecipientIterator($q -> q, dbcon(), $q -> email_field, $q -> toname_field, explode(',', $q -> addl_term_func));
	sendTemplatedEmail(getTemplateByID($etid), $recipients);
	die();
}

function previewTemplateByIDWithMysqlResult($etid, $qid, $niter = 10) {
	dbcon();
	$q = mysql_fetch_object(mysql_query(sprintf("SELECT * FROM EmailSelectionQueries where qid=%d", $qid)));
	$recipients = new MySQLResultEmailRecipientIterator($q -> q, dbcon(), $q -> email_field, $q -> toname_field, empty($q -> addl_term_func) ? null : explode(',', $q -> addl_term_func));
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
