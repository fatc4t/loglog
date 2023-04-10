<?php

/**
 * @file      Get Address Add Geolocation
 * @author    KARL
 * @date      2023/03
 * @version   69
 * @note      
 */

namespace App\Controller\MlWebApi;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\Cache\Cache;
use Cake\Datasource\ConnectionManager;
use App\Controller\Component\MlCommon\CommonComponent;

class GeoCheckerController extends AppController
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

    public function index($shop_cd, $add1, $add2, $add3)
    {

        // 共通のComponentを呼び出す
        $common = new CommonComponent();


        $longitude = "";
        $latitude = "";

        $fullShopAddr = $add1.$add2.$add3;

        $url = "https://msearch.gsi.go.jp/address-search/AddressSearch?q=" . urlencode($fullShopAddr);

        //run the URL and get return JSON here

        // Use curl to retrieve the data from the URL
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($curl);
        curl_close($curl);

        // Parse the JSON response
        $response = json_decode($data);

        // Extract the latitude and longitude from the response
        $longitude = $response[0]->geometry->coordinates[0];
        $latitude = $response[0]->geometry->coordinates[1];

        $this->insertLongLat($shop_cd, $longitude, $latitude, $fullShopAddr);
    }




}
