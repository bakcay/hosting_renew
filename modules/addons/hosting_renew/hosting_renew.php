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

    /*

    if ($userid > 1 && $_GET['reactivate'] == 'yes') {

        $host = Capsule::table('tblhosting as tbh')
                       ->leftJoin('tblproducts as tbp', 'tbp.id', '=', 'tbh.packageid')
                       ->where('tbh.domainstatus', 'Suspended')
                       ->where('tbp.configoption9', 'on')
                       ->where('tbp.servertype', 'cdnpro')
                       ->where('tbh.id', $_GET['hostingid'])
                       ->select(['tbh.id'])
                       ->first();

        if (isset($host->id)) {

            localAPI('ModuleUnsuspend', ['accountid' => $host->id,], 'admin');

            header("Location: upgrade.php?type=package&id={$_GET['hostingid']}");
        }

    }

    */


    if ($userid >= 1) {




        $_ccinfo = Capsule::table('tblclients')
                          ->where('id', $userid)
                          ->first();



        $ccinfo = '';
        if (trim($_ccinfo->cardlastfour) != '') {
            $ccinfo = $_ccinfo->cardtype . '- ****' . $_ccinfo->cardlastfour;
        }


        /* DOMAIN PROCESS */

        if (!isset($_GET['hostingid'])) {
            $_domain_pricing = Capsule::table('tbldomainpricing as tbdp')
                                      ->leftJoin('tblpricing as tbp', 'tbdp.id', '=', 'tbp.relid')
                                      ->select([
                                          'tbdp.extension',
                                          'tbp.msetupfee as year1',
                                          'tbp.qsetupfee as year2',
                                          'tbp.ssetupfee as year3',
                                          'tbp.asetupfee as year4',
                                          'tbp.bsetupfee as year5',
                                          'tbp.monthly as year6',
                                          'tbp.quarterly as year7',
                                          'tbp.semiannually as year8',
                                          'tbp.annually as year9',
                                          'tbp.biennially as year10'
                                      ])
                                      ->where('tbp.type', 'domainrenew')
                                      ->where('tbp.currency', 1)
                                      ->get();
            $_domain_pricing = json_decode(json_encode($_domain_pricing), true);




            $domains = Capsule::table('tbldomains')
                              ->where('userid', $userid)
                              ->whereIn('status', [
                                  'Active',
                                  'Expired'
                              ]);
            if ($domainid > 0) {
                $domains->where('tbh.id', $domainid);
            }

            $domains = $domains->get();
            $domains = json_decode(json_encode($domains), true);

            foreach ($domains as $k => $v) {

                $domains[$k]['daysleft'] = hosting_renew_calcdayleft($v['nextduedate']);
                $domains[$k]['pricings'] = null;

                foreach ($_domain_pricing as $kp => $vp) {
                    if (hosting_renew_endswith($v['domain'], $vp['extension'])) {
                        foreach ($vp as $kdp => $vdp) {
                            if (hosting_renew_startswith($kdp, 'year')) {
                                if ($vdp > 0) {
                                    $domains[$k]['pricings'][str_replace('year', '', $kdp)] = $vdp;
                                }
                            }
                        }
                    }
                }

                $_invoice = Capsule::table('tblinvoiceitems as tii')
                                   ->leftJoin('tblinvoices as tbi', 'tbi.id', '=', 'tii.invoiceid')
                                   ->select([
                                       'tbi.id',
                                       'tbi.total',
                                       'tii.description'
                                   ])
                                   ->where('tii.relid', $v['id'])
                                   ->where('tii.type', 'LIKE', 'Domain%')
                                   ->where('tbi.status', 'Unpaid')
                                   ->where('tbi.userid', $userid)
                                   ->get();

                $domains[$k]['invoicecount'] = count($_invoice);
                $domains[$k]['invoicetotal'] = 0;

                if (count($_invoice) == 1) {
                    foreach ($_invoice as $ki => $vi) {
                        $domains[$k]['invoiceid']    = $vi->id;
                        $domains[$k]['invoicetotal'] = $vi->total;
                    }
                } else {
                    foreach ($_invoice as $ki => $vi) {
                        $domains[$k]['invoicetotal'] += $vi->total;
                    }
                }


            }

        }
        /*END DOMAIN PROCESS*/




        /* HOSTING PROCESS */
        if (!isset($_GET['domainid'])) {


            $hostings = Capsule::table('tblhosting as tbh')
                               ->leftJoin('tblproducts as tbp', 'tbp.id', '=', 'tbh.packageid')
                               ->leftJoin('tblcancelrequests as tbcn', 'tbcn.relid', '=', 'tbh.id')
                               ->where('tbh.userid', $userid)
                               ->whereIn('tbh.domainstatus', [
                                   'Active',
                                   'Suspended'
                               ])
                               ->select([
                                   'tbh.id',
                                   'tbp.name as product',
                                   'tbh.domain',
                                   'tbh.billingcycle',
                                   'tbh.nextduedate',
                                   'tbh.nextinvoicedate',
                                   'tbh.amount',
                                   'tbh.domainstatus as status',
                                   'tbcn.id as cancel',
                                   'tbh.paymentmethod',
                                   'tbh.billingcycle'
                               ]);

            if ($hostingid > 0) {
                $hostings->where('tbh.id', $hostingid);
            }

            $hostings = $hostings->get();

            $hostings = json_decode(json_encode($hostings), true);

            foreach ($hostings as $k => $v) {


                $hostings[$k]['totalamount'] = $v['amount'];

                $hostings[$k]['daysleft']    = hosting_renew_calcdayleft($v['nextduedate']);
                $hostings[$k]['dayswilladd'] = hosting_renew_cycletoday($v['billingcycle']);

                $_addon = Capsule::table('tblhostingaddons as tba')
                                 ->leftJoin('tbladdons as tbad', 'tbad.id', '=', 'tba.addonid')
                                 ->select([
                                     'tba.id',
                                     'tba.billingcycle',
                                     'tba.recurring as amount',
                                     'tba.nextduedate',
                                     'tba.nextinvoicedate',
                                     'tbad.name as addonname'
                                 ])
                                 ->where('hostingid', $v['id'])
                                 ->get();
                $_addon = json_decode(json_encode($_addon), true);


                $_invoice = Capsule::table('tblinvoiceitems as tii')
                                   ->leftJoin('tblinvoices as tbi', 'tbi.id', '=', 'tii.invoiceid')
                                   ->select([
                                       'tbi.id',
                                       'tbi.total',
                                       'tii.description'
                                   ])
                                   ->where('tii.relid', $v['id'])
                                   ->where('tii.type', 'Hosting')
                                   ->where('tbi.status', 'Unpaid')
                                   ->where('tbi.userid', $userid)
                                   ->get();

                $hostings[$k]['invoicecount'] = count($_invoice);
                $hostings[$k]['invoicetotal'] = 0;

                if (count($_invoice) == 1) {
                    foreach ($_invoice as $ki => $vi) {
                        $hostings[$k]['invoiceid']    = $vi->id;
                        $hostings[$k]['invoicetotal'] = $vi->total;
                    }
                } else {
                    foreach ($_invoice as $ki => $vi) {
                        $hostings[$k]['invoicetotal'] += $vi->total;
                    }
                }



                if (isset($_domain['id'])) {

                    $hostings[$k]['altdomain'] = [$_domain];
                }


                foreach ($_addon as $ka => $va) {

                    $hostings[$k]['totalamount'] += $va['amount'];

                    $hostings[$k]['addons'][] = $va;
                }
                $hostings[$k]['addonstotal'] = 0;
                $hostings[$k]['addonsid']    = [];
                foreach ($hostings[$k]['addons'] as $kap => $vap) {
                    $hostings[$k]['addonstotal'] += $vap['amount'];
                    $hostings[$k]['addonsid'][]  = $vap['id'];
                }
                $hostings[$k]['addonsid'] = implode(',', $hostings[$k]['addonsid']);

                $hostings[$k]['totalamount'] = $hostings[$k]['addonstotal'] + $hostings[$k]['amount'];

            }
        }

        /*END HOSTING PROCESS */


        return [
            'pagetitle'    => 'Hizmet Yenileme',
            'breadcrumb'   => [],
            'templatefile' => 'template/clientarea_hosting_renew_all',
            'vars'         => [
                'hostings' => $hostings,
                'ccinfo'   => $ccinfo,
                'domains'  => $domains,
                'cycles'   => [
                    'Monthly'      => 'Aylık',
                    'Quarterly'    => '3 Aylık',
                    'Semiannually' => '6 Aylık',
                    'Annually'     => 'Yıllık',
                    'Biennially'   => '2 Yıllık',
                    'Triennially'  => '3 Yıllık'
                ]
            ]
        ];

    } else {


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
