<?php

// no direct access
defined('_JEXEC') or die;

if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

require_once( dirname(__FILE__) . '/helper.php' );
require_once( dirname(__FILE__) . '/../../components/com_helkakg/php/classes.php' );

if (!defined("HELKAKG_PATH2")) {
        $path = dirname($_SERVER['PHP_SELF']).DS.'components'.DS.'com_helkakg';
//        $position = strrpos($path,'/') + 1;
//        $path = substr($path, 0, $position);
        define("HELKAKG_PATH2", $path.DS);
}


$dir = $params->get('dir', 1);
$subfolders = $params->get('subfolders', 1);
$order = $params->get('order', 'time ASC');
$limit = $params->get('limit', 9);
$columns = $params->get('columns', 3);
$imgsize = $params->get('imgsize', '75px');
$imgmargin = $params->get('imgmargin', '0.2em');
$shadow = $params->get('shadow', '5');
$itemid = $params->get('galleryurl'); // ItemID of the gallery

// Obtain link to gallery from ItemID
$sql = "SELECT link FROM #__menu WHERE id = ".$itemid;
$db = JFactory::getDbo();
$query = $db->getQuery(true);
$query->select($db->quoteName('link'));
$query->from($db->quoteName('#__menu'));
$query->where($db->quoteName('id') . ' = ' . $db->quote($itemid));
$db->setQuery($query);
$menuItem = $db->loadObject();
$galleryurl = JRoute::_($menuItem->link);

$images = modHelkakgHelper::getHelkakg($order); // Get images from db

require( JModuleHelper::getLayoutPath('mod_helkakg', $params->get('layout', 'default')));

?>
