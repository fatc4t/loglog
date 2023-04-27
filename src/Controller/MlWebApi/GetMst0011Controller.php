<?php

/**
 * @file      .loglog顧客情報API   
 * @author    crmbhattarai, K(2023/03),(2023/04)
 * @date      2022/07/23
 * @version   1.00
 * @note      店舗顧客を取得
 * 
 * 
 * edited K(2023/04) 過去のmessage and coupon INSERT(new coupon table)
 */

namespace App\Controller\MlWebApi;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\Cache\Cache;
use Cake\Datasource\ConnectionManager;
use App\Controller\Component\MlCommon\CommonComponent;

class GetMst0011Controller extends AppController
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
    public function index()
    {
        // 共通のComponentを呼び出す
        $common = new CommonComponent();

        // パラメータを取得
        $body = $this->request->getQuery('param');



        //　パラメータなしの時はエラー
        if (is_null($body)) {
            # error データが無い
            http_response_code(500);
            echo "no data (JSON)";
            exit();
        }
        //　jsonに変換
        $json = json_decode($body, true);
        // jsonじゃなかったらエラー
        if (is_null($json)) {
            # error JSONをデコードできない
            http_response_code(500);
            echo "JSON error";
            exit();
        }

        

        // データがある時
        if ($json) {

            //電話番号に重複がないか調べる
            $where  = "";
            $where .= " mst0011.user_phone     = '" . $json[0]['user_phone'] . "' ";

            $user_data = $common->prGetData("mst0011", $where);
            $this->set(compact('user_data'));

            //電話番号に重複があった時          
            if ($user_data) {
                // メッセージ表示テスト用
                $json_array[] =  "already_have";
            } else {


                foreach ($json as $val) {

                    $searchParam = [];
                    $searchParam['insdatetime'] = 'now() ';
                    $searchParam['updatetime']  = 'now() ';
                    $searchParam['user_nm']  = $val['user_nm'];
                    //user_cd シーケンスがあるのでこれはいらないわけだ
                    $searchParam['user_pw']     = $val['user_pw'];
                    $searchParam['user_phone']  = $val['user_phone'];
                    $searchParam['connect_kbn'] = '0';


                    //save to Database --- DBに保存する
                    $user_cdSequenceArr = $this->saveRegistered('mst0011', $searchParam); //SEQUENCE の現在値  //------check RETURN 11111111

                    $user_cdSequence = $user_cdSequenceArr[0]['user_cd'];

                    // $user_cdSequence = '77777777'; //for testing only
                    
                    //=============================================MESSAGES=======================================================
                    //get 過去 rooms
                    $shopsList = $this->getDistinctShop(); //roomsから　shop_cdのリスト
                    //get 過去 messages
                    $msgList = $this->getDistinctMessages(); //messsagesから　content, datesent

                    //INSERT 過去 room_id + user_id(new)
                    foreach ($shopsList as $val) {
    
                        //room_id, shop_cd, user_cd
                       $this->makeRooms($val['shop_cd'], $user_cdSequence);
                    }
                
                    //INSERT 過去 content,shop_cd, sender, user_id(new) 
                    foreach ($msgList as $val) { //4 results)(3A, 1B)
                        
                        //get room_id
                        $room_id = $this->getRoomId($val['shop_cd'], $user_cdSequence );

                        //room_id, user_cd, shop_cd, datesent, content, seen, sender
                        $this->makeMessages(
                            $room_id[0]['room_id'],
                            $user_cdSequence, 
                            $val['shop_cd'], 
                            $val['datesent'],
                            $val['content'],
                            0,
                            "shop"
                        );
                    }
                    //=============================================MESSAGES=======================================================






                    //==============================================COUPONS========================================================

                    //get 過去 coupon データ
                    $couponCDList = $this->getValidCouponCD('coupons'); //couponsテーブルから　unique_coupon_cd GET

                    //get 今の持っているクーポンリスト 
                    $nowCouponList = $this->getNowCouponCD('coupons_used', $user_cdSequence); 
                    $nowCouponCdList = array_column($nowCouponList, 'unique_coupon_cd'); //convert into array with 1 index
                   
                  
                    //coupons_used リストに　既にあるデータ消す
                    foreach ($couponCDList as $key => $couponData) {
                        if (in_array($couponData['unique_coupon_cd'], $nowCouponCdList)) {
                            unset($couponCDList[$key]);
                        }
                    }

                    //処理した　クーポンリストをINSERTする
                    foreach ($couponCDList as $cpnData) {

                        //insert to coupons_used
                        $this->insertCouponData('coupons_used',
                                                (int)$cpnData['unique_coupon_cd'],  //convert to INTEGER
                                                $cpnData['updatetime'],
                                                $user_cdSequence
                                            );
                    }
                    


          
                    
                    //==============================================COUPONS========================================================



                }


                // メッセージ表示テスト用
                $json_array[] =  array(
                    'user_cd'       => $user_cdSequence,
                );
            }
        } else {
            // メッセージ表示テスト用
            $json_array[] =  "not_support";
        }

        $this->set(compact('json_array'));
        $this->viewBuilder()
            ->setClassName('Json')
            ->setOption('serialize', 'json_array');
    }


        /**
     * INSERT new user  rooms.【 新規登録　roomsに保存する 】
     * 
     *  K(2023/04)
     * @return void
     */
    private static function makeRooms($shop_cd = null, $user_cd = null)
    {
        // トランザクション
        $connection = ConnectionManager::get('default');  
        try {
            $sql   = "";
            $sql .= "INSERT INTO rooms(shop_cd,user_cd) VALUES ('" . $shop_cd . "','" . $user_cd . "')";
     
            $connection->execute($sql);
            $connection->commit();
    
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
           
            
        }
    }

        /**
     * get ROOM ID .【 room_id 取得 】
     *  MESSAGE 機能
     *  K(2023/04)
     * @return void
     */
    private static function getRoomId($shop_cd, $user_cd)
    {
        // トランザクション
        $connection = ConnectionManager::get('default');

       
        try {
            $sql = "SELECT room_id FROM rooms WHERE shop_cd='" . $shop_cd . "' AND user_cd='" . $user_cd . "' ";

            $room_id = $connection->query($sql)->fetchAll('assoc');

            return $room_id;
        } catch (Exception $e) {
            $this->Flash->error($e);
        }
    }

        /**
     * INSERT content to messages.【 過去データを保存する 】
     *  //room_id, user_cd, shop_cd, datesent, content, seen, sender
     * MESSAGE 機能
     *  K(2023/04)
     * @return void
     */
    public static function makeMessages($room_id, $user_cd, $shop_cd, $datesent, $content, $seen, $sender)
    {
        // トランザクション
        $connection = ConnectionManager::get('default');

        //$connection->begin();
        try {
            $sql = "INSERT INTO messages
                    (
                        room_id, 
                        user_cd,
                        shop_cd,
                        datesent,
                        content,
                        seen,
                        sender
                    )
                    VALUES 
                    (
                        '" . $room_id . "',
                        '" . $user_cd . "',
                        '" . $shop_cd . "',
                        '" . $datesent . "',
                        '" . $content . "',
                        0,
                        'shop'
                        
                    )";

            $connection->execute($sql);
            $connection->commit();
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }

    /**
     * 新規登録モバイル側
     * 
     * K(2023/04)
     * 
     * @return void
     */
    private static function saveRegistered($table = NULL, $searchParam = NULL)
    {

        // 
        date_default_timezone_set('Asia/Tokyo');
        // トランザクション
        $connection = ConnectionManager::get('default');

        // 共通のComponentを呼び出す
        $common = new CommonComponent();


        //  $connection->begin();
        try {
            // 登録
            $sql = "";
            $sql .= " INSERT into public." . $table . " 
            (           
            insdatetime,    
            updatetime,
            user_nm,
            user_pw,
            user_phone,
            connect_kbn)
            VaLUES
            (    
            now(),
            now(),
            '" . $searchParam['user_nm'] . "',
            '" . $searchParam['user_pw'] . "',
            '" . $searchParam['user_phone'] . "',
            '0'
            )
            RETURNING user_cd
            "; //RETURNING = returns (something)

           $user_cd = $connection->query($sql)->fetchAll('assoc'); //RETURN SEQUENCE user_cd

          
            return $user_cd;

            


        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }

        /**
     * get DISTINCT shop_cd.【 DISTINCT shop_cd 取得 】
     * SELECT rooms
     * MESSAGE 機能
     *  K(2023/04)
     * @return void
     */
    private static function getDistinctShop()
    {
        // トランザクション
        $connection = ConnectionManager::get('default');

        $connection->begin();
        try {
            $sql = "";
            $sql .= "SELECT DISTINCT shop_cd FROM rooms";
            $shopsList = $connection->query($sql)->fetchAll('assoc');

            return  $shopsList;
        } catch (Exception $e) {
            $this->Flash->error($e);
        }
    }

        /**
     * get DISTINCT content(messages).【 DISTINCT messages 取得 】
     * SELECT DISTINCT messages
     * MESSAGE 機能
     *  K(2023/04)
     * @return void
     */
    private static function getDistinctMessages()
    {
        // トランザクション
        $connection = ConnectionManager::get('default');

        //$connection->begin();
        try {
            $sql   = "";
            $sql .= "SELECT DISTINCT shop_cd, datesent, content FROM messages WHERE sender='shop' ORDER BY shop_cd ";

            $msgList = $connection->query($sql)->fetchAll('assoc');

            return  $msgList;
        } catch (Exception $e) {
            $this->Flash->error($e);
        }
    }

    /**
     * get COUPON CD LIST【 coupon_used リスト  取得 】
     * @param user_cd
     * COUPON 機能
     *  K(2023/04)
     * @return void
     */
    private static function getNowCouponCD($table=null,$user_cd=null)
    {
        // トランザクション
        $connection = ConnectionManager::get('default');

        try {
            $sql   = "";
            $sql .= "SELECT unique_coupon_cd FROM ".$table." WHERE user_cd='".$user_cd."' ";

            $nowCouponList = $connection->query($sql)->fetchAll('assoc');

            return  $nowCouponList;
        } catch (Exception $e) {
            $this->Flash->error($e);
        }
    }

    /**
     * GET COUPONデータ 【有効期限クーポン　取得】
     * @param NONE
     * COUPON 機能
     *  K(2023/04)
     * @return void
     */
    private static function getValidCouponCD($table=null)
    {
        // トランザクション
        $connection = ConnectionManager::get('default');

        try {
            $sql   = "";
            $sql .= " SELECT *  FROM ".$table." WHERE TO_CHAR(NOW(), 'YYYYMMDD') <= effect_end ";
            

            $couponData = $connection->query($sql)->fetchAll('assoc');
            

            return  $couponData;
        } catch (Exception $e) {
            $this->Flash->error($e);
        }
    }


        /** 
     * INSERT coupon data to coupons.【 過去データを保存する 】
     * @param table,user_cd,datesent(coupons table)
     *  K(2023/04)
     * 
     * @return void
     */
    public static function insertCouponData($table=null, $unique_coupon_cd, $updattime, $user_cdSequence )
    {
        // トランザクション
        $connection = ConnectionManager::get('default');

        //$connection->begin();
        try {
            $sql = "INSERT INTO ".$table." 
                    (
                        unique_coupon_cd,
                        updatetime,
                        user_cd,
                        used
                    )
                    VALUES 
                    (
                        '" . $unique_coupon_cd  . "',
                        '" . $updattime . "',
                        '" . $user_cdSequence           . "',
                        0
                    )";

                   

            $connection->execute($sql);
            $connection->commit();
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }


}
