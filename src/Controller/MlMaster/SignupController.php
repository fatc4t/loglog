<?php

namespace App\Controller\MlMaster;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\Cache\Cache;
use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\Folder;
use Cake\Filesystem\File;
use App\Controller\Component\MlCommon\CommonComponent;

class SignupController extends AppController
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

    /*
     * 
     *      
     */
    public function index($shop_cd = NULL)
    {

        $this->set('title', '店舗登録');

        // 共通のComponentを呼び出す
        $common = new CommonComponent();

        // urlから店舗コードを取得する
        $shop_cd  = '0001';
        $shop_cd1 = $this->request->getQuery('shop_cd1');
        $shop_cdChecker = $this->request->getQuery('shop_cd1');

        $path        = "";
        $cardLogoPath = CON_IMAGE_Logo;
        
        //('../webroot/img/CardLogo/')
        if($shop_cd1){
            $cardLogoPath = CON_IMAGE_Logo. $shop_cd1;   
        }else{
            $cardLogoPath = CON_IMAGE_Logo. $shop_cd;    
        }

      

        $pic_nm             = [];
        $pic_nm_cardLogo    = [];

        if ($shop_cd1) {
            // DBより店舗情報を取得
            $shop_dataW = "shop_cd = '" . $shop_cd1 . "'";
            $shop_dataO = "shop_cd ";
            $shop_data  = $common->prGetData("mst0010", $shop_dataW, $shop_dataO);
        } else {

            $where  = "";
            $where .= " shop_cd != '0001';";
            // DBよりMAX店舗コードを取得
            $result = $common->prGetMaxValue('shop_cd', 'mst0010', $where);


            //　結果に+1し、新店舗コード作成。
            //  $shop_cd1 = $result[0]['max']+1;
            $shop_cd1 = sprintf("%06d", $result[0]['max'] + 1);


            // 初期化する
            $shop_data = [];
            $shop_data[0]['insdatetime'] = "now()";
            $shop_data[0]['shop_nm']     = "";
            $shop_data[0]['shop_kn']     = "";
            $shop_data[0]['shop_phone']  = "";
            $shop_data[0]['shop_fax']    = "";
            $shop_data[0]['shop_postcd'] = "";
            $shop_data[0]['shop_add1']   = "";
            $shop_data[0]['shop_add2']   = "";
            $shop_data[0]['shop_add3']   = "";
            $shop_data[0]['opentime1']   = "";
            $shop_data[0]['closetime1']  = "";
            $shop_data[0]['opentime2']   = "";
            $shop_data[0]['closetime2']  = "";
            $shop_data[0]['shop_pw']     = "";
            $shop_data[0]['url_hp']      = "";
            $shop_data[0]['url_sns1']    = "";
            $shop_data[0]['url_sns2']    = "";
            $shop_data[0]['url_sns3']    = "";
            $shop_data[0]['url_sns4']    = "";
            $shop_data[0]['thumbnail1']  = "";
            $shop_data[0]['thumbnail2']  = "";
            $shop_data[0]['thumbnail3']  = "";
            $shop_data[0]['goods']       = "";
            $shop_data[0]['free_text']   = "";
            $shop_data[0]['paidmember']  = "";
            $shop_data[0]['point']  = "";
        }

        $this->set(compact('shop_data'));

        // お店の名前
        $shop_nm = $shop_data[0]['shop_nm'];
        $this->set(compact('shop_nm'));

        //  DBよりカテゴリマスタを取得する
        $ctgy = $common->prGetData('mst0014');
        $this->set(compact('ctgy'));

        //  DBよりエリアマスタを取得する
        $area = $common->prGetData("mst0015");
        $this->set(compact('area'));

        // 定休日を取得する
        $holiday = $common->prGetholidays();
        $this->set(compact('holiday'));

        // 会員を取得する
        $paidmembers = $common->prGetpaidmembers();
        $this->set(compact('paidmembers'));

        // 会員を取得する
        $point = $common->prGetpoint();
        $this->set(compact('point'));

        //K (2023/04)
        //barcode 区分------------------------------------------------
        $barcode_kbns = $common->barcodeList();
        $this->set(compact('barcode_kbns'));

        //画面からpostされたときのみ処理する 
        if ($this->getRequest()->is('post')) {

            $searchParam =  $this->getRequest()->getData();
            $this->set(compact('searchParam'));

            // 写真保存用のパスを設定する
            $path = CON_IMAGE . $shop_cd1;

            $myFiles = $this->request->getData('my_file');
            $myFiles_logo = $this->request->getData('logo');           //カードロゴ
            $pic_nm  = $common->prSavePic($path, $myFiles);
            $pic_nm_cardLogo = $common->saveCardLogo($cardLogoPath, $myFiles_logo);    //card logo

            $searchParam['thumbnail1'] = '';
            $searchParam['thumbnail2'] = '';
            $searchParam['thumbnail3'] = '';
            $searchParam['logo']       = "";    //店舗カード LOGO

            //------------------------------------------------THUMBNAIL the correct way
            if ($pic_nm[0] !== "" && $pic_nm[0]  !== null) {
                $j = 1;
                foreach ($pic_nm as $val) {
                    if ($shop_data[0]['thumbnail' . $j] !== "" && $shop_data[0]['thumbnail' . $j] !== null) { //if not empty, assign new file name and delete old file
                        $searchParam['thumbnail' . $j] = $val;
                        if (file_exists($path . '/' . $shop_data[0]['thumbnail' . $j])) {
                            unlink($path . '/' . $shop_data[0]['thumbnail' . $j]);
                        }
                    } else {
                        $searchParam['thumbnail' . $j] = $val;          //if EMPTY assign new file
                    }
                    $j++;
                }
            } else { //if pic_nm is empty
                $j = 1;
                foreach ($pic_nm as $val) {
                    $searchParam['thumbnail' . $j] = $shop_data[0]['thumbnail' . $j]; //assign current file to coupon
                }
            }
            //------------------------------------------------THUMBNAIL the correct way
            

            //----CARD LOGO K(2023/04)
            if ($pic_nm_cardLogo !== "" && $pic_nm_cardLogo !== null) {
                $searchParam['logo']       = $pic_nm_cardLogo;
                if (file_exists($path . '/' . $shop_data[0]['thumbnail' . $j])) {
                    unlink($path . '/' . $shop_data[0]['thumbnail' . $j]);
                }
            }else{
                $searchParam['logo']  = $shop_data[0]['logo']; 
            }



            $searchParam['insuser_cd']   = $shop_cd1;
            $searchParam['insdatetime']  = $shop_data[0]['insdatetime'];
            $searchParam['upduser_cd']   = $shop_cd1;
            $searchParam['updatetime']   = "now()";
            $searchParam['shop_cd']      = $shop_cd1;

            //make BLANK. 設計くそ悪い
            $searchParam['shop_group_cd']       = "";
            $searchParam['special_point_cd']    = "";
            $searchParam['card_image']          = "";
            $searchParam['bar_schar']           = "";


            //バーコード区分 SET コード -----------KARL
            //1:JAN13 2:JAN8 3:NW7 4:Code 39 5:Code 128
            $barcodeCODE = $common->convertBarcodeCode($searchParam['barcode_kbn']);
            $searchParam['barcode_kbn'] = $barcodeCODE;

            //重複電話番号チェック（新規）
            $shopPhone = $this->checkNumber($searchParam['shop_phone']); //check phone number K(2023/05)


            //geolocation 登録 K(2023/03)
            //-----------------------------------------------
            $add1 = $searchParam['shop_add1'];
            $add2 = $searchParam['shop_add2'];
            $add3 = $searchParam['shop_add3'];
            //-------------------------------------------------------------------------------------------------

            if (!$shop_cdChecker) { //if すでにあった場合


                //電話番号チェック
                if (!$shopPhone) {

                    $this->geolocationMake($shop_cd1, $add1, $add2, $add3, 1);
                    $common->prSavedata("mst0010", $searchParam); //<---- save ALL

                    //home 画面へパラメータを持って移動する                    
                    return $this->redirect(
                        [
                            'controller'  => '/Shoplist', 'action' => 'index', '?'      => [
                                'shop_cd'  => $shop_cd
                            ]
                        ]
                    );
                } else {

                    //同じだったら エラーを表示する
                    $alert = "<script type='text/javascript'>alert('この電話番号は既に登録済みです。');</script>";
                    echo $alert;
                }
            } else { //UPDATE geolocation


                $this->geolocationMake($shop_cd1, $add1, $add2, $add3, 0);
                $where = " shop_cd = '" . $shop_cd1 . "'";
                $common->prUpdateEditdata("mst0010", $searchParam, $where);

                //home 画面へパラメータを持って移動する                    
                return $this->redirect(
                    [
                        'controller'  => '/Shoplist', 'action' => 'index', '?'      => [
                            'shop_cd'  => $shop_cd
                        ]
                    ]
                );
            }
        }
    }

    /**
     * 電話番号あるかどうかチェック
     * 
     * K(2023/05)
     * @return user_phone
     */
    private function checkNumber($shop_phone)
    {

        $connection = ConnectionManager::get('default');

        $sql   = "";
        $sql   .= "SELECT ";
        $sql   .= " shop_phone ";
        $sql   .= "FROM ";
        $sql   .= "mst0010 ";
        $sql   .= "WHERE shop_phone = '" . $shop_phone . "' ";


        // SQLの実行
        $query = $connection->query($sql)->fetchAll('assoc');
        $this->set(compact('query'));

        return $query;
    }

    /**
     * update geolocation method.【 店舗の現在地　更新 】
     *　K(2023/04)
     * @return void
     */
    private function updateGeo($shop_cd, $longitude, $latitude, $fullShopAddr)
    {

        $connection = ConnectionManager::get('default');

        $sql    = "";
        $sql   .= " UPDATE geolocations ";
        $sql   .= " SET ";
        $sql   .= " longtitude  ='" . $longitude . "', ";
        $sql   .= " latitude    ='" . $latitude . "', ";
        $sql   .= " address     ='" . $fullShopAddr . "' ";

        $sql   .= " WHERE shop_cd = '" . $shop_cd . "' ";
        $sql   .= "  ";


        // SQLの実行
        $connection->execute($sql);
    }

    /**
     * Geolocation処理する
     *
     * K(2023/03)
     */
    private function geolocationMake($shop_cd, $add1, $add2, $add3, $insertChecker)
    {

        // 共通のComponentを呼び出す
        $common = new CommonComponent();


        $longitude = "";
        $latitude = "";

        $fullShopAddr = $add1 . $add2 . $add3;

        $url = "https://msearch.gsi.go.jp/address-search/AddressSearch?q=" . urlencode($fullShopAddr);

        //run the URL and get return JSON here

        // Use curl to retrieve the data from the URL
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($curl);
        curl_close($curl);

        // Parse the JSON response
        $response = json_decode($data);

        // Extract the latitude and longitude from the response
        $longitude = $response[0]->geometry->coordinates[0];
        $latitude = $response[0]->geometry->coordinates[1];


        //INSERT to GEOLOCATION テーブル
        if ($insertChecker == 1) {

            $common->insertLongLat($shop_cd, $longitude, $latitude, $fullShopAddr);
        } else {

            //UPDATE to GEOLOCATION テーブル
            $this->updateGeo($shop_cd, $longitude, $latitude, $fullShopAddr);
        }
    }
}
