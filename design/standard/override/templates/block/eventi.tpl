{def $valid_node = $block.valid_nodes[0]
$show_link = true()}

{if and( $valid_node|not(), is_set( $block.custom_attributes.source ) )}
    {set $valid_node = fetch( content, node, hash( node_id, $block.custom_attributes.source ) )}
{/if}

{if $valid_node|not()}
    {set $valid_node = fetch( content, node, hash( node_id, ezini( 'NodeSettings', 'RootNode', 'content.ini' ) ) )
         $show_link = false()}
{/if}

{def $calendarDataDay = fetch( openpa, calendario_eventi, hash( 'calendar', $valid_node, 'params', hash( 'interval', 'PT1439M' ) ) )}
{if $block.custom_attributes.tab_title|ne('')}
    {def $calendarDataOther = fetch( openpa, calendario_eventi, hash( 'calendar', $valid_node, 'params', $block.custom_attributes ) )}
{else}
    {def $calendarDataOther = false()}
{/if}
{*debug-log var=$calendarDataDay.fetch_parameters msg='Blocco eventi fetch oggi'*}

{def $day_events = $calendarDataDay.events
     $day_events_count = $calendarDataDay.search_count
     $prossimi = array()
     $prossimi_count = 0}

{if $calendarDataOther}
{*debug-log var=$calendarDataOther.fetch_parameters msg='Blocco eventi fetch secondo tab'*}
    {set $prossimi = $calendarDataOther.events
         $prossimi_count = $calendarDataOther.search_count}
{/if}

{if and( $prossimi_count|eq(0), $day_events_count|eq(0) )}

{editor_warning( "Nessun evento in programma" )}

{else}

    {ezscript_require(array( 'ezjsc::jquery', 'jquery.cycle2.min.js', 'jquery.cycle2.carousel.min.js' ))}
    <script>{literal}
      $(function() {
        "use strict";
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
          console.log(this);
          $( '.cycle-slideshow' ).cycle('destroy').cycle();
        })
      });
    {/literal}</script>

    {if $block.name|ne('')}
    <div class="widget events {$block.view}">
        <div class="widget_title">
            <h3>{$block.name|wash()}</h3>
        </div>
        <div class="widget_content">


    {else}

    <div class="widget_tabs events {if $prossimi_count|gt(0)}subnav{/if} {$block.view}">

    {/if}

        {if and( $day_events_count|gt(0), $prossimi_count|gt(0) )}
        <ul class="nav nav-tabs" role="tablist">
            {if $day_events_count|ne(0)}
                <li class="active eventi-oggi">
                    <a href="#oggi" data-toggle="tab">
                        Oggi
                    </a>
                </li>
            {/if}
            {if $prossimi_count|gt(0)}
                <li class="{if $day_events_count|eq(0)}active{/if} eventi-{$block.custom_attributes.tab_title|slugize}">
                    <a href="#{$block.custom_attributes.tab_title|slugize}" data-toggle="tab">
                        {$block.custom_attributes.tab_title}
                    </a>
                </li>
            {/if}
        </ul>
        {/if}

        <div class="tab-content">


            {if $day_events_count|ne(0)}
                <div class="tab-pane active" id="oggi">
                    <div class="cycle-slideshow" data-cycle-allow-wrap=false data-cycle-timeout=0 data-cycle-fx=carousel data-cycle-next=".cycle-next" data-cycle-prev=".cycle-prev" data-cycle-carousel-visible=4 data-cycle-carousel-vertical=true data-cycle-slides="> div.event-item">
                        {foreach $day_events as $index => $child}
                            {include uri="design:calendar/block_list_item.tpl" item=$child}
                        {/foreach}
                    </div>
                </div>
            {/if}

            {if $prossimi_count|gt(0)}
            <div id="{$block.custom_attributes.tab_title|slugize}" class="tab-pane {if $day_events_count|eq(0)}active{/if} no-js-hide">
                <div class="cycle-slideshow" data-cycle-allow-wrap=false data-cycle-timeout=0 data-cycle-fx=carousel data-cycle-next=".cycle-next" data-cycle-prev=".cycle-prev" data-cycle-carousel-visible=4 data-cycle-carousel-vertical=true data-cycle-slides="> div.event-item">
                    {foreach $prossimi as $index => $child}
                        {include uri="design:calendar/block_list_item.tpl" item=$child}
                    {/foreach}
                </div>
            </div>
        {/if}


        </div>



        {if $show_link}

        <div class="text-center m_top_20 clearfix f_size_large row">
            <div class="col-xs-2">
                <p class="pull-left cycle-prev m_bottom_0"><a class="btn btn-link f_size_large"><i class="fa fa-arrow-up"></i></a></p>
            </div>
            <div class="col-xs-8">
                <p class="m_bottom_0">
                    <a class="btn btn-link color_dark" href="{$valid_node.url_alias|ezurl(no)}">
                        <i class="color_dark fa fa-calendar"></i>
                        {*$valid_node.name*} Vai al calendario
                    </a>
                </p>
            </div>
            <div class="col-xs-2">
                <p class="pull-right cycle-next m_bottom_0"><a class="btn btn-link f_size_large"><i class="fa fa-arrow-down"></i></a></p>
            </div>

        </div>
        {/if}




        {if $block.name|ne('')}
    </div>
    {/if}

</div>


{/if}

