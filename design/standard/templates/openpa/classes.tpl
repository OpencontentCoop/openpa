<div class="global-view-full">

{if is_set( $class )}
    <h1><a href={'openpa/classes'|ezurl()}>Classi di contenuto</a> &raquo; {$class.name}</h1>

    <table width="100%" cellspacing="0" cellpadding="0" border="0" class="list">
        <thead>
        <tr>
            <th style="vertical-align: middle">Attributo</th>
            <th style="vertical-align: middle">Descrizione</th>
            <th style="vertical-align: middle">Tipo di dato</th>
            <th style="vertical-align: middle">Obbligatorio</th>
            <th style="vertical-align: middle">Ricercabile</th>
        </tr>
        </thead>
        <tbody>
        {foreach $class.data_map as $attribute sequence array(bglight,bgdark) as $style}
            <tr id="{$attribute.identifier}" class="class {$style}">
                <td style="vertical-align: middle">
                    {$attribute.name} ({$attribute.identifier})
                </td>
                <td>{$attribute.description}</td>
                <td>{$attribute.data_type.information.name} ({$attribute.data_type_string})</td>
                <td style="text-align: center">{if $attribute.is_required}X{/if}</td>
                <td style="text-align: center">{if $attribute.is_searchable}X{/if}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>

{else}
    <h1>Classi di contenuto {if is_set( $datatype )} con attributi di tipo {$datatype}{/if}</h1>
    {if is_set( $class_list )|not()}
      {def $class_list = fetch( 'class', 'list', hash( 'sort_by', array( 'name', true() ) ) )}
    {/if}
    <table width="100%" cellspacing="0" cellpadding="0" border="0" class="list">
        <thead>
            <tr>
                <th style="vertical-align: middle">Classe</th>
                {if is_set( $datatype )}
                  <th style="vertical-align: middle">Attributo</th>
                {/if}
                <th style="vertical-align: middle">Descrizione</th>                
                <th style="vertical-align: middle">Oggetti</th>
                <th style="vertical-align: middle">Relazioni</th>
                <th style="vertical-align: middle">JSON</th>
            </tr>
        </thead>
        <tbody>
            {foreach $class_list as $class sequence array(bglight,bgdark) as $style}
            <tr id="{$class.identifier}" class="class {$style}">
                <td {if is_set( $datatype )}style="vertical-align: top"{/if}>
                    <h3><a href={concat('/openpa/classes/',$class.identifier)|ezurl()}>
                        {$class.name} ({$class.identifier})
                    </a></h3>
                </td>
                {if is_set( $datatype )}
                <td>
                    <table class="list">
                        <thead>
                            <tr class="class {$style}">
                                <td style="font-weight:bold;vertical-align: middle">Attributo</td>
                                <td style="font-weight:bold;vertical-align: middle">Obbligatorio</td>
                                <td style="font-weight:bold;vertical-align: middle">Ricercabile</td>
                                <td style="font-weight:bold;vertical-align: middle">Gruppo</td>
                                <td style="font-weight:bold;vertical-align: middle"></td>
                            </tr>
                        </thead>
                        <tbody>
                        {foreach $class.data_map as $identifier => $attribute}
                            {if $attribute.data_type_string|eq($datatype)}
                              <tr class="class {$style}">
                                  <td style="width:80px;text-align: center;border-bottom:1px solid #ccc">{$attribute.name} ({$attribute.identifier})<br /></td>
                                  <td style="width:20px;text-align: center;border-bottom:1px solid #ccc">{if $attribute.is_required}X{/if}</td>
                                  <td style="width:20px;text-align: center;border-bottom:1px solid #ccc">{if $attribute.is_searchable}X{/if}</td>
                                  <td style="width:80px;text-align: center;border-bottom:1px solid #ccc">{if $attribute.category|eq('')}Valore predefinito{else}{$attribute.category}{/if}</td>
                                  <td style="width:200px;border-bottom:1px solid #ccc">{class_attribute_view_gui class_attribute=$attribute}</td>
                              </tr>
                            {/if}
                        {/foreach}
                        </tbody>
                    </table>
                </td>
                {/if}                
                <td>{$class.description}</td>
                <td>
                  {if ezmodule( 'classlists' )}
                      <a href={concat( 'classlists/list/', $class.identifier )|ezurl()}>{$class.object_count}</a>
                  {else}
                      {$class.object_count}
                  {/if}
                </td>
                <td style="text-align: center">
                    <a href={concat('/openpa/relations/',$class.identifier)|ezurl()}>
                        <img src={'websitetoolbar/ezwt-icon-locations.png'|ezimage()} />
                    </a>
                </td>
                <td style="text-align: center">
                    <a href={concat('/openpa/classdefinition/',$class.identifier)|ezurl()}>
                        JSON
                    </a>
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>
    {/if}

</div>