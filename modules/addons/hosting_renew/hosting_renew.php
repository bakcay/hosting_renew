<?php
/**
 * Created by PhpStorm.
 * User: Bunyamin
 * Project name freelance
 * 10.02.2024 00:00
 * Bünyamin AKÇAY <bunyamin@bunyam.in>
 */

if (!defined("WHMCS"))
    die("You shall not pass!");

use WHMCS\Module\Addon\AddonModule\Admin\AdminDispatcher;
use WHMCS\Module\Addon\AddonModule\Client\ClientDispatcher;
use Illuminate\Database\Capsule\Manager as Capsule;

function hosting_renew_config() {
    $configarray = [
        "name"        => "Hosting renew modülü",
        "description" => "hosting renew modülü",
        "version"     => "1.02 Beta",
        "author"      => "Bünyamin AKÇAY",
        "language"    => "english",
        "fields"      => []
    ];
    return $configarray;
}

function hosting_renew_activate() {


    # Return Result
    return [
        'status'      => 'success',
        'description' => 'Aktif edildi'
    ];
    return [
        'status'      => 'error',
        'description' => 'Aktif edilemedi'
    ];
    return [
        'status'      => 'info',
        'description' => 'Aktif etme Başarılı.'
    ];

}

function hosting_renew_deactivate() {


    # Return Result
    return [
        'status'      => 'success',
        'description' => 'If successful, you can return a message to show the user here'
    ];
    return [
        'status'      => 'error',
        'description' => 'If an error occurs you can return an error message for display here'
    ];
    return [
        'status'      => 'info',
        'description' => 'If you want to give an info message to a user you can return it here'
    ];

}

function hosting_renew_upgrade($vars) {

}

function hosting_renew_output($vars) {


}


function hosting_renew_sidebar($vars) {

}

function hosting_renew_clientarea($vars) {



    $hostingid = $_GET['hostingid'];
    $domainid  = $_GET['domainid'];
    $userid    = $_SESSION['uid'];


    $method = strtolower($_REQUEST['method']);
    if(is_file(__DIR__.'/methods/'.$method.'.php')){

    }else{
        $method = 'default';
    }

    require_once __DIR__.'/methods/'.$method.'.php';

    if($apiresponse['type'] == 'json'){
        header('Content-Type: application/json');
        echo json_encode($apiresponse);
        exit;
    }else{
        return [
            'pagetitle'    => 'Hizmet Yenileme',
            'breadcrumb'   => [],
            'templatefile' => 'template/clientarea_hosting_renew_all',
            'vars'         => $apiresponse
        ];
    }



}

function hosting_renew_calcdayleft($tarih) {
    // Bugünün tarihini 'Y-m-d' formatında al
    $bugun = date('Y-m-d');

    // Hedef tarih ve bugünün Unix zaman damgasını al
    $hedefTarihZamanDamgasi = strtotime($tarih);
    $bugunZamanDamgasi      = strtotime($bugun);

    // İki tarih arasındaki farkı saniye cinsinden hesapla
    $farkSaniye = $hedefTarihZamanDamgasi - $bugunZamanDamgasi;

    // Farkı gün cinsinden hesapla
    $farkGun = $farkSaniye / (60 * 60 * 24);

    // Gün farkını tam sayı olarak döndür
    return round($farkGun);
}

function hosting_renew_startswith($haystack, $needle) {
    // Haystack string'inin başlangıcındaki karakterler needle string ile eşleşiyor mu kontrol et
    return substr($haystack, 0, strlen($needle)) === $needle;
}

function hosting_renew_endswith($haystack, $needle) {
    // Haystack string'inin sonundaki karakterler needle string ile eşleşiyor mu kontrol et
    $length = strlen($needle);
    if ($length == 0) {
        return true; // Boş string her zaman eşleşir
    }

    return substr($haystack, -$length) === $needle;
}


function hosting_renew_cycletoday($cycle){
        switch (strtolower(trim($cycle))){
            case 'monthly':
                $tday=30;
                break;
            case 'quarterly':
                $tday=90;
                break;
            case 'semiannually':
                $tday=180;
                break;
            case 'annually':
                $tday=365;
                break;
            case 'biennially':
                $tday=730;
                break;
            case 'triennially':
                $tday=1095;
                break;
        }
        return $tday;
    }
