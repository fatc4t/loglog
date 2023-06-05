    <!-- Bootstrap CSS -->
    <!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"> -->


    <link href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
    <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>

    <?php
    // Clear cache
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    ?>

    <?php echo $this->Html->css('mobileparts'); ?>
    <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=no" />


    <?php echo $this->Form->create(null, ['id' => 'MapshopForm', 'name' => 'MapshopForm', 'onsubmit' => 'return searchCheck()', 'method' => 'post', 'enctype' => 'multipart/form-data']) ?>
    <form method="post">
        <input type="hidden" name="_csrfToken" autocomplete="off" value="<?= $this->request->getAttribute('csrfToken') ?>">
        <br />
        <div class="block_img">
            <div class="tabs">
                <?php if ($shop_data[0]['thumbnail1'] != "") { ?>
                    <input id="all" type="radio" name="tab_item" checked hidden>

                <?php } ?>
                <?php if ($shop_data[0]['thumbnail2'] != "") { ?>
                    <input id="programming" type="radio" name="tab_item" hidden>

                <?php } ?>
                <?php if ($shop_data[0]['thumbnail3'] != "") { ?>
                    <input id="design" type="radio" name="tab_item" hidden>

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
        <hr size="5" width="100%" color="#e72923">
        <div class="block_header">
            <div class="parts_shopnm">
                <h1><?= h($shop_data[0]['shop_nm']) ?></h1>
            </div>
        </div>
        <div class="block_sns">
            <ul class="list">
                <?php
                if ($shop_data[0]['url_sns4']) { ?>
                    <a href="<?= h($shop_data[0]['url_sns4']) ?>" target="_blank" rel="norefferrer"><img class="icon" src="../webroot/img/googlemap.png" /> </a>
                <?php } ?>

                <?php if ($shop_data[0]['url_sns1'] == '') { ?>
                    <img style="opacity: 0.5;" class="icon" src="../webroot/img/line.png" />
                <?php } else { ?>
                    <a href="<?= h($shop_data[0]['url_sns1']) ?>" target="_blank" rel="norefferrer"><img class="icon" src="../webroot/img/line.png" /> </a>
                <?php } ?>

                <?php if ($shop_data[0]['url_sns2'] == '') { ?>
                    <img style="opacity: 0.5;" class="icon" src="../webroot/img/instagram.png" />
                <?php } else { ?>
                    <a href="<?= h($shop_data[0]['url_sns2']) ?>" target="_blank" rel="norefferrer"><img class="icon" src="../webroot/img/instagram.png" /> </a>
                <?php } ?>


                <?php if ($shop_data[0]['url_sns3'] == '') { ?>
                    <img style="opacity: 0.5;" class="icon" src="../webroot/img/twitter.jpg" />
                <?php } else { ?>
                    <a href="<?= h($shop_data[0]['url_sns3']) ?>" target="_blank" rel="norefferrer"><img class="icon" src="../webroot/img/twitter.jpg" /> </a>
                <?php } ?>


                <?php if ($shop_data[0]['url_hp'] == '') { ?>
                    <img style="opacity: 0.5;" class="icon" src="../webroot/img/home.png" />
                <?php } else { ?>
                    <a href="<?= h($shop_data[0]['url_hp']) ?>" target="_blank" rel="norefferrer"><img class="icon" src="../webroot/img/home.png" /> </a>
                <?php } ?>

            </ul>

        </div>
        <div class="block_info">
            <div class="block_add">

                <p style="height: 100%" class="maximize-paragraph">
                    <img class="icon" src="../webroot/img/address.svg" id="pinMark" />〒<?= h($shop_data[0]['shop_postcd']) ?><br />
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= h($shop_data[0]['shop_add1']) ?><?= h($shop_data[0]['shop_add2']) ?><br />
                    <?php
                    if ($shop_data[0]['shop_add3'] != null || $shop_data[0]['shop_add3']  != "") { ?>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= h($shop_data[0]['shop_add3']) ?><br />
                    <?php } ?>
                    <img class="icon" src="../webroot/img/time.svg" id="timeMark" />
                    <?php
                    $opentime1 = '';
                    $opentime2 = '';
                    if ($shop_data[0]['opentime1']) {
                        $opentime1 = trim($shop_data[0]['opentime1']) . '~' . trim($shop_data[0]['closetime1']);
                    }
                    if ($shop_data[0]['opentime2']) {
                        $opentime2 = trim($shop_data[0]['opentime2']) . '~' . trim($shop_data[0]['closetime2']);
                    }
                    ?>
                    <?= h(trim($opentime1)) ?>　<?= h(trim($opentime2)) ?><br />
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;定休日:<?= h($shop_data[0]['holiday1']) ?>　<?= h($shop_data[0]['holiday2']) ?>　<?= h($shop_data[0]['holiday3']) ?><br />
                    <img class="icon" src="../webroot/img/telephone.svg" id="phoneMark" /><?= h($shop_data[0]['shop_phone']) ?><br /><br />
                    <?php
                    if ($shop_data[0]['free_text']) { ?>
                        <?= h($shop_data[0]['free_text']) ?>
                    <?php } ?><br /><br />
                    <?php
                    if ($raiten) { ?>
                        <?= h(substr($raiten[0]['raiten_time'], 0, 16)) . "に来店しました。" ?>
                    <?php } ?>
                </p>
            </div>
        </div>

        <!-- -----------------------------------------coupon block------------------------------------- -->
        <hr size="5" width="100%" color="#e72923">





        <!-- <div class="couponblock">

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
                                            <br>

                                            <button class="useButton btn btn-sm btn-outline-danger btn-block" type="input" id="<?= h($data['unique_coupon_cd']) ?>">利用</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div> -->


        <!-- <div class="couponblock">
            <div class="container content">
                <div id="carousel-example-generic" class="carousel slide" data-ride="carousel"> -->
        <!--Indicators -->
        <!-- <ol class="carousel-indicators">
                    <li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>
                    <li data-target="#carousel-example-generic" data-slide-to="1"></li>
                    <li data-target="#carousel-example-generic" data-slide-to="2"></li>
                </ol> Wrapper for slides -->
        <!-- <div class="carousel-inner">
                        <?php $counter = 0; ?>
                        <?php foreach ($cpnData as $data) { ?>
                            <div class="item<?php if ($counter === 0) {
                                                echo ' active';
                                            } ?>">
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="thumbnail adjust1">
                                            <div class="col-md-2 col-sm-2 col-xs-12"> <img class="media-object img-rounded img-responsive" src="../webroot/img/Coupon/<?= h($data['shop_cd']) ?>/<?= h($data['thumbnail1']) ?>">
                                            </div>
                                            <div class="col-md-10 col-sm-10 col-xs-12">
                                                <div class="caption">
                                                    <p class="text-info lead adjust2"><?= h($data['coupon_goods']) ?></p>
                                                    <p><?= h($data['effect_srt']) ?>～<?= h($data['effect_end']) ?></p>
                                                    <blockquote class="adjust2">
                                                        <p><?= h($data['coupon_discount']) ?></p><cite title="Source Title">
                                                            <button class="useButton btn btn-sm btn-outline-danger btn-block" type="input" id="<?= h($data['unique_coupon_cd']) ?>">利用</button></cite></small>
                                                    </blockquote>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php $counter++; ?>
                        <?php } ?>

                    </div> -->
        <!-- Controls  -->
        <!-- <?php if ($cpnData) { ?>
                        <a class="left carousel-control" href="#carousel-example-generic" data-slide="prev"><span class="glyphicon glyphicon-chevron-left"></span></a>
                        <a class="right carousel-control" href="#carousel-example-generic" data-slide="next"> <span class="glyphicon glyphicon-chevron-right"></span></a>
                    <?php } ?>
                </div>
            </div>
        </div> -->







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
            var unique_coupon_cd = $(this).attr('id');
            alert(unique_coupon_cd);

            var hiddenInput = document.createElement("input");
            hiddenInput.setAttribute("type", "hidden");
            hiddenInput.setAttribute("name", "unique_coupon_cd");
            hiddenInput.setAttribute("value", unique_coupon_cd);

            $("#MapshopForm").append(hiddenInput);

        });
    </script>