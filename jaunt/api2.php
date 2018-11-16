<?php
require_once('ripcordr/ripcord.php');

$url = 'http://192.168.1.79:8169/xmlrpc/2/';
$db = 'jaunt';
$username = 'admin';
$password = 'admin';


$info = ripcord::client($url)->start();

$common = ripcord::client($url.'common');
$uid = $common->authenticate($db, $username, $password, array());
var_dump($uid);

// отримання ID замовника -- початок
$models = ripcord::client($url.'object');
// пошук замовника за параметрами можливо id, name, email, phone тощо
$partner = $models->execute_kw($db, $uid, $password,
    'res.partner', 'search', array(
        array(array('email', '=', "gabriela.bieda@gmail.com"),)));
//var_dump($partner);
if(count($partner)) {
    $partner_id = $partner[0];
}
else {
    $partner_id = $models->execute_kw($db, $uid, $password,
    'res.partner', 'create', array(array(
        'name'=>"Gabriela Bieda", 
        'email'=>"gabriela.bieda@gmail.com",
        'phone'=>"48790707130"),
    ));
}
var_dump($partner_id);
// отримання ID замовника -- завершення

// отримання ID мови туру -- початок
$models = ripcord::client($url.'object');
$lang = $models->execute_kw($db, $uid, $password,
    'jaunt.language', 'search', array(
        array(array('name', '=', 'Українська'),)));;
//var_dump($lang);
if(count($lang)) {
    $lang_id = $lang[0];
}
else {
    $lang_id = $models->execute_kw($db, $uid, $password,
    'jaunt.language', 'create', array(
        array('name'=>"Українська"),
    ));
}

var_dump($lang_id);
// отримання ID мови туру  -- завершення

// отримання ID туру у каталозі -- початок
$models = ripcord::client($url.'object');
$catalog = $models->execute_kw($db, $uid, $password,
    'jaunt.catalog', 'search', array(
        array(array('name', '=', 'НЕкласичний Львів'),)));;
//var_dump($catalog);
if(count($catalog)) {
    $catalog_id = $catalog[0];
}
else {
    $catalog_id = $models->execute_kw($db, $uid, $password,
    'jaunt.catalog', 'create', array(
        array('name'=>"НЕкласичний Львів"),
    ));
}

var_dump($catalog_id);
// отримання ID туру у каталозі  -- завершення


# отримання ID туру у розкладі -- початок
$models = ripcord::client($url.'object');
# дата надається у вігляді рядка у форматі '%Y-%m-%d %H:%M:%S'
$jaunt = $models->execute_kw($db, $uid, $password,
    'ir.model.data', 'search', array(
        array(array('model', '=', "jaunt.jaunt"),array('name', '=', "333"))));
//var_dump($partner);
if(count($jaunt)) {
    $jaunt_id = $jaunt[0];
}
else {
    $jaunt_id = $models->execute_kw($db, $uid, $password,
    'jaunt.jaunt', 'create', array(array(
        'catalog_id'=>$catalog_id, 
        'date_begin'=>"2018-09-30 10:00:00",
        'is_individual'=>true,
        'organizer_id'=>$partner_id,
        'language_id'=>$lang_id),
    ));
    $external_ids = $models->execute_kw($db, $uid, $password,
    'ir.model.data', 'create', array(array(
        'model'=>"jaunt.jaunt", 
        'name'=>"333",
        'res_id'=>$jaunt_id),
    ));
}
var_dump($jaunt_id);
// отримання ID туру у розкладі  -- завершення

