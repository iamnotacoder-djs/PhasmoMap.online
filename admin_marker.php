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
// Субмиты
if (isset($_POST['submit'])) {

	if (trim($_POST['copy_of']) != "" && trim($_POST['copy_of']) != "0") {
		$queryCOPY = "SELECT * FROM `markers` where `id`='".trim($_POST['copy_of'])."'";
		$resultCOPY = $mysqli->query($queryCOPY);
		if ($resultCOPY -> num_rows != 0) {
			while ($line = $resultCOPY->fetch_assoc()) {
				$_POST['type'] = $line['type'];
				$_POST['author'] = $line['author'];
				$_POST['author_id'] = $line['author_id'];
				$_POST['description'] = $line['description'];
				$_POST['a_title'] = $line['title'];
				$_POST['a_image'] = $line['image'];
				$_POST['a_youtube'] = $line['youtube'];
			}
		}
	}

  $query3 = "INSERT INTO `markers` (`date`, `map_id`, `type`, `author`, `author_id`, `x`, `y`, `description`, `title`, `image`, `youtube`, `x2`, `y2`, `copy_of`) VALUES ('".$_POST['date']."', '".$_POST['map_id']."', '".$_POST['type']."', '".$_POST['author']."', '".$_POST['author_id']."', '".$_POST['x']."', '".$_POST['y']."', '".$_POST['description']."', '".$_POST['a_title']."', '".$_POST['a_image']."', '".$_POST['a_youtube']."', '".$_POST['a_x2']."', '".$_POST['a_y2']."', '".$_POST['copy_of']."')";
	
	$result3 = $mysqli->query($query3);
	
	$query3 = "DELETE FROM `markers_suggested` WHERE `id` = '".$_GET['id']."'";
	$result3 = $mysqli->query($query3);
	echo "<center><h1 style='color:#fff;'>Сохранено</h1></center>";
} else if (isset($_POST['submit2'])) {
	$query3 = "DELETE FROM `markers_suggested` WHERE `id` = '".$_GET['id']."'";
	$result3 = $mysqli->query($query3);
	echo "<center><h1 style='color:#fff;'>Удалено</h1></center>";
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
   <div id="main"><?php 
      $query = "SELECT * FROM `markers_suggested` where `id`='".$_GET['id']."'";
      $result = $mysqli->query($query);
      if ($result -> num_rows != 0) {
        while ($line = $result->fetch_assoc()) {?>
				<form method="post" action="admin_add_marker.php?id=<?php echo $_GET['id'];?>&thinkID=<?php echo $thinkID;?>">
					<input type="hidden" name="thinkID" value="<?php echo $thinkID;?>"/>
					<table class="addmarker">
						<thead>
							<tr>
								<td>Автор</td>
								<td>ID автора</td>
								<td>Дата</td>
								<td>Карта</td>
								<td>Тип</td>
								<td>X</td>
								<td>Y</td>
							</tr>
						</thead>
						<tr>
							<td><input type="text" name="author" placeholder="author" value="<?php echo str_replace("Aspin Tojps", "idaspin", $line['author']);?>"/></td>
							<td><input type="text" name="author_id" placeholder="author_id"  value=""/></td>
							<td><input type="text" name="date" placeholder="date" style="width:200px" value=""/></td>
							<td><input type="text" name="map_id" placeholder="map_id"  value="<?php echo $line['map_id'];?>"/></td>
							<td><input type="text" name="type" placeholder="type"  value="<?php echo $line['type'];?>"/></td>
							<td><input type="text" name="x" placeholder="x"  value="<?php echo $line['x'];?>"/></td>
							<td><input type="text" name="y" placeholder="y"  value="<?php echo $line['y'];?>"/></td>
						</tr>
					</table>
					<table class="addmarker2">
						<thead>
							<tr>
								<td>Описание</td>
								<td>Название локации</td>
							</tr>
						</thead>
						<tr>
							<td><textarea name="description" placeholder="description" ><?php echo $line['description'];?></textarea></td>
							<td><input type="text" name="a_title" placeholder="a_title"  value=""/></td>
						</tr>
					</table>
					<table class="addmarker">
						<thead>
							<tr>
								<td>Изображение. <?php if ($line['image'] != "") { ?>(<a href="<?php echo $line['image'];?>" target="_blank">Открыть</a>) <?php } ?></td>
								<td>X2</td>
								<td>Y2</td>
								<td>Копия другого маркера</td>
							</tr>
						</thead>
						<tr>
							<td>
								<input type="text" name="a_image" placeholder="a_image"  value="<?php echo $line['image'];?>"/>
							</td>
							<td>
								<input type="text" name="a_x2" placeholder="a_x2"  value=""/>
							</td>
							<td>
								<input type="text" name="a_y2" placeholder="a_y2"  value=""/>
							</td>
							<td>
								<input type="text" name="copy_of" placeholder="copy_of"  value=""/>
							</td>
						</tr>
					</table>
          					
					<div class="col-12">
						<ul class="actions">
							<li><input type="submit" name="submit" value="Отправить" class="primary" /></li>
							<li><input type="reset" value="Сбросить" /></li>
							<li><a href="/map/<?php echo $line['map_id'];?>/x/<?php echo $line['y'];?>/y/<?php echo $line['x'];?>/type/<?php echo $line['type'];?>" target="_blank" class="button primary">Проверить</a></li>
							<li><input type="submit" name="submit2" value="Удалить" class="button" /></li>
						</ul>
					</div>
				</form><?php }} ?>
   </div>
</body>
</html>
