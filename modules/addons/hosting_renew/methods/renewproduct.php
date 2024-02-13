<?php
/**
 * Created by PhpStorm.
 * User: esh
 * Project name hosting_renew
 * 12.02.2024 23:28
 * Bünyamin AKÇAY <bunyamin@bunyam.in>
 */

use Illuminate\Database\Capsule\Manager as Capsule;

$_addons = $result = [];

if ($_REQUEST['type'] == 'hosting') {
    $_product = Capsule::table('tblhosting')
                       ->where('id', $_REQUEST['hostingid'])
                       ->where('userid', $userid)
                       ->first();


    if (isset($_product->id)) {
        $_addons = Capsule::table('tblhostingaddons')
                          ->where('hostingid', $_product->id)
                          ->wherein('id', explode(',', $_REQUEST['addonsid']))
                          ->get();

        if (count($_addons) > 0) {
            $aids = array();
            foreach ($_addons as $kad => $vad) {
                $aids[] = $vad->id;
            }
            $_addons = Capsule::table('tblhostingaddons')
                              ->whereIn('id', $aids)
                              ->update([
                                  'nextduedate'     => $_product->nextduedate,
                                  'nextinvoicedate' => $_product->nextinvoicedate
                              ]);
        }
    }

    if (isset($_product->id)) {

        $apidata = array(
            'noemails'   => false,
            'clientid'   => $userid,
            'serviceids' => array($_product->id)
        );

        if (count($_addons) > 0) {
            foreach ($_addons as $kad => $vad) {
                $apidata['addonids'][] = $vad->id;
            }
        }

        $results = localAPI('geninvoices', $apidata, 'admin');
        if ($results['latestinvoiceid'] > 0) {
            $result['result']    = 'success';
            $result['invoiceid'] = $results['latestinvoiceid'];
        }
    }
}

if ($_REQUEST['type'] == 'domain') {

    $result=[
        'result'=>'redirect',
        'querystring'=>"renewalids[]=".$_REQUEST['domainid']."&renewalperiod[".$_REQUEST['domainid']."]=".$_REQUEST['years']
    ];


}


$result['type'] = 'json';
$apiresponse    = $result;

