<?php
// No direct access
defined('_JEXEC') or die;

$document = JFactory::getDocument();
$document->addStyleSheet(JUri::base().'modules/mod_helkakg/css/slideshow.css');
$document->addStyleDeclaration('.mod_helkakg_image img { max-height: '.$imgsize.'; max-width: '.$imgsize.'; margin: '.$imgmargin.'; box-shadow: 0px 0px '.$shadow.'px #000; }');
$document->addScript(JUri::base().'modules/mod_helkakg/js/slideshow.js');

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
echo '<div class="mod_helkakg">';
echo '<div class="mod_helkakg_arrow mod_helkakg_left"><img src="'.JUri::base().'modules/mod_helkakg/img/leftarrow.png" alt="'.JText::_('JLEFT').'" /></div>';
echo '<div class="mod_helkakg_images">';
foreach ($usable_images as $usable_image) {
	$imageobject = new HelkaKGObject($usable_image);

	echo '<span class="mod_helkakg_image">';
	if ($galleryurl != '') {
		echo '<a href="'.$galleryurl;
		if (strstr($galleryurl, "?")) echo '&'; else echo '?';
		echo 'dir='.$imageobject->parent.'">';
	}
	echo '<img src="'.HELKAKG_PATH2.$imageobject->getPath(true).'" alt="'.$imageobject->title.'">';

	if ($galleryurl != '') echo '</a>';

	echo '</span>';
}
echo '</div>';
echo '<div class="mod_helkakg_arrow mod_helkakg_right"><img src="'.JUri::base().'modules/mod_helkakg/img/rightarrow.png" alt="'.JText::_('JRIGHT').'" /></div>';
echo '</div>';

?>
