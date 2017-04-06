<dl class="dl-horizontal">

    <dt>Ultima modifica di:</dt>
    <dd>
        {if is_set($node.creator.main_node)}
            <a href={$node.creator.main_node.url_alias|ezurl}>{$node.creator.name}</a> il {$node.object.modified|l10n(shortdatetime)}
        {else}
            ?
        {/if}
    </dd>

    <dt>Creato da:</dt>
    <dd>
        {if and( $node.object.owner, $node.object.owner.main_node )}
            <a href={$node.object.owner.main_node.url_alias|ezurl}>{$node.object.owner.name}</a> il {$node.object.published|l10n(shortdatetime)}
        {else}
            ?
        {/if}
    </dd>

    <dt>Nodo:</dt>
    <dd>{$node.node_id}</dd>

    <dt>Oggetto</dt>
    <dd>{$node.contentobject_id} ({$node.object.remote_id})</dd>

    <dt>Collocazioni:</dt>
    <dd>
        <ul class="list-unstyled">
            {foreach $node.object.assigned_nodes as $item}
                <li><a href={$item.url_alias|ezurl()}>{$item.path_with_names}</a> {if $item.node_id|eq($node.object.main_node_id)}(principale){/if}</li>
            {/foreach}
        </ul>
    </dd>

    {def $sezione = fetch( 'section', 'object', hash( 'section_id', $node.object.section_id ))}
    <dt>Sezione: </dt>
    <dd>
        {*if $node.can_edit}
          <form action={concat('content/edit/', $node.contentobject_id)|ezurl()} method="post">
          {def $sections=$node.object.allowed_assign_section_list $currentSectionName='unknown'}
          {foreach $sections as $sectionItem }
              {if eq( $sectionItem.id, $node.object.section_id )}
                  {set $currentSectionName=$sectionItem.name}
              {/if}
          {/foreach}
          {undef $currentSectionName}
          <select id="SelectedSectionId" name="SelectedSectionId">
          {foreach $sections as $section}
              {if eq( $section.id, $node.object.section_id )}
              <option value="{$section.id}" selected="selected">{$section.name|wash}</option>
              {else}
              <option value="{$section.id}">{$section.name|wash}</option>
              {/if}
          {/foreach}
          </select>
          <input type="submit" value="{'Set'|i18n( 'design/admin/node/view/full' )}" name="SectionEditButton" class="button btn btn-xs" />
          <input type="hidden" value="{$node.url_alias}" name="RedirectRelativeURI">
          <input type="hidden" value="1" name="ChangeSectionOnly">
          </form>
        {else*}
        {$sezione.name|wash}
        {*/if*}
    </dd>

    <dt>Tipo: </dt>
    <dd><a target="_blank" href="{concat('openpa/classes/', $node.class_identifier)|ezurl(no)}">{$node.class_name} ({$node.class_identifier} {$node.object.contentclass_id})</a></dd>

    {if $openpa.content_virtual.folder}
        <dt>Folder virtuale:</dt>
        <dd>
            {$openpa.content_virtual.folder.classes|implode(', ')}
            ({foreach $openpa.content_virtual.folder.subtree as $node_id}<a href="{concat( 'content/view/full/', $node_id)|ezurl(no)}">{$node_id}</a>{delimiter}, {/delimiter}{/foreach})
        </dd>
    {/if}

    {if and( is_set( $openpa.content_albotelematico ), $openpa.content_albotelematico.is_container )}
        <dt>Albo telematico:</dt>
        <dd>Pagina configurata come contenitore di documenti Albo telematico <small>(vengono visualizzati i contenuti figli della collocazione principale)</small></dd>
    {/if}

    {if $openpa.content_virtual.calendar}
        <dt>Calendario virtuale:</dt>
        <dd>
            ({foreach $openpa.content_virtual.calendar.subtree as $node_id}<a href="{concat( 'content/view/full/', $node_id)|ezurl(no)}">{$node_id}</a>{delimiter}, {/delimiter}{/foreach})
        </dd>
    {/if}

    {if and( is_set( $node.data_map.data_iniziopubblicazione ), $node.data_map.data_iniziopubblicazione.has_content, $node.data_map.data_iniziopubblicazione.content.timestamp|gt(0) )}
        <dt>{$node.data_map.data_iniziopubblicazione.contentclass_attribute_name}</dt>
        <dd>{attribute_view_gui attribute=$node.data_map.data_iniziopubblicazione}</dd>
    {/if}

    {if and( is_set( $node.data_map.data_finepubblicazione ), $node.data_map.data_finepubblicazione.has_content, $node.data_map.data_finepubblicazione.content.timestamp|gt(0) )}
        <dt>{$node.data_map.data_finepubblicazione.contentclass_attribute_name}</dt>
        <dd>{attribute_view_gui attribute=$node.data_map.data_finepubblicazione}</dd>
    {/if}

    {if and( is_set( $node.data_map.data_archiviazione ), $node.data_map.data_archiviazione.has_content, $node.data_map.data_archiviazione.content.timestamp|gt(0) )}
        <dt>{$node.data_map.data_archiviazione.contentclass_attribute_name}</dt>
        <dd>{attribute_view_gui attribute=$node.data_map.data_archiviazione}</dd>
    {/if}

    {def $states = $node.object.allowed_assign_state_list}
    {if $states|count}
        <dt>Stati:</dt>
        <dd>
            {*if $node.can_edit}
              {def $enable_StateEditButton = false()}
              {foreach $node.object.allowed_assign_state_list as $allowed_assign_state_info}
              <div class="block">
                  <label for="SelectedStateIDList">{$allowed_assign_state_info.group.current_translation.name|wash}</label>
                  <select id="SelectedStateIDList" name="SelectedStateIDList[]" {if $allowed_assign_state_info.states|count|eq(1)}disabled="disabled"{/if}>
                  {if $allowed_assign_state_info.states}
                      {set $enable_StateEditButton = true()}
                  {/if}
                  {foreach $allowed_assign_state_info.states as $state}
                      <option value="{$state.id}" {if $node.object.state_id_array|contains($state.id)}selected="selected"{/if}>{$state.current_translation.name|wash}</option>
                  {/foreach}
                  </select>
              </div>
              {/foreach}
              {if $enable_StateEditButton}
                  <input type="submit" value="{'Set'|i18n( 'design/admin/node/view/full' )}" name="StateEditButton" class="button btn btn-xs" />
              {/if}
            {else*}
            {foreach $states as $allowed_assign_state_info}{foreach $allowed_assign_state_info.states as $state}{if $node.object.state_id_array|contains($state.id)}{$allowed_assign_state_info.group.current_translation.name|wash()}/{$state.current_translation.name|wash}{/if}{/foreach}{delimiter}, {/delimiter}{/foreach}
            {*/if*}
        </dd>
    {/if}

    {if $node.object.can_translate}
        <dt>Traduzioni:</dt>
        <dd>
            <ul class="list-inline">
                {foreach $node.object.languages as $language}
                    {if $node.object.available_languages|contains($language.locale)}
                        <li>
                            <a href="{concat( $node.url_alias, '/(language)/', $language.locale )|ezurl(no)}">
                                {if $language.locale|eq($node.object.current_language)}<strong>{/if}
                                    <small>{$language.name|wash()}</small>
                                    {if $language.locale|eq($node.object.current_language)}</strong>{/if}
                            </a>
                        </li>
                    {/if}
                {/foreach}
                <li>
                    {def $can_create_languages = $node.object.can_create_languages
                    $languages = fetch( 'content', 'prioritized_languages' )}
                    <form method="post" action={"content/action"|ezurl}>
                        <input type="hidden" name="HasMainAssignment" value="1"/>
                        <input type="hidden" name="ContentObjectID" value="{$node.object.id}"/>
                        <input type="hidden" name="NodeID" value="{$node.node_id}"/>
                        <input type="hidden" name="ContentNodeID" value="{$node.node_id}"/>
                        <input type="hidden" name="ContentObjectLanguageCode" value="" />
                        <input type="submit" name="EditButton" class="button btn-xs btn-default" value="Modifica/inserisci traduzione"/>
                    </form>
                </li>
            </ul>
        </dd>
    {/if}

    {if $openpa.content_globalinfo.has_content}
        <dt>Global info</dt>
        <dd>
            {if $openpa.content_globalinfo.object.parent_node_id|ne( $node.node_id )}
                <small>
                    Ereditato da <a href={$openpa.content_globalinfo.object.parent.url_alias|ezurl()}>{$openpa.content_globalinfo.object.parent.name|wash()}</a>
                </small>
                {if fetch( 'content', 'access', hash( 'access', 'create', 'contentclass_id', 'global_layout', 'contentobject', $node ) )}
                    <form method="post" action="{"content/action"|ezurl(no)}" class="form inline" style="display:inline">
                        <input type="hidden" name="HasMainAssignment" value="1"/>
                        <input type="hidden" name="ContentObjectID" value="{$node.object.id}"/>
                        <input type="hidden" name="NodeID" value="{$node.node_id}"/>
                        <input type="hidden" name="ContentNodeID" value="{$node.node_id}"/>
                        <input type="hidden" name="ContentLanguageCode" value="ita-IT"/>
                        <input type="hidden" name="ContentObjectLanguageCode" value="ita-IT"/>
                        <input type="hidden" value="global_layout" name="ClassIdentifier"/>
                        <input type="submit" class="btn btn-xs btn-default" value="Crea un box dedicato" name="NewButton"/>
                        <input type="hidden" name="RedirectIfDiscarded" value="{$node.url_alias}"/>
                        <input type="hidden" name="RedirectURIAfterPublish" value="{$node.url_alias}"/>
                    </form>
                {/if}
            {/if}
            <form action="{"/content/action"|ezurl(no)}" method="post" class="form inline" style="display:inline">
                {if $openpa.content_globalinfo.object.object.can_edit}
                    <input type="submit" name="EditButton" value="Modifica box" class="btn btn-xs btn-default" title="Modifica {$openpa.content_globalinfo.object.name|wash()}"/>
                    <input type="hidden" name="ContentObjectLanguageCode" value="{$openpa.content_globalinfo.object.object.current_language}"/>
                {/if}
                {if $openpa.content_globalinfo.object.object.can_remove}
                    <input type="submit" class="btn btn-xs btn-default" name="ActionRemove" value="Elimina box" alt="Elimina {$openpa.content_globalinfo.object.name|wash()}" title="Elimina {$openpa.content_globalinfo.object.name|wash()}"/>
                {/if}
                <input type="hidden" name="ContentObjectID" value="{$openpa.content_globalinfo.object.object.id}"/>
                <input type="hidden" name="NodeID" value="{$openpa.content_globalinfo.object.node_id}"/>
                <input type="hidden" name="ContentNodeID" value="{$openpa.content_globalinfo.object.node_id}"/>
                <input type="hidden" name="RedirectIfDiscarded" value="{$node.url_alias}"/>
                <input type="hidden" name="RedirectURIAfterPublish" value="{$node.url_alias}"/>
            </form>
        </dd>
    {elseif and( $openpa.content_globalinfo.has_content|not(), $node.can_create)}
        <dt>Global info</dt>
        <dd>
            <form method="post" action="{"content/action"|ezurl(no)}" class="form inline" style="display:inline">
                <input type="hidden" name="HasMainAssignment" value="1"/>
                <input type="hidden" name="ContentObjectID" value="{$node.object.id}"/>
                <input type="hidden" name="NodeID" value="{$node.node_id}"/>
                <input type="hidden" name="ContentNodeID" value="{$node.node_id}"/>
                <input type="hidden" name="ContentLanguageCode" value="ita-IT"/>
                <input type="hidden" name="ContentObjectLanguageCode" value="ita-IT"/>
                <input type="hidden" value="global_layout" name="ClassIdentifier"/>
                <input type="submit" class="btn btn-xs btn-default" value="Crea un box dedicato" name="NewButton"/>
            </form>
        </dd>
    {/if}

    {* NEWSLETTER *}
    {if ezmodule('newsletter','subscribe')}
        {def $newsletter_edition_hash = newsletter_edition_hash()}
        {if and( $node|can_add_to_newsletter(), $newsletter_edition_hash|count()|gt(0) )}
            <dt>Newsletter</dt>
            <dd>
            <form action={concat("/openpa/addlocationto/",$node.contentobject_id)|ezurl} method="post" class="form-inline" style="display:inline">

                <label for="add_to_newsletter" >Aggiungi alla prossima newsletter:</legend>
                    <select name="SelectedNodeIDArray[]" id="add_to_newsletter" class="form-control">
                        {foreach $newsletter_edition_hash as $edition_id => $edition_name}
                            <option value="{$edition_id}">{$edition_name|wash()}</option>
                        {/foreach}
                    </select>
                    <input class="btn btn-xs btn-default" type="submit" name="AddLocation" value="Aggiungi" />
            </form>
        {/if}
        </dd>
        {undef $newsletter_edition_hash}
    {/if}

</dl>

<hr />
<p>
    <a class="btn btn-sm btn-info" href="{concat('index/object/',$node.contentobject_id)|ezurl(no)}">Controlla indicizzazione contenuto</a>
    <a class="btn btn-sm btn-info" href="{concat('content/history/',$node.contentobject_id)|ezurl(no)}">Gestisci versioni</a>
    {if $node.class_identifier|eq('organigramma')}
        <a class="btn btn-sm btn-danger" href="{concat('openpa/refreshorganigramma/',$node.contentobject_id)|ezurl(no)}">Aggiorna {$node.class_name}</a>
    {/if}
    {if fetch( 'user', 'has_access_to', hash( 'module', 'classtools', 'function', 'class' ) )}
        <a class="btn btn-sm btn-info" href="{concat('classtools/extra/',$node.class_identifier)|ezurl(no)}">Impostazioni visualizzazione oggetti {$node.class_name}</a>
    {/if}
</p>