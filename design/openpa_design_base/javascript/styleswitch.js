
(function($)
{
	$(document).ready(function() {
		$('.styleswitch').click(function()
		{
			switchStylestyle(this.getAttribute("rel"));
			return false;
		});		
		var c = readCookie('style');
		if (c) switchStylestyle(c);
	});

	function switchStylestyle(styleName)
	{

		if (styleName=="normale") {
			var link_ = document.getElementsByTagName("link");
			for ($i=0;$i<link_.length;$i++) {
				if(link_[$i].rel=="stylesheet") link_[$i].disabled=false;
				if(link_[$i].rel=="alternate stylesheet") link_[$i].disabled=true;
			}
			createCookie('style', styleName, 365);
		} else {
			var link_ = document.getElementsByTagName("link");
			for ($i=0;$i<link_.length;$i++) {
				if(link_[$i].rel=="stylesheet") link_[$i].disabled=true;
				if(link_[$i].rel=="alternate stylesheet") link_[$i].disabled=false;
			}
			createCookie('style', styleName, 365);
		} 
	}


})(jQuery);
