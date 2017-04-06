{if $openpa.content_attachment.has_content}
  <div>
  {foreach $openpa.content_attachment.attributes as $attribute}        
    <h4>
      <strong>{$attribute.contentclass_attribute_name|wash()}</strong>
    </h4>
    {attribute_view_gui attribute=$attribute}
  {/foreach}
  </div>
{/if}

{if $openpa.content_attachment.children_count}
  {foreach $openpa.content_attachment.children as $item}    
    <div>
      {if $item|has_attribute( 'file' )}        
        <a class="object-right button" href="{concat("content/download/",$item|attribute( 'file' ).contentobject_id,"/",$item|attribute( 'file' ).id,"/file/",$item|attribute( 'file' ).content.original_filename)|ezurl(no))}" title="Scarica il file {$item|attribute( 'file' ).content.original_filename|wash( xhtml )}">
            <i class="fa fa-download fa-2x"></i><span class="hide">download</span>
        </a>
      {else}
        <a class="object-right button" href="{$item.url_alias|ezurl(no)}">LEGGI</a>
      {/if}	  
      <p class="float-break">
        <strong>{$item.name|wash()}</strong>		
        {if $item|has_attribute( 'file' )} 
          <br /><small>File {$item|attribute( 'file' ).content.original_filename} ({$item|attribute( 'file' ).content.filesize|si( byte )})</small>
        {/if}
      </p>
      {$item|abstract()}
	  {include uri="design:parts/toolbar/node_edit.tpl" current_node=$item}
	  {include uri="design:parts/toolbar/node_trash.tpl" current_node=$item}
    </div>      
  {/foreach}  
{/if}
