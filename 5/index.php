<?php
session_start();
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
function getValuesFromDB($db, $login){
  $id = $db->prepare("SELECT id_app FROM auth where login = :login");
  $id->bindParam(':login', $login);
  $id->execute();
  $id_app = $id->fetch(PDO::FETCH_ASSOC);

  $data = $db->prepare("SELECT name, phone, email, dateBirth, sex, bio FROM application where id_app = ?");
  $data->execute([$id_app['id_app']]);
  $user = $data->fetch(PDO::FETCH_ASSOC);

  $values = array();
  if ($user) {
            $values = [
                'fio' => $user['name'],
                'telephone' => $user['phone'],
                'email' => $user['email'],
                'dateOfBirth' => $user['dateBirth'],
                'bio' => $user['bio'],
            ];

            $tmp = $db->prepare("SELECT id_lang FROM connection WHERE id_app = ?");
            $tmp->execute([$id_app['id_app']]);
            $tmp_ab = $tmp->fetchAll(PDO::FETCH_ASSOC);

            foreach ($tmp_ab as $id_lang) {
                $values[$id_lang['id_lang']] = 1;
            }
        }
  return $values;
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
    return $id_app;
  }
  catch(PDOException $e){
    print('Error: ' . $e->getMessage());
    exit();
  }
}
function saveToAuth($db, $pass, $login, $id_app){
  try {
      $hash_pass = md5($pass);
      $stmt = $db->prepare("INSERT INTO auth (id_app, login, pass) VALUES (:id_app, :login, :pass)");
      $stmt->bindParam(':id_app', $id_app);
      $stmt->bindParam(':login', $login);
      $stmt->bindParam(':pass', $hash_pass);
      $stmt->execute();
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

function setMessagesAndDeleteCookies($errors, $abilities){
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
    foreach($abilities as $key => $value){
      setcookie($key, '', 100000);
    }
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
function getValuesFromCookies($abilities){
  $values = array();

  $values['fio'] = empty($_COOKIE['fio_value']) ? '' : $_COOKIE['fio_value'];
  $values['telephone'] = empty($_COOKIE['telephone_value']) ? '' : $_COOKIE['telephone_value'];
  $values['email'] = empty($_COOKIE['email_value']) ? '' : $_COOKIE['email_value'];
  $values['dateOfBirth'] = empty($_COOKIE['dateOfBirth_value']) ? '' : $_COOKIE['dateOfBirth_value'];
  foreach($abilities as $key => $value){
    $values[$key] = empty($_COOKIE[$key]) ? '' : $_COOKIE[$key];
  }
  
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
function saveValueCookies($P, $abilities){
  setcookie('fio_value', $P['fio'], time() + 12 * 30 * 24 * 60 * 60);
  setcookie('telephone_value', $P['telephone'], time() + 12 * 30 * 24 * 60 * 60);
  setcookie('email_value', $P['email'], time() + 12 * 30 * 24 * 60 * 60);
  setcookie('dateOfBirth_value', $P['dateOfBirth'], time() + 12 * 30 * 24 * 60 * 60);
  //print_r($P);
  foreach($P['abilities'] as $key => $value){
    setcookie($value, 1, time() + 12 * 30 * 24 * 60 * 60);
  }
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
    elseif(!preg_match('/^[\w\s!?,.()]{1,1000}$/u', $P['bio'])){
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
    setcookie('login', '', 100000);
    setcookie('pass', '', 100000);
    $messages[] = '<div class="success">Спасибо, результаты сохранены.</div>';
  }

   // Если в куках есть пароль, то выводим сообщение.
  if (!empty($_COOKIE['pass'])) {

    $messages[] = sprintf('Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong>
      и паролем <strong>%s</strong> для изменения данных.',
      strip_tags($_COOKIE['login']),
      strip_tags($_COOKIE['pass']));
  }

  $errors = getErrors();
  $errorMessages = setMessagesAndDeleteCookies($errors, $abilities);
  $messages = array_merge($messages, $errorMessages);
  $values = getValuesFromCookies($abilities);

  if(isset($_COOKIE[session_name()])){
    //if(session_start()){
      if(!empty($_SESSION['login'])) {
        $values = getValuesFromDB($db, $_SESSION['login']);
        $messages[] = sprintf('Вход с логином %s, uid %d', $_SESSION['login'], $_SESSION['uid']);
      }
  //}
  }

  include('form.php');
}
else { 
  
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
    try {
      $stmt = $db->prepare("UPDATE application SET name = ?, phone = ?, email = ?, dateBirth = ?, sex = ?, bio = ? WHERE id_app = ?");
      $stmt->execute([
              $_POST['fio'], $_POST['telephone'], $_POST['email'], $_POST['dateOfBirth'], $_POST['radio'], $_POST['bio'], $_SESSION['uid']
          ]);

      $stmt = $db->prepare("DELETE FROM connection WHERE id_app = ?");
      $stmt->execute([$_SESSION['uid']]);

      foreach ($_POST['abilities'] as $id_lang) {
          $stmt = $db->prepare("INSERT INTO connection (id_app, id_lang) VALUES (?, ?)");
          $stmt->execute([$_SESSION['uid'], $id_lang]);
      }
    } catch (PDOException $e) {
        print('Ошибка при сохранении данных: ' . $e->getMessage());
        exit();
    }
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

  // Сохраняем куку с признаком успешного сохранения.
  setcookie('save', '1');

  header('Location: ./');
}




