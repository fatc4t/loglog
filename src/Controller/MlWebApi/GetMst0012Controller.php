<?php
    /**
     * @file      .loglogCouponAPI   
     * @author    SANGGI
     * @date      2023/01/13
     * @version   1.00
     * @note      Coupon Info get
     */

    namespace App\Controller\MlWebApi;

    use App\Controller\AppController;
    use Cake\Event\EventInterface;
    use Cake\Cache\Cache; 
    use Cake\Datasource\ConnectionManager;
    use App\Controller\Component\MlCommon\CommonComponent;


    class GetMst0012Controller extends AppController {
        
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
        public static function prGetCouponData($table=NULL,$user_cd=NULL,$lat=null, $long=null){

            $connection = ConnectionManager::get('default');
            // 条件
        
            $sql   = "";
            $sql   .= "select";
            $sql   .= " mst0012.shop_cd, "; 
            $sql   .= " mst0010.shop_add1, "; 
            $sql   .= " mst0010.shop_add2, "; 
            $sql   .= " mst0012.coupon_cd, ";    
            $sql   .= " mst0010.shop_nm, ";    
            $sql   .= " mst0010.category_cd, ";    
            $sql   .= " mst0012.coupon_goods, ";    
            $sql   .= " mst0012.effect_srt, ";    
            $sql   .= " mst0012.effect_end, ";    
            $sql   .= " mst0012.coupon_discount, ";    
            $sql   .= " mst0012.thumbnail1, ";    
            $sql   .= " mst0012.thumbnail2, ";    
            $sql   .= " mst0012.thumbnail3, ";    
            $sql   .= " mst0012.connect_kbn, ";    
            $sql   .= " mst0012.used, ";  
            $sql   .= " mst0012.color, ";
            $sql   .= " mst0012.visit_condition, ";     
            $sql   .= " (ABS(GEO.longtitude::FLOAT-ABS(".$long."))+ABS(GEO.latitude::FLOAT-ABS(".$lat."))) as Proximity";             
            $sql   .= " from ".$table;
            $sql   .= " left join mst0010 on mst0012.shop_cd = mst0010.shop_cd ";
            $sql   .= " left join geolocations GEO on GEO.shop_cd = mst0010.shop_cd";
            $sql   .= " where user_cd = "."'".$user_cd."'";
            //$sql   .= " and effect_end >= to_char(Now(),'YYYYMMDD') "; //-----old
            $sql   .= " and (effect_end >= to_char(Now(),'YYYYMMDD') and to_char(Now(),'YYYYMMDD') >= effect_srt) "; 
            $sql   .= " order by proximity asc,";
            $sql   .= " used ASC, ";  
            $sql   .= " effect_srt DESC ";  
            

            
            // SQLの実行
            $query = $connection->query($sql)->fetchAll('assoc');
           
            return $query;
        }

        /*
         *  count 来店数回
         */
        public static function trnRaitenCheck($user_cd=NULL,$shop_cd=null,$dateStart=null,$dateEnd=null){

            $connection = ConnectionManager::get('default');
            // 条件
        
            $sql   = "";
            $sql   .= " select ";
            $sql   .= " count(shop_cd) from trn0012 where shop_cd='".$shop_cd."' and user_cd='".$user_cd."' "; 
            $sql   .= " and to_char(raiten_time,'YYYYMMDD') >= '".$dateStart."' and to_char(raiten_time,'YYYYMMDD') <= '".$dateEnd."' ";

            //print_r($sql);exit;
            // SQLの実行
            $query = $connection->query($sql)->fetchAll('assoc');
           
            return $query;
        }


        public function index($user_cd = null, $long=null, $lat=null) {
            
            // 共通のComponentを呼び出す
            $common = new CommonComponent();

            $user_cd = $this->request->getQuery('user_cd');
            $long = $this->request->getQuery('long');
            $lat = $this->request->getQuery('lat');
            
            $couponData = '';
            // Couponデータ
            $couponData =  $this -> prGetCouponData('mst0012',$user_cd,$lat,$long);
            

            $EffectS ='';
            $EffectE ='';
            $visitC = '';
            $shop_cd = '';



            //$key = index
            foreach($couponData as $key => $cpData){
                $visitC     =$cpData['visit_condition'];
                $EffectS    =$cpData['effect_srt'];
                $EffectE    =$cpData['effect_end'];
                $shop_cd    =$cpData['shop_cd'];

               

                if($visitC !== '' || $visitC !== NULL){
                    //trnRaitenCheck
                    $countCheck  = $this -> trnRaitenCheck($user_cd, $shop_cd,$EffectS,$EffectE);

                    //if  count < visitC -> HIDE
                    //charvar -> intに変更して
                    if((int)$countCheck[0]['count'] < (int)$visitC){
                        //delete record
                        unset($couponData[$key]);
                       
                    }
                    //if  count >= visitC -> そのまま
                    
                }
            }


            foreach($couponData as $data){
                // //arrange coupon here データ整理する
                $aryAddRow =  array(
                    'shop_cd'          => $data['shop_cd'],
                    'shop_add1'        => $data['shop_add1'],    
                    'shop_add2'        => $data['shop_add2'], 
                    'coupon_cd'        => $data['coupon_cd'],  
                    'shop_nm'          => $data['shop_nm'], 
                    'category_cd'      => $data['category_cd'], 
                    'coupon_goods'     => $data['coupon_goods'],   
                    'effect_srt'       => $data['effect_srt'],   
                    'effect_end'       => $data['effect_end'],
                    'coupon_discount'  => $data['coupon_discount'],
                    'thumbnail1'       => $data['thumbnail1'],
                    'thumbnail2'       => $data['thumbnail2'],
                    'thumbnail3'       => $data['thumbnail3'],
                    'connect_kbn'      => $data['connect_kbn'],
                    'used'             => $data['used'],
                    'color'            => $data['color'],
                    'visit_condition'  => $data['visit_condition'], 
                    'proximity'        => $data['proximity'],    //lat long
                );
                $json_array[] = $aryAddRow;
            }
           
        
            //print_r($couponData[0]);exit;
            $this->set(compact('json_array'));

            // JSON で出力
            $this->viewBuilder()
                ->setClassName('Json')
                ->setOption('serialize', 'json_array');
        }
    }
?>
