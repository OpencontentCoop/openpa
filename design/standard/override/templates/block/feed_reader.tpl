{cache-block expiry=300}

{set_defaults( hash('show_title', true()) )}

{def $source = $block.custom_attributes.source
     $limit = $block.custom_attributes.limit
     $offset = $block.custom_attributes.offset
     $res = feedreader( $source, $limit, $offset )}

{if and( $show_title, $block.name|ne('') )}
<div class="widget {$block.view}">

    <div class="widget_title">
        <h3>{$block.name|wash()}</h3>
    </div>
    <div class="widget_content">
{/if}
    <div class="block-type-feed-reader">
        <h2><a href="{$res.links[0]}" title="{$res.title|wash()}">{$res.title|wash()}</a></h2>

    <ul class="list-unstyled">
        {foreach $res.items as $item}
        <li>
            <a href="{$item.links[0]}" title="{$item.title|wash()}">{$item.title|wash()}</a>
        </li>
    {/foreach}
    </ul>

    </div>

{if and( $show_title, $block.name|ne('') )}
    </div>
</div>
{/if}
{/cache-block}
