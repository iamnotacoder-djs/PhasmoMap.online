<?php
require_once("dbcontroller.php");
require_once("pagination.class.php");

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

$mysqli = new DBController();
$perPage = new PerPage();

$sql = "SELECT * from `comments` where `marker_id` = '" . $_GET['id'] . "' ORDER BY rate desc, id desc";
$paginationlink = "comments.php?id=" . $_GET['id'] . "&page=";
$pagination_setting = $_GET["pagination_setting"];

$page = 1;
if (!empty($_GET["page"])) {
    $page = $_GET["page"];
}

$start = ($page - 1) * $perPage->perpage;
if ($start < 0) $start = 0;

$query = $sql . " limit " . $start . "," . $perPage->perpage;
$faq = $mysqli->runQuery($query);

if (empty($_GET["rowcount"])) {
    $_GET["rowcount"] = $mysqli->numRows($sql);
}
if ($pagination_setting == "prev-next") {
    $perpageresult = $perPage->getPrevNext($_GET["rowcount"], $paginationlink, $pagination_setting);
}
else {
    $perpageresult = $perPage->getAllPageLinks($_GET["rowcount"], $paginationlink, $pagination_setting);
}

$output = '';

if (!empty($perpageresult)) {
    $mysqli = new mysqli('localhost', 'db_username', 'db_password', 'db_name');
    foreach ($faq as $k => $v) {
        $avatar = "";
        $login = "";
        $name = "";
        $sname = "";
        $query = "SELECT `picture`,`login`,`name`,`sname` FROM `users` WHERE `id` = '".$faq[$k]["author_id"]."'";
        $result = $mysqli->query($query);
        
        if ($result -> num_rows != 0) {
            while ($line = $result->fetch_assoc()) {
                $avatar = $line['picture'];
                $login = $line['login'];
                $name = $line['name'];
                $sname = $line['sname'];
            }
        }

        $output .= '<input type="hidden" id="rowcount" name="rowcount" value="' . $_GET["rowcount"] . '" /><table class="commentformat">
        <tr><td class="author" title="'.$name.' '.$sname.'"><a href="/profile/'.$faq[$k]["author_id"].'" target="_blank">'.$login.'</a></td></tr>
        <tr><td>'.$faq[$k]["text"].'</td></tr>
        <tr><td class="date">'.$faq[$k]["date"].'</td></tr>
    </table>';
    }
}
if (!empty($perpageresult)) {
    $output .= '<div id="pagination">' . $perpageresult . '</div>';
} else {
    echo "<p>".$strings['comments_empty'][$lang]."</p>";
}
print $output;
?>
