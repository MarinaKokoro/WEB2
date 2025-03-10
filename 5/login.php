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

if ($_COOKIE[session_name()] && session_start()) {

  $session_started = true;

  if (!empty($_SESSION['login'])) {
    // Если есть логин в сессии, то пользователь уже авторизован.
    // TODO: Сделать выход (окончание сессии вызовом session_destroy()
    //при нажатии на кнопку Выход).
    // Делаем перенаправление на форму. 


    print('<input id="exit" name="exit" type="submit" value="Выход">');
    header('Location: ./');
    print('<input id="exit" name="exit" type="submit" value="Выход">');
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
    $pass = $_POST['pass'];

    $data = $db->prepare("SELECT pass FROM auth where login = :login");
    $data->bindParam(':login', $_POST['login']);
    $data->fetchAll();
    
    foreach ($data as $d) {
      print(d['pass']);
    }
    if(md5($pass) == $data['pass']){
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
