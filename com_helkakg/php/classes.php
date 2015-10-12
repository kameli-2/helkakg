<?php

//no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if (!defined("HELKAKG_PATH")) {
//	$foldername = $_SERVER['DOCUMENT_ROOT'].DS."images";
	$path = dirname($_SERVER['PHP_SELF']);
	$position = strrpos($path,'/') + 1;
	$path = substr($path, 0, $position);
	define("HELKAKG_PATH", $path);
}
class HelkaKGGUI {
	// This is the GUI: it displays the folder browser, image viewer,
	// edit- and create -pages, warnings, errors, etc.

	public $folder;
	public $system;

	public function __construct() {
		global $rootdir;
//		echo '<script>console.log("$rootdir @ classes.php __construct: '.$rootdir.'");</script>';
		if (!empty($_COOKIE['helkakg_folder'])) $this->folder = $_COOKIE['helkakg_folder'];
		else $this->folder = $rootdir;
		$this->system = new HelkaKGSystem();
	}

	public function displayObject($objectid=0, $headersize="2") {
		global $rootdir, $subfolders, $listtype;
		if ($objectid < 1) {
			if (isset($_COOKIE['helkakg_folder'])) $objectid = $_COOKIE['helkakg_folder'];
			else $objectid = $rootdir;
		}

		$object = new HelkaKGObject($objectid);

		if ($object->type == "submission" && !$this->system->userIsAdmin()) return false;

		if ($object->type == "img" or $object->type == "submission") $this->displayReturnIcon();
		elseif ($listtype != 2) {
			$this->folder = $objectid;
			setcookie("helkakg_folder", $objectid);
			$_COOKIE['helkakg_folder'] = $objectid;
		}

		echo '<div class="helkakg_header"><h'.$headersize.'>'.$this->displayTitle($objectid, (($object->type == "img" or $object->type == "submission" or $listtype == 2) ? false : true)).'</h'.$headersize.'>';

		$this->displayAdminLinks($objectid);

		echo '</div>';

		// if viewing image, echo img-tag
		if ($object->type == "img" or $object->type == "submission") {
			echo '<img class="helkakg_bigimage" src="'.HELKAKG_PATH.$object->getPath().'">';
			$this->displayArrows($objectid);
		}

		if ($object->description) echo '<div class="helkakg_description">'.$this->formatDescription($object->description).'</div>';


		//if viewing folder, display contents
		if ($object->type == "dir") {
			$children = $object->getChildren();
			if ($listtype == 1) {
				if ($objectid != $rootdir) $this->displayThumbnail($object->parent);
				foreach ($children as $childid) {
					if (!($subfolders==2 && $this->system->objectIsDir($childid))) $this->displayThumbnail($childid);
				}
			}
			elseif ($listtype == 2) {
				// List images
				foreach ($children as $childid) {
					if (!$this->system->objectIsDir($childid)) $this->displayThumbnail($childid);
				}

				// List directories (if allowed by settings)
				if ($subfolders != 2) {
					foreach ($children as $childid) {
						if ($this->system->objectIsDir($childid)) $this->displayObject($childid, $headersize+1);
					}
				}
			}
		}
	}

	public function displayThumbnail($objectid) {
		global $rootdir;
		$object = new HelkaKGObject($objectid);

		if ($object->type == "submission" && !$this->system->userIsAdmin()) return false;

		// Create new thumbnail for folder if current thumbnail is outdated or doesn't exist
		if ($object->type == 'dir') {
			$outdatetime = 60*60*24;
			if (!file_exists($object->getPath(true, true)) || filemtime($object->getPath(true, true)) < time()-$outdatetime) {
				$this->system->writeThumbnail($objectid);
			}
		}

		echo '<div class="helkakg_thumbnail'.($object->type=="submission" ? ' submission' : '').($object->type=="dir" ? ' hkg_folder' : ' hkg_image').'">';
		if ($object->type == "dir") {
			if (in_array($this->folder, $object->getChildren())) echo '<img src="'.HELKAKG_PATH.'images'.DS.'updirectory.png" class="helkakg_thumbnailpic" id="helkakg_dir_'.$objectid.'">';
			else echo '<img src="'.HELKAKG_PATH.$object->getPath(true).'" class="helkakg_thumbnailpic" id="helkakg_dir_'.$objectid.'">';
		}
		else echo '<img src="'.HELKAKG_PATH.$object->getPath(true).'" class="helkakg_thumbnailpic" id="helkakg_img_'.$objectid.'">';
		echo '<h3>'.$this->shortTitle($object->title).'</h3>';
		if ($this->system->userIsAdmin()) {
			if ($objectid != $rootdir) echo '<img src="'.HELKAKG_PATH.'images'.DS.'delete.png" class="helkakg_smallicon" id="helkakg_delete_'.$objectid.'">';
			echo '<img src="'.HELKAKG_PATH.'images'.DS.'edit.png" class="helkakg_smallicon" id="helkakg_edit_'.$objectid.'">';
			if ($object->type == "submission") echo '<img src="'.HELKAKG_PATH.'images'.DS.'publish.png" class="helkakg_smallicon" id="helkakg_publish_'.$objectid.'">';
			if ($this->folder == $object->parent) { // Display ordering arrows
				if ($object->previousObject()) echo '
					<img src="'.HELKAKG_PATH.'images'.DS.'first.png" class="helkakg_smallicon" id="helkakg_editOrdering_'.$objectid.'_first">
					<img src="'.HELKAKG_PATH.'images'.DS.'previous.png" class="helkakg_smallicon" id="helkakg_editOrdering_'.$objectid.'_'.$object->previousObject().'">
				';
				if ($object->nextObject()) echo '
					<img src="'.HELKAKG_PATH.'images'.DS.'next.png" class="helkakg_smallicon" id="helkakg_editOrdering_'.$objectid.'_'.$object->nextObject().'">
					<img src="'.HELKAKG_PATH.'images'.DS.'last.png" class="helkakg_smallicon" id="helkakg_editOrdering_'.$objectid.'_last">
				';
			}
		}
		echo '</div>';
	}

	public function displayAdminLinks($objectid) {
		global $rootdir;
//		if (!$this->system->userIsAdmin()) return false;
		$object = new HelkaKGObject($objectid);

		echo '<div class="helkakg_adminlinks">';

		// For directories (upload&newdir)
		if ($object->type == "dir") {
			echo '<div class="helkakg_smalliconcontainer" id="helkakg_uploadImage_'.$objectid.'"><img src="'.HELKAKG_PATH.'images'.DS.'upload.png" class="helkakg_smallicon"><br>Lataa kuvia</div>';
			if ($this->system->userIsAdmin()) echo '
				<div class="helkakg_smalliconcontainer" id="helkakg_importImages_'.$objectid.'"><img src="'.HELKAKG_PATH.'images'.DS.'import.png" class="helkakg_smallicon"><br>Tuo kuvia</div>
				<div class="helkakg_smalliconcontainer" id="helkakg_newFolder_'.$objectid.'"><img src="'.HELKAKG_PATH.'images'.DS.'createdir.png" class="helkakg_smallicon"><br>Uusi kansio</div>
			';
		}
		if ($this->system->userIsAdmin()) {

			echo '<div class="helkakg_smalliconcontainer" id="helkakg_edit_'.$objectid.'"><img src="'.HELKAKG_PATH.'images'.DS.'edit.png" class="helkakg_smallicon"><br>Muokkaa</div>';

			if ($objectid != $rootdir) echo '<div class="helkakg_smalliconcontainer" id="helkakg_delete_'.$objectid.'"><img src="'.HELKAKG_PATH.'images'.DS.'delete.png" class="helkakg_smallicon"><br>Poista</div>';

			if ($object->type == "submission") echo '<div class="helkakg_smalliconcontainer" id="helkakg_publish_'.$objectid.'"><img src="'.HELKAKG_PATH.'images'.DS.'publish.png" class="helkakg_smallicon"><br>Julkaise</div>';

			if ($object->type == "dir") echo '<div class="helkakg_smalliconcontainer" id="helkakg_displayHelp"><img src="'.HELKAKG_PATH.'images'.DS.'help.png" class="helkakg_smallicon"><br>Ohjeita</div>';
		}

		echo '</div>';
	}

	public function displayReturnIcon() {
		echo '<div class="helkakg_return"><img src="'.HELKAKG_PATH.'images'.DS.'return.png" class="helkakg_biggericon"><br>Takaisin</div>';
	}

	public function displayArrows($objectid) {
		$object = new HelkaKGObject($objectid);
		$previous = $object->previousObject();
		$next = $object->nextObject();
		if (!$previous && !$next) return;
		echo '<div>';
		if ($previous) echo '<img src="http://www.kaupunginosat.net/sandbox/portfolio/images/previousSlide.png" id="helkakg_previous_'.$previous.'" class="helkakg_previous helkakg_prevnext">';
		if ($next) echo '<img src="http://www.kaupunginosat.net/sandbox/portfolio/images/previousSlide.png" id="helkakg_next_'.$next.'" class="helkakg_next helkakg_prevnext">';
		echo '</div>';
	}

	public function displayEditForm($mode="edit", $parentid=1, $title="", $description="", $objectid="") {
		global $rootdir;
		if (!$this->system->userIsAdmin()) return false;

		$this->displayReturnIcon();

		$objecttype = "img";
		if ($mode == "root") $objecttype = "dir";
		elseif ($mode == "newdir") $objecttype = "dir";
		else {
			if ($this->system->objectIsDir($objectid)) $objecttype = "dir";
		}

		echo '
		<div id="helkakg_errordiv" style="display:none;"></div>
		<input type="hidden" name="helkakg_object_type" value="'.$objecttype.'">
		<table>
			<tr>
				<td>Otsikko:</td>
				<td><input type="text" id="helkakg_title" name="helkakg_title" value="'.$title.'" maxlength="100"></td>
			</tr>
			<tr>
				<td>Kuvaus:</td>
				<td><textarea id="helkakg_description" name="helkakg_description" maxlength="255">'.$description.'</textarea></td>
			</tr>
		';

		//display dropdown-selector for parent
		if ($mode != "root") { // root folder's parent can't be changed
			echo '
			<tr>
				<td>Kansio:</td>
				<td>
					<select id="helkakg_parent" name="helkakg_parent">
			';
			$this->displayOptionsForAllChildren(1, $parentid); //echo all folders as options
			echo '
					</select>
				</td>
			</tr>
			';
		}
		else echo '<input type="hidden" id="helkakg_parent" name="helkakg_parent" value="0">';
		echo '
			<tr>
				<td></td>
				<td><input type="button" class="helkakg_submit" value="Tallenna" id="';
		switch ($mode) {
			case 'root':
			case 'edit': echo 'helkakg_editHandle_'.$objectid; break;
			case 'newdir': echo 'helkakg_newFolderHandle_'.$parentid; break;
		}
		echo '"></td>
			</tr>
		</table>
		';
	}

	public function displayUploadImage($parentid=1) {
		global $rootdir, $subfolders;
//		if (!$this->system->userIsAdmin()) return false;

		echo '<div class="helkakg_header"><h2>Lisää kuvia</h2>';

		$this->displayReturnIcon();

		echo '</div>';

		if (!$this->system->userIsAdmin()) echo '<div class="helkakg_description">Lataamasi kuvat julkaistaan, kun sivuston toimitus on tarkastanut ne.</div>';

		echo '<div class="helkakg_description">Lataamalla kuvat kuvagalleriaan hyv&auml;ksyt, ett&auml; kuviasi voidaan k&auml;ytt&auml;&auml; vapaasti sill&auml; ehdolla, ett&auml; kuvan alkuper&auml;inen tekij&auml; mainitaan kuvan yhteydess&auml;. Lue lis&auml;&auml; <a href="http://creativecommons.org/licenses/by/4.0/" target="_blank">t&auml;&auml;lt&auml;</a>.</div>';
		echo '<div class="helkakg_description">Sallitut tiedostotyypit: png, gif, jpg/jpeg.</div>';
		echo '<div class="helkakg_description">Tiedostojen yhteenlaskettu koko ei saa ylitt&auml;&auml; 8192 kilotavua. Sy&ouml;tt&auml;m&auml;si tiedostojen yhteiskoko: <span id="helkakg_filesize_sum">0</span> / 8192 kt</div>';

		echo '
			<iframe name="hidden_upload" id="hidden_upload" src="" style="width:0;height:0;border:0px solid #fff;"></iframe>
			<form id="helkakg_upload_form" action="'.HELKAKG_PATH.'php'.DS.'action.php" method="post" enctype="multipart/form-data" target="hidden_upload">
			<input name="action" type="hidden" value="uploadImageHandle">
			<input type="hidden" name="MAX_FILE_SIZE" value="8388608">
			<div id="helkakg_uploader">
			<table>
		';
		for ($i=1; $i<11; $i++)	echo '
			<tr>
				<td>Kuva '.$i.'</td>
				<td><input type="file" name="helkakg_image'.$i.'" class="helkakg_fileinput" accept="image/*"></td>
				<td><span class="helkakg_filesize"></span> <span class="helkakg_clearfile">Poista</span></td>
			</tr>
		';

		echo '
			<tr>
				<td>Kuvaus:</td>
				<td colspan="2"><textarea id="helkakg_description" name="helkakg_description" maxlength="255"></textarea>
				<!-- small>Kirjoita t&auml;h&auml;n esimerkiksi kuvaajan nimi, kuvauspaikka ja kuvausaika.</small -->
				</td>
			</tr>
			<tr>
				<td>Kuvaaja:</td>
				<td colspan="2"><input type="text" name="helkakg_description_owner"></td>
			</tr>
			<tr>
				<td>Paikka:</td>
				<td colspan="2"><input type="text" name="helkakg_description_location"></td>
			</tr>
			<tr>
				<td>Aika:</td>
				<td colspan="2"><input type="text" name="helkakg_description_time"></td>
			</tr>
		';

		if ($subfolders == 1) {
			echo '
			<tr>
                               	<td>Kansio:</td>
	                        <td colspan="2">
        	                        <select id="helkakg_parent" name="helkakg_parent">
                	';
	                $this->displayOptionsForAllChildren($rootdir, $parentid); //echo all folders as options
        	        echo '
                                        </select>
                                </td>
                        </tr>';
		}
		else echo '<input type="hidden" id="helkakg_parent" name="helkakg_parent" value="'.$rootdir.'" />';
		echo '
			<tr>
				<td><div id="helkakg_loadimgdiv"></div></td>
				<td colspan="2"><input type="submit" id="helkakg_submit" name="helkakg_submit" class="helkakg_submit_upload" value="Lataa kuvat"></td>
			</tr>
		</table>
		</div>
		</form>
                ';

	}

	public function displayNewFolder($parentid=1) {
		if (!$this->system->userIsAdmin()) return false;

		echo '<div class="helkakg_header"><h2>Luo uusi kansio</h2></div>';
		$this->displayEditForm('newdir', $parentid);
	}

	public function displayHelp() {
		if (!$this->system->userIsAdmin()) return false;

		echo '<div class="helkakg_header"><h2>Ohjeet</h2></div>';

		$this->displayReturnIcon();

		echo '
			<h3>Kuvat</h3>
			<div>
				<h4>Kuvien lisääminen galleriaan</h4>
				<ol>
					<li>Klikkaa <img src="'.HELKAKG_PATH.'images'.DS.'upload.png" class="helkakg_help">-kuvaketta.</li>
					<li>Valitse enintään 10 kuvaa tietokoneeltasi.</li>
					<li>Valitse kansio, johon haluat ladata kuvat.</li>
					<li>Klikkaa "Lataa kuvat"-nappia. Kuvien latauksessa voi kestää useita minuutteja, joten ole kärsivällinen!</li>
				</ol>
			</div>
			<div>
				<h4>Palvelimella olevien kuvien tuominen galleriaan</h4>
				<p>Voit tuoda palvelimella sijaitsevia kuvia automaattisesti kuvagalleriaan. T&auml;m&auml; on hy&ouml;dyllist&auml; esimerkiksi silloin, kun haluat siirt&auml;&auml; kuvia vanhasta kuvagalleriasta uuteen.</p>
				<ol>
					<li>Klikkaa <img src="'.HELKAKG_PATH.'images'.DS.'import.png" class="helkakg_help">-kuvaketta.</li>
					<li>Valitse palvelimelta kansio, jossa tuotavat kuvat sijaitsevat.
						<ul>
							<li>Jos haluat, ett&auml; kuvia tuodaan my&ouml;s valitsemasi kansion sis&auml;ll&auml; olevista kansioista, laita rasti kohtaan "Tuo my&ouml;s alikansioista".</li>
						</ul>
					</li>
					<li>Valitse kuvagallerian kansio, johon haluat kuvien tulevan.</li>
					<li>Tuontity&ouml;kalu voi halutessasi luoda kuvagalleriaan automaattisesti kansioita, joihin kuvat sijoitetaan.
						<ul>
							<li>Mik&auml;li haluat j&auml;rjest&auml;&auml; kuvat kansioihin automaattisesti kuvien ottamisp&auml;iv&auml;m&auml;&auml;r&auml;n mukaan, valitse "Kuvien ottamisp&auml;iv&auml;m&auml;&auml;r&auml;".</li>
							<li>Mik&auml;li haluat j&auml;rjest&auml;&auml; kuvat kansioihin samalla tavoin, kuin ne on palvelimella j&auml;rjestetty, valitse "Palvelimen kansiorakenne".</li>
						</ul>
					</li>
					<li>Klikkaa "Tuo kuvat"-nappia. Kuvien tuomisessa voi kest&auml;&auml; useita minuutteja, joten ole k&auml;rsiv&auml;llinen!</li>
				</ol>
			</div>
			<div>
				<h4>K&auml;vij&ouml;iden lataamien kuvien julkaiseminen</h4>
				<ol>
					<li>Kuka tahansa sivuston k&auml;vij&auml; voi ladata kuvansa kuvagalleriaan.</li>
					<li>Kuvat tulevat n&auml;kyviin vain, jos sivuston toimitus julkaisee ne.</li>
					<li>Julkaisemattomat kuvat n&auml;kyv&auml;t kuvagalleriassa vain toimitukselle. Julkaisemattomat kuvat ovat hieman l&auml;pin&auml;kyvi&auml; ja reunustettu katkoviivalla.</p>
					<li>Julkaisemattoman kuvan voi julkaista painamalla kuvan kohdalla <img src="'.HELKAKG_PATH.'images'.DS.'publish.png" class="helkakg_help">-kuvaketta.</li>
				</ol>
			</div>
			<div>
				<h4>Otsikon ja kuvauksen lisääminen kuvaan</h4>
				<ol>
					<li>Etsi kuvagalleriasta haluamasi kuva ja klikkaa sitä, jolloin pääset katsomaan kuvaa suurempana.</li>
					<li>Klikkaa <img src="'.HELKAKG_PATH.'images'.DS.'edit.png" class="helkakg_help">-kuvaketta.</li>
					<li>Valitse kuvalle nimi ja kuvaus. Voit halutessasi jättää kumman tahansa tyhjäksi.</li>
					<li>Klikkaa "Tallenna"-nappia.</li>
				</ol>
			</div>
			<div>
				<h4>Kuvan siirtäminen toiseen kansioon</h4>
				<ol>
					<li>Etsi kuvagalleriasta haluamasi kuva ja klikkaa sitä, jolloin pääset katsomaan kuvaa suurempana.</li>
					<li>Klikkaa <img src="'.HELKAKG_PATH.'images'.DS.'edit.png" class="helkakg_help">-kuvaketta.</li>
					<li>Valitse kansio, johon haluat siirtää kuvan.</li>
					<li>Klikkaa "Tallenna"-nappia.</li>
				</ol>
			</div>
			<div>
				<h4>Kuvien järjestyksen muuttaminen</h4>
				<ol>
					<li>Siirry tarkastelemaan kansiota, jonka kuvia haluat järjestellä.</li>
					<li>Jos kansiossa on useampi kuin yksi kuva ja viet kursorin pikkukuvan päälle, kuvan kohdalle ilmestyvät kuvakkeet <img src="'.HELKAKG_PATH.'images'.DS.'first.png" class="helkakg_help"><img src="'.HELKAKG_PATH.'images'.DS.'previous.png" class="helkakg_help"><img src="'.HELKAKG_PATH.'images'.DS.'next.png" class="helkakg_help"><img src="'.HELKAKG_PATH.'images'.DS.'last.png" class="helkakg_help">.</li>
					<li>Näitä kuvakkeita klikkailemalla voit siirtää kuvan kansion ensimmäiseksi, yhden askeleen taaksepäin, yhden askeleen eteenpäin tai kansion viimeiseksi.</li>
					<li>Mitään erillistä tallentamista ei tarvita; uusi järjestys tallentuu sitä mukaa kun siirtelet kuvia.</li>
				</ol>
			</div>
			<div>
				<h4>Kuvan poistaminen</h4>
				<ol>
					<li>Etsi kuvagalleriasta haluamasi kuva ja klikkaa sitä, jolloin pääset katsomaan kuvaa suurempana.</li>
					<li>Klikkaa <img src="'.HELKAKG_PATH.'images'.DS.'delete.png" class="helkakg_help">-kuvaketta.</li>
					<li>Galleria kysyy, haluatko varmasti poistaa kuvan. Jos olet varma poistosta, voit poistaa sen klikkaamalla samaa poistokuvaketta uudestaan. Muussa tapauksessa paina <img src="'.HELKAKG_PATH.'images'.DS.'return.png" class="helkakg_help">-kuvaketta.</li>
				</ol>
			</div>

			<h3>Kansiot</h3>
			<div>
				<h4>Kansion lisääminen galleriaan</h4>
				<ol>
					<li>Klikkaa <img src="'.HELKAKG_PATH.'images'.DS.'createdir.png" class="helkakg_help">-kuvaketta.</li>
					<li>Valitse kansiolle nimi ja kuvaus. Kuvaus ei ole pakollinen.</li>
					<li>Valitse kansio, johon haluat luoda uuden kansion.</li>
					<li>Klikkaa "Tallenna"-nappia.</li>
				</ol>
			</div>
			<div>
				<h4>Kansion nimen ja kuvauksen muokkaaminen</h4>
				<ol>
					<li>Siirry tarkastelemaan kansiota, jota haluat muokata.</li>
					<li>Klikkaa <img src="'.HELKAKG_PATH.'images'.DS.'edit.png" class="helkakg_help">-kuvaketta.</li>
					<li>Valitse kansiolle haluamasi nimi ja kuvaus. Kuvaus ei ole pakollinen.</li>
					<li>Klikkaa "Tallenna"-nappia.</li>
				</ol>
			</div>
			<div>
				<h4>Kansion siirtäminen toiseen kansioon</h4>
				<ol>
					<li>Siirry tarkastelemaan kansiota, jonka haluat siirtää.</li>
					<li>Klikkaa <img src="'.HELKAKG_PATH.'images'.DS.'edit.png" class="helkakg_help">-kuvaketta.</li>
					<li>Valitse, mihin kansioon haluat siirtää kansion.</li>
					<li>Klikkaa "Tallenna"-nappia.</li>
				</ol>
			</div>
			<div>
				<h4>Kansion poistaminen</h4>
				<ol>
					<li>HUOM! Jos poistat kansion, kaikki sen sisältämät kuvat ja kansiot poistuvat samalla.</li>
					<li>Siirry tarkastelemaan kansiota, jonka haluat poistaa.</li>
					<li>Klikkaa <img src="'.HELKAKG_PATH.'images'.DS.'delete.png" class="helkakg_help">-kuvaketta.</li>
					<li>Galleria kysyy, haluatko varmasti poistaa kansion JA SAMALLA KAIKKI SEN SISÄLTÄMÄT KANSIOT JA KUVAT. Jos olet varma poistosta, voit poistaa sen klikkaamalla samaa poistokuvaketta uudestaan. Muussa tapauksessa paina <img src="'.HELKAKG_PATH.'images'.DS.'return.png" class="helkakg_help">-kuvaketta.</li>
				</ol>
			</div>

		';
	}

	public function displayImport($parentid=1) {
		global $rootdir;
		if (!$this->system->userIsAdmin()) return false;

		$this->displayReturnIcon();

		echo '<div class="helkakg_header"><h2>Tuo kuvia palvelimelta galleriaan</h2></div>';

		echo '<p>Voit tuoda palvelimella sijaitsevat kuvat kuvagalleriaan tämän työkalun avulla.</p>';

		echo '<table><tr>
			<td>Kansio, josta kuvat tuodaan:</td>
			<td><select name="directoryPath" id="helkakg_directoryPath">';

		// Go 2 directories down
		$foldername = substr(HELKAKG_PATH, 0, strrpos(HELKAKG_PATH, DS));
		$foldername = substr($foldername, 0, strrpos($foldername, DS));
		$foldername = substr($foldername, 0, strrpos($foldername, DS));
		$foldername .= DS."images";
		$foldername = "/var/www".$foldername;
		 if(!strstr($_SERVER['SCRIPT_URI'], 'kaupunginosat.net')){
                	$foldername=$_SERVER['DOCUMENT_ROOT'].DS."images";
       		 }

		$this->displayOptionsForAllSubdirs($foldername);

		echo '</select><input type="checkbox" name="includeSubdirs" id="helkakg_includeSubdirs"><label for="helkakg_includeSubdirs">Tuo my&ouml;s alikansioista</label></td></tr>';

		echo '<tr><td>Kansio, johon kuvat tuodaan:</td><td> <select id="helkakg_parent" name="helkakg_parent">';

		$this->displayOptionsForAllChildren(1, $parentid); //echo all folders as options
		echo '</select></td></tr>';

		echo '<tr><td>
			Kansiorakenteen peruste:</td><td>
			<input type="radio" name="folderstructure" id="helkakg_folderstructure_date" value="date" checked="checked"><label for="helkakg_folderstructure_date">Kuvien ottamisp&auml;iv&auml;m&auml;&auml;r&auml;</label><br>
			<input type="radio" name="folderstructure" value="folders" id="helkakg_folderstructure_folders"><label for="helkakg_folderstructure_folders">Palvelimen kansiorakenne</label>
		</td></tr></table>';

		echo '<p><input type="submit" value="Tuo kuvat" id="helkakg_import" class="helkakg_submit_import"><div id="helkakg_loadimgdiv"></div></p>';
	}

	public function displayEditPage($objectid) {
		global $rootdir;
		if (!$this->system->userIsAdmin()) return false;

		echo '<div class="helkakg_header"><h2>Muokkaa</h2></div>';

		$object = new HelkaKGObject($objectid);

		if ($objectid == 1) $mode = 'root';
		else $mode = 'edit';

		$this->displayEditForm($mode, $object->parent, $object->title, $object->description, $objectid);
	}

	public function displayError($error) {
		if ($error != "Anna kansiolle otsikko.") $this->displayReturnIcon();
		echo '<div class="helkakg_error">'.$error.'</div>';
	}

	public function displayOptionsForAllSubdirs($foldername, $spaces=0) {
		// This function displays recursively all subdirs of $foldername as <option> tags.

		// fetch the name of the root dir to avoid importing it
		$root = new HelkaKGObject(1);
		$rootdir = $root->filename;
		if (basename($foldername) == $rootdir) return false;

		//echo itself
		echo '<option value="'.$foldername.'">';

		//display folder structure by echoing as many spaces as there are parents
		for ($i=0; $i < $spaces; $i++) echo '- ';

		echo basename($foldername).'</option>';

		//echo all children
		$folder = opendir($foldername);
		while ($subdir = readdir($folder)) {
			if (is_dir($foldername.DS.basename($subdir)) && basename($subdir) != ".." && basename($subdir) != ".") {
				$this->displayOptionsForAllSubdirs($foldername.DS.basename($subdir), $spaces+1);
			}
		}

		closedir($folder);
	}

	public function displayOptionsForAllChildren($folderid, $selectedFolder) {
		// This function displays recursively all children of $folderid as <option> tags.
		$folder = new HelkaKGObject($folderid);

		if ($folder->type == "img" or $folder->type == "submission") return false;

		$children = $folder->getChildren();

		//echo itself
		echo '<option value="'.$folderid.'"';
		if ($folderid == $selectedFolder) echo ' selected';
		echo '>';

		//display folder structure by echoing as many spaces as there are parents
		for ($i=0; $i < $this->system->howManyParents($folderid); $i++) echo '- ';

		echo $folder->title.'</option>';

		//echo all children
		foreach ($children as $childid) {
			$this->displayOptionsForAllChildren($childid, $selectedFolder);
		}
	}

	public function displayTitle($objectid, $breadcrumbs = false, $link = false) {
		global $rootdir;
		$object = new HelkaKGObject($objectid);
		$title = "";
		if ($breadcrumbs && $objectid != $rootdir) $title .= $this->displayTitle($object->getParent()->objectid, true, true).' <img src="'.HELKAKG_PATH.'images'.DS.'breadcrumbsarrow.png"> ';
		if ($breadcrumbs && $object->type == "dir") $title .= '<span id="helkakg_breadcrumbs_'.$objectid.'" class="helkakg_breadcrumbs'.($link ? ' helkakg_bclink' : '').'">';
		$title .= $object->title;
		if ($breadcrumbs && $object->type == "dir") $title .= '</span>';
		return $title;
	}

	public function shortTitle($title) {
		$maxlength = 43;
		if (strlen($title) < $maxlength) return $title;
		return substr($title, 0, $maxlength-2)."...";
	}
	public function formatDescription($description) {
		return $this->makeClickableLinks($description);
	}

	public function makeClickableLinks($s) {
		$ret = ''.$s;
		// Replace links with http(s)://
		$ret = preg_replace('#(https?://|www\.)([^\s]*)#is', '<a href="$0" target="_blank">$0</a>', $ret);
		// Put http:// where necessary
		$ret = preg_replace('#<a href="www.(.*)" target="_blank">(.*)</a>#is', '<a href="http://www.$1" target="_blank">$2</a>', $ret);
		// Remove possible dot from the end of url
		$ret = preg_replace('#<a href="(.*)\." target="_blank">(.*)\.</a>#is', '<a href="$1" target="_blank">$2</a>.', $ret);
		// Replace emails
		$ret = preg_replace('/([^\s]*@[^ ]*\.[^\s]{2,})/', '<a href="mailto:$1" target="_blank">$1</a>', $ret);

		return $ret;
	}
}

class HelkaKGSystem {
	// Contains functions for updating, adding and deleting images and folders

	public $errormessage;

	public function createObject($title, $description, $parent, $uploadedFile="") {
//		if (!$this->userIsAdmin()) return false;

		$objectid = $this->getNextObjectId();
		$ordering = $this->getNextOrdering();

		if ($uploadedFile!="") $type="img";
		else $type="dir";

		if ($type == "img") {
			$fileExtension = strtolower(array_pop(explode(".", $uploadedFile['name'])));
			$allowedExtensions = array("jpg", "jpeg", "png", "gif");
			if (!in_array($fileExtension, $allowedExtensions)) return false;
			if ($uploadedFile['size'] > 10485760) return false;
		}
		else $fileExtension = "";

		if (!$this->objectInfoIsOk($objectid, $title, $parent, $fileExtension)) return false;

		$fileName = $this->createFileName($objectid, $title, $parent, $fileExtension);

		// Create database entry
		if (!$this->userIsAdmin()) $type="submission";
		$db =& JFactory::getDBO();
		$updatableObject = new UpdatableObject($objectid, $parent, $fileName, $title, $description, $type, $ordering);
		$db->insertObject('#__helkakg', $updatableObject);

		$object = new HelkaKGObject($objectid);

		if ($object->type == "img" or $object->type == "submission") {
			if (!move_uploaded_file($uploadedFile['tmp_name'], $object->getPath(false, true))) {
				copy($uploadedFile['tmp_name'], $object->getPath(false, true));
			}
			$this->writeThumbnail($objectid);
		}
		else {
			mkdir($object->getPath(false, true));
		}

		return true;
	}

	public function importImages($directoryPath, $parentid=1, $folderstructure="folders", $includeSubdirs=false) {
		if (!$this->userIsAdmin()) return false;
		if (!file_exists($directoryPath)) return false;
		else {
			$dir = opendir($directoryPath);

			// fetch the name of the root dir to avoid importing it
			$root = new HelkaKGObject(1);
			$rootdir = $root->filename;

			while (($object = readdir($dir)) !== false) {
				if (is_dir($directoryPath.DS.basename($object))) { // oh, it's just a directory... I can handle this.
					if ($includeSubdirs && basename($object) != $rootdir && basename($object) != ".." && basename($object) != "." && basename($object) != "") {
						if ($folderstructure == "folders") { // Create new dir in gallery and import dir images there
							$newparent = $this->getNextObjectId();
							$this->createObject(basename($object), "", $parentid);
							$this->importImages($directoryPath.DS.basename($object), $newparent, "folders", true);
						}
						else {
							$this->importImages($directoryPath.DS.basename($object), $parentid, "date", true);
						}
					}
				}
				elseif (in_array(array_pop(explode(".", $object)), array("jpg", "jpeg", "png", "gif"))) { // watch out, we're dealing with images here!
					$parentforimage = NULL;

					// Determine folder the image is put in
					if ($folderstructure == "date") { // put image to folder by date
						// Check if folder with this date exists in this folder
						$exif_date = "Ei aikatietoja";
						if (in_array(array_pop(explode(".", $object)), array("jpg", "jpeg"))) {
							$exif = exif_read_data($directoryPath.DS.basename($object));
							if (!empty($exif['DateTime'])) {
								$exif_date = date("j.n.Y" ,strtotime($exif['DateTime']));
							}
						}

						// Check for folder with the date of this image
						$db =& JFactory::getDBO();
						$query = "SELECT objectid FROM #__helkakg WHERE parent = ".$parentid." AND title = '".$exif_date."' LIMIT 1;";
						$db->setQuery($query);
						$parentforimage = $db->loadResult();
						if (!$parentforimage) { // folder with this date doesn't exist, so we'll create a new one
							$parentforimage = $this->getNextObjectId();
							$this->createObject($exif_date, "", $parentid);
						}
					}
					else $parentforimage = $parentid; // put image to given folder

					$importedImage = array(
						"tmp_name" => $directoryPath.DS.basename($object),
						"size" => 0,
						"name" => $directoryPath.DS.basename($object)
					);
					$this->createObject("", "", $parentforimage, $importedImage);
				}
			}

			closedir($dir);
		}
	}

	public function updateObject($objectid, $title, $description, $parent=0) {
		global $rootdir;
		if (!$this->userIsAdmin()) return false;

		$object = new HelkaKGObject($objectid);
		if ($objectid != 1) $newParent = new HelkaKGObject($parent);
		else $parent = 0;
		if ($object->type == "img" or $object->type == "submission") $fileExtension = array_pop(explode(".", $object->filename));
		else $fileExtension = "";

		$newFileName = $this->createFileName($objectid, $title, $parent, $fileExtension);
		if ($objectid != 1) $newPath = $newParent->getPath(false, true).DS.$newFileName;
		else $newPath = realpath($object->getPath(false, true).DS."..".DS).DS.$newFileName;

		if (!$this->objectInfoIsOk($objectid, $title, $parent, $fileExtension)) return false;
		rename($object->getPath(false, true), $newPath);

		$oldpaththumb = $object->getPath(true, true);

		//Update database
		$updatableObject = new UpdatableObject($objectid, $parent, $newFileName, $title, $description);
		$db =& JFactory::getDBO();
		$db->updateObject('#__helkakg', $updatableObject, 'objectid', false);

		// Move thumbnail
		if ($object->type == "img" or $object->type == "submission") rename($oldpaththumb, $object->getPath(true, true));

		return true;
	}

	public function publishObject($objectid) {
		if (!$this->userIsAdmin()) return false;

		$object = new HelkaKGObject($objectid);

		//Update database
		$updatableObject = new UpdatableObject($object->objectid, $object->parent, $object->filename, $object->title, $object->description, "img");
		$db =& JFactory::getDBO();
		$db->updateObject('#__helkakg', $updatableObject, 'objectid', false);

		return true;
	}

	public function swapOrdering($objectid1, $objectid2) {
		if (!$this->userIsAdmin()) return false;

		// Swaps ordering between two objects
		$object1 = new HelkaKGObject($objectid1);
		$object2 = new HelkaKGObject($objectid2);
		$updatableObject1 = new UpdatableObject($objectid1);
		$updatableObject2 = new UpdatableObject($objectid2);
		$updatableObject1->ordering = $object2->ordering;
		$updatableObject2->ordering = $object1->ordering;
		$db =& JFactory::getDBO();
		$db->updateObject('#__helkakg', $updatableObject1, 'objectid', false);
		$db->updateObject('#__helkakg', $updatableObject2, 'objectid', false);
		return true;
	}

	public function orderingLast($objectid) {
		if (!$this->userIsAdmin()) return false;

		// Sets the ordering of $objectid to the end
		$object = new HelkaKGObject($objectid);
		$updatableObject = new UpdatableObject($objectid);
		$updatableObject->ordering = $this->getNextOrdering();
		$db =& JFactory::getDBO();
		$db->updateObject('#__helkakg', $updatableObject, 'objectid', false);
		return true;
	}
	public function orderingFirst($objectid) {
		if (!$this->userIsAdmin()) return false;

		// Sets the ordering of $objectid to the beginning
		$object = new HelkaKGObject($objectid);
		$updatableObject = new UpdatableObject($objectid);
		$updatableObject->ordering = $this->getSmallestOrdering();
		$db =& JFactory::getDBO();
		$db->updateObject('#__helkakg', $updatableObject, 'objectid', false);
		return true;
	}

	public function deleteObject($objectid) {
		global $rootdir;
		if (!$this->userIsAdmin()) return false;
		if ($objectid == $rootdir) return false;

		//Delete all children first
		$object = new HelkaKGObject($objectid);
		foreach($object->getChildren() as $childid) $this->deleteObject($childid);

		//Delete object itself
		if ($object->type == "img" or $object->type == "submission") {
			if (file_exists($object->getPath(false, true))) unlink($object->getPath(false, true));
			if (file_exists($object->getPath(true, true))) unlink($object->getPath(true, true));
		}
		else if ($object->type == "dir" && file_exists($object->getPath(false, true))) rmdir($object->getPath(false, true));

		//Delete from database
		$db =& JFactory::getDBO();
		$query = "DELETE FROM #__helkakg WHERE objectid = ".$objectid.";";
		$db->setQuery($query);
		$db->query();
	}

        function sendNotificationEmail($dir = 1) {
        	if ($this->userIsAdmin()) return false; // Send email only if image was uploaded by a visitor

	        $mailer =& JFactory::getMailer();
                $config =& JFactory::getConfig();

                // Add recipients from koconde
                $jdb =& JFactory::getDBO();
                $cdb = trim($config->get( 'db' ));
                if (file_exists('/var/www/globalconfig/globaldatabase')) {
			$gdb = trim(file_get_contents('/var/www/globalconfig/globaldatabase'));
                }
                $query = "SELECT email,name FROM ".$jdb->quoteName($gdb).".jos_koconde_contacts con ".
                "JOIN ".$jdb->quoteName($gdb).".jos_joomla_db_link lnk ON (lnk.koid = con.ko) ".
                "WHERE lnk.db = ".$jdb->quote($cdb)." ".
                "AND con.role = \"publiccontact\" ".
                "ORDER BY con.ispublic ASC";
                $jdb->setQuery($query);
		$jdb->query();
                $entries = $jdb->loadAssocList();
                foreach ($entries as $entry) {
                        $mailer->addRecipient($entry['email']);
                }


                $sender = array(
                        $config->get( 'mailfrom' ),
                        $config->get( 'fromname' )
                );
                $site = $config->get('sitename');

                $mailer->setSender($sender);

                $mailer->addRecipient($emails);
                $mailer->setSubject($site.': Kuvagalleriassa on uusia kuvia, jotka odottavat julkaisua');
                $body = "Tämä on automaattinen ilmoitus.\n\n";
		$body .= "Sivuston $site kuvagalleriaan on ladattu uusia kuvia.\nKuvat odottavat tarkastusta.\n\n";
                $body .= "Voit tarkastaa, ja julkaista tai poistaa kuvat sivuston kuvagallerian kautta.\n";

                $parsed_url = parse_url(JURI::base());
                if ($parsed_url) {
			$itemid = JSite::getMenu()->getActive()->id;
			$url = $parsed_url['scheme'].'://'.$parsed_url['host'];
//			$url .= "|erotus|";
			$url .= str_replace('components/com_helkakg/php/', '', JRoute::_('index.php?option=com_helkakg&dir='.$dir));
			$body .= "\n".$url."\n";
		}
                $mailer->setBody($body);
                $send =& $mailer->Send();
                if ( $send !== true ) {
                        return false;
                        //echo 'Error sending email: ' . $send->message;
                } else {
                        return true;
                        //echo 'Mail sent';
                }
        }

	// Some helpful functions below...

	// Create thumbnail image. For normal images it's just a thumbnail of the image,
	// for directories create a thumbnail from its contents
	public function createThumbnail($objectid) {
		$object = new HelkaKGObject($objectid);
		$thumb_w = 128;
		$thumb_h = $thumb_w;
		$image = imagecreatetruecolor($thumb_w, $thumb_h);

		// Preserve transparency
//		imagealphablending($image, false);
		imagesavealpha($image, true);
		$transparentindex = imagecolorallocatealpha($image, 255, 255, 255, 127);
		imagefill($image, 0, 0, $transparentindex);

		if ($object->type != "dir") {
	                // Get file extension
        	        $extension = strtolower(array_pop(explode(".", $object->fileName)));
                	if ($extension == "jpg" || $extension == "jpeg") $src_image = imagecreatefromjpeg($object->getPath(false, true));
	                elseif ($extension == "png") $src_image = imagecreatefrompng($object->getPath(false, true));
        	        elseif ($extension == "gif") $src_image = imagecreatefromgif($object->getPath(false, true));
                	else return false;

	                // Calculate thumbnail size
        	        list($src_w, $src_h) = getimagesize($object->getPath(false, true));

        	        $resize = $thumb_w / $src_w;
                	if ($resize * $src_h < $thumb_h) $resize = $thumb_h / $src_h;

	                $src_x = ($src_w - $thumb_w/$resize)/2;
        	        $src_y = ($src_h - $thumb_h/$resize)/2;

                	// Create thumbnail
        	        imagecopyresampled($image, $src_image, 0, 0, $src_x, $src_y, $thumb_w, $thumb_h, $thumb_w/$resize, $thumb_h/$resize);
		}
		else {
			// Put directory image as background
			$dirimgpath = getcwd().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'directory.png';
			$dirimg = imagecreatefrompng($dirimgpath);
			list($dirimg_w, $dirimg_h) = getimagesize($dirimgpath);
			imagecopyresampled($image, $dirimg, 0, 0, 0, 0, $thumb_w, $thumb_h, $dirimg_w, $dirimg_h);

			// Get directorys children (max 4)
			$childrenid = $object->getChildren();
			shuffle($childrenid);
			$childthumbs = array();
			foreach ($childrenid as $childid) {
				$childobject = new HelkaKGObject($childid);
				if ($childobject->type == 'submission') continue;
				$childthumbs[] = $this->createThumbnail($childid);
				if (count($childthumbs) >= 4) break;
			}
			// Check how many children you have (0, 1, 2, 3 or 4)
			// and create the thumbnail accordingly
			switch (count($childthumbs)) {
				case 1:
					imagecopyresampled($image, $childthumbs[0], 10, 10, 0, 0, $thumb_w-20, $thumb_h-20, $thumb_w, $thumb_h);
					break;
				case 2:
					imagecopyresampled($image, $childthumbs[0], 10, 10, 0, 0, $thumb_w/2-12, $thumb_h-20, $thumb_w/2, $thumb_h);
					imagecopyresampled($image, $childthumbs[1], $thumb_w/2 + 2, 10, 0, 0, $thumb_w/2-12, $thumb_h-20, $thumb_w/2, $thumb_h);
					break;
				case 3:
					imagecopyresampled($image, $childthumbs[0], 10, 10, 0, 0, $thumb_w/2-12, $thumb_h-20, $thumb_w/2, $thumb_h);
					imagecopyresampled($image, $childthumbs[1], $thumb_w/2 + 2, 10, 0, 0, $thumb_w/2-12, $thumb_h/2-12, $thumb_w, $thumb_h);
					imagecopyresampled($image, $childthumbs[2], $thumb_w/2 + 2, $thumb_h/2 + 2, 0, 0, $thumb_w/2-12, $thumb_h/2-12, $thumb_w, $thumb_h);
					break;
				case 4:
					imagecopyresampled($image, $childthumbs[0], 10, 10, 0, 0, $thumb_w/2-12, $thumb_h/2-12, $thumb_w, $thumb_h);
					imagecopyresampled($image, $childthumbs[1], $thumb_w/2 + 2, 10, 0, 0, $thumb_w/2-12, $thumb_h/2-12, $thumb_w, $thumb_h);
					imagecopyresampled($image, $childthumbs[2], $thumb_w/2 + 2, $thumb_w/2 + 2, 0, 0, $thumb_w/2-12, $thumb_h/2-12, $thumb_w, $thumb_h);
					imagecopyresampled($image, $childthumbs[3], 10, $thumb_h/2 + 2, 0, 0, $thumb_w/2-12, $thumb_h/2-12, $thumb_w, $thumb_h);
					break;
			}

			// Put directory top image on top of the small icons
//			$dirimgpath = getcwd().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'directory_top.png';
//			$dirimg = imagecreatefrompng($dirimgpath);
//			list($dirimg_w, $dirimg_h) = getimagesize($dirimgpath);
//			imagecopyresampled($image, $dirimg, 0, 0, 0, 0, $thumb_w, $thumb_h, $dirimg_w, $dirimg_h);

		}

		return $image;
	}

	// Write thumbnail file
	public function writeThumbnail($objectid) {
		$object = new HelkaKGObject($objectid);

		// Get file extension
		$extension = strtolower(array_pop(explode(".", $object->fileName)));

		// Write file
		if ($extension == "jpg" || $extension == "jpeg") imagejpeg($this->createThumbnail($objectid), $object->getPath(true, true));
		elseif ($extension == "png") imagepng($this->createThumbnail($objectid), $object->getPath(true, true));
		elseif ($extension == "gif") imagegif($this->createThumbnail($objectid), $object->getPath(true, true));
		// Directory thumbnail
		else imagepng($this->createThumbnail($objectid), $object->getPath(true, true));

		return true;
	}

	public function userIsAdmin() {
		$user =& JFactory::getUser();
		// Use the com_content component's access control
		if ($user->authorise('core.edit', 'com_content')) return true;
		return false;
	}

	public function getNextObjectId() {
//		if (!$this->userIsAdmin()) return false;

		// Returns the next available ID for a new object
		$db =& JFactory::getDBO();
		$query = "SELECT objectid FROM #__helkakg ORDER BY objectid DESC LIMIT 1;";
		$db->setQuery($query);
		$greatestId = $db->loadResult();

		return $greatestId+1;
	}

	public function getNextOrdering() {
//		if (!$this->userIsAdmin()) return false;

		// Returns the next available ordering for a new object
		$db =& JFactory::getDBO();
		$query = "SELECT ordering FROM #__helkakg ORDER BY ordering DESC LIMIT 1;";
		$db->setQuery($query);
		$greatestOrder = $db->loadResult();

		return $greatestOrder+1;
	}
	public function getSmallestOrdering() {
		if (!$this->userIsAdmin()) return false;

		// Returns the smallest ordering available
		$db =& JFactory::getDBO();
		$query = "SELECT ordering FROM #__helkakg ORDER BY ordering ASC LIMIT 1;";
		$db->setQuery($query);
		$greatestOrder = $db->loadResult();

		return $greatestOrder-1;
	}

	public function objectIsDir($objectid) {
		$object = new HelkaKGObject($objectid);
		return $object->type == "dir";
	}

	public function objectIsImg($objectid) {
		$object = new HelkaKGObject($objectid);
		return ($object->type == "img" or $object->type == "submission");
	}

	public function createFileName($objectid, $title, $parent, $fileExtension) {
//		if (!$this->userIsAdmin()) return false;

		$illegalChars = array("(", ")", DS, " ", "/", "\\", "?", "%", "*", ":", "|", "<", ">", '"', "'", "ä", "Ä", "Ö", "ö");
		$title = str_replace($illegalChars, "_", $title);
		$fileName = $parent."_".$objectid."_".$title;
		if ($fileExtension) $fileName .= ".".$fileExtension;
		return $fileName;
	}

	public function objectInfoIsOk($objectid, $title, $parent, $fileExtension) {
		global $rootdir;
//		if (!$this->userIsAdmin()) return false;

		// Checks on update/object creation if object information is ok

		$object = new HelkaKGObject($objectid);
		$newParent = new HelkaKGObject($parent);

		if ($objectid != 1) $newParent = new HelkaKGObject($parent);
                else $parent = 0;

                $newFileName = $this->createFileName($objectid, $title, $parent, $fileExtension);
                if ($objectid != 1) $newPath = $newParent->getPath(false, true).DS.$newFileName;
                else $newPath = realpath($object->getPath(false, true).DS."..".DS).DS.$newFileName;

		if (file_exists($newPath) && $newPath != $object->getPath(false, true) && $objectid != 1) {
			$this->errormessage = "Tämän niminen tiedosto on jo olemassa tässä kansiossa.";
			return false;
		}
		if ($title == "" && $fileExtension == "") {
			$this->errormessage = "Anna kansiolle otsikko.";
			return false;
		}
		if (!$this->objectIsDir($parent) && $objectid != 1) {
			$this->errormessage = "Valitsemasi kansio ei ole olemassa oleva kansio.";
			return false;
		}
		if ($this->folderIsParent($objectid, $parent)) {
			$this->errormessage = "Et voi siirtää kansiota itsensä alikansioksi.";
			return false;
		}

		return true;
	}

	public function getErrormessage() {
		return $this->errormessage;
	}

	public function folderIsParent($objectid, $parentid) {
		// returns true if $parentid is (grand)^n -parent
		// of $objectid (n = 0, 1, 2, ...) or $objectid == $parentid

		if ($parentid == 1 or $objectid == 1) return false; //root has no parents
		if ($objectid == $parentid) return true;

		$parent = new HelkaKGObject($parentid);
		return $this->folderIsParent($objectid, $parent->parent);
	}

	public function howManyParents($folderid) {
		// This function calculates the distance of a folder and the root folder
		// in the "family tree".

		if ($folderid == 1) return 0; // root has no parents

		$folder = new HelkaKGObject($folderid);

		// a folder has one more parent than its parentfolder
		return $this->howManyParents($folder->parent)+1;
	}

	public function log($message) {
		$logfile = fopen("../logs/logs", "a");
		fwrite($logfile, date("[d.m.y H:i] ", time()).$message."\n");
		fclose($logfile);
	}

}

class HelkaKGObject {

	// All images and folders are objects of this class.

	public $objectid;

	public function __construct($objectid=1) {
		$this->objectid = $objectid;
		if ($objectid < 1) $this->objectid = 1;
	}

	public function __get($name) {
		$db =& JFactory::getDBO();
		$query = "SELECT ".$name." FROM #__helkakg WHERE objectid = ".$this->objectid.";";
		$db->setQuery($query);
		$value = $db->loadResult();

		return $value;
	}

	public function getPath($thumbnail = false, $filehandling = false) {
		// root-folder:
		if ($this->objectid == 1) {
			// if getpath is called for move_uploaded_file, unlink, etc. use different path.
			if ($filehandling) return $_SERVER['DOCUMENT_ROOT'].HELKAKG_PATH."gallery".DS.$this->filename.($thumbnail ? DIRECTORY_SEPARATOR."thumbnail_self.png" : "");
			return "gallery".DS.$this->filename.($thumbnail ? DIRECTORY_SEPARATOR."thumbnail_self.png" : "");
		}
		if ($this->type == 'dir') {
			return $this->getParent()->getPath(false, $filehandling).DS.$this->filename.($thumbnail ? DIRECTORY_SEPARATOR."thumbnail_self.png" : "");
		}
		return $this->getParent()->getPath(false, $filehandling).DS.($thumbnail ? "thumbnail_" : "").$this->filename;
	}

	public function getParent() {
		if ($this->objectid != 1) return new HelkaKGObject($this->parent);
	}

	public function getChildren() {
		$db =& JFactory::getDBO();

		//folders first
		$query = "SELECT objectid FROM #__helkakg WHERE parent = ".$this->objectid." AND type = 'dir' ORDER BY ordering DESC;";
		$db->setQuery($query);
		$dirchildren = $db->loadRowList();

		//then images and submissions
		$query = "SELECT objectid FROM #__helkakg WHERE parent = ".$this->objectid." AND type != 'dir' ORDER BY ordering DESC;";
		$db->setQuery($query);
		$imgchildren = $db->loadRowList();

		$childrenid = array();
		foreach ($imgchildren as $child) array_unshift($childrenid, $child[0]);
		foreach ($dirchildren as $child) array_unshift($childrenid, $child[0]);

		return $childrenid;
	}

	public function nextObject() {
		$db =& JFactory::getDBO();
		if ($this->type == "dir") $sametype = "type = 'dir'";
		else $sametype = "(type = 'img' OR type = 'submission')";
		$query = "SELECT objectid FROM #__helkakg WHERE parent = ".$this->parent." AND ".$sametype." AND ordering > ".$this->ordering." ORDER BY ordering ASC LIMIT 1;";
		$db->setQuery($query);
		$next = $db->loadResult();
		if ($next) return $next;
		return false;
	}
	public function previousObject() {
		$db =& JFactory::getDBO();
		if ($this->type == "dir") $sametype = "type = 'dir'";
		else $sametype = "(type = 'img' OR type = 'submission')";
		$query = "SELECT objectid FROM #__helkakg WHERE parent = ".$this->parent." AND ".$sametype." AND ordering < ".$this->ordering." ORDER BY ordering DESC LIMIT 1;";
		$db->setQuery($query);
		$previous = $db->loadResult();
		if ($previous) return $previous;
		return false;
	}
}

// This class is for using JDatabase functions
class UpdatableObject {
	public $objectid;
	public $parent;
	public $fileName;
	public $title;
	public $description;
	public $type;
	public $ordering;

	public function __construct($objectid, $parent=NULL, $fileName=NULL, $title=NULL, $description=NULL, $type=NULL, $ordering=NULL) {
		$this->objectid = $objectid;
		if ($parent) $this->parent = $parent;
		if ($fileName) $this->fileName = $fileName;
		if ($title) $this->title = JFilterOutput::cleanText($title);
		if ($description) $this->description = JFilterOutput::cleanText($description);
		if ($type) $this->type = $type;
		if ($ordering) $this->ordering = $ordering;
	}
}

?>
