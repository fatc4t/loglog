<?php
    /**
     * @file      check message if READ
     * @author    KARL
     * @date      2023/03
     * @version   69 nice
     * @note      
     */

    namespace App\Controller\MlWebApi;

    use App\Controller\AppController;
    use Cake\Event\EventInterface;
    use Cake\Cache\Cache; 
    use Cake\Datasource\ConnectionManager;
    use App\Controller\Component\MlCommon\CommonComponent;
    
    class GetReadMessageController extends AppController {
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
         *
         */
        public function index($user_cd = null) {
            
            // 共通のComponentを呼び出す
            $common = new CommonComponent();
            
            // urlからユーザーコードを取得する
            $user_cd = $this->request->getQuery('user_cd');
            $readMsgs = $common->checkReadMessages($user_cd);  //make query here, check Commonshit file
            
           

            if($readMsgs){
                // not read
                $aryAddRow =  array(
                    'seen'          => "not read"
                );

            }else{
                //yes read
                $aryAddRow =  array(
                    'seen'          => "read"
                );

            }


                $json_array[] = $aryAddRow;
            


            //index.php に投げる
            $this->set(compact('json_array','readMsgs'));
            
            // JSON で出力
            $this->viewBuilder()
                ->setClassName('Json')
                ->setOption('serialize', 'json_array');
        }
    }
