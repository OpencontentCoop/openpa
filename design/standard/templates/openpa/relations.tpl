<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style>{literal}
html,body,p,img,h1,h2,h3,h4,h5,h6,#header,ul,li,ol,fieldset,abbr,acronym,form,input{border:none;margin:0;padding:0;font-family: sans-serif;font-size: 10px}
html,body{height:100%;}
body{color:#000;text-align:left;}
#portfolio .img-list img{border:1px #ccc solid;}
table{border-collapse:collapse;border-spacing:0;width:100%;}
caption{text-align:left;}
q:before,q:after,blockquote:before,blockquote:after{content:'';}
input,select,textarea,button{font-size:1em;line-height:normal;width:auto;}
input,select{vertical-align:middle;}
textarea{height:auto;overflow:auto;}
option{padding-left:0.6em;}
button{border:0;text-align:center;}
ul,ol,li{list-style:none;}
ul.table{opacity:.9;float:left;background:#fff;-moz-border-radius:10px 10px 8px 8px;border:1px solid #AAA;float:left;overflow:hidden;z-index:10;box-shadow:2px 4px 10px -6px #000;-webkit-box-shadow:2px 4px 10px -6px #000;-moz-box-shadow:2px 4px 10px -6px #000;width: 200px;margin: 10px;}
ul.fields li{border-top:1px solid #ccc;border-bottom:1px solid #ccc;margin:0 0 -1px;padding:3px;}
ul li.title_table{-moz-border-radius:7px;background:#9ABEDE !important;cursor:move; padding: 5px 3px}
ul.fields{margin:8px 0 0;}
.visible{background:url("../img/visible.png") no-repeat scroll top left;height:20px;cursor:pointer;float:right;font-size:12px;font-weight:700;width:12px;margin:0 0 0 10px;padding:0 0 0 4px;}
li.{/literal}{$current.identifier}{literal}, li.related{background: #ddd}
li small{color: #aaa;}
li.title_table small{color: #fff;}
ul.current{background: #eee; border: 4px solid #aaa}
#inverse{width: 40%;float: left}
#current{width: 10%;float: left; min-width:250px;}
#direct{width: 40%;float: left}
span.hidden{text-decoration: line-through}
span.meta,span.details{font-style: italic}
{/literal}</style>
<!--[if IE]> 
<script type="text/javascript" src="http://explorercanvas.googlecode.com/svn/trunk/excanvas.js"></script>
<![endif]-->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript" ></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/jquery-ui.min.js"></script>
<script src={'javascript/jquery.masonry.min.js'|ezdesign()} type="text/javascript" ></script>
<script src={'javascript/jquery.jsPlumb-1.0.4-min.js'|ezdesign()} type="text/javascript" ></script>
<script type="text/javascript">{literal}
(function($){
    $(document).ready(function(){
        function reshowPlumb(){
            jsPlumb.detachEverything();
{/literal}
{foreach $inverse_relations as $class}{if $class.name}
$("ul.class-{$class.identifier} .{$current.identifier}").plumb({ldelim}target:'end-connect-{$current.identifier}'{rdelim});
{/if}{/foreach}
{foreach $direct_relations as $class}{if $class.name}
$("ul.current .{$class.identifier}").plumb({ldelim}target:'end-connect-{$class.identifier}'{rdelim});
{/if}{/foreach}
$("ul.current .{$current.identifier}").plumb({ldelim}target:'end-connect-{$current.identifier}'{rdelim});
{literal}
            $("li[id*='connect']").css('background-color','#f5f5f5')
            jsPlumb.repaintEverything();
        }
        $('#inverse').masonry({itemSelector : '.table'});
        $('#direct').masonry({itemSelector : '.table'});
        $(".table").draggable({
            cursor:'crosshair',
            addClasses:false,
            containment:'body',
            opacity:'0.3',
            drag:function(a,b){
                reshowPlumb();
            },
            stop:function(a,b){
                reshowPlumb();
            }
        });    
        jsPlumb.setDraggableByDefault(false);
        jsPlumb.DEFAULT_PAINT_STYLE={lineWidth:2,strokeStyle:'rgba(83,140,191,0.8)'};
        jsPlumb.DEFAULT_ENDPOINTS=[new jsPlumb.Endpoints.Dot({radius:5}),
                                   new jsPlumb.Endpoints.Dot({radius:5})];
        jsPlumb.DEFAULT_CONNECTOR=new jsPlumb.Connectors.Bezier(60);
        jsPlumb.DEFAULT_ANCHORS=[jsPlumb.Anchors.RightMiddle,jsPlumb.Anchors.LeftMiddle];
        
        reshowPlumb();
        $( window ).resize(function() { $('#inverse').masonry({itemSelector : '.table'});$('#direct').masonry({itemSelector : '.table'}); reshowPlumb(); });
})
})(jQuery);
{/literal}</script>

</head>
<body>

<div id="inverse">
{foreach $inverse_relations as $class}
{if $class.name}
<ul class="table class-{$class.identifier}">
	<li class="title_table" id="end-connect-{$class.identifier}">
    <a href={concat('openpa/relations/', $class.identifier)|ezurl}><b>{$class.name|wash()}</b></a>
    <br /><small>{foreach $class.ingroup_list as $group}{$group.group_name|wash()}{delimiter}, {/delimiter}{/foreach}</small>
  </li>
	<li>
      <ul class="fields">
      {foreach $class.data_map as $attribute}
        {def $css = ''}        
        {if $attribute.data_type_string|eq( 'ezobjectrelationlist' )}
          {set $css = $attribute.content.class_constraint_list|implode( ' ' )}
          <li class="{$css}">
			<span class="{$attribute.category}">{$attribute.name|wash()}</span> <small>({$attribute.data_type_string})</small>
			{if $attribute.is_searchable}&reg;{/if} {if $attribute.is_required}<strong>!</strong>{/if}
		  </li>
        {else}
          <li>
			<span class="{$attribute.category}">{$attribute.name|wash()}</span> <small>({$attribute.data_type_string})</small>
			{if $attribute.is_searchable}&reg;{/if} {if $attribute.is_required}<strong>!</strong>{/if}
		  </li>
        {/if}        
        {undef $css}
      {/foreach}
      </ul>
    </li>
</ul>
{/if}
{/foreach}
</div>

<div id="current">
<ul class="table class-{$current.identifier} current">
	<li class="title_table" id="end-connect-{$current.identifier}">
    <b>{$current.name|wash()}</b>
    <br /><small>{foreach $current.ingroup_list as $group}{$group.group_name|wash()}{delimiter}, {/delimiter}{/foreach}</small>
  </li>
	<li>
      <ul class="fields">
      {foreach $current.data_map as $attribute}
        {def $css = ''}        
        {if $attribute.data_type_string|eq( 'ezobjectrelationlist' )}
          {set $css = $attribute.content.class_constraint_list|implode( ' ' )}
          <li class="{$css} related">
			<span class="{$attribute.category}">{$attribute.name|wash()}</span>
			<small>({$attribute.data_type_string})</small>
			{if $attribute.is_searchable}&reg;{/if} {if $attribute.is_required}<strong>!</strong>{/if}
		  </li>
        {else}
          <li>
			<span class="{$attribute.category}">{$attribute.name|wash()}</span>
			<small>({$attribute.data_type_string})</small>
			{if $attribute.is_searchable}&reg;{/if} {if $attribute.is_required}<strong>!</strong>{/if}
		  </li>
        {/if}        
        {undef $css}
      {/foreach}
      </ul>
    </li>
</ul>
</div>

<div id="direct">
{foreach $direct_relations as $class}
{if $class.name}
<ul class="table class-{$class.identifier}">
	<li class="title_table" id="end-connect-{$class.identifier}">
    <a href={concat('openpa/relations/', $class.identifier)|ezurl}><b>{$class.name|wash()}</b></a>
    <br /><small>{foreach $class.ingroup_list as $group}{$group.group_name|wash()}{delimiter}, {/delimiter}{/foreach}</small>
  </li>
	<li>
      <ul class="fields">
      {foreach $class.data_map as $attribute}
        {def $css = ''}        
        {if $attribute.data_type_string|eq( 'ezobjectrelationlist' )}
          {set $css = $attribute.content.class_constraint_list|implode( ' ' )}
          <li class="{$css}">
			<span class="{$attribute.category}">{$attribute.name|wash()}</span>
			<small>({$attribute.data_type_string})</small>
			{if $attribute.is_searchable}&reg;{/if} {if $attribute.is_required}<strong>!</strong>{/if}
		  </li>
        {else}
          <li>
			<span class="{$attribute.category}">{$attribute.name|wash()}</span>
			<small>({$attribute.data_type_string})</small>
			{if $attribute.is_searchable}&reg;{/if} {if $attribute.is_required}<strong>!</strong>{/if}
		  </li>
        {/if}        
        {undef $css}
      {/foreach}
      </ul>
    </li>
</ul>
{/if}
{/foreach}
</div>


</body>
</html>