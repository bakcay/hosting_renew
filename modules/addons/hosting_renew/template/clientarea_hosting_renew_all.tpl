<!-- BEGIN PORTLET-->
<div class="portlet light bordered">
    <div class="portlet-title">
        <div class="caption">
            <i class="font-blue fa fa-recycle "></i>
            <span class="caption-subject   font-blue">Hizmet Yenile</span>
        </div>
        <div class="actions"></div>

    </div>
    <div class="portlet-body">


        <table width="100%" class="datatable table table-hover table-bordered tc-table ">
            <thead>
            <tr>
                <th>Hizmet</th>
                <th>Durum</th>
                <th>Kalan Gün</th>
                <th>Tutar</th>

                <th colspan="2">Yenile</th>
            </tr>
            </thead>
            <tbody>

            {foreach from=$hostings key=k item=v name=ind}

                {assign var="cycle" value=$v.billingcycle}
                <tr>
                    <td>
                        {$v.product} - {$v.domain}
                        {if $v.addons|count gt 0}
                            <small data-toggle="collapse" data-target="#attrs{$v.id}" style="display: list-item">
                                <i class="fa fa-plus-square-o" aria-hidden="true"></i> {$v.addons|count} bağlı eklenti (Toplam {$v.addonstotal}$)
                            </small>
                            <div id="attrs{$v.id}" class="collapse">
                                <ul>
                                    {foreach from=$v.addons key=ka item=va name=inda}
                                        <li>&raquo; {$va.addonname} <span style="float:right;">({$va.amount}$)</span>
                                        </li>
                                    {/foreach}
                                </ul>

                            </div>
                        {/if}

                    </td>

                    <td>
                        <label class="label label-{if $v.status|strtolower eq 'active'}success{else}danger{/if}">
                            {if $v.status eq 'Active'}
                                Aktif
                            {elseif $v.status eq 'Suspended'}
                                Askıya Alınmış
                            {/if}
                        </label>
                    </td>
                    {if $v.billingcycle eq 'Free Account'}
                        <td colspan="4" style="text-align: center">Bu ürün yenilenemez</td>
                    {else}
                        <td>{$v.daysleft}</td>
                        {if $v.cancel gt 0}
                            <td colspan="3" style="text-align: center">İptal Talebi inceleniyor</td>
                        {else}
                            <td>{$v.amount}$</td>
                            <td style="text-align: center;">
                                {if $v.invoicecount gt 0}
                                    <p><b>{$v.invoicetotal}$</b> beklenen ödeme bulunmakta.</p>
                                    <p></p>
                                {else}
                                {/if}
                            </td>
                            <td>
                                {if $v.invoicecount gt 0}
                                    <a class="btn blue " href="{if $v.invoicecount eq 1}creditcard.php?invoiceid={$v.invoiceid}{else}clientarea.php?action=invoices{/if}">
                                        <i class="fa fa-credit-card" aria-hidden="true"></i>
                                        Hemen Öde</a>
                                {else}
                                    <a href="javascript:void(0)" class="btn blue  renewthisproduct" data-producttype="hosting" data-productname="{$v.product}" data-hostingid="{$v.id}" data-addonsid="{$v.addonsid}" data-daysmore="{$v.dayswilladd}" data-totalamount="{$v.totalamount}" data-domain="{$v.domain}" data-addonsprice="{$v.addonstotal}" data-productprice="{$v.amount}">
                                        <i class="fa fa-repeat" aria-hidden="true"></i> {$cycles.$cycle} Yenile</a>
                                {/if}
                            </td>
                        {/if}
                    {/if}
                </tr>
            {/foreach}

            {foreach from=$domains key=k item=v name=ind}
                <tr>
                    <td>Alan adı: {$v.domain}</td>
                    <td>
                        <label class="label label-{$v.status|strtolower}">  {if $v.status eq 'Active'} Aktif {elseif $v.status eq 'Expired'} Zamanı Geçmiş {/if} </label>
                    </td>
                    <td>{$v.daysleft}</td>
                    <td>{$v.recurringamount}$</td>

                    <td style="text-align: center;">
                        {if $v.invoicecount gt 0}
                            <p><b>{$v.invoicetotal}$</b> beklenen ödeme bulunmakta.</p>
                            <p></p>
                        {else}

                        {/if}


                    </td>
                    <td>


                        {if $v.invoicecount gt 0}
                            <a class="btn blue " href="{if $v.invoicecount eq 1}creditcard.php?invoiceid={$v.invoiceid}{else}clientarea.php?action=invoices{/if}">
                                <i class="fa fa-credit-card" aria-hidden="true"></i>
                                Hemen Öde</a>
                        {else}
                            <div class="btn-group">
                                <a href="javascript:void(0)" class="btn blue  dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-repeat" aria-hidden="true"></i>
                                    Yenile </a>
                                {if $v.pricings gt 1}
                                    <ul class="dropdown-menu" role="menu">
                                        {foreach from=$v.pricings key=kpr item=vpr name=indpr}
                                            <li>
                                                <a href="javascript:void(0)" class="renewthisdomain" data-producttype="domain" data-domain="{$v.domain}" data-domainid="{$v.id}" data-year="{$kpr}" data-productprice=" {$vpr} " domain-redemption="{if $v.daysleft gt -30}0{else}1{/if}" data-addonsid="0">{$kpr} Yıl Yenile ({$vpr}$)</a>
                                            </li>
                                        {/foreach}
                                    </ul>
                                {/if}
                            </div>
                        {/if}
                    </td>
                </tr>
            {/foreach}

            </tbody>

        </table>


        <style>
            .page-header {
                display: none;
            }

            .tooltip-inner {
                white-space: pre-wrap;
            }
        </style>


    </div>
</div>

{include file="./assets.tpl"}
