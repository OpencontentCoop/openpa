function eZFLGMapView( id, center, zoom, json ){
    var markers = [];
    var mapProp = {
        center:center,
        zoom:zoom,
        mapTypeId:google.maps.MapTypeId.ROADMAP
    };
    var map=new google.maps.Map(document.getElementById(id),mapProp);
    var infowindow =  new google.maps.InfoWindow({
        content: ""
    });
    for (var i = 0, length = json.length; i < length; i++) {
        var data=json[i];
        var latLng = new google.maps.LatLng(data.lat, data.lng);         
        var marker = new google.maps.Marker({
            position: latLng,
            map: map,
            title: data.title
        });
        bindInfoWindow(marker, map, infowindow, data.description, id, i);
    } 
}

function bindInfoWindow(marker, map, infowindow, strDescription, id, index) {    
    var a = document.getElementById(id+'-'+index);
    google.maps.event.addListener(marker, 'click', function() {
        infowindow.setContent(strDescription);
        infowindow.open(map, marker);
    });
    google.maps.event.addDomListener(a, 'click', function(e) {
        infowindow.setContent(strDescription);
        infowindow.open(map, marker);
        e.preventDefault();        
    });
}
