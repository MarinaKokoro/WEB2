<?php
header("Content-type: text/html; charset: UTF-8");
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$allowed_includes = ['modules/db.php', 'modules/validation.php'];

$file = 'modules/db.php';
if (in_array($file, $allowed_includes) && file_exists($file)) {
    include $file;
} else {
    die('Недопустимый файл для включения');
}



function checkAdminAuth($db) {
    $admin_data = getAdminData($db, empty($_SERVER['PHP_AUTH_USER']) ? '' : $_SERVER['PHP_AUTH_USER']);
    
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

checkAdminAuth($db);
$abilities = getAbilities($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['delete'])) {
        deleteUserData($db, $_POST['delete']);
    } 
    elseif (isset($_POST['update'])) {
      updateApplication($db, $_POST['update']);
    }

    header("Location: admin.php");
    exit();
}

$users = getAllUserData($db);
$user_lang = getAllConnectionData($db);            
$stats = getStatsAboutLangs($db);

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



  <?php if ($user_to_edit == null): ?>
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
                <td><?php echo htmlspecialchars($user['id_app']) ?></td>
                <td><?php echo htmlspecialchars($user['name']) ?></td>
                <td><?php echo htmlspecialchars($user['phone']) ?></td>
                <td><?php echo htmlspecialchars($user['email']) ?></td>
                <td><?php echo htmlspecialchars($user['dateBirth']) ?></td>
                <td><?php echo htmlspecialchars($user['sex']) ?></td>
                <td><?php echo htmlspecialchars($user['bio']) ?></td>
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
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="delete" value="<?php echo htmlspecialchars( $user['id_app']) ?>">
                        <button type="submit">Удалить</button>
                    </form>
                    <a href="admin.php?edit=<?php echo htmlspecialchars( $user['id_app']) ?>" class="edit-link">Редактировать</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
  <?php endif; ?>




    <?php if ($user_to_edit): ?>
    <div class="edit-form">
        <h2>Редактирование заявки #<?php echo htmlspecialchars($user_to_edit['id_app']) ?></h2>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="update" value="<?php echo htmlspecialchars( (int)$user_to_edit['id_app']) ?>">
            
            <label>ФИО:
                <input name="fio" type="text" value="<?php echo htmlspecialchars($user_to_edit['name']) ?>">
            </label>
            
            <label>Телефон:
                <input name="telephone" type="tel" value="<?php echo htmlspecialchars($user_to_edit['phone']) ?>">
            </label>
            
            <label>Email:
                <input name="email" type="email" value="<?php echo htmlspecialchars($user_to_edit['email']) ?>">
            </label>
            
            <label>Дата рождения:
                <input name="dateOfBirth" type="date" value="<?php echo htmlspecialchars($user_to_edit['dateBirth']) ?>">
            </label>
            
            <div class="radio-group">
                <label>Пол:</label>
                <label><input type="radio" name="radio" value="female" <?php echo htmlspecialchars( $user_to_edit['sex']) == 'female' ? 'checked' : '' ?>> Женский</label>
                <label><input type="radio" name="radio" value="male" <?php echo htmlspecialchars( $user_to_edit['sex']) == 'male' ? 'checked' : '' ?>> Мужской</label>
            </div>

            <label>Любимые языки программирования:
                <select name="abilities[]" multiple>
                    <?php foreach ($abilities as $id_lang => $name): ?>
                        <option value="<?php echo htmlspecialchars( $id_lang) ?>" <?php echo htmlspecialchars( in_array($id_lang, $user_langs)) ? 'selected' : '' ?>>
                            <?php echo htmlspecialchars($name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            
            <label>Биография:
                <textarea name="bio"><?php echo htmlspecialchars($user_to_edit['bio']) ?></textarea>
            </label>
            
            <div class="form-actions">
                <button type="submit">Сохранить</button>
                <a href="admin.php">Отмена</a>
            </div>
        </form>
    </div>
    <?php endif; ?>
    


    <?php if ($user_to_edit == null): ?>
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
                <td><?php echo htmlspecialchars($stat['name']) ?></td>
                <td><?php echo htmlspecialchars($stat['user_count']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

</body>
</html>


