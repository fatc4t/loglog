    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">


    <?php echo $this->Html->css('mobileparts'); ?>
    <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=no" />


    <?php echo $this->Form->create(null, ['id' => 'MapshopForm', 'name' => 'MapshopForm', 'onsubmit' => 'return searchCheck()', 'method' => 'post', 'enctype' => 'multipart/form-data']) ?>
    <form method="post">
        <input type="hidden" name="_csrfToken" autocomplete="off" value="<?= $this->request->getAttribute('csrfToken') ?>">
        <br />
        <div class="block_header">
            <div class="parts_shopnm">
                <h1 style="overflow-wrap: normal"><?= h($shop_data[0]['shop_nm']) ?></h1>
            </div>
        </div>
        <hr size="5" width="100%" color="#e72923">
        <div class="block_img">
            <div class="tabs">
                <?php if ($shop_data[0]['thumbnail1'] != "") { ?>
                    <input id="all" type="radio" name="tab_item" checked>
                <?php } ?>
                <?php if ($shop_data[0]['thumbnail2'] != "") { ?>
                    <input id="programming" type="radio" name="tab_item">
                <?php } ?>
                <?php if ($shop_data[0]['thumbnail3'] != "") { ?>
                    <input id="design" type="radio" name="tab_item">
                <?php } ?>

                <div class="tab_content" id="all_content">
                    <?php if ($shop_data[0]['thumbnail1'] != "") { ?>
                        <img class="home_image" src="../webroot/img/Home/<?= h($shop_data[0]['shop_cd']) ?>/<?= h($shop_data[0]['thumbnail1']) ?>" />
                    <?php } ?>
                </div>
                <div class="tab_content" id="programming_content">
                    <?php if ($shop_data[0]['thumbnail2'] != "") { ?>
                        <img class="home_image" src="../webroot/img/Home/<?= h($shop_data[0]['shop_cd']) ?>/<?= h($shop_data[0]['thumbnail2']) ?>" />
                    <?php } ?>
                </div>
                <div class="tab_content" id="design_content">
                    <?php if ($shop_data[0]['thumbnail3'] != "") { ?>
                        <img class="home_image" src="../webroot/img/Home/<?= h($shop_data[0]['shop_cd']) ?>/<?= h($shop_data[0]['thumbnail3']) ?>" />
                    <?php } ?>
                </div>



            </div>
        </div>
        <div class="block_info">
            <div class="block_add">
                <p></p>
                <?php
                if ($shop_data[0]['url_sns4']) { ?>
                    <br />
                    <a href="<?= h($shop_data[0]['url_sns4']) ?>" target="_blank" rel="norefferrer">Google Mapで表示</a>
                <?php } ?>

                <p style="height: 100%">〒<?= h($shop_data[0]['shop_postcd']) ?> <br />
                    <?= h($shop_data[0]['shop_add1']) ?><?= h($shop_data[0]['shop_add2']) ?><?= h($shop_data[0]['shop_add3']) ?><br />
                    TEL　<?= h($shop_data[0]['shop_phone']) ?><br />
                    <?php
                    $opentime1 = '';
                    $opentime2 = '';
                    if ($shop_data[0]['opentime1']) {
                        $opentime1 = $shop_data[0]['opentime1'] . '～' . $shop_data[0]['closetime1'];
                    }
                    if ($shop_data[0]['opentime2']) {
                        $opentime2 = $shop_data[0]['opentime2'] . '～' . $shop_data[0]['closetime2'];
                    }
                    ?>
                    営業時間 ：<?= h($opentime1) ?>　<?= h($opentime2) ?><br />
                    定休日：<?= h($shop_data[0]['holiday1']) ?>　<?= h($shop_data[0]['holiday2']) ?>　<?= h($shop_data[0]['holiday3']) ?><br /><br />
                    <?php
                    if ($shop_data[0]['free_text']) { ?>
                        <?= h($shop_data[0]['free_text']) ?>
                    <?php } ?><br /><br />
                    <?php
                    if ($raiten) { ?>
                        <?= h(substr($raiten[0]['raiten_time'], 0, 16)) . "に来店しました。" ?>
                    <?php } ?>
                    <!--                <br />
                <a href="http://www.facebook.com/share.php?u=http://153.126.145.215/loglog/MlMobile/Mapshop?shop_cd=<?= h($shop_data[0]['shop_cd']) ?>" class="sns-btn -fb">Facebookでシェアする</a>
                <br />
                <a href="https://twitter.com/intent/tweet?url=http://153.126.145.215/loglog/MlMobile/Mapshop?shop_cd=<?= h($shop_data[0]['shop_cd']) ?>"  class="sns-btn -tw">Twitterでシェアする</a>-->
                </p>
            </div>
            <div class="block_sns">
                <ul class="list">
                    <?php if ($shop_data[0]['url_hp'] == '') { ?>
                        <li><img style="opacity: 0.5;" class="icon" src="../webroot/img/home.png" /></li>
                    <?php } else { ?>
                        <li><a href="<?= h($shop_data[0]['url_hp']) ?>" target="_blank" rel="norefferrer"><img class="icon" src="../webroot/img/home.png" /> </a></li>
                    <?php } ?>
                    <?php if ($shop_data[0]['url_sns1'] == '') { ?>
                        <li><img style="opacity: 0.5;" class="icon" src="../webroot/img/line.png" /></li>
                    <?php } else { ?>
                        <li><a href="<?= h($shop_data[0]['url_sns1']) ?>" target="_blank" rel="norefferrer"><img class="icon" src="../webroot/img/line.png" /> </a></li>
                    <?php } ?>
                    <?php if ($shop_data[0]['url_sns2'] == '') { ?>
                        <li><img style="opacity: 0.5;" class="icon" src="../webroot/img/instagram.png" /></li>
                    <?php } else { ?>
                        <li><a href="<?= h($shop_data[0]['url_sns2']) ?>" target="_blank" rel="norefferrer"><img class="icon" src="../webroot/img/instagram.png" /> </a></li>
                    <?php } ?>
                    <?php if ($shop_data[0]['url_sns3'] == '') { ?>
                        <li><img style="opacity: 0.5;" class="icon" src="../webroot/img/twitter.jpg" /></li>
                    <?php } else { ?>
                        <li><a href="<?= h($shop_data[0]['url_sns3']) ?>" target="_blank" rel="norefferrer"><img class="icon" src="../webroot/img/twitter.jpg" /> </a></li>
                    <?php } ?>

                </ul>

            </div>
        </div>

        <!-- -----------------------------------------coupon block------------------------------------- -->
        <div class="couponblock">

            <?php foreach ($cpnData as $data) { ?>
                <div class="container my-5">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="coupon bg-white rounded mb-3 d-flex justify-content-between">
                                <div class="kiri p-3">
                                    <div class="icon-container ">
                                        <div class="icon-container_box">
                                            <img src="../webroot/img/Coupon/<?= h($data['shop_cd']) ?>/<?= h($data['thumbnail1']) ?>" width="85" alt="" class="" />
                                        </div>
                                    </div>
                                </div>
                                <div class="tengah py-3 d-flex w-100 justify-content-start">
                                    <div>
                                        <span class="badge badge-success">有効期限クーポン</span>
                                        <h3 class="lead"><?= h($data['coupon_goods']) ?> </h3>
                                        <p class="text-muted mb-0"><?= h($data['coupon_discount']) ?></p>
                                        <p class="text-muted mb-0"><?= h($data['effect_srt']) ?>～<?= h($data['effect_end']) ?></p>
                                    </div>
                                </div>
                                <div class="kanan">
                                    <div class="info m-3 d-flex align-items-center">
                                        <div class="w-100">
                                            <div class="block">
                                                <span class="time font-weight-light">
                                                    <span></span>
                                                </span>
                                            </div>

                                            <button class="useButton btn btn-sm btn-outline-danger btn-block" type="submit" id="<?= h($data['coupon_cd']) ?>">利用</button>

                                            <!-- ---------------------------------------------------hidden----------------------------------------------- -->
                                            <input id="coupon_goods" type="hidden" name="coupon_goods" value="<?= h($data['coupon_goods']) ?>">
                                            <input id="coupon_discount" type="hidden" name="coupon_discount" value="<?= h($data['coupon_discount']) ?>">
                                            <input id="effect_srt" type="hidden" name="effect_srt" value="<?= h($data['effect_srt']) ?>">
                                            <input id="effect_end" type="hidden" name="effect_end" value="<?= h($data['effect_end']) ?>">
                                            <input id="coupon_cd" type="hidden" name="coupon_cd" value="<?= h($data['coupon_cd']) ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>


        </div>

    </form>
    <?php echo $this->Form->end() ?>





    <script>
        function searchCheck() {
            var agree = confirm("ご利用よろしいですか？");
            if (agree)
                return true;
            else
                return false;
        }



        $(".useButton").on("click", function() {
            var coupon_cd = $(this).attr('id');
            document.getElementById("coupon_cd").value = coupon_cd;
        });
    </script>