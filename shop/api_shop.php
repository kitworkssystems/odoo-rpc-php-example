<?php
require_once('ripcordr/ripcord.php');
$url = 'http://192.168.1.79:8169/xmlrpc/2/';
$db = 'shop';
$username = 'admin';
$password = 'admin';

/*
Розглядається приклад:
Замовник: Gabriela Bieda, gabriela.bieda1@gmail.com, 48790707130
Замовила:
1) 2 одиниці Car glass за ціною 700
2) 4 одиниці Car wheel за ціною 500
Валюта не передається, вважається що в магазині та odoo використовується 
однакова валюта (рекомендується використовувати гривні)
*/

$cart = array(
    'id'=>98765,
    'customer'=>array(
        'id'=>12345,
        'name'=>'Gabriela Bieda', 
        'email'=>'gabriela.bieda1@gmail.com',
        'phone'=>'48790707130'
    ),
    'items'=>array(
        0=>array(
            'id'=>223399,
            'name'=>'Car glass', 
            'barcode'=>'QAZXSW21',
            'price'=>700,
            'quantity'=>2
        ),
        1=>array(
            'id'=>115577,
            'name'=>'Car wheel', 
            'barcode'=>'EDCVFR65',
            'price'=>50,
            'quantity'=>4
        ),
    )
);

$info = ripcord::client($url)->start();

$common = ripcord::client($url.'common');
$uid = $common->authenticate($db, $username, $password, array());
echo('uid: ');
var_dump($uid);

// отримання ID замовника -- початок
$models = ripcord::client($url.'object');

// пошук замовника за зовнишнім id -  id замовника у базі магазину
// є кращою альтернативою до пошуку за іншими данними
$external_ids = $models->execute_kw($db, $uid, $password,
    'ir.model.data', 'search', array(
        array(array('model', '=', "res.partner"),
        array('name', '=', $cart['customer']['id']))));

if($external_ids) {
    $external_ids = $models->execute_kw($db, $uid, $password,
    'ir.model.data', 'read',
    array($external_ids[0]),
    array('fields'=>array('res_id', )));
    $partner_id = $external_ids[0]['res_id'];    
}
else {
    // пошук замовника за параметрами можливо id, name, email, phone тощо
    $partner = $models->execute_kw($db, $uid, $password,
        'res.partner', 'search', array(
            array(array('email', '=', $cart['customer']['email']),)));
    //var_dump($partner);

    if(count($partner)) {
        $partner_id = $partner[0];
    }
    else {
        $partner_id = $models->execute_kw($db, $uid, $password,
        'res.partner', 'create', array(array(
            'name'=>$cart['customer']['name'], 
            'email'=>$cart['customer']['email'],
            'phone'=>$cart['customer']['phone']),
        ));

        // для пошуку за зовнішнім id при наступному запиті, 
        // його треба зберегти в базі odoo
        $external_ids = $models->execute_kw($db, $uid, $password,
        'ir.model.data', 'create', array(array(
            'model'=>"res.partner", 
            'name'=>$cart['customer']['id'],
            'res_id'=>$partner_id),
        ));
    }
}
echo('partner_id: ');
var_dump($partner_id);
// отримання ID замовника -- завершення

// отримання ID кошику -- початок
$external_ids = $models->execute_kw($db, $uid, $password,
    'ir.model.data', 'search', array(
        array(array('model', '=', "sale.order"),
        array('name', '=', $cart['id']))));

if($external_ids) {
    $external_ids = $models->execute_kw($db, $uid, $password,
    'ir.model.data', 'read',
    array($external_ids[0]),
    array('fields'=>array('res_id', )));
    $cart_id = $external_ids[0]['res_id'];    
}
else {
    $cart_id = $models->execute_kw($db, $uid, $password,
    'sale.order', 'create', array(array(
        'partner_id'=>$partner_id, ),
    ));

    // для пошуку за зовнішнім id при наступному запиті, 
    // його треба зберегти в базі odoo
    $external_ids = $models->execute_kw($db, $uid, $password,
    'ir.model.data', 'create', array(array(
        'model'=>"sale.order", 
        'name'=>$cart['id'],
        'res_id'=>$cart_id),
    ));

    // отримання ID продукту -- початок
    foreach($cart['items'] as $k => $item){
        $models = ripcord::client($url.'object');

        $external_ids = $models->execute_kw($db, $uid, $password,
        'ir.model.data', 'search', array(
            array(array('model', '=', "product.product"),
            array('name', '=', $item['id']))));

        if($external_ids) {
            $external_ids = $models->execute_kw($db, $uid, $password,
            'ir.model.data', 'read',
            array($external_ids[0]),
            array('fields'=>array('res_id', )));
            $product_id = $external_ids[0]['res_id'];    
        }
        else {
            // пошук продукту за параметрами можливо id, name, code тощо
            $product = $models->execute_kw($db, $uid, $password,
                'product.product', 'search', array(
                    array(array('barcode', '=', $item['barcode']),)));
            
            if(count($product)) {
                $product_id = $product[0];
            }
            else {
                $product_id = $models->execute_kw($db, $uid, $password,
                'product.product', 'create', array(array(
                    'name'=>$item['name'], 
                    'barcode'=>$item['barcode'],
                    'price'=>$item['price']),
                ));
            
                // для пошуку за зовнішнім id при наступному запиті, 
                // його треба зберегти в базі odoo
                $external_ids = $models->execute_kw($db, $uid, $password,
                'ir.model.data', 'create', array(array(
                    'model'=>"product.product", 
                    'name'=>$item['id'],
                    'res_id'=>$product_id),
                ));
            }
            // отримання ID продукту -- завершення            
        }
        echo('product_id: ');
        var_dump($product_id);

        $item_id = $models->execute_kw($db, $uid, $password,
        'sale.order.line', 'create', array(array(
            'order_id'=>$cart_id, 
            'product_id'=>$product_id,
            'product_uom_qty'=>$item['quantity']),
        ));
        echo('item_id: ');
        var_dump($item_id);

    }
}
echo('cart: ');
var_dump($cart_id);
// отримання ID кошику -- завершення
