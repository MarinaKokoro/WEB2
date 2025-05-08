<html>
  <body>

    <?php
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    if (!empty($messages)) {
      print('<div id="messages">');
      foreach ($messages as $message) {
        print($message);
      }
      print('</div>');
    }
    ?>

    <form id="form" action="" method="POST">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

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
              print_r($values);
              foreach ($abilities as $key => $value) {
                $selected = $values[$key];
                printf('<option value="%s"', $key);
                if(!empty($selected)){
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

