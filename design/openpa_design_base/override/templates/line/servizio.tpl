{*?template charset=utf-8?*}
{*
	TEMPLATE VIDE LINE
	mode	modalita' in cui visualizzare i link
*}
{def $attributes_to_show=array()
	 $attributes_structure=array('data_fine_validita','servizio','incarico','ufficio','struttura')
	 $attributes_with_title=array('telefoni','fax','email','email2','email_certificata')
}
{if is_set($mode)}
	{def $mode_link=$mode}
{else}
	{def $mode_link=''}
{/if}

{if is_set($show_image)}
	{def $show_icon_image=$show_image}
{else}
	{def $show_icon_image=''}
{/if}


 <div class="class-{$node.class_identifier} float-break">
	{if $show_icon_image|ne('nessuna')}
    <div class="main-image left">
		{if and( is_set( $node.data_map.image ), $node.data_map.image.has_content )}
			{attribute_view_gui attribute=$node.data_map.image image_class='small'}
		{else}
            {include node=$node uri='design:parts/common/class_icon.tpl' css_class="image-small"}
		{/if}
	</div>
    {/if}
	<div class="blocco-titolo-oggetto">    
 		<div class="titolo-blocco-titolo">
			{if $node.class_identifier|eq('link')}
        			<h3>
					<a href={$node.data_map.location.content|ezurl()} target="_blank" title="{$node.name|wash()}">
						{$node.name|wash()}
					</a>
				</h3>
			{else}
				{if is_set( $node.url_alias )}
					{if $mode_link|eq('virtual')}
         				<h3><a href={concat("/",$original_link,"/(node)/",$node.node_id)} title="{$node.name|wash()}">{$node.name|wash()}</a></h3>
					{else}
         				<h3><a href="{$node.url_alias|ezurl('no')}" title="{$node.name|wash()}">{$node.name|wash()}</a></h3>
					{/if}
				{else}
          				<h3>{$node.name|wash()}</h3>
				{/if}
			{/if}
		</div>

        {* mostro gli elementi della strtuttura *}	
        <div class="published">
            {foreach $node.data_map as $attribute}
                {if $attributes_structure|contains($attribute.contentclass_attribute_identifier)}
                    {if $attribute.has_content}
                    {if eq($attribute.data_type_string,'ezdate')}					
                        {*$attribute.content|attribute(show,2)*}
                        {if $attribute.content.is_valid}
                            {$attribute.contentclass_attribute_name}: 
                            {attribute_view_gui href=nolink attribute=$attribute}
                        {/if}
                    {else}
                        {$attribute.contentclass_attribute_name}: 
                        {attribute_view_gui href=nolink attribute=$attribute}
                    {/if}
                    {/if}
                {/if}
            {/foreach}
        </div>
    
        {* mostro abstract o oggetto *}
        {if $node.data_map.abstract.has_content}
            <div class="abstract-line">{attribute_view_gui attribute=$node.data_map.abstract}</div>
        {/if}
    
        {* mostro gli altri attributi *}
        {foreach $node.data_map as $attribute}
            {if $attributes_with_title|contains($attribute.contentclass_attribute_identifier)}
                {if $attribute.has_content}
                    <strong>{$attribute.contentclass_attribute_name}: </strong>
                    {attribute_view_gui attribute=$attribute}
                {/if}
            {/if}
            {if $attributes_to_show|contains($attribute.contentclass_attribute_identifier)}
                {attribute_view_gui attribute=$attribute}
            {/if}
        {/foreach}
	</div>
</div>
