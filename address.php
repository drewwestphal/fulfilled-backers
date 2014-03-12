<?
require_once './lib/common.php';

$fmap = array(
	'ShipName' => 'Name',
	'ShipLine_1' => 'Line_1',
	'ShipLine_2' => 'Line_2',
	'ShipCity' => 'City',
	'ShipStateProvince' => 'State',
	'ShipPostCode' => 'Postal Code',
	'ShipCountry' => 'Country'
);

if(checkKeysForValueInArray(array(
	'ShipName',
	'ShipLine_1',
	'ShipCity',
	'ShipStateProvince',
	'ShipPostCode',
	'ShipCountry',
	'backertoken',
), $_POST)) {

	$db = dbobj();
	$backerToken = $db -> escape_string($_POST['backertoken']);
	$shipInfo = $db -> query(sprintf("SELECT _AllShippingAddresses . * 
FROM backers
NATURAL JOIN _AllShippingAddresses
WHERE BackerAccessToken = '%s'
", $backerToken));
	if($shipInfo -> num_rows <> 1) {
		printf("<h1>Sorry, there was a problem and we were unable to update your address record.</h1>");
	} else {
		$shipInfo = $shipInfo -> fetch_assoc();

		if(!isset($_POST['ShipLine_2'])) {
			$_POST['ShipLine_2'] = '';
		}
		$insqBM = "INSERT INTO backermeta (BackerID, BackerMetaKey, BackerMetaValue, BackerBatch) VALUES ";
		foreach($fmap as $postname => $dbname) {
			$insqBM .= sprintf("('%s'),", implode("','", array(
				$db -> escape_string($shipInfo['BackerID']),
				$db -> escape_string($dbname),
				$db -> escape_string(trim($_POST[$postname])),
				$db -> escape_string('custom'),
			)));
		}
		$insqBM = sprintf("%s ON DUPLICATE KEY UPDATE BackerMetaValue=VALUES(BackerMetaValue), BackerBatch=VALUES(BackerBatch)", substr($insqBM, 0, -1));
		$db -> query($insqBM);
		printf("<h1>Thank you. Your address has been updated</h1>");
	}
}
?>
<h2><a href="<?printf('./backer_home.php?accesstoken=%s',$backerToken);?>" >BACK</a></h2>
<br/>
<br/>
Please email <a href="mailto:codemonkeysaveworld@jonathancoulton.com">codemonkeysaveworld@jonathancoulton.com</a> with any questions about this form. 