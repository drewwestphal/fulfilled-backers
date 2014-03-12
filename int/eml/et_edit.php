<?

require_once '../../lib/common.php';
dbcon();

if (isset($_GET['etid']) && strlen($_GET['etid']) > 0) {
	$etid = safe($_GET['etid']);
	if (isset($_POST['save']) || isset($_POST['debug']) || isset($_POST['preview'])) {
		$ethtml = safe($_POST['ethtml']);
		$ettext = safe($_POST['ettext']);
		$etname = safe($_POST['etname']);
		$etsubj = safe($_POST['etsubj']);

		mysql_query(sprintf("REPLACE INTO EmailTemplates (etid,ethtml,ettext,etname,etsubj) VALUES (%d,'%s','%s','%s','%s')", $etid, $ethtml, $ettext, $etname, $etsubj)) or die('et save error');
	}
	if (isset($_POST['debug'])) {
		redirKill('et_debug.php?' . $_SERVER['QUERY_STRING']);
	}
	if (isset($_POST['preview'])) {
		redirKill('et_preview.php?' . $_SERVER['QUERY_STRING']);
	}

	$et = mysql_fetch_object(mysql_query("SELECT ethtml, ettext, etname, etsubj, etcat from EmailTemplates where etid='$etid'")) or redirKill($_SERVER['SCRIPT_NAME']);

} else {

	// garbage collect
	mysql_query("
DELETE FROM  `EmailTemplates` WHERE (
NOW( ) - INTERVAL 12 HOUR
) >  `etlastupdate` AND  `etname` IS NULL AND  `etsubj` IS NULL
AND  `ettext` IS NULL AND  `ethtml` IS NULL");
	mysql_query("INSERT INTO EmailTemplates () VALUES ()");
	redirKill($_SERVER['SCRIPT_NAME'] . '?etid=' . mysql_insert_id());
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Edit Email Template</title>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
		<script type="text/javascript" src="tiny_mce/jquery.tinymce.js"></script>
		<script type="text/javascript">
			$(document).ready(function() {

				$('#ethtml').tinymce({

					// Location of TinyMCE script
					script_url : 'tiny_mce/tiny_mce.js',
					relative_urls : false,
					remove_script_host : false,
					//document_base_url : base_path(),

					// General options
					theme : "advanced",
					plugins : "autolink,lists,pagebreak,style,layer,table,save,contextmenu,paste,directionality,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
					paste_text_sticky : true,
					setup : function(ed) {
						ed.onInit.add(function(ed) {
							ed.pasteAsPlainText = true;
						});
					},
					theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,|,cut,copy,paste,pastetext,pasteword,|,bullist,numlist,outdent,indent,|,undo,redo,|,link,unlink,image",
					theme_advanced_buttons2 : "",
					theme_advanced_toolbar_location : "top",
					theme_advanced_toolbar_align : "left",
					theme_advanced_statusbar_location : "bottom",
					theme_advanced_resizing : true,

					// Example content CSS (should be your site CSS)
					content_css : "content.css",

				});

				// get plain text content to save in DB
				$('#etedit').submit(function() {
					var editor = tinyMCE.get('ethtml');
					var root = editor.dom.select('*')[0];
					editor.selection.select(root);
					var content = editor.selection.getContent({
						format : 'text'
					});
					editor.selection.collapse();
					$('#ettext').val(content);
				});
			});

		</script>
		<style>
			body {
				margin-left: 4em;
			}
			.fl-l, .fl-r {
				float: left;
			}
			.fl-r {
				padding-left: 0.4em;
			}
			.clr {
				clear: both;
				display: block;
			}
			#etname, #etsubj {
				width: 15em;
			}
			input[type='text'] {
				display: block;
			}
		</style>
	</head>
	<body>
		<h1>Edit Email Template</h1>
		<form id='etedit' method="post" class='fl-l'>
			<div class='fl-l'>
				<label for='etname'>Email template name:</label>
				<input type='text' name='etname' id='etname' value='<?=$et -> etname; ?>'>
			</div>
			<div class='fl-r'>
				<label for='etsubj'>Subject line:</label>
				<input type='text' name='etsubj' id='etsubj' value="<?=$et -> etsubj; ?>">
			</div><div class='clr'></div>
			<br/>
			<br/>
			<textarea id="ethtml" name="ethtml" rows="25" cols="80" style="width: 600px" class="tinymce"><?=htmlentities($et -> ethtml); ?></textarea>																											
</div>
 <input type='hidden' id='ettext' name='ettext' value=''>
			<br/>
			<br/>
			<input type="submit" name="save" value="Save Email Template" />
			<input type="submit" name="debug" value="Debug Email Template" />
			<input type="submit" name="preview" value="Preview Email Template" />
			<input type="reset" name="reset" value="Revert to last Save" />
		</form>
		<div class='fl-r'></div><div class='clr'></div>
	</body>
</html>
