<?php

namespace App\Controller\MlMobile;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\Cache\Cache; 
use Cake\Filesystem\Folder;
use Cake\Filesystem\File;
use App\Controller\Component\MlCommon\CommonComponent;

class MapshopController extends AppController 
{
    
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

    public function index($shop_cd=NULL) {

        // 共通のComponentを呼び出す
        $common = new CommonComponent();
        
        // urlからクーポンコードを取得する
        $shop_cd = $this->request->getQuery('shop_cd');
        $user_cd = $this->request->getQuery('user_cd');
        
        // 登録されてる店舗情報を取得
        $where = "";
        $where .= "shop_cd = '".$shop_cd."'";
        
        $shop_data = $common->prGetData("mst0010",$where);
        $this->set(compact('shop_data'));
        
        // 来店履歴を取得
        $whereR  = "shop_cd = '".$shop_cd."'";
        $whereR .= "and user_cd = '".$user_cd."'";
        $orderR  = "raiten_time desc";
        $limitR  = " 1 ";

        $raiten = $common->prGetDataDistinct("trn0012",$whereR,$orderR,$limitR);
        $this->set(compact('raiten'));



        //coupon 表示する
        //---------------------------------

        $whereCpn  = "shop_cd = '".$shop_cd."' AND   (effect_end >= to_char(Now(),'YYYYMMDD') AND to_char(Now(),'YYYYMMDD') >= effect_srt) ";
        $cpnData = $common->prGetData("coupons",$whereCpn);

        $visitC =''; //来店条件 counter

        foreach($cpnData as $key => $data){
            $visitC     =$data['visit_condition'];
             

            //USED チェック
            $usedCheck = $common->couponCheckused('coupons_used', $data['unique_coupon_cd']);

            if($usedCheck){
                unset($cpnData[$key]);
            }


            if($visitC !== '' || $visitC !== NULL){
                //trnRaitenCheck
                $countCheck  = $common->trnRaitenCheck($user_cd, $shop_cd,$data['effect_srt'],$data['effect_end']);

                //if  count < visitC -> HIDE
                //charvar -> intに変更して
                if((int)$countCheck[0]['count'] < (int)$visitC){
                    //delete record
                    unset($cpnData[$key]);
                   
                }
                //if  count >= visitC -> そのまま
            }


            
        }
        $this->set(compact('cpnData')); //VIEWにCOUPONを投げる

       $searchParam ="";

        //クーポン使用時
        if ($this->getRequest()->is('post')) { exit;

            //get page data
            $searchParam =  $this->getRequest()->getData();
            $this->set(compact('searchParam')); 

            $unique_coupon_cd = $searchParam['unique_coupon_cd'];


           //coupons_usedに　INSERTする
           $common->insertCouponUsed('coupons_used',$user_cd,$unique_coupon_cd);

            return $this->redirect(
                ['controller'  => '../MlMobile/Mapshop'
                    , 'action' => 'index'
                    , '?'      => [
                    'shop_cd'  => $shop_cd,
                    'user_cd'  => $user_cd
                    
                ]]
            );

        }


    
    }

}
?>