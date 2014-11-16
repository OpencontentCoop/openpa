<div id="content-tree">

{* DESIGN: Header START *}<div class="box-header"><div class="box-ml">
<h4>Profili</h4>
{* DESIGN: Header END *}</div></div>
{* DESIGN: Content START *}<div class="box-bc"><div class="box-ml"><div class="box-content">

{* Treemenu. *}
<div id="contentstructure">
    {include uri='design:contentstructuremenu/content_structure_menu_dynamic.tpl' custom_root_node=apps_root()}    
</div>

{* DESIGN: Content END *}</div></div></div>
</div>


{* Left menu width control. *}
<div id="widthcontrol-links" class="widthcontrol">
<p>
{switch match=ezpreference( 'admin_left_menu_size' )}
    {case match='medium'}
    <a href={'/user/preferences/set/admin_left_menu_size/small'|ezurl} title="{'Change the left menu width to small size.'|i18n( 'design/admin/parts/content/menu' )}">{'Small'|i18n( 'design/admin/parts/content/menu' )}</a>
    <span class="current">{'Medium'|i18n( 'design/admin/parts/content/menu' )}</span>
    <a href={'/user/preferences/set/admin_left_menu_size/large'|ezurl} title="{'Change the left menu width to large size.'|i18n( 'design/admin/parts/content/menu' )}">{'Large'|i18n( 'design/admin/parts/content/menu' )}</a>
    {/case}

    {case match='large'}
    <a href={'/user/preferences/set/admin_left_menu_size/small'|ezurl} title="{'Change the left menu width to small size.'|i18n( 'design/admin/parts/content/menu' )}">{'Small'|i18n( 'design/admin/parts/content/menu' )}</a>
    <a href={'/user/preferences/set/admin_left_menu_size/medium'|ezurl} title="{'Change the left menu width to medium size.'|i18n( 'design/admin/parts/content/menu' )}">{'Medium'|i18n( 'design/admin/parts/content/menu' )}</a>
    <span class="current">{'Large'|i18n( 'design/admin/parts/content/menu' )}</span>
    {/case}

    {case in=array( 'small', '' )}
    <span class="current">{'Small'|i18n( 'design/admin/parts/content/menu' )}</span>
    <a href={'/user/preferences/set/admin_left_menu_size/medium'|ezurl} title="{'Change the left menu width to medium size.'|i18n( 'design/admin/parts/content/menu' )}">{'Medium'|i18n( 'design/admin/parts/content/menu' )}</a>
    <a href={'/user/preferences/set/admin_left_menu_size/large'|ezurl} title="{'Change the left menu width to large size.'|i18n( 'design/admin/parts/content/menu' )}">{'Large'|i18n( 'design/admin/parts/content/menu' )}</a>
    {/case}

    {case}
    <a href={'/user/preferences/set/admin_left_menu_size/small'|ezurl} title="{'Change the left menu width to small size.'|i18n( 'design/admin/parts/content/menu' )}">{'Small'|i18n( 'design/admin/parts/content/menu' )}</a>
    <a href={'/user/preferences/set/admin_left_menu_size/medium'|ezurl} title="{'Change the left menu width to medium size.'|i18n( 'design/admin/parts/content/menu' )}">{'Medium'|i18n( 'design/admin/parts/content/menu' )}</a>
    <a href={'/user/preferences/set/admin_left_menu_size/large'|ezurl} title="{'Change the left menu width to large size.'|i18n( 'design/admin/parts/content/menu' )}">{'Large'|i18n( 'design/admin/parts/content/menu' )}</a>
    {/case}
{/switch}
</p>
</div>

{* This is the border placed to the left for draging width, js will handle disabling the one above and enabling this *}
<div id="widthcontrol-handler" class="hide">
<div class="widthcontrol-grippy"></div>
</div>
