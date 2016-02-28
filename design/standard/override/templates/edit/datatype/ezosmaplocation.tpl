{if is_set( $attribute_base )|not}
    {def $attribute_base = 'ContentObjectAttribute'}
{/if}
{def $latitude  = $attribute.content.latitude|explode(',')|implode('.')
$longitude = $attribute.content.longitude|explode(',')|implode('.')}


<div class="block float-break" data-gmap-attribute="{$attribute.id}">
    <div class="element ezgml-data">
        <div id="map-{$attribute.id}" style="width: 500px; height: 280px; margin-top: 2px;"></div>

        <div class="element address">
            <input class="ezgml_new_address"
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
            <input class="ezgml_new_latitude"
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
            <input class="ezgml_new_longitude"
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

    <div class="element ezgml-form">
        <div class="block">
            <input class="box" type="text" name="query" placeholder="Ricerca libera" value=""/>
        </div>

        <div class="block">
            <input type="text" name="road" placeholder="Indirizzo" value=""/>
            <input type="text" name="house_number" placeholder="Numero" size="5" value=""/>
        </div>

        <div class="block">
            <input type="text" name="postcode" placeholder="CAP" size="10" value=""/>
        </div>

        <div class="block">
            <input type="text" name="village" placeholder="Località" size="20" value=""/>
        </div>

        <div class="block">
            <input type="text" name="city" placeholder="Città" size="20" value=""/>
        </div>

        <div class="block">
            <input type="text" name="state" placeholder="Regione" size="20" value=""/>
        </div>

        <div class="block">
            <input type="text" name="country" placeholder="Stato" size="20" value="Italia"/>
        </div>

        <button class="defaultbutton" name="GeoSearch">Cerca indirizzo</button>
        <button class="button" name="MyLocation">Rileva posizione</button>
    </div>

    <div class="element ezgml-search-results" style="display: none">

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
                        return new L.Control.Geocoder.Nominatim({geocodingQueryParams: this.address});
                    },
                    "address": {
                        "road": null,
                        "house_number": null,
                        "postcode": null,
                        "state": null,
                        "village": null,
                        "city": null,
                        "country": null
                    },
                    "search": function (query, cb, context) {
                        this.map.loadingControl.addLoader('sc');
                        var that = this;
                        this.geocoder().geocode(query, function (results) {
                            if (results.length > 0)
                                cb.call(context, results);
                            else
                                $container.find('.ezgml-search-results').empty().html("Nessun risultato, Prova a ridurre i parametri di ricerca");

                            that.map.loadingControl.removeLoader('sc');

                        });
                    },
                    "toString": function () {
                        var parts = [];
                        if (this.address.road) parts.push(this.address.road);
                        if (this.address.house_number) parts.push(this.address.house_number);
                        if (this.address.postcode) parts.push(this.address.postcode);
                        if (this.address.village) parts.push(this.address.village);
                        if (this.address.city) parts.push(this.address.city);
                        if (this.address.state) parts.push(this.address.state);
                        return parts.join(' ');
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
                    "reset": function () {
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
                        this.reset().addMarker(this.marker,true);
                        this.map.loadingControl.addLoader('sc');
                        var that = this;
                        this.geocoder().reverse(latLng, 0, function (result) {
                            that.map.loadingControl.removeLoader('sc');
                            $container.find('.ezgml-form input').val('');
                            if(result[0].properties) {
                                that.address = result[0].properties.address;
                                $.each(that.address, function (index, value) {
                                    $container.find("[name='" + index + "']").val(value);
                                });
                            }
                            $container.find('.ezgml_new_address').val(that.toString());
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

                // init userMarker
                userMarker.init(map);

                // popola il marker in base alle coordinate esistenti
                if ($container.find('.ezgml_new_latitude').val().length) {
                    userMarker.moveIn($container.find('.ezgml_new_latitude').val(), $container.find('.ezgml_new_longitude').val());
                }

                // esegue il form di ricerca
                $container.find("[name='GeoSearch']").bind('click', function (e) {
                    var query;
                    $container.find('.ezgml-form input').each(function (i, v) {
                        var input = $(v);
                        if(input.attr('name') == 'query')
                            query = input.val();
                        else
                            userMarker.address[input.attr('name')] = input.val();
                    });
                    userMarker.search(query || userMarker.toString(), function (results) {
                        userMarker.reset();
                        if(results.length) {
                            var markers = {};
                            var list = $('<ol/>');
                            $.each(results, function (index, result) {
                                var number = index + 1;
                                var latLng = new L.latLng(result.center.lat, result.center.lng);
                                var marker = new L.marker(latLng, {icon: new L.NumberedDivIcon({number: number})});
                                markers[index] = marker;
                                userMarker.addMarker(marker, false);
                                list.append(
                                        $('<li/>')
                                                .html(result.name)
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
                            userMarker.fitBounds();
                        }
                    });
                    $container.find('.ezgml-search-results').show();
                    e.preventDefault();
                });

                // intercetta il click dell'utente sulla mappa
                map.on('click', function (e) {
                    userMarker.moveIn(e.latlng.lat,e.latlng.lng);
                    $container.find('.ezgml-search-results').empty().hide();
                });

                // intercetta il MyLocation
                $container.find("[name='MyLocation']").bind('click', function (e) {
                    map.loadingControl.addLoader('lc');
                    map.locate({setView: true, watch: false})
                            .on('locationfound', function (e) {
                                map.loadingControl.removeLoader('lc');
                                userMarker.moveIn(e.latitude, e.longitude);
                            })
                            .on('locationerror', function (e) {
                                map.loadingControl.removeLoader('lc');
                                alert(e.message);
                            });
                    e.preventDefault();
                });

            });
        });
    </script>
{/literal}

{/run-once}