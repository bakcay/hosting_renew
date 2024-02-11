<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{literal}
    <script type="text/javascript">

      $(document).ready(function() {

        $(document).on('click', '.renewthisproduct , .renewthisdomain', function() {
          const {
                  domain,
                  hostingid,
                  domainid,
                  addonsid,
                  daysmore,
                  totalamount,
                  productprice,
                  addonsprice,
                  productname,
                  producttype,
                  year: domainyear,
                } = $(this).data(); // jQuery .data() ile destructuring kullanımı

          const addonscount = addonsid.split(',').length;

          let warningtext = '';
          if (producttype === 'hosting') {
               warningtext = `${domain} hizmetine ilişkin ücret özeti aşağıdaki gibidir.<br>
                <table width="100%" class="datatable table">
                  <tbody>
                    <tr> <td>Ürün</td> <td>${productname}</td> </tr>
                    <tr> <td>Ürün Fiyatı</td> <td>${productprice}$</td> </tr>
                    ${addonscount > 1 ? `<tr> <td>Eklenti Adeti</td> <td>${addonscount}</td> </tr>
                      <tr> <td>Eklenti Fiyatı</td> <td>${addonsprice}$</td> </tr>` : ''}
                    <tr> <td>Uzatılacak Gün</td> <td>${daysmore}</td> </tr>
                    <tr> <td>Toplam Ücret</td> <td>${totalamount}$</td> </tr>
                  </tbody>
                </table>
                <br><i>Hizmetinizin devam etmesi için Yenile butonuna tıklayınız. Butona tıkladığınız da ödeme ekranına yönlendirileceksiniz.
                <br><small>** Kullanmak istemediğiniz hizmetler için 'Hizmet iptali' butonuna tıklayınız.</small>`;
                      }
              else if (producttype === 'domain') {
                warningtext = `${domain} alan adına ilişkin ücret özeti aşağıdaki gibidir.<br>
                <table width="100%" class="datatable table">
                  <tbody>
                    <tr> <td>Alan Adı</td> <td>${domain}</td> </tr>
                    <tr> <td>Alan adı Fiyatı</td> <td>${productprice}$</td> </tr>
                    <tr> <td>Uzayacağı yıl</td> <td>${domainyear}</td> </tr>
                  </tbody>
                </table>
                <br><i>Alan adınızın uzaması için Yenile butonuna tıklayınız. Butona tıkladığınız da ödeme ekranına yönlendirileceksiniz.
                <br><small>** Kullanmak istemediğiniz hizmetler için 'Alan Adı iptali' butonuna tıklayınız.</small>`;
          }

          const postparams = producttype === 'hosting'
              ? {type: 'hosting', hostingid, addonsid}
              : {type: 'domain', domainid, years: domainyear};

          var gotourl = '';
          Swal.fire({
            title              : 'Bilgi!',
            html               : warningtext,
            icon               : 'info', // 'type' yerine 'icon' kullanılıyor
            showCancelButton   : true,
            confirmButtonColor : '#DD6B55',
            confirmButtonText  : 'Yenile',
            cancelButtonText   : 'İptal',
            showLoaderOnConfirm: true,
            allowOutsideClick  : false,
            preConfirm         : () => {
              return new Promise((resolve, reject) => {
                $.post('/portal/ajax.php?method=renewproduct', postparams).done(data => {
                  if (data.result === 'success') { // '=' yerine '===' kullanılmalı
                    gotourl = 'creditcard.php?invoiceid=' + data.invoiceid;
                    resolve();
                  }
                  else {
                    Swal.showValidationMessage( // 'reject' yerine 'Swal.showValidationMessage' kullanılıyor
                        'Bir hata meydana geldi. Lütfen destek bildirimi açınız',
                    );
                  }
                }).fail(() => {
                  Swal.showValidationMessage(
                      'İstek işlenirken bir hata meydana geldi.',
                  );
                });
              });
            },
          }).then((result) => {
            if (result.value) {
              Swal.fire({
                icon             : 'success',
                title            : 'Ödeme sayfasına yönlendiriliyorsunuz.',
                showConfirmButton: false,
                timer            : 1500, // Belirli bir süre sonra otomatik kapanması için timer eklendi
              });

              window.location.href = gotourl;
            }
          }).catch((error) => {
            // Hata yönetimi için catch bloğu eklendi
            console.error('Bir hata meydana geldi:', error);
          });

        });


      });


    </script>
{/literal}