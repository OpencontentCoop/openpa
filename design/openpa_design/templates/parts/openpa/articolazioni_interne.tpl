{if is_set($style)|not()}
	{def $style='col-odd'}
{/if}

{if is_set($title)|not()}
	{def $title=true()}
{/if}

{if is_set($icon)|not()}
	{def $icon=false()}
{/if}

{if is_set($no_servizi)|not()}
	{def $no_servizi=false()}
{/if}

{def 
	$servizi_correlati=array()
	
	$incarichi_correlati=array()
		$incarico_uffici_correlati=array()
			$incarico_ufficio_altre_strutture_correlate=array()
			$incarico_ufficio_strutture_correlate=array()
		$incarico_altre_strutture_correlate=array()
		$incarico_strutture_correlate=array()
	
	$uffici_correlati=array()		
		$ufficio_altre_strutture_correlate=array()
		$ufficio_strutture_correlate=array()

	$altre_strutture_correlate=array()
	$strutture_correlate=array()
		$strutture_strutture_correlate=array()
	
	$done=array()
}

{def $classi_con_area = wrap_user_func('getClassAttributes', array(array('area')) )  
	 $classe_con_area = false()
	 $classi_con_questonodo = wrap_user_func('getClassAttributes', array(array($node.class_identifier)) ) 
	 $ccqn = array()}

{foreach $classi_con_area as $ccs}
	{if $ccs.identifier|eq($node.class_identifier)}
		{set $classe_con_area = true() }
	{/if}
{/foreach}

{foreach $classi_con_questonodo as $ccq}
	{set $ccqn = $ccqn|merge(array($ccq.identifier))}
{/foreach}

{if $no_servizi|not}
{if $classe_con_area}
	{set $servizi_correlati= fetch( 'content', 'reverse_related_objects',
					hash( 'object_id', $node.object.id,
						'attribute_identifier', concat($node.class_identifier,'/area'),
						'sort_by',  array( 'name', true() ) ) )}
{/if}	
{/if}					

{if $ccqn|contains('incarico')}
	{set $incarichi_correlati= fetch( 'content', 'reverse_related_objects', 
					hash( 'object_id', $node.object.id, 
						'attribute_identifier', concat('incarico/',$node.class_identifier),
						'sort_by',  array( 'name', true() ) ) )}
{/if}

{if $ccqn|contains('ufficio')}					
{set $uffici_correlati= fetch( 'content', 'reverse_related_objects', 
				hash(  'object_id',$node.object.id,
					'attribute_identifier',  concat('ufficio/',$node.class_identifier),
					'sort_by',  array( 'name', true() ) ) )}				
{/if}

{if $ccqn|contains('struttura')}	
{set $strutture_correlate=fetch( 'content', 'reverse_related_objects', 
				hash( 'object_id', $node.object.id,
					'attribute_identifier', concat('struttura/',$node.class_identifier),
					'sort_by',  array( 'name', true() ) 
					) )}
{/if}					

{if $ccqn|contains('altra_struttura')}
{set $altre_strutture_correlate=fetch( 'content', 'reverse_related_objects', 
				hash( 'object_id', $node.object.id,
					'attribute_identifier', concat('altra_struttura/',$node.class_identifier),
					'sort_by',  array( 'name', true() )
					) )}
{/if}	

{if or( $incarichi_correlati|count(), $uffici_correlati|count(), $altre_strutture_correlate|count(), $strutture_correlate|count(), $servizi_correlati|count() )}
	
	{if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
	
	{if $title}
	<div class="{$style} col float-break attribute-articolazioni-interne">
		<div class="col-title"><span class="label">Strutture interne</span></div>
		<div class="col-content"><div class="col-content-design">
	{/if}
			{if $servizi_correlati|count()}						
			<ul>
			{foreach $servizi_correlati as $object_correlato}
				{if $object_correlato.id|ne($node.contentobject_id)}
					{if $object_correlato.section_id|eq(1)}
					   <li>
					    {if $icon} {$object_correlato.main_node.object.class_identifier} {/if}
						
						{if is_area_tematica()}
						<a href= {concat($node.url_alias, '/(reference)/', $object_correlato.main_node.node_id)|ezurl()}>{$object_correlato.name}</a>
						{else}
						<a href= {$object_correlato.main_node.url_alias|ezurl()}>{$object_correlato.name}</a>
						{/if}
						
					   </li>
					{/if}	
				{/if}
			{/foreach}
			</ul>
			{/if}

			{if $incarichi_correlati|count()}						
			<ul>
			{foreach $incarichi_correlati as $incarico}
				{if $incarico.id|ne($node.contentobject_id)}
					{if $incarico.section_id|eq(1)}
					   <li>
						{*
						{if $icon}{$incarico.main_node.object.class_identifier} - {/if}
						*}
						
						{if is_area_tematica()}
						<a href= {concat($node.url_alias, '/(reference)/', $incarico.main_node.node_id)|ezurl()}>{$incarico.name}</a>
						{else}
						<a href= {$incarico.main_node.url_alias|ezurl()}>{$incarico.name}</a>
						{/if}
						
					   </li>

						{set $incarico_uffici_correlati= fetch( 'content', 'reverse_related_objects', 
										hash(  'object_id',$incarico.id,
											'attribute_identifier',  'ufficio/incarico',
											'sort_by',  array( 'name', true() ) ) )}
						{set $incarico_altre_strutture_correlate=fetch( 'content', 'reverse_related_objects', 
										hash( 'object_id', $incarico.id,
											'attribute_identifier', 'struttura/incarico',
											'sort_by',  array( 'name', true() ) 
											) )}
						{set $incarico_strutture_correlate=fetch( 'content', 'reverse_related_objects', 
										hash( 'object_id', $incarico.id,
											'attribute_identifier', 'altra_struttura/incarico',
											'sort_by',  array( 'name', true() )
											) )}							   
					   
						{if or( $incarico_uffici_correlati|count(), $incarico_altre_strutture_correlate|count(), $incarico_strutture_correlate|count() )}
							<ul>
							{if $incarico_uffici_correlati|count()}	
								{foreach $incarico_uffici_correlati as $object_correlato}																	
									{if $object_correlato.id|ne($node.contentobject_id)}
										{if $object_correlato.section_id|eq(1)}
										{set $done = $done|append($object_correlato.id)}
										   <li>
											{if $icon}{$object_correlato.main_node.object.class_identifier} {/if}
	
											{if is_area_tematica()}
											<a href= {concat($node.url_alias, '/(reference)/', $object_correlato.main_node.node_id)|ezurl()}>{$object_correlato.name}</a>
											{else}
											<a href= {$object_correlato.main_node.url_alias|ezurl()}>{$object_correlato.name}</a>
											{/if}
											
										   
										   
											{set $incarico_ufficio_strutture_correlate=
											   fetch( 'content', 'reverse_related_objects', 
												hash( 'object_id', $object_correlato.id,
												'attribute_identifier', 'struttura/ufficio',
												'sort_by',  array( 'name', true() ) 
											) )}
											{set $incarico_ufficio_altre_strutture_correlate=
											   fetch( 'content', 'reverse_related_objects', 
												hash( 'object_id', $object_correlato.id,
												'attribute_identifier', 'altra_struttura/ufficio',
												'sort_by',  array( 'name', true() )
											) )}			
											   
											{if or( $incarico_ufficio_strutture_correlate|count(),
 												$incarico_ufficio_strutture_correlate|count() )}
											<ul>
											{if $incarico_ufficio_altre_strutture_correlate|count()}	
												{foreach $incarico_ufficio_altre_strutture_correlate as $object_correlato_}
												{if $object_correlato_.id|ne($node.contentobject_id)}
												{if $object_correlato_.section_id|eq(1)}
												{set $done = $done|append($object_correlato_.id)}
												<li>
												{*{if $icon} {$object_correlato.main_node.object.class_identifier} - {/if}*}
												{if $icon}
												{attribute_view_gui href=nolink 
							 					attribute=$object_correlato.data_map.tipo_struttura}
												{/if}
														
												{if is_area_tematica()}
													<a href= {concat($node.url_alias, '/(reference)/', $object_correlato.main_node.node_id)|ezurl()}>{$object_correlato.name}</a>
												{else}
													<a href= {$object_correlato.main_node.url_alias|ezurl()}>{$object_correlato.name}</a>
												{/if}
																	
												</li>
												{/if}	
												{/if}
												{/foreach}
											{/if}
											{if $incarico_ufficio_strutture_correlate|count()}	
														{foreach $incarico_ufficio_strutture_correlate as $object_correlato_}
															{if $object_correlato_.id|ne($node.contentobject_id)}
																{if $object_correlato_.section_id|eq(1)}
																{set $done = $done|append($object_correlato_.id)}
																   <li>
																	{*{if $icon} {$object_correlato.main_node.object.class_identifier} - {/if}*}
												{if $icon}
												{attribute_view_gui href=nolink 
							 					attribute=$object_correlato.data_map.tipo_struttura}
												{/if}

																	{if is_area_tematica()}
																	<a href= {concat($node.url_alias, '/(reference)/', $object_correlato.main_node.node_id)|ezurl()}>{$object_correlato.name}</a>
																	{else}
																	<a href= {$object_correlato.main_node.url_alias|ezurl()}>{$object_correlato.name}</a>
																	{/if}

																	</li>
																{/if}	
															{/if}
														{/foreach}
													{/if}						
													</ul>
												{/if}										   
											</li>
										{/if}	
									{/if}
								{/foreach}
							{/if}
							
							{if $incarico_altre_strutture_correlate|count()}	
								{foreach $incarico_altre_strutture_correlate as $object_correlato}
									{if $object_correlato.id|ne($node.contentobject_id)}
										{if $object_correlato.section_id|eq(1)}
										{set $done = $done|append($object_correlato.id)}
										   <li>
											{*{if $icon} {$object_correlato.main_node.object.class_identifier} - {/if}*}
												{if $icon}
												{attribute_view_gui href=nolink 
							 					attribute=$object_correlato.data_map.tipo_struttura}
												{/if}

											{if is_area_tematica()}
											<a href= {concat($node.url_alias, '/(reference)/', $object_correlato.main_node.node_id)|ezurl()}>{$object_correlato.name}</a>
											{else}
											<a href= {$object_correlato.main_node.url_alias|ezurl()}>{$object_correlato.name}</a>
											{/if}

											</li>
										{/if}	
									{/if}
								{/foreach}
							{/if}
							
							{if $incarico_strutture_correlate|count()}	
								{foreach $incarico_strutture_correlate as $object_correlato}
									{if $object_correlato.id|ne($node.contentobject_id)}
										{if $object_correlato.section_id|eq(1)}
										{set $done = $done|append($object_correlato.id)}
										   <li>
											{*{if $icon} 
												{$object_correlato.main_node.object.class_identifier} - 
											{/if}*}
												{if $icon}
												{attribute_view_gui href=nolink 
							 					attribute=$object_correlato.data_map.tipo_struttura}
												{/if}
						
											{if is_area_tematica()}
											<a href= {concat($node.url_alias, '/(reference)/', $object_correlato.main_node.node_id)|ezurl()}>{$object_correlato.name}</a>
											{else}
											<a href= {$object_correlato.main_node.url_alias|ezurl()}>{$object_correlato.name}</a>
											{/if}
						
										   </li>
										{/if}	
									{/if}
								{/foreach}
							{/if}						
							</ul>
						{/if}					   
					   
					{/if}	
				{/if}
			{/foreach}
			</ul>
			{/if}
			 
			{if $uffici_correlati|count()}						
			<ul>
			{foreach $uffici_correlati as $ufficio}
				{if $ufficio.id|ne($node.contentobject_id)}
					{if $ufficio.section_id|eq(1)}
					{if $done|contains($ufficio.id)|not()}
					   <li>
						{* JEK *}
						{if $icon}
							{attribute_view_gui href=nolink attribute=$ufficio.main_node.object.data_map.tipo_struttura}
							{*$ufficio.main_node.object.class_identifier*} 
						{/if}

						{if is_area_tematica()}
						<a href= {concat($node.url_alias, '/(reference)/', $ufficio.main_node.node_id)|ezurl()}>{$ufficio.name}</a>
						{else}
						<a href= {$ufficio.main_node.url_alias|ezurl()}>{$ufficio.name}</a>
						{/if}
						
					   


						{set $ufficio_strutture_correlate=fetch( 'content', 'reverse_related_objects', 
										hash( 'object_id', $ufficio.id,
											'attribute_identifier', 'struttura/ufficio',
											'sort_by',  array( 'name', true() ) 
											) )}											
						{set $ufficio_altre_strutture_correlate=fetch( 'content', 'reverse_related_objects', 
										hash( 'object_id', $ufficio.id,
											'attribute_identifier', 'altra_struttura/ufficio',
											'sort_by',  array( 'name', true() )
											) )}			
					   						
						{if or( $ufficio_altre_strutture_correlate|count(), $ufficio_strutture_correlate|count() )}
							<ul>
							{if $ufficio_altre_strutture_correlate|count()}	
								{foreach $ufficio_altre_strutture_correlate as $object_correlato}
									{if $object_correlato.id|ne($node.contentobject_id)}
										{if $object_correlato.section_id|eq(1)}
										{set $done = $done|append($object_correlato.id)}
										   <li>
											{if $icon}
												{attribute_view_gui href=nolink 
												 attribute=$object_correlato.data_map.tipo_struttura}
											{/if}
						
											{if is_area_tematica()}
											<a href= {concat($node.url_alias, '/(reference)/', $object_correlato.main_node.node_id)|ezurl()}>{$object_correlato.name}</a>
											{else}
											<a href= {$object_correlato.main_node.url_alias|ezurl()}>{$object_correlato.name}</a>
											{/if}
						
										   </li>
										{/if}	
									{/if}
								{/foreach}
							{/if}
							{if $ufficio_strutture_correlate|count()}	
								{foreach $ufficio_strutture_correlate as $object_correlato}
									{if $object_correlato.id|ne($node.contentobject_id)}
										{if $object_correlato.section_id|eq(1)}
										{set $done = $done|append($object_correlato.id)}
										   <li>
											{if $icon}
												{attribute_view_gui href=nolink 
												 attribute=$object_correlato.data_map.tipo_struttura}
											{/if}
						
											{if is_area_tematica()}
											<a href= {concat($node.url_alias, '/(reference)/', $object_correlato.main_node.node_id)|ezurl()}>{$object_correlato.name}</a>
											{else}
											<a href= {$object_correlato.main_node.url_alias|ezurl()}>{$object_correlato.name}</a>
											{/if}
						
										   </li>
										{/if}	
									{/if}
								{/foreach}
							{/if}						
							</ul>
						{/if}
						</li>
					{/if}
					{/if}
				{/if}
			{/foreach}
			</ul>
			{/if}			 
			
			{if $strutture_correlate|count()}						
			<ul>
			{foreach $strutture_correlate as $object_correlato}
				{if $object_correlato.id|ne($node.contentobject_id)}
					{if $object_correlato.section_id|eq(1)}
					{if $done|contains($object_correlato.id)|not()}
					   <li>
						{*{if $icon}{$object_correlato.main_node.object.class_identifier} - {/if}*}
						{if $icon}
							{attribute_view_gui href=nolink 
							attribute=$object_correlato.data_map.tipo_struttura}
						{/if}

						{if is_area_tematica()}
						<a href= {concat($node.url_alias, '/(reference)/', $object_correlato.main_node.node_id)|ezurl()}>{$object_correlato.name}</a>
						{else}
						<a href= {$object_correlato.main_node.url_alias|ezurl()}>{$object_correlato.name}</a>
						{/if}

						
						{set $strutture_strutture_correlate=fetch( 'content', 'reverse_related_objects', 
										hash( 'object_id', $object_correlato.id,
											'attribute_identifier', 'struttura/struttura',
											'sort_by',  array( 'name', true() ) 
											) )}	


							{if $strutture_strutture_correlate|count()}
							<ul>	
								{foreach $strutture_strutture_correlate as $object_correlato}
									{if $object_correlato.id|ne($node.contentobject_id)}
										{if $object_correlato.section_id|eq(1)}
										{set $done = $done|append($object_correlato.id)}
										   <li>
											{if $icon}
												{attribute_view_gui href=nolink 
												 attribute=$object_correlato.data_map.tipo_struttura}
											{/if}
						
											{if is_area_tematica()}
											<a href= {concat($node.url_alias, '/(reference)/', $object_correlato.main_node.node_id)|ezurl()}>{$object_correlato.name}</a>
											{else}
											<a href= {$object_correlato.main_node.url_alias|ezurl()}>{$object_correlato.name}</a>
											{/if}
						
										   </li>
										{/if}	
									{/if}
								{/foreach}
							</ul>
							{/if}


						
					   </li>
					{/if}	
					{/if}	
				{/if}
			{/foreach}
			</ul>
			{/if}	

			{if $altre_strutture_correlate|count()}						
			<ul>
			{foreach $altre_strutture_correlate as $object_correlato}
				{if $object_correlato.id|ne($node.contentobject_id)}
					{if $object_correlato.section_id|eq(1)}
					{if $done|contains($object_correlato.id)|not()}
					   <li>
						{*{if $icon}{$object_correlato.main_node.object.class_identifier} - {/if}*}
						{if $icon}
							{attribute_view_gui href=nolink 
							attribute=$object_correlato.data_map.tipo_struttura}
						{/if}

						{if is_area_tematica()}
						<a href= {concat($node.url_alias, '/(reference)/', $object_correlato.main_node.node_id)|ezurl()}>{$object_correlato.name}</a>
						{else}
						<a href= {$object_correlato.main_node.url_alias|ezurl()}>{$object_correlato.name}</a>
						{/if}						
						
					   </li>
					{/if}	
					{/if}	
				{/if}
			{/foreach}
			</ul>
			{/if}			 
			 
			
			
	{if $title}		
		</div></div>
	</div>
	{/if}
	
{/if}
