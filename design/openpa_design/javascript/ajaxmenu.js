/*
Parametri attesi
node_id (int) variabile ez: $pagedata.path_array[$left_menu_depth].node_id 
current_node_id (int) variabile ez: $current_node_id
current_menu (string) variabile ez: $pagedata.current_menu
ui_context (string) variabile ez: $module_result.ui_context
is_area_tematica (int bool) variabile opencontent: $custom_keys.is_area_tematica
*/
(function($){
	$.fn.loadMenu = function(node_id, current_node_id, current_menu, ui_context, is_area_tematica) {
        // visualizza il menu con l'accordion via userParameters     
        this.load('/ezjscore/call/openpaajax::menu/(node_id)/'+node_id+'/(current_node_id)/'+current_node_id+'/(current_menu)/'+current_menu+'/(ui_context)/'+ui_context+'/(is_area_tematica)/'+is_area_tematica, function(){accordionMenu(this);})
        accordionMenu(this);
	}

	function selectedActivable(_this){
		$('li div span.selected.activable', this).removeClass("activable").addClass('selected-activable');
	}

	function accordionMenu(_this) {
		$('ul', _this).hide();
		$('li div span', _this).click(
			function() {
				$('li div span.selected.activable', _this).removeClass("activable").addClass('selected-activable');
				var parentsElement = $(this).parents();
				var parentHandler = $(this).parents().filter('li').children().children();
				var checkElement = $(this).parent().next();
				if((checkElement.is('ul')) && (checkElement.is(':visible'))) {
					$(this).removeClass("active").addClass("activable");
					checkElement.slideUp('normal');
					$('li div span.selected.activable', _this).removeClass("activable").addClass('selected-activable');
				}
				if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
					$('li div span.active', this).parent().next().slideUp('normal');
					$('li div span.active', _this)
						.not(parentHandler)
						.removeClass("active")					
						.addClass("activable");
					
					$(this).removeClass("activable").removeClass("selected-activable").addClass("active");
					
					$('ul:visible', _this)
						.not(parentsElement)					
						.slideUp('normal');
					checkElement.slideDown('normal');
					$('li div span.selected.activable', _this).removeClass("activable").addClass('selected-activable');
					return false;
				}
				parentHandler.css('color:#fff');			
			}
		);	
	$('a.current, a.selected', _this).parent().next().show();	
	$("span.activable ~ a.selected", _this).prev().removeClass("activable").addClass("active");
	$("span.activable ~ a.current", _this).prev().removeClass("activable").addClass("active");
	}  
	
})(jQuery)