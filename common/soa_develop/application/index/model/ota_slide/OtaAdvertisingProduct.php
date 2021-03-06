<?php

namespace app\index\model\ota_slide;
use app\common\help\Help;
use app\index\model\ota_product\OtaProductInfo;
use app\index\model\ota_product\OtaProductJourney;
use think\Model;

use think\config;
use think\Db;
class OtaAdvertisingProduct extends Model{
    protected $table = 'ota_advertising_product';

    public function initialize()
    {
        parent::initialize();
    }

    public function addOtaAdvertisingProduct($params){

        $t = time();
        if($params['product_title']){
            $data['product_title'] = $params['product_title'];
        }
        if($params['sort']){
            $data['sort'] = $params['sort'];
        }
        if($params['banner_image']){
            $data['banner_image'] = $params['banner_image'];
        }
        if($params['currency_id']){
            $data['currency_id'] = $params['currency_id'];
        }
        if($params['price']){
            $data['price'] = $params['price'];
        }
        if($params['tag_name']){
            $data['tag_name'] = $params['tag_name'];
        }
        if($params['interior_type'] == 2){
            $data['team_product_uuid'] = $params['interior_uuid'];
        }
        if($params['href_type']){
            $data['href_type'] = $params['href_type'];
        }
        if($params['without_href']){
            $data['without_href'] = $params['without_href'];
        }
        if($params['interior_type']){
            $data['interior_type'] = $params['interior_type'];
        }
        if($params['interior_uuid']){
            $data['interior_uuid'] = $params['interior_uuid'];
        }
        $data['ota_advertising_uuid'] = $params['ota_advertising_uuid'];
        $data['website_uuid'] = $params['website_uuid'];
        $data['company_id'] = $params['choose_company_id'];
        $data['status'] = $params['status'];
        $data['update_user_id'] = $params['user_id'];
        $data['update_time'] = $t;
        $data['create_user_id'] = $params['user_id'];
        $data['create_time'] = $t;
        $data['uuid'] = Help::getUuid();

        Db::startTrans();
        try{
            Db::name('ota_advertising_product')->insertGetId($data);

            $result = 1;
            // 提交事务
            Db::commit();

        } catch (\Exception $e) {
            $result = $e->getMessage();
            // 回滚事务
            Db::rollback();
            \think\Response::create(['code' => '400', 'msg' =>$result], 'json')->send();
            exit();

        }

        return $result;
    }

    public function getOneOtaAdvertisingProduct($params){

        $data['ota_advertising_product_id'] = $params['ota_advertising_product_id'];
        $result = $this->table("ota_advertising_product")->where($data)->find();

        return $result;
    }

    public function getOtaAdvertisingProductList($params,$is_count=false,$is_page=false,$page=null,$page_size=20){
        $data = "1=1 ";
        if($params['status'] < 2){
            $data.= " and ota_advertising_product.status= ".$params['status'];
        }
        if(!empty($params['subtitle'])){
            $data.= " and ota_advertising_product.product_title like'%".$params['product_title']."%'";
        }
        if(isset($params['choose_company_id'])){
            $data.= " and ota_advertising_product.company_id = ".$params['choose_company_id'];
        }
        if(isset($params['website_uuid'])) {
            $data .= " and ota_advertising_product.website_uuid= '" . $params['website_uuid'] . "'";
        }
        if(isset($params['ota_advertising_uuid'])){
            $data.= " and ota_advertising_product.ota_advertising_uuid= '".$params['ota_advertising_uuid']."'";
        }
        if($is_count==true){
            $result = $this->table("ota_advertising_product")->where($data)->count();
        }else {
            if ($is_page == true) {

                $result = $this->table("ota_advertising_product")
                    ->join('user','user.user_id = ota_advertising_product.update_user_id', 'left')
                    ->where($data)
                    ->limit($page, $page_size)
                    ->field(['ota_advertising_product.*','user.nickname'])
                    ->order('sort asc')
                    ->select();

            } else {
                $result = $this->table("ota_advertising_product")
                    ->join('currency','currency.currency_id = ota_advertising_product.currency_id', 'left')
                    ->where($data)
                    ->field(['ota_advertising_product.*,currency.symbol,currency.unit,currency.currency_name'])
                    ->order('sort asc')
                    ->select();
                $journey_model = new OtaProductJourney();

                $product_model = new OtaProductInfo();

                foreach ($result as $k=>$v){
                    $journey = $journey_model->getJourneyCountByProductUuid($result[$k]['team_product_uuid']);
                    $result[$k]['day_num'] = $journey ? : 0;

                    $productInfo = $product_model->getInfoByProductUuid($result[$k]['team_product_uuid']);
                    $result[$k]['traffic_icon'] = explode(",", $productInfo['traffic_icon']) ? : [];
                    $result[$k]['slogan'] = $productInfo['slogan'] ? : '';
                    $result[$k]['aviation_icon'] = explode(",", $productInfo['aviation_icon']) ? : [];
                }

            }
        }
        return $result;
    }


    public function updateOtaAdvertisingProductById($params){
        $t = time();

        if(isset($params['product_title'])){
            $data['product_title'] = $params['product_title'];
        }
        if(isset($params['sort'])){
            $data['sort'] = $params['sort'];
        }
        if(isset($params['banner_image'])){
            $data['banner_image'] = $params['banner_image'];
        }
        if(isset($params['currency_id'])){
            $data['currency_id'] = $params['currency_id'];
        }
        if(isset($params['price'])){
            $data['price'] = $params['price'];
        }
        if(isset($params['tag_name'])){
            $data['tag_name'] = $params['tag_name'];
        }
        if($params['interior_type'] == 2){
            $data['team_product_uuid'] = $params['interior_uuid'];
        }
        if(isset($params['href_type'])){
            $data['href_type'] = $params['href_type'];
        }
        if(isset($params['without_href'])){
            $data['without_href'] = $params['without_href'];
        }
        if(isset($params['interior_type'])){
            $data['interior_type'] = $params['interior_type'];
        }
        if(isset($params['interior_uuid'])){
            $data['interior_uuid'] = $params['interior_uuid'];
        }
        $data['company_id'] = $params['choose_company_id'];
        $data['status'] = $params['status'];
        $data['update_user_id'] = $params['user_id'];
        $data['update_time'] = $t;
        Db::startTrans();
        try{
            Db::name('ota_advertising_product')->where("ota_advertising_product_id = ".$params['ota_advertising_product_id'])->update($data);

            $result = 1;
            // 提交事务
            Db::commit();

        } catch (\Exception $e) {
            $result = $e->getMessage();
            // 回滚事务
            Db::rollback();

        }
        return $result;


    }

}