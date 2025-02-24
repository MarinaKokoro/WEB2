<html>
  <head>
    <style>
      .error {
      border: 2px solid red;
      }
    </style>
  </head>
  <body>

    <?php
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
      <select id="abilities" name="abilities[]" multiple="multiple"
            <?php if ($errors['abilities']) {print 'class="error"';} ?>>
          <?php 
            foreach ($abilities as $key => $value) {
              printf('<option value="%s">%s</option>', $key, $value);
            } 
          ?>
      </select>
      <br>

      
      Биография:<br>
      <textarea id="bio" name="bio"
          <?php if ($errors['bio']) {print 'class="error"';} ?> value="<?php print $values['bio']; ?>"></textarea>
      <br>

      <input id="check" name="check" type="checkbox" checked="checked"
          <?php if ($errors['check']) {print 'class="error"';} ?>>С контрактом ознакомлен(а)<br>

      <input id="submit" name="send" type="submit" value="Сохранить">
    </form>

  </body>
</html>

