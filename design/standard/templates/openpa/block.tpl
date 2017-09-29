{if is_set($select_blocks)}

  <div class="row">
    <div class="col-md-12">
      <h3>Seleziona il blocco</h3>
      <ul class="list-group">
        {foreach $select_blocks as $block}
          <li class="list-group-item">
            <a href="{concat('openpa/block/',$block.id)|ezurl(no)}"><strong>{$block.name}</strong> {$block.type} <small>{$block.view}</small></a>
          </li>
        {/foreach}
      </ul>
    </div>
  </div>
{else}

  <a class="btn btn-xs btn-info" href="{'openpa/block/'|ezurl(no)}">Seleziona nuovo oggetto</a>

  <div class="row">
    <div class="nav-section">
      {foreach $blocks as $AllowedType => $AllowedBlock}      
        <h3>{$AllowedBlock.Name|wash()}</h3>
        <ul class="list-group">     
          {foreach $AllowedBlock.ViewName as $ViewList => $ViewName}
            <li class="list-group-item">
              {if $block.view|eq($ViewList)}
                {$ViewName|wash()} <br /><small>{$ViewList}</small>
              {else}
                <a href="{concat('openpa/block/',$block.id, '/', $ViewList)|ezurl(no)}">{$ViewName|wash()} <br /><small>{$ViewList}</small></a>
              {/if}
            </li>
          {/foreach}
        </ul>      
      {/foreach}    
    </div>
    
    <div class="content-main">
      {if $block}
      <p>
        <a id="expand" class="btn btn-default"><i class="fa fa-expand"></i></a>
        <a id="collapse" class="btn btn-default"><i class="fa fa-compress"></i></a>
      </p>
      
      <div class="row">
        <div id="demo" data-width="12" class="col-md-12">
          {block_view_gui block=$block}
        </div>
      </div>    
    </div>
    {/if}
  </div>
  
  {literal}
  <script>
    $(document).ready(function(){
      var $demo = $('#demo');
      $('#expand').on('click',function(){
        var current = $demo.data('width');
        if (current < 12) {
          $demo.removeClass('col-md-'+current);
          current++;
          $demo.data('width', current);
          $demo.addClass('col-md-'+current);
          $(window).trigger('resize');
          
          var owl = $('#carousel_{/literal}{$block.id}{literal}');
          var owlInstance = owl.data('owlCarousel');
          if(owlInstance != null) owlInstance.reinit();
          
        }
      });
      $('#collapse').on('click',function(){
        var current = $demo.data('width');
        if (current > 1) {
          $demo.removeClass('col-md-'+current);
          current--;
          $demo.data('width', current);
          $demo.addClass('col-md-'+current);
          $(window).trigger('resize');
          
          var owl = $('#carousel_{/literal}{$block.id}{literal}');
          var owlInstance = owl.data('owlCarousel');
          if(owlInstance != null) owlInstance.reinit();
          
        }
      });
    });
  </script>
  {/literal}

{/if}