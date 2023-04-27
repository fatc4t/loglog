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
        public static function updateCouponUsed($table=NULL,$user_cd=NULL,$shop_cd=NULL,$coupon_cd=NULL){

            $connection = ConnectionManager::get('default');
            // 条件
        
            // $sql   = "";
            // $sql   .= "update";
            // $sql   .= " mst0012 set used = '1' "; 
            // $sql   .= " where user_cd = '".$user_cd."'";    
            // $sql   .= " and shop_cd = '".$shop_cd."'";    
            // $sql   .= " and coupon_cd = '".$coupon_cd."'";     


            $sql   = "";
            $sql   .= "UPDATE ";
            $sql   .= " ".$table." SET used = 1 "; 
            $sql   .= " WHERE unique_coupon_cd = ".$coupon_cd." ";    
            $sql   .= " and user_cd = '".$user_cd."'";    
 
            
            // SQLの実行
            $connection->execute($sql);
            $connection->commit();
        }

        public static function checkData ($table=NULL,$user_cd=NULL,$shop_cd=NULL,$coupon_cd=NULL){

            $connection = ConnectionManager::get('default');
            // 条件
        
            $sql   = "";
            $sql   .= " SELECT used FROM ".$table. " ";
            $sql   .= " WHERE unique_coupon_cd = '".$coupon_cd."' ";
            $sql   .= " AND user_cd = '".$user_cd."'";    
    
     

            // SQLの実行
            $query = $connection->query($sql)->fetchAll('assoc');
            return $query;
        }

        public function index($user_cd = null) {
            
            // 共通のComponentを呼び出す
            $common = new CommonComponent();

            $user_cd = $this->request->getQuery('user_cd');
            $shop_cd = $this->request->getQuery('shop_cd');
            $coupon_cd = $this->request->getQuery('coupon_cd');
            
            // Couponデータ
            $this -> updateCouponUsed('coupons_used',$user_cd,$shop_cd,$coupon_cd); //update USED=1 mst0012
            $result =  $this -> checkData('coupons_used',$user_cd,$shop_cd,$coupon_cd);          // return USED only 1 or 0
            
            
            
            $this->set(compact('result'));
            // JSON で出力
            $this->viewBuilder()
                ->setClassName('Json')
                ->setOption('serialize', 'result');
        }
    }
?>
