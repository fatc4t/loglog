<?php
    /**
     * @file      .loglogCouponAPI   
     * @author    SANGGI
     * @date      2023/01/13
     * @version   1.00
     * @note      Coupon Info get + SLider -> update coupon to USED
     * 
     * edited K(2023/04) - new coupons/coupons_used table
     */

    namespace App\Controller\MlWebApi;

    use App\Controller\AppController;
    use Cake\Event\EventInterface;
    use Cake\Cache\Cache; 
    use Cake\Datasource\ConnectionManager;
    use App\Controller\Component\MlCommon\CommonComponent;
    use Laminas\Diactoros\Response;


    class PostMst0012Controller extends AppController {
        
        /*
        * 
        *
        */
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
        
        /*
         *
         */
        public static function updateCouponUsed($table=NULL,$user_cd=NULL,$coupon_cd=NULL){

            $connection = ConnectionManager::get('default');   

            //unique_coupon_cd(coupon_cd), updatetime, user_cd, used
            $sql   = "";
            $sql   .= "INSERT INTO  ";
            $sql   .= " ".$table." (unique_coupon_cd, updatetime, user_cd, used ) "; 
            $sql   .= " VALUES (".$coupon_cd.", now(), '".$user_cd."' , 1)";    
   
 
            
            // SQLの実行
            $connection->execute($sql);
            $connection->commit();

            return '1'; //return to アプリ側 
        }



        public function index($user_cd = null) {
            
            // 共通のComponentを呼び出す
            $common = new CommonComponent();

            $user_cd = $this->request->getQuery('user_cd');
            $shop_cd = $this->request->getQuery('shop_cd');
            $coupon_cd = $this->request->getQuery('coupon_cd');
            
            // Couponデータ
            $result = $this -> updateCouponUsed('coupons_used',$user_cd,$coupon_cd); //INSERT INTO coupons_used // Return '1' for phone
           
            
            $response = ['used' => $result];
            
            $this->set(compact('result'));
            // JSON で出力
            $this->viewBuilder()
                ->setClassName('Json')
                ->setOption('serialize', 'result');
        }
    }
?>
