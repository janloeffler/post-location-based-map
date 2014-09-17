google.maps.event.addDomListener(window, 'load', init);
var map;
function init() {
    var mapOptions = {
        center: new google.maps.LatLng(47.768868,15.091552),
        zoom: 5,
        zoomControl: true,
        zoomControlOptions: { style: google.maps.ZoomControlStyle.DEFAULT },
        disableDoubleClickZoom: true,
        mapTypeControl: true,
        mapTypeControlOptions: { style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR },
        scaleControl: true,
        scrollwheel: true,
        streetViewControl: true,
        draggable : true,
        overviewMapControl: true,
        overviewMapControlOptions: { opened: false },
        mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    var mapElement = document.getElementById('map_canvas');
    var map = new google.maps.Map(mapElement, mapOptions);

    for (i = 0; i < locations.markers.length; i++) {
        marker = new google.maps.Marker({
            icon: '',
            position: new google.maps.LatLng(locations.markers[i][1], locations.markers[i][2]),
            url: locations.markers[i][3],
            title: locations.markers[i][0],
            map: map
        });

        google.maps.event.addListener(marker, 'click', function() {
            window.location.href = this.url;
        });

    }
}
