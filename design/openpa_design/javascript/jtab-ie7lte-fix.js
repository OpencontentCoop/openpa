/* @DEV correzione di un bug di ie7lte @TODO trovare soluzione senza js */
$(document).ready(function(){  		
	if ($.browser.msie && ($.browser.version < "8.0")) {
		$('.block-lista_tab .ui-tabs .ui-tabs-nav li').each( function(){
			var _w = $(this).width();
			var w = _w - 8;
			$('.border-tc', this).css('width', w);
		});
	}
});
