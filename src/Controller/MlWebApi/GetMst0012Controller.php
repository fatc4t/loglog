<?php

/**
 * @file      .loglogCouponAPI   
 * @author    SANGGI
 * @date      2023/01/13
 * @version   1.00
 * @note      Coupon Info get
 * 
 * edited K(2023/02)
 * edited K(2023/03) - geolocation
 * edited K(2023/04) - new coupon table, shopimage 追加
 * edited K(2023/05) - LIKED機能乗せた、geolocationない場合はcoupon LOAD
 */

namespace App\Controller\MlWebApi;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\Cache\Cache;
use Cake\Datasource\ConnectionManager;
use App\Controller\Component\MlCommon\CommonComponent;


class GetMst0012Controller extends AppController
{

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
        $this->Security->setConfig('unlockedActions', ['ajaxShow', 'index']);
    }

    /*
         *
         */
    public static function prGetCouponData($table, $user_cd, $lat = null, $long = null)
    {

        $connection = ConnectionManager::get('default');

        $sql   = "";

        //if 軽度  YES or 緯度　YES

        if (($lat && $lat !== "") && ($long  && $long !== "")) {

            $sql   .= "
            SELECT 
            COALESCE(cpnTrn.used, 0) as used, 
            CASE  
                WHEN CL.unique_coupon_cd IS NOT NULL THEN 1 
                ELSE 0
            END AS liked, 
            coupons.unique_coupon_cd as coupon_cd, 
            coupons.shop_cd, mst0010.shop_add1, mst0010.shop_add2, 
            mst0010.shop_nm, mst0010.category_cd, 
            coupons.coupon_goods, coupons.effect_srt, coupons.effect_end, coupons.coupon_discount, 
            mst0010.thumbnail1 as shopimage, coupons.thumbnail1, coupons.thumbnail2, coupons.thumbnail3, coupons.connect_kbn, 
            coupons.color, 
            coupons.visit_condition, 
            (ABS(GEO.longtitude::FLOAT-ABS(" . $long . "))+ABS(GEO.latitude::FLOAT-ABS(" . $lat . "))) as proximity 
            from " . $table . " 
            left join (select unique_coupon_cd, used from coupons_used where user_cd='" . $user_cd . "') cpnTrn 
            on cpnTrn.unique_coupon_cd = coupons.unique_coupon_cd
            left join mst0010 on coupons.shop_cd = mst0010.shop_cd 
            left join geolocations GEO on GEO.shop_cd = mst0010.shop_cd 
            left join (select unique_coupon_cd , user_cd from coupons_liked where user_cd='" . $user_cd . "' ) CL on CL.unique_coupon_cd = coupons.unique_coupon_cd
            WHERE 
             (effect_end >= to_char(Now(),'YYYYMMDD') 
            and to_char(Now(),'YYYYMMDD') >= effect_srt) 
            order by cpnTrn.used desc, liked desc,  proximity asc, effect_srt DESC";
        } else {

            //if 軽度　NO or 緯度　NO
            //proximity set to 0

            $sql   .= "
            SELECT 
            COALESCE(cpnTrn.used, 0) as used, 
            CASE  
                WHEN CL.unique_coupon_cd IS NOT NULL THEN 1 
                ELSE 0
            END AS liked, 
            coupons.unique_coupon_cd as coupon_cd, 
            coupons.shop_cd, mst0010.shop_add1, mst0010.shop_add2, 
            mst0010.shop_nm, mst0010.category_cd, 
            coupons.coupon_goods, coupons.effect_srt, coupons.effect_end, coupons.coupon_discount, 
            mst0010.thumbnail1 as shopimage, coupons.thumbnail1, coupons.thumbnail2, coupons.thumbnail3, coupons.connect_kbn, 
            coupons.color, 
            coupons.visit_condition,
            '0' as proximity  
            from " . $table . " 
            left join (select unique_coupon_cd, used from coupons_used where user_cd='" . $user_cd . "') cpnTrn 
            on cpnTrn.unique_coupon_cd = coupons.unique_coupon_cd
            left join mst0010 on coupons.shop_cd = mst0010.shop_cd 
            left join geolocations GEO on GEO.shop_cd = mst0010.shop_cd 
            left join (select unique_coupon_cd , user_cd from coupons_liked where user_cd='" . $user_cd . "' ) CL on CL.unique_coupon_cd = coupons.unique_coupon_cd
            WHERE 
             (effect_end >= to_char(Now(),'YYYYMMDD') 
            and to_char(Now(),'YYYYMMDD') >= effect_srt) 
            order by cpnTrn.used desc, liked desc, proximity asc, effect_srt DESC";
        }



        // SQLの実行
        $query = $connection->query($sql)->fetchAll('assoc');

        return $query;
    }

    /*
         *  count 来店数回
         */
    public static function trnRaitenCheck($user_cd = NULL, $shop_cd = null, $dateStart = null, $dateEnd = null)
    {

        $connection = ConnectionManager::get('default');
        // 条件

        $sql   = "";
        $sql   .= " select ";
        $sql   .= " count(shop_cd) from trn0012 where shop_cd='" . $shop_cd . "' and user_cd='" . $user_cd . "' ";
        $sql   .= " and to_char(raiten_time,'YYYYMMDD') >= '" . $dateStart . "' and to_char(raiten_time,'YYYYMMDD') <= '" . $dateEnd . "' ";

        //print_r($sql);exit;
        // SQLの実行
        $query = $connection->query($sql)->fetchAll('assoc');

        return $query;
    }


    public function index($user_cd = null, $long = null, $lat = null)
    {

        // 共通のComponentを呼び出す
        $common = new CommonComponent();

        $user_cd = $this->request->getQuery('user_cd');
        $long = $this->request->getQuery('long');
        $lat = $this->request->getQuery('lat');

        $couponData = '';
        // Couponデータ
        $couponData =  $this->prGetCouponData('coupons', $user_cd, $lat, $long);


        $EffectS = '';
        $EffectE = '';
        $visitC = '';
        $shop_cd = '';



        //$key = index
        foreach ($couponData as $key => $cpData) {
            $visitC     = $cpData['visit_condition'];
            $EffectS    = $cpData['effect_srt'];
            $EffectE    = $cpData['effect_end'];
            $shop_cd    = $cpData['shop_cd'];



            if ($visitC !== '' || $visitC !== NULL) {
                //trnRaitenCheck
                $countCheck  = $this->trnRaitenCheck($user_cd, $shop_cd, $EffectS, $EffectE);

                //if  count < visitC -> HIDE
                //charvar -> intに変更して
                if ((int)$countCheck[0]['count'] < (int)$visitC) {
                    //delete record
                    unset($couponData[$key]);
                }
                //if  count >= visitC -> そのまま

            }
        }

        foreach ($couponData as $data) {
            //arrange coupon here データ整理する
            $aryAddRow =  array(
                'shop_cd'          => $data['shop_cd'],
                'shop_add1'        => $data['shop_add1'],
                'shop_add2'        => $data['shop_add2'],
                'coupon_cd'        => strval($data['coupon_cd']),  //integer in coupons table
                'shop_nm'          => $data['shop_nm'],
                'category_cd'      => $data['category_cd'],
                'coupon_goods'     => $data['coupon_goods'],
                'effect_srt'       => $data['effect_srt'],
                'effect_end'       => $data['effect_end'],
                'coupon_discount'  => $data['coupon_discount'],
                'shopimage'        => $data['shopimage'],
                'thumbnail1'       => strval($data['coupon_cd']) . '/' . $data['thumbnail1'], //added coupon_cd FOlder
                'thumbnail2'       => $data['thumbnail2'],
                'thumbnail3'       => $data['thumbnail3'],
                'connect_kbn'      => $data['connect_kbn'],
                'used'             => strval($data['used']),    //integer in coupons_used table
                'color'            => $data['color'],
                'visit_condition'  => $data['visit_condition'],
                'proximity'        => $data['proximity'],    //lat long
                'liked'            => $data['liked'],  //sanggi added

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
