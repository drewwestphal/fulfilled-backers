<?

require_once '../lib/common.php';
require_once './query.php';

$db = dbobj();
$q = $db -> query("SELECT * 
FROM  `tiers` 
NATURAL JOIN productsbytier
ORDER BY TierMinPledge ASC , TierID ASC, ProductID ASC
");
?>

<html>
	<head>
		<style>
			body {
				margin-top: 22px;
				margin-left: 22px;
				width: 777px;
			}
			h3 {
				margin-bottom: 0;
			}
			ol {
				margin-bottom: 2em;
			}
		</style>
	</head>
	<body>

		<?
		$labels = array();
		$lists = array();

		$i = -1;
		$labels[-1] = 'sdfdsds';
		while ($row = $q -> fetch_assoc()) {
			$rowlabel = sprintf('<h3>%s</h3><strong>(%s)</strong><br/>%s<br/>', $row['TierDescName'], $row['TierID'], $row['TierFullText']);
			if ($labels[$i] <> $rowlabel) {
				$i++;
			}

			$labels[$i] = $rowlabel;
			$lists[$i][] = $row['ProductID'] . ' (' . $row['DefaultQty'] . ')';
		}

		for ($i = 0; $i < count($labels); $i++) {
			print $labels[$i];
			printf('<ol><li>%s</li></ol>', implode('</li><li>', $lists[$i]));
		}
		?>
	</body>
</html>
