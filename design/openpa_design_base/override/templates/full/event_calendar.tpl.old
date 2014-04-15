{include name=menu_control node=$node uri='design:parts/common/menu_control.tpl'}
{set-block scope=root variable=cache_ttl}400{/set-block}

{def $type_c = $node.data_map.view.class_content.options[$node.data_map.view.value[0]].name|downcase()}
{if $type_c|eq('program')}
	{include uri=concat("design:full/event_view_", $type_c, ".tpl") }
{else}
	{include uri=concat("design:full/event_view_calendar.tpl") }
{/if}
