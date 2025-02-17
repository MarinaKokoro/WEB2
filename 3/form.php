<form id="form" action="" method="POST">
  <input name="fio" type="text" placeholder="ФИО"><br>
  <input name="telephone" type="tel" placeholder="Номер телефона"><br>
  <input name="email" type="email" placeholder="Email@mail.mail"><br>

  Дата рождения:<br>
  <input name="dateOfBirth" type="date"><br>

  Пол:<br>
  <label><input class="radio" name="radio" checked="checked" type="radio" value="female">Женский</label>
  <label><input class="radio" name="radio" type="radio" value="male">Мужской</label><br>

  
  Любимый язык программирования:
  <br>
  <select id="abilities" name="abilities[]" multiple="multiple">
      <?php 
      foreach ($abilities as $key => $value) {
        printf('<option value="%s">%s</option>', $key, $value);
      } 
      ?>
  </select>
  <br>

  
  Биография:<br>
  <textarea id="bio" name="bio"></textarea>
  <br>

  <input id="check" name="check" type="checkbox" checked="checked">С контрактом ознакомлен(а)<br>

  <input id="submit" name="send" type="submit" value="Сохранить">
</form>
