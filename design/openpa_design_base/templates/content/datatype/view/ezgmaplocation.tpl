{run-once}

{def $domain=ezsys( 'hostname' )|explode('.')|implode('_')}

<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
<script type="text/javascript">

{literal}
/* <![CDATA[ */
function eZGmapLocation_MapView( attributeId, latitude, longitude ) {
    var myLatlng = new google.maps.LatLng(latitude, longitude);
    var mapOptions = {
        zoom: 13,
        center: myLatlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    var map = new google.maps.Map(document.getElementById( 'ezgml-map-' + attributeId ), mapOptions);

    var marker = new google.maps.Marker({
        position: myLatlng,
        map: map,
        title: 'marker'
    });
}
/* ]]> */
{/literal}

</script>
{/run-once}

{if $attribute.has_content}
<script type="text/javascript">
<!--
google.maps.event.addDomListener(window, 'load', function(){ldelim} eZGmapLocation_MapView( {$attribute.id}, {first_set( $attribute.content.latitude, '0.0')}, {first_set( $attribute.content.longitude, '0.0')} ) {rdelim});
-->
</script>


<!--
<div class="block">
<label>{'Latitude'|i18n('extension/ezgmaplocation/datatype')}:</label> {$attribute.content.latitude}
<label>{'Longitude'|i18n('extension/ezgmaplocation/datatype')}:</label> {$attribute.content.longitude}
  {if $attribute.content.address}
    <label>{'Address'|i18n('extension/ezgmaplocation/datatype')}:</label> {$attribute.content.address}
  {/if}
</div>

<label>{'Map'|i18n('extension/ezgmaplocation/datatype')}:</label>
-->
<div id="ezgml-map-{$attribute.id}" style="width: 100%; height: 280px;"></div>
{/if}
