<?php
header('Content-Type: text/html; charset=UTF-8');

function err_check($P) {
    $errors = FALSE;

    if (empty($P['fio']) || !preg_match('/^([A-Z]|[a-z]| |[а-я]|[А-Я]){3,150}$/ui', $P['fio'])) {
      print('Заполните имя.<br/>');
      $errors = TRUE;
    }

    if (empty($P['telephone']) || !preg_match('/^\+?[0-9]{11,14}$/', $P['telephone'])) {
      print('Заполните телефон.<br/>');
      $errors = TRUE;
    }

    if (empty($P['email']) || !preg_match('/^\w+@\w+.\w+$/', $P['email'])) {
      print('Заполните почту.<br/>');
      $errors = TRUE;
    }
    //[dateOfBirth] => 2005-11-21
    if (empty($P['dateOfBirth']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $P['dateOfBirth'])) {
      print('Заполните дату рождения.<br/>');
      $errors = TRUE;
    }

    if (empty($P['radio']) || !($P['radio'] == "female" || $P['radio'] == "male")) {
      print('Выберите пол.<br/>');
      $errors = TRUE;
    }
    //!
    if (empty($P['abilities'])) {
      print('Выберите любимый ЯП.<br/>');
      $errors = TRUE;
    }

    if (empty($P['bio']) || !preg_match('/^(\w|\s)+$/', $P['bio'])) {
      print('Заполните биографию.<br/>');
      $errors = TRUE;
    }

    if (empty($P['check'])) {
      print('Согласитесь с котрактом или покиньте сайт.<br/>');
      $errors = TRUE;
    }
    return $errors;
}

// В суперглобальном массиве $_SERVER PHP сохраняет некторые заголовки запроса HTTP
// и другие сведения о клиненте и сервере, например метод текущего запроса $_SERVER['REQUEST_METHOD'].

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  // В суперглобальном массиве $_GET PHP хранит все параметры, переданные в текущем запросе через URL.
  if (!empty($_GET['save'])) {
    // Если есть параметр save, то выводим сообщение пользователю.
    print('Спасибо, результаты сохранены.');
  }

  // Включаем содержимое файла form.php.
  include('form.php');
  // Завершаем работу скрипта.
  exit();
}
// Иначе, если запрос был методом POST, т.е. нужно проверить данные и сохранить их в БД.

//echo '<pre>';
//print_r($_POST);
//echo '</pre>';

// Проверяем ошибки.
if (err_check($_POST)) {
  exit();
}

// Сохранение в базу данных.

$user = 'u68859'; // Заменить на ваш логин uXXXXX
$pass = '5248297'; // Заменить на пароль
$db = new PDO('mysql:host=localhost;dbname=u68859', $user, $pass,
  [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); // Заменить test на имя БД, совпадает с логином uXXXXX

// Подготовленный запрос. Не именованные метки.
try {
  $stmt = $db->prepare("INSERT INTO application SET name = ?");
  $stmt->execute([$_POST['fio']]);
}
catch(PDOException $e){
  print('Error : ' . $e->getMessage());
  exit();
}

//  stmt - это "дескриптор состояния".
 
//  Именованные метки.
//$stmt = $db->prepare("INSERT INTO test (label,color) VALUES (:label,:color)");
//$stmt -> execute(['label'=>'perfect', 'color'=>'green']);
 
//Еще вариант
/*$stmt = $db->prepare("INSERT INTO users (firstname, lastname, email) VALUES (:firstname, :lastname, :email)");
$stmt->bindParam(':firstname', $firstname);
$stmt->bindParam(':lastname', $lastname);
$stmt->bindParam(':email', $email);
$firstname = "John";
$lastname = "Smith";
$email = "john@test.com";
$stmt->execute();
*/

// Делаем перенаправление.
// Если запись не сохраняется, но ошибок не видно, то можно закомментировать эту строку чтобы увидеть ошибку.
// Если ошибок при этом не видно, то необходимо настроить параметр display_errors для PHP.
header('Location: ?save=1');
