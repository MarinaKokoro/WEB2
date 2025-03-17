<html>
  <body>

    <?php

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

    $db = getDatabase();
    $abilities = getAbilities($db);

    if (!empty($messages)) {
      print('<div id="messages">');
      foreach ($messages as $message) {
        print($message);
      }
      print('</div>');
    }?>

    <form id="form" action="" method="POST">

      <input name="fio" type="text" placeholder="ФИО" 
          <?php if ($errors['fio']) {print 'class="error"';} ?> value="<?php print $values['fio']; ?>"><br>
      
      <input name="telephone" type="tel" placeholder="Номер телефона"
          <?php if ($errors['telephone']) {print 'class="error"';} ?> value="<?php print $values['telephone']; ?>"><br>
      
      <input name="email" type="email" placeholder="Email@mail.mail"
          <?php if ($errors['email']) {print 'class="error"';} ?> value="<?php print $values['email']; ?>"><br>

      Дата рождения:<br>
      <input name="dateOfBirth" type="date"
          <?php if ($errors['dateOfBirth']) {print 'class="error"';} ?> value="<?php print $values['dateOfBirth']; ?>"><br>

      Пол:<br>
      <div <?php if ($errors['radio']) {print 'class="error"';} ?>>
        <label><input class="radio" name="radio" checked="checked" type="radio" value="female">Женский</label>
        <label><input class="radio" name="radio" type="radio" value="male">Мужской</label><br>
      </div>
      
      Любимый язык программирования:
      <br>
      <div <?php if ($errors['abilities']) {print 'class="error"';} ?>>
        <select id="abilities" name="abilities[]" multiple="multiple">
            <?php 
              foreach ($abilities as $key => $value) {
                //
                $selected = $values['abilities'][$key];
                printf('<option value="%s"', $key);
                if($selected){
                  print(' selected ');
                }
                printf('>%s</option>', $value);
              } 
            ?>
        </select>
      </div><br>
        
      Биография:<br>
      <div <?php if ($errors['bio']) {print 'class="error"';} ?>>
        <textarea id="bio" name="bio"><?php print htmlspecialchars($values['bio']); ?></textarea>
      </div><br>
      
      <div <?php if ($errors['check']) {print 'class="error"';} ?>>
        <input id="check" name="check" type="checkbox" checked="checked">С контрактом ознакомлен(а)
      </div><br>

      <input id="submit" name="send" type="submit" value="Сохранить">
    </form>

  </body>
</html>

