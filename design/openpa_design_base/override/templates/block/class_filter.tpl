{def $customs=$block.custom_attributes 
	 $parent_node_id=ezini( 'NodeSettings', 'RootNode', 'content.ini' ) 
	 $filter_array=array() 
	 $class='' 
	 $sort_by=array('name', true())}

{if $customs[node_id]|gt(0)}
	{set $parent_node_id=$customs[node_id]}
	{def $parent_node=fetch(content,node,hash(node_id,$parent_node_id))}
{/if}

{if $customs[class]|ne('')}
	{set $class=$customs[class]}
{/if}

{if $class|ne('')}

    {def $nodes = fetch( content, tree, hash( parent_node_id, $parent_node_id,
                                                'sort_by', $sort_by, 'class_filter_type',
                                                'include', 'class_filter_array', array($class),
                                                'limit', 100))}

    {if $nodes|count()|gt(0)}	
    <form id="search-form-{$block.id}" action="{'redirect/redirect'|ezurl('no')}" method="post">
        <fieldset>
            <legend class="block-title"><span>{$block.name}</span></legend>
            <div class="border-box box-gray block-search">
            <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
            <div class="border-ml"><div class="border-mr"><div class="border-mc">
            <div class="border-content">     
                <select id="redirect-{$block.name}" name="node_id">
                {foreach $nodes as $n}
                    <option value="{$n.node_id}">{$n.name}</option>
                {/foreach}
                </select>
                <input value={$customs[attribute]} name="view" type="hidden" />
            
                <input id="search-button-{$block.id}" class="defaultbutton" type="submit" name="SearchButton" value="Cerca" />
                
            
            </div>
            </div></div></div>
            <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>					
            </div>
        </fieldset>
    </form>
	{/if}
{else}
	<h2>Attenzione: non hai specificato la classe nel blocco!</h2>
{/if}
