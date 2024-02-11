<?php
/**
 * Created by PhpStorm.
 * User: Bunyamin
 * Project name freelance
 * 10.02.2024 00:00
 * Bünyamin AKÇAY <bunyamin@bunyam.in>
 */

use Illuminate\Database\Capsule\Manager as Capsule;

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
