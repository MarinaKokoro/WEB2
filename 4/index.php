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
function saveToApplication($db){
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
function saveToConnection($db){
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

function getErrors(){
  $errors = array();

  $errors['fio'] = empty($_COOKIE['fio_error']) ? 0 : $_COOKIE['fio_error'];
  $errors['telephone'] = empty($_COOKIE['telephone_error']) ? 0 : $_COOKIE['telephone_error'];
  $errors['email'] = empty($_COOKIE['email_error']) ? 0 : $_COOKIE['email_error'];
  $errors['dateOfBirth'] = empty($_COOKIE['dateOfBirth_error']) ? 0 : $_COOKIE['dateOfBirth_error'];
  $errors['radio'] = empty($_COOKIE['radio_error']) ? 0 : $_COOKIE['radio_error'];
  $errors['abilities'] = empty($_COOKIE['abilities_error']) ? 0 : $_COOKIE['abilities_error'];
  $errors['bio'] = empty($_COOKIE['bio_error']) ? 0 : $_COOKIE['bio_error'];
  $errors['check'] = empty($_COOKIE['check_error']) ? 0 : $_COOKIE['check_error'];

  return $errors;
}

function setMessagesAndDeleteCookies($errors){
  $messages = array();

  // Выдаем сообщения об ошибках.
  if ($errors['fio'] != 0) {
    setcookie('fio_error', '', 100000);
    setcookie('fio_value', '', 100000);
    if ($errors['fio'] == 1)
      $messages[] = '<div class="error">Заполните имя.</div>';
    else
      $messages[] = '<div class="error">Допустимые символы для имени: 
        <br> латинские и кириллические буквы, пробелы. 
        <br> Длина от 3 до 150 символов</div>';
  }

  if ($errors['telephone'] != 0) {
    setcookie('telephone_error', '', 100000);
    setcookie('telephone_value', '', 100000);
    if ($errors['telephone'] == 1)
      $messages[] = '<div class="error">Заполните телефон.</div>';
    else
      $messages[] = '<div class="error">Допустимые символы для телефона: 
        <br> \'+\' и цифры. 
        <br> Длина от 11 до 14 символов</div>';
  }

  if ($errors['email'] != 0) {
    setcookie('email_error', '', 100000);
    setcookie('email_value', '', 100000);
    if ($errors['email'] == 1)
      $messages[] = '<div class="error">Заполните почту.</div>';
    else
      $messages[] = '<div class="error">Допустимые символы для почты: 
        <br> \'@\',  \'.\', цифры и латинские буквы. 
        <br> Длина от 3 до 100 символов</div>';
  }

  if ($errors['dateOfBirth'] != 0) {
    setcookie('dateOfBirth_error', '', 100000);
    setcookie('dateOfBirth_value', '', 100000);
    if ($errors['dateOfBirth'] == 1)
      $messages[] = '<div class="error">Заполните дату рождения.</div>';
    else
      $messages[] = '<div class="error">Формат даты: 
        <br> yyyy-mm-dd </div>';
  }

  if ($errors['radio'] != 0) {
    setcookie('radio_error', '', 100000);
    if ($errors['radio'] == 1)
      $messages[] = '<div class="error">Заполните пол.</div>';
    else
      $messages[] = '<div class="error">Допустимые значения для пола:
        <br> \'female\' и \'male\' </div>';
  }

  if ($errors['abilities'] != 0) {
    setcookie('abilities_error', '', 100000);
    if ($errors['abilities'] == 1)
      $messages[] = '<div class="error">Заполните любимые ЯП.</div>';
    else
      $messages[] = '<div class="error">Выберите любимый ЯП из списка.</div>';
  }

  if ($errors['bio'] != 0) {
    setcookie('bio_error', '', 100000);
    setcookie('bio_value', '', 100000);
    if ($errors['bio'] == 1)
      $messages[] = '<div class="error">Заполните биографию.</div>';
    else
      $messages[] = '<div class="error">Допустимые символы для биографии:
        <br> Буквы и цифры.!,?()
        <br> Длина до 1000 символов </div>';
  }
  if ($errors['check'] != 0) {
    setcookie('check_error', '', 100000);
    $messages[] = '<div class="error">Согласитесь с контрактом.</div>';
  }
  
  return $messages;
}
function getValuesFromCookies(){
  $values = array();

  $values['fio'] = empty($_COOKIE['fio_value']) ? '' : $_COOKIE['fio_value'];
  $values['telephone'] = empty($_COOKIE['telephone_value']) ? '' : $_COOKIE['telephone_value'];
  $values['email'] = empty($_COOKIE['email_value']) ? '' : $_COOKIE['email_value'];
  $values['dateOfBirth'] = empty($_COOKIE['dateOfBirth_value']) ? '' : $_COOKIE['dateOfBirth_value'];
  $values['bio'] = empty($_COOKIE['bio_value']) ? '' : $_COOKIE['bio_value'];

  return $values;
}
function deleteErrorCookies(){
  setcookie('fio_error', '', 100000);
    setcookie('telephone_error', '', 100000);
    setcookie('email_error', '', 100000);
    setcookie('dateOfBirth_error', '', 100000);
    setcookie('radio_error', '', 100000);
    setcookie('abilities_error', '', 100000);
    setcookie('bio_error', '', 100000);
    setcookie('check_error', '', 100000);
}
function saveValueCookies($P){
  setcookie('fio_value', $P['fio'], time() + 12 * 30 * 24 * 60 * 60);
  setcookie('telephone_value', $P['telephone'], time() + 12 * 30 * 24 * 60 * 60);
  setcookie('email_value', $P['email'], time() + 12 * 30 * 24 * 60 * 60);
  setcookie('dateOfBirth_value', $P['dateOfBirth'], time() + 12 * 30 * 24 * 60 * 60);
  setcookie('bio_value', $P['bio'], time() + 12 * 30 * 24 * 60 * 60);
}
function checkErrorAndSaveErrorCookies($P, $abilities) {
    $errors = FALSE;
    
    if (empty($P['fio'])) {
      setcookie('fio_error', '1', time() + 24 * 60 * 60);
      $errors = TRUE;
    }
    elseif(!preg_match('/^([A-Z]|[a-z]| |[а-я]|[А-Я]){3,150}$/ui', $P['fio'])){
      setcookie('fio_error', '2', time() + 24 * 60 * 60);
      $errors = TRUE;
    }


    if (empty($P['telephone'])) {
      setcookie('telephone_error', '1', time() + 24 * 60 * 60);
      $errors = TRUE;
    }
    elseif(!preg_match('/^\+?[0-9]{11,14}$/', $P['telephone'])){
      setcookie('telephone_error', '2', time() + 24 * 60 * 60);
      $errors = TRUE;
    }
    

    if (empty($P['email'])) {
      setcookie('email_error', '1', time() + 24 * 60 * 60);
      $errors = TRUE;
    }
    elseif(!preg_match('/^\w{1,80}@\w{1,10}.\w{1,10}$/', $P['email'])){
      setcookie('email_error', '2', time() + 24 * 60 * 60);
      $errors = TRUE;
    }
    

    if (empty($P['dateOfBirth'])) {
      setcookie('dateOfBirth_error', '1', time() + 24 * 60 * 60);
      $errors = TRUE;
    }
    elseif(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $P['dateOfBirth'])){
      setcookie('dateOfBirth_error', '2', time() + 24 * 60 * 60);
      $errors = TRUE;
    }
    

    if (empty($P['radio'])) {
      setcookie('radio_error', '1', time() + 24 * 60 * 60);
      $errors = TRUE;
    }
    elseif(!($P['radio'] == "female" || $P['radio'] == "male")){
      setcookie('radio_error', '2', time() + 24 * 60 * 60);
      $errors = TRUE;
    }
    
  
    if (empty($P['abilities'])) {
      setcookie('abilities_error', '1', time() + 24 * 60 * 60);
      $errors = TRUE;
    }
    else{
      foreach ($_POST['abilities'] as $ability) {
        if (empty($abilities[$ability])){
          setcookie('abilities_error', '2', time() + 24 * 60 * 60);
          $errors = TRUE;
        }
      }
    }
   

    if (empty($P['bio'])) {
      setcookie('bio_error', '1', time() + 24 * 60 * 60);
      $errors = TRUE;
    }
    elseif(!preg_match('/^(\w|\s|.|!|,|\?|\(|\)){1,1000}$/', $P['bio'])){
      setcookie('bio_error', '2', time() + 24 * 60 * 60);
      $errors = TRUE;
    }
    
    
    if (empty($P['check'])) {
      setcookie('check_error', '1', time() + 24 * 60 * 60);
      $errors = TRUE;
    }

    
    return $errors;
}

$db = getDatabase();
$abilities = getAbilities($db);


if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  $messages = array();

  if (!empty($_COOKIE['save'])) {
    setcookie('save', '', 100000);
    $messages[] = 'Спасибо, результаты сохранены.';
  }

  $errors = getErrors();
  $messages = setMessagesAndDeleteCookies($errors);
  $values = getValuesFromCookies();
  include('form.php');
}
else {  
  print_r($_POST);
   $errors = checkErrorAndSaveErrorCookies($_POST, $abilities);
  saveValueCookies($_POST);

  if ($errors) {
    header('Location: index.php');
    print_r($_POST);
    exit();
  }
  else {
    deleteErrorCookies();
  }

  saveToApplication($db);
  saveToConnection($db);

  // Сохраняем куку с признаком успешного сохранения.
  setcookie('save', '1');

  header('Location: index.php');
}




