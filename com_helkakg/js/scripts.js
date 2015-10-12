var path = getPath();
var loadimg = "<img src='"+path+"images/loading.gif' class='helkakg_loadimg'>";
var ie_fix = new Date().getTime();

jQuery(document).ready(function($){

	/* Create popup-boxes */

	var popupboxhelper = document.createElement("div");
	popupboxhelper.setAttribute("id", "helkakg_popupboxhelper");

	var imagecontainerhelper = document.createElement("div");
	imagecontainerhelper.setAttribute("id", "helkakg_imagecontainerhelper");

	var docfrag = document.createDocumentFragment();
	docfrag.appendChild(imagecontainerhelper);
	docfrag.appendChild(popupboxhelper);
	document.body.insertBefore(docfrag, document.body.firstChild);


	$("#helkakg_imagecontainerhelper, #helkakg_popupboxhelper").hide();
	$("#helkakg_imagecontainerhelper").html("<div id='helkakg_imagecontainer'></div><div class='helkakg_shade'></div>");
	$("#helkakg_popupboxhelper").html("<div id='helkakg_popupbox'></div><div class='helkakg_shade'></div>");

	$("#helkakg_loading").hide();

	if (cookiedir === 'undefined' || cookiedir === null || cookiedir == '') cookiedir=1;
	var cookieurl="&dir="+cookiedir;

	if (cookierootdir === 'undefined' || cookierootdir === null || cookierootdir == '') cookierootdir=1;
	var cookierooturl="&rootdir="+cookierootdir;

	if (cookiesubfolders === 'undefined' || cookiesubfolders === null || cookiesubfolders == '') cookiesubfolders=1;
	var cookiesubfoldersurl="&subfolders="+cookiesubfolders;

	if (cookielisttype === 'undefined' || cookielisttype === null || cookielisttype == '') cookielisttype=1;
	var cookielisttypeurl="&listtype="+cookielisttype;

	$("#helkakg").load(path+"php/action.php?action=displayObject"+cookieurl+cookierooturl+cookiesubfoldersurl+cookielisttypeurl+"&iefix="+new Date().getTime());




	/* Clicking on a thumbnail */

	$("#helkakg").on("click", ".helkakg_thumbnailpic, .helkakg_bclink", function(){
		$("#helkakg_loading").show("fast");

		var a = $(this).attr("id").split("_");
		var fullpath = path+"php/action.php?action=displayObject&objectid="+a[2]+"&iefix="+new Date().getTime()+cookierooturl+cookiesubfoldersurl+cookielisttypeurl;
		
		if (a[1] == "dir" || a[1] == "breadcrumbs") {
			$("#helkakg").hide("fast", function(event){
				$("#helkakg").load(fullpath,function(responseTxt,statusTxt,xhr){
					if (statusTxt=="success") {
						$("#helkakg").show("fast");
						$("#helkakg_loading").hide("fast");
					}
				});
			});
		}
		else if (a[1] == "img") {
			$("#helkakg_imagecontainer").load(fullpath,function(responseTxt,statusTxt,xhr){
				if (statusTxt=="success") {
					$("#helkakg_imagecontainerhelper").show("fast");
					$("#helkakg_loading").hide("fast");
				}
			});
		
		}
	});





	/* Closing the "popup"-box */

	$(".helkakg_shade").click(function(){
		$(this).parent().hide("fast");
	});

	$("#helkakg_imagecontainer").on("click", ".helkakg_return", function(){
		$("#helkakg_imagecontainerhelper").hide("fast");
	});

	$("#helkakg_popupbox").on("click", ".helkakg_return", function(){
		$("#helkakg_popupboxhelper").hide("fast");
	});






	/* Previous/Next arrow click */

	$("#helkakg_imagecontainer").on("click", ".helkakg_prevnext", function(){
		var a = $(this).attr("id").split("_");
		var fullpath = path+"php/action.php?action=displayObject&objectid="+a[2]+"&iefix="+new Date().getTime()+cookierooturl+cookiesubfoldersurl+cookielisttypeurl;

		$("#helkakg_imagecontainer .helkakg_bigimage").fadeOut("fast", function(){
			$("#helkakg_imagecontainer").load(fullpath, function(responseTxt,statusTxt,xhr){
				if (statusTxt=="success") {
					$("#helkakg_imagecontainer .helkakg_bigimage").hide().fadeIn("fast");
				}
			});
		});
	});




	/* Show/hide thumbnail admin icons on hover */

	$("#helkakg").on("mouseenter", ".helkakg_thumbnail", function(){
		$(this).find(".helkakg_smallicon").fadeIn("fast");
	});
	$("#helkakg").on("mouseleave", ".helkakg_thumbnail", function(){
		$(this).find(".helkakg_smallicon").fadeOut("fast");
	});





	/* Clicking the thumbnail admin icons */

	$("#helkakg").on("click", ".helkakg_thumbnail .helkakg_smallicon", function(){
		$("#helkakg_loading").show("fast");

		var a = $(this).attr("id").split("_");
		var fullpath = path+"php/action.php?action="+a[1]+"&objectid="+a[2]+"&iefix="+new Date().getTime()+cookierooturl+cookiesubfoldersurl+cookielisttypeurl;

		/* No need for popup box in case of order editing */
		if (a[1] == "editOrdering") {
			fullpath += "&type="+a[3];
			$("#helkakg").load(fullpath, $("#helkakg_loading").hide("fast"));
		}
		/* ...or publishing */
		else if (a[1] == "publish") {
			$("#helkakg").load(fullpath, $("#helkakg_loading").hide("fast"));		
		}

		/* In other cases - let's pop that box up. */
		else {
			$("#helkakg_popupbox").load(fullpath,function(responseTxt,statusTxt,xhr){
				if (statusTxt=="success") {
					$("#helkakg_popupboxhelper").show("fast");
					$("#helkakg_loading").hide("fast");
				}
			});
		}
	});





	/* Confirming/Not confirming delete action */

	$("#helkakg_popupbox").on("click", ".helkakg_error .helkakg_smalliconcontainer", function(){
		if (!$(this).hasClass("helkakg_cancel")) { /* Delete */

			var a = $(this).attr("id").split("_");
			var fullpath = path+"php/action.php?action="+a[1]+"&objectid="+a[2]+"&iefix="+new Date().getTime()+cookierooturl+cookiesubfoldersurl+cookielisttypeurl;

			var selector = "#helkakg_img_"+a[2]+", #helkakg_dir_"+a[2];

			$(selector).parent().fadeOut("slow", function(){
				$("#helkakg").load(fullpath);
			});
			if (!$(selector).length) $("#helkakg").load(fullpath);

			$("#helkakg_imagecontainerhelper").hide("fast");
		}
		$("#helkakg_popupboxhelper").hide("fast");
	});






	/* Sending edit form */

	$("#helkakg_popupbox").on("click", ".helkakg_submit", function(){
		$("#helkakg_errordiv").fadeOut("fast");

		var a = $(this).attr("id").split("_");
		var fullpath = path+"php/action.php?iefix="+new Date().getTime()+cookierooturl+cookiesubfoldersurl+cookielisttypeurl;
		var headers = {
			"helkakg_title": $("#helkakg_title").val(),
			"helkakg_description": $("#helkakg_description").val(),
			"action": a[1]
		};
		if (a[1] != "newFolderHandle") headers.objectid = a[2];
		if (a[2] != 1 || a[1] == "newFolderHandle") headers.helkakg_parent = $("#helkakg_parent").val();

		if ($("input[name=helkakg_object_type]").val() == "dir" && !headers.helkakg_title) $("#helkakg_errordiv").load(fullpath, headers, $("#helkakg_errordiv").fadeIn("fast"));
		else $("#helkakg").load(fullpath, headers, $("#helkakg_popupboxhelper").hide("fast"));

		if ($("#helkakg_imagecontainerhelper").is(":visible")) {
			fullpath = path+"php/action.php?action=displayObject&objectid="+a[2]+"&iefix="+new Date().getTime()+cookierooturl+cookiesubfoldersurl+cookielisttypeurl;
			$("#helkakg_imagecontainer").load(fullpath, $("#helkakg_popupboxhelper").hide("fast"));
		}
	});







	/* Sending import form */

	$("#helkakg_popupbox").on("click", "#helkakg_import", function(){
		$("input#helkakg_import").attr("disabled", true).val("Tuodaan kuvia...");
		$("#helkakg_loadimgdiv").html(loadimg);

		var a = $(this).attr("id").split("_");
		var fullpath = path+"php/action.php?action=import&iefix="+new Date().getTime()+cookierooturl+cookiesubfoldersurl+cookielisttypeurl;
		fullpath += "&parentid="+$("#helkakg_parent").val();
		fullpath += "&directoryPath="+$("#helkakg_directoryPath").val();
		fullpath += "&includeSubdirs=";
		if ($("#helkakg_includeSubdirs").is(":checked")) fullpath += "1";
		else fullpath += "0";
		fullpath += "&folderstructure="+$("input[name=folderstructure]:checked").val();
		$("#helkakg").load(fullpath, $("#helkakg_popupboxhelper").hide("fast"));
	});






	/* Clicking on admin links in the upper right corner */

	$("html").on("click", ".helkakg_adminlinks .helkakg_smalliconcontainer", function(){
		$("#helkakg_loading").show("fast");

		var a = $(this).attr("id").split("_");
		var fullpath = path+"php/action.php?action="+a[1]+"&iefix="+new Date().getTime()+cookierooturl+cookiesubfoldersurl+cookielisttypeurl;
		if (a.length > 2) {
			if (a[1] == "newFolder" || a[1] == "uploadImage" || a[1] == "importImages") fullpath += "&parentid="+a[2];
			else fullpath += "&objectid="+a[2];
		}

		if (a[1] == "displayHelp") {
			$("#helkakg_imagecontainer").load(fullpath,function(responseTxt,statusTxt,xhr){
				if (statusTxt=="success") {
					$("#helkakg_imagecontainerhelper").show("fast");
					$("#helkakg_loading").hide("fast");
				}
			});
		}
		else if (a[1] == "publish") {
			$("#helkakg").load(fullpath,function(responseTxt,statusTxt,xhr){
				if (statusTxt=="success") {
					$("#helkakg_imagecontainerhelper").hide("fast");
					$("#helkakg_loading").hide("fast");
				}
			});
		}
		else {
			$("#helkakg_popupbox").load(fullpath,function(responseTxt,statusTxt,xhr){
				if (statusTxt=="success") {
					$("#helkakg_popupboxhelper").show("fast");
					$("#helkakg_loading").hide("fast");
				}
			});
		}
	});






	/* Clicking submit on upload */

	$("html").on("submit", "#helkakg_upload_form", function(evt){
		/* Prevent from sending empty form */
		var uploaded_file_exists = false;
		for (var i = 1; i < 11; i++) {
			if ($("input[name='helkakg_image"+i+"']").val()) {
				uploaded_file_exists = true;
				break;
			}
		}

		if (!uploaded_file_exists) {
			evt.preventDefault();
			return;
		}

		$("#helkakg_uploader #helkakg_submit").attr("disabled", true).val("Ladataan kuvia...");
		$("#helkakg_loadimgdiv").html(loadimg);

		$("#hidden_upload").load(function(){
			var fullpath = path+"php/action.php?action=displayObject&objectid="+$("#helkakg_parent").val()+"&iefix="+new Date().getTime()+cookierooturl+cookiesubfoldersurl+cookielisttypeurl;
			$("#helkakg").load(fullpath);
			$("#helkakg_popupboxhelper").hide("fast");
		});
	});

	/* Calculating and showing size of files being uploaded */
	$(".helkakg_fileinput").live('change', function(){
		var filesize_sum = 0;
		$(".helkakg_fileinput").each(function(){
			var filesize = 0;
			if (typeof $(this)[0].files != 'undefined') {
				if (typeof $(this)[0].files[0] != 'undefined') {
					if (typeof $(this)[0].files[0].size != 'undefined') {
						filesize = $(this)[0].files[0].size;
						filesize_sum += filesize;
					}
				}
			}
			if (filesize > 0) {
				$(this).parent().siblings().children(".helkakg_filesize").html(Math.floor(filesize/1024) + " kt");
				$(this).parent().siblings().children(".helkakg_clearfile").show();
			}
			else {
				$(this).parent().siblings().children(".helkakg_filesize").html("");
				$(this).parent().siblings().children(".helkakg_clearfile").hide();
			}
		});
		$("#helkakg_filesize_sum").html(Math.floor(filesize_sum/1024));
		if (filesize_sum > 8388608) {
			$("#helkakg_filesize_sum").css("color", "red");
			$("input#helkakg_submit").attr("disabled", true).val("Kuvien yhteenlaskettu tiedostokoko ylittää sallitun rajan.");
		}
		else {
			$("#helkakg_filesize_sum").css("color", "");
			$("input#helkakg_submit").attr("disabled", false).val("Lataa kuvat");
		}
	});

	$("html").on("click", ".helkakg_clearfile", function(){
		var fileinput = $(this).parent().siblings().children(".helkakg_fileinput");
		fileinput.val("").change();
	});

});
