<?php
header('Content-Type: text/html; charset=UTF-8');
echo "<link rel='stylesheet' href='style.css'>";

function getDatabase(){
  $user = 'u68859'; 
  $pass = '5248297'; 
  $db = new PDO('mysql:host=localhost;dbname=u68859', $user, $pass, 
      [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); 
  return $db;
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
function checkAuth($db, $pass, $login){
  try {
    $data = $db->prepare("SELECT pass FROM auth WHERE login = ?");
    $data->execute([$login]);
    $user = $data->fetch(PDO::FETCH_ASSOC);

    if(md5($pass) == $user['pass']){
      return true;
    }else{
      return false;
    }
  }
  catch(PDOException $e){
    echo '<div class="form-container">';
    echo '<div class="error">Ошибка: ' . $e->getMessage() . '</div>';
    echo '</div>';
    exit();
  }
}
function getId($db, $login){
  try{
    $data = $db->prepare("SELECT id_app FROM auth WHERE login = ?");
    $data->execute([$login]);
    $user = $data->fetch(PDO::FETCH_ASSOC);
    return $user['id_app'];
  }
  catch(PDOException $e){
    echo '<div class="form-container">';
    echo '<div class="error">Ошибка: ' . $e->getMessage() . '</div>';
    echo '</div>';
    exit();
  }
}

$db = getDatabase();
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
    $uid = getId($db, $login);

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