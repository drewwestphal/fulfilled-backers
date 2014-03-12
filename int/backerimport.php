<?php
error_reporting(E_ALL);

ini_set('display_errors', '1');

function tier_read_cell($tier, $rowidx, $colname) {
    $colidx = array_search($colname, $tier['headers']);
    if($colidx === false) {
        return '';
    } else {
        return isset($tier['rows'][$rowidx][$colidx]) ? $tier['rows'][$rowidx][$colidx] : '';
    }
}

require_once './func.php';
ob_end_flush();

$list = array();
$rv = exec('ls -t1 ./kickstarterzips/"Kickstarter Backer Report - All Rewards - "*', $list);

$zipLoc = $list[0];
$zip = new ZipArchive();
if($zip -> open($zipLoc)) {
    tellus('Zip Archive Opened [%s][%s]. Contains %d files. [%s]', $zip -> status, $zip -> statusSys, $zip -> numFiles, $zipLoc);
} else {
    tellus('Zip Archive Open Failed [%s]', $zipLoc);
}

// Kickstarter Backer Report - $3.00 reward - May 23 11am.csv

$parse = array();
$startTime = microtime(true);
$timeWhen = microtime(true);
tellus('beginning zip file parse...');

for($i = 0; $i < $zip -> numFiles; $i++) {
    $csvNamePat = "/Kickstarter Backer Report - ((\S+ USD|No) reward( \d)?) - (\S+ \S+ \S+).csv/";
    $stat = $zip -> statIndex($i);
    $name = $stat['name'];
    preg_match($csvNamePat, $name, $matches);
    if(preg_match($csvNamePat, $name, $matches) && count($matches) > 4) {

        $parse[$matches[1]] = array();
        $tierRecord = &$parse[$matches[1]];
        $tierRecord['tierName'] = $matches[1];
        $tierRecord['batch'] = $matches[4];
        // iterate over the zip file
        $j = 0;
        //$csvFile = $zip -> getFromIndex($i);
        $csvPtr = $zip -> getStream($name);
        while($line = fgets($csvPtr)) {
            $line = trim($line);
            $row = str_getcsv($line);
            if($j == 0) {
                // get rid of UTF-8 BOM -- this maybe never worked...
                $row[0] = substr($row[0], 3);
                $tierRecord['headers'] = $row;
                $target = count($row);
            } else if(count($row) > 2) {
                // better solution invalidated by ks file
                //} else if (count($row) == $target) {

                $tierRecord['rows'][] = $row;

            }
            $j++;
        }

        //tellus(print_r($tierRecord, true));

        //tellus(print_r($parse, true));

    } else {

        //tellus(print_r($matches, true));

        tellus("no match found [%s]", $name);
    }
    //	tellus($stat['name']);
}
$nback = 0;
foreach($parse as $tier) {
    $nback += count($tier['rows']);
}

tellus('parse complete (%f seconds).... %d tiers parsed with a total of %d backers', microtime(true) - $timeWhen, count($parse), $nback);

$insfields = array(//
    'BackerID', //
    'BackerName', //
    'BackerEmail', //
    'BackerPledgeAmount', //
    'BackerPledgeStatus', //
    'TierID', //
    'BackerBatch', //
    'BackerShippingCategory', //
    'BackerAccessToken'//
);

$metafields = array(
    'Survey Answered' => 'Survey Answered',
    'Shipping Name' => 'Name',
    'Shipping Address 1' => 'Line_1',
    'Shipping Address 2' => 'Line_2',
    'Shipping City' => 'City',
    'Shipping State' => 'State',
    'Shipping Postal Code' => 'Postal Code',
    'Shipping Country' => 'Country',
    'Choices' => 'Choices',
);

$batchSalt = '_' . base64_url_encode(openssl_random_pseudo_bytes(6));
$db = dbobj();
$j = 0;
foreach($parse as $tier) {

    $timeWhen = microtime(true);

    $insq = sprintf("INSERT INTO backers (%s) VALUES ", implode(',', $insfields));
    $insqBM = "INSERT INTO backermeta (BackerID, BackerMetaKey, BackerMetaValue, BackerBatch) VALUES ";
    $hasMeta = false;
    for($i = 0; $i < count($tier['rows']); $i++) {

        $vals = array();
        $backerIDEscaped = $vals[] = $db -> escape_string(tier_read_cell($tier, $i, 'Backer Id'));
        $vals[] = $db -> escape_string(tier_read_cell($tier, $i, 'Backer Name'));
        $vals[] = $db -> escape_string(tier_read_cell($tier, $i, 'Email'));
        // get rid of $ in front of value
        $vals[] = $db -> escape_string(substr(tier_read_cell($tier, $i, 'Pledge Amount'), 1));
        $vals[] = $db -> escape_string(tier_read_cell($tier, $i, 'Pledged Status'));
        $vals[] = $db -> escape_string($tier['tierName']);
        $batchEscaped = $vals[] = $db -> escape_string($tier['batch'] . $batchSalt);
        $vals[] = $db -> escape_string(tier_read_cell($tier, $i, 'Shipping'));
        $vals[] = $db -> escape_string(base64_url_encode(openssl_random_pseudo_bytes(18, $did)));
        $insq .= sprintf("('%s'),", implode("','", $vals));

        // meta values...
        foreach($metafields as $mk => $mkv) {
            $mv = tier_read_cell($tier, $i, $mk);
            if(!empty($mv)) {
                $hasMeta = true;
                $insqBM .= sprintf("('%s'),", implode("','", array(
                    $backerIDEscaped,
                    $db -> escape_string($mkv),
                    $db -> escape_string($mv),
                    $batchEscaped
                )));
                if($mk === 'Choices' && strlen($mv) < 2) {
                    tellus('%s %s %s %s', $mv, $tier, $i, $mk);
                }
            }
        }
    }
    $updatePortion = '';
    foreach($insfields as $if) {

        switch($if) {

            case 'BackerAccessToken' :
                $updatePortion .= sprintf('%s=IF(%s is NULL,VALUES(%s), %s),', $if, $if, $if, $if);
                break;
            default :
                $updatePortion .= sprintf('%s=VALUES(%s),', $if, $if);
                break;
        }
    }

    $insq = sprintf("%s ON DUPLICATE KEY UPDATE %s", substr($insq, 0, -1), substr($updatePortion, 0, -1));
    $db -> query($insq);
    tellus('%dth insert [%s] (%f seconds), %d affected rows, error[%s]', $j, $tier['tierName'], microtime(true) - $timeWhen, $db -> affected_rows, $db -> error);

    $timeWhen = microtime(true);
    if($hasMeta) {
        $insqBM = sprintf("%s ON DUPLICATE KEY UPDATE BackerMetaValue=IF(BackerBatch<>'custom',VALUES(BackerMetaValue),BackerMetaValue), BackerBatch=IF(BackerBatch<>'custom',VALUES(BackerBatch),BackerBatch)", substr($insqBM, 0, -1));
        //echo $insqBM;
        $db -> query($insqBM);
        tellus('populating backermeta rows (%f seconds), %d affected rows, error[%s]', microtime(true) - $timeWhen, $db -> affected_rows, $db -> error);
    }
    //tellus(print_r($tier, true));
    //die();
    $j++;
}

$timeWhen = microtime(true);
$db -> query("INSERT INTO productsbybacker (`BackerID`,`ProductID`,`OwedQty`,`CreatedByBatch`) SELECT * FROM _ComputeRewardsByTier _c ON DUPLICATE KEY UPDATE OwedQty=DefaultQty,CreatedByBatch=BackerBatch") or die($db -> error);
tellus('Populating rows of products due by tier description... %d rows affected (%f seconds)', $db -> affected_rows, microtime(true) - $timeWhen);
$timeWhen = microtime(true);
$db -> query("INSERT INTO productsbybacker (`BackerID`,`ProductID`,`OwedQty`,`CreatedByBatch`) SELECT * FROM _ComputeAddOnRewards _c ON DUPLICATE KEY UPDATE OwedQty=IF(BackerBatch=CreatedByBatch,OwedQty+AddOnQty, AddOnQty),CreatedByBatch=BackerBatch") or die($db -> error);
tellus('Populating rows of products due by special order... %d rows affected (%f seconds)', $db -> affected_rows, microtime(true) - $timeWhen);
$timeWhen = microtime(true);
$db -> query("UPDATE  `backermeta` bm JOIN productsbybacker pb ON bm.BackerID = pb.BackerID AND pb.ProductID =  'Tshirt' SET pb.SkuResNotes = bm.BackerMetaValue WHERE  `BackerMetaKey` LIKE  'Choices'") or die($db -> error);
tellus('Adding Sku Resolution Notes to TShirts from Survey... %d rows affected (%f seconds)', $db -> affected_rows, microtime(true) - $timeWhen);

// do custom mods
tellus('performing last minute hacks... ');

tellus('Dealing with the guy\'s extra shirt..., etc ');


tellus('Total execution time %f seconds', microtime(true) - $startTime);
/*
 *
 foreach ($parse['$3.00 reward']['headers'] as $hdr) {
 tellus('[%s][%d][%s][%s]', $hdr, strlen($hdr), $hdr[0], htmlentities($hdr));
 }*/
//tellus(var_dump(array_search('Backer Id', $parse['$3.00 reward']['headers'])));
// last comma
//tellus(print_r($parse['$3.00 reward'], true));
?>

