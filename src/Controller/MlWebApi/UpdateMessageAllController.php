<?php

/**
 * @file      .loglog顧客情報API   
 * @author    K
 * @date      2023/04
 * @version   1.00
 * @note      UPDATE ALL USERS (message)
 * 
 */

namespace App\Controller\MlWebApi;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\Cache\Cache;
use Cake\Datasource\ConnectionManager;
use App\Controller\Component\MlCommon\CommonComponent;

class UpdateMessageAllController extends AppController
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

        //流れ -> get all users -> 

        set_time_limit(1800); //30分

        //==============================================USERS========================================================

        $userListArr = $this->getUserList('mst0011');
        $user = array_column($userListArr, 'user_cd'); 

        

        foreach ($user as $userList) {

            print_r("<br>-----".$userList);

            //=============================================MESSAGES=======================================================
            //get 過去 rooms
            $shopsList = $this->getDistinctShop('rooms');                   //roomsから　shop_cdのリスト(ALL)
            $nowShopListArr = $this->getNowShops('rooms', $userList);        //ユーザーごとにの　現在のrooms
            $nowShopList = array_column($nowShopListArr, 'shop_cd');        //convert array 1 column ONLY

            //get 過去 messages
            $msgList = $this->getDistinctMessages('messages');    //distinct get message
            $nowMsgListArr = $this->getNowMsgs('messages', $userList);  //get current shop_cd, content 
            $nowMsgListArrContent = array_column($nowMsgListArr, 'content');     //set to content array


            //messages リストに　既にあるデータ消す
            foreach ($msgList as $key => $messageData) {
                if (in_array($messageData['content'], $nowMsgListArrContent)) {
                    unset($msgList[$key]);
                }
            }
      

            //rooms リストに　既にあるデータ消す
            foreach ($shopsList as $key => $roomData) {
                if (in_array($roomData['shop_cd'], $nowShopList)) {
                    unset($shopsList[$key]);
                }
            }



            //INSERT 過去 room_id + user_id(new)
            foreach ($shopsList as $val) {

                //room_id, shop_cd, user_cd
                $this->makeRooms($val['shop_cd'], $userList);
            }

            //INSERT 過去 content,shop_cd, sender, user_id(new) 
            foreach ($msgList as $val) { //4 results)(3A, 1B)

                //get room_id
                $room_id = $this->getRoomId($val['shop_cd'], $userList);

                //room_id, user_cd, shop_cd, datesent, content, seen, sender
                $this->makeMessages(
                    $room_id[0]['room_id'],
                    $userList,
                    $val['shop_cd'],
                    $val['datesent'],
                    $val['content'],
                    0,
                    "shop"
                );
            }

            //==============================================COUPONS========================================================

        } //end foreach here

        print_r("<br>---終了");
        exit;
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
                        " . $room_id . ",
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
     * get DISTINCT shop_cd.【 DISTINCT shop_cd 取得 】
     * SELECT rooms
     * MESSAGE 機能
     *  K(2023/04)
     * @return void
     */
    private static function getDistinctShop($table = null)
    {
        // トランザクション
        $connection = ConnectionManager::get('default');

        $connection->begin();
        try {
            $sql = "";
            $sql .= "SELECT DISTINCT shop_cd FROM " . $table;
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
    private static function getDistinctMessages($table = null)
    {
        // トランザクション
        $connection = ConnectionManager::get('default');

        //$connection->begin();
        try {
            $sql   = "";
            $sql .= "SELECT DISTINCT ON (content) * FROM " . $table . " WHERE sender='shop' ";

            $msgList = $connection->query($sql)->fetchAll('assoc');

            return  $msgList;
        } catch (Exception $e) {
            $this->Flash->error($e);
        }
    }


    /**
     * GET USERS LIST
     * @param NONE
     * USERS データ 機能
     *  K(2023/04)
     * @return void
     */
    private static function getUserList($table = null)
    {
        // トランザクション
        $connection = ConnectionManager::get('default');

        try {
            $sql   = "";
            $sql .= " SELECT user_cd  FROM " . $table . "  WHERE user_cd > '00019713'  order by user_cd asc limit 500";




            $couponData = $connection->query($sql)->fetchAll('assoc');

            return  $couponData;
        } catch (Exception $e) {
            $this->Flash->error($e);
        }
    }

    /**
     * GET NOW ROOMS 【 現在のrooms 】
     * @param user_cd
     * 
     *  K(2023/04)
     * @return void
     */
    private static function getNowShops($table = null, $user_cd = null)
    {
        // トランザクション
        $connection = ConnectionManager::get('default');

        try {
            $sql   = "";
            $sql .= "SELECT shop_cd FROM " . $table . " WHERE user_cd='" . $user_cd . "' ";

            $nowCouponList = $connection->query($sql)->fetchAll('assoc');

            return  $nowCouponList;
        } catch (Exception $e) {
            $this->Flash->error($e);
        }
    }

    /**
     * GET NOW Messages 【 現在のmessages 】
     * @param user_cd
     * 
     *  K(2023/04)
     * @return void
     */
    private static function getNowMsgs($table = null, $user_cd = null)
    {
        // トランザクション
        $connection = ConnectionManager::get('default');

        try {
            $sql   = "";
            $sql .= "SELECT shop_cd, content FROM " . $table . " WHERE user_cd='" . $user_cd . "' ";

            $nowMsgList = $connection->query($sql)->fetchAll('assoc');

            return  $nowMsgList;
        } catch (Exception $e) {
            $this->Flash->error($e);
        }
    }
}
