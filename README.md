Код состоит из нескольких файлов: <br>
В корневой директории odoo_server.php - тут прописываються параметры доступа к серверу
В директории ripcordr - служба которую надо поднять на сервере.
в директории jaunts - примеры вызова  для интеграции. 
Тут 2 пары файлов:
r1 - файл со всеми переменными для записи на групповую (регулярную,сборную) экскурсию.
Задача при интеграции - в переменные этого файла передавать параметры полученные от пользователя при записи
regular.php - непосредственно код, который эти параметры пересылаем в crm систему odoo
и
i1 - файл со всеми переменными для заказа индивидуальной экскурсии. 
Задача при интеграции - в переменные этого файла передавать параметры полученные от пользователя при записи
individual.php - непосредственно код, который эти параметры пересылаем в crm систему odoo
Важно понимать различие этих 2х типов событий:
Регулярная: "заказчиком" экскурсии выступает сама фирма организатор, а все клиенты записываються в "участники" экскурсии
Индивидуальная : "Заказчик" экскурсии - сам клиент, вкладка "участники экскурсии" отключены.



