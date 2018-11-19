<?php
require_once('../odoo_server.php');
require_once('./regular.php');

// Реєстрація на подію, також можливий запис з купивлею білету на сайті
$r = array(
    'id'=>'2344r23s4', // унікальний ID запису реєстрації 
    'customer'=>array(
        'id'=>12345, // унікальний ID користувача, що записуюється на подію
        'name'=>'Gabriela Bieda', // Ім'я та призвище користувача
        'email'=>'gabriela.bieda1@gmail.com', // емейл користвача
        'phone'=>'48790707130' // телефон користувача (базажно задавати телефон лише цифрами без рисочок, пропусків, дужок тощо)
    ),
    'jaunt'=> array(
        'id'=>12345,
        # дата надається у вігляді рядка у форматі '%Y-%m-%d %H:%M:%S'
        'date_begin'=>"2018-11-30 10:00:00",
        'is_individual'=>false,
        'language'=>array(
            'id'=>1, // унікальний ID мови проведення події
            'name'=>'Українська', // Текстова назва мови
        ),
        'catalog'=>array(
            'id'=>1, // унікальний ID продукту в каталозі
            'name'=>'НЕкласичний Львів', // Назва продукту в каталозі
        ), 
        'guide'=>array(
            'id'=>1, // унікальний ID контакту гіда
            'name'=>'Максим Переийкопито', // Ім'я та призвище гіда
        ), 
    ),
    'is_paid'=> false, // true, якщо білент був оплачений на сайті, false, якщо ні
    'catalog_ticket'=> array(
        'coupon_type'=> false, // просто назва купона, або акції, яка впилває на ціну
        'ticket_type'=> 'standart', // тип квітка, наприклад: дорослий, дитячий тощо
        'partner_company'=> false
    ),
    'ticket_price'=> 100,
    'coupon_number'=> 'TGDEKMF'
);

insert_registration($server, $r);