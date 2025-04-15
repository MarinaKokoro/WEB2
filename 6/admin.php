<?php
/*
  - Оформить визуал
  - Вынести всё лишнее в файлы (подписано)
  - Вынести данные админа в отдельную БД

*/

//Вынести в отдельный файл (и из index.php)
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
      print('Ошибка при сохранении данных: ' . $e->getMessage());
      exit();
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
    print('Ошибка при удалении данных: ' . $e->getMessage());
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

function checkAdminAuth($db) {
    $admin_data = getAdminData($db, empty($_SERVER['PHP_AUTH_PW']) ? '' : $_SERVER['PHP_AUTH_PW']);
    
    if (empty($_SERVER['PHP_AUTH_USER']) || 
        empty($_SERVER['PHP_AUTH_PW']) ||
        !password_verify($_SERVER['PHP_AUTH_PW'], $admin_data['pass'])) 
    {
        header('HTTP/1.1 401 Unauthorized');
        header('WWW-Authenticate: Basic realm="Admin Panel"');
        print('<h1>401 Требуется авторизация</h1>');
        exit();
    }
}



checkAdminAuth();
$db = getDatabase();
$abilities = getAbilities($db);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Удаление записи
    if (isset($_POST['delete'])) {
        deleteUserData($db, $_POST['delete']);
    } 

    // Редактирование записи
    elseif (isset($_POST['update'])) {
      updateApplication($db, $_POST['update']);
    }
    header("Location: admin.php");
    exit();
}

// Вынести в функции для блока(файла) базы данных
// Получение данных пользователей
$users = $db->query("SELECT app.id_app, app.name, app.phone, app.email, app.dateBirth, app.sex, app.bio
                      FROM application app"
                    )->fetchAll();

$user_lang = $db->query("SELECT c.id_app, c.id_lang, l.name
                      FROM connection c 
                      JOIN langs l 
                        ON c.id_lang = l.id_lang"
                    )->fetchAll();                    

// Получение статистики
$stats = $db->query("SELECT l.name, count(*) as user_count
                      FROM application app 
                      INNER JOIN connection c
                        ON app.id_app = c.id_app
                      JOIN langs l 
                        ON c.id_lang = l.id_lang
                      GROUP BY
                        l.id_lang"
                    )->fetchAll();


// Проверяем, нужно ли показать форму редактирования
$edit_id = $_GET['edit'] ?? null;
$user_to_edit = null;
$user_langs = []; 

if ($edit_id) {
    foreach ($users as $user) {
        if ($user['id_app'] == $edit_id) {  
            $user_to_edit = $user;
            break;
        }
    }
    if ($user_to_edit) {
        foreach ($user_lang as $lang) {
            if ($lang['id_app'] == $user_to_edit['id_app']) {
                $user_langs[] = $lang['id_lang'];
            }
        }
    }
}
?>



<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админка</title>
    <link rel='stylesheet' href='style.css'>
</head>
<body>




    <h2>Данные пользователей</h2> 
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Имя</th>
                <th>Телефон</th>
                <th>Email</th>
                <th>Дата рождения</th>
                <th>Пол</th>
                <th>Биография</th>
                <th>Любимые ЯП</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['id_app']) ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['phone']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['dateBirth']) ?></td>
                <td><?= htmlspecialchars($user['sex']) ?></td>
                <td><?= htmlspecialchars($user['bio']) ?></td>
                <td><?php 
                  foreach ($user_lang as $lang){
                    if($lang['id_app'] == $user['id_app']){
                      echo ($lang['name']);
                      echo(" ");
                    }
                  }
                  ?> 
                </td>
                <td>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="delete" value="<?= $user['id_app'] ?>">
                        <button type="submit">Удалить</button>
                    </form>
                    <a href="admin.php?edit=<?= $user['id_app'] ?>">Редактировать</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>





    <?php if ($user_to_edit): ?>
    <div class="edit-form">
        <h2>Редактирование заявки #<?= htmlspecialchars($user_to_edit['id_app']) ?></h2>
        <form method="post">
            <input type="hidden" name="update" value="<?= (int)$user_to_edit['id_app'] ?>">
            
            <label>ФИО:<br>
                <input name="fio" type="text" value="<?= htmlspecialchars($user_to_edit['name']) ?>">
            </label><br><br>
            
            <label>Телефон:<br>
                <input name="telephone" type="tel" value="<?= htmlspecialchars($user_to_edit['phone']) ?>">
            </label><br><br>
            
            <label>Email:<br>
                <input name="email" type="email" value="<?= htmlspecialchars($user_to_edit['email']) ?>">
            </label><br><br>
            
            <label>Дата рождения:<br>
                <input name="dateOfBirth" type="date" value="<?= htmlspecialchars($user_to_edit['dateBirth']) ?>">
            </label><br><br>
            
            <label>Пол:<br>
                <label><input type="radio" name="radio" value="female" <?= $user_to_edit['sex'] == 'female' ? 'checked' : '' ?>> Женский</label>
                <label><input type="radio" name="radio" value="male" <?= $user_to_edit['sex'] == 'male' ? 'checked' : '' ?>> Мужской</label>
            </label><br><br>

            <label>Любимые языки программирования:<br>
            <select name="abilities[]" multiple>
                <?php foreach ($abilities as $id_lang => $name): ?>
                    <option value="<?= $id_lang ?>" <?= in_array($id_lang, $user_langs) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            </label><br><br>
            
            <label>Биография:<br>
                <textarea name="bio"><?= htmlspecialchars($user_to_edit['bio']) ?></textarea>
            </label><br><br>
            
            <button type="submit">Сохранить</button>
            <a href="admin.php">Отмена</a>
        </form>
    </div>
    <?php endif; ?>

    <h2>Статистика по языкам</h2>
    <table>
        <thead>
            <tr>
                <th>Язык программирования</th>
                <th>Количество пользователей</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stats as $stat): ?>
            <tr>
                <td><?= htmlspecialchars($stat['name']) ?></td>
                <td><?= $stat['user_count'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>


