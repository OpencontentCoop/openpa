(function($){    
	$.fn.OpenPAAccordionMenu = function() {
		$('ul', this).hide();
		$('li div span', this).click( function(e) {
				$('li div span.selected.activable', this).removeClass("activable").addClass('selected-activable');
				var parentsElement = $(this).parents();
				var parentHandler = $(this).parents().filter('li').children().children();
				var checkElement = $(this).parent().next();
				if((checkElement.is('ul')) && (checkElement.is(':visible'))) {
					$(this).removeClass("active").addClass("activable");
					checkElement.slideUp('normal');
					$('li div span.selected.activable', this).removeClass("activable").addClass('selected-activable');
				}
				if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
					$('li div span.active', this).parent().next().slideUp('normal');
					$('li div span.active', this)
						.not(parentHandler)
						.removeClass("active")					
						.addClass("activable");
					
					$(this).removeClass("activable").removeClass("selected-activable").addClass("active");
					
					$('ul:visible', this)
						.not(parentsElement)					
						.slideUp('normal');
					checkElement.slideDown('normal');
					$('li div span.selected.activable', this).removeClass("activable").addClass('selected-activable');
					e.preventDefault();
				}
				parentHandler.css('color:#fff');			
			}
		);	
        $('a.current, a.selected', this).parent().next().show();	
        $("span.activable ~ a.selected", this).prev().removeClass("activable").addClass("active");
        $("span.activable ~ a.current", this).prev().removeClass("activable").addClass("active");
	}  
	
})(jQuery)

$(document).ready(function(){  	    
	$('#topmenu-firstlevel a').each( function(e){
        var node = $(this).data( 'contentnode' );
        if (node) {
            var href = $(this).attr( 'href' );
            if ( UiContext == 'browse' ) {
                href = '/content/browse/' + node;
            }            
            $(this).attr( 'href', href );
            var self = $(this);
            $.each(PathArray, function(i,v){                
                if (v==node){                    
                    self.parents( 'li' ).addClass( 'selected' );                    
                    if (i==0)
                        self.parents( 'li' ).addClass( 'current' );
                }
            });
        }
    });
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
    
    var openMenu = null; 
	$('#topmenu-firstlevel > li').bind('click', function(e){
        $('ul', '#topmenu-firstlevel').not($('ul', this)).removeClass('hover');
        $('li', '#topmenu-firstlevel').not($(this)).removeClass('hover');
        $('ul', this).toggleClass('hover');
        $(this).toggleClass('hover');
        if ( $(this).hasClass("hover") ){
            openMenu = $(this);
        }else{
            openMenu = null;
        } 
    }).not('.not-extend').attr('title', 'Click per aprire/chiudere il menu esteso');
	
    $(window).bind('click',function(e){
        if( openMenu !== null && $(e.target).parents("#topmenu-firstlevel").length == 0){
            openMenu.trigger('click');
        }
    }); 
    
    $('#sidemenu a').each( function(e){
        var node = $(this).data( 'contentnode' );
        if (node) {
            var href = $(this).attr( 'href' );
            if ( UiContext == 'browse' ) {
                href = '/content/browse/' + node;
            }
            //if ( UriPrefix != '/' ) {
            //    href = UriPrefix + href;
            //}
            $(this).attr( 'href', href );
            var self = $(this);
            $.each(PathArray, function(i,v){                
                if (v==node){                    
                    self.addClass( 'selected' );                    
                    self.prev().addClass( 'selected' );                    
                    self.parents( 'li' ).addClass( 'selected' );                    
                    if (i==0)
                    {
                        self.addClass( 'current' );
                        self.prev().addClass( 'current' );
                        self.parents( 'li' ).addClass( 'current' );
                    }
                }
            });
        }
    });
    $('#sidemenu .menu-list').OpenPAAccordionMenu();
	
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