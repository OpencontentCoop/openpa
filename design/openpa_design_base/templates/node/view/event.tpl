{* Event - Line view *}

<div class="content-view-line">

{if gt(currentdate() , $node.object.data_map.to_time.content.timestamp)}
    <div class="class-event ezagenda_event_old">
{else}
   <div class="class-event">
{/if}

    <h3>
        <a title="{$node.object.data_map.abstract.content.output.output_text|explode("<br />")|implode(" ")|strip_tags()|trim()}" 
	   href={$node.url_alias|ezurl}>
		{$node.name|shorten(73)|wash()}
	</a>
    </h3>  

    <span class="ezagenda_date">
	{if $node.object.data_map.periodo_svolgimento.has_content}
		{attribute_view_gui attribute=$node.object.data_map.periodo_svolgimento}
	{else}

	{$node.object.data_map.from_time.content.timestamp|datetime(custom,"%j %F")|shorten( 12 , '')}
    	  {if and($node.object.data_map.to_time.has_content,  ne( $node.object.data_map.to_time.content.timestamp|datetime(custom,"%j %M"),
            $node.object.data_map.from_time.content.timestamp|datetime(custom,"%j %M") ))}
       	    - {$node.object.data_map.to_time.content.timestamp|datetime(custom,"%j %F")|shorten( 12 , '')}
    	  {/if}
    	{/if}
    </span>

    <div class="ezagenda_abstract no-js-hide">
        {if $node.object.data_map.abstract.has_content}
            {attribute_view_gui attribute=$node.object.data_map.abstract}
        {elseif $node.object.data_map.materia.has_content}
            {attribute_view_gui attribute=$node.data_map.materia}    	
        {/if}
    </div>	  

  </div>
</div>
