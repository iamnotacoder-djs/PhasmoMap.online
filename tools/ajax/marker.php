<?php
// Определение языка страницы
$lang = "en";
$availableLangs = array("en", "ru");
if (isset($_GET['lang'])) {
	if (in_array($_GET['lang'], $availableLangs)) {
		// setcookie("lang", $_GET['lang'], strtotime( '+30 days' ), '/', "apexmap.online");
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
		
		// setcookie("lang", $lang, strtotime( '+30 days' ), '/', "apexmap.online");
		return $lang;
	} else if (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], $availableLangs)) {

		$lang = $_COOKIE['lang'];
        
		return $lang;
	} else {
		return "en";
	}
}

// Строки локализации
$strings = null;
if (file_exists("../langs.json") == 1) {
	$strings = json_decode(file_get_contents("../langs.json"), true);
} else { exit(); }

if (isset($_GET['id']) && intval($_GET['id'])."" === $_GET['id'] && isset($_GET['map']) && intval($_GET['map'])."" === $_GET['map']) {
    $mysqli = new mysqli('localhost', 'db_username', 'db_password', 'db_name');
    $query3 = "SELECT * FROM `markers` WHERE `id` = '".$_GET['id']."'";
    $result3 = $mysqli->query($query3);
    if ($result3 -> num_rows == 0) {
        header("HTTP/1.0 404 Not Found");
    } else {
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
        while ($line = $result3->fetch_assoc()) { 
            $contentToPrint = "";
            
            $typeName = json_decode($types[intval($line['type'])]['title']);
            $typeName = $typeName -> $lang;

            $contentToPrint .= "<h3>".$typeName." #".$line['id']."</h3>";

            if ($line['title'] != "") { 
                $titleName = json_decode($line['title']);
                $contentToPrint .= "<h2>".$titleName -> $lang."</h2>";
            }
            if ($line['author'] != "") { 
                $contentToPrint .= "<p class='author'>".$strings['marker_added'][$lang]." <b>".$line['author']."</b></p>";
            } else {
                $contentToPrint .= "<p class='author'>".$strings['marker_added'][$lang]." <b>admin</b></p>";
            }
            if ($line['description'] != "") { 
                $description = json_decode($line['description']);
                $contentToPrint .= "<p>".$description -> $lang."</p>";
            }
            if ($line['youtube'] != "") { 
                $contentToPrint .= "<iframe width='250' height='157' src='https://www.youtube.com/embed/".$line['youtube']."' frameborder='0' allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>";
            }
            if ($line['image'] != "") { 
                $contentToPrint .= "<a href='".$line['image']."' target='_blank'><img src='".$line['image']."'/></a>";
            }
            $ShortURL = $maps[intval($line['map_id'])]["code"];

            $contentToPrint .= "<div class='clipmarker'><a onclick='appCopyToClipBoard(\"http://phasmomap.online/".$ShortURL."/id"."/".$_GET['id']."\");'>".$strings['marker_share'][$lang]." <span>".$strings['marker_clipboard'][$lang]."</span><blockquote>http://phasmomap.online/".$ShortURL."/id"."/".$_GET['id']."</blockquote></a></div>"; 
            echo $contentToPrint;
        }
    }
} else {
    header("HTTP/1.0 401 Unauthorized");
}
?>