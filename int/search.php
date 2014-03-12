<?
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once './func.php';

$doSearch = false;
if (hasget('q')) {
	$doSearch = true;
	$q = getblank('q');

	$searchFields = array(//
	'BackerID', //
	'BackerName', //
	'BackerEmail', //
	'BackerAccessToken', //
	'TierID', //
		);

	$resFields = array(//
	'BackerID', //
	'BackerName', //
	'BackerEmail', //
	'BackerPledgeAmount', //
	'BackerPledgeStatus', //
	'TierID', //
	'BackerAccessToken', //
	sqlLinkMarkup("CONCAT('https://secure.jonathancoulton.com/cmsw/backer_home.php?accesstoken=',BackerAccessToken)",'backer home').' as LINK'
	);

	$db = dbobj();
	$searchStr = false;
	$whereClause = '';
	foreach ($searchFields as $sf) {
		$whereClause .= sprintf("CONVERT(%s USING utf8) LIKE '%%%s%%' OR ", $sf, $db -> escape_string($q));
	}
	$whereClause = substr($whereClause, 0, -4);

}
?>
<html>
	<head>
		<style>
			label {
				display: block;
			}
			td, th {
				padding: 4px;
			}
			#output {
				font-family: Courier;
			}
		</style>
	</head>
	<body>

		<form>
			<?=simpleGetTextField('search for', 'q'); ?>
			<input type="submit" name='submit' value='submit'>
		</form>
		<?
		if ($doSearch) {
			resToTable(sprintf('select %s from backers where (%s)', implode(',', $resFields), $whereClause));

		}
		?>
	</body>
</html>
