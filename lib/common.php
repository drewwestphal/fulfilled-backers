<?

date_default_timezone_set('America/New_York');

//error_reporting(E_ALL);
//ini_set('display_errors', '1');

define("APP_ROOT", realpath(dirname(__FILE__)) . '/');

require_once APP_ROOT . 'lib/vendor/autoload.php';

require_once APP_ROOT . 'lib/dl_links.php';
require_once APP_ROOT . 'lib/et_code_v2.php';

setlocale(LC_ALL, 'en_US.utf8');

$GLOBALS['cmsw_DBusr'] = 'YOUR DB USER';
$GLOBALS['cmsw_DBpwd'] = 'YOUR DB PWD';
$GLOBALS['cmsw_DBname'] = 'YOUR DB';
$GLOBALS['cmsw_DBhost'] = 'localhost';

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
    foreach($array as $item) {
        $rv .= vsprintf($fmt, array_fill(0, $ct, $item));
    }
    return $rv;
}

function checkKeysForValueInArray($reqKeys, $toCheck) {
    foreach($reqKeys as $rk) {
        if(!((isset($toCheck[$rk])) && strlen($toCheck[$rk]) > 0))
            return false;
    }
    return true;
}
?>