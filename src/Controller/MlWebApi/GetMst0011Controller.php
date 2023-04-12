<?php
    /**
     * @file      .loglog顧客情報API   
     * @author    crmbhattarai
     * @date      2022/07/23
     * @version   1.00
     * @note      店舗顧客を取得
     * 
     * 
     * K(2023/04) 
     */

    namespace App\Controller\MlWebApi;

    use App\Controller\AppController;
    use Cake\Event\EventInterface;
    use Cake\Cache\Cache; 
    use Cake\Datasource\ConnectionManager;
    use App\Controller\Component\MlCommon\CommonComponent;

    class GetMst0011Controller extends AppController {
        
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

            //print_r($json[0]['user_phone']);exit;
            
            // データがある時
            if($json){

                //電話番号に重複がないか調べる
                $where  = "";
                $where .= " mst0011.user_phone     = '". $json[0]['user_phone']."' ";

                $user_data = $common->prGetData("mst0011",$where);
                $this->set(compact('user_data'));   
                
                //電話番号に重複があった時          
                if($user_data){
                    // メッセージ表示テスト用
                    $json_array[] =  "already_have";
                }else{
                    

                    foreach($json as $val){

                        $searchParam = [];
                        $searchParam['insdatetime'] = 'now() ';
                        $searchParam['updatetime']  = 'now() '; 
                        $searchParam['user_nm']  = $val['user_nm'];  
                        //user_cd シーケンスがあるのでこれはいらないわけだ
                        $searchParam['user_pw']     = $val['user_pw']; 
                        $searchParam['user_phone']  = $val['user_phone'];
                        $searchParam['connect_kbn'] = '0'; 


                        //save to Database
                        $user_cdSequence = $common->saveRegistered('mst0011',$searchParam); //SEQUENCE の現在地

                      
                    }
                  

                    // メッセージ表示テスト用
                    $json_array[] =  array(
                        'user_cd'       => $user_cdSequence[0]['user_cd'],
                    );

                }
            }else{
                // メッセージ表示テスト用
                    $json_array[] =  "not_support";
                    
            }

            $this->set(compact('json_array'));
            $this->viewBuilder()
                ->setClassName('Json')
                ->setOption('serialize', 'json_array');
        }
    }
    
    
?>
