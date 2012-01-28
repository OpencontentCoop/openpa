{if is_set( $attribute_base )|not}
  {def $attribute_base = 'ContentObjectAttribute'}
{/if}
<div class="block">

<div class="element">
{run-once}
{def $domain=ezsys( 'hostname' )|explode('.')|implode('_')}

<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key={ezini('SiteSettings', 'GMapsKey')}&amp;sensor=true" type="text/javascript"></script>
<script type="text/javascript">
{literal}
function eZGmapLocation_MapControl( attributeId, latLongAttributeBase )
{
    var mapid = 'ezgml-map-' + attributeId, latid  = 'ezcoa-' + latLongAttributeBase + '_latitude', longid = 'ezcoa-' + latLongAttributeBase + '_longitude';
    var geocoder = null, addressid = 'ezgml-address-' + attributeId;

    var showAddress = function()
    {
        var address = document.getElementById( addressid ).value;
        if ( geocoder )
        {
            geocoder.getLatLng( address, function( point )
            {
                if ( !point )
                {
                    alert( address + " not found" );
                }
                else
                {
                    map.setCenter( point, 13 );
                    map.clearOverlays();
                    map.addOverlay( new GMarker( point ) );
                    updateLatLngFields( point );
                }
            });
        }
    };
    
    var updateLatLngFields = function( point )
    {
        document.getElementById(latid).value = point.lat();
        document.getElementById(longid).value = point.lng();
        document.getElementById( 'ezgml-restore-button-' + attributeId ).disabled = false;
        document.getElementById( 'ezgml-restore-button-' + attributeId ).className = 'button';
    };

    var restoretLatLngFields = function()
    {
        document.getElementById( latid ).value     = document.getElementById('ezgml_hidden_latitude_' + attributeId ).value;
        document.getElementById( longid ).value    = document.getElementById('ezgml_hidden_longitude_' + attributeId ).value;
        document.getElementById( addressid ).value = document.getElementById('ezgml_hidden_address_' + attributeId ).value;
        if ( document.getElementById( latid ).value && document.getElementById( latid ).value != 0 )
        {
            var point = new GLatLng( document.getElementById( latid ).value, document.getElementById( longid ).value );
            //map.setCenter(point, 13);
            map.clearOverlays();
            map.addOverlay( new GMarker(point) );
            map.panTo( point );
        }
        document.getElementById( 'ezgml-restore-button-' + attributeId ).disabled = true;
        document.getElementById( 'ezgml-restore-button-' + attributeId ).className = 'button-disabled';
        return false;
    };

    var getUserPosition = function()
    {
        navigator.geolocation.getCurrentPosition( function( position )
        {
            var location = '', point = new GLatLng(  position.coords.latitude, position.coords.longitude );

            if ( navigator.geolocation.type == 'Gears' && position.gearsAddress )
                location = [position.gearsAddress.city, position.gearsAddress.region, position.gearsAddress.country].join(', ');
            else if ( navigator.geolocation.type == 'ClientLocation' )
                location = [position.address.city, position.address.region, position.address.country].join(', ');

            document.getElementById( addressid ).value = location;
            map.setCenter( point, 13 );
            map.clearOverlays();
            map.addOverlay( new GMarker(point) );
            updateLatLngFields( point );
        },
        function( e )
        {
            alert( 'Could not get your location, error was: ' + e.message );
        },
        { 'gearsRequestAddress': true });
    };

    if (GBrowserIsCompatible())
    {
        var startPoint = null, zoom = 0, map = new GMap2( document.getElementById( mapid ) );
        if ( document.getElementById( latid ).value && document.getElementById( latid ).value != 0 )
        {
            startPoint = new GLatLng( document.getElementById( latid ).value, document.getElementById( longid ).value );
            zoom = 13;
        }
        else
        {
            startPoint = new GLatLng(0,0);
        }
        map.addControl( new GSmallMapControl() );
        map.addControl( new GMapTypeControl() );
        map.setCenter( startPoint, zoom );
        map.addOverlay( new GMarker( startPoint ) );
        geocoder = new GClientGeocoder();
        GEvent.addListener( map, 'click', function( newmarker, point )
        {
            map.clearOverlays();
            map.addOverlay( new GMarker( point ) );
            map.panTo( point );
            updateLatLngFields( point );
            document.getElementById( addressid ).value = '';
        });

        document.getElementById( 'ezgml-address-button-' + attributeId ).onclick = showAddress;
        document.getElementById( 'ezgml-restore-button-' + attributeId ).onclick = restoretLatLngFields;

        if ( navigator.geolocation )
        {
            document.getElementById( 'ezgml-mylocation-button-' + attributeId ).onclick = getUserPosition;
            document.getElementById( 'ezgml-mylocation-button-' + attributeId ).className = 'button';
            document.getElementById( 'ezgml-mylocation-button-' + attributeId ).disabled = false;
        }
    }
}
{/literal}
</script>
{/run-once}

<script type="text/javascript">
<!--

if ( window.addEventListener )
    window.addEventListener('load', function(){ldelim} eZGmapLocation_MapControl( {$attribute.id}, "{if ne( $attribute_base, 'ContentObjectAttribute' )}{$attribute_base}-{/if}{$attribute.contentclassattribute_id}_{$attribute.contentclass_attribute_identifier}" ) {rdelim}, false);
else if ( window.attachEvent )
    window.attachEvent('onload', function(){ldelim} eZGmapLocation_MapControl( {$attribute.id}, "{if ne( $attribute_base, 'ContentObjectAttribute' )}{$attribute_base}-{/if}{$attribute.contentclassattribute_id}_{$attribute.contentclass_attribute_identifier}" ) {rdelim} );

-->
</script>

    <input type="text" id="ezgml-address-{$attribute.id}" size="62" name="{$attribute_base}_data_gmaplocation_address_{$attribute.id}" value="{$attribute.content.address}"/>
    <input class="button" type="button" id="ezgml-address-button-{$attribute.id}" value="{'Find address'|i18n('extension/ezgmaplocation/datatype')}"/>
    <input class="button-disabled" type="button" id="ezgml-restore-button-{$attribute.id}" value="{'Restore'|i18n('extension/ezgmaplocation/datatype')}" onclick="javascript:void( null ); return false" disabled="disabled"  title="{'Restores location and address values to what it was on page load.'|i18n('extension/ezgmaplocation/datatype')}" />

    <input id="ezgml_hidden_address_{$attribute.id}" type="hidden" name="ezgml_hidden_address_{$attribute.id}" value="{$attribute.content.address}" disabled="disabled" />
    <input id="ezgml_hidden_latitude_{$attribute.id}" type="hidden" name="ezgml_hidden_latitude_{$attribute.id}" value="{$attribute.content.latitude}" disabled="disabled" />
    <input id="ezgml_hidden_longitude_{$attribute.id}" type="hidden" name="ezgml_hidden_longitude_{$attribute.id}" value="{$attribute.content.longitude}" disabled="disabled" />
    <div id="ezgml-map-{$attribute.id}" style="width: 500px; height: 280px; margin-top: 2px;"></div>
</div>

<div class="element">
  <div class="block">
    <label>{'Latitude'|i18n('extension/ezgmaplocation/datatype')}:</label>
    <input id="ezcoa-{if ne( $attribute_base, 'ContentObjectAttribute' )}{$attribute_base}-{/if}{$attribute.contentclassattribute_id}_{$attribute.contentclass_attribute_identifier}_latitude" class="box ezcc-{$attribute.object.content_class.identifier} ezcca-{$attribute.object.content_class.identifier}_{$attribute.contentclass_attribute_identifier}" type="text" name="{$attribute_base}_data_gmaplocation_latitude_{$attribute.id}" value="{$attribute.content.latitude}" />
  </div>
  
  <div class="block">
    <label>{'Longitude'|i18n('extension/ezgmaplocation/datatype')}:</label>
    <input id="ezcoa-{if ne( $attribute_base, 'ContentObjectAttribute' )}{$attribute_base}-{/if}{$attribute.contentclassattribute_id}_{$attribute.contentclass_attribute_identifier}_longitude" class="box ezcc-{$attribute.object.content_class.identifier} ezcca-{$attribute.object.content_class.identifier}_{$attribute.contentclass_attribute_identifier}" type="text" name="{$attribute_base}_data_gmaplocation_longitude_{$attribute.id}" value="{$attribute.content.longitude}" />
  </div>

  <div class="block">
    <input class="button-disabled" type="button" id="ezgml-mylocation-button-{$attribute.id}" value="{'My current location'|i18n('extension/ezgmaplocation/datatype')}" onclick="javascript:void( null ); return false" disabled="disabled" title="{'Gets your current position if your browser support GeoLocation and you grant this website access to it! Most accurate if you have a built in gps in your Internet device! Also note that you might still have to type in address manually!'|i18n('extension/ezgmaplocation/datatype')}" />
  </div>
</div>

<div class="break"></div>
</div>
