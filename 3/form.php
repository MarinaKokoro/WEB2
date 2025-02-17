<form action="" method="POST">
  <input name="fio" type="text" placeholder="ФИО"><br>
  <input name="telephone" type="tel" placeholder="Номер телефона"><br>
  <input name="email" type="email" placeholder="Email@mail.mail"><br>

  Дата рождения:<br>
  <input name="dateOfBirth" type="date"><br>

  Пол:<br>
  <label><input name="radio" checked="checked" type="radio" value="female">Женский</label>
  <label><input name="radio" type="radio" value="male">Мужской</label><br>

  
  Любимый язык программирования:
  <br>
  <select name="abilities[]" multiple="multiple">
      <?php 
      foreach ($abilities as $key => $value) {
        printf('<option value="%s">%s</option>', $key, $value)
      } 
      ?>
  </select>
  <br>

  
  Биография:<br>
  <textarea name="bio"></textarea>
  <br>

  <input name="check" type="checkbox" checked="checked">С контрактом ознакомлен(а)<br>

  <input name="send" type="submit" value="Сохранить">
</form>
