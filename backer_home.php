<?


require_once './lib/common.php';

if(empty($_GET['accesstoken'])) {
    die("access denied... email codemonkeysaveworld@jonathancoulton.com for support if you believe you reached this page in error");
}

$startTime = microtime(true);
$timeWhen = microtime(true);
$extimes = array();

$db = dbobj();
$backerToken = $db -> escape_string($_GET['accesstoken']);

$supportLevel = $db -> query(sprintf("SELECT * , IF( BackerShippingCategory =  '', TierShippingMeta, BackerShippingCategory ) ProjectedShipType
FROM backers
NATURAL JOIN tiers
WHERE BackerAccessToken =  '%s'
", $backerToken));

if($db -> affected_rows <> 1) {
    die("access denied... email codemonkeysaveworld@jonathancoulton.com for support if you believe you reached this page in error");
}
$extimes['summary'] = microtime(true) - $timeWhen;
$timeWhen = microtime(true);

$physicalProducts = $db -> query(sprintf("SELECT * 
FROM backers
NATURAL JOIN productsbybacker
NATURAL JOIN products
WHERE IsPhysical
AND BackerAccessToken = '%s'
", $backerToken));

$extimes['physprod'] = microtime(true) - $timeWhen;
$timeWhen = microtime(true);

$digitalProducts = $db -> query(sprintf("SELECT * 
FROM backers
NATURAL JOIN productsbybacker
NATURAL JOIN products
WHERE NOT IsPhysical
AND BackerAccessToken = '%s'
", $backerToken));

$extimes['digprod'] = microtime(true) - $timeWhen;
$timeWhen = microtime(true);

$shipInfo = $db -> query(sprintf("SELECT _AllShippingAddresses . *, _AllShirtSizes.*
FROM backers
NATURAL JOIN _AllShippingAddresses
NATURAL LEFT JOIN _AllShirtSizes
WHERE BackerAccessToken = '%s'
", $backerToken));

if($shipInfo -> num_rows <> 1) {
    $shipInfo = false;
} else {
    $shipInfo = $shipInfo -> fetch_assoc();
}
unset($shipInfo['BackerID']);
foreach($shipInfo as $k => $v) {
    if(is_null($v)) {
        unset($shipInfo[$k]);
    }
}
$displayAddress = sprintf("%s\n%s\n%s%s, %s %s\n%s", $shipInfo['ShipName'], //
$shipInfo['ShipLine_1'], isset($shipInfo['ShipLine_2']) ? $shipInfo['ShipLine_2'] . "\n" : '',
//
$shipInfo['ShipCity'], $shipInfo['ShipStateProvince'], $shipInfo['ShipPostCode'], $shipInfo['ShipCountry']);
$extimes['shipinfo'] = microtime(true) - $timeWhen;
$timeWhen = microtime(true);

// note that there is a potential for a bug in here if other places generate dl
// codes or owed qty changes...
// (owed qty is always generated even if some are already taken care of)
$linksToGenerate = $db -> query(sprintf("SELECT FileID, BackerID, OwedQty
FROM backers
NATURAL JOIN productsbybacker
NATURAL JOIN products
NATURAL JOIN productfilecorrelations
NATURAL JOIN dl_files
WHERE NOT IsPhysical
AND CONCAT(FileID,'-',OwedQty) NOT 
IN (
SELECT CONCAT(FileID,'-',COUNT(FileID))
FROM dl_links
WHERE OwnerKey = BackerID
GROUP BY FileID
)
AND BackerAccessToken =  '%s'
", $backerToken));
$extimes['linksgenq'] = microtime(true) - $timeWhen;
$timeWhen = microtime(true);

$supportLevel = $supportLevel -> fetch_assoc();

$physlist = array();
while($prod = $physicalProducts -> fetch_assoc()) {
    $physlist[] = $prod['ProductDisplayName'] . (is_null($prod['SkuResNotes']) ? '' : ' (' . $prod['SkuResNotes'] . ')');
}

$diglist = array();
while($prod = $digitalProducts -> fetch_assoc()) {
    $diglist[] = $prod['ProductDisplayName'];
}

while($gen = $linksToGenerate -> fetch_assoc()) {
    for($i = 0; $i < $gen['OwedQty']; $i++) {
        makelink($gen['FileID'], $gen['BackerID']);
    }
}
$extimes['linksgenphp'] = microtime(true) - $timeWhen;
$timeWhen = microtime(true);

$redemptionCodesToAdd = $db -> query(sprintf("SELECT *,ProductID, BackerID, OwedQty - IFNULL( NCodesProvided, 0 ) AS QtyToReserve
FROM backers
NATURAL JOIN productsbybacker
NATURAL JOIN products
NATURAL JOIN (
SELECT DISTINCT ProductID
FROM dl_redemptioncodes
)t1
NATURAL LEFT JOIN (
SELECT ProductID, BackerID, COUNT( RedemptionCode ) AS NCodesProvided
FROM dl_redemptioncodes
WHERE BackerID is not null
GROUP BY BackerID, ProductID
)t2
WHERE NOT IsPhysical
AND BackerAccessToken =  '%s'
", $backerToken));
$extimes['redcodesq'] = microtime(true) - $timeWhen;
$timeWhen = microtime(true);

while($gen = $redemptionCodesToAdd -> fetch_assoc()) {
    $db -> query(sprintf("UPDATE dl_redemptioncodes 
                        SET BackerID='%s'
                        WHERE BackerID IS NULL 
                        AND RedemptionCodeIsPublic
                        AND ProductID =  '%s'
                        LIMIT %d
                        ", $gen['BackerID'], $gen['ProductID'], $gen['QtyToReserve']));
}
$extimes['addredcodes'] = microtime(true) - $timeWhen;
$timeWhen = microtime(true);

$linksAvailable = $db -> query(sprintf("SELECT ProductID, FileDisplayName, LinkKey Link, ProductDisplayName
FROM backers
NATURAL JOIN productsbybacker
NATURAL JOIN dl_linksNJ
NATURAL JOIN dl_files
NATURAL JOIN productfilecorrelations
NATURAL JOIN products
WHERE DisplayLink
AND BackerAccessToken =  '%s'
ORDER BY FileSortOrder
", $backerToken));
$extimes['avail links'] = microtime(true) - $timeWhen;
$timeWhen = microtime(true);

$linklist = array();
while($link = $linksAvailable -> fetch_assoc()) {
    $linklist[$link['ProductDisplayName']][$link['FileDisplayName']][] = getlinkurl($link['Link']);
}

$redemptionCodesAvailable = $db -> query(sprintf("SELECT ProductID, ProductDisplayName, RedemptionCode
FROM backers
NATURAL JOIN dl_redemptioncodes
NATURAL JOIN products
WHERE BackerAccessToken =  '%s'
ORDER BY ProductID
", $backerToken));

$extimes['availredcodes'] = microtime(true) - $timeWhen;
$timeWhen = microtime(true);

$redcodelist = array();
while($rc = $redemptionCodesAvailable -> fetch_assoc()) {
    $redcodelist[$rc['ProductDisplayName']][] = $rc['RedemptionCode'];
}

$extimes['total'] = microtime(true) - $startTime;
?>

<!doctype html>
<html>

	<head>
		<title>CMSW Backers</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
		<link rel="stylesheet" href="themes/superhero/bootstrap.css">
		<link rel="stylesheet" href="css/bootstrap-responsive.css">
		<link rel="stylesheet" href="https://djyhxgczejc94.cloudfront.net/builds/80037b02082b29f5f9cea127cab2a4ba4365ec67.css">
		<script src="js/jquery.min.js"></script>
		<script src="js/jquery.validate.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/php.js"></script>
		<?=variableToJS('linklist', $linklist); ?>
		<?=variableToJS('redcodelist', $redcodelist); ?>
		<?=variableToJS('shipinfo', $shipInfo); ?>
		<?=variableToJS('timing', $extimes); ?>
		<style>
			body {
				padding-bottom: 55px;
			}
			.modal {
				height: 77%;
			}
			.modal-body {
				height: 80%;
				overflow-y: auto;
			}
			.main-features {
				margin-top: 10px;
			}
			ul li ol li {
				font-size: 66%;
				line-height: 1.1;
			}
			#AddrEditModal ul {
				list-style: none;
			}
			#AddrEditModal label {
				font-weight: bold;
				margin-top: 11px;
				margin-bottom: 0;
			}
			#AddrEditModal input, select {
				margin-bottom: 0;
				margin-top: 2px;
				display: block;
			}
			#AddrEditModal input[type="submit"] {
				display: inline;
			}
			#AddrEditModal .examples {
				font-size: 77%;
				font-style: italic;
			}
			input.error {
				background-color: #f7d6d5;
			}
			select.error {
				background-color: #f7d6d5;
			}

		</style>
	</head>

	<body>
		<div class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container">
					<a class="brand" href="#">CMSW</a>
					<div class="navbar-content">
						<ul class="nav ">
							<!--
							<li class="active">
							<a href="#">Home</a>
							</li>
							!-->
						</ul>
					</div>
				</div>
			</div>
		</div>
		<div class="container">
			<div class="hero-unit hidden-phone">
				<h1>Code Monkey Save World</h1>
				<p>
					Thank you again for your support. Below you'll find a summary of your
					support level, along with the physical and digital products that you selected
					(if any) for your reward.
				</p>
				<p>
					As the information becomes available, this page will include links to
					download digital products, along with shipment information on the physical
					products
					that you've ordered.
				</p>
				<p>
					If you have a question about the information here--especially if we are
					missing something--please do not hesitate to get in touch. We'll do our
					best to get back to you quickly. Our email is
					<a href="mailto:codemonkeysaveworld@jonathancoulton.com">codemonkeysaveworld@jonathancoulton.com</a>.
				</p>
				<p></p>
			</div>
			<div class="row">
				<div class="span6">
					<h3>Support Level</h3>
					<p>
						<?printf('You pledged a total of $%0.2f. Your shipping preference was listed as %s. The full description of your reward tier is below.', $supportLevel['BackerPledgeAmount'], ($shipInfo ? $shipInfo['ActualShippingCategory'] : (empty($supportLevel['ProjectedShipType']) ? 'n/a' : $supportLevel['ProjectedShipType']))); ?>
					</p>
					<p>
						<em> <?=$supportLevel['TierFullText']; ?> </em>
					</p>
					<h3>Your Address</h3>
					<?
					if(count($physlist)){
					if($shipInfo){
					?>
					<p>
						You provided the following as your address.
					</p>
					<address style="margin-left:10px; font-style:italic;">
						<?=nl2br($displayAddress); ?>
					</address>
					<?if(intval($shipInfo['AddressLocked'])!==0){
					?>
					<p style="color:red; font-size:130%;">
						You reward has been prepared for shipment and address editing is locked. Please contact <a href="mailto:codemonkeysaveworld@jonathancoulton.com">codemonkeysaveworld@jonathancoulton.com</a>.
						with any further questions.
					</p>
					<?
                    } else {
                ?>

					<a href="#AddrEditModal" role="button" class="btn btn-large btn-primary" data-toggle="modal">Update Your Address</a>
					<?}
                        } else {
					?>
					<p>
						We have sent out surveys for your address via kickstarter. Please respond! Here are instructions from
						<a href="http://www.kickstarter.com/help/faq/backer+questions#faq_41811">http://www.kickstarter.com/help/faq/backer+questions#faq_41811</a>
						<blockquote>
							<em> If you think you might have missed a survey email, please log in to your Kickstarter account to check — you’ll see a yellow notification bar at the top of the site for any missed surveys. Once you complete a survey, you'll receive an email confirmation that includes a copy of your responses. </em>
						</blockquote>

					</p>
					<?

                    }
                    }
					?>
				</div>
				<div class="span3">
					<h3>Physical Products</h3>
					<ol>
						<?=patternImplode('
						<li data-prodname="%%%">
							%%%
						</li>', $physlist);
						?>
					</ol>
				</div>
				<div class="span3">
					<h3>Digital Products</h3>
					<p>
						Click on any orange links below
						to download your reward.
						Links that aren't yet orange
						are not ready for download.
					</p>
					<ol id="digprods">
						<?=patternImplode('
						<li data-prodname="%%%">
							%%%
						</li>', $diglist);
						?>

					</ol>
				</div>
			</div>
		</div>

		<div id="DownloadModal" class="modal hide fade">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
					&times;
				</button>
				<h3>Download <span class='ProdName'></span></h3>
			</div>
			<div class="modal-body">
				<p>
					Below are the download link(s) for
					the product you clicked. Note that
					you may have access to other downloads.
					To view those downloads, click the name
					of the product. Products without links
					have not had downloads made available yet.
				</p>
				<p style="font-weight:bold;color:red;">
					NOTE:
					File downloads may not work on your mobile device.
					Please try file downloads from your home computer.
				</p>
				<p style="font-size:77%;font-style:italic;">
					Looking for <a href="./dl/hashes.txt" target="_blank">checksums</a>?
				</p>
				<ul class="DownloadList"></ul>
			</div>
			<div class="modal-footer">
				<a href="#" data-dismiss="modal" class="btn btn-primary">Close</a>
			</div>
		</div>

		<div id="RedemptionCodeModal" class="modal hide fade">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
					&times;
				</button>
				<h3>Redeem <span class='ProdName'></span></h3>
			</div>
			<div class="modal-body">
				<p>
					Below are the redemption code(s) for
					the product you clicked. Note that
					you may have access to other redemption codes.
					To view those codes, click the name
					of the product. Products without links
					have not had redemption codes made available yet.
				</p>
				<p>
					<strong>REDEMPTION INSTRUCTIONS</strong>:
					To redeem the code,
					visit <a href="http://www.comixology.com/redeem" target=_blank>http://www.comixology.com/redeem</a>
					and follow the instructions.
					(You'll have to sign up for Comixology to use the code.)
					You'll then be able to read the issue on your computer via
					a browser or on a tablet or phone using the
					Comixology app.
				</p>
				<p style="font-weight:bold;color:red;">
					NOTE:
					Redemption codes have not been tested on mobile, please
					try from your home computer.
				</p>
				<ul class="RedemptionCodeList"></ul>
			</div>
			<div class="modal-footer">
				<a href="#" data-dismiss="modal" class="btn btn-primary">Close</a>
			</div>
		</div>

		<script type="text/javascript">
            $.each(linklist, function(product, downloadlist) {
                $(sprintf('#digprods [data-prodname="%s"]', product)).wrapInner('<a href="#"><a/>');
                $(sprintf('#digprods [data-prodname="%s"] a', product)).click(function(event) {
                    event.preventDefault();
                    var downloadModalHtml = '';
                    $.each(downloadlist, function(downloadname, downloadlinks) {
                        if (downloadlinks.length > 1) {
                            downloadModalHtml = sprintf('%s<li>%s - <strong><em>Multiple Links available</strong></em><ol>', downloadModalHtml, downloadname);
                            $.each(downloadlinks, function(idx, individualLink) {
                                downloadModalHtml = sprintf('%s<li><a href="%s">%s</a></li>', downloadModalHtml, individualLink, individualLink);
                            });
                            downloadModalHtml = sprintf('%s</ol></li>', downloadModalHtml);

                        } else {
                            downloadModalHtml = sprintf('%s<li><a href="%s">%s</a></li>', downloadModalHtml, downloadlinks, downloadname);
                        }
                    })
                    $('#DownloadModal .DownloadList').html(downloadModalHtml);
                    $('#DownloadModal .ProdName').text(product);
                    $('#DownloadModal').modal('show');
                    //$(linklist[key]).wrap('<li></li>')
                });
            });
		</script>
		<script type="text/javascript">
            $.each(redcodelist, function(product, redcodelist) {
                $(sprintf('#digprods [data-prodname="%s"]', product)).wrapInner('<a href="#"><a/>');
                $(sprintf('#digprods [data-prodname="%s"] a', product)).click(function(event) {
                    event.preventDefault();
                    var modalHtml = '';
                    $.each(redcodelist, function(idx, redcode) {
                        modalHtml = sprintf('%s<li>%s</li>', modalHtml, redcode);
                    })
                    $('#RedemptionCodeModal .RedemptionCodeList').html(modalHtml);
                    $('#RedemptionCodeModal .ProdName').text(product);
                    $('#RedemptionCodeModal').modal('show');
                    //$(linklist[key]).wrap('<li></li>')
                });
            });
		</script>
		<div id="AddrEditModal" class="modal hide fade">
			<form method="post" action="address.php">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						&times;
					</button>
					<h3>Update Your Address</h3>
					<p>
						Make any necessary changes and click "Save Address" to update your record.
					</p>
				</div>
				<div class="modal-body">
					<ul class="fields address grouped">
						<li class="name overlabels">
							<div class="field">
								<label class="overlabel overlabel-apply" >Name
									<input class="text"  name="ShipName" size="30" type="text">
								</label>
							</div>
						</li>
						<li class="address_1 overlabels">
							<div class="field">
								<label class="overlabel overlabel-apply">Address 1
									<input class="text"  name="ShipLine_1" size="30" type="text">
								</label>
								<div class="examples">
									Street address, P.O. box
								</div>
							</div>
						</li>
						<li class="address_2 overlabels">
							<div class="field">
								<label class="overlabel overlabel-apply">Address 2 (Optional)
									<input class="text"  name="ShipLine_2" size="30" type="text">
								</label>
								<div class="examples">
									Apartment, suite, unit, building, floor, etc.
								</div>
							</div>
						</li>
						<li class="city overlabels">
							<div class="field">
								<label class="overlabel overlabel-apply">City
									<input class="text" name="ShipCity" size="30" type="text">
								</label>
							</div>
						</li>
						<li class="state overlabels">
							<div class="field">
								<label class="overlabel overlabel-apply">State or Province
									<input class="text" name="ShipStateProvince" size="30" type="text">
								</label>
							</div>
						</li>
						<li class="zip overlabels">
							<div class="field">
								<label class="overlabel overlabel-apply">Zip or Postal Code
									<input class="text" name="ShipPostCode" size="30" type="text">
								</label>
							</div>
						</li>
						<li class="country" >
							<div class="field">
								<label class="overlabel overlabel-apply">Country
									<select class="select" name="ShipCountry">
										<option value="">Select Country</option>
										<option value="AF">Afghanistan</option>
										<option value="AX">Aland Islands</option>
										<option value="AL">Albania</option>
										<option value="DZ">Algeria</option>
										<option value="AS">American Samoa</option>
										<option value="AD">Andorra</option>
										<option value="AO">Angola</option>
										<option value="AI">Anguilla</option>
										<option value="AQ">Antarctica</option>
										<option value="AG">Antigua and Barbuda</option>
										<option value="AR">Argentina</option>
										<option value="AM">Armenia</option>
										<option value="AW">Aruba</option>
										<option value="AC">Ascension Island</option>
										<option value="AU">Australia</option>
										<option value="AT">Austria</option>
										<option value="AZ">Azerbaijan</option>
										<option value="BS">Bahamas</option>
										<option value="BH">Bahrain</option>
										<option value="BD">Bangladesh</option>
										<option value="BB">Barbados</option>
										<option value="BY">Belarus</option>
										<option value="BE">Belgium</option>
										<option value="BZ">Belize</option>
										<option value="BJ">Benin</option>
										<option value="BM">Bermuda</option>
										<option value="BT">Bhutan</option>
										<option value="BO">Bolivia, Plurinational State of</option>
										<option value="BQ">Bonaire, Sint Eustatius and Saba</option>
										<option value="BA">Bosnia and Herzegovina</option>
										<option value="BW">Botswana</option>
										<option value="BV">Bouvet Island</option>
										<option value="BR">Brazil</option>
										<option value="IO">British Indian Ocean Territory</option>
										<option value="BN">Brunei Darussalam</option>
										<option value="BG">Bulgaria</option>
										<option value="BF">Burkina Faso</option>
										<option value="BI">Burundi</option>
										<option value="KH">Cambodia</option>
										<option value="CM">Cameroon</option>
										<option value="CA">Canada</option>
										<option value="CV">Cape Verde</option>
										<option value="KY">Cayman Islands</option>
										<option value="CF">Central African Republic</option>
										<option value="TD">Chad</option>
										<option value="CL">Chile</option>
										<option value="CN">China</option>
										<option value="CX">Christmas Island</option>
										<option value="CC">Cocos (Keeling) Islands</option>
										<option value="CO">Colombia</option>
										<option value="KM">Comoros</option>
										<option value="CG">Congo</option>
										<option value="CD">Congo, the Democratic Republic of the</option>
										<option value="CK">Cook Islands</option>
										<option value="CR">Costa Rica</option>
										<option value="CI">Côte d'Ivoire</option>
										<option value="HR">Croatia</option>
										<option value="CU">Cuba</option>
										<option value="CW">Curaçao</option>
										<option value="CY">Cyprus</option>
										<option value="CZ">Czech Republic</option>
										<option value="DK">Denmark</option>
										<option value="DJ">Djibouti</option>
										<option value="DM">Dominica</option>
										<option value="DO">Dominican Republic</option>
										<option value="EC">Ecuador</option>
										<option value="EG">Egypt</option>
										<option value="SV">El Salvador</option>
										<option value="GQ">Equatorial Guinea</option>
										<option value="ER">Eritrea</option>
										<option value="EE">Estonia</option>
										<option value="ET">Ethiopia</option>
										<option value="FK">Falkland Islands (Malvinas)</option>
										<option value="FO">Faroe Islands</option>
										<option value="FJ">Fiji</option>
										<option value="FI">Finland</option>
										<option value="FR">France</option>
										<option value="GF">French Guiana</option>
										<option value="PF">French Polynesia</option>
										<option value="TF">French Southern Territories</option>
										<option value="GA">Gabon</option>
										<option value="GM">Gambia</option>
										<option value="GE">Georgia</option>
										<option value="DE">Germany</option>
										<option value="GH">Ghana</option>
										<option value="GI">Gibraltar</option>
										<option value="GR">Greece</option>
										<option value="GL">Greenland</option>
										<option value="GD">Grenada</option>
										<option value="GP">Guadeloupe</option>
										<option value="GU">Guam</option>
										<option value="GT">Guatemala</option>
										<option value="GG">Guernsey</option>
										<option value="GN">Guinea</option>
										<option value="GW">Guinea-Bissau</option>
										<option value="GY">Guyana</option>
										<option value="HT">Haiti</option>
										<option value="HM">Heard Island and McDonald Islands</option>
										<option value="VA">Holy See (Vatican City State)</option>
										<option value="HN">Honduras</option>
										<option value="HK">Hong Kong</option>
										<option value="HU">Hungary</option>
										<option value="IS">Iceland</option>
										<option value="IN">India</option>
										<option value="ID">Indonesia</option>
										<option value="IR">Iran, Islamic Republic of</option>
										<option value="IQ">Iraq</option>
										<option value="IE">Ireland</option>
										<option value="IM">Isle of Man</option>
										<option value="IL">Israel</option>
										<option value="IT">Italy</option>
										<option value="JM">Jamaica</option>
										<option value="JP">Japan</option>
										<option value="JE">Jersey</option>
										<option value="JO">Jordan</option>
										<option value="KZ">Kazakhstan</option>
										<option value="KE">Kenya</option>
										<option value="KI">Kiribati</option>
										<option value="KP">Korea, Democratic People's Republic of</option>
										<option value="KR">Korea, Republic of</option>
										<option value="KV">Kosovo</option>
										<option value="KW">Kuwait</option>
										<option value="KG">Kyrgyzstan</option>
										<option value="LA">Lao People's Democratic Republic</option>
										<option value="LV">Latvia</option>
										<option value="LB">Lebanon</option>
										<option value="LS">Lesotho</option>
										<option value="LR">Liberia</option>
										<option value="LY">Libya</option>
										<option value="LI">Liechtenstein</option>
										<option value="LT">Lithuania</option>
										<option value="LU">Luxembourg</option>
										<option value="MO">Macao</option>
										<option value="MK">Macedonia, The Former Yugoslav Republic Of</option>
										<option value="MG">Madagascar</option>
										<option value="MW">Malawi</option>
										<option value="MY">Malaysia</option>
										<option value="MV">Maldives</option>
										<option value="ML">Mali</option>
										<option value="MT">Malta</option>
										<option value="MH">Marshall Islands</option>
										<option value="MQ">Martinique</option>
										<option value="MR">Mauritania</option>
										<option value="MU">Mauritius</option>
										<option value="YT">Mayotte</option>
										<option value="MX">Mexico</option>
										<option value="FM">Micronesia, Federated States of</option>
										<option value="MD">Moldova, Republic of</option>
										<option value="MC">Monaco</option>
										<option value="MN">Mongolia</option>
										<option value="ME">Montenegro</option>
										<option value="MS">Montserrat</option>
										<option value="MA">Morocco</option>
										<option value="MZ">Mozambique</option>
										<option value="MM">Myanmar</option>
										<option value="NA">Namibia</option>
										<option value="NR">Nauru</option>
										<option value="NP">Nepal</option>
										<option value="NL">Netherlands</option>
										<option value="AN">Netherlands Antilles</option>
										<option value="NC">New Caledonia</option>
										<option value="NZ">New Zealand</option>
										<option value="NI">Nicaragua</option>
										<option value="NE">Niger</option>
										<option value="NG">Nigeria</option>
										<option value="NU">Niue</option>
										<option value="NF">Norfolk Island</option>
										<option value="MP">Northern Mariana Islands</option>
										<option value="NO">Norway</option>
										<option value="OM">Oman</option>
										<option value="PK">Pakistan</option>
										<option value="PW">Palau</option>
										<option value="PS">Palestinian Territory, Occupied</option>
										<option value="PA">Panama</option>
										<option value="PG">Papua New Guinea</option>
										<option value="PY">Paraguay</option>
										<option value="PE">Peru</option>
										<option value="PH">Philippines</option>
										<option value="PN">Pitcairn</option>
										<option value="PL">Poland</option>
										<option value="PT">Portugal</option>
										<option value="PR">Puerto Rico</option>
										<option value="QA">Qatar</option>
										<option value="RE">Reunion</option>
										<option value="RO">Romania</option>
										<option value="RU">Russian Federation</option>
										<option value="RW">Rwanda</option>
										<option value="BL">Saint Barthelemy</option>
										<option value="SH">Saint Helena, Ascension and Tristan da Cunha</option>
										<option value="KN">Saint Kitts and Nevis</option>
										<option value="LC">Saint Lucia</option>
										<option value="MF">Saint Martin (French part)</option>
										<option value="PM">Saint Pierre and Miquelon</option>
										<option value="VC">Saint Vincent and the Grenadines</option>
										<option value="WS">Samoa</option>
										<option value="SM">San Marino</option>
										<option value="ST">Sao Tome and Principe</option>
										<option value="SA">Saudi Arabia</option>
										<option value="SN">Senegal</option>
										<option value="RS">Serbia</option>
										<option value="SC">Seychelles</option>
										<option value="SL">Sierra Leone</option>
										<option value="SG">Singapore</option>
										<option value="SX">Sint Maarten (Dutch part)</option>
										<option value="SK">Slovakia</option>
										<option value="SI">Slovenia</option>
										<option value="SB">Solomon Islands</option>
										<option value="SO">Somalia</option>
										<option value="ZA">South Africa</option>
										<option value="GS">South Georgia and the South Sandwich Islands</option>
										<option value="SS">South Sudan, Republic of</option>
										<option value="ES">Spain</option>
										<option value="LK">Sri Lanka</option>
										<option value="SD">Sudan</option>
										<option value="SR">Suriname</option>
										<option value="SJ">Svalbard and Jan Mayen</option>
										<option value="SZ">Swaziland</option>
										<option value="SE">Sweden</option>
										<option value="CH">Switzerland</option>
										<option value="SY">Syrian Arab Republic</option>
										<option value="TW">Taiwan</option>
										<option value="TJ">Tajikistan</option>
										<option value="TZ">Tanzania, United Republic of</option>
										<option value="TH">Thailand</option>
										<option value="TL">Timor-Leste</option>
										<option value="TG">Togo</option>
										<option value="TK">Tokelau</option>
										<option value="TO">Tonga</option>
										<option value="TT">Trinidad and Tobago</option>
										<option value="TA">Tristan da Cunha</option>
										<option value="TN">Tunisia</option>
										<option value="TR">Turkey</option>
										<option value="TM">Turkmenistan</option>
										<option value="TC">Turks and Caicos Islands</option>
										<option value="TV">Tuvalu</option>
										<option value="UG">Uganda</option>
										<option value="UA">Ukraine</option>
										<option value="AE">United Arab Emirates</option>
										<option value="GB">United Kingdom</option>
										<option value="US">United States</option>
										<option value="UM">United States Minor Outlying Islands</option>
										<option value="UY">Uruguay</option>
										<option value="UZ">Uzbekistan</option>
										<option value="VU">Vanuatu</option>
										<option value="VE">Venezuela, Bolivarian Republic of</option>
										<option value="VN">Viet Nam</option>
										<option value="VG">Virgin Islands, British</option>
										<option value="VI">Virgin Islands, U.S.</option>
										<option value="WF">Wallis and Futuna</option>
										<option value="EH">Western Sahara</option>
										<option value="YE">Yemen</option>
										<option value="ZM">Zambia</option>
										<option value="ZW">Zimbabwe</option>
									</select></label>
							</div>
						</li>
					</ul>
				</div>
				<div class="modal-footer">
					<a href="#" data-dismiss="modal" class="btn btn-primary">Close</a>
					<input type="submit" name="submitaddress" value="Save Address" class="btn btn-success">

				</div>
				<input type="hidden" name="backertoken" value="<?=$backerToken; ?>">
			</form>
		</div>

		<script type="text/javascript">
            $('#AddrEditModal select option').each(function() {
                $(this).val($(this).text());
            });
            $($('#AddrEditModal select option')[0]).val('')

            $.each(shipinfo, function(idx, val) {
                $(sprintf('input[name="%s"],select[name="%s"]', idx, idx)).val(val);
                // a HACK!
                $(sprintf('span[name="%s"]', idx)).text(val);

            });

            $('#AddrEditModal form').validate({
                //debug:true,
                rules : {
                    ShipName : "required",
                    ShipLine_1 : "required",
                    ShipCity : "required",
                    ShipStateProvince : "required",
                    ShipPostCode : "required",
                    ShipCountry : "required",

                },

                errorPlacement : function(error, element) {
                },
                errorElement : null,
                highlight : function(element, errorClass, validClass) {
                    $(element).addClass(errorClass).removeClass(validClass);
                },
                unhighlight : function(element, errorClass, validClass) {
                    $(element).removeClass(errorClass).addClass(validClass);
                },
                invalidHandler : function(form, validator) {
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                        var msg = sprintf("Before proceeding, please correct the %d pink highlighted field%s.", errors, (errors == 1 ? "" : "s"));
                        alert(msg);
                    }
                },
            });

		</script>
	</body>
</html>