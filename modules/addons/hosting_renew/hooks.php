<?php
/**
 * Created by PhpStorm.
 * User: Bunyamin
 * Project name freelance
 * 10.02.2024 00:00
 * Bünyamin AKÇAY <bunyamin@bunyam.in>
 */

use Illuminate\Database\Capsule\Manager as Capsule;

use WHMCS\View\Menu\Item as MenuItem;

add_hook('InvoiceCancelled', 1, function ($vars) {

    $invoiceid = $vars['invoiceid'];

    $lines = Capsule::table('tblinvoiceitems')
                    ->where('invoiceid', $invoiceid)
                    ->get();


    foreach ($lines as $k => $v) {

        if ($v->type == 'Hosting') {
            $hosting = Capsule::table('tblhosting')
                              ->where('id', $v->relid)
                              ->first();

            $nextdue = $hosting->nextduedate;
            if (strlen($nextdue) > 5) {
                Capsule::table('tblhosting')
                       ->where('id', $v->relid)
                       ->update(['nextinvoicedate' => $nextdue]);

                $nextdue = date('Y-m-d', strtotime('+1 day', $nextdue));
                Capsule::table('tblinvoiceitems')
                       ->where('id', $v->id)
                       ->update(['duedate' => $nextdue]);
            }
        }

        if ($v->type == 'Addon') {
            $hosting = Capsule::table('tblhostingaddons')
                              ->where('id', $v->relid)
                              ->first();
            $nextdue = $hosting->nextduedate;
            if (strlen($nextdue) > 5) {
                Capsule::table('tblhostingaddons')
                       ->where('id', $v->relid)
                       ->update(['nextinvoicedate' => $nextdue]);

                $nextdue = date('Y-m-d', strtotime('+1 day', $nextdue));
                Capsule::table('tblinvoiceitems')
                       ->where('id', $v->id)
                       ->update(['duedate' => $nextdue]);
            }
        }


    }
});


add_hook('ClientAreaNavbars', 1, function ($vars) {


    $addon_uri = explode(DIRECTORY_SEPARATOR, __FILE__);
    $addon_name = $addon_uri[count($addon_uri) - 2];

    $primaryNavbar   = Menu::primaryNavbar();
    // Kullanıcı giriş yapmışsa işlem yap
    if (!is_null($primaryNavbar->getChild('Services'))) {
        // 'Hizmetlerim' menüsünü bul
        $servicesMenu = $primaryNavbar->getChild('Services');

        // 'Hizmetlerim' menüsüne yeni bir link ekleyin
        if (!is_null($servicesMenu)) {
            $servicesMenu->addChild('Custom Module Divider', array(
                'label' => '', // Görünecek isim
                'uri' => '#', // Ayırıcı için yönlendirme adresi gerekmez
                'order' => 43, // Menüdeki sıralama konumu
                 'attributes' => array(
                    'class' => 'nav-divider', // Ayırıcı için CSS sınıfı
                ),
            ));
            $servicesMenu->addChild('Custom Module Link', array(
                'label' => 'Servis Yenile', // Görünecek isim
                'uri' => 'index.php?m='.$addon_name, // Linkin yönlendireceği adres
                'order' => 45, // Menüdeki sıralama konumu
            ));
        }
    }
});


add_hook('ClientAreaSecondarySidebar', 1, function  ($secondaryNavbar) {
    // Kullanıcı giriş yapmış ve bir hosting id'si varsa işlem yap
    if (!is_null($secondaryNavbar) && isset($_GET['id']) && in_array($_GET['action'],['productdetails','domaindetails']) && $_GET['id'] > 0){
        // Hosting id'sini al
        $relatedid = $_GET['id'];

        $type='hosting';
        if($_GET['action'] == 'domaindetails'){
            $type='domain';
        }

        // Eğer sekonder navbar'da 'Services' menüsü varsa, onu al
        if (!is_null($secondaryNavbar->getChild(($type=='hosting'?'Hosting Yenile':'Domain Yenile')))) {
            $servicesMenu = $secondaryNavbar->getChild(($type=='hosting'?'Hosting Yenile':'Domain Yenile'));
        } else {
            // Yoksa, 'Services' menüsünü oluştur
            $servicesMenu = $secondaryNavbar->addChild(($type=='hosting'?'Hosting Yenile':'Domain Yenile'));
        }



        // 'Services' menüsüne 'Renew Hosting' öğesini ekle
        $servicesMenu->addChild(($type=='hosting'?'Hosting Yenile':'Domain Yenile'), array(
            'label' => ' Yenile',
            'uri' => '/index.php?m=hosting_renew&'.($type=='hosting'?'hostingid':'domainid').'=' . $relatedid,
            'order' => 10, // Menüdeki sıralama konumu
        ));
    }
});



add_hook('ClientAreaHomepagePanels', 1, function($homePagePanels) {
    $newPanel = $homePagePanels->addChild(
        'unique-css-name',
        array(
            'name' => 'Friendly Name',
            'label' => 'Translated Language String',
            'icon' => 'fas fa-calendar-alt', //see http://fortawesome.github.io/Font-Awesome/icons/
            'order' => '1',
            'extras' => array(
                'color' => 'pomegranate', //see Panel Accents in template styles.css
                'btn-link' => 'https://www.whmcs.com',
                'btn-text' => Lang::trans('go'),
                'btn-icon' => 'fas fa-arrow-right',
            ),
        )
    );
// Repeat as needed to add enough children
    $newPanel->addChild(
        'unique-css-name-id1',
        array(
            'label' => 'Panel Row Text Goes Here',
            'uri' => 'index.php?m=yourmodule',
            'order' => 10,
        )
    );
});

