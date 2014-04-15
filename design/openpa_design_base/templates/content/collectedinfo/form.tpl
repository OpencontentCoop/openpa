{*
	TEMPLATE FORM, retecivica
	per risposta a chi compila il form di valutazione delle pagine del sito

*}

<div class="border-box">
<div class="border-content">

 <div class="global-view-full content-view-full">
  <div class="class-{$node.object.class_identifier}">


{default collection=cond( $collection_id, fetch( content, collected_info_collection, hash( collection_id, $collection_id ) ),
                          fetch( content, collected_info_collection, hash( contentobject_id, $node.contentobject_id ) ) )}

{set-block scope=global variable=title}{'Form %formname'|i18n('design/standard/content/form',,hash('%formname',$node.name|wash))}{/set-block}
{set-block scope=root variable=email_receiver}nospam@ez.no{/set-block}
{set-block scope=root variable=email_sender}custom_sender@example.com{/set-block}
{set-block scope=root variable=email_reply_to}custom_reply_to@example.com{/set-block}

<h1>Informazioni per l'utente</h1>

<h2>Grazie di aver compilato il form di valutazione "{$object.name|wash}"</h2>

{if $error}

{if $error_existing_data}
<p>{'You have already submitted this form. The previously submitted data was:'|i18n('design/standard/content/form')}</p>
{/if}

{/if}


<h3>{'Collected information'|i18n('design/standard/content/form')}</h3>

<ul>
{section loop=$collection.attributes}
<li>
<label>{$:item.contentclass_attribute_name|wash}: </label>
{attribute_result_gui view=info attribute=$:item}
</li>
{/section}
</ul>

<p/>
{if $collection.data_map.link.has_content}
	<h2><a href={concat( $collection.data_map.link.content)|ezurl()}>{'Return to site'|i18n('design/standard/content/form')}</a></h2>
{else}
	<h2><a href={$node.parent.url|ezurl}>{'Return to site'|i18n('design/standard/content/form')}</a></h2>
{/if}

{/default}

    </div>
</div>

</div>
</div>
