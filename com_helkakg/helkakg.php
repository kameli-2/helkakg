<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');


$jinput = JFactory::getApplication()->input;
global $rootdir, $subfolders, $listtype;
$rootdir = $jinput->get('rootdir', 1, 'INTEGER');
$subfolders = $jinput->get('subfolders', 1, 'INTEGER');
$listtype = $jinput->get('listtype', 1, 'INTEGER');

setcookie("helkakg_rootdir", $rootdir);
$_COOKIE['helkakg_rootdir'] = $rootdir;
setcookie("helkakg_subfolders", $subfolders);
$_COOKIE['helkakg_subfolders'] = $subfolders;
setcookie("helkakg_listtype", $listtype);
$_COOKIE['helkakg_listtype'] = $listtype;



$document = JFactory::getDocument();
$document->addStyleSheet(JUri::base().'components'.DIRECTORY_SEPARATOR.'com_helkakg'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'styles.css');
JHtml::_('jquery.framework');
//$document->addScript('//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js');


        if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
        if (isset($_SERVER['SCRIPT_URI'])) $uri = $_SERVER['SCRIPT_URI'];
        else $uri = '';
        $path = dirname(__FILE__).DS;
        if (strpos($path, "/site/") === 0) $path = substr($path, 5);
        if(!strstr($uri, 'kaupunginosat.net')){
                $path=strstr($path, '/components/com_helkakg');
        }
?>
<script>
function getPath() {
        <?php echo "return '".$path."';";
        ?>
}
<?php
if (isset($_GET['dir'])) echo 'var cookiedir = "'.$_GET['dir'].'";';
elseif (isset($_COOKIE['helkakg_folder'])) echo 'var cookiedir = "'.$_COOKIE['helkakg_folder'].'";';
else echo 'var cookiedir = '.$rootdir.';';

if (isset($_COOKIE['helkakg_rootdir'])) echo 'var cookierootdir = "'.$_COOKIE['helkakg_rootdir'].'";';
else echo 'var cookierootdir = '.$rootdir.';';

if (isset($_COOKIE['helkakg_subfolders'])) echo 'var cookiesubfolders = "'.$_COOKIE['helkakg_subfolders'].'";';
else echo 'var cookiesubfolders = 1;';

if (isset($_COOKIE['helkakg_listtype'])) echo 'var cookielisttype = "'.$_COOKIE['helkakg_listtype'].'";';
else echo 'var cookielisttype = 1;';

?>
</script>

<script src="components/com_helkakg/js/scripts.js"></script>
<div id="helkakg_loading"><img src="components/com_helkakg/images/loading.gif" class="helkakg_loadimg"></div>
<div id="helkakg"></div>
<div id="helkakg_license"><a href="http://creativecommons.org/licenses/by/4.0/" target="_blank">
Kuvia saa k&auml;ytt&auml;&auml; vapaasti, kunhan mainitsee alkuper&auml;isen kuvan tekij&auml;n.
</a></div>
