$(document).ready(function () {
    $("[data-osmap-attribute]").each(function () {
        var
            $container = $(this),

            attributeId = $container.data('osmap-attribute'),

            originalLat = $container.find('.ezgml_hidden_latitude').val(),
            originalLng = $container.find('.ezgml_hidden_longitude').val(),
            originalText = $container.find('.ezgml_hidden_address').val(),

            inputLat = $container.find('.ezgml_new_latitude'),
            inputLng = $container.find('.ezgml_new_longitude'),
            inputText = $container.find('.ezgml_new_address'),

            resetButton = $container.find("[name='Reset']").on('click', function (e) {
                reset();
                e.preventDefault();
            }),

            myLocationButton = $container.find("[name='MyLocation']"),

            map = new L.Map('map-' + attributeId, {loadingControl: true})
                .setView(new L.latLng(0, 0), 1),

            //geocoder = L.Control.Geocoder.mapzen('search-DopSHJw'),
            geocoder = L.Control.Geocoder.nominatim(),
            control = L.Control.geocoder({
                //geocoder: geocoder,
                collapsed: false,
                placeholder: 'Cerca...',
                errorMessage: 'Nessun risultato.',
                suggestMinLength: 5,
                defaultMarkGeocode: false
            }).on('markgeocode', function (e) {
                setMarker(e.geocode.center, e.geocode.name);
                map.fitBounds(e.geocode.bbox);
                if (originalLat.length) {
                    resetButton.show();
                }
            }).addTo(map),

            marker = new L.marker(new L.latLng(0, 0), {
                icon: new L.MakiMarkers.icon({icon: "star", color: "#f00", size: "l"}),
                draggable: true
            }).on('dragend', function (event) {
                var target = event.target.getLatLng();
                map.loadingControl.addLoader('lc');
                geocoder.reverse(target, 1, function (data) {
                    map.loadingControl.removeLoader('lc');
                    if (data.length)
                        setMarker(target, data[0].name);
                    else
                        setMarker(target);
                });
                if (originalLat.length) {
                    resetButton.show();
                }
            }),

            setMarker = function (latLng, text) {
                inputText.val(text);
                inputLat.val(latLng.lat);
                inputLng.val(latLng.lng);
                map.removeLayer(marker);
                marker.setLatLng(latLng).addTo(map);
            },

            reset = function () {
                if (originalLat.length) {
                    var latLng = new L.latLng(originalLat, originalLng);
                    setMarker(latLng, originalText);
                    map.setView(latLng, 18);
                    resetButton.hide();
                }
            };

        reset();

        myLocationButton.bind('click', function (e) {
            map.loadingControl.addLoader('lc');
            map.locate({setView: true, watch: false})
                .on('locationfound', function (e) {
                    map.loadingControl.removeLoader('lc');
                    var target = new L.latLng(e.latitude, e.longitude);
                    geocoder.reverse(target, 1, function (data) {
                        if (data.length)
                            setMarker(target, data[0].name);
                        else
                            setMarker(target);
                    });
                })
                .on('locationerror', function (e) {
                    map.loadingControl.removeLoader('lc');
                    alert(e.message);
                });
            e.preventDefault();
        });

        L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
    });
});
