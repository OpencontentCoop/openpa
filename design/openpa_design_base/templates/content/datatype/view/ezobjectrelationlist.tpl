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

	{if module_params().parameters|count()|gt(0)}
		{if module_params().parameters.NodeID}
			{def $BNode_id=module_params().parameters.NodeID  $local_link=fetch(content,node,hash(node_id,$BNode_id))}
		{else}
			{def $local_link=$node}
		{/if}
	{/if}

	{section show=$attribute.content.relation_list}
		{section var=Relations loop=$attribute.content.relation_list sequence=array( bglight, bgdark )}
			{section show=$Relations.item.in_trash|not()}   

			{*	
			{set $obj=fetch( content, object, hash( object_id, $Relations.item.contentobject_id ) )}
			   {if $obj.class_identifier|eq('politico')}	
				{node_view_gui content_node=$obj.main_node view='line'}
			   {else}	
			*}

					
					{content_view_gui  view=embed  content_object=fetch( content, object, hash( object_id, $Relations.item.contentobject_id ) )}
			   {*/if*}	

			{/section}
			{*{delimiter} <span class="delimiter">-</span> {/delimiter}*}
			{delimiter} <span class="previous">-</span> {/delimiter}
		{/section}
	{/section}
{/if}

