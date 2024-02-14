<div class="container">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Modül Ayarları</h3>
            </div>
            <form class="form-horizontal" method="post" action="addonmodules.php?module=hosting_renew">

                <input type="hidden" name="action" value="savesettings">

                <div class="panel-body">

                    {if $post_success eq 1}
                    <div class="alert alert-success">Ayarlar Kaydedildi</div>
                    {/if}

                    <div class="form-group">
                        <label for="showınallpages" class="col-sm-4 control-label">Tüm Sayfalarda Yenileme Linki Göster</label>
                        <div class="col-sm-8">
                            <input type="checkbox" id="showinallpages" name="showinallpages" value="1" {if $setting.showinallpages eq 1}checked{/if}>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="showınhosting" class="col-sm-4 control-label">Hosting Sayfasında Yenileme Linki Göster</label>
                        <div class="col-sm-8">
                            <input type="checkbox" id="showinhosting" name="showinhosting" value="1" {if $setting.showinhosting eq 1}checked{/if}>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="showındomain" class="col-sm-4 control-label">Domain Sayfasında Yenileme Linki Göster</label>
                        <div class="col-sm-8">
                            <input type="checkbox" id="showindomain" name="showindomain" value="1" {if $setting.showindomain eq 1}checked{/if}>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="showsummaryonhomepage" class="col-sm-4 control-label">Anasayfada Özet Bilgileri Göster</label>
                        <div class="col-sm-8">
                            <input type="checkbox" id="showsummaryonhomepage" name="showsummaryonhomepage" value="1" {if $setting.showsummaryonhomepage eq 1}checked{/if}>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="hideexpireddomains" class="col-sm-4 control-label">Üzerinden Kaç Gün Geçmiş Domainleri Gizle</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" id="hideexpireddomains" name="hideexpireddomains" placeholder="Gün sayısı girin" value="{$setting.hideexpireddomains}">
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <button type="submit" class="btn btn-primary">Ayarları Kaydet</button>
                </div>
            </form>
        </div>
</div>
