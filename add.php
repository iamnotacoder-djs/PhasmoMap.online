<?php
$mysqli = new mysqli('localhost', 'db_username', 'db_password', 'db_name');

$map_id = 0;
if (isset($_GET['map']) && intval($_GET['map']) >= 0) {
	$map_id = intval($_GET['map']);
}

$marker_x = -1;
if (isset($_GET['lat']) && intval($_GET['lat']) >= 0) {
	$marker_x = intval($_GET['lat']);
}

$marker_y = -1;
if (isset($_GET['lng']) && intval($_GET['lng']) >= 0) {
	$marker_y = intval($_GET['lng']);
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

$maps = array();
$query = "SELECT * FROM `maps` WHERE 1";
$result = $mysqli->query($query);
if ($result -> num_rows != 0) {
	while ($line = $result->fetch_assoc()) { 
        array_push($maps, $line);
	}
}

if (isset($_POST['submit'])) {
	$lat = $_POST['lat'];
	$lng = $_POST['lng'];
	
	$author = $_POST['author'];
	$description = $_POST['description'];
	$map_id = $_POST['map'];
	$type = $_POST['marker'];

	$video = $_POST['video'];
	$image = $_POST['image'];

	$query3 = "INSERT INTO `markers_suggested`(`x`, `y`, `map_id`, `description`, `type`, `image`, `youtube`, `author`) VALUES ('$lat', '$lng', '$map_id', '$description', '$type', '$image', '$video', '$author')";
	$result3 = $mysqli->query($query3);
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
    <meta name="robots" content="noindex" />
    <meta name="googlebot" content="noindex" />
    
    <script src="/tools/leaflet/js/jquery.min.js"></script>

    <link rel="stylesheet" type="text/css" href="/tools/css/main.css" />
    <link rel="stylesheet" type="text/css" href="/tools/css/custom.css" />
    <?php if (isset($_POST['submit'])) { ?><meta http-equiv="refresh" content="5;URL=http://phasmomap.online" /> <? } ?>
    <script> top.history.pushState(null, null, `http://phasmomap.online`); </script>
  </head>
  <body>
   <div id="main">
      <!-- One -->
      <section id="one">
         <header class="major">
            <h2><?php echo $strings['add_title'][$lang];?></h2>
         </header>
         <p><?php echo $strings['add_descr_1'][$lang];?></p>
      </section>
      <?php if (!isset($_POST['submit'])) { ?>
      <section>
         <form method="post" action="add.php?lat=<?php echo $marker_x;?>&lng=<?php echo $marker_y;?>&map=<?php echo $map_id;?>">
            <h4><?php echo $strings['add_position'][$lang];?></h4>
            <div class="row gtr-uniform gtr-50">
               <div class="col-12">
                  <select name="map" required="">
                     <option value="">- <?php echo $strings['add_map'][$lang];?> -</option><?php
                      foreach ($maps as & $map) {
                        echo '<option value="'.$map['id'].'"'.($map['id'] == $map_id ? ' selected=""' : '').'>'.json_decode($map['title'], true)[$lang].'</option>';
                      }
                     ?>
                  </select>
               </div>
               <div class="col-6 col-12-xsmall">
                  <input type="hidden" name="lat" value="<?php echo $marker_x;?>">
                  <input type="text" value="Lat: <?php echo $marker_x;?>" disabled="">
               </div>
               <div class="col-6 col-12-xsmall">
                  <input type="hidden" name="lng" value="<?php echo $marker_y;?>">
                  <input type="text" value="Lng: <?php echo $marker_y;?>" disabled="">
               </div>
               <div class="col-12">
                  <textarea name="description" placeholder="<?php echo $strings['add_field_description'][$lang];?>" rows="3" maxlength="250"></textarea>
               </div>
               <h4><?php echo $strings['add_info'][$lang];?></h4>
               <div class="col-12">
                  <select name="marker" id="viewSelector" required="">
                     <option value="">- <?php echo $strings['add_type'][$lang];?> -</option><?php
                      foreach ($types as & $type) {
                        echo '<option value="'.$type['id'].'">'.json_decode($type['title'], true)[$lang].'</option>';
                      }
                     ?>
                  </select>
               </div>
               <div class="col-6 col-12-xsmall">
                  <input type="url" name="image" placeholder="<?php echo $strings['add_field_image'][$lang];?>">
               </div>
               <div class="col-6 col-12-xsmall">
                  <input type="url" name="video" placeholder="<?php echo $strings['add_field_video'][$lang];?>">
               </div>
               <div class="col-12">
                  <input type="hidden" name="author_id" value="0">
                  <input type="text" name="author" placeholder="<?php echo $strings['add_field_name'][$lang];?>" required="">
               </div>
               <div class="col-12">
                  <ul class="actions">
                     <li><input type="submit" name="submit" value="<?php echo $strings['add_submit'][$lang];?>" class="primary"></li>
                     <li><input type="reset" value="Сброс"></li>
                  </ul>
               </div>
            </div>
         </form>
      </section>
      <?php } else { ?>
      <section>
         <header>
            <h2><?php echo $strings['add_submit_title'][$lang];?></h2>
         </header>
         <p><?php echo $strings['add_submit_descr'][$lang];?></p>
      </section>
      <?php } ?>
   </div>
</body>
</html>
