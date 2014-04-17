{def $obj = false()}
{if is_set($href)}
	{if $href|eq('nolink')}
	{section show=$attribute.content.relation_list}
		{section var=Relations loop=$attribute.content.relation_list sequence=array( bglight, bgdark )}
			{section show=$Relations.item.in_trash|not()}
			    {set $obj=fetch( content, object, hash( object_id, $Relations.item.contentobject_id ) )}
				{if $obj.class_identifier|eq('tipo_struttura')}
					{def $tipi_stuttura_invisibili=openpaini( 'GestioneAttributi', 'tipi_stuttura_invisibili', array())}
					{if $tipi_stuttura_invisibili|contains($obj.data_map.tipo_struttura.content)|not()}
						{content_view_gui view=embed_nolink content_object=$obj}
					{/if}
				{else}
					{content_view_gui view=embed_nolink content_object=$obj}
				{/if}			    
			{/section}
			{delimiter} <span class="previous">-</span> {/delimiter}
		{/section}
	{/section}
	{elseif $href|eq('noedit')}
		{section show=$attribute.content.relation_list}
			{section var=Relations loop=$attribute.content.relation_list}
			{if $Relations.item.in_trash|not()}
			    {content_view_gui view=embed content_object=fetch( content, object, hash( object_id, $Relations.item.contentobject_id ) )}
			    <br />
			{/if}
			{/section}
		{section-else}
			{'There are no related objects.'|i18n( 'design/standard/content/datatype' )}
		{/section}

	{elseif $href|eq('estesa')}

		{set $cont_obj=fetch( content, object, hash( object_id, $Relations.item.contentobject_id ) )}
		{node_view_gui content_node=$cont_obj.main_node view='line'}

	{/if}
{else}
	{section show=$attribute.content.relation_list}
		{section var=Relations loop=$attribute.content.relation_list sequence=array( bglight, bgdark )}
			{section show=$Relations.item.in_trash|not()}   

                {set $obj=fetch( content, object, hash( object_id, $Relations.item.contentobject_id ) )}
                {if $obj.can_read}
                    {content_view_gui  view=embed  content_object=$obj}
                {else}
					{$obj.name|wash()}
                {/if}
			{/section}			
			{delimiter}, {/delimiter}
		{/section}
	{/section}
{/if}

