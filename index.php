<?php
session_start();
session_regenerate_id(true);
if ($_GET['logout'] == true) {
  unset($_GET['logout']);
  unset($_SESSION);
  session_destroy();
  session_start();
  session_regenerate_id(true);
}
if (empty($_SESSION['token'])) {
  $_SESSION['token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['token'];
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Woodpecker test - Login</title>
  </head>
  <body>
    <?php
    if (isset($_GET['invalid']) && $_GET['invalid'] == true) {
      echo('<div>Invalid login!</div>');
    }
    ?>
  	<form action="list.php" method="POST" class="loginform">
  		Username:
      <input type="hidden" name="token" value="<?php echo($token); ?>">
  		<div class="username"><input type="text" name="username"></div>
  		Password:
  		<div class="password"><input type="password" name="password"></div>
  		<div class="login"><input type="submit" value="Login"></div>
  	</form>
  </body>
</html>