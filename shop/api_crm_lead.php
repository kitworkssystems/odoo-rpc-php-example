<?php
require_once('ripcordr/ripcord.php');
$url = 'http://192.168.1.79:8169/xmlrpc/2/';
$db = 'shop';
$username = 'admin';
$password = 'admin';

$opportunity = array(
    'id'=>'443322', // унікальний ID запису реєстрації
    'name'=>'Plan to buy 10 keyboards',
    'customer'=>array(
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
                    array('name', '=', $opportunity['customer']['id']))));
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
                        array('email', '=', $opportunity['customer']['email']),
                        array('phone', '=', $opportunity['customer']['phone']),)));
            if(count($partner)) {
                $partner_id = $partner[0];
            }
            else {
                $partner_id = $models->execute_kw($server['db'], $uid, $server['password'],
                    'res.partner', 'create', array(array(
                        'name'=>$opportunity['customer']['name'],
                        'email'=>$opportunity['customer']['email'],
                        'phone'=>$opportunity['customer']['phone']),
                    ));
                }
            // збережемо ID для наступних поколінь
            $models->execute_kw($server['db'], $uid, $server['password'],
                'ir.model.data', 'create', array(
                    array(
                        'module'=>"site_customer_code",
                        'model'=>"res.partner",
                        'name'=>$opportunity['customer']['id'],
                        'res_id'=>$partner_id
                    )));
        }
        echo("partner_id: \n");
        var_dump($partner_id);
        // отримання ID замовника -- завершення
    ),
    'email_from'=>'willmac@rediffmail.example.com',
    'phone'=>'1234567890',
    'user_id'=>array(
        // отримання ID продавця -- початок
        echo("user_id start \n");
        $models = ripcord::client($server['url'].'object');
        // пошук продавця за параметрами можливо id, name, email, phone тощо
        // $partner = $models->execute_kw($server['db'], $uid, $server['password'],
        //     'res.users', 'search', array(
        //         array(array('email', '=', $r['customer']['email']),)));
        // var_dump($partner);
        // але в прикладі спочатку спробуємо шукати по зовнішньому ID
        $external_ids = $models->execute_kw($server['db'], $uid, $server['password'],
            'ir.model.data', 'search', array(
                array(
                    array('module', '=', "site_user_code"),
                    array('model', '=', "res.users"),
                    array('name', '=', $opportunity['user']['id']))));
        var_dump($external_ids);
        if ($external_ids) {
            $external_id = $models->execute_kw($server['db'], $uid, $server['password'],
                'ir.model.data', 'read', array($external_ids));
            $user_id = $external_id[0]['res_id'];
        } else {
            // якщо з пошуком за ID нас спіткала невдачка
            // повертаємось до пошуку за телефоном та ємейлом
            $user = $models->execute_kw($server['db'], $uid, $server['password'],
                'res.users', 'search', array(
                    array(
                        array('email', '=', $opportunity['user']['email']),
                        array('phone', '=', $opportunity['user']['phone']),)));
            if(count($user)) {
                $user_id = $user[0];
            }
            else {
                $user_id = $models->execute_kw($server['db'], $uid, $server['password'],
                    'res.users', 'create', array(array(
                        'name'=>$opportunity['user']['name'],
                        'email'=>$opportunity['user']['email'],
                        'phone'=>$opportunity['user']['phone']),
                    ));
                }
            // збережемо ID для наступних поколінь
            $models->execute_kw($server['db'], $uid, $server['password'],
                'ir.model.data', 'create', array(
                    array(
                        'module'=>"site_user_code",
                        'model'=>"res.users",
                        'name'=>$r['user']['id'],
                        'res_id'=>$user_id
                    )));
        }
        echo("user_id: \n");
        var_dump($user_id);
        // отримання ID замовника -- завершення
    ),
    'team_id'=>array(

    ),
    'kws_etalonk_crm_needs_type_id'=>array(
      'id'=>'223344',
      'name'=>'Call',
    ),
    'kws_etalonk_crm_traffic_channel_id'=>array(
      'id'=>'1111',
      'name'=>'Some channel',
    ),
    'date_deadline'=>"2018-11-29 17:00:00",
    'utm_source'=>'news',
    'utm_medeium'=>'email',
    'utm_campaign'=>'sping',

);
insert_order($server, $opportunity);
