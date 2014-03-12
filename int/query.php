<?php


if (isset($_POST['querytocsv']) && isset($_POST['csvfilename'])) {
	date_default_timezone_set('America/New_York');
	$dt = date('_M-j_Gi');

	restocsv($_POST['querytocsv'], $_POST['csvfilename'] . $dt . '.csv');

}

function resToTable($query, $tableClass = 'qtable', $sideways = false) {
	dbcon();

	$result = mysql_query($query) or cantdisplay($query, mysql_error());
	if (mysql_num_rows($result)) {
		$headers = array();
		$rows = array();

		for ($i = 0; $i < mysql_num_fields($result); $i++) {
			$headers[] = mysql_field_name($result, $i);
		}
		while ($row = mysql_fetch_row($result)) {
			$rows[] = $row;
		}
		if ($sideways) {
			fmttable_sideways($tableClass, $headers, $rows);
		} else {
			fmttable($tableClass, $headers, $rows);
		}
	} else {
		cantdisplay($query);
	}
}

function fmttable($tableClass, $headers, $rows) {
	echo "<table class='$tableClass'>";
	echo "<thead>";
	foreach ($headers as $hdr) {
		echo "<th>$hdr</th>";
	}
	echo "</thead>";
	echo "<tbody>";
	foreach ($rows as $r) {
		echo "<tr>";
		foreach ($r as $val) {
			echo "<td>$val</td>";
		}
		echo "</tr>";
	}
	echo "</tbody>";
	echo "</table>";
}

function fmttable_sideways($tableClass, $headers, $rows) {
	$newRows = array();
	for ($i = 0; $i < count($headers); $i++) {
		$newRows[$i] = array();
		$newRows[$i][] = $headers[$i];
		foreach ($rows as $row) {
			$newRows[$i][] = $row[$i];
		}
	}

	fmttable($tableClass, array(), $newRows);
}

function cantdisplay($query, $err = false) {
	echo "no results for query <span style='font-family:courier; font-size:10px;'>$query</span>";
	if ($err) {
		echo "error:<br>$err";
	}
}

function restocsv($query, $filename) {

	// from http://stackoverflow.com/questions/125113/php-code-to-convert-a-mysql-query-to-csv

	dbcon();
	$result = mysql_query($query);
	if (!$result)
		die(mysql_error());

	$num_fields = mysql_num_fields($result);
	$headers = array();
	for ($i = 0; $i < $num_fields; $i++) {
		$headers[] = mysql_field_name($result, $i);
	}
	$fp = fopen('php://output', 'w');
	if ($fp && $result) {
		header("Content-Description: File Transfer");
		header('Content-Encoding: UTF-8');
		header('Content-type: text/csv; charset=UTF-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Pragma: no-cache');
		header('Expires: 0');
		//echo "\xEF\xBB\xBF";
		fputcsv($fp, $headers);
		while ($row = mysql_fetch_row($result)) {
			fputcsv($fp, $row);
		}
		die();
	}
}
?>
