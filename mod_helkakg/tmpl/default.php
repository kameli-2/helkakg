<?php
// No direct access
defined('_JEXEC') or die;

$document = JFactory::getDocument();
$document->addStyleSheet('modules/mod_helkakg/css/default.css');
$document->addStyleDeclaration('div.mod_helkakg_image img { max-height: '.$imgsize.'; max-width: '.$imgsize.'; margin: '.$imgmargin.'; box-shadow: 0px 0px '.$shadow.'px #000; }');

// Select images to be shown
$usable_images = array();
if ($order == 'random') shuffle($images);
foreach ($images as $image) {
	$imageobject = new HelkaKGObject($image);
	if ($imageobject->parent == $dir) {
		array_push($usable_images, $image);
	}
	elseif ($subfolders == 1) {
		// check if image is in one of the subfolders
		$parentobject = $imageobject;
		while ($parentobject->parent != 1) {
			$parentobject = $parentobject->getParent();
			if ($parentobject->parent == $dir) {
				array_push($usable_images, $image);
			}
		}
	}
	else {
		continue;
	}

	if (count($usable_images) >= $limit) break;
}

$column = 0;
echo '<div class="mod_helkakg"><h3>'.$module->title.'</h3><div class="mod_helkakg_row">';
foreach ($usable_images as $usable_image) {
	$column++;
	if ($column > $columns) {
		echo '</div><div class="mod_helkakg_row">';
		$column = 1;
	}
	$imageobject = new HelkaKGObject($usable_image);

	// TODO: url to gallery, preferably to the correct folder

	echo '<div class="mod_helkakg_image">';
	if ($galleryurl != '') {
		echo '<a href="'.$galleryurl;
		if (strstr($galleryurl, "?")) echo '&'; else echo '?';
		echo 'dir='.$imageobject->parent.'">';
	}
	echo '<img src="'.HELKAKG_PATH2.$imageobject->getPath(true).'" alt="'.$imageobject->title.'">';

	if ($galleryurl != '') echo '</a>';

	echo '</div>';
}
echo '</div></div>';

?>
