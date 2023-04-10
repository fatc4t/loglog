<?php echo $this->Html->css('mobileparts'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<link rel="stylesheet" href="/loglog/webroot/css/mobilemapshopmini.css" />

<!-- 前回のIMG -->
<!-- <link rel="stylesheet" href="/loglog/webroot/css/mobilemapshop.css" /> -->


<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=no" />



<form method="post">
    <input type="hidden" name="_csrfToken" autocomplete="off" value="<?= $this->request->getAttribute('csrfToken') ?>">

    <div class="item active">
        

        <?php if ($shop_data[0]['thumbnail1'] != "") { ?>
            <img class='img-fluid' src="../webroot/img/Home/<?= h($shop_data[0]['shop_cd']) ?>/<?= h($shop_data[0]['thumbnail1']) ?>" />
        <?php } ?>
    </div>
<hr>
    <br />
    <div class="block_header">
        <div class="parts_shopnm">
            <h1 style="overflow-wrap: normal"><?= h($shop_data[0]['shop_nm']) ?></h1>
        </div>
    </div>



</form>