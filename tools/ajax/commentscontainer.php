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
if (file_exists("../langs.json") == 1) {
	$strings = json_decode(file_get_contents("../langs.json"), true);
} else { exit(); }

if (isset($_GET['id']) && intval($_GET['id'])."" === $_GET['id']) {
    $mysqli = new mysqli('localhost', 'db_username', 'db_password', 'db_name');

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
    $contentToPrint = "";

    $contentToPrint .= "<h3>".$strings['comments_title'][$lang]."</h3><div class='page-content'>
    <div id='pagination-result'>
        <input type='hidden' name='rowcount' id='rowcount'/>
    </div>
</div><br>";

    // Comment or Login form
	if ($loggedIn) {
        $msg = "";
        if (isset($_POST['message'])) {
            $msg = $_POST['message'];
        }
        $contentToPrint .= "<form class='commentform' method='POST'><input type='hidden' name='marker' value='".$_GET['id']."'/><input type='hidden' name='map' value='".$_GET['map']."'/><textarea name='message' placeholder='".$strings['comments_placeholder']."' maxlength='250' required>".$msg."</textarea><br><input type='submit' name='submit' value='Submit'/></form>";
    }

    echo $contentToPrint; 

} else {
    header("HTTP/1.0 401 Unauthorized");
}
?>