<?php 
$path = '../../tileserver/'.$_GET['server'].'/'.$_GET['z'].'/'.$_GET['x'].'/'.$_GET['y'].'.png';
echo $path;
if (!file_exists($path)) {
    $path = 'empty.png';
    $path = 'http://phasmomap.online/tools/tiles/empty.png';
} else {
$path = 'http://phasmomap.online/tileserver/'.$_GET['server'].'/'.$_GET['z'].'/'.$_GET['x'].'/'.$_GET['y'].'.png';
}

header('Location: '.$path);
exit;
$c = file_get_contents($path, true);
$size = filesize($path);
header ('Content-Type: image/png');
header ("Content-length: $size");
echo $c;
exit;
?>