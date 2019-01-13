<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>PLANE SIGHT</title>
  <link href="./css/StyleSheet.css" rel="stylesheet" type="text/css">
</head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<body onload="getLocation()">
<h1>PlaneSight - Easily know what's flying above!</h1>
<!--onload="getLocation()" use it for on load page-->

<!-- <button onclick="getLocation()">Try It</button> -->

<?php



    echo('<script>console.log("Start: '.((DateTime::createFromFormat('U.u', microtime(true)))->format("d-m-Y H:i:s.u")).'")</script>');
    if(isset($_GET['lat']) && isset($_GET['long'])){


        //code to pull javascript GPSlocation data into PHP

        $lat=(isset($_GET['lat']))?$_GET['lat']:'';
        //$lat=
//        echo '<br>';
        $long=(isset($_GET['long']))?$_GET['long']:'';
//        echo '<br>';

        echo('<script>console.log("GPS data grabbed: '.((DateTime::createFromFormat('U.u', microtime(true)))->format("d-m-Y H:i:s.u")).'")</script>');

        //function to convert degrees to radians
        function toRad($angle) {
            return $angle * (pi()/180);
          }
      
        //function to convert radians to degrees
        function toDegrees($angle) {
            return $angle * (180 / pi());
        }
      
        //function to calculate the postional direction point b is from point a
        
        function relPosition($posaLat, $posaLong, $posbLat, $posbLong) {
            $pta = log(tan(($posbLat / 2) + (pi() / 4)) / tan(($posaLat / 2) + (pi() / 4)));    
            if ($posaLong >= $posbLong) {
                $brng = toDegrees(atan2(($posaLong - $posbLong), $pta));
            } else {
                $brng = 360 - toDegrees(atan2(abs(($posaLong - $posbLong)), $pta));
            }
            return $brng;
            }

        function posDirection($coord) {
            $relPos = '';
            switch (round($coord/22.5)) {
                case 0: $relPos = 'N';
                break;
                case 1: $relPos = 'NNE';
                break;
                case 2: $relPos = 'NE';
                break;
                case 3: $relPos = 'ENE';
                break;
                case 4: $relPos = 'E';
                break;
                case 5: $relPos = 'ESE';
                break;
                case 6: $relPos = 'SE';
                break;
                case 7: $relPos = 'SSE';
                break;
                case 8: $relPos = 'S';
                break;
                case 9: $relPos = 'SSW';
                break;
                case 10: $relPos = 'SW';
                break;
                case 11: $relPos = 'WSW';
                break;
                case 12: $relPos = 'W';
                break;
                case 13: $relPos = 'WNW';
                break;
                case 14: $relPos = 'NW';
                break;
                case 15: $relPos = 'NNW';
                break;
                case 16: $relPos = 'N';
                break;
                default: $relPos = 'Not Specified';
                }
            return $relPos;
            }

        function headedDirection($gpsRelDirection, $flightDirection) {
            $invgPos = $gpsRelDirection > 180 ? ($gpsRelDirection - 180) : ($gpsRelDirection + 180);
        
            if ($flightDirection < 45) {
                $dirMin = 360 + ($flightDirection - 45);
                $dirMax = $flightDirection + 45;
                if ($invgPos >= $dirMin || $invgPos <= $dirMax) {
                return 0;
                } else {
                return 1;
                }
            } else if ($flightDirection > 315) {
                $dirMin = $flightDirection - 45;
                $dirMax = ($flightDirection + 45)-360;
                if ($invgPos >= $dirMin || $invgPos <= $dirMax) {
                return 0;
                } else {
                return 1;
                }
            } else {
                $dirMin = $flightDirection - 45;
                $dirMax = $flightDirection + 45;
                if ($invgPos >= $dirMin && $invgPos <= $dirMax) {
                return 0;
                } else {
                return 1;
                }
            }
            }
            
            //function to calculate the distance between 2 GPS locations 
        function gpsDistance($posaLat, $posaLong, $posbLat, $posbLong) {
            $R = 6371e3; // metres
            $φ1 = toRad($posbLat);
            $φ2 = toRad($posaLat);
            $Δφ = toRad(($posaLat-$posbLat));
            $Δλ = toRad(($posaLong-$posbLong));
            $a = sin($Δφ/2) * sin($Δφ/2) +
                    cos($φ1) * cos($φ2) *
                    sin($Δλ/2) * sin($Δλ/2);
            $c = 2 * atan2(sqrt($a), sqrt(1-$a));
            $d = $R * $c;
            return $d;
            }
        /*
        function adsbValues($adsbArray) {


            return Array($portFrom, $portTo)
        }
        */

        function array_sort($array, $on, $order=SORT_ASC){

            $new_array = array();
            $sortable_array = array();
        
            if (count($array) > 0) {
                foreach ($array as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $k2 => $v2) {
                            if ($k2 == $on) {
                                $sortable_array[$k] = $v2;
                            }
                        }
                    } else {
                        $sortable_array[$k] = $v;
                    }
                }
        
                switch ($order) {
                    case SORT_ASC:
                        asort($sortable_array);
                        break;
                    case SORT_DESC:
                        arsort($sortable_array);
                        break;
                }
        
                foreach ($sortable_array as $k => $v) {
                    $new_array[$k] = $array[$k];
                }
            }
        
            return $new_array;
        }

        //just to use Melbourne GPS coordinates while cutting code late at night and needing data to help with validation;
        //$lat = -37.814;
        //$long = 144.96;
        //Los Angeles GPS Coordinates
        //$lat = 34.0522;
        //$long = 118.2437;

        //Grab the realtime flight data from opensky-network.
        $openskyrequest = 'https://opensky-network.org/api/states/all?lamin='.((float)$lat-.2).'&lomin='.((float)$long-.2).'&lamax='.((float)$lat+.2).'&lomax='.((float)$long+.2);
        //echo $openskyrequest.'<br>';

        $ch = curl_init($openskyrequest);

        curl_setopt($ch,CURLOPT_HTTPHEADER,array('X-Parse-Application-Id: myApplicationID',
            'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $flightSourceData = json_decode(curl_exec($ch), TRUE)['states'];
        $errFlightData = curl_error($ch);
        curl_close($ch);

        echo('<script>console.log("Relatime Flight Data Grabbed: '.((DateTime::createFromFormat('U.u', microtime(true)))->format("d-m-Y H:i:s.u")).'")</script>');

        //print_r($flightSourceData);
        //echo $errFlightData;
        //echo '<BR>';
        //echo count($flightSourceData[0]);
        //echo '<br><br>';

        //function to grab the IATA name and code for a given ICAO carrier code

        function iataDetails($carrIcaoCode) {

            if ($carrIcaoCode == 'VOZ') {
                $iatacodesrequest = 'https://iatacodes.org/api/v7/airlines?api_key=ff2c00ad-be0c-4ff6-8e11-f3ae67060068&icao_code=VAU';
                } else {
                $iatacodesrequest = 'https://iatacodes.org/api/v7/airlines?api_key=ff2c00ad-be0c-4ff6-8e11-f3ae67060068&icao_code='.$carrIcaoCode;
            }
//            echo '<br>'.$iatacodesrequest.'<br>';
            $ca = 'C:\xampp\htdocs\_certs\cacert.pem';
            $ch1 = curl_init();
    
            curl_setopt_array($ch1, array(
                CURLOPT_URL => $iatacodesrequest,
                CURLOPT_HEADER => 0, 
                CURLOPT_CAINFO => realpath($ca),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => FALSE,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 90,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array("content-type: application/x-www-form-urlencoded",
                    "x-api-key: ff2c00ad-be0c-4ff6-8e11-f3ae67060068",),
                ));
    
            $result1 = json_decode(curl_exec($ch1), TRUE)['response'];
            $err = curl_error($ch1);
            curl_close($ch1);

            if (count($result1) >= 1) {
                $tempiCode = ''.$result1[0]['iata_code'];
                $tempiName = ''.$result1[0]['name'];               
                } else {
                $tempiCode = '';
                $tempiName = '';
                };
    
            return Array($tempiCode, $tempiName);
         }
      
         //Get real airport code

         function iataAirport($portIcaoCode) {

            $iataportrequest = 'https://iatacodes.org/api/v7/airports?api_key=ff2c00ad-be0c-4ff6-8e11-f3ae67060068&icao_code='.$portIcaoCode;
            
//            echo '<br>'.$iatacodesrequest.'<br>';
            $ca = 'C:\xampp\htdocs\_certs\cacert.pem';
            $ch1 = curl_init();
    
            curl_setopt_array($ch1, array(
                CURLOPT_URL => $iataportrequest,
                CURLOPT_HEADER => 0, 
                CURLOPT_CAINFO => realpath($ca),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => FALSE,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 90,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array("content-type: application/x-www-form-urlencoded",
                    "x-api-key: ff2c00ad-be0c-4ff6-8e11-f3ae67060068",),
                ));
    
            $result3 = json_decode(curl_exec($ch1), TRUE)['response'];
            $err = curl_error($ch1);
            curl_close($ch1);

            $tempPort = '';

            if (count($result3) >= 1) {
                $tempPort = ''.$result3[0]['iata_code'];             
                };
    
            return $tempPort;
         }



         //echo 'Returned value from the function : ';
         //print_r(iataDetails('VAU'));
         //echo '<br><br>';

        // Grab the ADS-B Data

        $adsbRequest = 'https://public-api.adsbexchange.com/VirtualRadar/AircraftList.json?lat='.$lat.'&lng='.$long.'&fDstL=000&fDstU=50';

        //echo $adsbRequest.'<br><br>';

        $ch2 = curl_init($adsbRequest);

        curl_setopt($ch2,CURLOPT_HTTPHEADER,array('X-Parse-Application-Id: myApplicationID',
            'Content-Type: application/json'));
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);

        $adsbSourceData = json_decode(curl_exec($ch2), TRUE)['acList'];
        $err2 = curl_error($ch2);
        curl_close($ch2);

        echo('<script>console.log("ADSB data grabbed: '.((DateTime::createFromFormat('U.u', microtime(true)))->format("d-m-Y H:i:s.u")).'")</script>');

        //print_r($adsbSourceData);
        //echo '<br><br>';
        //echo $err2;

        //build a consolidated array

        $last = count($flightSourceData) - 1;

        $consolidatedFlightData[] = Array(); 

        foreach ($flightSourceData as $i => $row)
        {
            $icaoValue = trim($flightSourceData[$i][1]);
            $iataArray[] = iataDetails(substr(trim($flightSourceData[$i][1]), 0, 3));
            $iataCode = $iataArray[0][0];
            $iataName = $iataArray[0][1];
            //echo '<br>icaoValue: '.$icaoValue.'<br>';
            //sub routine to get the ADS-B record

            $portFrom = '';
            $portTo = '';
            $acType = '';
            $acReg = '';
            $acOp = '';

            foreach ($adsbSourceData as $ai => $arow)
            {
                $adsbIcao = '';
                if (array_key_exists("Call",$adsbSourceData[$ai])) {
                    $adsbIcao = trim($adsbSourceData[$ai]['Call']);
                    if ($adsbIcao == $icaoValue) {
                        if (array_key_exists("From",$adsbSourceData[$ai]))
                            {
                                $portFrom = $adsbSourceData[$ai]['From'];
                            };
                        if (array_key_exists("To",$adsbSourceData[$ai]))
                            {
                                $portTo = $adsbSourceData[$ai]['To'];
                            };       
                        if (array_key_exists("Mdl",$adsbSourceData[$ai]))
                            {
                                $acType = $adsbSourceData[$ai]['Mdl'];
                            };     
                        if (array_key_exists("Reg",$adsbSourceData[$ai]))
                            {
                                $acReg = $adsbSourceData[$ai]['Reg'];
                            };   
                        if (array_key_exists("Op",$adsbSourceData[$ai]) && strlen($iataName)<1)
                            {
                                $acOp = $adsbSourceData[$ai]['Op'];
                            } else {
                                $acOp = $iataName;
                            };   
                    };        
                }

            };

            echo('<script>console.log("In Loop: IATA Carrier Details grabbed: '.((DateTime::createFromFormat('U.u', microtime(true)))->format("d-m-Y H:i:s.u")).'")</script>');

            $relativePos = relPosition($flightSourceData[$i][6], $flightSourceData[$i][5], $lat, $long);
            $aircraftDistance = gpsDistance($flightSourceData[$i][6], $flightSourceData[$i][5], $lat, $long);


            //$consolidatedFlightData[] = array("x"=>$resp['friends_count'],"y"=>$resp ['statuses_count']);
            $consolidatedFlightData[$i] = [$flightSourceData[$i][0], trim($flightSourceData[$i][1]), $iataCode, $acOp, $flightSourceData[$i][6], 
            $flightSourceData[$i][5], $flightSourceData[$i][10], posDirection($flightSourceData[$i][10]), $flightSourceData[$i][13], $portFrom, $portTo, 
            $relativePos, posDirection($relativePos), $aircraftDistance, headedDirection($relativePos, $flightSourceData[$i][10]),
            ((headedDirection($relativePos, $flightSourceData[$i][10])*100000)+$aircraftDistance), $acType, $acReg];

            unset($ai);
            unset($arow);
            unset($iataArray);

        };

        echo('<script>console.log("Flight Data Condolidated: '.((DateTime::createFromFormat('U.u', microtime(true)))->format("d-m-Y H:i:s.u")).'")</script>');

        $sortedFlightData = array_sort($consolidatedFlightData, 15);

        echo('<script>console.log("Flight Data Sorted: '.((DateTime::createFromFormat('U.u', microtime(true)))->format("d-m-Y H:i:s.u")).'")</script>');

        //print_r($consolidatedFlightData);
        //echo '<br><br>';
        //print_r($sortedFlightData);

        $prevDirection = -1;
        $currDirection = -1;
        foreach ($sortedFlightData as $af => $arow)
        {
            $flightNumber = $sortedFlightData[$af][2].substr($sortedFlightData[$af][1], 3, strlen($sortedFlightData[$af][1]));
            
            $flightHeader = '';
            if (strlen($sortedFlightData[$af][1]) > 3) {
              $flightHeader = $sortedFlightData[$af][3].' flight - '.$flightNumber.'    '.iataAirport(substr($sortedFlightData[$af][9],0,4)).' -> '.iataAirport(substr($sortedFlightData[$af][10],0,4));
              } else {
              $flightHeader = $sortedFlightData[$af][3].' flight - '.$sortedFlightData[$af][1];
              };

            echo '<br>';

            echo '<div class="flight-group">';
            echo '<div class="flight-group-headline">';
            $currDirection = $sortedFlightData[$af][14];
            if ($currDirection != $prevDirection) {
                if ($currDirection==0) {
                    echo 'Flights headed towards you<br>';
                    } else {
                    echo 'Flights headed away from you<br>';
                }
            }
            echo '</div>';
            echo '<div class"flight-box"><div class="flight-headline">'.$flightHeader.'</div>';
            echo '<div class="flight-description">Aircraft: '.$sortedFlightData[$af][16].'    Registration: '.$sortedFlightData[$af][17];
            echo '<br>Distance from your location: '.(round($sortedFlightData[$af][13]/100)/10);
            echo 'kms away, '.round($sortedFlightData[$af][11]).' degrees ';
            echo $sortedFlightData[$af][12].'.<br>Current Aircraft Position: Latitude - ';
            echo $sortedFlightData[$af][4].', Longitude - '.$sortedFlightData[$af][5];
            echo ', Altitude - '.$sortedFlightData[$af][8].'ms.<br>Direction headed: '.$sortedFlightData[$af][6].' degrees ';
            echo $sortedFlightData[$af][7];

            echo '</div></div>';
            echo '</div>';
            $prevDirection=$currDirection;

            echo('<script>console.log("Data Displayed: '.((DateTime::createFromFormat('U.u', microtime(true)))->format("d-m-Y H:i:s.u")).'")</script>');

            //echo 
            //print_r($sortedFlightData[$af]);
           };

           echo('<script>console.log("Finished: '.((DateTime::createFromFormat('U.u', microtime(true)))->format("d-m-Y H:i:s.u")).'")</script>');
//    echo '<br><br><br>';
//    print_r($sortedFlightData);
    ?>
<?php
    } else {
    ?>



<script>

    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(redirectToPosition);
        } else {
            x.innerHTML = "Geolocation is not supported by this browser.";
        }
    }

    function redirectToPosition(position) {
        window.location='plainsight.php?lat='+position.coords.latitude+'&long='+position.coords.longitude;
    }
</script>
<?php
    }
?>
</body>
</html>