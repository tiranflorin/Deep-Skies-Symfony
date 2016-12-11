$(window).ready(function () {
    var addressMarkup = document.getElementById('address-name');
    if (addressMarkup) {
        initialize();
        google.maps.event.addDomListener(window, 'load', initialize);
    }
});

var gmap;
var gmarker;

function initialize() {
    var lat = 45.7740482, long = 25.2736636;
    var customLat = document.getElementById('address-lat').value;
    var customLong = document.getElementById('address-long').value;

    if (customLat && customLong) {
        lat = customLat;
        long = customLong;
    }

    var mapOptions = {
        center: new google.maps.LatLng(lat, long),
        zoom: 6,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    gmap = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
}

function searchAddress() {
    var addressInput = document.getElementById('address-name').value;
    var geocoder = new google.maps.Geocoder();

    geocoder.geocode({address: addressInput}, function (results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            var myResult = results[0].geometry.location;

            createMarker(myResult);
            gmap.setCenter(myResult);
            gmap.setZoom(7);

            document.getElementById('address-name').value = results[0].formatted_address;
            document.getElementById('address-lat').value = myResult.lat().toFixed(4);
            document.getElementById('address-long').value = myResult.lng().toFixed(4);
            document.getElementById('address-name-warning').innerHTML = '';
        } else {
            document.getElementById('address-name-warning').innerHTML = '! The above address was not found.';
        }
    });
}

function createMarker(latlng) {
    if (gmarker != undefined && gmarker != '') {
        gmarker.setMap(null);
        gmarker = '';
    }

    gmarker = new google.maps.Marker({
        map: gmap,
        position: latlng
    });
}
