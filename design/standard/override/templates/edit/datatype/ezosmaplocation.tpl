{if is_set( $attribute_base )|not}
    {def $attribute_base = 'ContentObjectAttribute'}
{/if}
{def $latitude  = $attribute.content.latitude|explode(',')|implode('.')
     $longitude = $attribute.content.longitude|explode(',')|implode('.')
     $contacts = openpapagedata().contacts}

<div class="block float-break" data-osmap-attribute="{$attribute.id}">

    <div id="map-{$attribute.id}" style="width: 100%; height: 300px; margin-top: 2px;"></div>

    <div class="block buttons float-break">
        <button class="pull-right button btn btn-sm btn-danger" name="Reset" style="display: none">Annulla modifiche</button>
        <button class="pull-right button btn btn-sm btn-info" name="MyLocation">Rileva posizione</button>
    </div>
    <div class="row">
        <div class="block address  col-md-12">
            <label>Indirizzo</label>
            <input class="ezgml_new_address box form-control"
                   type="text"
                   name="{$attribute_base}_data_gmaplocation_address_{$attribute.id}"
                   value="{$attribute.content.address}"/>
            <input class="ezgml_hidden_address"
                   type="hidden"
                   name="ezgml_hidden_address"
                   value="{$attribute.content.address}"
                   disabled="disabled"/>
        </div>
    </div>
    <div class="row">
        <div class="element latitude col-md-6">
            <label>Latitudine</label>
            <input class="ezgml_new_latitude box form-control"
                   type="text"
                   name="{$attribute_base}_data_gmaplocation_latitude_{$attribute.id}"
                   value="{$latitude}"/>
            <input class="ezgml_hidden_latitude"
                   type="hidden"
                   name="ezgml_hidden_latitude"
                   value="{$latitude}"
                   disabled="disabled"/>
        </div>

        <div class="element longitude col-md-6">
            <label>Longitudine</label>
            <input class="ezgml_new_longitude box form-control"
                   type="text"
                   name="{$attribute_base}_data_gmaplocation_longitude_{$attribute.id}"
                   value="{$longitude}"/>
            <input class="ezgml_hidden_longitude"
                   type="hidden"
                   name="ezgml_hidden_longitude"
                   value="{$longitude}"
                   disabled="disabled"/>
        </div>
    </div>

</div>

{ezcss_require(array(
    'leaflet/leaflet.0.7.2.css',
    'leaflet/geocoder/Control.Geocoder.css',
    'leaflet/Control.Loading.css'
))}
{ezscript_require(array(
    'leaflet/leaflet.0.7.2.js',
    'ezjsc::jquery',
    'leaflet/Control.Loading.js',
    'leaflet/Leaflet.MakiMarkers.js',
    'leaflet/geocoder/Control.Geocoder.js',
    'leaflet/geocoder/osmaplocation.js'
))}
