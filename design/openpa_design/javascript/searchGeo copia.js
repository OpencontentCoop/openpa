jQuery(function($) {	
	search = false;
	if (data['search'] != '') search = data['search'];
	
	map = new GMap2(document.getElementById("map_canvas"));	
	map.addControl( new GMapTypeControl() );
	map.addControl( new GLargeMapControl() );	

	geocoder = new GClientGeocoder;
	bounds = new GLatLngBounds();
	
	var geoSearch = function(_search) {
		map.clearOverlays();
		geocoder.getLocations(_search, function(result){
			if (result.Status.code == G_GEO_SUCCESS){							
				if (result.Placemark.length > 1){
					var list = $('<ol />');
					var listTitle = $('<strong>'+ geoSearchRegional['Trovati'] + ' ' + result.Placemark.length + ' ' + geoSearchRegional['indirizzi. Clicca su uno dei seguenti'] +': </strong>');
					for (var i = 0; i < result.Placemark.length; i++){
						p = result.Placemark[i].Point.coordinates;
						marker = new GMarker( new GLatLng( p[1], p[0] ) );
						address = result.Placemark[i].address;
						resultItem = $("<li class='result-item' id='" + result.Placemark[i].id + "'>" + result.Placemark[i].address + "</li>");
						list.append(resultItem);
						resultItem.bind('click', {str: result.Placemark[i].address}, function(event) {
							geoSearch(event.data.str);
						  });
						if (i == 0){
							map.addOverlay( marker );
							map.setCenter( new GLatLng( p[1], p[0] ), 14 );
						}
					}
					print( listTitle, 1 );
					print( list );
				}else{
					p = result.Placemark[0].Point.coordinates;
					var blueIcon = new GIcon(G_DEFAULT_ICON);
					blueIcon.image = "http://www.google.com/intl/en_us/mapfiles/ms/micons/blue-dot.png";
					blueIcon.iconSize = new GSize(32, 32);
					marker = new GMarker( new GLatLng( p[1], p[0] ), { icon: blueIcon } );
					address = result.Placemark[0].address;
					$('#'+searchInputId).val(result.Placemark[0].address);					
					map.addOverlay( marker );
					map.setCenter( new GLatLng( p[1], p[0] ), 10 );					
					postdata = new postdata(p[1], p[0]);
					querySearch(postdata, marker);
				}
			}else{
				var reason = "Code: " + result.Status.code;
				if ( reasons[result.Status.code] ){
					reason = reasons[result.Status.code];
				}
				print("<p class='error'><strong>" + reason + "</strong></p>");
			}
		});	
	}

	if (search != '') geoSearch(search);
	else print("<p class='error'><strong>" + reasons[G_GEO_MISSING_ADDRESS] + "</strong></p>");

	
	function print(data, reset){
		if (!reset){
			$('#'+resultDivId).append( data );
		}else{
			$('#'+resultDivId).html( data );
		}
	}
	
	function querySearch(postdata, startMarker){		
		print('<div class="spinner"></div>', 1);
		$.ez( 'ezjscsearch::geosearch', postdata, function( data ){ 
			if ( data.error_text )
				print( data.error_text );
			else
				if (data.content.this_results_count > 0)
					displayResults( data.content, startMarker, postdata );
				else
					displayNoResults();
		});
	}
	
	function displayNoResults(){
		print( '<h2><strong>' + geoSearchRegional['Nessun risultato'] + '</strong></h2>', 1 );
	}
	
	function displayResults( searchResults, startMarker, postdata ){		

		map.clearOverlays();
		map.addOverlay( startMarker );
		bounds.extend( startMarker.getLatLng() );
		
		print( '<h2><strong>'+ geoSearchRegional['Trovati'] + ' ' + searchResults.results_count + ' ' + geoSearchRegional['risultati'] + ':</strong></h2>', 1 );
		
		var markerResult=[];
		var class_identifier=[];
		
		for (var i = 0; i < searchResults.this_results_count; i++){
			var searchResult = searchResults.results[i];
			
			if ( class_identifier[searchResult.class_identifier] )
				class_identifier[searchResult.class_identifier]++;
			else
				class_identifier[searchResult.class_identifier] = 1;
			
			var markerPoint = new GLatLng( searchResult.lat, searchResult.lng );			
					
			var container = $('<div id="node-' + searchResult.node_id + '" class="close_by float-break" />');			
			
			var image = '<a href="' + baseUrl + '/' + searchResult.url + '" title="' + searchResult.title + '"><img src="/' + searchResult.image + '" alt="' + searchResult.title + '" /></a>';
			var imageContainer = $('<div class="preview" />');
			imageContainer.html( image );
			container.append(imageContainer);
			
			var item = '<a href="' + baseUrl + '/' + searchResult.url + '" title="' + searchResult.title + '">' + searchResult.title + '<span>[' + searchResult.class_name + ']</span> <span>' + searchResult.address + '</span></a>';
			var itemContainer = $('<div class="address" />');
			itemContainer.html( item );			
			container.append(itemContainer);			
			
			var markerHtml = '<div class="close_by float-break"><div class="preview">'+image+'</div><div class="address">'+item+'</div></div>';			
			markerResult[i] = createMarker( markerPoint, markerHtml  );
			$('a', imageContainer).bind('click', {str: markerHtml, index:i}, function(event) {
				event.preventDefault();
				markerResult[event.data.index].openInfoWindowHtml(event.data.str);
			});
			
			map.addOverlay( markerResult[i] );
			bounds.extend( markerPoint );			
			print(container);
		}
		
		map.setZoom( map.getBoundsZoomLevel( bounds ) );
		map.setCenter( bounds.getCenter() );
				
		var next = false;
		var prev = false;
		if ( ( postdata.offset == 0 && ( postdata.limit < searchResults.results_count ) ) || ( ( postdata.offset + postdata.limit ) < searchResults.results_count ) ) next = true;
		if ( postdata.offset > 0 ) prev = true		
		var pages = Math.ceil( searchResults.results_count / postdata.limit );
		var currentPage = ( postdata.offset / postdata.limit) + 1;		
		var navigation = $('<div class="pagenavigator float-break" />');				
		if (prev){			
			var prevItem = $('<span class="previous">' + geoSearchRegional['precedenti'] + '</span>');
			navigation.append(prevItem);
			prevItem.bind('click', {postdata: postdata, startMarker: startMarker}, function(event) {
				postdata.offset -= postdata.limit;
				querySearch(postdata, startMarker)
			});			
		}				
		if (next){			
			var nextItem = $('<span class="next">' + geoSearchRegional['successivi'] + '</span>');
			navigation.append(nextItem);
			nextItem.bind('click', {postdata: postdata, startMarker: startMarker}, function(event) {
				postdata.offset += postdata.limit;
				querySearch(postdata, startMarker)
			});			
		}
		if ( pages > 1 ){
			var navigationItem = $('<span class="pages">' + currentPage + ' ' + geoSearchRegional['di'] + ' ' + pages + '</span>');
			navigation.append(navigationItem);
		}			
		print(navigation);		
	}
	
	function postdata( lat, lng, distance, limit, offset, section, classes ){
		
		if (lat) this.lat = lat;
		else if (data['lat'] != '')  this.lat = data['lat'];
		
		if (lng) this.lng = lng;
		else if (data['lng'] != '') this.lng = data['lng'];
		
		if (distance) this.distance = distance;
		else if (data['distance'] != '') this.distance = data['distance'];
		
		if (limit) this.limit = parseInt( limit );
		else if (data['limit'] != '') this.limit = parseInt( data['limit'] );
		else this.limit = parseInt( 5 );
		
		if (offset) this.offset = parseInt( offset );
		else if (data['offset'] != '') this.offset = parseInt( data['offset'] );
		else this.offset = parseInt( 0 );
		
		if (section) this.section = section;
		else if (data['section'] != '') this.section = data['section'];
		
		if (classes) this.classes = classes;
		else if (data['classes'] != '') this.classes = data['classes'];
		
		this.http_accept = 'json';
	}

	function createMarker(point,html)
	{
		var marker = new GMarker(point);
		GEvent.addListener(marker, "click", function(){
			marker.openInfoWindowHtml(html);
			}
		);
		return marker;
	}

});