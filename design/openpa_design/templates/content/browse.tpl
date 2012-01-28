{set-block scope=root variable=cache_ttl}0{/set-block}

{let browse_indentation=5
     page_limit=40
     browse_list_count=fetch(content,list_count,hash(parent_node_id,$node_id,depth,1))
     object_array=fetch(content,list,hash(parent_node_id,$node_id,depth,1,offset,$view_parameters.offset,limit,$page_limit,sort_by,$main_node.sort_array))
     bookmark_list=fetch('content','bookmarks',array())
     recent_list=fetch('content','recent',array())

     select_name='SelectedObjectIDArray'
     select_type='checkbox'
     select_attribute='contentobject_id'}

{section show=eq($browse.return_type,'NodeID')}
    {set select_name='SelectedNodeIDArray'}
    {set select_attribute='node_id'}
{/section}
{section show=eq($browse.selection,'single')}
    {set select_type='radio'}
{/section}

<form action={concat($browse.from_page)|ezurl} method="post">

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>

    <td width="80%" valign="top">

                {section show=$main_node.depth|gt(1)}
                    <a href={concat("/content/browse/",$main_node.parent_node_id,"/")|ezurl}>
                        <h3>[{'Up one level'|i18n('design/standard/content/browse')}]</h3>
                    </a>
                {/section}
{section show=$browse.description_template}
                {section show=$main_node.depth|gt(1)}
 		   {include name=Description uri=$browse.description_template browse=$browse main_node=$main_node}
                {/section}
{section-else}
    <div class="maincontentheader">
    <h1>{"Browse"|i18n("design/standard/content/browse")} - {$main_node.name|wash}</h1>
    </div>

    <p>{'To select objects, choose the appropriate radio button or checkbox(es), then click the "Select" button.'|i18n("design/standard/content/browse")}</p>



<h4>{'Quick search'|i18n( 'design/admin/content/edit' )}</h4>
        <table class="list" width="100%" border="0" cellspacing="0" cellpadding="0">
	<th>
		    <label>{'Search phrase'|i18n( 'design/admin/content/edit' )}</label>
	</th>
	<th>
			<label>Limita la ricerca a:</label>
	</th>
        <tr>
		<td>
		<div class="block">
		    <input id="search-string-{$main_node.node_id}" class="textfield" size=50 type="text" name="SearchStr" value="" />
		    <input name="SearchOffset" type="hidden" value="0" id="search-offset"  />
		    <input name="SearchLimit" type="hidden" value="20" id="search-limit" />
		</div>
		</td>
		<td>
		<div class="block subtree">
			<select name="SubTreeArray" id="search-subtree">
			{def $node_level = false()}
			{foreach $main_node.path_array as $level}
				{set $node_level=fetch(content,node,hash(node_id,$level))}
					<option value="{$node_level.node_id}" {if $main_node.node_id|eq($node_level.node_id)} selected="selected" {/if} title="{$node_level.name}">{$node_level.name}</option>
			{/foreach}
			</select>
		</div>
		</td>
        </tr>
	</table>
<div class="block">
    <input id="search-button-{$main_node.node_id}" class="button" type="button" name="SearchButton" value="{'Search'|i18n( 'design/admin/content/edit' )}" />
</div>
<div class="block search-results">
    <div id="search-results-{$main_node.node_id}" style="overflow: hidden">
</div>


{include uri='design:content/edit_menu.tpl'}

{ezscript_require( array( 'ezjsc::yui3', 'ezjsc::yui3io', 'browse-ezajaxsearch.js' ) )}

<script type="text/javascript">
eZAJAXSearch.cfg = {ldelim}
                        searchstring: '#search-string-{$main_node.node_id}',
                        searchlimit: '#search-limit',
                        searchoffset: '#search-offset',
                        searchsubtree: '#search-subtree',
                        searchbutton: '#search-button-{$main_node.node_id}',
                        searchresults: '#search-results-{$main_node.node_id}',
                        resulttemplate: '<div class="block"><input type={$select_type} value="{ldelim}{$select_attribute}{rdelim}" name="{$select_name}[]" /><div class="item-title">{ldelim}title{rdelim}</div><div class="item-published-date"> [{ldelim}class_name{rdelim}]</div><div class="link">Posizione: {ldelim}url_alias{rdelim}</div></div>'
                   {rdelim};
eZAJAXSearch.init();
</script>

<!-- SEARCH BOX: END -->

</div>
</div>



{/section}


        {* Browse listing start *}
        <table class="list" width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <th width="1">
                {if $browse.start_node|gt( 1 )}
                    <a href={concat( '/content/browse/', $main_node.parent_node_id, '/' )|ezurl}><img src={'back-button-16x16.gif'|ezimage} alt="{'Back'|i18n( 'design/ezwebin/content/browse' )}" /></a>
                {/if}    
            </th>
            <th width="69%">
            {"Name"|i18n("design/standard/content/browse")}
            </th>
            <th width="30%">
            {"Class"|i18n("design/standard/content/browse")}
            </th>
            <th width="30%">
            {"Section"|i18n("design/standard/content/browse")}
            </th>
        </tr>
        <tr>
            <td class="bglight">
            {section show=and( or( $browse.permission|not,
                                   cond( is_set( $browse.permission.contentclass_id ),
                                         fetch( content, access,
                                                hash( access, $browse.permission.access,
                                                      contentobject, $main_node,
                                                      contentclass_id, $browse.permission.contentclass_id ) ),
                                         fetch( content, access,
                                                hash( access, $browse.permission.access,
                                                      contentobject, $main_node ) ) ) ),
                               $browse.ignore_nodes_select|contains( $main_node.node_id )|not() )}
	      {section show=is_array($browse.class_array)}
	        {section show=$browse.class_array|contains($main_node.object.content_class.identifier)}
		  <input type="{$select_type}" name="{$select_name}[]" value="{$main_node[$select_attribute]}" {section show=eq($browse.selection,'single')}checked="checked"{/section} />
		{section-else}
		    &nbsp;
		{/section}
	      {section-else}
	        <input type="{$select_type}" name="{$select_name}[]" value="{$main_node[$select_attribute]}" {section show=eq($browse.selection,'single')}checked="checked"{/section} />
	      {/section}
	    {section-else}
	        &nbsp;
            {/section}
            </td>

            <td class="bglight">

		{if $main_node.is_container }
                                        <h3><a href="{concat('content/browse/',$main_node.node_id)|ezurl('no')}" title="{$main_node.name|wash()}">{$main_node.name|wash()}</a></h3>
                                {else}
                                        {$main_node.name|wash()}
                {/if}
            </td>

            <td class="bglight">
            {$main_node.object.content_class.name|wash}
            </td>

            <td class="bglight">
            {$main_node.object.section_id}
            </td>
        </tr>
        {section name=Object loop=$object_array sequence=array(bgdark,bglight)}
        <tr class="{$Object:sequence}">
            <td>
            {section show=and( or( $browse.permission|not,
                                   cond( is_set( $browse.permission.contentclass_id ),
                                         fetch( content, access,
                                                hash( access, $browse.permission.access,
                                                      contentobject, $:item,
                                                      contentclass_id, $browse.permission.contentclass_id ) ),
                                         fetch( content, access,
                                                hash( access, $browse.permission.access,
                                                      contentobject, $:item ) ) ) ),
                               $browse.ignore_nodes_select|contains($:item.node_id)|not() )}
              {section show=is_array($browse.class_array)}
                {section show=$browse.class_array|contains($:item.object.content_class.identifier)}
                  <input type="{$select_type}" name="{$select_name}[]" value="{$:item[$select_attribute]}" />
                {section-else}
                  &nbsp;
                {/section}
              {section-else}
                <input type="{$select_type}" name="{$select_name}[]" value="{$:item[$select_attribute]}" />
              {/section}
            {/section}
            </td>

            <td>
                {*<img src={"1x1.gif"|ezimage} width="{mul(sub($:item.depth,$main_node.depth),$browse_indentation)}" height="1" alt="" border="0" />*}

                 {node_view_gui view=browse_line content_node=$Object:item node_url=cond( $browse.ignore_nodes_click|contains($Object:item.node_id)|not(), concat( 'content/browse/', $Object:item.node_id, '/' ), false() )}
            </td>

            <td>
                    {$Object:item.object.content_class.name|wash}
            </td>

            <td>
                    {$:item.object.section_id}
            </td>
        </tr>
        {/section}
        </table>
        {* Browse listing end *}

    </td>

    <td width="20">
    </td>

    <td width="200" valign="top">

        {* Recent and bookmark start *}
        <table class="list" width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <th colspan="2">
                {"I tuoi preferiti"|i18n("design/standard/content/browse")}
            </th>
        </tr>

        {section name=Bookmark loop=$bookmark_list show=$bookmark_list sequence=array(bgdark,bglight)}
        <tr class="{$:sequence}">
            <td width="1">
            {section show=and( or( $browse.permission|not,
                                   cond( is_set( $browse.permission.contentclass_id ),
                                         fetch( content, access,
                                                hash( access, $browse.permission.access,
                                                      contentobject, $:item.node,
                                                      contentclass_id, $browse.permission.contentclass_id ) ),
                                         fetch( content, access,
                                                hash( access, $browse.permission.access,
                                                      contentobject, $:item.node ) ) ) ),
                               $browse.ignore_nodes_select|contains( $:item.node_id )|not() )}
              {section show=is_array($browse.class_array)}
                {section show=$browse.class_array|contains($:item.object.content_class.identifier)}
                  <input type="{$select_type}" name="{$select_name}[]" value="{$:item.node[$select_attribute]}" />
                {section-else}
                  &nbsp;
                {/section}
              {section-else}
                <input type="{$select_type}" name="{$select_name}[]" value="{$:item.node[$select_attribute]}" />
              {/section}
            {section-else}
              &nbsp;
            {/section}
            </td>

            <td>
                {node_view_gui view=browse_line content_node=$:item.node
                               node_url=cond( eq( $:item.node_id, $main_node.node_id ), false(),
                                              $browse.ignore_nodes_click|contains( $:item.node_id )|not(), concat( 'content/browse/', $:item.node_id, '/' ), false() )}
            </td>
        </tr>
        {section-else}
        <tr>
            <td colspan="2">
                {'Bookmark items are managed using %bookmarkname in the %personalname part.'
                 |i18n('design/standard/content/browse',,
                       hash('%bookmarkname',concat('<i>','My bookmarks'|i18n('design/standard/content/browse'),'</i>'),
                            '%personalname',concat('<i>','Personal'|i18n('design/standard/content/browse'),'</i>')))}
            </td>
        </tr>
        {/section}

        <tr height="6">
            <td>
            </td>
        </tr>

        <tr>
            <th colspan="2">
                {"Recent items"|i18n("design/standard/content/browse")}
            </th>
        </tr>

        {section show=$recent_list}
            {section name=Recent loop=$recent_list sequence=array(bgdark,bglight)}
            <tr class="{$:sequence}">
                <td width="1">
                {section show=and( or( $browse.permission|not,
                                       cond( is_set( $browse.permission.contentclass_id ),
                                             fetch( content, access,
                                                    hash( access, $browse.permission.access,
                                                          contentobject, $:item.node,
                                                          contentclass_id, $browse.permission.contentclass_id ) ),
                                             fetch( content, access,
                                                    hash( access, $browse.permission.access,
                                                          contentobject, $:item.node ) ) ) ),
                                   $browse.ignore_nodes_select|contains( $:item.node_id )|not() )}
                  {section show=is_array($browse.class_array)|not()}
                    <input type="{$select_type}" name="{$select_name}[]" value="{$:item[$select_attribute]}" />
                  {section-else}
                    &nbsp;
                  {/section}
                {section-else}
                  &nbsp;
                {/section}
                </td>

                <td>
                {node_view_gui view=browse_line content_node=$:item.node
                               node_url=cond( eq( $:item.node_id, $main_node.node_id ), false(),
                                              $browse.ignore_nodes_click|contains( $:item.node_id )|not(), concat( 'content/browse/', $:item.node_id, '/' ), false() )}
                </td>
            </tr>
            {/section}
        {section-else}
        <tr>
            <td colspan="2">
                {'Recent items are added on publishing.'|i18n('design/standard/content/browse')}
            </td>
        </tr>
        {/section}

        </table>
        {* Recent and bookmark end *}

    </td>

</tr>
</table>

{include name=Navigator
         uri='design:navigator/google.tpl'
         page_uri=concat('/content/browse/',$main_node.node_id)
         item_count=$browse_list_count
         view_parameters=$view_parameters
         item_limit=$page_limit}


{section name=Persistent show=$browse.persistent_data loop=$browse.persistent_data}
    <input type="hidden" name="{$:key|wash}" value="{$:item|wash}" />
{/section}

<input type="hidden" name="BrowseActionName" value="{$browse.action_name}" />
{section show=$browse.browse_custom_action}
<input type="hidden" name="{$browse.browse_custom_action.name}" value="{$browse.browse_custom_action.value}" />
{/section}

        <input class="button" type="submit" name="SelectButton" value="{'Select'|i18n('design/standard/content/browse')}" />
 	<input class="button" type="submit" name="BrowseCancelButton" value="{'Cancel'|i18n( 'design/standard/content/browse' )}" />

{section show=$cancel_action}
<input type="hidden" name="BrowseCancelURI" value="{$cancel_action}" />
{/section}
</form>

{/let}
