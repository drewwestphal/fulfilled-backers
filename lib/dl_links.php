<?php

// FOR EXAMPLE
$linkbase = "https://secure.jonathancoulton.com/cmsw/dl/?k=";
use Aws\S3\S3Client;

function getAwsLink($name) {

	$AWSaccessKey = "YOUR AWS ACCESS KEY";
	$AWSsecretKey = "YOUR AWS SECRET KEY";
	$AWSbaseurl = "http://s3.amazonaws.com/YOUR BUCKET/";

	$s3 = S3Client::factory(array('key' => $AWSaccessKey, //
	'secret' => $AWSsecretKey));
	return $s3 -> getObjectUrl('codemonkeysaveworld', $name, '+180 minutes');
}

// generate a key
function getk() {
	dbcon();

	$i = 0;

	do {
		$k = base64_url_encode(openssl_random_pseudo_bytes(18, $did));

		$i++;
		if ($i > 1024) {
			die();
		}
	} while(!mysql_query("INSERT INTO dl_links (LinkKey) VALUES ('$k');"));

	return $k;
}

function getlinkurl($linkkey) {
	global $linkbase;
	return $linkbase . $linkkey;
}

function makelink($fileID, $ownerkey = '', $maxclick = 5, $visible = 1) {
	global $linkbase;

	dbcon();
	$k = getk();

	if (strlen($ownerkey) == 0) {
		$ownerkey = 'null';
	} else {
		$ownerkey = mysql_real_escape_string($ownerkey);
		$ownerkey = "'$ownerkey'";
	}

	mysql_query(sprintf("INSERT INTO  dl_links (  LinkKey ,  FileID ,  MaxClick ,  OwnerKey ,  DisplayLink ) 
VALUES (
'%s',  '%d',  '%d', %s ,  '%d'
) ON DUPLICATE 
KEY UPDATE FileID = VALUES (
FileID
), MaxClick = 
VALUES (
MaxClick
), OwnerKey = 
VALUES (
OwnerKey
), DisplayLink = 
VALUES (
DisplayLink
)
", $k, $fileID, $maxclick, $ownerkey, $visible)) or die(mysql_error());

	return $linkbase . $k;
}

function clicklink($k) {
	$link = dbcon();

	$ip = $_SERVER['REMOTE_ADDR'];

	// count the total number
	$q = mysql_query(sprintf("SELECT li.LinkKey, ( COUNT( cl.LinkKey ) < li.MaxClick OR li.MaxClick =0 ) AS AllowDL, fi.AWSPath FROM ( dl_links li JOIN dl_files fi ON li.FileID = fi.FileID ) LEFT JOIN dl_clicks cl ON cl.LinkKey = li.LinkKey WHERE li.LinkKey =  '%s' GROUP BY li.LinkKey ", $k)) or die();

	if (!$q || mysql_num_rows($q) < 1) {
		//return 'Sorry, This download link does not appear valid';
		die ;
	}
	$q = mysql_fetch_object($q);
	//print_r($q);
	//exit(0);

	if ($q -> AllowDL === '1') {
		// add a record to dl_clicks
		if (!mysql_query("INSERT INTO `dl_clicks` (`LinkKey` ,`IPAddress`) VALUES ( '$k',  '$ip')")) {
			return "I'm sorry there was an error with your download";
		}
	} else {
		return 'Sorry, this download link has exceeded its maximum number of download attempts';
	}

	header(sprintf('location: %s', getAwsLink($q -> AWSPath)));

	// we're done
	exit(0);
}
?>