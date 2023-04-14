<?php
    
namespace App\Controller\MlEdit;
    
use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\Cache\Cache; 
use Cake\Filesystem\Folder;
use Cake\Filesystem\File;
use App\Controller\Component\MlCommon\CommonComponent;

class EditController extends AppController {
    
    public function beforeFilter(EventInterface $event)
    {
        
        parent::beforeFilter($event);
            
        // '_cake_core_' のキャッシュを削除
        Cache::clear('_cake_core_');
            
        // 'default' のキャッシュを削除
        Cache::clear();
            
        // ajaxでPOSTするFunctionのみ許可
        $this->Security->setConfig('unlockedActions', ['ajaxShow','index']);
            
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
        public function index() {
            
            // タイトル用意
            $this->set('title', '登録内容変更');
            
            // 共通のComponentを呼び出す
            $common = new CommonComponent();
            
            $path        = "";
            $pic_nm      = [];
            $pointChecker = "";
            //$pic_nm_Pro  = [];
            
            $ERR_PHONE   = "";
            $this->set(compact('ERR_PHONE'));
            
            // urlから店舗コードを取得する
            $shop_cd = $this->request->getQuery('shop_cd');
            
            //  DBよりエリアマスタを取得する
            $area = $common->prGetData("mst0015");
            $this->set(compact('area'));
            
            // 定休日を取得する
            $holiday = $common->prGetholidays();
            $this->set(compact('holiday'));

            //K (2023/03)
             //fucking POINTSSSSSSSS--------------------------------1 or 0
             $point = $common->prGetpoint();
             $this->set(compact('point'));

            //K (2023/04)
            //barcode 区分------------------------------------------------
            $barcodeType = $common->barcodeList();
            $this->set(compact('barcodeType'));

            // DBより店舗の情報を取得する
            $shop_dataW = "shop_cd = '".$shop_cd."'";
            $shop_dataO = "shop_cd";
            $shop_data  = $common->prGetData("mst0010",$shop_dataW,$shop_dataO);
            $this->set(compact('shop_data'));
            if(!$shop_data){
                echo '【店舗情報が見つかりません。URLに間違いないか確認してください。】';
                exit();
            }
            // お店の名前
            $shop_nm = $shop_data[0]['shop_nm'];
            $this->set(compact('shop_nm'));

           

            if ($this->getRequest()->is('post')) {
                
                $searchParam =  $this->getRequest()->getData();

                
                $this->set(compact('searchParam')); 

                
                    
                // 写真保存用のパスを設定する           
                $path = CON_IMAGE.$shop_cd;                 //('../webroot/img/Home/')
                $path2 = CON_IMAGE_Card.$shop_cd;           //('../webroot/img/Card/')
                $cardLogoPath = CON_IMAGE_Logo.$shop_cd;    //('../webroot/img/CardLogo/')
                

                $myFiles = $this->request->getData('my_file');
                $myFiles_card = $this->request->getData('my_card');             //カード画像
                $myFiles_logo = $this->request->getData('logo');           //カードロゴ
                

                $pic_nm  = $common->prSavePic($path,$myFiles);                              //thumbnail
                $pic_nm_card = $common->prSaveCardPic($path2, $myFiles_card);               //card picture
                $pic_nm_cardLogo = $common->saveCardLogo($cardLogoPath, $myFiles_logo);    //card logo

                $searchParam['thumbnail1'] = "";
                $searchParam['thumbnail2'] = "";
                $searchParam['thumbnail3'] = "";
                $searchParam['card_image'] = "";    //point card image
                $searchParam['logo']       = "";    //店舗カード LOGO

                
                //if no PIC = string(0) ""

                //------------------------------------------------THUMBNAIL
                if($pic_nm[0] !== "" && $pic_nm[0]  !== null){
                    $j=1;
                    foreach($pic_nm as $val){
                        if($shop_data[0]['thumbnail'.$j] !== "" && $shop_data[0]['thumbnail'.$j] !== null){
                            $searchParam['thumbnail'.$j] = $val;
                            unlink($path.'/'.$shop_data[0]['thumbnail'.$j]);
                        }else{
                            $searchParam['thumbnail'.$j] = $shop_data[0]['thumbnail'.$j];
                        }
                        $j++;
                    }
                }
                //---------card image (fucking clean)
                if ($pic_nm_card !== "" && $pic_nm_card !== null) {
                    if ($shop_data[0]['card_image'] !== "") {
                        unlink($path2 . '/' . $shop_data[0]['card_image']);
                        $searchParam['card_image'] = $pic_nm_card;
                    } else {
                        $searchParam['card_image'] =$pic_nm_card;
                    }
                }else{
                    //pic_nm_card is empty but DBデータがある
                    $searchParam['card_image'] = $shop_data[0]['card_image']; //上書きしないように overwrite NO
                }
                //---------card image


                //-----------------------------------------------------------------card LOGO (fucking clean)
                if ($pic_nm_cardLogo !== "" && $pic_nm_cardLogo !== null) {
                    if ($shop_data[0]['logo'] !== "") {
                        $searchParam['logo'] = $pic_nm_cardLogo;
                        unlink($path2 . '/' . $shop_data[0]['logo']);
                    } else {
                        $searchParam['logo'] =$pic_nm_cardLogo;
                    }
                }else{
                    //pic_nm_card is empty but DBデータがある
                    $searchParam['logo'] = $shop_data[0]['logo']; //上書きしないように overwrite NO
                }
                //-----------------------------------------------------------------card LOGO
                
            
                // 画面上にないパラメータを準備する
                $searchParam['insuser_cd']   = $shop_cd;
                $searchParam['insdatetime']  = "now()";
                $searchParam['upduser_cd']   = $shop_cd;
                $searchParam['updatetime']   = "now()";
                $searchParam['shop_cd']      = $shop_cd;
                $searchParam['category_cd']  = $shop_data[0]['category_cd'];
                $searchParam['paidmember']   = $shop_data[0]['paidmember'];

                //$searchParam['point']   = $shop_data[0]['point'];

                $searchParam['shop_group_cd']   = $shop_data[0]['shop_group_cd'];  //  from Edit 画面


                //-----------SPECIALPOINT あれば------------------------------------------
                $special_pointCD = $this->request->getData('special_point_cd');
                $pointChecker = $common->prSpecialPointCheck("mst0010",$searchParam);
                $dbPointChecker = $shop_data[0]['special_point_cd'];


               
                if($special_pointCD !== NULL){  //BOX checked
                    if($dbPointChecker == $special_pointCD){

                        //if POINTCHECKER == Special POINT そのまま
                        $searchParam['special_point_cd']   = $shop_data[0]['special_point_cd'];
                        
                    }else{
                        //if POINTCHECKER !== Special POINT 
                        //do this
                        $searchParam['special_point_cd']  = $special_pointCD;
                    }
                }else{ //BOX unchecked
                    
                    //if blank/unchecked そのまま
                    $searchParam['special_point_cd']   = $shop_data[0]['special_point_cd']; 

                }
               

                //バーコード区分 SET コード -----------KARL
                //1:JAN13 2:JAN8 3:NW7 4:Code 39 5:Code 128
                $barcodeCODE = $common->convertBarcodeCode($searchParam['barcodeType']);
                $searchParam['barcodeType'] = $barcodeCODE;
                
                

                //KARL UPDATE
                $where = " shop_cd = '".$shop_cd."'";
                $common->prUpdateEditdata("mst0010",$searchParam, $where);

                //　登録する
                //$common->prSavedata("mst0010",$searchParam);
                //　$common->prUpdSend();
                
                //home 画面へパラメータを持って移動する
                return $this->redirect(
                 ['controller'  => '../MlHome/Home'
                     , 'action' => 'index'
                     , '?'      => [
                     'shop_cd' => $shop_cd]
                 ]);
            }
        }
    }
