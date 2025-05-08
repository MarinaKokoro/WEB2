<?php
function getDatabase(){
  $user = 'u68859'; 
  $pass = '5248297'; 
  $db = new PDO('mysql:host=localhost;dbname=u68859', $user, $pass, 
      [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); 

  return $db;
}
$db = getDatabase();

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
    error_log('Database error: ' . $e->getMessage());
    die('Произошла ошибка базы данных');
  }
}
function getValuesFromDB($db, $login){
  try{
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
  catch (PDOException $e) {
    print('Ошибка при получении данных: ' . $e->getMessage());
    exit();
  }
}
function getAdminData($db, $login){
  try{
    $data = $db->prepare("SELECT login, pass FROM admins where login = :login");
    $data->bindParam(':login', $login);
    $data->execute();
    $data = $data->fetch(PDO::FETCH_ASSOC);
    return $data;
  }
  catch (PDOException $e) {
    print('Ошибка при получении данных: ' . $e->getMessage());
    exit();
  }
}
function getAllUserData($db){
  try{
    $users = $db->query("SELECT app.id_app, app.name, app.phone, app.email, app.dateBirth, app.sex, app.bio
                         FROM application app"
                       )->fetchAll();
    return $users;
  }
    catch (PDOException $e) {
    print('Ошибка при получении данных: ' . $e->getMessage());
    exit();
  }
}
function getAllConnectionData($db){
  try{
    $connection = $db->query("SELECT c.id_app, c.id_lang, l.name
                      FROM connection c 
                      JOIN langs l 
                        ON c.id_lang = l.id_lang"
                    )->fetchAll();    
    return $connection;
  }
    catch (PDOException $e) {
    print('Ошибка при получении данных: ' . $e->getMessage());
    exit();
  }
}
function getStatsAboutLangs($db){
  try{
    $stats = $db->query("SELECT l.name, count(*) as user_count
                      FROM application app 
                      INNER JOIN connection c
                        ON app.id_app = c.id_app
                      JOIN langs l 
                        ON c.id_lang = l.id_lang
                      GROUP BY
                        l.id_lang"
                    )->fetchAll();
    return $stats;
  }
    catch (PDOException $e) {
    print('Ошибка при получении данных: ' . $e->getMessage());
    exit();
  }
}
function getUserId($db, $login){
  try{
    $data = $db->prepare("SELECT id_app FROM auth WHERE login = ?");
    $data->execute([$login]);
    $user = $data->fetch(PDO::FETCH_ASSOC);
    return $user['id_app'];
  }
  catch(PDOException $e){
    error_log('Database error: ' . $e->getMessage());
    die('Произошла ошибка базы данных');
  }
}
function getUserAuthData($db, $login){
  try {
    $data = $db->prepare("SELECT pass FROM auth WHERE login = ?");
    $data->execute([$login]);
    $user = $data->fetch(PDO::FETCH_ASSOC);
    return $user;
  }
  catch(PDOException $e){
    error_log('Database error: ' . $e->getMessage());
    die('Произошла ошибка базы данных');
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
    error_log('Database error: ' . $e->getMessage());
    die('Произошла ошибка базы данных');
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
    error_log('Database error: ' . $e->getMessage());
    die('Произошла ошибка базы данных');
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
      error_log('Database error: ' . $e->getMessage());
      die('Произошла ошибка базы данных');
    }
}

function updateApplication($db, $id){
  try {
      $stmt = $db->prepare("UPDATE application SET name = ?, phone = ?, email = ?, dateBirth = ?, sex = ?, bio = ? WHERE id_app = ?");
      $stmt->execute([
              $_POST['fio'], $_POST['telephone'], $_POST['email'], $_POST['dateOfBirth'], $_POST['radio'], $_POST['bio'], $id
          ]);

      $stmt = $db->prepare("DELETE FROM connection WHERE id_app = ?");
      $stmt->execute([$id]);

      foreach ($_POST['abilities'] as $id_lang) {
          $stmt = $db->prepare("INSERT INTO connection (id_app, id_lang) VALUES (?, ?)");
          $stmt->execute([$id, $id_lang]);
      }
  } catch (PDOException $e) {
      error_log('Database error: ' . $e->getMessage());
      die('Произошла ошибка базы данных');
  }
}

function deleteUserData($db, $id){
  try{
    $db->beginTransaction();

    $stmt = $db->prepare("DELETE FROM connection WHERE id_app = ?");
    $stmt->execute([$id]);

    $stmt = $db->prepare("DELETE FROM auth WHERE id_app = ?");
    $stmt->execute([$id]);

    $stmt = $db->prepare("DELETE FROM application WHERE id_app = ?");
    $stmt->execute([$id]);

    $db->commit();
  } 
  catch (PDOException $e) {
    $db->rollBack();
    error_log('Database error: ' . $e->getMessage());
    die('Произошла ошибка базы данных');
  }
}


?>