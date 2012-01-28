{literal}
<script type="text/javascript">
$(document).ready(function(){
	var prefix = '{/literal}{ezini( 'CookiesSettings', 'CookieKeyPrefix', 'cookieoperator.ini' )}{/literal}';
	var dimensione = readCookie( prefix + 'dimensione' );
	if (dimensione) switchSize(dimensione);
	
	function switchSize(tipo,valore){
		if (tipo == 'dimensione'){
			if (valore == 'normale'){
				$('body').css('font-size', '0.8em');
			}else{
				$('body').css('font-size', '1em');
			}
		}
		if (tipo == 'layout'){
			if (valore == 'fluido'){
				$('div.width-layout').css('width', '100%');
                $('body').addClass('fluido');
			}else{
				$('div.width-layout').css('width', '990px');
                $('body').removeClass('fluido');
			}
		}
        createCookie( prefix + tipo, valore, 365);
	}
    
    $(document).bind('switchSize', function(event, param1, param2) {switchSize(param1, param2);} );
	
	$("#riduci-caratteri").click( function(){
        $(document).trigger('switchSize', ['dimensione', 'normale']);
		$("#aumenta-caratteri").removeClass("access_selected");
		$(this).addClass("access_selected");
		return false;
    });
	$("#aumenta-caratteri").click( function(){
        $(document).trigger('switchSize', ['dimensione', 'grande']);
		$("#riduci-caratteri").removeClass("access_selected");
		$(this).addClass("access_selected");		
		return false;
    });
	$("#layout-rigido").click( function(){
        switchSize('layout','rigido');
        $(document).trigger('switchSize', ['layout', 'rigido']);
		$("#layout-fluido").removeClass("access_selected");
		$(this).addClass("access_selected");
		return false;
    });
	$("#layout-fluido").click( function(){
        $(document).trigger('switchSize', ['layout', 'fluido']);
		$("#layout-rigido").removeClass("access_selected");
		$(this).addClass("access_selected");		
		return false;
    });
	
});
</script>
{/literal}


<div class="accessibilita">
<h2 class="hide">Strumenti di accessibilit&agrave;</h2>

<a rel="alternate" {if $custom_keys.dimensione|eq('normale')}class="access_selected"{/if} id="riduci-caratteri" href="?dimensione=normale" title="Visualizza caratteri normali">Visualizza caratteri normali</a>

<span class="hide"> - </span>

<a rel="alternate" {if $custom_keys.dimensione|eq('grande')}class="access_selected"{/if} id="aumenta-caratteri" href="?dimensione=grande" title="Visualizza caratteri grandi">Visualizza caratteri grandi</a>

<span class="hide"> - </span>

<a rel="alternate" {if $custom_keys.contrasto|eq('alto')}class="access_selected"{/if} id="alto-contrasto" href="?contrasto=alto" title="Visualizzazione ad alto contrasto">Visualizzazione ad alto contrasto</a>

<span class="hide"> - </span>

<a rel="alternate" id="normale" class="ac-show{if $custom_keys.contrasto|eq('normale')} access_selected{/if}" href="?contrasto=normale" title="Visualizzazione normale">Visualizzazione normale</a>

<span class="hide"> - </span>

<a rel="alternate" {if $custom_keys.layout|eq('rigido')}class="access_selected"{/if} id="layout-rigido" href="?layout=rigido" title="Comprimi pagina a dimensione fissa">Comprimi pagina a dimensione fissa</a> 

<a rel="alternate" {if $custom_keys.layout|eq('fluido')}class="access_selected"{/if} id="layout-fluido" href="?layout=fluido" title="Espandi pagina alla dimensione della finestra">Espandi pagina alla dimensione della finestra</a>

</div>