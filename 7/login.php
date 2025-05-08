<?php
header('Content-Type: text/html; charset=UTF-8');
echo "<link rel='stylesheet' href='style.css'>";

$allowed_includes = ['modules/db.php', 'modules/validation.php'];

$file = 'modules/db.php';

if (in_array($file, $allowed_includes) && file_exists($file)) {
    include $file;
} else {
    die('Недопустимый файл для включения');
}




function checkAuth($db, $pass, $login){
  $user = getUserAuthData($db, $login);
  if(md5($pass) == $user['pass']){
      return true;
  }else{
    return false;
  }
}
function authorized(){
  echo '<div class="form-container">';
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
      echo '<form id="form" action="" method="post">
            <h2>Вы авторизованы</h2>
            <input id="exit" name="exit" type="submit" value="Выход" class="submit-btn">
            </form>';
    }
    else { 
      session_destroy();
      header('Location: ./');
    }
    echo '</div>';
}
function printFieldsToAuth(){
  echo '<div class="form-container">
        <form id="form" action="" method="post">
        <h2>Авторизация</h2>
        <input name="login" placeholder="Логин" required>
        <input name="pass" type="password" placeholder="Пароль" required>
        <input type="submit" value="Войти" class="submit-btn">
        </form>
        </div>';
}
function printFieldsToAuthAndError(){
  echo '<div class="form-container">
          <div class="error">Неверный логин или пароль</div>
          <form id="form" action="" method="post">
          <h2>Авторизация</h2>
          <input name="login" placeholder="Логин" required>
          <input name="pass" type="password" placeholder="Пароль" required>
          <input type="submit" value="Войти" class="submit-btn">
          </form>
          </div>';
}


$session_started = false;

if (isset($_COOKIE[session_name()]) && session_start()) {
  $session_started = true;

  if (!empty($_SESSION['login'])) {
    authorized();
    exit();
  }
}


if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  printFieldsToAuth();
}
else {
  $login = $_POST['login'];
  $pass = $_POST['pass'];
  $auth = checkAuth($db, $pass, $login);
  if ($auth){
    $uid = getUserId($db, $login);

    if (!$session_started) {
      session_start();
    }

    $_SESSION['login'] = $_POST['login'];
    $_SESSION['uid'] = $uid;

    header('Location: ./');

  }else{
    printFieldsToAuthAndError();
  }
}
?>