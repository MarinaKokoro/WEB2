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
// Иначе, если запрос был методом POST, т.е. нужно сделать авторизацию с записью логина в сессию.
else {
  // TODO: Проверть есть ли такой логин и пароль в базе данных.
  $auth = false;
  try {
    $login = $_POST['login'];
    $pass = $_POST['pass'];

    print($login);
    $data = $db->prepare("SELECT pass FROM auth WHERE login = \":login\"");
    $data->bindParam(':login', $login);
    $data->execute();
    $user = $data->fetch(PDO::FETCH_ASSOC);
   
    if(md5($pass) == $user['pass']){
      $auth = true;
    }
  }
  catch(PDOException $e){
    print('Error: ' . $e->getMessage());
    exit();
  }

  // Выдать сообщение об ошибках.
  if(!$auth){
    print('<div class="error">Неверный логин или пароль</div>');
  }
  else{
    if (!$session_started) {
      session_start();
    }
    // Если все ок, то авторизуем пользователя.
    $_SESSION['login'] = $_POST['login'];
    // Записываем ID пользователя.
    $_SESSION['uid'] = rand();

    // Делаем перенаправление.
    header('Location: ./');
  }

  

  
}
