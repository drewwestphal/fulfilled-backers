<?

define('MESSAGE_CHUNKING_SIZE', 500);
define('MESSAGE_SERVER_CAT', 'cmsw');

function safe($value) {
	return mysql_real_escape_string($value);
}

interface EmailRecipient {
	// must return a valid email
	public function toAddress();
	// null or a name to assoc with email address
	public function toName();
	// assoc array of merg terms w/ the appropriate values or null;
	public function mergeTermArray();
}

class BasicEmailRecipient implements EmailRecipient {

	protected $toAddress;
	protected $toName;
	protected $mergeTermArray;

	public function __construct($toAddress, $toName, $mergeTermArray, $addlMergeTermUserFunc = null) {
		$this -> toAddress = $toAddress;
		$this -> toName = $toName;

		if (is_array($mergeTermArray) && !is_null($addlMergeTermUserFunc)) {
			if (!is_array($addlMergeTermUserFunc)) {
				$addlMergeTermUserFunc = array($addlMergeTermUserFunc);
			}
			foreach ($addlMergeTermUserFunc as $addlfunc) {
				if (empty($addlfunc))
					continue;
				$addArr = call_user_func($addlfunc, $mergeTermArray);
				if (is_array($addArr)) {
					$mergeTermArray = array_merge($mergeTermArray, $addArr);
				}
			}

		}

		$this -> mergeTermArray = $mergeTermArray;
	}

	function toAddress() {
		return $this -> toAddress;
	}

	function toName() {
		return $this -> toName;
	}

	function mergeTermArray() {
		return $this -> mergeTermArray;
	}

}

class MySQLResultEmailRecipientIterator implements Iterator {
	private $pos = 0;
	private $n = 0;
	private $res = null;
	private $toAddressFieldName;
	private $toNameFieldName;
	private $addlMergeTermUserFunc;

	public function __construct($queryStr, $dbLink, $toAddressFieldName, $toNameFieldName = null, $addlMergeTermUserFunc = null) {
		$this -> res = mysql_query($queryStr, $dbLink);
		$this -> n = mysql_num_rows($this -> res);
		$this -> toAddressFieldName = $toAddressFieldName;
		$this -> toNameFieldName = $toNameFieldName;
		$this -> addlMergeTermUserFunc = $addlMergeTermUserFunc;
	}

	function rewind() {
		$this -> pos = 0;
		mysql_data_seek($this -> res, $this -> pos);
	}

	function current() {
		mysql_data_seek($this -> res, $this -> pos);
		$assoc = mysql_fetch_assoc($this -> res);
		$to = $assoc[$this -> toAddressFieldName];
		$toName = null;
		if (!is_null($this -> toNameFieldName) && isset($assoc[$this -> toNameFieldName])) {
			$toName = $assoc[$this -> toNameFieldName];
		}
		return new BasicEmailRecipient($to, $toName, $assoc, $this -> addlMergeTermUserFunc);
	}

	function key() {
		return $this -> pos;
	}

	function next() {
		++$this -> pos;
	}

	function valid() {
		return $this -> pos < $this -> n;
	}

}

function getTemplateByID($etid) {
	dbcon();
	$etid = safe($etid);

	$et = mysql_fetch_object(mysql_query("select * from EmailTemplates join EmailSenders on EmailTemplates.etsender=EmailSenders.SenderID where etid='$etid'"));
	if (!$et) {
		trigger_error('template by id does not exist', E_USER_ERROR);
	}
	return $et;
}

function getTemplateByName($etname) {
	dbcon();
	$etname = safe($etname);

	$et = mysql_fetch_object(mysql_query("select * from EmailTemplates join EmailSenders on EmailTemplates.etsender=EmailSenders.SenderID where etname='$etname'"));
	if (!$et) {
		trigger_error('template by name does not exist', E_USER_ERROR);
	}
	return $et;
}

function sendTemplatedEmail($template, $recipients, $queueing = true) {
	// allow pass in single recipient
	if ($recipients instanceof EmailRecipient) {
		$recipients = array($recipients);
		$class = 'SingleRecipient';
	} else {
		$class = get_class($recipients);
	}

	$mergeToks = getEtMergeToks($template -> etsubj . ' ' . $template -> ethtml . ' ' . $template -> ettext);
	$theSubs = array();
	$theRecips = array();

	// populate recipient and merges possible for each recipient
	foreach ($recipients as $rec) {
		$theRecips[] = ($rec -> toName() ? $rec -> toName() . "<" . $rec -> toAddress() . ">" : $rec -> toAddress());

		$mergeTerms = $rec -> mergeTermArray();
		$subKeys = array_intersect(array_keys($mergeTerms), $mergeToks);
		foreach ($subKeys as $sk) {
			$theSubs["[:$sk:]"][] = $mergeTerms[$sk];
		}
	}

	// the merge must be available for all messages or we'll die here
	$nRecip = count($theRecips);
	foreach ($theSubs as $subKey => $subValArray) {
		if (count($subValArray) !== $nRecip) {
			die('invalid subs given... killing mail process. None sent.');
		}
	}

	// chunk the messages & send them!
	$nChunks = floor($nRecip / MESSAGE_CHUNKING_SIZE);
	$sendgrid = getSendGrid();
	for ($i = $nRecip; $i > 0; $i -= MESSAGE_CHUNKING_SIZE) {

		$sliceOffset = max($i - MESSAGE_CHUNKING_SIZE, 0);
		$sliceLength = MESSAGE_CHUNKING_SIZE + min($i - MESSAGE_CHUNKING_SIZE, 0);
		$theMail = new SendGrid\Mail();

		// array quantities...
		$theMail -> setTos(array_slice($theRecips, $sliceOffset, $sliceLength));
		foreach ($theSubs as $subKey => $subValArray) {
			$theMail -> addSubstitution($subKey, array_slice($subValArray, $sliceOffset, $sliceLength));
		}

		// static quantities
		$theMail -> setFrom($template -> SenderAddress);
		$theMail -> setFromName($template -> SenderName);
		$theMail -> setSubject($template -> etsubj);
		$theMail -> setText($template -> ettext);
		$theMail -> setHtml($template -> ethtml);
		$theMail -> setCategories(array(MESSAGE_SERVER_CAT, $template -> etname, $template -> etname . '_ch_' . $nChunks));

		printf("<pre>CHUNK IDX : %d\nsliceOffset=%d\nsliceLength=%d\ni=%d\n\n", $nChunks, $sliceOffset, $sliceLength, $i);
		//print_r($theMail);
		echo "</pre>";
		$sendgrid -> smtp -> send($theMail);

		$nChunks--;
	}

}

function getSendGrid() {
	$sendgrid = new SendGrid('SENDGRID USER NAME', 'SENDGRID PASSWORD');
	return $sendgrid;
}

function getSwiftMailer($queueing = true) {
	//Swift_Preferences::getInstance()->setCacheType('null');
	return Swift_Mailer::newInstance(Swift_SendmailTransport::newInstance(sprintf('/usr/local/bin/msmtp%s -t -i -a default', ($queueing ? '-enqueue.sh' : ''))));
}

// pass the template and recipient objects
function createSwiftMessage($et, $rec, $cat = null) {
	require_once 'SmtpApiHeader.php';

	//add sendgrid headers
	$hdr = new SmtpApiHeader();

	// populate categories
	$cats = array('et_v2');
	if (!is_null($et -> etname)) {
		$cats[] = $et -> etname;
	}
	if (!is_null($cat)) {
		$cats[] = $cat;
	}
	$hdr -> setCategory($cats);

	// Create the message
	$message = Swift_Message::newInstance();
	$headers = $message -> getHeaders();
	$headers -> addTextHeader('X-SMTPAPI', $hdr -> asJSON());

	// Give the message a subject
	$message -> setSubject(processEtMergeSubs($et -> etsubj, $rec -> mergeTermArray()));

	// Set the From address with an associative array
	$message -> setFrom(array($et -> SenderAddress => $et -> SenderName));

	// Set the To addresses with an associative array
	$toArray = array($rec -> toAddress());
	if (!is_null($rec -> toName())) {
		$toArray = array($rec -> toAddress() => $rec -> toName());
	}
	$message -> setTo($toArray);
	$message -> setBody(processEtMergeSubs($et -> ethtml, $rec -> mergeTermArray()), 'text/html');
	// Add the text body
	$message -> addPart(processEtMergeSubs($et -> ettext, $rec -> mergeTermArray()));

	return $message;
}

function processEtMergeSubs($subj, $mergeArray) {
	$reqMergeValues = getEtMergeToks($subj);
	//print_r($reqMergeValues);

	// replace all merge values for which we've got a replacement
	foreach ($reqMergeValues as $tok) {
		if (isset($mergeArray[$tok])) {
			$subj = str_replace("[:$tok:]", $mergeArray[$tok], $subj);
		}
	}

	return $subj;
}

function getEtMergeToks($subj) {
	// find all unique merge tokens

	$pat = '#\[:([a-zA-Z0-9_]+):\]#';
	$matches = array();
	preg_match_all($pat, $subj, $matches);
	//print_r($matches);
	$reqMergeValues = array_unique($matches[1]);
	return $reqMergeValues;
}

function logSentMessage($et, $message, $class = '') {
	date_default_timezone_set('America/New_York');
	$logFileName = date("Y-m-d") . ".txt";
	$logDir = '../int/eml/logs/';
	$log = fopen($logDir . $logFileName, 'a');
	$headers = $message -> getHeaders();
	fprintf($log, "%s\t%s\t%s\t%s\t%s\n", date("H:i:s"), substr($headers -> get('To'), 0, -2), substr($headers -> get('Subject'), 0, -2), substr($headers -> get('X-SMTPAPI'), 0, -2), $class);
	fclose($log);

}
?>