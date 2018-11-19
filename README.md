Код состоит из нескольких файлов: <br>
В корневой директории odoo_server.php - тут прописываються параметры доступа к серверу<br>
В директории ripcordr - служба которую надо поднять на сервере.<br>
в директории jaunts - примеры вызова  для интеграции. <br>
Тут 2 пары файлов:<br>
r1,r2 - Примеры файла со всеми переменными для вызова  записи на групповую (регулярную,сборную) экскурсию.<br>
Задача при интеграции - в переменные этого файла передавать параметры полученные от пользователя при записи<br>
regular.php - непосредственно код, который эти параметры пересылаем в crm систему odoo<br>
и<br>
i1 - файл со всеми переменными для заказа индивидуальной экскурсии. <br>
Задача при интеграции - в переменные этого файла передавать параметры полученные от пользователя при записи<br>
individual.php - непосредственно код, который эти параметры пересылаем в crm систему odoo<br>
Важно понимать различие этих 2х типов событий:<br>
Регулярная: "заказчиком" экскурсии выступает сама фирма организатор, а все клиенты записываються в "участники" экскурсии<br>
Индивидуальная : "Заказчик" экскурсии - сам клиент, вкладка "участники экскурсии" отключены.<br>



