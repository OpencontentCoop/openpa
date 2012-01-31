$(document).ready(function(){  	

	$('#topmenu-firstlevel > li > div > a').click(function(e){
		if ( $(this).parent().parent().hasClass('menu-area-tematica') ) 
			return true;
		e.preventDefault();
	});
	$('#topmenu-firstlevel > li').bind('click', function(e){
        $('ul', '#topmenu-firstlevel').not($('ul', this)).removeClass('hover');
        $('li', '#topmenu-firstlevel').not($(this)).removeClass('hover');
        $('ul', this).toggleClass('hover');
        $(this).toggleClass('hover');
    });

	$('#topmenu-firstlevel > li').not('.menu-area-tematica').attr('title', 'Click per aprire/chiudere il menu esteso');	
	
	
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