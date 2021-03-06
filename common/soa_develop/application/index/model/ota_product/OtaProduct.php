<?php
namespace app\index\model\ota_product;
use app\common\help\Help;
use app\index\model\system\Country;
use think\Db;
use think\Exception;
use think\Model;

class OtaProduct extends Model{
 
    protected $table = 'ota_product';

    //自动过滤掉不存在的字段
    protected $field = true;

    public function initialize()
    {
        parent::initialize();
    }


    /**
     * 获取产品合集
     * Created by PhpStorm.
     * User: Administrator
     * Date: 2019/5/15
     * Time: 9:53
     * @param $post_data array 查询条件数组
     * @return mixed 返回数据
     */
    public function getProducts($post_data)
    {

        $product_model = new OtaProduct();

        $where = " 1=1 and p.is_del = 0";

        //分类的uuid
        if (isset($post_data['type_uuid']))
        {
            $where .= " and pt.product_type_uuid = '". $post_data['type_uuid']."' ";
        }

        //地区id
        if (isset($post_data['country_id']))
        {
            $country_model = new Country();
            $city_data = $country_model->getProvinceUnder(['province_id' => $post_data['country_id']]);
            if ($city_data)
            {
                $city_ids = implode(',', array_column($city_data, 'country_id'));
                $where .= " and j.country_id in (". $city_ids .")";
            }
        }

        //状态
        if(isset($post_data['status']))
        {
            $where .= " and p.status = ". $post_data['status'];
        }

        if (isset($post_data['is_like']) && $post_data['is_like'] == 1)
        {
            //标题
            if($post_data['title'])
            {
                $where .= " and p.title like '%". $post_data['title'] ."%'";
            }
            //创建人
            if($post_data['create_username'])
            {
                $where .= " and u.username like '%". $post_data['create_username'] ."%'";
            }
        }
        else
        {
            //标题
            if($post_data['title'])
            {
                $where .= " and p.title = ". $post_data['title'];
            }
            //创建人
            if($post_data['create_username'])
            {
                $where .= " and u.username = " . $post_data['create_username'];
            }
        }

        //公司
        if($post_data['company_id'])
        {
            $where .= " and p.company_id  = ". $post_data['company_id'];
        }

        //网站uuid
        if($post_data['website_uuid'])
        {
            $where .= " and p.website_uuid  = '". $post_data['website_uuid'] . "'";
        }

        $where1 = [];
        //目的地uuid
        if (!empty($post_data['destination_uuid']))
        {
            if (is_array($post_data['destination_uuid']))
            {
                $where1['d.ota_product_type_destination_uuid'] = ['in', implode(',', $post_data['destination_uuid'])];
            }
            else
            {
                $where1['d.ota_product_type_destination_uuid'] = $post_data['destination_uuid'];
            }
        }

        //景点uuid
        if (!empty($post_data['scenic_spot_uuid']))
        {
            if (is_array($post_data['scenic_spot_uuid']))
            {
                $where1['s.ota_product_type_scenic_spot_uuid'] = ['in', implode(',', $post_data['scenic_spot_uuid'])];
            }
            else
            {
                $where1['s.ota_product_type_scenic_spot_uuid'] = $post_data['scenic_spot_uuid'];
            }
        }

        //行程天数
        //最小天数
        if (!empty($post_data['day_min']))
        {
            $where .= ' and (select count(the_days) from ota_product_journey j where j.ota_product_uuid = p.uuid) >= '. $post_data['day_min'];

        }
        //最大天数
        if (!empty($post_data['day_max']))
        {
            $where .= ' and (select count(the_days) from ota_product_journey j where j.ota_product_uuid = p.uuid) <= '. $post_data['day_max'];
        }

        //最早出发日期
        if (!empty($post_data['first_begin_time']))
        {
            $where .= ' and tp.begin_time >= ' .$post_data['first_begin_time'];
        }

        //最晚出发日期
        if (!empty($post_data['last_begin_time']))
        {
            $where .= ' and tp.begin_time <= ' .$post_data['last_begin_time'];
        }

        //最低价格
        if (!empty($post_data['price_min']))
        {
            $where .= ' and t.customer_price >= ' .$post_data['price_min'];
        }

        //最高价格
        if (!empty($post_data['price_max']))
        {
            $where .= ' and t.customer_price <= ' .$post_data['price_max'];
        }


        $order = [];
        //综合排序
        if (!empty($post_data['sorting']))
        {
            $order['p.sorting'] = $post_data['sorting'];
        }

        //行程天数排序
        if (!empty($post_data['day_sort']))
        {
            $order['day_num'] = $post_data['day_sort'];
        }

        //最新排序
        if (!empty($post_data['create_time_sort']))
        {
            $order['p.create_time'] = $post_data['create_time_sort'];
        }

        if (!empty($post_data['price_sort']))
        {
            $order['t.original_price'] = $post_data['price_sort'];
        }

        try
        {
            if (isset($post_data['page']) && $post_data['size'])
            {
                $count = $product_model->alias('p')->where($where)->where($where1)
                    ->join('user u', 'u.user_id = p.create_user_id', 'LEFT')
                    ->join('ota_product_specifications o ', 'o.ota_product_uuid = p.uuid', 'LEFT')
                    ->join('ota_product_team_product t ', 't.product_specifications_uuid = o.uuid', 'LEFT')
                    ->join('team_product tp', 'tp.team_product_id = t.team_product_id', 'LEFT')
                    ->join('currency c', 't.currency_id = c.currency_id', 'LEFT')
                    ->join('ota_product_types pt', 'pt.product_uuid = p.uuid', 'LEFT')
                    ->join('ota_product_info i', 'i.ota_product_uuid = p.uuid', 'LEFT')
                    ->join('ota_product_journey j', 'j.ota_product_uuid = p.uuid', 'LEFT')
                    ->join('ota_product_destination d', 'd.ota_product_uuid = p.uuid', 'LEFT')
                    ->join('ota_product_scenic_spot s', 's.ota_product_uuid = p.uuid', 'LEFT')
                    ->group('p.uuid')
                    ->count();

                $list = $product_model->alias('p')
                    ->field([
                    'p.ota_product_id', 'p.uuid', 'p.title', 'p.pviews',
                    'p.sorting', 'p.status', 'u.username as create_username', 't.customer_price', 't.distributor_price', 'i.cover_image', 'c.currency_name', 'c.symbol', 'c.unit', 'i.traffic_icon', 'i.slogan', 'i.aviation_icon',
                    '(select count(the_days) from ota_product_journey j where j.ota_product_uuid = p.uuid)' => 'day_num'
                    ])
                    ->join('user u', 'u.user_id = p.create_user_id', 'LEFT')
                    ->join('ota_product_specifications o ', 'o.ota_product_uuid = p.uuid', 'LEFT')
                    ->join('ota_product_team_product t ', 't.product_specifications_uuid = o.uuid', 'LEFT')
                    ->join('team_product tp', 'tp.team_product_id = t.team_product_id', 'LEFT')
                    ->join('currency c', 't.currency_id = c.currency_id', 'LEFT')
                    ->join('ota_product_types pt', 'pt.product_uuid = p.uuid', 'LEFT')
                    ->join('ota_product_info i', 'i.ota_product_uuid = p.uuid', 'LEFT')
                    ->join('ota_product_journey j', 'j.ota_product_uuid = p.uuid', 'LEFT')
                    ->join('ota_product_destination d', 'd.ota_product_uuid = p.uuid', 'LEFT')
                    ->join('ota_product_scenic_spot s', 's.ota_product_uuid = p.uuid', 'LEFT')
                    ->where($where)->where($where1)->order($order)->group('p.uuid')->limit(($post_data['page']-1)*$post_data['size'], $post_data['size'])->select();
                foreach ($list as $k=>$v)
                {
                    $list[$k]['traffic_icon'] = $v['traffic_icon'] ? explode(',', $v['traffic_icon']) : [];
                    $list[$k]['aviation_icon'] = $v['aviation_icon'] ? explode(',', $v['aviation_icon']) : [];
                }

                $result = [
                    'count' => $count,
                    'list' => $list,
                    'page_count' => ceil($count / $post_data['size'])
                ];
            }
            else
            {

                $result = $product_model->alias('p')
                    ->field([
                        'p.ota_product_id', 'p.uuid', 'p.title', 'p.pviews',
                    'p.sorting', 'p.status', 'u.username as create_username', 't.customer_price', 't.distributor_price', 'i.cover_image', 'c.currency_name', 'c.symbol', 'c.unit', 'i.traffic_icon', 'i.slogan', 'i.aviation_icon',
                    '(select count(the_days) from ota_product_journey j where j.ota_product_uuid = p.uuid)' => 'day_num'
                    ])
                    ->join('user u', 'u.user_id = p.create_user_id', 'LEFT')
                    ->join('ota_product_specifications o ', 'o.ota_product_uuid = p.uuid', 'LEFT')
                    ->join('ota_product_team_product t ', 't.product_specifications_uuid = o.uuid', 'LEFT')
                    ->join('team_product tp', 'tp.team_product_id = t.team_product_id', 'LEFT')
                    ->join('currency c', 't.currency_id = c.currency_id', 'LEFT')
                    ->join('ota_product_types pt', 'pt.product_uuid = p.uuid', 'LEFT')
                    ->join('ota_product_info i', 'i.ota_product_uuid = p.uuid', 'LEFT')
                    ->join('ota_product_journey j', 'j.ota_product_uuid = p.uuid', 'LEFT')
                    ->join('ota_product_destination d', 'd.ota_product_uuid = p.uuid', 'LEFT')
                    ->join('ota_product_scenic_spot s', 's.ota_product_uuid = p.uuid', 'LEFT')
                    ->where($where)->where($where1)->order($order)->group('p.uuid')->select();
                foreach ($result as $k=>$v)
                {
                    $result[$k]['traffic_icon'] = $v['traffic_icon'] ? explode(',', $v['traffic_icon']) : [];
                    $result[$k]['aviation_icon'] = $v['aviation_icon'] ? explode(',', $v['aviation_icon']) : [];
                }
            }

        }
        catch (Exception $e)
        {
            error_log(print_r($e->getMessage(),1));
        }

        return $result;
    }


    public function getProductByUuid($uuid)
    {
        $info =  $this->alias('p')
            ->field('p.*, group_concat(t.ota_product_type_id) as type_ids')
            ->join('ota_product_types pt', 'p.uuid = pt.product_uuid')
            ->join('ota_product_type t', 't.uuid = pt.product_type_uuid')
            ->where('p.uuid = "'. $uuid.'"')->group('p.uuid')->find();
        if ($info)
        {
            $info['type_ids'] = explode(',', $info['type_ids']);

            try
            {
                $info['destination'] = Db::table('ota_product_destination')->alias('p')
                    ->field('t.uuid, t.destination_name')
                    ->join('ota_product_type_destination t', 'p.ota_product_type_destination_uuid = t.uuid')
                    ->where(['p.ota_product_uuid' => $info['uuid']])->column('uuid');

                $info['scenic_spot'] = Db::table('ota_product_scenic_spot')->alias('p')
                    ->field('t.uuid, t.scenic_spot_name')
                    ->join('ota_product_type_scenic_spot t', 'p.ota_product_type_scenic_spot_uuid = t.uuid')
                    ->where(['p.ota_product_uuid' => $info['uuid']])->column('uuid');
            }

            catch (Exception $e)
            {
                error_log(print_r($e->getMessage(),1));die;
            }

        }
        return $info;
    }


    public function add($params, $user_id)
    {
        if($params['title']){
            $data['title'] = $params['title'];
        }
        if($params['sorting']){
            $data['sorting'] = $params['sorting'];
        }
        if($params['visibility']){
            $data['visibility'] = $params['visibility'];
        }

        if($params['product_tag']){
            $data['product_tag'] = $params['product_tag'];
        }

        if($params['company_id']){
            $data['company_id'] = $params['company_id'];
        }

        $data['is_del'] = 0;
        $data['website_uuid'] = $params['website_uuid'];
        $data['status'] = $params['status'];
        $data['create_user_id'] = $user_id;
        $data['create_time'] = time();
        $data['uuid'] = $params['uuid'];
        if ($this->insert($data) === false) return false;
        if ($this->__addProductsType($params['uuid'], $params['type_ids']) === false) return false;
        if ($this->addDestination($params['destination_uuid'], $data['uuid']) === false) return false;
        if ($this->addScenicSpot($params['scenic_spot_uuid'], $data['uuid']) === false) return false;

    }

    public function edit($params, $user_id)
    {

        if(!empty($params['title'])){
            $data['title'] = $params['title'];
        }
        if(is_numeric($params['sorting'])){
            $data['sorting'] = $params['sorting'];
        }
        if(is_numeric($params['visibility'])){
            $data['visibility'] = $params['visibility'];
        }

        if(!empty($params['product_tag'])){
            $data['product_tag'] = $params['product_tag'];
        }

        if(is_numeric($params['user_company_id'])){
            $data['company_id'] = $params['user_company_id'];
        }

        if(is_numeric($params['status'])){
            $data['status'] = $params['status'];
        }

        if(is_numeric($params['is_del'])){
            $data['is_del'] = $params['is_del'];
        }

        if(is_numeric($params['delete_user_id'])){
            $data['delete_user_id'] = $params['delete_user_id'];
        }

        if(is_numeric($params['delete_time'])){
            $data['delete_time'] = $params['delete_time'];
        }

        $where['uuid'] = $params['uuid'];
        if ($params['website_uuid'])
        {
            $where['website_uuid'] = $params['website_uuid'];
        }

        $data['update_user_id'] = $user_id;
        $data['update_time'] = time();
        if ($this->update($data, $where) === false) return false;

        if (isset($params['type_ids']))
        {
            if ($this->__addProductsType($params['uuid'], $params['type_ids']) === false) return false;
        }


        if ($this->addDestination($params['destination_uuid'], $params['uuid']) === false) return false;
        if ($this->addScenicSpot($params['scenic_spot_uuid'], $params['uuid']) === false) return false;

        return true;
    }

    /**
     * 添加产品和类型关联数据
     * Created by PhpStorm.
     * User: Administrator
     * Date: 2019/5/20
     * Time: 13:39
     * @param $product_uuid 产品uuid
     * @param $type_ids 关联的多个类型id合集
     * return bool
     */
    private function __addProductsType($product_uuid, $type_ids)
    {
        Db::table('ota_product_types')->where(['product_uuid' => $product_uuid])->delete();


        //存取列表对应的分类合集
        $type_id_arr = explode(',', $type_ids);
        $type_all = Db::table('ota_product_type')->where(['status' => 1])->select();
        $id_arr = [];

        foreach ($type_id_arr as $v)
        {
            $arr = Help::toTree($type_all, $v, 0, 'ota_product_type_id');
            $id_arr = array_merge($id_arr, array_column($arr,'ota_product_type_id'));
            array_push($id_arr, $v);
        }
        $type_id_arr = array_unique($id_arr);

        $product_type_model = new OtaProductType();
        $data = [];
        foreach ($type_id_arr as $v)
        {
            $arr = [];
            $arr['product_type_uuid'] = $product_type_model->where(['ota_product_type_id' => $v])->value('uuid');
            $arr['product_uuid'] = $product_uuid;
            array_push($data, $arr);
        }
        return Db::table('ota_product_types')->insertAll($data);
    }


    public function addDestination($destination, $product_uuid)
    {
        Db::table('ota_product_destination')->where(['ota_product_uuid' => $product_uuid])->delete();

        if ($destination)
        {
            $destination_arr = [];
            foreach ($destination as $v)
            {
                $arr = [];
                $arr['ota_product_type_destination_uuid'] = $v;
                $arr['ota_product_uuid'] = $product_uuid;
                array_push($destination_arr, $arr);
            }
            return Db::table('ota_product_destination')->insertAll($destination_arr);
        }
        return true;
    }

    public function addScenicSpot($scenic_spot, $product_uuid)
    {
        Db::table('ota_product_scenic_spot')->where(['ota_product_uuid' => $product_uuid])->delete();
        if ($scenic_spot)
        {
            $scenic_spot_arr = [];
            foreach ($scenic_spot as $v)
            {
                $arr = [];
                $arr['ota_product_type_scenic_spot_uuid'] = $v;
                $arr['ota_product_uuid'] = $product_uuid;
                array_push($scenic_spot_arr, $arr);
            }
            return Db::table('ota_product_scenic_spot')->insertAll($scenic_spot_arr);
        }

        return true;

    }
}