<?php
header('Content-Type: text/html; charset=UTF-8');
echo "<link rel='stylesheet' href='style.css'>";

function err_check($P, $abilities) {
    $errors = FALSE;

    if (empty($P['fio']) || !preg_match('/^([A-Z]|[a-z]| |[а-я]|[А-Я]){3,150}$/ui', $P['fio'])) {
      print('Заполните имя.<br/>');
      $errors = TRUE;
    }

    if (empty($P['telephone']) || !preg_match('/^\+?[0-9]{11,14}$/', $P['telephone'])) {
      print('Заполните телефон.<br/>');
      $errors = TRUE;
    }

    if (empty($P['email']) || !preg_match('/^\w{1,80}@\w{1,10}.\w{1,10}$/', $P['email'])) {
      print('Заполните почту.<br/>');
      $errors = TRUE;
    }

    if (empty($P['dateOfBirth']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $P['dateOfBirth'])) {
      print('Заполните дату рождения.<br/>');
      $errors = TRUE;
    }

    if (empty($P['radio']) || !($P['radio'] == "female" || $P['radio'] == "male")) {
      print('Выберите пол.<br/>');
      $errors = TRUE;
    }
    
    if (empty($P['abilities'])) {
      print('Выберите любимый ЯП.<br/>');
      $errors = TRUE;
    }
    else{
      foreach ($_POST['abilities'] as $ability) {
        if (empty($abilities[$ability])){
          print('Выберите любимый ЯП из списка.<br/>');
          $errors = TRUE;
        }
      }
    }

    if (empty($P['bio']) || !preg_match('/^(\w|\s|.|!|,|\?|\(|\)){1,1000}$/', $P['bio'])) {
      print('Заполните биографию.<br/>');
      $errors = TRUE;
    }

    if (empty($P['check'])) {
      print('Согласитесь с котрактом или покиньте сайт.<br/>');
      $errors = TRUE;
    }
    return $errors;
}
function getAbilities($db){
  try {
    $abilities = [];
    $data = $db->query("SELECT id_lang, name FROM langs")->fetchAll();
    foreach ($data as $ability) {
      $name = $ability['name'];
      $id_lang = $ability['id_lang'];
      $abilities[$id_lang] = $name;
    }
    return $abilities;
  }
  catch(PDOException $e){
    print('Error: ' . $e->getMessage());
    exit();
  }
}
function toApplication($db){
  try {
    $stmt = $db->prepare("INSERT INTO application (name, phone, email, dateBirth, sex, bio) VALUES (:name, :phone, :email, :dateBirth, :sex, :bio)");
    $stmt->bindParam(':name', $_POST['fio']);
    $stmt->bindParam(':phone', $_POST['telephone']);
    $stmt->bindParam(':email', $_POST['email']);
    $stmt->bindParam(':dateBirth', $_POST['dateOfBirth']);
    $stmt->bindParam(':sex', $_POST['radio']);
    $stmt->bindParam(':bio', $_POST['bio']);
    $stmt->execute();
  }
  catch(PDOException $e){
    print('Error: ' . $e->getMessage());
    exit();
  }
}
function toConnection($db){
  try {
    $id_app = $db->lastInsertId();
    $stmt = $db->prepare("INSERT INTO connection (id_app, id_lang) VALUES (:id_app, :id_lang)");
    foreach ($_POST['abilities'] as $ability) {
      $stmt->bindParam(':id_app', $id_app);
      $stmt->bindParam(':id_lang', $ability);
      $stmt->execute();
    }
  }
  catch(PDOException $e){
    print('Error: ' . $e->getMessage());
    exit();
  }
}

// Входим в базу данных.
$user = 'u68859'; 
$pass = '5248297'; 
$db = new PDO('mysql:host=localhost;dbname=u68859', $user, $pass, 
      [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); 

// Получаем списки языков
$abilities = getAbilities($db);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

  if (!empty($_GET['save'])) {
    print('Спасибо, результаты сохранены.');
  }

  include('form.php');
  exit();
}

if (err_check($_POST, $abilities)) {
  exit();
}

toApplication($db);
toConnection($db);

header('Location: ?save=1');


