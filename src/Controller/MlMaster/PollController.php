<?php
    
namespace App\Controller\MlMaster;
    
use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\Cache\Cache; 
use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\Folder;
use Cake\Filesystem\File;
use App\Controller\Component\MlCommon\CommonComponent;

class PollController extends AppController {
    
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
    
    public function index($shop_cd = NULL) {

        $this->set('title', 'アンケート');

        // 共通のComponentを呼び出す
        $common = new CommonComponent();
        
        // urlから店舗コードを取得する
        $shop_cd = $this->request->getQuery('shop_cd');

        // DBより店舗情報を取得
        $shop_dataW = "shop_cd = '".$shop_cd."'";
        $shop_dataO = "shop_cd ";
        $shop_data  = $common->prGetData("mst0010",$shop_dataW,$shop_dataO);
        $this->set(compact('shop_data'));
        if(!$shop_data){
            echo '【店舗情報が見つかりません。URLに間違いないか確認してください。】';
            exit();
        }
        // お店の名前
        $shop_nm = $shop_data[0]['shop_nm'];
        $this->set(compact('shop_nm'));
        
        $ans1  = "";
        $ans2  = "";
        $ans3  = "";
        $ans4  = "";
        $ans5  = "";
        $bigans  = "";
        $poll_data = [];
        // $rank_cd1  = "";
        // $rank_nm1  = "";
        // $rank_data = [];
        $whereR    = "";
        $order = " shop_cd";

        //--------------------KARL
        $poll_data = []; 
        
        //画面からpostされたときのみ処理する 
        if ($this->getRequest()->is('post')) {

            $searchParam =  $this->getRequest()->getData();
            $this->set(compact('searchParam'));

            //-----------------------------KARL(inside POST)
           
            if($searchParam['btn_click_name'] == CON_SAVE_IN3){
                $ans1  = $searchParam['ans1'];
                $ans2  = $searchParam['ans2'];
                $ans3  = $searchParam['ans3'];
                $ans4  = $searchParam['ans4'];
                $ans5  = $searchParam['ans5'];
                $bigans  = $searchParam['bigAns'];

                //prSavedata($table=NULL,$searchParam=NULL)a
                $this->$common->prSavedata("polls",$searchParam);
            }
       }
       
       $whereR .= " shop_cd='".$shop_cd."'";
       $poll_data   = $common->prGetData("polls",$whereR,$order);

        // DBより顧客ランク情報を取得
        //$rank_data   = $common->prGetData("mst0016",$whereR,$order);
        //$this->set(compact('rank_data')); //sql data
        //$this->set(compact('shop_cd1'));
        //$this->set(compact('rank_cd1'));
        //$this->set(compact('rank_nm1'));

       $this->set(compact('poll_data'));

       
    }
}