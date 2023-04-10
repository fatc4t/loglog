<?php $this->assign('title', '.loglog'.$title); ?>
<?php echo $this->element('Common/header'); ?>
<?php echo $this->Html->css('MlCustrank'); ?>
<form method="post">

  <input type="hidden" name="_csrfToken" autocomplete="off" value="<?= $this->request->getAttribute('csrfToken') ?>">    

<div id="sb-site" style="overflow: auto;padding-top: 5px;padding-bottom: 5px;">

    <div class="title"><?= h($title); ?></div>
                            

    <?php echo $this->Form->create(null, [
                                      'id'       => 'CustrankForm', 
                                      'name'     => 'CustrankForm',
                                      'method'   => 'post', 
                                      'enctype'  => 'multipart/form-data',
                                      'onsubmit' => 'return check()'
                                    ]) ?>
    <div >
        <?php echo $this->Form->input('shop_cd',[
                                        'type'      => 'text',
                                        'id'        => 'shop_cd',
                                        'name'      => 'shop_cd',
                                        'value'     => '',
                                        'hidden',
                                        'label' => false,
                                        ]); ?>
        <?php echo $this->Form->input('rank_cd',[
                                        'type'      => 'text',
                                        'id'        => 'rank_cd',
                                        'name'      => 'rank_cd',
                                        'value'     => '',
                                        'hidden',
                                        'label' => false,
                                        ]); ?>
        <?php echo $this->Form->input('btn_click_name',[
                                        'type'      => 'text',
                                        'id'        => 'btn_click_name',
                                        'value'     => '',
                                        'hidden',
                                        'label' => false,
                                        ]); ?> 
    </div>
    <br />


    <label for="select5Small">
        <input type="radio" id="kbn_1" name="poll_kbnCheck" onclick="check(this.id)" value="1">Questionairre ONLY
    </label>
    <label for="select1Big">
        <input type="radio" id="kbn_2" name="poll_kbnCheck" onclick="check(this.id)" value="2">Comment ONLY
    </label>
    <br>


    <div id="small5" class="div_his1">
        <table class="">
            <tr class="caption">
                <td width=20%>Question 1</td>
                <td><input id="ans1" disabled="disabled" style="width:98%;" name="ans1" value="<?= h($ans1); ?>" ></td>
            </tr>
            <tr class="caption">
                <td width=20%>Question 2</td>
                <td><input id="ans1" disabled="disabled" style="width:98%;" name="ans2"  value="<?= h($ans2); ?>" ></td>
            </tr>
            <tr class="caption">
                <td width=20%>Question 3</td>
                <td><input id="ans2" disabled="disabled" style="width:98%;" name="ans3"  value="<?= h($ans3); ?>" ></td>
            </tr>
            <tr class="caption">
                <td width=20%>Question 4</td>
                <td><input id="ans3" disabled="disabled" style="width:98%;" name="ans4"  value="<?= h($ans4); ?>" ></td>
            </tr>   
            <tr class="caption">
                <td width=20%>Question 5</td>
                <td><input id="ans5" disabled="disabled" style="width:98%;" name="ans5"  value="<?= h($ans5); ?>" ></td>
            </tr>                 
    </div>
    <div id="commentBOX" class="">
        <table class="">
            <tr class="">
                <td width=10%>Comment</td>
                <!-- <td><input style="width:98%;" name="bigAns" value="<?= h($bigAns); ?>"></td>       -->
                <td><textarea id="bigAns" style="resize:none;" id="bigAns" disabled="disabled" name="bigAns"  rows="4" cols="50"  ></textarea></td>
            </tr>
        </table>     

    </div



    <br>

    <?php echo $this->Form->input('search',[
                                    'type'      => 'submit',
                                    'id'        => 'rank_search',
                                    'onclick'   => 'btn_click("'.CON_SAVE_IN3.'")', //    config/common_const.php
                                    'value'     => CON_SAVE_IN3,
                                    'style'     => 'width: 100',
                                    'label'     => false,
                                    'onsubmit'  => 'return check()'
                                    ]); ?>
    <div class="div_his1">
        <table class="table_his">
            <tr>
                <td>
                    <table class="topic">
                        <tr class="caption">
                            <td>SHOP＿CD</td>
                            <td>INSERTDATE</td>
                            <td>USER</td>
                            <td>POLL＿KBN</td>
                            <td>Answer 1</td>
                            <td>Answer 2</td>
                            <td>Answer 3</td>
                            <td>Answer 4</td>
                            <td>Answer 5</td>
                            <td>Comment</td>
                            
                        </tr>
                    </table>
                    <table class="tables" id="tables">
                        <?php
                        
                        foreach ($poll_data as $rows ) { ?>
                            <tr class="table_tr">
                                <td align=left><?= h($rows['shop_cd']); ?></td>
                                <td align=left width=40%><?= h($rows['insdatetime']); ?></td>
                                <td align=left><?= h($rows['poll_kbn']); ?></td>
                                <td align=left><?= h($rows['poll_kbn']); ?></td>
                                <td align=left><?= h($rows['ans1']); ?></td>
                                <td align=left><?= h($rows['ans2']); ?></td>
                                <td align=left><?= h($rows['ans3']); ?></td>  
                                <td align=left><?= h($rows['ans4']); ?></td>
                                <td align=left><?= h($rows['ans5']); ?></td>
                                <td align=left><?= h($rows['bigans']); ?></td>     
                            </tr>
                        <?php 
                        } ?>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    
</div>    
</form>
<?php echo $this->Form->end() ?>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script>
    $(".rank_delete").on("click", function(){
       //
       var shop_cd = $(this).closest('tr').children("td")[0].innerText;
       document.getElementById("shop_cd").value = shop_cd;
       //  
       var rank_cd = $(this).closest('tr').children("td")[1].innerText;
       document.getElementById("rank_cd").value = rank_cd;
    });
    
    $(".rank_edit").on("click", function(){
       // 
       var shop_cd = $(this).closest('tr').children("td")[0].innerText;
       document.getElementById("shop_cd").value = shop_cd;
       // 
       var rank_cd = $(this).closest('tr').children("td")[1].innerText;
       document.getElementById("rank_cd").value = rank_cd;
    });
    /**
     * 押下ボタンが「検索」か「CSV出力」かを保持するHiddenテキストボックスに値を詰める
     */
    function btn_click(btn_name)
    {
        document.getElementById("btn_click_name").value = btn_name;
    }

    $(function () {
        $("input[name='poll_kbnCheck']").click(function () {
            if ($("#kbn_1").is(":checked")) {
                $("#ans1").removeAttr("disabled");
                $("#ans2").removeAttr("disabled");
                $("#ans3").removeAttr("disabled");
                $("#ans4").removeAttr("disabled");
                $("#ans5").removeAttr("disabled");        
                $("#ans1").focus();
            } else {
                $("#bigAns").attr("disabled", "disabled");
            }
        });
    });ent.getElementById(othertextbox).checked = false;
    

</script>