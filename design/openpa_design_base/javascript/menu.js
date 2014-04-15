$(document).ready(function(){  	
    
	$('#topmenu-firstlevel > li > div > a').each(function(e){
		if ( $(this).parent().parent().hasClass('menu-area-tematica') || ( $('ul',  $(this).parent().parent()).length == 0 ) ){
			$(this).parent().parent().addClass('not-extend');
		}
	}).click(function(e){
		if ( $(this).parent().parent().hasClass('menu-area-tematica') || ( $('ul',  $(this).parent().parent()).length == 0 ) ){
            return true;
		}
		e.preventDefault();
	});
	$('#topmenu-firstlevel > li').bind('click', function(e){
        $('ul', '#topmenu-firstlevel').not($('ul', this)).removeClass('hover');
        $('li', '#topmenu-firstlevel').not($(this)).removeClass('hover');
        $('ul', this).toggleClass('hover');
        $(this).toggleClass('hover');
    }).not('.not-extend').attr('title', 'Click per aprire/chiudere il menu esteso');
	
	
	var cache = [];
	$.preLoadImages = function() {
		var args_len = arguments.length;
		for (var i = args_len; i--;) {
		  var cacheImage = document.createElement('img');
		  cacheImage.src = arguments[i];
		  cache.push(cacheImage);
		}
	}

    if ( $.browser.msie ){
        $("div.block-search select")
            .mousedown(function(){            
                $(this).css("width", "auto");
            })
            .blur(function(){
                $(this).css("width", '100%');
            })
            .change(function(){
                $(this).css("width", '100%');
            });
    }
});