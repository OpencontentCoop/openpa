{if is_set( $attribute_base )|not}
    {def $attribute_base = 'ContentObjectAttribute'}
{/if}
{def $latitude  = $attribute.content.latitude|explode(',')|implode('.')
     $longitude = $attribute.content.longitude|explode(',')|implode('.')}

<div class="block float-break" data-gmap-attribute="{$attribute.id}">
    <div class="element ezgml-data">
        <div id="map-{$attribute.id}" style="width: 500px; height: 280px; margin-top: 2px;"></div>

        <div class="block address">
            <label>Indirizzo</label>
            <input class="ezgml_new_address box"
                   type="text"
                   name="{$attribute_base}_data_gmaplocation_address_{$attribute.id}"
                   value="{$attribute.content.address}"/>
            <input class="ezgml_hidden_address"
                   type="hidden"
                   name="ezgml_hidden_address"
                   value="{$attribute.content.address}"
                   disabled="disabled"/>
        </div>

        <div class="element latitude">
            <label>Latitudine</label>
            <input class="ezgml_new_latitude box"
                   type="text"
                   name="{$attribute_base}_data_gmaplocation_latitude_{$attribute.id}"
                   value="{$latitude}"/>
            <input class="ezgml_hidden_latitude"
                   type="hidden"
                   name="ezgml_hidden_latitude"
                   value="{$latitude}"
                   disabled="disabled"/>
        </div>

        <div class="element longitude">
            <label>Longitudine</label>
            <input class="ezgml_new_longitude box"
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

    <div class="element square-box-soft-gray ezgml-form">

        <div class="ezgml-form-fields">
            <h4>Cerca un punto sulla mappa</h4>
            <div class="block">
                <input class="box" type="hidden" name="query" placeholder="Query" value=""/>
            </div>

            <div class="block">
                <label>Indirizzo</label>
                <input type="text" class="box" name="street" placeholder="Indirizzo" value=""/>
            </div>

            {*<div class="block">
                <label>Numero</small>
                 <input type="text" class="box" name="house_number" placeholder="Numero" size="5" value=""/>
            </div>*}

            <div class="block">
                <label>CAP</label>
                 <input type="text" name="postcode" placeholder="CAP" size="10" value=""/>
            </div>

            <div class="block">
                <label>Città</label>
                 <input type="text" name="city" placeholder="City" size="20" value=""/>
            </div>

            <div class="block">
                <label>Provincia</label>
                 <input type="text" name="county" placeholder="Provincia" size="20" value="Provincia Autonoma di Trento"/>
            </div>

            <div class="block">
                <label>Regione</label>
                 <input type="text" name="state" placeholder="Regione" size="20" value="Trentino-Alto Adige"/>
            </div>

            <div class="block">
                <label>Stato</label>
                 <input type="text" name="country" placeholder="Stato" size="20" value="Italia"/>
            </div>
        </div>

        <button class="defaultbutton" name="GeoSearch">Cerca indirizzo</button>
        <button class="button" name="MyLocation">Rileva posizione</button>
        <button class="button" name="Reset">Annulla</button>
    </div>

    <div class="element square-box-soft-gray ezgml-search-results" style="display: none;">

    </div>
</div>

{ezcss_require(array(
'leaflet/leaflet.0.7.2.css',
'leaflet/Control.Loading.css',
'leaflet/MarkerCluster.css',
'leaflet/MarkerCluster.Default.css'
))}
{ezscript_require(array(
'leaflet/leaflet.0.7.2.js',
'ezjsc::jquery',
'leaflet/leaflet.activearea.js',
'leaflet/leaflet.markercluster.js',
'leaflet/Leaflet.MakiMarkers.js',
'leaflet/Control.Geocoder.js',
'leaflet/Control.Loading.js'
))}

{run-once}
{literal}
    <style>

        .leaflet-div-icon {
            background: transparent;
            border: none;
        }
        p .leaflet-div-icon{

        }

        .ezgml-data input{
            border: none;
        }

        .ezgml-form-fields p{
            margin:0
        }

        .ezgml-form-fields label{
            display: block;
            line-height: 0.5;
            font-weight: bold;
        }

        .ezgml-form-fields p small.key{
            font-style: italic;
            padding-right: 5px;
        }

        .ezgml-form-fields small.key{
            margin:0
        }

        .ezgml-search-results {
            position: relative;
            max-width: 400px;
            width: 400px;
        }

        .ezgml-search-results .leaflet-marker-icon{
            position: relative;
            float: left;
            padding-right: 10px;
            height: 41px;
        }

        .ezgml-search-results a.close {
            position: absolute;
            right: 3px;
            top: 3px;
            font-family: sans-serif;
            font-weight: bold;
        }

        .leaflet-marker-icon .number {
            background: #fff none repeat scroll 0 0;
            border-radius: 20px;
            color: #000;
            font-size: 1em;
            font-weight: bold;
            height: 21px;
            line-height: 21px;
            margin-left: 2px;
            position: relative;
            text-align: center;
            top: -42px;
            width: 21px;
        }
    </style>
    <script type="text/javascript">

        (function(){
            // Your base, I'm in it!
            var originalAddClassMethod = jQuery.fn.addClass;

            jQuery.fn.addClass = function(){
                // Execute the original method.
                var result = originalAddClassMethod.apply( this, arguments );

                // trigger a custom event
                jQuery(this).trigger('cssClassChanged');

                // return the original result
                return result;
            }
        })();

        L.NumberedDivIcon = L.Icon.extend({
            options: {
                iconUrl: 'http://cdn.leafletjs.com/leaflet/v0.7.7/images/marker-icon.png',
                number: '',
                shadowUrl: null,
                iconSize: new L.Point(25, 41),
                iconAnchor: new L.Point(13, 41),
                popupAnchor: new L.Point(0, -33),
                className: 'leaflet-div-icon'
            },

            createIcon: function () {
                var div = document.createElement('div');
                var img = this._createImg(this.options['iconUrl']);
                var numdiv = document.createElement('div');
                numdiv.setAttribute("class", "number");
                numdiv.innerHTML = this.options['number'] || '';
                div.appendChild(img);
                div.appendChild(numdiv);
                this._setIconStyles(div, 'icon');
                return div;
            },

            //you could change this to add a shadow like in the normal marker if you really wanted
            createShadow: function () {
                return null;
            }
        });

        $(document).ready(function () {
            $("[data-gmap-attribute]").each(function () {

                // variables
                var $container = $(this);
                var attributeId = $container.data('gmap-attribute');
                var map;
                var userMarker = {
                    "lat": 0,
                    "lng": 0,
                    "map": null,
                    "marker": null,
                    "markers": null,
                    "geocoder": function () {
                        //return new L.Control.Geocoder.Nominatim({geocodingQueryParams: this.address});
                        return new L.Control.Geocoder.Nominatim();
                    },
                    "address": {
                        "street": null,
//                        "house_number": null,
                        "postcode": null,
                        "state": null,
                        "country": null,
                        "county": null
                    },
                    "search": function (query, cb, context) {
                        this.map.loadingControl.addLoader('sc');
                        var that = this;
                        this.geocoder().geocode(query, function (results) {
//                            console.log(results);
                            if (results.length > 0)
                                cb.call(context, results);
                            else {
                                var close = $('<a href="#">Riprova</a>');
                                close.bind('click',function(e){
                                    $container.find('.ezgml-search-results').empty().hide();
                                    $container.find('.ezgml-form').show();
                                    e.preventDefault();
                                });
                                $container.find('.ezgml-search-results').empty().append("<h4>Nessun risultato</h4><p>Riprova riducendo i parametri di ricerca oppure scrivendo l'indirizzo completo (ad esempio \"Corso Antonio Rosmini\, \"Via Giovanni Segantini\", ...)<p>").append(close);
                            }

                            that.map.loadingControl.removeLoader('sc');

                        });
                    },
                    "text": null,
                    "toQuery": function () {
                        var parts = [];
                        if (this.address.street) parts.push(this.address.street);
//                        if (this.address.house_number) parts.push(this.address.house_number);
                        if (this.address.city) parts.push(this.address.city);
                        if (this.address.state) parts.push(this.address.state);
                        if (this.address.postcode) parts.push(this.address.postcode);
                        if (this.address.country) parts.push(this.address.country);
                        return parts.join(', ');
                    },
                    "init": function (map, lat, lng) {
                        this.lat = lat || 0;
                        this.lng = lng || 0;
                        this.map = map;
                        this.marker = new L.marker(
                                new L.latLng(this.lat, this.lng), {
                                    icon: new L.MakiMarkers.icon({
                                        icon: "star",
                                        color: "#f00",
                                        size: "l"
                                    }),
                                    draggable: true
                                }
                        );
                        var that = this;
                        this.marker.on('dragend', function (event) {
                            var position = event.target.getLatLng();
                            that.moveIn(position.lat, position.lng);
                        });
                        this.markers = new L.markerClusterGroup();
                        return this;
                    },
                    "reset": function(){
                        if ($container.find('.ezgml_hidden_latitude').val().length) {
                            this.text = $container.find('.ezgml_hidden_address').val();
                            this.moveIn(
                                $container.find('.ezgml_hidden_latitude').val(),
                                $container.find('.ezgml_hidden_longitude').val()
                            );
                        }
                    },
                    "resetMakers": function () {
                        this.markers.clearLayers();
                        return this;
                    },
                    "addMarker": function (marker, fit) {
                        this.markers.addLayer(marker).addTo(this.map);
                        if (fit) this.map.fitBounds(this.markers.getBounds());
                        return this;
                    },
                    "fitBounds": function () {
                        this.map.fitBounds(this.markers.getBounds());
                        return this;
                    },
                    "moveIn": function (lat, lng) {
                        this.lat = lat || 0;
                        this.lng = lng || 0;
                        var latLng = new L.latLng(this.lat, this.lng);
                        this.marker.setLatLng(latLng);
                        this.resetMakers().addMarker(this.marker,true);
                        this.map.loadingControl.addLoader('sc');
                        var that = this;
                        this.geocoder().reverse(latLng, 0, function (result) {
//                            console.log(result);
                            that.map.loadingControl.removeLoader('sc');
                            $container.find('.ezgml-form-fields p').remove();
                            $container.find('.ezgml-form input').val('');
                            if(result[0].properties) {
                                that.address = $.extend({}, {street:that.address.street}, result[0].properties.address );
                                $.each(that.address, function (index, value) {
                                    if ( $container.find("[name='" + index + "']").length > 0) {
                                        $container.find("[name='" + index + "']").val(value);
                                    }else {
                                        $container.find('.ezgml-form-fields').append('<p><small class="key">' + index + ':</small><small>' + value + '</small></p>');
                                    }
                                });
                                if(that.text == null){
                                    that.text = result[0].name;
                                }
                            }
                            $container.find('.ezgml_new_address').val(that.text);
                            $container.find('.ezgml_new_latitude').val(that.lat);
                            $container.find('.ezgml_new_longitude').val(that.lng);

                        });
                        return this;
                    }
                };

                // init map
                map = new L.Map('map-' + attributeId, {loadingControl: true});
                //map.scrollWheelZoom.disable();
                L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);
                map.setView(new L.latLng(0, 0), 1);

                if ( $('a[data-toggle="tab"').length > 0) {
                    $('a[data-toggle="tab"]').bind('shown.bs.tab', function (e) {
                        map.invalidateSize(false);
                    });
                }
                else if ( $(".ui-tabs .ui-tabs-nav").length > 0) {
                    $(".ui-tabs .ui-tabs-panel").bind("cssClassChanged", function (event, ui) {
                        map.invalidateSize(false);
                    });
                }

                // init userMarker
                userMarker.init(map);

                // popola il marker in base alle coordinate esistenti
                userMarker.reset();

                $container.find('.ezgml-form input').bind('keypress', function(e) {
                    if(e.which == 13){
                        $container.find("[name='GeoSearch']").trigger('click');
                        e.preventDefault();
                    }
                });

                // esegue il form di ricerca
                $container.find("[name='GeoSearch']").bind('click', function (e) {
                    var query = null;
                    $container.find('.ezgml-form input').each(function (i, v) {
                        var input = $(v);
                        if(input.attr('name') == 'query')
                            query = input.val();
                        else
                            userMarker.address[input.attr('name')] = input.val();
                    });
                    query = userMarker.toQuery();
                    $container.find("[name='query']").val(query);
                    userMarker.search(query, function (results) {
                        userMarker.text = query;
                        userMarker.resetMakers();
                        $container.find('.ezgml-form-fields p').remove();
                        if(results.length) {
                            var markers = {};
                            var list = $('<div/>');

                            var close = $('<a class="close" href="#" title="Chiudi risultati e torna al form di ricerca">X</a>');
                            close.bind('click',function(e){
                                $container.find('.ezgml-search-results').empty().hide();
                                $container.find('.ezgml-form').show();
                                e.preventDefault();
                            });
                            list.append(close);

                            list.append('<h4>Risultati della ricerca:</h4>');
                            if(results.length > 1) {
                                $.each(results, function (index, result) {
                                    var number = index + 1;
                                    var latLng = new L.latLng(result.center.lat, result.center.lng);
                                    var marker = new L.marker(latLng, {icon: new L.NumberedDivIcon({number: number})});
                                    marker.on('click', function(e){
                                        if (e.latlng !== undefined) {
                                            userMarker.moveIn(e.latlng.lat, e.latlng.lng);
                                            $container.find('.ezgml-search-results').empty().hide();
                                            $container.find('.ezgml-form').show();
                                        }
                                    });
                                    markers[index] = marker;
                                    userMarker.addMarker(marker, false);
                                    var icon = $('<div class="leaflet-marker-icon"><img src="http://cdn.leafletjs.com/leaflet/v0.7.7/images/marker-icon.png"><div class="number">'+number+'</div></div>');
                                    list.append(
                                            $('<p class="float-break"/>')
                                                    .append(icon)
                                                    .append(result.name)
                                                    .css({"cursor": 'pointer'})
                                                    .bind('click', function () {
                                                        userMarker.markers.zoomToShowLayer(marker,
                                                            function () {
                                                                marker.fire('click');
                                                            }
                                                        );
                                                    })
                                    );
                                });
                                $container.find('.ezgml-search-results').empty().append(list);
                            }else{
                                userMarker.moveIn(results[0].center.lat,results[0].center.lng);
                                $container.find('.ezgml-search-results').empty().hide();
                                $container.find('.ezgml-form').show();
                            }
                            userMarker.fitBounds();
                        }
                    });
                    $container.find('.ezgml-form').hide();
                    $container.find('.ezgml-search-results').show().append('<p>Attendere caricamento ...</p>');
                    e.preventDefault();
                });

                // intercetta il click dell'utente sulla mappa
                map.on('click', function (e) {
                    userMarker.moveIn(e.latlng.lat,e.latlng.lng);
                    $container.find('.ezgml-search-results').empty().hide();
                    $container.find('.ezgml-form').show();
                });

                // intercetta il MyLocation
                $container.find("[name='MyLocation']").bind('click', function (e) {
                    map.loadingControl.addLoader('lc');
                    map.locate({setView: true, watch: false})
                            .on('locationfound', function (e) {
                                map.loadingControl.removeLoader('lc');
                                userMarker.address.street = null;
                                userMarker.text = null;
                                userMarker.moveIn(e.latitude, e.longitude);
                            })
                            .on('locationerror', function (e) {
                                map.loadingControl.removeLoader('lc');
                                alert(e.message);
                            });
                    e.preventDefault();
                });

                // intercetta il Reset
                $container.find("[name='Reset']").bind('click', function (e) {
                    userMarker.address.street = null;
                    userMarker.reset();
                    e.preventDefault();
                });

            });
        });
    </script>
{/literal}

{/run-once}