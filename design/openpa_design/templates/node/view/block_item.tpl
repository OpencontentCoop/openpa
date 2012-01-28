{def $has_image = false()
     $has_text = false()}

{if is_set( $shorten )|not()}
    {def $shorten = false()}
{/if}

{if is_set( $title_shorten )|not()}
    {def $title_shorten = 200}
{/if}

{if is_set( $image_class )|not()}
    {def $image_class = false()}
{/if}

{if is_set( $image_class )}
    {if is_set( $node.data_map.image )}
        {if $node.data_map.image.has_content}
            {set $has_image = $node.data_map.image}
        {/if}
    {/if}
    {if and( is_set( $node.data_map.file ), $has_image|not() )}
        {if $node.data_map.file.has_content}
            {if $node.object.data_map.file.content.mime_type|eq('application/pdf')}
                {set $has_image = $node.object.data_map.file.content.filepath|pdfpreview( 100, 100, 1, $node.name|slugize() )|ezroot}
            {/if}
        {/if}
    {/if}
{/if}
     
<div class="float-break content_view-block{if $has_image} image-padding{/if}">

    {if $has_image}
        {if is_set( $has_image.content )}
            <div class="item-image">
                <div class="image text-center">
                    {attribute_view_gui attribute=$has_image image_class=$image_class}
                </div>
            </div>
        {else}
            <div class="item-image">
                <div class="image text-center">
                    <img src={$has_image} alt="{$node.name|wash()}">
                </div>
            </div>
        {/if}
    {/if}
    
    <div class="item-content"><div class="item-content-design">
        <div class="class-{$node.object.class_identifier}">
            
            {def $highlights = openpaini( 'Blocchi', 'ListaClassHighlight', array() )}

            <span class="item-meta {$node.class_identifier}{if $highlights|contains( $node.class_identifier )} highlight{/if}">
                {if and( is_set( $node.data_map.ufficio ), $node.data_map.ufficio.has_content )}
                    <span class="item-rif">{attribute_view_gui attribute=$node.data_map.ufficio}</span>
                {elseif and( is_set( $node.data_map.servizio ), $node.data_map.servizio.has_content )}
                    <span class="item-rif">{attribute_view_gui attribute=$node.data_map.servizio}</span>
                {/if}
                <span class="item-class">{$node.object.class_name}</span>
            </span>
            
            <div class='item-date'>
                {include name=date node=$node uri='design:parts/common/date.tpl'}
            </div>
            
            {if is_set( $node.url_alias )}
                <h3><a href="{$node.url_alias|ezurl('no')}" title="{$node.name|wash()}">{$node.name|shorten( $title_shorten )|wash()}</a></h3>
            {else}
                <h3>{$node.name|wash()}</h3>
            {/if}
            
            {if $shorten}
                
                {if is_set($node.data_map.intro)}
                    {if $node.data_map.intro.has_content}
                        {attribute_view_gui attribute=$node.data_map.intro}
                        {set $has_text = true()}
                    {/if}
                {/if}
                
                {if and( $has_text|not(), is_set($node.data_map.short_description) )}
                    {if $node.data_map.short_description.has_content}
                        {attribute_view_gui attribute=$node.data_map.short_description}
                        {set $has_text = true()}
                    {/if}
                {/if}
                
                {if and( $has_text|not(), is_set($node.data_map.abstract) )}
                    {if $node.data_map.abstract.has_content}
                        {attribute_view_gui attribute=$node.data_map.abstract}
                        {set $has_text = true()}
                    {/if}
                {/if}
                
                {if and( $has_text|not(), is_set($node.data_map.body) )}
                    {if $node.data_map.body.has_content}
                        <p class="item-text">{attribute_view_gui attribute=$node.data_map.body shorten=$shorten}</p>
                        {set $has_text = true()}
                    {/if}
                {/if}
    
                {if and( $has_text|not(), is_set($node.data_map.description) )}
                    {if $node.data_map.description.has_content}
                        <p class="item-text">{attribute_view_gui attribute=$node.data_map.description shorten=$shorten}</p>
                        {set $has_text = true()}
                    {/if}
                {/if}
            
                <div class='item-link'><a href="{$node.url_alias|ezurl('no')}" title="{$node.name|wash()}">Leggi tutto &raquo;</a></div>
            {/if}
                
        </div>
    </div></div>
</div>
{undef}