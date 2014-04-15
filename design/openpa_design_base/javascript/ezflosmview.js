function eZFLOsmView( blockId, data )
{
	var options = {
		projection: new OpenLayers.Projection("EPSG:900913"),
		displayProjection: new OpenLayers.Projection("EPSG:4326"),
		units: "m",
		numZoomLevels: 18,
		maxResolution: 156543.0339,
		maxExtent: new OpenLayers.Bounds(-20037508, -20037508, 20037508, 20037508.34)
	};
	var map = new OpenLayers.Map( 'ezflb-map-' + blockId, options);
	var bounds = new OpenLayers.Bounds();

	// create Google Mercator layers
	var gmap = new OpenLayers.Layer.Google( "Google Streets", {'sphericalMercator': true} );
	var gsat = new OpenLayers.Layer.Google( "Google Satellite", {type: G_SATELLITE_MAP, 'sphericalMercator': true, numZoomLevels: 22} );
	var ghyb = new OpenLayers.Layer.Google( "Google Hybrid", {type: G_HYBRID_MAP, 'sphericalMercator': true} );
	
	// create WMS layer
	var wms = new OpenLayers.Layer.WMS( "OpenLayers WMS", "http://labs.metacarta.com/wms/vmap0", {layers: 'basic'} );		

	map.addLayers([gmap, gsat, ghyb, wms]);
	map.addControl(new OpenLayers.Control.LayerSwitcher());
	map.addControl(new OpenLayers.Control.Permalink());
	map.addControl(new OpenLayers.Control.MousePosition());
	
	var startPoint = new OpenLayers.LonLat( 0, 0 ).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject()), zoom = 2;
	map.setCenter( startPoint, zoom );
	if (!map.getCenter()) {map.zoomToMaxExtent()}		

	var markers = new OpenLayers.Layer.Markers("Markers");
    map.addLayer(markers);
	
	var popups = [];
	var vectorLayers = [];
	for( var i = 0; i < data.length; i++ ){
		var point = data[i].point.transform( new OpenLayers.Projection("EPSG:4326") , map.getProjectionObject() );	
		popups.push( new OpenLayers.Popup.FramedCloud("Example"+i,
			point,
			new OpenLayers.Size(0,0),
			data[i].address,
			null,
			true) );
		popups[i].autoSize = true;
		popups[i].hide();
		map.addPopup(popups[i]);

		vectorLayers.push( new OpenLayers.Layer.Vector('Geocoded Points'+i) ); 
		feature = new OpenLayers.Feature.Vector(new OpenLayers.Geometry.Point(point.lon, point.lat));
		vectorLayers[i].addFeatures(feature);	
		//vectorLayers[i].events.register( "click", popups[i], function () { this.toggle() } );
		map.addLayer(vectorLayers[i]);		
		bounds.extend(point);
	}    
	
	$( '.marker' ).bind( 'click', function() {			
		var dataId = $(this).attr('id').replace('ezflb-pointer-' +blockId + '-', "");
		var lData = data[dataId];
		map.panTo( lData.point );
		map.setCenter( lData.point, 13 );
		for( var i = 0; i < popups.length; i++ ){
			popups[i].hide();
		}
		popups[dataId].show();
		return false;
	} );	
	
	map.setCenter( bounds.getCenterLonLat(), map.zoomToExtent( bounds ) );
}
