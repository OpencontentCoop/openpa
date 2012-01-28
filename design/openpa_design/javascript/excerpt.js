$(document).ready(function() {
	
	$('.attribute-competenze .col-content-design').each( function() {
		var $r = $('<a href="#" class="mostra-tutto">Mostra tutto</a>'); 
		var $div = $(this);
		var o = $div.html();
		$div.excerpt({ lines: 0, always_end: $r }); 
		$div.find('a').click(function(e){e.preventDefault(); $div.html(o);}); 
	}); 
	$('.attribute-personale .col-content-design').each( function() {
		var $r = $('<a href="#" class="mostra-tutto">Mostra tutto</a>'); 
		var $div = $(this);
		var o = $div.html();
		//$div.excerpt({ lines: 0, always_end: $r }); 
		$div.html($r);
		$div.find('a').click(function(e){e.preventDefault(); $div.html(o);}); 
	}); 
});