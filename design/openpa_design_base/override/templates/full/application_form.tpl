{* Feedback form - Full view *}
{include name=menu_control node=$node uri='design:parts/common/menu_control.tpl'}

<div class="border-box">
<div class="global-view-full content-view-full">
    <div class="class-feedback-form">

    <h1>{$node.name|wash()}</h1>

    {* EDITOR TOOLS *}
	{include name = editor_tools
             node = $node             
             uri = 'design:parts/openpa/editor_tools.tpl'}

	{* ATTRIBUTI : mostra i contenuti del nodo *}
    {include name = attributi_principali
             uri = 'design:parts/openpa/attributi_principali.tpl'
             node = $node}
 
    {include name=Validation uri='design:content/collectedinfo_validation.tpl'
            class='message-warning'
            validation=$validation collection_attributes=$collection_attributes}

    <form method="post" action={"content/action"|ezurl}>

	<h4>{$node.data_map.subject.contentclass_attribute.name}</h4>
        <div class="attribute-subject">
                {attribute_view_gui attribute=$node.data_map.subject}
        </div>

	<h4>{$node.data_map.anno.contentclass_attribute.name}</h4>
        <div class="attribute-anno">
                {attribute_view_gui attribute=$node.data_map.anno}
        </div>

	<h4>{$node.data_map.categoria.contentclass_attribute.name}</h4>
        <div class="attribute-categoria">
                {attribute_view_gui attribute=$node.data_map.categoria}
        </div>

	<h4>{$node.data_map.istituzione.contentclass_attribute.name}</h4>
        <div class="attribute-istituzione">
                {attribute_view_gui attribute=$node.data_map.istituzione}
        </div>

	<h4>{$node.data_map.indirizzo.contentclass_attribute.name}</h4>
        <div class="attribute-indirizzo">
                {attribute_view_gui attribute=$node.data_map.indirizzo}
        </div>

	<h4>{$node.data_map.citta.contentclass_attribute.name}</h4>
        <div class="attribute-citta">
                {attribute_view_gui attribute=$node.data_map.citta}
        </div>

	<h4>{$node.data_map.partita_iva.contentclass_attribute.name}</h4>
        <div class="attribute-partita_iva">
                {attribute_view_gui attribute=$node.data_map.partita_iva}
        </div>

	<h4>{$node.data_map.codice_fiscale.contentclass_attribute.name}</h4>
        <div class="attribute-codice_fiscale">
                {attribute_view_gui attribute=$node.data_map.codice_fiscale}
        </div>

	<h4>{$node.data_map.sito_web.contentclass_attribute.name}</h4>
        <div class="attribute-sito_web">
                {attribute_view_gui attribute=$node.data_map.sito_web}
        </div>

    <h4>{$node.data_map.sender_name.contentclass_attribute.name}</h4>
        <div class="attribute-sender-name">
                {attribute_view_gui attribute=$node.data_map.sender_name}
        </div>

    <h4>{$node.data_map.email.contentclass_attribute.name}</h4>
        <div class="attribute-email">
                {attribute_view_gui attribute=$node.data_map.email}
        </div>

    <h4>{$node.data_map.telefono.contentclass_attribute.name}</h4>
        <div class="attribute-telefono">
                {attribute_view_gui attribute=$node.data_map.telefono}
        </div>

    <h4>{$node.data_map.cellulare.contentclass_attribute.name}</h4>
        <div class="attribute-cellulare">
                {attribute_view_gui attribute=$node.data_map.cellulare}
        </div>
      

    <h4>{$node.data_map.message.contentclass_attribute.name}</h4>
        <div class="attribute-message">
                {attribute_view_gui attribute=$node.data_map.message}
        </div>


    <h4>{$node.data_map.description.contentclass_attribute.name}</h4>
        <div class="attribute-description">
                {attribute_view_gui attribute=$node.data_map.description}
        </div>

    <h4>{$node.data_map.ulteriori_informazioni.contentclass_attribute.name}</h4>
        <div class="attribute-ulteriori_informazioni">
                {attribute_view_gui attribute=$node.data_map.ulteriori_informazioni}
        </div>


    <h4>{$node.data_map.info_utili.contentclass_attribute.name}</h4>
        <div class="attribute-info_utili">
                {attribute_view_gui attribute=$node.data_map.info_utili}
        </div>


    <h4>{$node.data_map.info_via_email.contentclass_attribute.name}</h4>
    	{$node.data_map.info_via_email.contentclass_attribute.description}
        <div class="attribute-info_via_email">
                {attribute_view_gui attribute=$node.data_map.info_via_email}
        </div>

    <h4>{$node.data_map.uso_documentazione.contentclass_attribute.name}</h4>
    	{$node.data_map.uso_documentazione.contentclass_attribute.description}
        <div class="attribute-uso_documentazione">
                {attribute_view_gui attribute=$node.data_map.uso_documentazione}
        </div>

    <h4>{$node.data_map.adesione_rete.contentclass_attribute.name}</h4>
    	{$node.data_map.adesione_rete.contentclass_attribute.description}
        <div class="attribute-adesione_rete">
                {attribute_view_gui attribute=$node.data_map.adesione_rete}
        </div>

    <h4>{$node.data_map.consenso_trattamento_testo.contentclass_attribute.name}</h4>
        <div class="attribute-consenso_trattamento_testo">
                {attribute_view_gui attribute=$node.data_map.consenso_trattamento_testo}
        </div>


    <h4>{$node.data_map.consenso_trattamento.contentclass_attribute.name}</h4>
	{$node.data_map.consenso_trattamento.contentclass_attribute.description}
        <div class="attribute-consenso_trattamento">
                {attribute_view_gui attribute=$node.data_map.consenso_trattamento}
        </div>

        <div class="content-action">
            <input type="submit" class="defaultbutton" name="ActionCollectInformation" value="{"Send form"|i18n("design/ezwebin/full/feedback_form")}" />
            <input type="hidden" name="ContentNodeID" value="{$node.node_id}" />
            <input type="hidden" name="ContentObjectID" value="{$node.object.id}" />
            <input type="hidden" name="ViewMode" value="full" />
        </div>
    </form>

    </div>
</div>

</div>
</div>
