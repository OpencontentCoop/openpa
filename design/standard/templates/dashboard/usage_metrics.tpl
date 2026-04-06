<h2>Servizi attivi</h2>
<div data="usage_metrics"></div>
{literal}
    <script>
        $(document).ready(function () {
            var usageMetricsContainer = $('[data="usage_metrics"]');
            $.getJSON('/openpa/data/usage_metrics', function(data){
                $.each(data, function(){
                    var wrapper = $('<div class="u-padding-bottom-s"></div>');
                    var title = $('<h3></h3>');
                    $('<a href="//'+this.service_url+'" title="'+this.service_name+'">'+this.service_name+'</a>').appendTo(title);
                    $.each(this.usage_metrics, function () {
                        $('<br /><small class="u-color-black">'+this.name+': '+' '+this.value+'<small>').appendTo(title);
                    });
                    title.appendTo(wrapper);
                    wrapper.appendTo(usageMetricsContainer);
                });
            });
        });
    </script>
{/literal}