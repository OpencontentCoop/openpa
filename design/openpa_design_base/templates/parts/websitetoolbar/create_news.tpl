{*
	TEMPLATE con strumenti per creare news
	classes_parent_to_edit	
*}

{if and( is_set( $servizio_utente )|not(), is_set( $servizio )|not() )}
    {def $current_user = fetch( 'user', 'current_user' )
         $servizio_utente = fetch( 'content', 'related_objects', hash( 'object_id', $current_user.contentobject_id,
                                                                        'attribute_identifier', openpaini( 'ControlloUtenti', 'user_servizio_attribute_ID', 0 ),'all_relations', false() ))
         $classi_con_servizi = wrap_user_func( 'getClassAttributes', array( array( 'servizio' ) ) )
         $parent_con_servizio = false()
         $classe_con_servizio = false()
         $servizio = false()}         
    {foreach $classi_con_servizi as $ccs}
        {if $ccs.identifier|eq( $node.parent.class_identifier )}
            {set $parent_con_servizio = true() }
        {/if}
        {if $ccs.identifier|eq( $node.class_identifier )}
            {set $classe_con_servizio = true() }
        {/if}
    {/foreach}
    
    {if $classes_parent_to_edit|contains( $node.class_identifier )}
        {if $parent_con_servizio}
            {set $servizio = fetch( 'content', 'related_objects',  hash( 'object_id', $node.parent.object.id, 
                                                                          'attribute_identifier', concat( $node.parent.class_identifier, '/servizio' ),
                                                                          'all_relations', false() ))}
        {/if}
    {else}
        {if $classe_con_servizio}
            {set $servizio = fetch( 'content', 'related_objects',  hash( 'object_id', $node.object.id, 
                                                                         'attribute_identifier', concat($node.class_identifier, '/servizio'),
                                                                         'all_relations', false() )) }
        {/if}
    {/if}
{/if}    

{def $servizi_array = array( 'null' )
     $servizio_utente_id = 'null'}

{if $node.class_identifier|eq( 'politico' )}
    {if count( $servizio_utente )|gt(0)}
    {foreach $servizio_utente as $su}
        {set $servizio_utente_id = $su.id}
        {break}
    {/foreach}
    {/if}
    
    {if count( $servizio )|gt(0)}
    {foreach $servizio as $s}
        {set $servizi_array = $servizi_array|append( $s.id )}
    {/foreach}
    {/if}
{/if}

{def $possible_classes = array('news')
	 $class_class = array()
	 $editable_children = array()
	 $editable_children_count = 0
}

{foreach $possible_classes as $possible_class}
    {set $class_class = fetch( 'content', 'class', hash( 'class_id', $possible_class ) )
         $editable_children = fetch(content, list, hash(parent_node_id, $node.node_id,
                                                    'class_filter_type', 'include',
                                                    'class_filter_array', array($class_class.identifier)))
         $editable_children_count = fetch(content, list_count, hash(parent_node_id, $node.node_id,
                                                    'class_filter_type', 'include',
                                                    'class_filter_array', array($class_class.identifier)))}    
    {if $editable_children_count|gt(0)}
		<div class="oggetti-correlati news">
			<div class="border-header border-box box-trans-blue box-allegati-header">
				<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
				<div class="border-ml"><div class="border-mr"><div class="border-mc">
				<div class="border-content">
					<h2>{$class_class.name}</h2>
				</div>
				</div></div></div>
			</div>
            
			<div class="border-box box-violet box-allegati-content">
            <div class="border-ml"><div class="border-mr"><div class="border-mc">
            <div class="border-content">                
                {foreach $editable_children as $figlio}
                   <div class="news">
                        <form method="post" action={"content/action"|ezurl} class="left">
                            <input type="hidden" name="HasMainAssignment" value="1" />
                            <input type="hidden" name="ContentObjectID" value="{$figlio.object.id}" />
                            <input type="hidden" name="NodeID" value="{$figlio.node_id}" />
                            <input type="hidden" name="ContentNodeID" value="{$figlio.node_id}" />
                            <input type="hidden" name="ContentLanguageCode" value="ita-IT" />
                            <input type="hidden" name="ContentObjectLanguageCode" value="ita-IT" />                            
                            {if and( $figlio.object.can_edit, $servizi_array|contains( $servizio_utente_id ) )}
                                <input type="image" src={"websitetoolbar/ezwt-icon-edit.png"|ezimage} name="EditButton" title="{'Edit'|i18n( 'design/ezwebin/parts/website_toolbar')}: {$figlio.object.content_class.name|wash()}" />
                            {/if}
                            {if and( $figlio.object.can_remove, $servizi_array|contains( $servizio_utente_id ) ) }
                                <input type="image" src={"websitetoolbar/ezwt-icon-remove.png"|ezimage} name="ActionRemove" title="{'Remove'|i18n('design/ezwebin/parts/website_toolbar')}: {$figlio.object.content_class.name|wash()}" />
                            {/if}
                        </form>
                    {node_view_gui view='included' content_node=$figlio}
                </div>
                {delimiter}<hr class="delimiter" />{/delimiter}
       		{/foreach}
			</div>
			</div></div></div>
			<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
			</div>
		</div>	
      {/if}
{/foreach}
{undef}
