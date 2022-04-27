<?php
if (!empty($_GET['code'])) {
	// Отправляем код для получения токена (POST-запрос).
	$params = array(
		'client_id'     => 'client_id',
		'client_secret' => 'client_secret',
		'redirect_uri'  => 'http://phasmomap.online/api/auth.php',
		'grant_type'    => 'authorization_code',
		'code'          => $_GET['code']
	);	
			
	$ch = curl_init('https://accounts.google.com/o/oauth2/token');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$data = curl_exec($ch);
	curl_close($ch);	
 
	$data = json_decode($data, true);
	if (!empty($data['access_token'])) {
		// Токен получили, получаем данные пользователя.
		$params = array(
			'access_token' => $data['access_token'],
			'id_token'     => $data['id_token'],
			'token_type'   => 'Bearer',
			'expires_in'   => 3599
		);
 
		$info = file_get_contents('https://www.googleapis.com/oauth2/v1/userinfo?' . urldecode(http_build_query($params)));
		$info = json_decode($info, true);
        
        $mysqli = new mysqli('localhost', 'db_username', 'db_password', 'db_name');
        $query3 = "SELECT * FROM `users` WHERE `google_id` = '".$info['id']."'";
        $result3 = $mysqli->query($query3);
        if ($result3 -> num_rows == 0) {
            $name = "";
            $fname = "";
            if (isset($info['given_name'])) {
                $name = $info['given_name'];
            }
            if (isset($info['family_name'])) {
                $fname = $info['family_name'];
            }
            $query4 = "INSERT INTO `users`(`login`, `name`, `sname`, `google_id`, `email`, `picture`) VALUES ('".$info['name']."', '".$fname."', '".$fname."', '".$info['id']."', '".$info['email']."', '".$info['picture']."')";
            $mysqli->query($query4);
		} else {
            $query4 = "UPDATE `users` SET `login`='".$info['name']."' WHERE `google_id` = '".$info['id']."'";
            $mysqli->query($query4);
		}
		setcookie("PhasmoMapSession", "0".$info['id']."00a".md5($info['name']), strtotime( '+30 days' ), '/', "apexmap.online", true);
		?><script>document.cookie = "PhasmoMapSession=`."0".$info['id']."00a".md5($info['name']).`; expires=`.strtotime( '+30 days' ).`; path=/;";</script><?php
	}
}
$l = 'http://phasmomap.online';
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="refresh" content="0;URL=
    <?php echo $l;?>" />
  </head>
    <body>
        <script>document.location.href = '<?php echo $l;?>';</script>
	</body>
</html>