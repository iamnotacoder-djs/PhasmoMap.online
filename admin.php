<?php
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
if (!$loggedIn || $userId != 1) {
  header('Location: http://phasmomap.online');
  exit();
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
    <script> top.history.pushState(null, null, `http://phasmomap.online`); </script>
  </head>
  <body>
   <div id="main">
   <section id="one">
      <header class="major">
         <h2>Список Маркеров на валидацию</h2>
      </header>
   </section><?php 
   $query = "SELECT `id` FROM `markers_suggested` ORDER by `author_id` = '1' DESC, `author_id`";
      $result = $mysqli->query($query);
      if ($result -> num_rows != 0) {
         while ($line = $result->fetch_assoc()) {?>
   <section>
      <iframe src="admin_marker.php?id=<?php echo $line['id'];?>" width="100%" height="500px" style="border: 1px solid #1F2429;"></iframe>
   </section> <?php } } ?>
   </div>
</body>
</html>
