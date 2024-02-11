<div class="jumbotron jumbotron-fluid">
    <div class="container">
        <h1 class="display-3 text-center">

            {if $latestinvoiceid gt 0}
                <i class="fa fa-thumbs-o-up" aria-hidden="true"></i>
                Siparişiniz İşleme Konuldu. Ödeme Bekliyor.
            {else}
                <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                Siparişiniz Malesef Oluşturulamadı...
            {/if}

        </h1>

        {if $latestinvoiceid gt 0}
            <p class="lead">Siparişiniz işleme konuldu ve başarılı bir şekilde faturanız oluşturuldu. Şimdi kredi kartınızla ödeme yapabilir, yada banka havalesi ile ödemenizi gönderebilirsiniz.</p>
            {*

                        <div class="col-md-4 text-center">
                            <form method="post" action="/creditcard.php" name="paymentfrm" style="display:none;">
                                <input type="hidden" name="invoiceid" value="{$latestinvoiceid}">
                                <input type="submit" class="btn btn-success" value="Kredi Kartıyla Ödeme Yapın">
                             </form>
                            <a href="javascript:void(0)" class="btn btn-success cc-payment-on-complete" data-id="{$invoiceid}"><i class="fa fa-credit-card" aria-hidden="true"></i> Kredi Kartıyla Ödeme Yapın</a>
                        </div>
            *}
            <div class="col-md-4 text-center">
                <a href="clientarea.php" class="btn btn-warning ">
                    <i class="fa fa-users" aria-hidden="true"></i> {$LANG.ordergotoclientarea}</a>
            </div>
            <div class="col-md-4 text-center">
                <a href="viewinvoice.php?id={$latestinvoiceid}" class="btn btn-info ">
                    <i class="fa fa-credit-card" aria-hidden="true"></i>
                    Faturanızı görüntüleyin Ve ödeme yapınız</a>
            </div>
        {else}
            <p class="lead">Değerli Müşterimiz, Faturalanma aşamasında sistem taraflı bir hata oluştu... </p>
            <p class="lead">Endişe etmeyin. Destek birimimiz bu konu hakkında şuan bilgilendirildi.</p>
            <p class="lead">En kısa sürede sorun giderilerek sizinle irtibata geçilecek, sorun çözüme kavuşturulacaktır.</p>
            <p class="lead">İsterseniz bir destek bildirimi ile konu akıbetini yazılı olarak da bildirebilirsiniz.</p>
            <div class="col-md-6 text-center">
                <a href="clientarea.php" class="btn btn-warning ">
                    <i class="fa fa-users" aria-hidden="true"></i> {$LANG.ordergotoclientarea}</a>
            </div>
            <div class="col-md-6 text-center">
                <a href="submitticket.php" class="btn btn-warning ">
                    <i class="fa fa-users" aria-hidden="true"></i>
                    Destek Talebi Açın</a>
            </div>
        {/if}


    </div>
</div>
