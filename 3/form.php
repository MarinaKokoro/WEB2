<form action="" method="POST">
  <input name="fio" type="text" placeholder="ФИО"><br>
  <input name="telephone" type="tel" placeholder="Номер телефона"><br>
  <input name="email" type="email" placeholder="Email@mail.mail"><br>

  Дата рождения:<br>
  <input name="dateOfBirth" type="date"><br>

  Пол:<br>
  <input name="radio-f" type="radio" value="female">Женский
  <input name="radio-m" type="radio" value="male">Мужской<br>

  
  Любимый язык программирования:
  <br>
  <select name="abilities[]" multiple="multiple">
      <option value="Pascal">Pascal</option>
      <option value="C">C</option>
      <option value="C++">C++</option>
      <option value="JavaScript">JavaScript</option>
      <option value="PHP">PHP</option>
      <option value="Python">Python</option>
      <option value="Java">Java</option>
      <option value="Haskel">Haskel</option>
      <option value="Clojure">Clojure</option>
      <option value="Prolog">Prolog</option>
      <option value="Scala">Scala</option>
      <option value="Go">Go</option>
  </select>
  <br>

  
  Биография:<br>
  <textarea></textarea>
  <br>

  <input name="check" type="checkbox" checked="checked">С контрактом ознакомлен(а)<br>

  <input name="send" type="submit" value="Отправить">
</form>
