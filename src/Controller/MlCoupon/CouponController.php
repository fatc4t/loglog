<?php

namespace App\Controller\MlCoupon;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\Cache\Cache;
use Cake\Filesystem\Folder;
use Cake\Filesystem\File;
use Cake\Datasource\ConnectionManager;
use App\Controller\Component\MlCommon\CommonComponent;

class CouponController extends AppController
{

    public function beforeFilter(EventInterface $event)
    {

        parent::beforeFilter($event);

        // '_cake_core_' のキャッシュを削除
        Cache::clear('_cake_core_');

        // 'default' のキャッシュを削除
        Cache::clear();

        // ajaxでPOSTするFunctionのみ許可
        $this->Security->setConfig('unlockedActions', ['ajaxShow', 'index']);
    }

    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('RequestHandler');

        $uri = $_SERVER['REQUEST_URI'];
        $this->set(compact('uri'));

        // Load the paginator component with the simple paginator strategy.
        $this->loadComponent('Paginator', [
            'paginator' => new \Cake\Datasource\SimplePaginator(),
        ]);

        $this->session = $this->getRequest()->getSession();
    }
    public function index()
    {

        $this->set('title', 'クーポン作成');

        // 共通のComponentを呼び出す
        $common = new CommonComponent();

        // urlから店舗コードを取得する
        $shop_cd = $this->request->getQuery('shop_cd');
        // urlからクーポンコードを取得する
        $coupon_cd = $this->request->getQuery('coupon_cd');
        $couponChecker = $this->request->getQuery('unique_coupon_cd'); //--check if unique_coupon_cd exist



        // DBより店舗情報を取得
        $shop_dataW = "shop_cd = '" . $shop_cd . "'";
        $shop_dataO = "shop_cd ";
        $shop_data  = $common->prGetData("mst0010", $shop_dataW, $shop_dataO);

        $this->set(compact('shop_data'));
        if (!$shop_data) {
            echo '【店舗情報が見つかりません。URLに間違いないか確認してください。】';
            exit();
        }

        // お店の名前
        $shop_nm = $shop_data[0]['shop_nm'];
        $this->set(compact('shop_nm'));

        // DBよりエリア情報を取得
        $area = $common->prGetData("mst0015");
        $this->set(compact('area'));

        // DBより顧客ランク情報を取得
        $whereR = "shop_cd = '" . $shop_cd . "'";
        $rank   = $common->prGetData("mst0016", $whereR);
        $this->set(compact('rank'));

        // 
        $months = $common->prGetmonths();
        $this->set(compact('months'));

        $genders = $common->prGetgender();
        $this->set(compact('genders'));

        $ages = $common->prGetages();
        $this->set(compact('ages'));

        //IMGはDBにあれば------------------------------------KARL
        $existImg = $shop_data[0]['thumbnail1'];
        $this->set(compact('existImg'));

        //visit conditions 来店条件を表示する
        $visit_conditions = $common->getVisitConditions();
        $this->set(compact('visit_conditions'));


        if ($couponChecker) { //EDIT COUPON

            $cpn_data[0]['unique_coupon_cd'] = $couponChecker;


            //GET クーポンデータ - K(2023/04)
            $cpn_data = $common->getCouponData("coupons", $couponChecker, $shop_cd);



            $existImg = $cpn_data[0]['thumbnail1']; //追加した　KARL

            $str1               =  substr($cpn_data[0]['effect_srt'], 0, 4);
            $str2               =  substr($cpn_data[0]['effect_srt'], 4, 2);
            $str3               =  substr($cpn_data[0]['effect_srt'], 6, 2);
            $effect_srt         = $str1 . "-" . $str2 . "-" . $str3;

            $str4               =  substr($cpn_data[0]['effect_end'], 0, 4);
            $str5               =  substr($cpn_data[0]['effect_end'], 4, 2);
            $str6               =  substr($cpn_data[0]['effect_end'], 6, 2);
            $effect_end         =  $str4 . "-" . $str5 . "-" . $str6;
            $prefecture         =  $cpn_data[0]['prefecture'];
            $age                =  $cpn_data[0]['age'];
            $genderB            =  $cpn_data[0]['gender'];
            $birthday           =  $cpn_data[0]['birthday'];
            $rankB              =  $cpn_data[0]['rank'];
            $visit_condition    =  $cpn_data[0]['visit_condition']; //来店条件----------from DB KARL



            if ($cpn_data[0]['background'] == NULL) {
                $background  = "#ffffff";
                $color       = "#696969";
            } else {
                $background  = $cpn_data[0]['background'];
                $color       = $cpn_data[0]['color'];
            }
        } else {



            //$cpn_data[0]['coupon_cd']       = (float)$coupon_cd[0]['coupon_cd'] + 1;
            $cpn_data[0]['coupon_goods']    = "";
            $cpn_data[0]['coupon_discount'] = "";
            $cpn_data[0]['thumbnail1']      = "";
            $cpn_data[0]['thumbnail2']      = "";
            $cpn_data[0]['thumbnail3']      = "";
            $effect_srt                     = "";
            $effect_end                     = "";
            $background                     = "#ffffff";
            $color                          = "#696969";
            $prefecture                     = "";
            $age                            = "";
            $genderB                        = "";
            $birthday                       = "";
            $rankB                          = "";
        } //--END of if(couponChecker)

        $this->set(compact('cpn_data'));
        $this->set(compact('effect_srt'));
        $this->set(compact('effect_end'));
        $this->set(compact('background'));
        $this->set(compact('color'));
        $this->set(compact('prefecture'));
        $this->set(compact('age'));
        $this->set(compact('genderB'));
        $this->set(compact('birthday'));
        $this->set(compact('rankB'));

        $this->set(compact('visit_conditions'));  //来店条件ーーーーーーーーーーー KARL
        $this->set(compact('existImg'));  //IMG---thumbnail1-------- KARL 


        if ($this->getRequest()->is('post')) { //from Preview-> CONFIRM button

            // 画面パラメータ
            $searchParam =  $this->getRequest()->getData();
            $this->set(compact('searchParam'));


            //if ($shop_data[0]['paidmember'] == 0) { //conditions not shown(FREE USER)
            $searchParam['user_add']    = "";
            $searchParam['age']         = "";
            $searchParam['birth_month'] = "";
            $searchParam['gender']      = "";
            $searchParam['background']  = "";
            $searchParam['color']       = "";
            $searchParam['rank']       = "";
            //}
            
            $searchParam['thumbnail1'] = "";
            $searchParam['thumbnail2'] = "";
            $searchParam['thumbnail3'] = "";
            if (!$searchParam['background']) {
                $searchParam['background'] = "#ffffff";
            }
            if (!$searchParam['color']) {
                $searchParam['color'] = "696969";
            }




            if ($searchParam['btn_click_name'] == CON_SAVE_IN) {

                $whereU = " 1 = 1";
                if ($searchParam['user_add']) {
                    $whereU .= " and add1 like '%" . $searchParam['user_add'] . "%' ";
                }

                if ($searchParam['age']) {

                    // 年を取得 
                    $year = date('Y');
                    // 年代 
                    $age = $searchParam['age'];
                    $yearlist2 = "(";
                    for ($i = 0; $i < $age; $i++) {

                        $year_list = ((int)$year - (int)$i - (int)$age);

                        $yearlist2 .= "'" . $year_list . "',";
                    }
                    $year_list1 = substr($yearlist2, 0, -1);

                    $year_list1 .= ")";

                    $whereU    .= " and substr(birthday,0,5) in " . $year_list1;
                }
                if ($searchParam['birth_month']) {
                    $whereU .= " and substr(birthday,5,2 )  = '" . $searchParam['birth_month'] . "'";
                }
                if ($searchParam['gender']) {
                    $whereU .= " and gender = '" . $searchParam['gender'] . "' ";
                }


                // 対象のユーザーを取得する
                $user_data = $common->prGetData("mst0011", $whereU, NULL, NULL);
                $this->set(compact('user_data'));



                



                if (!$couponChecker) { //----If NULL-> INSERT DB
                   

                    if ($shop_data[0]['paidmember'] == 0) { //--free member



                        $searchParam['insuser_cd']   = $shop_cd;
                        $searchParam['insdatetime']  = "now()";
                        $searchParam['upduser_cd']   = $shop_cd;
                        $searchParam['updatetime']   = "now()";
                        $searchParam['shop_cd']      = $shop_cd;
                        //$searchParam['coupon_cd']    = $cpn_cd_1;  //クーポン履歴の為に　要る
                        $searchParam['effect_srt']   = str_replace("-", "", $searchParam['effect_srt']);
                        $searchParam['effect_end']   = str_replace("-", "", $searchParam['effect_end']);

                        //$searchParam['user_cd']      = $val['user_cd'];  //remove this
                        $searchParam['connect_kbn']  = '0';
                        $searchParam['used']         = '0';
                        $searchParam['prefecture']   = NULL;
                        $searchParam['age']          = NULL;
                        $searchParam['gender']       = NULL;
                        $searchParam['birthday']     = NULL;
                        $searchParam['rank']         = NULL;
                        $searchParam['background']   = "#ffffff";
                        $searchParam['color']        = "696969";


                        $unique_cp_valArr = $common->insertNEWCouponData("coupons", $searchParam, 0); //0 for FREE member(paidmemberchecker)
                        $unique_cp_val = $unique_cp_valArr[0]['currval'];

                        //------------------------------------------------THUMBNAIL---------------------------------------------------------------------
                        // 写真保存用のパスを設定する -------------if EDIT (unique_coupon_cd あり)
                        //path for IMAGE
                        $path    = CON_CPN_IMAGE . $shop_cd . '/' . $unique_cp_val;

                        $myFiles = $this->request->getData('my_file');
                        $pic_nm  = $common->prSavePic($path, $myFiles);
                       

                        if ($pic_nm[0] !== "" && $pic_nm[0]  !== null) {
                            $j = 1;
                            foreach ($pic_nm as $val) {
                                if ($cpn_data[0]['thumbnail' . $j] !== "" && $cpn_data[0]['thumbnail' . $j] !== null) { //if not empty, assign new file name and delete old file
                                    $searchParam['thumbnail' . $j] = $val;
                                    if (file_exists($path . '/' . $cpn_data[0]['thumbnail' . $j])) {
                                        unlink($path . '/' . $cpn_data[0]['thumbnail' . $j]);
                                    }
                                } else {
                                    $searchParam['thumbnail' . $j] = $val;          //if EMPTY assign new file
                                }
                                $j++;
                            }
                        } else { //if pic_nm is empty
                            $j = 1;
                            foreach ($pic_nm as $val) {
                                $searchParam['thumbnail' . $j] = $cpn_data[0]['thumbnail' . $j]; //assign current file to coupon
                            }
                        }

                        $common->updateCouponThumbnail('coupons', $unique_cp_val, $searchParam); //update thumbnail
                        //------------------------------------------------THUMBNAIL---------------------------------------------------------------------


                    }else{ // PAID MEMBER 会員あれば

                        $searchParam['updatetime']   = "now()";
                        $searchParam['shop_cd']      = $shop_cd;
                        //$searchParam['coupon_cd']    = $cpn_cd_1;
                        $searchParam['effect_srt']   = str_replace("-", "", $searchParam['effect_srt']);
                        $searchParam['effect_end']   = str_replace("-", "", $searchParam['effect_end']);

                        //$searchParam['user_cd']      = $val['user_cd'];
                        $searchParam['connect_kbn']  = '0';
                        $searchParam['used']         = '0';
                        $searchParam['prefecture']   = $searchParam['user_add'];
                        $searchParam['age']          = $searchParam['age'];
                        $searchParam['gender']       = $searchParam['gender'];
                        $searchParam['birthday']     = $searchParam['birth_month'];
                        $searchParam['rank']         = $searchParam['rank'];


                        //　登録する---INSERT
                        //$common->insertCouponData("mst0012", $searchParam, 1);   //OLD SHIT

                        $unique_cp_valArr = $common->insertNEWCouponData("coupons", $searchParam, 1);  //1 for PAID member(paidmemberchecker)
                        $unique_cp_val = $unique_cp_valArr[0]['currval'];

                        //------------------------------------------------THUMBNAIL---------------------------------------------------------------------
                        // 写真保存用のパスを設定する -------------if EDIT (unique_coupon_cd あり)
                        //path for IMAGE
                        $path    = CON_CPN_IMAGE . $shop_cd . '/' . $unique_cp_val;

                        $myFiles = $this->request->getData('my_file');
                        $pic_nm  = $common->prSavePic($path, $myFiles);  


                        if ($pic_nm[0] !== "" && $pic_nm[0]  !== null) {
                            $j = 1;
                            foreach ($pic_nm as $val) {
                                if ($cpn_data[0]['thumbnail' . $j] !== "" && $cpn_data[0]['thumbnail' . $j] !== null) { //if not empty, assign new file name and delete old file
                                    $searchParam['thumbnail' . $j] = $val;
                                    if (file_exists($path . '/' . $cpn_data[0]['thumbnail' . $j])) {
                                        unlink($path . '/' . $cpn_data[0]['thumbnail' . $j]);
                                    }
                                } else {
                                    $searchParam['thumbnail' . $j] = $val;          //if EMPTY assign new file
                                }
                                $j++;
                            }
                        } else { //if pic_nm is empty
                            $j = 1;
                            foreach ($pic_nm as $val) {
                                $searchParam['thumbnail' . $j] = $cpn_data[0]['thumbnail' . $j]; //assign current file to coupon
                            }
                        }

                        $common->updateCouponThumbnail('coupons', $unique_cp_val, $searchParam); //update thumbnail
                        //------------------------------------------------THUMBNAIL---------------------------------------------------------------------
                    }
                } else { //check if COUPON_CD not NULL = update ALL ONCE 
                    
                    //------------------------------------------------THUMBNAIL---------------------------------------------------------------------
                    // 写真保存用のパスを設定する -------------if EDIT (unique_coupon_cd あり)
                    //path for IMAGE
                    $path    = CON_CPN_IMAGE . $shop_cd . '/' . $couponChecker;

                    $myFiles = $this->request->getData('my_file'); 
                    $pic_nm  = $common->prSavePic($path, $myFiles); 


                    if ($pic_nm[0] !== "" && $pic_nm[0]  !== null) {
                        $j = 1;
                        foreach ($pic_nm as $val) {
                            if ($cpn_data[0]['thumbnail' . $j] !== "" && $cpn_data[0]['thumbnail' . $j] !== null) { //if not empty, assign new file name and delete old file
                                $searchParam['thumbnail' . $j] = $val;
                                if (file_exists($path . '/' . $cpn_data[0]['thumbnail' . $j])) {
                                    unlink($path . '/' . $cpn_data[0]['thumbnail' . $j]);
                                }
                            } else {
                                $searchParam['thumbnail' . $j] = $val;          //if EMPTY assign new file
                            }
                            $j++;
                        }
                    } else { //if pic_nm is empty
                        $j = 1;
                        foreach ($pic_nm as $val) {
                            $searchParam['thumbnail' . $j] = $cpn_data[0]['thumbnail' . $j]; //assign current file to coupon
                        }
                    }
                    //------------------------------------------------THUMBNAIL---------------------------------------------------------------------


                    //$whereUpdate = " shop_cd = '" . $shop_cd . "' and coupon_cd ='" . $cpn_cd_1 . "'";
                    $whereUpdate = " unique_coupon_cd =" . $couponChecker . " AND  shop_cd = '" . $shop_cd . "'";


                    if ($shop_data[0]['paidmember'] == 1) { //paid member UPDATE

                        //searchParam 追加する
                        $searchParam['birthday']     = $searchParam['birth_month'];
                        $searchParam['prefecture']   = $searchParam['user_add'];
                        $searchParam['effect_srt']   = str_replace("-", "", $searchParam['effect_srt']);
                        $searchParam['effect_end']   = str_replace("-", "", $searchParam['effect_end']);

                        $common->updateCouponData("coupons", $searchParam, $whereUpdate);

                        //=================================================================add COUPONS table update here


                    } else { //FREE  member UPDATE

                        $searchParam['effect_srt']   = str_replace("-", "", $searchParam['effect_srt']);
                        $searchParam['effect_end']   = str_replace("-", "", $searchParam['effect_end']);

                        $common->updateCouponData("coupons", $searchParam, $whereUpdate);

                        //=================================================================add COUPONS table update here
                    }
                }
            } //--END of button click SAVE
            return $this->redirect(
                [
                    'controller'  => '../MlRCpn/RCpn', 'action' => 'index', '?'      => [
                        'shop_cd'  => $shop_cd
                    ]
                ]
            );
        } //--END of if(POST) 


    } //--END of index() 

    private function prGetcouponData($shop_cd)
    {
        $connection = ConnectionManager::get('default');

        $sql   = "";
        $sql   .= "select ";
        $sql   .= " max(coupon_cd) as coupon_cd ";
        $sql   .= "from ";
        $sql   .= "mst0012 ";
        $sql   .= "where ";
        $sql   .= "shop_cd = '" . $shop_cd . "'";

        // SQLの実行
        $query = $connection->query($sql)->fetchAll('assoc');
        $this->set(compact('query'));

        return $query;
    }
}
