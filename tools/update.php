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

$maps = array();
$query = "SELECT * FROM `maps` WHERE 1";
$result = $mysqli->query($query);
if ($result -> num_rows != 0) {
	while ($line = $result->fetch_assoc()) { 
        array_push($maps, $line);
	}
}

$timestamp = time();

$hta = 'Options +MultiViews
Options +FollowSymlinks
RewriteEngine On
DirectoryIndex map.php

RewriteRule "^map/([0-9]+)/id/([0-9]+)$" "?map=$1&id=$2" [NC]
RewriteRule "^map/([0-9]+)/id/([0-9]+)/$" "?map=$1&id=$2" [NC]

RewriteRule "^map/([0-9]+)/x/([0-9]+)/y/([0-9]+)$" "?map=$1&x=$2&y=$3" [NC]
RewriteRule "^map/([0-9]+)/x/([0-9]+)/y/([0-9]+)/$" "?map=$1&x=$2&y=$3" [NC]

RewriteRule "^map/([0-9]+)/x/([0-9]+)/y/([0-9]+)/type/([0-9]+)$" "?map=$1&x=$2&y=$3&type=$4" [NC]
RewriteRule "^map/([0-9]+)/x/([0-9]+)/y/([0-9]+)/type/([0-9]+)/$" "?map=$1&x=$2&y=$3&type=$4" [NC]

RewriteRule "^map/([0-9]+)$" "?map=$1" [NC]
RewriteRule "^map/([0-9]+)/$" "?map=$1" [NC]

ErrorDocument 400 /404
ErrorDocument 401 /404
ErrorDocument 403 /404
ErrorDocument 404 /404

';

$sitemap = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<url>
	<loc>http://phasmomap.online/</loc>
	<lastmod>' . date('c', $timestamp) . '</lastmod>
	<priority>1.0</priority>
</url>
';

foreach ($maps as & $map)
{
    $hta .= '
  RewriteRule "^' . $map['code'] . '$" "?map=' . $map['id'] . '" [NC]
  RewriteRule "^' . $map['code'] . '/$" "?map=' . $map['id'] . '" [NC]

  RewriteRule "^map/' . $map['code'] . '/id/([0-9]+)$" "?map=' . $map['id'] . '&id=$1" [NC]
  RewriteRule "^map/' . $map['code'] . '/id/([0-9]+)/$" "?map=' . $map['id'] . '&id=$1" [NC]
id=$1" [NC]

  RewriteRule "^' . $map['code'] . '/id/([0-9]+)$" "?map=' . $map['id'] . '&id=$1" [NC]
  RewriteRule "^' . $map['code'] . '/id/([0-9]+)/$" "?map=' . $map['id'] . '&id=$1" [NC]

  RewriteRule "^' . $map['code'] . '/x/([0-9]+)/y/([0-9]+)$" "?map=' . $map['id'] . '&x=$1&y=$2" [NC]
  RewriteRule "^' . $map['code'] . '/x/([0-9]+)/y/([0-9]+)/$" "?map=' . $map['id'] . '&x=$1&y=$2" [NC]

  RewriteRule "^' . $map['code'] . '/x/([0-9]+)/y/([0-9]+)/type/([0-9]+)$" "?map=' . $map['id'] . '&x=$1&y=$2&type=$3" [NC]
  RewriteRule "^' . $map['code'] . '/x/([0-9]+)/y/([0-9]+)/type/([0-9]+)/$" "?map=' . $map['id'] . '&x=$1&y=$2&type=$3" [NC]

  RewriteRule "^' . $map['code'] . '/type/([0-9]+)$" "?map=' . $map['id'] . '&type=$1" [NC]
  RewriteRule "^' . $map['code'] . '/type/([0-9]+)/$" "?map=' . $map['id'] . '&type=$1" [NC]

  ';
    $pr = 1.0;
    $pr -= 0.1 * (count($maps) - $map['id']);
    if ($pr < 0.3)
    {
        $pr = 0.3;
    }
    $sitemap .= '<url>
  <loc>http://phasmomap.online/' . $map['code'] . '</loc>
  <lastmod>' . date('c', $timestamp) . '</lastmod>
  <priority>' . $pr . '</priority>
</url>
';
}

file_put_contents('../.htaccess', $hta);
file_put_contents('../sitemap.xml', $sitemap);
file_put_contents('../timestamp.txt', date('c', $timestamp));

// geoJSON
$types = array();
$query = "SELECT * FROM `types` WHERE 1";
$result = $mysqli->query($query);
if ($result -> num_rows != 0) {
	while ($line = $result->fetch_assoc()) { 
        array_push($types, $line);
	}
}

foreach ($maps as & $map) {
  if (!file_exists('geoJSON/'.$map['id'])) {
    mkdir('geoJSON/'.$map['id'], 0777, true);
  }
  foreach ($types as & $type) {

    $query = "SELECT * FROM `markers` WHERE `map_id` = '" . $map['id'] . "' and `type` = '" . $type['id'] . "'";
    $result = $mysqli->query($query);
    if ($result -> num_rows != 0) {
      $file = '[';
      $d = 0;
      while ($line = $result->fetch_assoc()) { 
        if ($d == 0) {
            $d++;
        }
        else {
            $file .= ',';
        }
        $file .= '{
          "type": "Feature",
          "properties": {
            "id": "' . $line['id'] . '",
            "author": "' . str_replace("Aspin Tojps", "idaspin", $line['author']) . '",
            "type": "' . $line['type'] . '",
            "author_id": "' . $line['author_id'] . '",
            "description": "' . str_replace('"', "'", $line['description']) . '",
            "title": "' . str_replace('"', "'", $line['title']) . '",
            "image": "' . $line['image'] . '",
            "youtube": true,
            "deprecated": "true"';
          if ($line['x2'] != "0" && $line['y2'] != "0") {
              $file .= ',
              "start": [' . (intval($map['size']) - intval($line['y'])) . ', ' . $line['x'] . '],
              "end": [' . (intval($map['size']) - intval($line['y2'])) . ', ' . $line['x2'] . ']';
          }
          $file .= '},
            "geometry": {
              "type": "Point",
              "coordinates": [' . $line['x'] . ', ' . ($mapsize - intval($line['y'])) . ']
            }
          }';
      }
      $file .= ']';
      if (file_exists('geoJSON/'.$map['id'].'/'.$type['id'].'.json')) {
          unlink('geoJSON/'.$map['id'].'/'.$type['id'].'.json');
      }
      file_put_contents('geoJSON/'.$map['id'].'/'.$type['id'].'.json', $file);
    }
  }
}
?>