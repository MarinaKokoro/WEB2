<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
echo "<link rel='stylesheet' href='style.css'>";

$allowed_includes = ['modules/db.php', 'modules/validation.php'];

$file = 'modules/db.php';
if (in_array($file, $allowed_includes) && file_exists($file)) {
    include $file;
} else {
    die('Недопустимый файл для включения');
}

$file = 'modules/validation.php';
if (in_array($file, $allowed_includes) && file_exists($file)) {
    include $file;
} else {
    die('Недопустимый файл для включения');
}

$abilities = getAbilities($db);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  $messages = array();

  if (!empty($_COOKIE['save'])) {
    setcookie('save', '', 100000);
    setcookie('login', '', 100000);
    setcookie('pass', '', 100000);
    $messages[] = '<div class="success">Спасибо, результаты сохранены.</div>';
  }

  if (!empty($_COOKIE['pass'])) {
    $messages[] = sprintf('<div class="success">Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong>
      и паролем <strong>%s</strong> для изменения данных.</div>',
      strip_tags($_COOKIE['login']),
      strip_tags($_COOKIE['pass']));
  }

  $errors = getErrors();
  $errorMessages = setMessagesAndDeleteCookies($errors, $abilities);
  $messages = array_merge($messages, $errorMessages);
  $values = getValuesFromCookies($abilities);

  if(isset($_COOKIE[session_name()])){
      if(!empty($_SESSION['login'])) {
        $values = getValuesFromDB($db, $_SESSION['login']);
        //$messages[] = sprintf('Вход с логином %s, uid %d', $_SESSION['login'], $_SESSION['uid']);
      }
  }

  include('form.php');
}
else { 
  if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Недействительный CSRF-токен');
  }
  
  $errors = checkErrorAndSaveErrorCookies($_POST, $abilities);
  saveValueCookies($_POST, $abilities);

  if (!empty($errors)) {
    header('Location: index.php');
    exit();
  }
  else {
    deleteErrorCookies();
  }

  if (!empty($_COOKIE[session_name()]) && !empty($_SESSION['login'])) {
    updateApplication($db, $_SESSION['uid']);
  }
  else {
    $login = substr(md5(time()), 0, 9);
    $pass = substr(md5(time()), 10, 19);

    setcookie('login', $login);
    setcookie('pass', $pass);

    saveToApplication($db);
    $id_app = saveToConnection($db);
    saveToAuth($db, $pass, $login, $id_app);
  }

  setcookie('save', '1');
  header('Location: ./');
}



