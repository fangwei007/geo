<?php
function place_info($placename) {
  $url = "http://api.geonames.org/searchJSON?q=" . $placename . "&fuzzy=0.8&maxRows=21&username=fw_007";
  
  // make the HTTP request
  $ch = curl_init ();
  curl_setopt ( $ch, CURLOPT_URL, $url );
  curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
  $data = curl_exec ( $ch );
  curl_close ( $ch );
  
  return $jsondata = json_decode ( $data, true );
}
function earthquake_info($lat, $lng, $radius) {

  $radius = round ( $radius / 111.12, 4 );
  $north = $lat + $radius;
  $south = $lat - $radius;
  $east = $lng + $radius;
  $west = $lng - $radius;
  
  $earthquake_url = "http://api.geonames.org/earthquakesJSON?north=" . $north . "&south=" . $south . "&east=" . $east . "&west=" . $west . "&username=fw_007";
  
  // make the HTTP request
  $ch = curl_init ();
  curl_setopt ( $ch, CURLOPT_URL, $earthquake_url );
  curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
  $data = curl_exec ( $ch );
  curl_close ( $ch );
  
  return $jsondata = json_decode ( $data, true );
}

function topTen() {
  $date = date('Y-m-d');
  $url = "http://api.geonames.org/earthquakesJSON?north=90&south=-90&east=180&west=-180&maxRows=10&date=".$date."&username=fw_007";

  // make the HTTP request
  $ch = curl_init ();
  curl_setopt ( $ch, CURLOPT_URL, $url );
  curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
  $data = curl_exec ( $ch );
  curl_close ( $ch );

  $array = json_decode ( $data, true );
  $topTen = $array['earthquakes'];
  echo '<h2>Top 10 in last 12 months</h2><br><ol type="1">';
  for ($i=0; $i < 10; $i++) { 
    echo '<li>Mag: '.$topTen[$i]['magnitude'].' Date: '.$topTen[$i]['datetime'].'<br>'.
    'Lat: '.$topTen[$i]['lat'].' Lng: '.$topTen[$i]['lng'].'</li>';
  }
  echo '</ol>';
}

?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="earthquake infomation">
<meta charset="utf-8">
<title>map info for earthquakes</title>
<style type="text/css">
div#container{width:500px}
div#header {background-color:#99bbbb;}
div#menu {background-color:#ffff99;float:left;}
div#content {background-color:#EEEEEE;height:200px;width:350px;float:left;}
div#sidebar {background-color:#ffEE99;height:600px;float:left;}
div#footer {background-color:#99bbbb;clear:both;text-align:center;}

</style>
<script
  src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
  <script src="geo.js"></script>
</head>
<body>
  <form class="form" method="post"
    action="<?php echo $_SERVER['PHP_SELF'];?>">
    <div id="header"><label for="email">City/Location: </label> <input type="text"
      name="city" />
    <label for="email">Radius(km): 200 default</label> 
    <input type="submit" value="Submit" name="submit" /></div>
  </form>

    <?php
        if (isset ( $_POST ['submit'] )) {
          if ($_POST ['city'] != NULL) {
            $placename = mysql_real_escape_string ( trim ( $_POST ['city'] ) );
            $placename = str_replace ( ' ', '_', $placename );
            
            $cityInfo = place_info ( $placename );
            if ($cityInfo ['totalResultsCount'] == '0')
              echo 'No city/location information!!<br>';
            else {
              $lat = $cityInfo ['geonames'] [0] ['lat'];
              $lng = $cityInfo ['geonames'] [0] ['lng'];
              $locationdata = array();
              
              echo '<div id="menu"><table border="1">';
              for($i = 0; $i < count ( $cityInfo ['geonames'] ); $i ++) {
                $earthquakeInfo = earthquake_info($cityInfo ['geonames'] [$i]['lat'], $cityInfo ['geonames'] [$i]['lng'],200);
                // var_dump($earthquakeInfo);
                if($earthquakeInfo == NULL) continue;

                //create location data
                $locationdata = '';
                $details = '';

                for($j = 0; $j < count ( $earthquakeInfo['earthquakes'] ); $j ++) {
                  $details = '';
                  foreach ($earthquakeInfo['earthquakes'][$j] as $key => $value) {
                    $details.= $key.':'.$value.'; ';
                  }
                  // $details = str_replace('"\n\r\t', ' ', $details);
                  // echo $details;
                  $locationdata = $locationdata.'['.
                    $earthquakeInfo['earthquakes'][$j]['lat'].','.$earthquakeInfo['earthquakes'][$j]['lng'].',\''.$details.'\'],';
                  if($j == count ( $earthquakeInfo['earthquakes']) -1) $locationdata = $locationdata.'['.
                    $earthquakeInfo['earthquakes'][$j]['lat'].','.$earthquakeInfo['earthquakes'][$j]['lng'].',\''.$details.'\']';
                }
                $locationdata = '['.$locationdata.']';
                // $locationdata = mysql_real_escape_string ( trim ( $locationdata) );
                // $locationdata = str_replace('"\n\r\t ', '-', $locationdata);
                // echo '<br>'.$locationdata;



                echo '<tr><td>' . $cityInfo ['geonames'] [$i] ['name'] . ', ' . $cityInfo ['geonames'] [$i] ['adminName1'] . ', ' . $cityInfo ['geonames'] [$i] ['countryCode'] . '</td>';
                echo '<td><button onclick="initialize(' . $cityInfo ['geonames'] [$i] ['lat'] . ',' . $cityInfo ['geonames'] [$i] ['lng'] . ','.$locationdata.')">Show on Map</button></td></tr>';
                // $lat = $cityInfo ['geonames'] [$i] ['lat'];
                // $lng = $cityInfo ['geonames'] [$i] ['lng'];
                // echo '<td><button onclick=initialize()>Load</button></td></tr>';
              }
              echo '</table></div>';
              
              // var_dump ( earthquake_info ( $lat, $lng, 200 ) );
            }
          } else
            echo "Nothing input!";
        }
        ?> 

 <div id="map-canvas" style="height:600px;width:630px;float:left"></div>
 
 <div id="sidebar"><?php topTen(); ?></div>
 <div id="footer">Copyright Wei Fang</div>

  </script>
 
</body>
</html>