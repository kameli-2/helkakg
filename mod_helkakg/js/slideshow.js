jQuery(document).ready(function($){
	var lastclick = 0;

	$('.mod_helkakg_arrow').click(function(){
		var arrow = $(this);

		if (lastclick+1000 < new Date().getTime()) {
			lastclick = new Date().getTime();

			var animate = '50px';
			if (arrow.hasClass('mod_helkakg_left')) {
				var firstimg = arrow.siblings('.mod_helkakg_images').children(':first-child');
				var firstimgwidth = firstimg.outerWidth(true);
				animate = '-='+firstimgwidth;
			}
			else {
				var lastimg = arrow.siblings('.mod_helkakg_images').children(':last-child');
				var lastimgwidth = lastimg.outerWidth(true);
				animate = '+='+lastimgwidth;

				var lastimg = arrow.siblings('.mod_helkakg_images').children(':last-child');
				lastimg.prependTo(arrow.siblings('.mod_helkakg_images'));
				arrow.siblings('.mod_helkakg_images').children().animate({left: '-='+lastimgwidth}, 0);
			}

			arrow.siblings('.mod_helkakg_images').children().each(function(){
				$(this).animate({left: animate}, 1000);
			});

			setTimeout(function(){
				if (arrow.hasClass('mod_helkakg_left')) {
					var firstimg = arrow.siblings('.mod_helkakg_images').children(':first-child');
					firstimg.appendTo(arrow.siblings('.mod_helkakg_images'));
				}
				else {
				}
				arrow.siblings('.mod_helkakg_images').children().animate({left: 0}, 0);
			}, 1000);
		}
	});

	window.setInterval(function(){
		if (lastclick+4900 < new Date().getTime()) $('.mod_helkakg_left').click();
	}, 5000);
});
