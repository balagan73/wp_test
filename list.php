<?php
session_start();
session_regenerate_id(true);
if (empty($_SESSION['token'])) {
  $_SESSION['token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['token'];
$conn = new PDO('mysql:dbname=wp_test;host=127.0.0.1;charset=utf8', 'wp_test', 'wp_secretpass');
$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Authenticate user.
if ($_POST['username'] && $_POST['password']) {
   if (hash_equals($_SESSION['token'], $_POST['token'])) {
    $username = $_POST['username'];
    $pass = $_POST['password'];
    try {
      $stmt = $conn->prepare('SELECT * FROM users WHERE username = :name AND password = PASSWORD(:pass)');
      $stmt->execute(array('name' => htmlspecialchars($username, ENT_QUOTES, 'UTF-8'), 'pass' => htmlspecialchars($pass, ENT_QUOTES, 'UTF-8')));
      $result = $stmt->fetchAll();
      if ($result[0]) {
        $_SESSION['validated'] = true;
        // User successfully validated.
        // Update last login.
        $stmt2 = $conn->prepare('UPDATE users SET last_login=NOW() WHERE username = :name AND password = PASSWORD(:pass)');
        $stmt2->execute(array('name' => htmlspecialchars($username, ENT_QUOTES, 'UTF-8'), 'pass' => htmlspecialchars($pass, ENT_QUOTES, 'UTF-8')));
      }
      else {
        // Invalid login attempt, redirect to login page.
        header('Location:' . './index.php?invalid=true');
        exit();
      }
    }
    catch(PDOException $e) {
      echo "Error: " . $e->getMessage();
    }
  }
}

if ($_SESSION['validated'] != true) {
  header('Location:' . './index.php');
  exit();
}

// Add new user.
if ($_POST['new_username'] && $_POST['pass1'] && $_POST['pass2']) {
  if (hash_equals($_SESSION['token'], $_POST['token'])) {
    if ($_POST['pass1'] == $_POST['pass2']) {
      $pass = htmlspecialchars($_POST['pass1'], ENT_QUOTES, 'UTF-8');
      $username = htmlspecialchars($_POST['new_username'], ENT_QUOTES, 'UTF-8');
      $stmt = $conn->prepare('SELECT * FROM users WHERE username = :name');
      $stmt->execute(array('name' => $username));
      $result = $stmt->fetchAll();
      if ($result[0]) {
        $message = "User already exists";
      }
      else {
        // Insert user;
        $insert = $conn->prepare("INSERT INTO users (username, password) VALUES (:username, PASSWORD(:password))");
        $insert->execute(array('username' => $username, 'password' => $pass));
      }
    }
    else {
      $message = "Passwords do not match!";
    }
  }
}




?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Woodpecker test - List users</title>
  </head>
  <body>
    <div class="menu"><a href="index.php?logout=true">Logout</a></div>
<?php 
    if ($message) {
      echo("<div class='message'>" . $message . "</div");
    }
?> 
    <div>Add new user here</div>
    <form name="adduserform" method="POST" target="_self" class="adduserform">
      <input type="hidden" name="token" value="<?php echo($token); ?>">
      Username:
      <div><input type="text" name="new_username" id="new_username"></div>
      Password:
      <div><input type="password" name="pass1" id="pass1"></div>
      Confirm password:
      <div><input type="password" name="pass2" id="pass2"></div>
      <div><input type="submit" value="Add user" id="adduserbutton"></div>
    </form>
    <hr>
    <table class="userlist"> 
  <?php
    if ($_SESSION['validated'] == true) {
      // List users alphabetically.
      $stmt = $conn->prepare('SELECT * FROM users ORDER BY username ASC');
      $stmt->execute();
      $userlist = $stmt->fetchAll();
      echo("<tr><th colspan='2'>Existing users</th></tr>");
      echo("<tr><th>Username</th><th>Last login</th></tr>");

      foreach($userlist as $user) {
        echo("<tr><td>" . $user['username'] . "</td><td>" . $user['last_login'] . "</tr>");
      }
    }
    $conn = null;
  ?>
  </table>
  </body>
  <script src="validate_form.js"></script>
</html>