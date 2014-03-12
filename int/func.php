<?
require_once '../lib/common.php';
require_once './query.php';

function qStringCreate($varr = array()) {
	$arr = array_merge($_GET, $varr);
	return http_build_query($arr);
}

function tellus($str) {
	printf("<pre>%s</pre>", call_user_func_array('sprintf', func_get_args()));
}

function getblank($gstr) {
	if (isset($_GET[$gstr]))
		return $_GET[$gstr];
	return '';
}

function postblank($gstr) {
	if (isset($_POST[$gstr]))
		return $_POST[$gstr];
	return '';
}

function hasget($gstr) {
	return (strlen(getblank($gstr)) > 0);
}

function simpleGetTextField($label, $getvarname) {
	return sprintf('<label>%s: <input type="text" name="%s" value="%s" /></label>', $label,$getvarname,getblank($getvarname));

}

function sqlLinkMarkup($fieldName, $linkText) {
	return sprintf("IFNULL(CONCAT('<a href=\\'',%s,'\\' target=_blank>%s</a>'),'NO LINK')", $fieldName, $linkText);
}


?>