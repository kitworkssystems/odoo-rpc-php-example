<?php
require_once('../ripcordr/ripcord.php');

function insert_order($server, $r) { 
    $info = ripcord::client($server['url'])->start();
    $common = ripcord::client($server['url'].'common');
    $uid = $common->authenticate($server['db'], $server['username'], $server['password'], array());
    echo("uid: \n");
    var_dump($uid);

    // отримання ID замовника -- початок
    echo("partner_id start \n");
    $models = ripcord::client($server['url'].'object');
    // пошук замовника за параметрами можливо id, name, email, phone тощо
    // $partner = $models->execute_kw($server['db'], $uid, $server['password'],
    //     'res.partner', 'search', array(
    //         array(array('email', '=', $r['customer']['email']),)));
    // var_dump($partner);

    // але в прикладі спочатку спробуємо шукати по зовнішньому ID
    $external_ids = $models->execute_kw($server['db'], $uid, $server['password'],
        'ir.model.data', 'search', array(
            array(
                array('module', '=', "site_customer_code"),
                array('model', '=', "res.partner"),
                array('name', '=', $r['customer']['id']))));
    var_dump($external_ids);
    if ($external_ids) {
        $external_id = $models->execute_kw($server['db'], $uid, $server['password'],
            'ir.model.data', 'read', array($external_ids));
        $partner_id = $external_id[0]['res_id'];                        
    } else {
        // якщо з пошуком за ID нас спіткала невдачка
        // повертаємось до пошуку за телефоном та ємейлом
        $partner = $models->execute_kw($server['db'], $uid, $server['password'],
            'res.partner', 'search', array(
                array(
                    array('email', '=', $r['customer']['email']),
                    array('phone', '=', $r['customer']['phone']),)));

        if(count($partner)) {
            $partner_id = $partner[0];
        }
        else {
            $partner_id = $models->execute_kw($server['db'], $uid, $server['password'],
                'res.partner', 'create', array(array(
                    'name'=>$r['customer']['name'], 
                    'email'=>$r['customer']['email'],
                    'phone'=>$r['customer']['phone']),
                ));
            }
        // збережемо ID для наступних поколінь
        $models->execute_kw($server['db'], $uid, $server['password'],
            'ir.model.data', 'create', array(
                array(
                    'module'=>"site_customer_code", 
                    'model'=>"res.partner", 
                    'name'=>$r['customer']['id'],
                    'res_id'=>$partner_id
                )));   
    }
    echo("partner_id: \n");
    var_dump($partner_id);
    // отримання ID замовника -- завершення

    // отримання ID мови туру -- початок
    if ($r['jaunt']['language'] and is_array($r['jaunt']['language'])){

        $models = ripcord::client($server['url'].'object');

        // повторюємо фокус пошуку по ID
        $external_ids = $models->execute_kw($server['db'], $uid, $server['password'],
                'ir.model.data', 'search', array(
                    array(
                        array('module', '=', "site_language_code"),
                        array('model', '=', "jaunt.language"),
                        array('name', '=', $r['jaunt']['language']['id']))));

        if ($external_ids) {
            $external_id = $models->execute_kw($server['db'], $uid, $server['password'],
                'ir.model.data', 'read', array($external_ids));
            $lang_id = $external_id[0]['res_id'];                        
        } else {
            // ну ми намагалися, спробуємо за назвою
            $lang = $models->execute_kw($server['db'], $uid, $server['password'],
                'jaunt.language', 'search', array(
                    array(array('name', '=', $r['jaunt']['language']['name']))));        
            if(count($lang)) {
                $lang_id = $lang[0];
            } 
            else {
                $lang_id = $models->execute_kw($server['db'], $uid, $server['password'],
                    'jaunt.language', 'create', array(
                        array('name'=>$r['jaunt']['language']['name'])));
            }
            $models->execute_kw($server['db'], $uid, $server['password'],
                'ir.model.data', 'create', array(
                    array(
                        'module'=>"site_language_code", 
                        'model'=>"jaunt.language",
                        'name'=>$r['jaunt']['language']['id'],
                        'res_id'=>$lang_id)));  
        }    
    } 
    else {
        // якщо мову не задано, но ID не передаємо
        $lang_id = false;
    }

    echo("lang_id: \n");
    var_dump($lang_id);
    // отримання ID мови туру  -- завершення


    // отримання ID туру у каталозі -- початок
    $models = ripcord::client($server['url'].'object');

    $external_ids = $models->execute_kw($server['db'], $uid, $server['password'],
        'ir.model.data', 'search', array(
            array(
                array('module', '=', "site_catalog_code"),
                array('model', '=', "jaunt.catalog"),
                array('name', '=', $r['jaunt']['catalog']['id']))));
    if ($external_ids) {
        $external_id = $models->execute_kw($server['db'], $uid, $server['password'],
            'ir.model.data', 'read', array($external_ids));
        $catalog_id = $external_id[0]['res_id'];                        
    } else {
        $catalog = $models->execute_kw($server['db'], $uid, $server['password'],
            'jaunt.catalog', 'search', array(
                array(array('name', '=', $r['jaunt']['catalog']['name']))));;
        //var_dump($catalog);
        if(count($catalog)) {
            $catalog_id = $catalog[0];
        }
        else {
            $catalog_id = $models->execute_kw($server['db'], $uid, $server['password'],
            'jaunt.catalog', 'create', array(
                array('name'=>$r['jaunt']['catalog']['name'],
                    'language_ids'=>array(4,$lang_id), ),
            ));
        }
        $models->execute_kw($server['db'], $uid, $server['password'],
            'ir.model.data', 'create', array(
                array(
                    'module'=>"site_catalog_code", 
                    'model'=>"jaunt.catalog",
                    'name'=>$r['jaunt']['catalog']['id'],
                    'res_id'=>$catalog_id)));  
    }
    echo("catalog_id: \n");
    var_dump($catalog_id);
    // отримання ID туру у каталозі  -- завершення



    // отримання ID туру у розкладі -- початок
    $models = ripcord::client($server['url'].'object');

    $external_ids = $models->execute_kw($server['db'], $uid, $server['password'],
        'ir.model.data', 'search', array(
            array(
                array('module', '=', "site_jaunt_code"),
                array('model', '=', "jaunt.jaunt"),
                array('name', '=', $r['jaunt']['id']))));
    if ($external_ids) {
        $external_id = $models->execute_kw($server['db'], $uid, $server['password'],
            'ir.model.data', 'read', array($external_ids));
        $jaunt_id = $external_id[0]['res_id'];                        
    } else {

        $jaunt = $models->execute_kw($server['db'], $uid, $server['password'],
            'jaunt.jaunt', 'search', array(
                array(
                    array('catalog_id', '=', $catalog_id),
                    array('date_begin', '=', $r['jaunt']['date_begin']))));
        //var_dump($partner);
        if(count($jaunt)) {
            $jaunt_id = $jaunt[0];
        }
        else {
            $comment = $r['comment'];
            if($r['is_paid']){
                $comment = "Заказ оплачен \n". $comment;
            }
            $jaunt_id = $models->execute_kw($server['db'], $uid, $server['password'],
            'jaunt.jaunt', 'create', array(array(
                'catalog_id'=>$catalog_id, 
                'date_begin'=>$r['jaunt']['date_begin'],
                'is_individual'=>true,
                'organizer_id'=>$partner_id,
                'price'=>$r['price'],
                'language_id'=>$lang_id,
                'comment'=>$comment,
                'number_of_participants'=>$r['jaunt']['number_of_participants']
            ),
            ));
        } 

        $models->execute_kw($server['db'], $uid, $server['password'],
            'ir.model.data', 'create', array(
                array(
                    'module'=>"site_catalog_code", 
                    'model'=>"jaunt.catalog",
                    'name'=>$r['jaunt']['id'],
                    'res_id'=>$jaunt_id)));  
    }
    echo("jaunt_id: \n");
    var_dump($jaunt_id);
    // отримання ID туру у розкладі  -- завершення

}
