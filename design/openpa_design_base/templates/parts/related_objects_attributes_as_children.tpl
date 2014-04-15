{*
	Oggetti correlati a partire da un elenco

	node			nodo di riferimento
	title			titolo del blocco
	oggetti_correlati	array di class_indentifier
	is_area_tematica	boolean (vero se si è in un'area tematica)
*}

{if $oggetti_correlati|count()|gt(0)}

	{def $has_content=false()}
	{def $style='col-odd'}

	{set-block variable=correlati}
		{def $BNode_id=module_params().parameters.NodeID
			 $local_link=fetch(content,node,hash(node_id,$BNode_id))}	 
			 
		{foreach $oggetti_correlati as $oggetto_correlato}
			{def $classi_attributi = wrap_user_func('getClassAttributes', array(array($oggetto_correlato)) )
				 $classe_attributo = false()}
			{foreach $classi_attributi as $ca}
				{if $ca.identifier|eq($node.object.class_identifier)}
					{set $classe_attributo = true() }
				{/if}
			{/foreach}
			{if $classe_attributo} 
				{def $res_fetch=fetch( 'content', 'related_objects', hash( 'object_id', $node.object.id, 'attribute_identifier', concat( $node.object.class_identifier,'/',$oggetto_correlato) ) ) }
			{else}
				{def $res_fetch = array()}
			{/if}

			{if $res_fetch|count()|gt(0)}

				<div class="border-body border-box box-violet box-allegati-content">
				<div class="border-ml"><div class="border-mr"><div class="border-mc">
				<div class="border-content">			
                    {foreach $res_fetch as $figlio}
                        {if $sezioni_per_tutti|contains($figlio.object.section_id)}
                            {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
                            <div class="{$style} col col-notitle float-break">
                        {else}
                            <div class="square-box-soft-gray">
                        {/if}
                        
                        <div class="col-content-design">
                        
                        {if $figlio.object.can_edit}
                            <form method="post" action={"content/action"|ezurl} class="left">
                            <input type="hidden" name="HasMainAssignment" value="1" />
                            <input type="hidden" name="ContentObjectID" value="{$figlio.object.id}" />
                            <input type="hidden" name="NodeID" value="{$figlio.node_id}" />
                            <input type="hidden" name="ContentNodeID" value="{$figlio.node_id}" />
                            <input type="hidden" name="ContentLanguageCode" value="ita-IT" />
                            <input type="hidden" name="ContentObjectLanguageCode" value="ita-IT" />
                            <input type="image" src={"websitetoolbar/ezwt-icon-edit.png"|ezimage} name="EditButton" title="{'Edit'|i18n( 'design/ezwebin/parts/website_toolbar')}: {$figlio.object.content_class.name|wash()}" />
                            <input type="image" src={"websitetoolbar/ezwt-icon-remove.png"|ezimage} name="ActionRemove" title="{'Remove'|i18n('design/ezwebin/parts/website_toolbar')}: {$figlio.object.content_class.name|wash()}" />
                            </form>
                        {/if}
                        {node_view_gui content_node=$figlio view='simplified_line'}
						</div>
						</div>
                    {/foreach}
				</div>
				</div></div></div>
				<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
				</div>

			{/if}
			{undef $res_fetch $classi_attributi $classe_attributo}
		{/foreach}
	{/set-block}


	{if $has_content}

		<div class="oggetti-correlati">
			<div class="border-header border-box box-violet-gray box-allegati-header">
				<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
				<div class="border-ml"><div class="border-mr"><div class="border-mc">
				<div class="border-content">
					<h2>{$title}</h2>
				</div>
				</div></div></div>
			</div>
			<div class="border-body border-box box-violet box-allegati-content">
				<div class="border-ml"><div class="border-mr"><div class="border-mc">
				<div class="border-content">
					{$correlati}
				</div>
				</div></div></div>
				<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
			</div>
		</div>
	{/if}
	{undef $has_content}


{/if}
