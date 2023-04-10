<?php

namespace App\Controller\MlMobile;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\Cache\Cache; 
use Cake\Filesystem\Folder;
use Cake\Filesystem\File;
use App\Controller\Component\MlCommon\CommonComponent;

class CpnController extends AppController
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
    public function index($user_cd=NULL) {
        
        // 共通のComponentを呼び出す
        $common = new CommonComponent();
        
        // urlからクーポンコードを取得する
        $user_cd = $this->request->getQuery('user_cd');
        $coupon_Cd = "";
        $shop_cd   = "";
        
        // 日付を取得
        $today = $common->prGetToday();
        
        // 画面からpostされたときのみ処理する
        if ($this->getRequest()->is('post')) {
            
            $searchParam =  $this->getRequest()->getData();
            $this->set(compact('searchParam'));
            
            // 使用済み更新
            $where = " shop_cd='".$searchParam['shop_cd']."' and coupon_cd='".$searchParam['coupon_cd']."' and user_cd='".$user_cd."'";
            $common->prUpdSend("mst0012",$where,NULL,'1');

            return $this->redirect(
                ['controller'      => '../MlMobile/Cpn'
                    , 'action'     => 'index'
                    , '?'          => [
                    'user_cd'     => $user_cd]
            ]);
            
        }
        
        // 登録されてる店舗情報を取得
        $where  = "";
        $where .= " mst0012.shop_cd = mst0010.shop_cd ";
        $where .= "and mst0012.user_cd='".$user_cd."'";
        $where .= " and mst0012.effect_srt <= '".$today."' and mst0012.effect_end >= '".$today."'";
        $order  = "";
        $order .= "used";
        
        
        $coupon_data = $common->prGetDatajoin("mst0010 , mst0012 ",$where,$order);
        $this->set(compact('coupon_data'));
        
        // 使用したクーポンも含めて過去に配布されたクーポンが一枚もないか確認する
        if (!$coupon_data){
            $where1 = "";
            $where1 .= " mst0012.user_cd='".$user_cd."'";
            $coupon_data1 = $common->prGetData("mst0012",$where1);
            $coupon_data  = $coupon_data1;
                   
            // 過去のクーポンを新規ユーザー用に再登録する
            if(!$coupon_data1){
                $cpn_dataW    = "mst0012.effect_srt <= '".$today."' and mst0012.effect_end >= '".$today."'";
                $cpn_dataW   .= "and mst0012.prefecture is NULL and mst0012.age is NULL ";
                $cpn_dataW   .= "and mst0012.gender is NULL and mst0012.birthday is NULL and mst0012.rank is NULL";
                $distinctM    = " shop_cd,coupon_cd";
                $coupon_data2 = $common->prGetDataDistinct("mst0012",$cpn_dataW,NULL,NULL,$distinctM);
                
                foreach($coupon_data2 as $val){
                    $searchParam1['insuser_cd']      = $val['shop_cd'];
                    $searchParam1['insdatetime']     = "now()";
                    $searchParam1['upduser_cd']      = $val['shop_cd'];
                    $searchParam1['updatetime']      = "now()";
                    $searchParam1['shop_cd']         = $val['shop_cd'];
                    $searchParam1['coupon_cd']       = $val['coupon_cd'];
                    $searchParam1['coupon_goods']    = $val['coupon_goods'];
                    $searchParam1['effect_srt']      = $val['effect_srt'];
                    $searchParam1['effect_end']      = $val['effect_end'];
                    $searchParam1['coupon_discount'] = $val['coupon_discount'];
                    $searchParam1['thumbnail1']      = $val['thumbnail1'];
                    $searchParam1['thumbnail2']      = $val['thumbnail2'];
                    $searchParam1['thumbnail3']      = $val['thumbnail3'];
                    $searchParam1['user_cd']         = $user_cd;
                    $searchParam1['connect_kbn']     = '1';
                    $searchParam1['used']            = '0';
                    $searchParam1['background']      = $val['background'];
                    $searchParam1['color']           = $val['color'];
                    $searchParam1['prefecture']      = NULL;
                    $searchParam1['age']             = NULL;
                    $searchParam1['gender']          = NULL;
                    $searchParam1['birthday']        = NULL;
                    $searchParam1['rank']            = NULL;
                    
                    //　登録する
                    $common->prSavedata("mst0012",$searchParam1);  
                }
                return $this->redirect(
                ['controller'      => '../MlMobile/Cpn'
                    , 'action'     => 'index'
                    , '?'          => [
                    'user_cd'     => $user_cd]
                ]);
            }
        }
    }
    

}
?>