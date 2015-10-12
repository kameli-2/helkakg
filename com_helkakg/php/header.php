<?php
// This file includes joomla libararies

define('DS', DIRECTORY_SEPARATOR);
define('_JEXEC', 1);
if (!defined('_JDEFINES')) {
	define('JPATH_BASE', substr(__DIR__, 0, strpos(__DIR__, "/components/com_helkakg/")));
        require_once JPATH_BASE.'/includes/defines.php';
}

require_once JPATH_BASE . '/includes/framework.php';

// Mark afterLoad in the profiler.
JDEBUG ? $_PROFILER->mark('afterLoad') : null;

// Instantiate the application.
$app = JFactory::getApplication('site');

global $rootdir, $subfolders, $listtype;
if (isset($_GET['rootdir'])) $rootdir = $_GET['rootdir'];
else $rootdir = 1;
if (isset($_GET['subfolders'])) $subfolders = $_GET['subfolders'];
else $subfolders = 1;
if (isset($_GET['listtype'])) $listtype = $_GET['listtype'];
else $listtype = 1;

//echo '<script>console.log("$rootdir @ header.php: '.$rootdir.'");</script>';
//echo '<script>console.log("isset($_GET[\'rootdir\']) @ header.php: '.isset($_GET['rootdir']).'");</script>';

?>
