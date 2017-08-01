{ezscript_require( array( 'ezjsc::jquery', 'leaflet/leaflet.0.7.2.js', 'leaflet/leaflet-osm.js') )}
{ezcss_require( array( 'leaflet/leaflet.css', 'leaflet/map.css' ) )}

{set $xml_link = $xml_link|trim('/')}
{set $xml_link = cond( $xml_link|ends_with('full'), $xml_link, concat($xml_link, '/full'))}

{if $title}<h4><a href="{$xml_link}">{$title}</a></h4>{/if}
<div id="percorso-{$xml_link|md5()}" style="width: 100%; height: 400px;"></div>
{run-once}
{literal}
    <script type="text/javascript">
        var drawData = function(url,id){
            var map = new L.Map('percorso-'+id);
            L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            $.ajax({
                url: url,
                dataType: "xml",
                success: function (xml) {
                    var layer = new L.OSM.DataLayer(xml).addTo(map);
                    map.fitBounds(layer.getBounds());
                }
            });
        }
    </script>
{/literal}
{/run-once}

<script type="text/javascript">
    drawData("{$xml_link}","{$xml_link|md5()}");
</script>
