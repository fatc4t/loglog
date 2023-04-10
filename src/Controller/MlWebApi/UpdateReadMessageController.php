<?php
    /**
     * @file      update READ message to 1=read
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
    
    class UpdateReadMessageController extends AppController {
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
        public function index($user_cd = null, $shop_cd = null) {
            
            // 共通のComponentを呼び出す
            $common = new CommonComponent();
            
            // urlからユーザーコードを取得する
            $user_cd = $this->request->getQuery('user_cd');
            $shop_cd = $this->request->getQuery('shop_cd');


            $updateReadMST0013 = $common->updateReadMST0013($shop_cd, $user_cd);  //make query here, check Commonshit file

            $updateReadMessages = $common->updateReadMessages($shop_cd, $user_cd);
            
           

            if($updateReadMST0013 && $updateReadMessages){
                // not read
                $aryAddRow =  array(
                    'update'          => "yes"
                );
            }else{
                //yes read
                $aryAddRow =  array(
                    'update'          => "no"
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
