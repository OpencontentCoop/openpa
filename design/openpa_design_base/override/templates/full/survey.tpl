{set-block scope=root variable=cache_ttl}0{/set-block}
{include name=menu_control node=$node uri='design:parts/common/menu_control.tpl'}

{if openpaini('GestioneClassi','nocache', array('questionario'))|contains($node.class_identifier)}
    {set-block scope=root variable=cache_ttl}0{/set-block}
{/if}

{def $attributi_da_evidenziare = openpaini( 'GestioneAttributi', 'attributi_da_evidenziare' )
$attributi_a_destra = openpaini( 'GestioneAttributi', 'attributi_a_destra' )
$classes_parent_to_edit = openpaini( 'GestioneClassi', 'classi_figlie_da_editare' )}

<div class="border-box">
    <div class="border-content">

        <div class="global-view-full content-view-full">
            <div class="class-{$node.object.class_identifier}">

                <h1>{attribute_view_gui attribute=$node.data_map.name}</h1>

                {* DATA e ULTIMAMODIFICA *}
                {include name = last_modified
                node = $node
                uri = 'design:parts/openpa/last_modified.tpl'}


                {* EDITOR TOOLS *}
                {include name = editor_tools
                node = $node
                uri = 'design:parts/openpa/editor_tools.tpl'}

                {* ATTRIBUTI : mostra i contenuti del nodo *}
                {include name = attributi_principali
                uri = 'design:parts/openpa/attributi_principali.tpl'
                node = $node}

                <div class="attributi-principali float-break col col-notitle">
                <div class="col-content"><div class="col-content-design">
                    {attribute_view_gui attribute=$node.data_map.survey}
                </div></div>
                </div>

            </div>
        </div>

    </div>
</div>
