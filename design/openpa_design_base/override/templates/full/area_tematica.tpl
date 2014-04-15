{include name=menu_control node=$node uri='design:parts/common/menu_control.tpl'}

	<div class="content-view-full">
	    <div class="class-area_tematica">

		<div class="attribute-page">
		    {attribute_view_gui attribute=$node.object.data_map.layout}
		</div>
		{*<div class="attribute-description">{attribute_view_gui attribute=$node.object.data_map.abstract}</div>*}
	    </div>
	</div>
