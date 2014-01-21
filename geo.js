function initialize(lat, lng , locationdata) {
  // alert(locationdata);
  var myLatlng = new google.maps.LatLng(lat, lng);
  var mapOptions = {
    zoom: 7,
    center: myLatlng
  };

  var LocationData = locationdata;//<?php echo $locationdata; ?>//new
  var infowindow = new google.maps.InfoWindow();
  var bounds = new google.maps.LatLngBounds();

  var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

  for (var i in LocationData)
  {
    var p = LocationData[i];
    var latlng = new google.maps.LatLng(p[0], p[1]);
    bounds.extend(latlng);

    // var infowindow = new google.maps.InfoWindow({
    //   content: p[2],
    //   maxWidth: 200
    // });

    var marker = new google.maps.Marker({
        position: latlng,
        map: map,
        title: p[2]
    });

     google.maps.event.addListener(marker, 'click', function() {
        infowindow.setContent(this.title);
        infowindow.open(map, this);
    });
     
  }
}

google.maps.event.addDomListener(window, 'load', initialize);
