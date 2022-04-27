<?php
$mysqli = new mysqli('localhost', 'db_username', 'db_password', 'db_name');

$map_id = 0;
if (isset($_GET['map']) && intval($_GET['map']) >= 0) {
	$map_id = intval($_GET['map']);
}

$marker_type = -1;
if (isset($_GET['type']) && intval($_GET['type']) >= 0) {
	$marker_type = intval($_GET['type']);
}

$marker_id = -1;
if (isset($_GET['id']) && intval($_GET['id']) >= 0) {
	$marker_id = intval($_GET['id']);
}

$marker_x = -1;
if (isset($_GET['x']) && intval($_GET['x']) >= 0) {
	$marker_x = intval($_GET['x']);
}

$marker_y = -1;
if (isset($_GET['y']) && intval($_GET['y']) >= 0) {
	$marker_y = intval($_GET['y']);
}

$maps = array();
$query = "SELECT * FROM `maps` WHERE 1";
$result = $mysqli->query($query);
if ($result -> num_rows != 0) {
	while ($line = $result->fetch_assoc()) { 
        array_push($maps, $line);
	}
}

$map = null;
$query = "SELECT * FROM `maps` WHERE `id` = '".$map_id."' OR `id` = '0'";
$result = $mysqli->query($query);
if ($result -> num_rows != 0) {
	while ($line = $result->fetch_assoc()) { 
		$map = $line;
	}
}

/* Определение языка страницы */
$lang = "en";
$availableLangs = array("en", "ru");
if (isset($_GET['lang'])) {
	if (in_array($_GET['lang'], $availableLangs)) {
		setcookie("lang", $_GET['lang'], strtotime( '+30 days' ), '/', "phasmomap.online");
		$lang = $_GET['lang'];
	} else {
		$lang = getLangFromCookie($availableLangs);
	}
} else { $lang = getLangFromCookie($availableLangs); }

function getLangFromCookie($availableLangs) {
	if (!isset($_COOKIE['lang'])) {
		$lang = 'en';
		
		$sites = array(
			"en" => "en",
			"ru" => "ru",
			"be" => "ru",
			"uk" => "ru",
			"ky" => "ru",
			"ab" => "ru",
			"mo" => "ru",
			"et" => "ru",
			"lv" => "ru"
		);
		
		// получаем язык
		$lang = !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? strtok(strip_tags($_SERVER['HTTP_ACCEPT_LANGUAGE']), ',') : '';
		$lang = substr($lang, 0,2);
		
		// проверяем язык
		if (!in_array($lang, array_keys($sites))){
			$lang = 'en';
		}
		if (in_array($sites[$lang], $availableLangs)) {
			$lang = $sites[$lang];
		}
		
		setcookie("lang", $lang, strtotime( '+30 days' ), '/', "phasmomap.online");
		return $lang;
	} else {
		$lang = "en";
		if (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], $availableLangs)) {
			$lang = $_COOKIE['lang'];
		}
		return $lang;
	}
}

// Строки локализации
$strings = null;
if (file_exists("tools/langs.json") == 1) {
	$strings = json_decode(file_get_contents("tools/langs.json"), true);
} else { exit(); }

$types = array();
$query = "SELECT * FROM `types` WHERE 1";
$result = $mysqli->query($query);
if ($result -> num_rows != 0) {
	while ($line = $result->fetch_assoc()) { 
        array_push($types, $line);
	}
}

$loggedIn = false;
$userId = 0;
if (isset($_COOKIE['PhasmoMapSession'])) {
  $id = substr($_COOKIE['PhasmoMapSession'], 1, strpos($_COOKIE['PhasmoMapSession'], "00a") - 1);
  $login = substr($_COOKIE['PhasmoMapSession'], strpos($_COOKIE['PhasmoMapSession'], "00a") + 3);

  $query = "SELECT `id`, `login` FROM `users` WHERE `google_id` = '".$id."'";
  $result = $mysqli->query($query);

  if ($result -> num_rows != 0) {
    while ($line = $result->fetch_assoc()) { 
      if (md5(trim($line['login'])) === trim($login)) {
        $loggedIn = true;
        $userId = $line['id'];
      }
    }
  }
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Phasmophobia Interactive Map</title>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
	  <link rel="shortcut icon" href="/tools/images/favicon.ico">
    <meta http-equiv="Last-Modified" content="<?php echo file_get_contents('../timestamp.txt');?>">
    
    <link rel="stylesheet" href="/tools/leaflet/css/leaflet.css">
    <link rel="stylesheet" href="/tools/leaflet/css/L.Control.MousePosition.css">

    <script src="/tools/leaflet/js/leaflet.js" type="text/javascript"></script>
    <script src="/tools/leaflet/js/L.Control.MousePosition.js" type="text/javascript"></script>
    <script type="text/javascript" src="/tools/leaflet/MovingMarker.js"></script>
    <script src="/tools/leaflet/js/jquery.min.js"></script>
    <script src="/tools/leaflet/leaflet.draw.js"
      id='leafletdraw' data-lang='<?php echo $lang;?>' ></script>

    <link rel="stylesheet" type="text/css" href="/tools/leaflet/leaflet.draw.css" />
    <link rel="stylesheet" type="text/css" href="/tools/css/main.css" />
  </head>
  <body>
    <h1 class="title"><?php echo json_decode($map['title'], true)[$lang];?></h1>
    <span id="menuButton">&#9776;</span>
    <div id="mapmenu" class="sidenav">
      <div class="nav">
          <h2>PhasmoMap.online</h2>
          <details>
            <summary><?php echo $strings['menu_mapchooser'][$lang];?></summary> <?php 
            foreach ($maps as & $map) {
              echo '<a href="/'.$map['code'].'">'.json_decode($map['title'], true)[$lang].'</a>';
            } ?>
          </details>
          <?php if (!$loggedIn) {
							$params = array(
							'client_id'     => 'client_id',
							'redirect_uri'  => 'http://phasmomap.online/api/auth.php',
							'response_type' => 'code',
							'scope'         => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
							'state'         => '123'
						);
						
						$url = 'https://accounts.google.com/o/oauth2/auth?'.urldecode(http_build_query($params));?>
            <?php echo '<p class="google"><img src="/tools/images/icons/google.png"/><a target="_blank" href="' . $url . '">'.$strings["menu_login"][$lang].'</a></p>';?>
						<?php } ?>
          <hr>
          <h2><?php echo json_decode($map['title'], true)[$lang];?></h2>
          <div id="menuInfo"></div>
          <br><span>Sound Sensor</span>
          <table>
            <tr>
              <td>
                <label>
                  <div>
                    <input type="checkbox" class="leaflet-control-layers-selector" name="soundSensor1">
                    <span>
                      <img src="/tools/images/types/soundsensor.png" align="center" width="30" height="30">
                    </span> 
                  </div>
                </label>
              </td>
              <td>
                <label>
                  <div>
                    <input type="checkbox" class="leaflet-control-layers-selector" name="soundSensor2">
                    <span>
                      <img src="/tools/images/types/soundsensor.png" align="center" width="30" height="30">
                    </span>
                  </div>
                </label>
              </td>
              <td>
                <label>
                  <div>
                    <input type="checkbox" class="leaflet-control-layers-selector" name="soundSensor3">
                    <span>
                      <img src="/tools/images/types/soundsensor.png" align="center" width="30" height="30">
                    </span>
                  </div>
                </label>
              </td>
              <td>
                <label>
                  <div>
                    <input type="checkbox" class="leaflet-control-layers-selector" name="soundSensor4">
                    <span>
                      <img src="/tools/images/types/soundsensor.png" align="center" width="30" height="30">
                    </span>
                  </div>
                </label>
              </td>
            </tr>
          </table>
          <hr>
          <label>
            <div>
              <input type="checkbox" class="leaflet-control-layers-selector" name="checkDrawing">
              <span>
                <img src="/tools/images/icons/drawing.png" align="center" width="30" height="30"> drawing
              </span>
            </div>
          </label>
      </div>
    </div>
    <div id="map" class="intro" style="width: 100%; height: 100%; background-color:#060E12;"></div>
    <script src="/tools/js/sidemenu.js"></script>
    <script src="/tools/js/main.js" id="main" 
    data-map='<?php echo json_encode($map);?>' 
    data-marker-type='<?php echo $marker_type;?>' 
    data-marker-id='<?php echo $marker_id;?>' 
    data-marker-x='<?php echo $marker_x;?>' 
    data-marker-y='<?php echo $marker_y;?>' 
    data-lang='<?php echo $lang;?>'
    data-types='<?php echo json_encode($types);?>' ></script>
  </body>
</html>
