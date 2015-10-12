<?php
// This file is used via AJAX from index.php and its purpose is to use
// classes HelkaKGGUI and HelkaKGSystem and tell index.php what to write
// in the document.

//header.php includes joomla libraries
require(dirname(__FILE__)."/header.php");

require(dirname(__FILE__)."/classes.php");

if (!defined("HELKAKG_PATH")) {
	$path = dirname($_SERVER['PHP_SELF']);
	$position = strrpos($path,'/') + 1;
	$path = substr($path, 0, $position);
	define("HELKAKG_PATH", $path);
}

if (isset($_GET['dir'])) {
        setcookie('helkakg_folder', $_GET['dir']);
        $_COOKIE['helkakg_folder'] = $_GET['dir'];
        unset($_GET['dir']);
}

$helkaKGGUI = new HelkaKGGUI();
$helkaKGSystem = new HelkaKGSystem();

//GET-variables (for displaying stuff)
if (isset($_GET['action'])) $action = $_GET['action'];
elseif (isset($_POST['action'])) $action = $_POST['action'];
else $helkaKGGUI->displayError("Virhe: toiminto ei määritelty.");

if (isset($_GET['objectid'])) $objectid = $_GET['objectid'];
elseif (isset($_POST['objectid'])) $objectid = $_POST['objectid'];
else $objectid = NULL;

$helkaKGObject = new HelkaKGObject($objectid);

if (isset($_GET['type'])) $type = $_GET['type'];
else $type='last';

if (isset($_GET['directoryPath'])) $directoryPath = $_GET['directoryPath'];
if (isset($_GET['folderstructure'])) $folderstructure = $_GET['folderstructure'];
if (isset($_GET['includeSubdirs'])) $includeSubdirs = $_GET['includeSubdirs'];

if (isset($_GET['parentid'])) $parentid = $_GET['parentid'];
elseif (isset($_POST['helkakg_parent'])) $parentid = $_POST['helkakg_parent'];
elseif ($objectid == 1) $parentid = 0;
else $parentid = 1;

//POST-variables (for creating/editing stuff)
if (isset($_POST['helkakg_title'])) $title = $_POST['helkakg_title'];
else $title = "";
if (isset($_POST['helkakg_description'])) $description = $_POST['helkakg_description'];
else $description = "";
if (isset($_POST['helkakg_description_owner'])) {
	if ($description != "") $description .= " - ";
	$description .= "Kuvaaja: ".$_POST['helkakg_description_owner'];
}
if (isset($_POST['helkakg_description_location'])) {
	if ($description != "") $description .= " - ";
	$description .= "Paikka: ".$_POST['helkakg_description_location'];
}
if (isset($_POST['helkakg_description_time'])) {
	if ($description != "") $description .= " - ";
	$description .= "Aika: ".$_POST['helkakg_description_time'];
}


switch ($action) {
	// Displaying stuff
	case "displayObject":
		$helkaKGGUI->displayObject($objectid);
		break;
	case "uploadImage":
		$helkaKGGUI->displayUploadImage($parentid);
		break;
	case "newFolder":
		$helkaKGGUI->displayNewFolder($parentid);
		break;
	case "edit":
		$helkaKGGUI->displayEditPage($objectid);
		break;
	case "delete":
		if ($helkaKGObject->type == "dir") $message = "Oletko varma, että haluat poistaa kansion ".$helkaKGObject->title."? Kaikki sen sisältämät kansiot ja kuvat poistuvat myös.";
		else $message = "Oletko varma, että haluat poistaa kuvan ".$helkaKGObject->title."?";
		$message .= "<br><div class='helkakg_smalliconcontainer' id='helkakg_deleteHandle_".$objectid."'><img src='".HELKAKG_PATH."images/delete.png' class='helkakg_smallicon'><br>Poista</div>";
		$message .= "<div class='helkakg_smalliconcontainer helkakg_cancel'><img src='".HELKAKG_PATH."images/return.png' class='helkakg_smallicon'><br>Peru</div>";
		$helkaKGGUI->displayError($message);
		break;
	case "displayHelp":
		$helkaKGGUI->displayHelp();
		break;
	case "importImages":
		$helkaKGGUI->displayImport($parentid);
		break;

	// Editing or creating stuff
	case "uploadImageHandle":
		for ($i=0;$i<11;$i++) {
			if (isset($_FILES['helkakg_image'.$i]['name'])) $helkaKGSystem->createObject("", $description, $parentid, $_FILES['helkakg_image'.$i]);
		}
		$helkaKGSystem->sendNotificationEmail($parentid);
		setcookie('helkakg_folder', $parentid);
		break;
	case "newFolderHandle":
		if ($helkaKGSystem->createObject($title, $description, $parentid)) $helkaKGGUI->displayObject($parentid);
		else $helkaKGGUI->displayError($helkaKGSystem->getErrormessage());
		break;
	case "editHandle":
		if ($helkaKGSystem->updateObject($objectid, $title, $description, $parentid)) $helkaKGGUI->displayObject();
		else $helkaKGGUI->displayError($helkaKGSystem->getErrormessage());
		break;
	case "editOrdering":
		if ($type == 'first') $helkaKGSystem->orderingFirst($objectid);
		elseif ($type == 'last') $helkaKGSystem->orderingLast($objectid);
		else $helkaKGSystem->swapOrdering($objectid, $type);
		$helkaKGGUI->displayObject();
		break;
	case "deleteHandle":
		$parentid = $helkaKGObject->parent;
		$helkaKGSystem->deleteObject($objectid);
		$helkaKGGUI->displayObject($parentid);
		break;
	case "publish":
		$helkaKGSystem->publishObject($objectid);
		$helkaKGGUI->displayObject();
		break;
	case "import":
		$helkaKGSystem->importImages($directoryPath, $parentid, $folderstructure, $includeSubdirs);
		$helkaKGGUI->displayObject($parentid);
}

?>
