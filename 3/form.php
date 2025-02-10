<form action="" method="POST">
  
    <input type="text" placeholder="Введите ФИО">
  <br>

  
  <input type="tel" placeholder="Введите ваш номер телефона">
  <br>

  
  <input type="email" placeholder="Введите ваш email">
  <br>

  
  Дата рождения:<br>
  <input type="date">
  <br>

  Пол:<br>
  <<input type="radio" checked="checked" name="radio-group-1" value="Значение1">Женский
  <input type="radio" name="radio-group-1" value="Значение2">Мужской<br>

  
  Любимый язык программирования:
  <br>
  <select name="field-name-4[]"
      multiple="multiple">
      <option value="Значение1">Pascal</option>
      <option value="Значение2">C</option>
      <option value="Значение3">JavaScript</option>
      <option value="Значение4">C++</option>
      <option value="Значение5">PHP</option>
      <option value="Значение6">Python</option>
      <option value="Значение7">Java</option>
      <option value="Значение8">Haskel</option>
      <option value="Значение9">Clojure</option>
      <option value="Значение10">Prolog</option>
      <option value="Значение10">Scala</option>
  </select>
  <br>

  
  Биография:<br>
  <textarea></textarea>
  <br>

  <input type="checkbox" checked="checked">С контрактом ознакомлен(а)<br>

  <input type="submit" value="Отправить">
  <input name="fio" />
  <select name="year">
    <?php 
    for ($i = 1922; $i <= 2022; $i++) {
      printf('<option value="%d">%d год</option>', $i, $i);
    }
    ?>
  </select>
  
  <input type="submit" value="ok" />
</form>
