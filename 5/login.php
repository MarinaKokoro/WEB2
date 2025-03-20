<?php
header('Content-Type: text/html; charset=UTF-8');

function getDatabase(){
  $user = 'u68859'; 
  $pass = '5248297'; 
  $db = new PDO('mysql:host=localhost;dbname=u68859', $user, $pass, 
      [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); 

  return $db;
}
$db = getDatabase();

// В суперглобальном массиве $_SESSION хранятся переменные сессии.
// Будем сохранять туда логин после успешной авторизации.

$session_started = false;

if (isset($_COOKIE[session_name()]) && session_start()) {
  $session_started = true;

  if (!empty($_SESSION['login'])) {
    // Если есть логин в сессии, то пользователь уже авторизован.
    // TODO: Сделать выход (окончание сессии вызовом session_destroy()
    //при нажатии на кнопку Выход).
    // Делаем перенаправление на форму. 


    //переделать
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
      print('<form action="" method="post"><input id="exit" name="exit" type="submit" value="Выход"></form>');
    }
    else { 
      session_destroy();
      header('Location: ./');
    }
    exit();
  }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  ?>

  <form action="" method="post">
    <input name="login" />
    <input name="pass" />
    <input type="submit" value="Войти" />
  </form>

  <?php
}
else {
  $auth = false;
  $uid = -1;
  try {
    $login = $_POST['login'];
    $pass = $_POST['pass'];
   
    $data = $db->prepare("select pass from auth where login = ?");
    $data->execute([$login]);
    $user = $data->fetch(PDO::FETCH_ASSOC);

    if(md5($pass) == $user['pass']){
      $auth = true;
    }

    $data = $db->prepare("select id_app from auth where login = ?");
    $data->execute([$login]);
    $user = $data->fetch(PDO::FETCH_ASSOC);

    $uid = $user['id_app'];
    
  }
  catch(PDOException $e){
    print('Error: ' . $e->getMessage());
    exit();
  }

  if(!$auth){
    print('<div class="error">Неверный логин или пароль</div>');
  }
  else{
    if (!$session_started) {
      session_start();
    }
    $_SESSION['login'] = $_POST['login'];
    $_SESSION['uid'] = $uid;

    header('Location: ./');
  }

  

  
}
