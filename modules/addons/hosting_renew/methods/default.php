<?php
/**
 * Created by PhpStorm.
 * User: esh
 * Project name hosting_renew
 * 12.02.2024 23:27
 * Bünyamin AKÇAY <bunyamin@bunyam.in>
 */
use Illuminate\Database\Capsule\Manager as Capsule;

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


    $setting = Capsule::table('tblconfiguration')
                     ->where('setting', 'hosting_renew_hideexpireddomains')->first();

    $hdays = -60;
    if ($setting->value >1) {
        $hdays = $setting->value*-1;
    }


    $domains = Capsule::table('tbldomains as tbd')
                      ->where('userid', $userid)
                      ->whereIn('status', [
                          'Active',
                          'Expired'
                      ])
                      ->where('nextduedate', '>', date('Y-m-d', strtotime($hdays.' day')));
    if ($domainid > 0) {
        $domains->where('tbd.id', $domainid);
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


$apiresponse = [
    'type'     => 'html',
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
];

