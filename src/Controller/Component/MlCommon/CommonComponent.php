<?php


/**
 * 共通のクラス
 * @note
 */

declare(strict_types=1);

namespace App\Controller\Component\MlCommon;

use Cake\Datasource\ConnectionManager;

class CommonComponent
{
    /**
     * prUpdSend method.【 データ更新 】
     *
     * @return void
     */
    public static function prUpdSend($table = NULL, $where = NULL, $connect_kbn = NULL, $used = NULL)
    {

        $connection = ConnectionManager::get('default');

        // 条件
        if ($where) {
            $where = " where " . $where;
        }

        try {

            $sql  = "";
            $sql .= " update public." . $table . " set ";
            if ($connect_kbn) {
                $sql .= " connect_kbn = '" . $connect_kbn . "'";
            }
            if ($used) {
                $sql .= " used = '" . $used . "'";
            }
            $sql .= $where;

            $connection->execute($sql);
            $connection->commit();
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }
    /**
     * prSavePic method.【 データ取得 】
     * param : コード 
     * @return void
     */
    public static function prGetData($table = NULL, $where = NULL, $order = NULL, $limit = NULL)
    {

        $connection = ConnectionManager::get('default');

        // 条件
        if ($where) {
            $where = " where " . $where;
        }
        // 並順
        if ($order) {
            $order = " order by " . $order;
        }
        // データ件数リミット
        if ($limit) {
            $limit = " limit " . $limit;
        }
        $sql   = "";
        $sql   .= "select ";
        $sql   .= "* ";
        $sql   .= "from " . $table;
        $sql   .= $where;
        $sql   .= $order;
        $sql   .= $limit;




        // SQLの実行
        $query = $connection->query($sql)->fetchAll('assoc');

        return $query;
    }
    /**
     * prSavePic method.【 データ取得 】
     * param : コード 
     * @return void
     */
    public static function prGetDatajoin($table = NULL, $where = NULL, $order = NULL, $limit = NULL)
    {

        $connection = ConnectionManager::get('default');

        // 条件
        if ($where) {
            $where = " where " . $where;
        }
        // 並順
        if ($order) {
            $order = " order by " . $order;
        }
        // データ件数リミット
        if ($limit) {
            $limit = " limit " . $limit;
        }
        $sql   = "";
        $sql   .= "select ";
        $sql   .= "* ";
        $sql   .= "from " . $table;
        $sql   .= $where;
        $sql   .= $order;
        $sql   .= $limit;

        // SQLの実行
        $query = $connection->query($sql)->fetchAll('assoc');

        return $query;
    }
    /**
     * prSavePic method.【 データ取得 】
     * param : コード 
     * @return void
     */
    public static function prGetDataDistinct($table = NULL, $where = NULL, $order = NULL, $limit = NULL, $distinct = NULL)
    {

        $connection = ConnectionManager::get('default');

        // 条件
        if ($where) {
            $where = " where " . $where;
        }
        // 並順
        if ($order) {
            $order = " order by " . $order;
        }
        // データ件数リミット
        if ($limit) {
            $limit = " limit " . $limit;
        }
        if ($distinct) {
            $distinct = "distinct on (" . $distinct . ") ";
        }
        $sql   = "";
        $sql   .= "select * from ( ";
        $sql   .= "select ";
        $sql   .= $distinct;
        $sql   .= "* ";
        $sql   .= "from " . $table;
        $sql   .= $where;
        $sql   .= " ) a ";
        $sql   .= $order;
        $sql   .= $limit;

        // SQLの実行
        $query = $connection->query($sql)->fetchAll('assoc');

        return $query;
    }

    /**
     * prSavePic method.【 MAXデータ取得 】
     * param : コード 
     * @return void
     */
    public static function prGetMaxValue($menu = NULL, $table = NULL, $where = NULL)
    {

        $connection = ConnectionManager::get('default');
        // 条件
        if ($where) {
            $where = " where " . $where;
        }

        $sql   = "";
        $sql   .= "select max( ";
        $sql   .= "$menu ) ";
        $sql   .= "from " . $table;
        $sql   .= $where;

        // SQLの実行
        $query = $connection->query($sql)->fetchAll('assoc');

        return $query;
    }
    /**
     * prSavePic method.【 MAXデータ取得 】
     * param : コード 
     * @return void
     */
    public static function prGetCount($table = NULL, $count = NULL, $where = NULL)
    {

        $connection = ConnectionManager::get('default');
        // 条件
        if ($where) {
            $where = " where " . $where;
        }

        $sql   = "";
        $sql   .= "select count( " . $count . ")";
        $sql   .= "from " . $table;
        $sql   .= $where;

        // SQLの実行
        $query = $connection->query($sql)->fetchAll('assoc');

        return $query;
    }

    /**
     * prSavePic method.【 写真保存 】
     *
     * @return void
     */
    public static  function prSavePic($path = NULL, $myFiles = NULL)
    {

        $pic_nm = [];
        // お店の写真を保存するためにフォルダーを作成する
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $files = $_FILES['my_file']['tmp_name']; //+ $_FILES['my_file_Pro']['tmp_name'];


        $file_name = $_FILES['my_file']['name'];


        $path1     = $path . '/';

        $j = 0;
        foreach ($files as $file) {
            // ファイルがアップロードされているかの判定
            if ($file) {

                list($width, $height) = getimagesize($file);

                // 縦横のリサイズ後のピクセル数を求める
                if ($width > $height) {
                    $ratio = 300 / $width;
                } else {
                    $ratio = 300 / $height;
                }

                $nwidth  = (int)($width * $ratio);
                $nheight = (int)($height * $ratio);
                $newimage = imagecreatetruecolor($nwidth, $nheight);

                // JPEG, PNG, GIF, BMP, WBMP, GD2 をサポートするようビルドされている場合、 イメージの種類は自動的に判別される
                $source = imagecreatefromstring(file_get_contents($file));
                imagecopyresized($newimage, $source, 0, 0, 0, 0, $nwidth, $nheight, $width, $height);
                imagejpeg($newimage, $path1 . $file_name[$j], 30);


                //print_r($myFiles[$j]); 

                // アップロードしたファイルを $path1 に保存
                $pic_nm[$j] = $myFiles[$j]->getClientFilename();
            } else {
                $pic_nm[$j] = "";
            }
            $j++;
        }
        return $pic_nm;
    }


    /**
     * Card image save method.【 写真保存 】 -----K(2023/02)
     *
     * @return void
     */

    public static  function prSaveCardPic($path = NULL, $myFiles = NULL)
    {

        $pic_nm = [];
        // お店の写真を保存するためにフォルダーを作成する
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $files = $_FILES['my_card']['tmp_name']; //+ $_FILES['my_file_Pro']['tmp_name'];


        $file_name = $_FILES['my_card']['name'];


        $path1     = $path . '/';

        $j = 0;

        // ファイルがアップロードされているかの判定
        if ($files) {

            list($width, $height) = getimagesize($files);

            // 縦横のリサイズ後のピクセル数を求める
            if ($width > $height) {
                $ratio = 300 / $width;
            } else {
                $ratio = 300 / $height;
            }

            $nwidth  = (int)($width * $ratio);
            $nheight = (int)($height * $ratio);
            $newimage = imagecreatetruecolor($nwidth, $nheight);

            // JPEG, PNG, GIF, BMP, WBMP, GD2 をサポートするようビルドされている場合、 イメージの種類は自動的に判別される
            $source = imagecreatefromstring(file_get_contents($files));
            imagecopyresized($newimage, $source, 0, 0, 0, 0, $nwidth, $nheight, $width, $height);
            imagejpeg($newimage, $path1 . $file_name, 30);


            //print_r($myFiles[$j]); 

            // アップロードしたファイルを $path1 に保存
            $pic_nm = $myFiles->getClientFilename();
        } else {
            $pic_nm = "";
        }
        $j++;

        return $pic_nm;
    }

    /**
     * Card LOGO save method.【 写真保存 】 -----K(2023/04)
     *
     * @return void
     */

    public static  function saveCardLogo($path = NULL, $myFiles = NULL)
    {

        $pic_nm = [];
        // お店の写真を保存するためにフォルダーを作成する
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $files = $_FILES['logo']['tmp_name'];


        $file_name = $_FILES['logo']['name'];


        $path1     = $path . '/';

        $j = 0;

        // ファイルがアップロードされているかの判定
        if ($files) {

            list($width, $height) = getimagesize($files);

            // 縦横のリサイズ後のピクセル数を求める
            if ($width > $height) {
                $ratio = 300 / $width;
            } else {
                $ratio = 300 / $height;
            }

            $nwidth  = (int)($width * $ratio);
            $nheight = (int)($height * $ratio);
            $newimage = imagecreatetruecolor($nwidth, $nheight);

            // JPEG, PNG, GIF, BMP, WBMP, GD2 をサポートするようビルドされている場合、 イメージの種類は自動的に判別される
            $source = imagecreatefromstring(file_get_contents($files));
            imagecopyresized($newimage, $source, 0, 0, 0, 0, $nwidth, $nheight, $width, $height);
            imagejpeg($newimage, $path1 . $file_name, 30);




            // アップロードしたファイルを $path1 に保存
            $pic_nm = $myFiles->getClientFilename();
        } else {
            $pic_nm = "";
        }
        $j++;

        return $pic_nm;
    }

    /**
     * prDeletedata method.【 削除 】
     *
     * @return void
     */
    public static function prDeletedata($table = NULL, $where = NULL)
    {
        // トランザクション
        $connection = ConnectionManager::get('default');

        $connection->begin();

        if ($where) {
            $where = " where " . $where;
        }
        try {
            // 削除
            $delsql = " delete from public." . $table . $where;

            $connection->execute($delsql);
            $connection->commit();
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }

    /**
     * prSavedata method.【 登録 】
     *
     * @return void
     */
    public static function prSavedata($table = NULL, $searchParam = NULL)
    {

        // 
        date_default_timezone_set('Asia/Tokyo');
        // トランザクション
        $connection = ConnectionManager::get('default');

        // 共通のComponentを呼び出す
        $common = new CommonComponent();


        // テーブルのカラムを取得
        $columns = $common->prgetColumn($table);
        $count = count($columns);

        for ($i = 0; $i < $count; $i++) {
            $values[] = "'{$searchParam[$columns[$i]['column_name']]}'";
            $column[] = $columns[$i]['column_name'];
        }

        //  $connection->begin();
        try {
            // 登録
            $sql = "";
            $sql .= " INSERT into public." . $table . " (" . implode(", ", $column) . " ) VALUES (" . join(",", $values) . " );";

            $connection->execute($sql);
            $connection->commit();
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }
    /**
     * UPDATE method.【 更新 】店舗側　SHOP- EDIT
     * KARL 2023/01
     * @return void
     */
    public static function prUpdateEditdata($table = NULL, $searchParam = NULL, $where = NULL)
    {
        // 
        date_default_timezone_set('Asia/Tokyo');
        // トランザクション
        $connection = ConnectionManager::get('default');

        // 共通のComponentを呼び出す
        $common = new CommonComponent();

        if ($where) {
            $where = " where " . $where;
        }

        //  $connection->begin();
        try {
            // UPDATE 
            $sql2 = " UPDATE public." . $table . " SET ";
            $sql2 .= "updatetime          = now(), ";
            $sql2 .= "upduser_cd          ='" . $searchParam['upduser_cd'] . "', ";
            $sql2 .= "shop_nm             ='" . $searchParam['shop_nm'] . "', ";
            $sql2 .= "shop_kn             ='" . $searchParam['shop_kn'] . "', ";
            $sql2 .= "shop_phone          ='" . $searchParam['shop_phone'] . "', ";
            $sql2 .= "shop_fax            ='" . $searchParam['shop_fax'] . "', ";
            $sql2 .= "shop_postcd         ='" . $searchParam['shop_postcd'] . "', ";
            $sql2 .= "shop_add1           ='" . $searchParam['shop_add1'] . "', ";
            $sql2 .= "shop_add2           ='" . $searchParam['shop_add2'] . "', ";
            $sql2 .= "shop_add3           ='" . $searchParam['shop_add3'] . "', ";
            $sql2 .= "opentime1           ='" . $searchParam['opentime1'] . "', ";
            $sql2 .= "closetime1          ='" . $searchParam['closetime1'] . "', ";
            $sql2 .= "opentime2           ='" . $searchParam['opentime2'] . "', ";
            $sql2 .= "closetime2          ='" . $searchParam['closetime2'] . "', ";
            $sql2 .= "shop_pw             ='" . $searchParam['shop_pw'] . "', ";
            $sql2 .= "url_hp              ='" . $searchParam['url_hp'] . "', ";
            $sql2 .= "url_sns2            ='" . $searchParam['url_sns2'] . "', ";
            $sql2 .= "url_sns3            ='" . $searchParam['url_sns3'] . "', ";
            $sql2 .= "url_sns4            ='" . $searchParam['url_sns4'] . "', ";
            $sql2 .= "goods               ='" . $searchParam['goods'] . "', ";

            if ($searchParam['thumbnail1'] !== "") {
                $sql2 .= "thumbnail1          ='" . $searchParam['thumbnail1'] . "', ";
            }
            if ($searchParam['thumbnail2'] !== "") {
                $sql2 .= "thumbnail2          ='" . $searchParam['thumbnail2'] . "', ";
            }
            if ($searchParam['thumbnail3'] !== "") {
                $sql2 .= "thumbnail3          ='" . $searchParam['thumbnail3'] . "', ";
            }
            //----card image
            $sql2 .= "card_image            ='" . $searchParam['card_image'] . "', ";
            $sql2 .= "holiday1              ='" . $searchParam['holiday1'] . "', ";
            $sql2 .= "holiday2              ='" . $searchParam['holiday2'] . "', ";
            $sql2 .= "holiday3              ='" . $searchParam['holiday3'] . "', ";
            $sql2 .= "free_text             ='" . $searchParam['free_text'] . "', ";
            $sql2 .= "point                 ='" . $searchParam['point'] . "', ";
            $sql2 .= "special_point_cd      ='" . $searchParam['special_point_cd'] . "', ";
            $sql2 .= "shop_group_cd         ='" . $searchParam['shop_group_cd'] . "', ";
            $sql2 .= "barcode_kbn           ='" . $searchParam['barcode_kbn'] . "', ";      //
            $sql2 .= "logo                  ='" . $searchParam['logo'] . "' ";              //card logo

            $sql2 .= " " . $where . " ";


            $connection->execute($sql2);
            $connection->commit();
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }

    /**
     * SPPoint Check method.【 更新 】
     * KARL 2023/02
     * @return void
     */
    public static function prSpecialPointCheck($table = NULL, $searchParam = NULL)
    {
        // 
        date_default_timezone_set('Asia/Tokyo');
        // トランザクション
        $connection = ConnectionManager::get('default');

        // 共通のComponentを呼び出す
        $common = new CommonComponent();

        $connection->begin();
        try {

            // Check if 有るか無いか
            $sql2 = " SELECT special_point_cd  from public." . $table . " ";
            $sql2 .= " where shop_cd = '" . $searchParam['shop_cd'] . "' ";

            $query = $connection->query($sql2)->fetchAll('assoc');

            return $query;
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }

    /**
     * prSavedata method.【 店舗マスタ登録 】
     *
     * @return void
     */
    public static function prgetColumn($table = NULL)
    {

        // トランザクション
        $connection = ConnectionManager::get('default');
        $connection->begin();

        try {
            $where = "";
            if ($table) {
                $where = "where table_name = '" . $table . "'";
            }
            $sql = "";
            $sql .= "select ";
            $sql .= "   column_name ";
            $sql .= "from ";
            $sql .= "   information_schema.columns ";
            $sql .= $where;

            // SQLの実行
            $query = $connection->query($sql)->fetchAll('assoc');

            return $query;
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }

    /**
     * prGetholidays method.【 定休日 】
     *
     * @return void
     */
    public static  function prGetholidays()
    {

        $holiday[0] = ['cd'  => '0', 'name' => '不定休',];
        $holiday[1] = ['cd'  => '1', 'name' => '月曜日',];
        $holiday[2] = ['cd'  => '2', 'name' => '火曜日',];
        $holiday[3] = ['cd'  => '3', 'name' => '水曜日',];
        $holiday[4] = ['cd'  => '4', 'name' => '木曜日',];
        $holiday[5] = ['cd'  => '5', 'name' => '金曜日',];
        $holiday[6] = ['cd'  => '6', 'name' => '土曜日',];
        $holiday[7] = ['cd'  => '7', 'name' => '日曜日',];
        $holiday[8] = ['cd'  => '8', 'name' => '祝日',];
        $holiday[9] = ['cd'  => '9', 'name' => 'なし',];

        return $holiday;
    }
    /**
     * prGetholidays method.【 定休日 】
     *
     * @return void
     */
    public static  function prGetToday()
    {

        // 日本の時間を設定
        date_default_timezone_set('Asia/Tokyo');

        // 日時取得
        $currentdate = date('Ymd');

        return $currentdate;
    }
    /*ADDSTR 2022/11/12 */
    /**
     * prGetholidays method.【 定休日 】
     *
     * @return void
     */
    public static  function prGetmonths()
    {

        $months[0] = ['cd'  => '01', 'name' => '1月',];
        $months[1] = ['cd'  => '02', 'name' => '2月',];
        $months[2] = ['cd'  => '03', 'name' => '3月',];
        $months[3] = ['cd'  => '04', 'name' => '4月',];
        $months[4] = ['cd'  => '05', 'name' => '5月',];
        $months[5] = ['cd'  => '06', 'name' => '6月',];
        $months[6] = ['cd'  => '07', 'name' => '7月',];
        $months[7] = ['cd'  => '08', 'name' => '8月',];
        $months[8] = ['cd'  => '09', 'name' => '9月',];
        $months[9] = ['cd'  => '10', 'name' => '10月',];
        $months[10] = ['cd'  => '11', 'name' => '11月',];
        $months[11] = ['cd'  => '12', 'name' => '12月',];

        return $months;
    }

    /**
     * prGetgender method.【 性別 】
     *
     * @return void
     */
    public static  function prGetgender()
    {

        $gender[0] = ['cd'  => '01', 'name' => '男性',];
        $gender[1] = ['cd'  => '02', 'name' => '女性',];
        $gender[2] = ['cd'  => '03', 'name' => '答えない',];


        return $gender;
    }
    /**
     * prGetages method.【 年代 】
     *
     * @return void
     */
    public static  function prGetages()
    {

        $ages[0] = ['cd'  => '10', 'name' => '10代',];
        $ages[1] = ['cd'  => '20', 'name' => '20代',];
        $ages[3] = ['cd'  => '30', 'name' => '30代',];
        $ages[4] = ['cd'  => '40', 'name' => '40代',];
        $ages[5] = ['cd'  => '50', 'name' => '50代',];
        $ages[6] = ['cd'  => '60', 'name' => '60以上',];


        return $ages;
    }
    /**
     * prGetpaidmembers method.【 会員 】
     *
     * @return void
     */
    public static  function prGetpaidmembers()
    {

        $paidmembers[0] = ['cd'  => '0', 'name' => '有料会員ではない',];
        $paidmembers[1] = ['cd'  => '1', 'name' => '有料会員である',];

        return $paidmembers;
    }
    /**
     * prGetpaidmembers method.【 ポイントカード対応 】
     *  K(2023/03)
     * @return void
     */
    public static  function prGetpoint()
    {

        $point[0] = ['cd'  => '0', 'name' => '対応していない',];
        $point[1] = ['cd'  => '1', 'name' => '対応している',];

        return $point;
    }

    /**
     * barcodeList method.【バーコード区分 】
     *  K(2023/04)
     * @return void
     */
    public static  function barcodeList()
    {
        $barcodeType[0] = ['cd'  => '0', 'name' => 'JAN13',];
        $barcodeType[1] = ['cd'  => '1', 'name' => 'JAN8',];
        $barcodeType[2] = ['cd'  => '2', 'name' => 'NW7',];
        $barcodeType[3] = ['cd'  => '3', 'name' => 'Code 38',];
        $barcodeType[4] = ['cd'  => '4', 'name' => 'Code 128',];

        // $barcodeType[0] = ['cd'  => '0', 'name' => 'Codabar',];
        // $barcodeType[1] = ['cd'  => '1', 'name' => 'Code 39 Extended',];
        // $barcodeType[2] = ['cd'  => '2', 'name' => 'Code 39',];
        // $barcodeType[3] = ['cd'  => '3', 'name' => 'Code 93',];
        // $barcodeType[4] = ['cd'  => '4', 'name' => 'Code 128',];
        // $barcodeType[5] = ['cd'  => '5', 'name' => 'Code 128A',];
        // $barcodeType[6] = ['cd'  => '6', 'name' => 'Code 128B',];
        // $barcodeType[7] = ['cd'  => '7', 'name' => 'Code 128C',];
        // $barcodeType[8] = ['cd'  => '8', 'name' => 'EAN-8',];
        // $barcodeType[9] = ['cd'  => '9', 'name' => 'EAN-13',];
        // $barcodeType[10] = ['cd'  => '10', 'name' => 'UPC-A',];
        // $barcodeType[11] = ['cd'  => '11', 'name' => 'UPC-E',];


        return $barcodeType;
    }

    /**
     * barcode change to CODE.【バーコード区分 】
     *  K(2023/04)
     * @return void
     */
    public static  function convertBarcodeCode($code)
    {

        switch ($code) {
            case "JAN13":
                $convertCode = "1";
                break;
            case "JAN8":
                $convertCode = "2";
                break;
            case "NW7":
                $convertCode = "3";
                break;
            case "Code 38":
                $convertCode = "4";
                break;
            case "Code 128":
                $convertCode = "5";
                break;
            default:
                $convertCode = "";
        }

        return $convertCode;
    }


    /**
     * getVisitConditions method.【 来店条件 】------- K 23/03
     *
     * @return void
     */
    public static  function getVisitConditions()
    {

        $raiten_jo[0] = ['cd'  => '0', 'name' => '0',];
        $raiten_jo[1] = ['cd'  => '1', 'name' => '1',];
        $raiten_jo[2] = ['cd'  => '2', 'name' => '2',];
        $raiten_jo[3] = ['cd'  => '3', 'name' => '3',];
        $raiten_jo[4] = ['cd'  => '4', 'name' => '4',];
        $raiten_jo[5] = ['cd'  => '5', 'name' => '5',];
        $raiten_jo[6] = ['cd'  => '6', 'name' => '6',];
        $raiten_jo[7] = ['cd'  => '7', 'name' => '7',];
        $raiten_jo[8] = ['cd'  => '8', 'name' => '8',];
        $raiten_jo[9] = ['cd'  => '9', 'name' => '9',];
        $raiten_jo[10] = ['cd' => '10', 'name' => '10',];



        return $raiten_jo;
    }
    /*
     *  count 来店数回    K(2023/03)
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

    /*
    *  set coupon to USED=1 K(2023/03)
    */
    public static function updateUsedCoupon($user_cd, $shop_cd, $coupon_cd)
    {

        $connection = ConnectionManager::get('default');
        // 条件

        $sql   = "";
        $sql   .= "update";
        $sql   .= " mst0012 set used = '1' ";
        $sql   .= " where user_cd = '" . $user_cd . "'";
        $sql   .= " and shop_cd = '" . $shop_cd . "'";
        $sql   .= " and coupon_cd = '" . $coupon_cd . "'";

        //print_r($sql);exit;

        // SQLの実行
        $query = $connection->query($sql)->fetchAll('assoc');


        return $query;
    }

    /*
     * prGetrirekiData method.【 履歴データ 】
     * @return void
     */
    public static function prGetrirekiData($shop_cd, $user_cd)
    {
        // トランザクション
        $connection = ConnectionManager::get('default');
        $connection->begin();

        try {

            $sql = "";
            $sql .= " select ";
            $sql .= "   shop_cd, ";
            $sql .= "   count(user_Cd) ";
            $sql .= " from trn0012 ";
            $sql .= " where user_cd = '" . $user_cd . "'";
            $sql .= " and shop_cd = '" . $shop_cd . "'";
            $sql .= " and shop_cd <> '0000' ";
            $sql .= " group by shop_cd ";

            // SQLの実行
            $query = $connection->query($sql)->fetchAll('assoc');



            return $query;
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }
    /*
     * prGetraitenData method.【 日別来店者データ 】
     * @return void
     */
    public static function prGetraitenData($where = NULL)
    {

        $connection = ConnectionManager::get('default');

        try {

            $sql  = "";
            $sql  .= " select ";
            $sql  .= " to_char(trn0012.updatetime, 'yyyy/mm/dd')  as visit_dt ";
            $sql  .= " ,user_cd  as user_cd ";
            $sql  .= " ,user_nm  as user_nm ";
            $sql  .= " ,count(*) as  tot_count ";
            $sql  .= " from trn0012 ";
            $sql  .= " left join mst0011 using(user_cd) ";
            $sql  .= $where;
            $sql  .= " group by visit_dt ,user_cd ,user_nm ";
            $sql  .= " order by visit_dt ";

            // SQLの実行
            $query = $connection->query($sql)->fetchAll('assoc');

            return $query;
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }

    /*
     * prGetcouponUsedData method.【 クーポン利用者数データ 】
     * K(2023/04)
     */
    public static function prGetcouponUsedData($where = NULL)
    {

        $connection = ConnectionManager::get('default');

        try {

            $sql  = "";
            $sql  .= " select ";
            $sql  .= " to_char(updatetime, 'yyyy/mm/dd')  as visit_dt ";
            $sql  .= " ,count(*) as  tot_count ";
            $sql  .= " from mst0012 ";
            $sql  .= $where;
            $sql  .= " group by visit_dt  ";
            $sql  .= " order by visit_dt ";

            // SQLの実行
            $query = $connection->query($sql)->fetchAll('assoc');

            return $query;
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }
    /*
     * prGetraitenData method.【 来店者属性データ 】
     * @return void
     */
    public static function prGetMigrationRateData($where = NULL)
    {

        $connection = ConnectionManager::get('default');

        try {

            $sql   = "";
            $sql  .= " select ";
            $sql  .= "    case when numb = 1 then '１店舗のみ'  ";
            $sql  .= "         when numb between 2 and 5 then '2～5店舗' ";
            $sql  .= "         when numb between 6 and 7 then '6～7店舗' ";
            $sql  .= "         when numb between 8 and 10 then '8～10店舗' ";
            $sql  .= "         when numb > 10 then '10店舗以上' end as shop_cnt ";
            $sql  .= "    ,sum(numb) as shop_count ";
            $sql  .= " from ( ";
            $sql  .= " select ";
            $sql  .= "   count(shop_cd) as numb ";
            $sql  .= " from trn0012 ";
            $sql  .= $where;
            $sql  .= " group by user_cd ";
            $sql  .= " ) a ";
            $sql  .= " group by ";
            $sql  .= "  shop_cnt ";
            $sql  .= " order by  ";
            $sql  .= " shop_cnt ";

            // SQLの実行
            $query = $connection->query($sql)->fetchAll('assoc');

            return $query;
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }
    /*
     * prGetraitenData method.【 来店者属性データ 】
     * @return void
     */
    public static function prGetraitenTypeData($where = NULL)
    {

        $connection = ConnectionManager::get('default');

        try {

            $sql   = "";
            $sql  .= " select ";
            $sql  .= "    case when address = '' then '未登録' else address end as addss  ";
            $sql  .= "   ,case when ((age / 10) * 10) = 0 then '未登録' ";
            $sql  .= "       else CONCAT((age / 10) * 10, '代') end AS period ";
            $sql  .= "   , gender ";
            $sql  .= "   , count(*) as total ";
            $sql  .= " from ( ";
            $sql  .= " select ";
            $sql  .= "   add1 as address ";
            $sql  .= "  ,case when birthday = '' then 0 else (current_date - TO_DATE(birthday, 'YYYYMMDD')) /365 end as age ";
            $sql  .= "  ,case when gender = '01' then '男性' ";
            $sql  .= "        when gender = '02' then '女性' ";
            $sql  .= "        when gender = '03' then '答えない' ";
            $sql  .= "        when gender = ''   then '未登録' end  as gender ";
            $sql  .= " from mst0011 ";
            $sql  .= " left join trn0012 using (user_cd) ";
            $sql  .= $where;
            $sql  .= " order by address ,age ";
            $sql  .= " ) a ";
            $sql  .= " group by ";
            $sql  .= "   addss ";
            $sql  .= "  ,a.age / 10 ";
            $sql  .= "  ,gender ";
            $sql  .= " order by  ";
            $sql  .= "   addss ";
            $sql  .= "  ,period ";
            $sql  .= "  ,gender ";

            // SQLの実行
            $query = $connection->query($sql)->fetchAll('assoc');

            return $query;
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }
    /**
     * prCsvOutput method.【 CSV出力 】
     *
     * @return void
     */
    public static function prCsvOutput($export_header, $data, $filename)
    {

        $startDateM = date("Ymd");
        // ファイル名
        // escStr があったが、一時的に外している
        $file_path = mb_convert_encoding(str_replace("/", "", $startDateM) . ".csv", 'SJIS-win', 'UTF-8');

        if (touch($file_path)) {

            // オブジェクト生成
            $file = new \SplFileObject($file_path, "w");

            // エンコードしたタイトル行を配列ごとCSVデータ化
            $file = fopen($file_path, 'a');
            fwrite($file, implode(',', $export_header) . "\r");

            $csv_data = $data;

            // 明細行ごと処理

            foreach ($csv_data as $row) {

                // 明細行をセット
                $str = '"' . implode('","', $row);
                $str = $str . '"';

                // SJIS-winへ
                $str = mb_convert_encoding($str, 'SJIS-win', 'UTF-8');
                // 書き込み
                $file = fopen($file_path, 'a');
                fwrite($file, "\n" . $str . "\r");
            }
        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=" . $filename . mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8') . ";");
        header("Content-Transfer-Encoding: binary");
        readfile("$file_path");

        exit;
    }
    /**
     * update message KBN to seen (1=seen) MST0013
     * KARL 2023/03
     * @return void
     */
    public static function updateReadMST0013($shop_cd, $user_cd)
    {

        $connection = ConnectionManager::get('default');

        try {
            $sql   = "";
            $sql .=  "update  MST0013 
             SET
             connect_kbn='1'
             WHERE 
             shop_cd='" . $shop_cd . "'
             and 
             user_cd='" . $user_cd . "'
            ";

            //where read is not 1

            // SQLの実行
            $connection->query($sql)->fetchAll('assoc');
            return "Success";
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }
    /**
     * update message KBN to seen (1=seen) MESSAGES
     * KARL 2023/03
     * @return void
     */
    public static function updateReadMessages($shop_cd, $user_cd)
    {

        $connection = ConnectionManager::get('default');

        try {
            $sql   = "";
            $sql .=  "update MESSAGES 
             SET 
             seen='1' 
             WHERE 
             user_cd='" . $user_cd . "' 
             and 
             shop_cd='" . $shop_cd . "' 
            ";

            //where read is not 1

            // SQLの実行
            $connection->query($sql)->fetchAll('assoc');

            return "Success";
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }

    /**
     * check message KBN MST0013
     * KARL 2023/03
     * @return void
     */
    public static function checkReadMessages($user_cd)
    {

        $connection = ConnectionManager::get('default');

        try {
            $sql   = "";
            $sql .=  "select * from mst0013 where user_cd='" . $user_cd . "' and connect_kbn <> '1'";

            //where read is not 1

            // SQLの実行
            $query = $connection->query($sql)->fetchAll('assoc');

            return $query;
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }

    /**
     * Get Message SHop Info
     * KARL 2023/02
     * @return void
     */
    public static function getMessageShopInfo($user_cd)
    {

        $connection = ConnectionManager::get('default');

        try {
            $sql   = "";
            $sql .=  " 
            SELECT R.room_id, 
            TO_CHAR((SELECT datesent FROM messages WHERE user_cd='" . $user_cd . "' and messages.room_id=R.room_id ORDER BY datesent DESC limit 1   ), 'YYYY-MM-DD HH24:MI') AS datesent,
            S.shop_nm, 
           R.shop_cd, 
            R.user_cd, S.shop_postcd, S.shop_add1, S.shop_add2, S.shop_add3, 
           S.shop_phone, S.opentime1, S.closetime1, S.opentime2, S.closetime2, S.url_hp, S.url_sns1, S.url_sns2, S.url_sns3, S.url_sns4, 
           S.thumbnail1, S.thumbnail2, S.thumbnail3, S.goods, S.paidmember, S.holiday1, S.holiday2, S.holiday3, S.free_text, R.liked
            FROM rooms R
            LEFT JOIN mst0010 S on R.shop_cd = S.shop_cd
            where R.user_cd='" . $user_cd . "' 
            and 
            (SELECT datesent FROM messages WHERE user_cd='" . $user_cd . "' and messages.room_id=R.room_id ORDER BY datesent DESC limit 1   ) is not NULL
            order by datesent desc";


            // SQLの実行
            $query = $connection->query($sql)->fetchAll('assoc');

            return $query;
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }


    /**
     * GET messages USER and SHOP
     * KARL 2023/02
     * @return void
     */
    public static function getMessageData($room_id)
    {

        $connection = ConnectionManager::get('default');

        try {
            $sql   = "";
            $sql .=
                "SELECT
            room_id,
            user_cd,
            shop_cd,
            TO_CHAR(datesent, 'YYYY-MM-DD HH24:MI:SS.FF6') AS datesent,
            content,
            seen,
            sender
        FROM MESSAGES
        WHERE room_id = '" . $room_id . "'
            ORDER BY datesent desc";

            //print_r($sql);exit;
            // SQLの実行
            $query = $connection->query($sql)->fetchAll('assoc');
            return $query;
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }
    /**
     * GET ROOMS user_cd and shop_cd if getMessageData is EMPTY
     * KARL 2023/02
     * @return void
     */
    public static function getRoomInfo($room_id)
    {

        $connection = ConnectionManager::get('default');

        try {
            $sql   = "";
            $sql = "Select * from rooms where room_id='" . $room_id . "'";

            // SQLの実行
            $query = $connection->query($sql)->fetchAll('assoc');

            return $query;
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }

    /**
     * GET chat room ID SHOP
     * KARL 2023/02
     * @return void
     */
    public static function getUserRoomID($shop_cd)
    {

        $connection = ConnectionManager::get('default');

        try {
            $sql   = "";
            $sql = "Select * from rooms where shop_cd='" . $shop_cd . "'";

            // SQLの実行
            $query = $connection->query($sql)->fetchAll('assoc');

            return $query;
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }
    /**
     * GET chat room ID USER
     * KARL 2023/02
     * @return void
     */
    public static function getShopRoomID($user_cd)
    {

        $connection = ConnectionManager::get('default');

        try {
            $sql   = "";
            $sql = "Select * from rooms where user_cd='" . $user_cd . "'";

            // SQLの実行
            $query = $connection->query($sql)->fetchAll('assoc');

            return $query;
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }



    /**
     * SEND MESSAGE (USER)
     * KARL 2023/02
     * @return void
     */
    public static function sendUserMessageData($room_id, $user_cd, $shop_cd, $content)
    {

        $connection = ConnectionManager::get('default');

        try {
            $sql = "";
            $sql = "INSERT INTO messages (room_id,user_cd, shop_cd, datesent, content, seen, sender)
            VALUES ('" . $room_id . "', '" . $user_cd . "', '" . $shop_cd . "', now(),'" . $content . "', '0', 'user')";

            // SQLの実行
            $connection->query($sql)->fetchAll('assoc');


            $dataBack = "";
            $dataBack = "SELECT * FROM messages where room_id='" . $room_id . "' and content='" . $content . "' order by datesent desc limit 1";


            $query = $connection->query($dataBack)->fetchAll('assoc');

            return $query;
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }

    /**
     * SEND MESSAGE (SHOP)
     * KARL 2023/02
     * @return void
     */
    public static function sendShopMessageData($room_id, $user_cd, $shop_cd, $content)
    {

        $connection = ConnectionManager::get('default');

        try {
            $sql = "";
            $sql = "INSERT INTO messages (room_id,user_cd, shop_cd, datesent, content, seen, sender)
            VALUES ('" . $room_id . "', '" . $user_cd . "', '" . $shop_cd . "', now(),'" . $content . "', '0', 'shop')";

            // SQLの実行
            $connection->query($sql)->fetchAll('assoc');

            //return Data

            $dataBack = "";
            $dataBack .= " SELECT * from messages where room_id='" . $room_id . "' and content='" . $content . "' order by datesent desc limit 1";

            $query =  $connection->query($dataBack)->fetchAll('assoc');

            return $query;
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }

    /**
     * SEND MESSAGE (SHOP)
     * KARL 2023/02
     * @return void
     */
    public static function createRoomID($user_cd, $shop_cd)
    {

        $connection = ConnectionManager::get('default');

        try {
            $sqlInsert = "";
            $sqlInsert .= "INSERT INTO rooms (user_cd, shop_cd)
            VALUES ('" . $user_cd . "', '" . $shop_cd . "')";

            // SQLの実行
            $connection->query($sqlInsert)->fetchAll('assoc');


            //-----------------------------------------------------------------------

            //GET ROOM_ID
            $sql = "";
            $sql .= "select room_id from rooms where user_cd='" . $user_cd . "' and shop_cd='" . $shop_cd . "'";


            $query = $connection->query($sql)->fetchAll('assoc');

            return $query;
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }

    /**
     * Create Room ID for SHOP Announcement
     * KARL 2023/02
     * @return void
     */
    public static function createRoomNewUsers($shop_cd)
    {

        $connection = ConnectionManager::get('default');

        try {
            $sqlInsert = "";
            $sqlInsert .= "
            INSERT INTO rooms (shop_cd, user_cd)
            SELECT '" . $shop_cd . "', user_cd FROM mst0011
            WHERE user_cd NOT IN (SELECT user_cd FROM rooms WHERE shop_cd = '" . $shop_cd . "')";
            //print_r("-----createRoomNewUsers");exit;
            // SQLの実行
            $connection->query($sqlInsert)->fetchAll('assoc');
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }

    /**
     * BROADCAST Message to ALL 
     * KARL 2023/02
     * @return void
     */
    public static function announceMessage($shop_cd, $msgContent)
    {

        $connection = ConnectionManager::get('default');

        try {
            $sqlInsert = "";
            $sqlInsert .= "
            INSERT INTO messages (room_id, user_cd, shop_cd, datesent, content, seen, sender)
            SELECT r.room_id, r.user_cd, r.shop_cd , NOW(), '" . $msgContent . "', '0', 'shop'
            FROM rooms r
            WHERE r.shop_cd = '" . $shop_cd . "'";

            //print_r($sqlInsert);exit;

            // SQLの実行
            $connection->query($sqlInsert)->fetchAll('assoc');
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }

    /*
    * insert Geolocation K(2023/03)
    */
    public static function insertLongLat($shop_cd, $long, $lat, $fullShopAddr)
    {

        $connection = ConnectionManager::get('default');
        // 条件

        $sql   = "";
        $sql   .= " INSERT INTO geolocations (shop_cd, longtitude, latitude, address)  
        VALUES (
        '" . $shop_cd . "', 
        '" . $long . "', 
        '" . $lat . "', 
        '" . $fullShopAddr . "')";

        // SQLの実行
        $connection->query($sql)->fetchAll('assoc');
    }

    /**
     * UPDATE method.【 クーポン側 更新 】　COUPON - EDIT
     * KARL 2023/03
     * @return void
     */
    public static function updateCouponData($table = NULL, $searchParam = NULL, $where = NULL)
    {
        // 
        date_default_timezone_set('Asia/Tokyo');
        // トランザクション
        $connection = ConnectionManager::get('default');

        // 共通のComponentを呼び出す
        $common = new CommonComponent();

        if ($where) {
            $where = " where " . $where;
        }

        //  $connection->begin();

        try {
            // UPDATE 
            $sql2 = "";
            $sql2 = " UPDATE public." . $table . " SET ";
            $sql2 .= "updatetime            = now(), ";
            $sql2 .= "age                   ='" . $searchParam['age'] . "', ";
            $sql2 .= "gender                ='" . $searchParam['gender'] . "', ";
            $sql2 .= "rank                  ='" . $searchParam['rank'] . "', ";
            $sql2 .= "background            ='" . $searchParam['background'] . "', ";
            $sql2 .= "color                 ='" . $searchParam['color'] . "', ";
            $sql2 .= "prefecture            ='" . $searchParam['user_add'] . "', ";
            $sql2 .= "birthday              ='" . $searchParam['birth_month'] . "', ";
            //-------------------------FREE USER-----------------------------------
            $sql2 .= "effect_srt            ='" . $searchParam['effect_srt'] . "', ";
            $sql2 .= "effect_end            ='" . $searchParam['effect_end'] . "', ";
            $sql2 .= "visit_condition       ='" . $searchParam['visit_condition'] . "', ";
            $sql2 .= "coupon_goods          ='" . $searchParam['coupon_goods'] . "', ";
            $sql2 .= "coupon_discount       ='" . $searchParam['coupon_discount'] . "', ";
            $sql2 .= "thumbnail1            ='" . $searchParam['thumbnail1'] . "' ";


            $sql2 .= " " . $where . " ";



            $connection->execute($sql2);
            $connection->commit();
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }

    /**
     * INSERT Coupon Data 【 登録 】
     * K(2023/03)
     * @return void
     */
    public static function insertCouponData($table = NULL, $searchParam = NULL, $paidMemberCheck = null)
    {

        // 
        date_default_timezone_set('Asia/Tokyo');
        // トランザクション
        $connection = ConnectionManager::get('default');

        // 共通のComponentを呼び出す
        $common = new CommonComponent();

        //  $connection->begin();
        try {
            $sql = "";
            if ($paidMemberCheck == 0) { //--not paid member
                $sql .= " INSERT INTO public." . $table . " 
                (
                insuser_cd,
                insdatetime,
                upduser_cd,
                updatetime,
                shop_cd,
                coupon_cd,
                coupon_goods,
                effect_srt,
                effect_end,
                coupon_discount,
                thumbnail1,
                thumbnail2,
                thumbnail3,
                user_cd,
                connect_kbn,
                used,
                background,
                color,
                prefecture,
                age,
                gender,
                birthday,
                visit_condition
                ) 
                VALUES (
                    '" . $searchParam['insuser_cd'] . "',
                    '" . $searchParam['insdatetime'] . "',
                    '" . $searchParam['upduser_cd'] . "',
                    '" . $searchParam['updatetime'] . "',
                    '" . $searchParam['shop_cd'] . "',
                    '" . $searchParam['coupon_cd'] . "',
                    '" . $searchParam['coupon_goods'] . "', 
                    '" . $searchParam['effect_srt'] . "',
                    '" . $searchParam['effect_end'] . "',
                    '" . $searchParam['coupon_discount'] . "', 
                    '" . $searchParam['thumbnail1'] . "',
                    '" . $searchParam['thumbnail2'] . "',
                    '" . $searchParam['thumbnail3'] . "',
                    '" . $searchParam['user_cd'] . "',
                    '" . $searchParam['connect_kbn'] . "',
                    '" . $searchParam['used'] . "',
                    '" . $searchParam['background'] . "',
                    '" . $searchParam['color'] . "',
                    '" . $searchParam['prefecture'] . "', 
                    '" . $searchParam['age'] . "', 
                    '" . $searchParam['gender'] . "',
                    '" . $searchParam['birthday'] . "',
                    '" . $searchParam['visit_condition'] . "'
                    )";




                $connection->execute($sql);
                $connection->commit();
            } else { //-- paid member
                $sql .= " INSERT into public." . $table . "
                (
                    insuser_cd,
                    insdatetime,
                    upduser_cd,
                    updatetime,
                    shop_cd,
                    coupon_cd,
                    coupon_goods,
                    effect_srt,
                    effect_end,
                    coupon_discount,
                    thumbnail1,
                    thumbnail2,
                    thumbnail3,
                    user_cd,
                    connect_kbn,
                    used,
                    background,
                    color,
                    prefecture,
                    age,
                    gender,
                    birthday,
                    rank,
                    visit_condition
                    ) 
                    VALUES (
                        '" . $searchParam['insuser_cd'] . "',
                        '" . $searchParam['insdatetime'] . "',
                        '" . $searchParam['upduser_cd'] . "',
                        '" . $searchParam['updatetime'] . "',
                        '" . $searchParam['shop_cd'] . "',
                        '" . $searchParam['coupon_cd'] . "',
                        '" . $searchParam['coupon_goods'] . "', 
                        '" . $searchParam['effect_srt'] . "',
                        '" . $searchParam['effect_end'] . "',
                        '" . $searchParam['coupon_discount'] . "', 
                        '" . $searchParam['thumbnail1'] . "',
                        '" . $searchParam['thumbnail2'] . "',
                        '" . $searchParam['thumbnail3'] . "',
                        '" . $searchParam['user_cd'] . "',
                        '" . $searchParam['connect_kbn'] . "',
                        '" . $searchParam['used'] . "',
                        '" . $searchParam['background'] . "',
                        '" . $searchParam['color'] . "',
                        '" . $searchParam['prefecture'] . "', 
                        '" . $searchParam['age'] . "', 
                        '" . $searchParam['gender'] . "',
                        '" . $searchParam['birthday'] . "',
                        '" . $searchParam['rank'] . "',
                        '" . $searchParam['visit_condition'] . "'
                        )";

                $connection->execute($sql);
                $connection->commit();
            }
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }

    /**
     * INSERT NEW Coupon Data 【 登録 】- Coupons table----> return currval
     * K(2023/04)
     * @return currval('unique_coupon_cd_seq')
     */
    public static function insertNEWCouponData($table = NULL, $searchParam = NULL, $paidMemberCheck = null)
    {

        // 
        date_default_timezone_set('Asia/Tokyo');
        // トランザクション
        $connection = ConnectionManager::get('default');

        // 共通のComponentを呼び出す
        $common = new CommonComponent();

        //  $connection->begin();
        try {
            $sql = "";
            if ($paidMemberCheck == 0) { //--not paid member
                $sql .= " INSERT INTO public." . $table . " 
                (
                unique_coupon_cd,
                updatetime,
                shop_cd,
                coupon_goods,
                effect_srt,
                effect_end,
                coupon_discount,
                thumbnail1,
                thumbnail2,
                thumbnail3,
                connect_kbn,
                visit_condition
                ) 
                VALUES (
                    nextval('unique_coupon_cd_seq'), 
                    '" . $searchParam['updatetime'] . "',
                    '" . $searchParam['shop_cd'] . "',
                    '" . $searchParam['coupon_goods'] . "', 
                    '" . $searchParam['effect_srt'] . "',
                    '" . $searchParam['effect_end'] . "',
                    '" . $searchParam['coupon_discount'] . "', 
                    '" . $searchParam['thumbnail1'] . "',
                    '" . $searchParam['thumbnail2'] . "',
                    '" . $searchParam['thumbnail3'] . "',
                    '" . $searchParam['connect_kbn'] . "',
                    '" . $searchParam['visit_condition'] . "'
                    )";



                $connection->query($sql)->fetchAll('assoc');

                $currvalSQL = "";
                $currvalReturn = "";

                $currvalSQL .= "SELECT currval('unique_coupon_cd_seq')";
                $currvalReturn = $connection->query($currvalSQL)->fetchAll('assoc');

                return $currvalReturn;
            } else { //-- paid member
                $sql .= " INSERT into public." . $table . "
                (
                    unique_coupon_cd,
                    updatetime,
                    shop_cd,
                    coupon_goods,
                    effect_srt,
                    effect_end,
                    coupon_discount,
                    thumbnail1,
                    thumbnail2,
                    thumbnail3,
                    connect_kbn,
                    background,
                    color,
                    prefecture,
                    age,
                    gender,
                    birthday,
                    rank,
                    visit_condition
                    ) 
                    VALUES (
                        nextval('unique_coupon_cd_seq'), 
                        '" . $searchParam['updatetime'] . "',
                        '" . $searchParam['shop_cd'] . "',
                        '" . $searchParam['coupon_goods'] . "', 
                        '" . $searchParam['effect_srt'] . "',
                        '" . $searchParam['effect_end'] . "',
                        '" . $searchParam['coupon_discount'] . "', 
                        '" . $searchParam['thumbnail1'] . "',
                        '" . $searchParam['thumbnail2'] . "',
                        '" . $searchParam['thumbnail3'] . "',
                        '" . $searchParam['connect_kbn'] . "',
                        '" . $searchParam['background'] . "',
                        '" . $searchParam['color'] . "',
                        '" . $searchParam['user_add'] . "', 
                        '" . $searchParam['age'] . "', 
                        '" . $searchParam['gender'] . "',
                        '" . $searchParam['birthday'] . "',
                        '" . $searchParam['rank'] . "',
                        '" . $searchParam['visit_condition'] . "'
                        )";

                $connection->query($sql)->fetchAll('assoc');

                $currvalSQL = "";
                $currvalReturn = "";

                $currvalSQL .= "SELECT currval('unique_coupon_cd_seq')";
                $currvalReturn = $connection->query($currvalSQL)->fetchAll('assoc');

                return $currvalReturn;
            }
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
    public static function saveRegistered($table = NULL, $searchParam = NULL)
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
     * Coupon Data GET method.【 データ取得 】
     * K(2023/04)
     * @return void
     */
    public static function getCouponData($table = NULL, $unique_coupon_cd = NULL, $shop_cd = NULL)
    {

        $connection = ConnectionManager::get('default');

        $sql   = "";
        $sql   .= "SELECT ";
        $sql   .= " * ";
        $sql   .= " FROM " . $table;
        $sql   .= " WHERE ";
        $sql   .= " unique_coupon_cd          = " . $unique_coupon_cd;
        $sql   .= " AND shop_cd                   = '" . $shop_cd . "' ";

        // SQLの実行
        $query = $connection->query($sql)->fetchAll('assoc');

        return $query;
    }


    /**
     * Coupon Data GET method.【 データ取得 】履歴画面
     * K(2023/04)
     * @return void
     */
    public static function getCouponDataRireki($table = NULL, $shop_cd = NULL)
    {

        $connection = ConnectionManager::get('default');

        $sql   = "";
        $sql   .= "SELECT ";
        $sql   .= " * ";
        $sql   .= " FROM " . $table;
        $sql   .= " WHERE ";
        $sql   .= " shop_cd   = '" . $shop_cd . "' ";

        // SQLの実行
        $query = $connection->query($sql)->fetchAll('assoc');

        return $query;
    }

    /**
     * 新規登録クーポン -- ユーザーごとに作成
     * 
     * K(2023/04)
     * 
     * @return void
     */
    public static function insertCouponTrn($table = NULL, $unique_coupon_id = null, $user_cd = null, $searchParam = null)
    {

        // 
        date_default_timezone_set('Asia/Tokyo');
        // トランザクション
        $connection = ConnectionManager::get('default');

        // Set the transaction timeout to 5 minutes
        $connection->execute('SET LOCAL statement_timeout = 300000');

        // 共通のComponentを呼び出す
        $common = new CommonComponent();


        //$connection->begin();
        try {
            // 登録
            $sql = "";
            $sql .= " INSERT into public." . $table . " 
            (           
            unique_coupon_cd,    
            updatetime,
            user_cd,
            used
            )
            VaLUES
            (    
            " .  $unique_coupon_id . ",
            '" . $searchParam['updatetime'] . "',
            '" . $user_cd . "',
            '" . $searchParam['used'] . "'
            )
            ";

            $connection->query($sql)->fetchAll('assoc'); //RETURN SEQUENCE user_cd

            $connection->commit();
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }

    /**
     * delete coupons.【 削除 】
     * delete coupons_used
     *  K(2023/04)
     * @return void
     */
    public static function couponsDelete($unique_coupon_cd, $shop_cd)
    {
        // トランザクション
        $connection = ConnectionManager::get('default');

        //$connection->begin();
        try {
            // 削除
            $delCouponsSql = " DELETE FROM public.coupons
                                WHERE unique_coupon_cd=" . $unique_coupon_cd . " 
                                AND shop_cd='" . $shop_cd . "'";
            
            $connection->execute($delCouponsSql);

            
            // $delCouponsUsedSql = " DELETE FROM public.coupons_used 
            //                         WHERE unique_coupon_cd=" . $unique_coupon_cd;
             
            // $connection->execute($delCouponsUsedSql);

            $connection->commit();
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }

    public static function updateCouponThumbnail($table = NULL, $unique_cp_val = null, $searchParam = null)
    {

        // 
        date_default_timezone_set('Asia/Tokyo');
        // トランザクション
        $connection = ConnectionManager::get('default');


        // 共通のComponentを呼び出す
        $common = new CommonComponent();

        try {


            $sql = "";
            $sql = " UPDATE public." . $table . " SET ";
            $sql .= "thumbnail1                 ='" . $searchParam['thumbnail1'] . "', ";
            $sql .= "thumbnail2                 ='" . $searchParam['thumbnail2'] . "', ";
            $sql .= "thumbnail3                 ='" . $searchParam['thumbnail3'] . "' ";
            $sql .= "WHERE unique_coupon_cd     =" . $unique_cp_val;


            $connection->execute($sql);

            $connection->commit();
        } catch (Exception $e) {
            $this->Flash->error($e);
            $connection->rollback();
        }
    }
}
