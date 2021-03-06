<?php 
namespace app\index\model\branchcompany;
use think\Model;
use app\common\help\Help;
use think\config;
use think\Db;

class BranchProductSource extends Model
{
   // protected $connection = ['database' => 'erp'];
    protected $table = 'branch_product_source';
    private $_languageList;

    public function initialize()
    {
        $this->_languageList = config('systom_setting')['language_list'];
        parent::initialize();

    }

    /**
     * 添加分公司产品 资源
     * 王
     */
    public function addBranchProductSource($params)
    {
        $t = time();
	
        $data['branch_product_number'] = $params['branch_product_number'];
        $data['supplier_type_id'] = $params['supplier_type_id'];
        $data['source_number'] = $params['source_number'];
        $data['source_name'] = $params['source_name'];
        $data['source_cost'] = $params['source_cost'];
        $data['source_distributor_price'] = $params['source_distributor_price'];
        $data['source_customer_price'] = $params['source_customer_price'];
        $data['cost_currency_id'] = (float)$params['cost_currency_id'];
        $data['price_currency_id'] = (float)$params['price_currency_id'];
        $data['source_id'] = $params['source_id'];

        if(!empty($params['branch_product_team_id'])){
        	$data['branch_product_team_id'] = $params['branch_product_team_id'];
        }
        if(!empty($params['team_product_number'])){
        	$data['team_product_number'] = $params['team_product_number'];
        }
        $data['is_team_product'] = $params['is_team_product'];
        $data['supplier_name'] = $params['supplier_name'];
        $data['create_time'] = $t;
        $data['create_user_id'] = $params['now_user_id'];
        $data['update_time'] = $t;
        $data['update_user_id'] = $params['now_user_id'];
        $data['status'] =1;

        $this->startTrans();
        try{
            $pk_id = $this->insertGetId($data);
            $pk_number = $data['branch_product_number'];
 
            // 提交事务
            $this->commit();
            $result = $pk_number;

        } catch (\Exception $e) {
            $result = $e->getMessage();
            // 回滚事务
            $this->rollback();

        }

         return $result;
    }
    /**
     * 获取所属分公司资源
     * 王
     */
    public function getOwnCompanySource($params)
    {
        $data = "1=1";

        if (!empty($params['supplier_type_id'])) {
            $data .= " and supplier_type.supplier_type_id = " . $params['supplier_type_id'];
        }
        $result = $this->table("supplier_type")->alias("supplier_type")->
        where($data)->
        field(['*',
            "(select nickname  from user where user.user_id = supplier_type.create_user_id)" => 'create_user_name',
            "(select nickname  from user where user.user_id = supplier_type.update_user_id)" => 'update_user_name',

        ])->select();
        foreach ($result as $key => $val) {
            $supplier_type_id = $val['supplier_type_id'];
            unset($result[0]);
            if ($val['supplier_type_id'] == 2) {
                $hotel_result = Db::table("supplier")->where(array("supplier_type_id" => $supplier_type_id))->
                join("hotel", "hotel.supplier_id = supplier.supplier_id", 'left')->
                join("company", "company.company_id = hotel.company_id", 'left')->
                select();
                if (count($hotel_result) > 0) {
                    $content = '[';
                    foreach ($hotel_result as $key_o => $val_o) {
                        $content .= '{"supplier_name":"' . $val_o['supplier_name']
                            . '","room_name":"' . $val_o['room_name']
                            . '","company_name":"' . $val_o['company_name']
                            . '","product_type":"' . "酒店"
                            . '"},';
                    }
                    $content = substr($content, 0, strlen($content) - 1);
                    $content .= ']';
                    $result[$key]['hotel_array'] = $content;
                }
            } else if ($val['supplier_type_id'] == 3) {
                $dining_result = Db::table("supplier")->where(array("supplier_type_id" => $supplier_type_id))->
                join("dining", "dining.supplier_id = supplier.supplier_id", 'left')->
                join("company", "company.company_id = dining.company_id", 'left')->select();
                if (count($dining_result) > 0) {
                    $content = '[';
                    foreach ($dining_result as $key_o => $val_o) {
                        $content .= '{"supplier_name":"' . $val_o['supplier_name']
                            . '","dining_name":"' . $val_o['dining_name']
                            . '","company_name":"' . $val_o['company_name']
                            . '","product_type":"' . "用餐"
                            . '"},';
                    }
                    $content = substr($content, 0, strlen($content) - 1);
                    $content .= ']';
                    $result[$key]['dining_array'] = $content;
                }
            } else if ($val['supplier_type_id'] == 4) {
                $flight_result = Db::table("supplier")->where(array("supplier_type_id" => $supplier_type_id))->
                join("flight", "flight.supplier_id = supplier.supplier_id", 'left')->
                join("company", "company.company_id = flight.company_id", 'left')->select();
                if (count($flight_result) > 0) {
                    $content = '[';
                    foreach ($flight_result as $key_o => $val_o) {
                        $content .= '{"supplier_name":"' . $val_o['supplier_name']
                            . '","flight_number":"' . $val_o['flight_number']
                            . '","company_name":"' . $val_o['company_name']
                            . '","product_type":"' . "航班"
                            . '"},';
                    }
                    $content = substr($content, 0, strlen($content) - 1);
                    $content .= ']';
                    $result[$key]['flight_array'] = $content;
                }
            } else if ($val['supplier_type_id'] == 5) {
                $cruise_result = Db::table("supplier")->where(array("supplier_type_id" => $supplier_type_id))->
                join("cruise", "cruise.supplier_id = supplier.supplier_id", 'left')->
                join("company", "company.company_id = cruise.company_id", 'left')->select();
                if (count($cruise_result) > 0) {
                    $content = '[';
                    foreach ($cruise_result as $key_o => $val_o) {
                        $content .= '{"supplier_name":"' . $val_o['supplier_name']
                            . '","cruise_name":"' . $val_o['cruise_name']
                            . '","company_name":"' . $val_o['company_name']
                            . '","product_type":"' . "邮轮"
                            . '"},';
                    }
                    $content = substr($content, 0, strlen($content) - 1);
                    $content .= ']';
                    $result[$key]['cruise_array'] = $content;
                }
            } else if ($val['supplier_type_id'] == 6) {
                $visa_result = Db::table("supplier")->where(array("supplier_type_id" => $supplier_type_id))->
                join("visa", "visa.supplier_id = supplier.supplier_id", 'left')->
                join("company", "company.company_id = visa.company_id", 'left')->select();
                if (count($visa_result) > 0) {
                    $content = '[';
                    foreach ($visa_result as $key_o => $val_o) {
                        $content .= '{"supplier_name":"' . $val_o['supplier_name']
                            . '","company_name":"' . $val_o['company_name']
                            . '","visa_name":"' . $val_o['visa_name']
                            . '","product_type":"' . "签证"
                            . '"},';
                    }
                    $content = substr($content, 0, strlen($content) - 1);
                    $content .= ']';
                    $result[$key]['visa_array'] = $content;
                }
            } else if ($val['supplier_type_id'] == 7) {
                $scenic_spot_result = Db::table("supplier")->where(array("supplier_type_id" => $supplier_type_id))->
                join("scenic_spot", "scenic_spot.supplier_id = supplier.supplier_id", 'left')->
                join("company", "company.company_id = scenic_spot.company_id", 'left')->select();
                if (count($scenic_spot_result) > 0) {
                    $content = '[';
                    foreach ($scenic_spot_result as $key_o => $val_o) {
                        $content .= '{"supplier_name":"' . $val_o['supplier_name']
                            . '","scenic_spot_name":"' . $val_o['scenic_spot_name']
                            . '","company_name":"' . $val_o['company_name']
                            . '","product_type":"' . "景点"
                            . '"},';
                    }
                    $content = substr($content, 0, strlen($content) - 1);
                    $content .= ']';
                    $result[$key]['scenic_spot_array'] = $content;
                }
            } else if ($val['supplier_type_id'] == 8) {
                $vehicle_result = Db::table("supplier")->where(array("supplier_type_id" => $supplier_type_id))->
                join("vehicle", "vehicle.supplier_id = supplier.supplier_id", 'left')->
                join("company", "company.company_id = vehicle.company_id", 'left')->select();
                if (count($vehicle_result) > 0) {
                    $content = '[';
                    foreach ($vehicle_result as $key_o => $val_o) {
                        $content .= '{"supplier_name":"' . $val_o['supplier_name']
                            . '","vehicle_name":"' . $val_o['vehicle_name']
                            . '","company_name":"' . $val_o['company_name']
                            . '","product_type":"' . "车辆"
                            . '"},';
                    }
                    $content = substr($content, 0, strlen($content) - 1);
                    $content .= ']';
                    $result[$key]['vehicle_array'] = $content;
                }
            } else if ($val['supplier_type_id'] == 9) {
                $tour_guide_result = Db::table("supplier")->where(array("supplier_type_id" => $supplier_type_id))->
                join("tour_guide", "tour_guide.supplier_id = supplier.supplier_id", 'left')->
                join("company", "company.company_id = tour_guide.company_id", 'left')->select();
                if (count($tour_guide_result) > 0) {
                    $content = '[';
                    foreach ($tour_guide_result as $key_o => $val_o) {
                        $content .= '{"supplier_name":"' . $val_o['supplier_name']
                            . '","company_name":"' . $val_o['company_name']
                            . '","tour_guide_name":"' . $val_o['tour_guide_name']
                            . '","product_type":"' . "导游"
                            . '"},';
                    }
                    $content = substr($content, 0, strlen($content) - 1);
                    $content .= ']';
                    $result[$key]['tour_guide_array'] = $content;
                }
            } else if ($val['supplier_type_id'] == 10) {
                $single_source_result = Db::table("supplier")->where(array("supplier_type_id" => $supplier_type_id))->
                join("single_source", "single_source.supplier_id = supplier.supplier_id", 'left')->
                join("company", "company.company_id = single_source.company_id", 'left')->select();
                if (count($single_source_result) > 0) {
                    $content = '[';
                    foreach ($single_source_result as $key_o => $val_o) {
                        $content .= '{"supplier_name":"' . $val_o['supplier_name']
                            . '","single_source_name":"' . $val_o['single_source_name']
                            . '","company_name":"' . $val_o['company_name']
                            . '","product_type":"' . "单项资源"
                            . '"},';
                    }
                    $content = substr($content, 0, strlen($content) - 1);
                    $content .= ']';
                    $result[$key]['single_source_array'] = $content;
                }
            }else if ($val['supplier_type_id'] == 11) {
                $own_expense_result = Db::table("supplier")->where(array("supplier_type_id" => $supplier_type_id))->
                join("own_expense", "own_expense.supplier_id = supplier.supplier_id", 'left')->
                join("company", "company.company_id = own_expense.company_id", 'left')->select();
                if (count($own_expense_result) > 0) {
                    $content = '[';
                    foreach ($own_expense_result as $key_o => $val_o) {
                        $content .= '{"supplier_name":"' . $val_o['supplier_name']
                            . '","own_expense_name":"' . $val_o['own_expense_name']
                            . '","company_name":"' . $val_o['company_name']
                            . '","product_type":"' . "自费项目"
                            . '"},';
                    }
                    $content = substr($content, 0, strlen($content) - 1);
                    $content .= ']';
                    $result[$key]['own_expense_array'] = $content;
                }
            }
        }


        return $result;
    }
    /**
     * 获取分公司产品资源
     * 王
     */             
    public function getBranchProductSource($params){

        $data = "1=1";
        if(isset($params['branch_product_source_id'])){
            $data.= " and branch_product_source.branch_product_source_id = '".$params['branch_product_source_id']."'";
        }
        if(isset($params['branch_product_number'])){
            $data.= " and branch_product_source.branch_product_number = '".$params['branch_product_number']."'";
        }
        if(isset($params['team_product_number'])){
            $data.= " and branch_product_source.team_product_number = '".$params['team_product_number']."'";
        }
        if(isset($params['is_team_product'])){
            $data.= " and branch_product_source.is_team_product = '".$params['is_team_product']."'";
        }
        if(isset($params['supplier_type_id'])){
            $data.= " and branch_product_source.supplier_type_id = '".$params['supplier_type_id']."'";
        }
        if(isset($params['source_id'])){
            $data.= " and branch_product_source.source_id = '".$params['source_id']."'";
        }
        if(isset($params['supplier_name'])){
            $data.= " and branch_product_source.supplier_name = '".$params['supplier_name']."'";
        }
        if(isset($params['source_number'])){
            $data.= " and branch_product_source.source_number = '".$params['source_number']."'";
        }
        if(isset($params['source_name'])){
            $data.= " and branch_product_source.source_name = '".$params['source_name']."'";
        }
        if(isset($params['supplier_name'])){
            $data.= " and branch_product_source.supplier_name = '".$params['supplier_name']."'";
        }
        
        if(isset($params['company_order_product_team_id'])){
        	$data.= " and branch_product_source.company_order_product_team_id = ".$params['company_order_product_team_id'];
        }        
        
        
        if(is_numeric($params['status'])){
            $data.= " and branch_product_source.status = ".$params['status'];
        }
        $result = $this->table("branch_product_source")->alias('branch_product_source')->
        where($data)->
        field(['branch_product_source.branch_product_source_id','branch_product_source.branch_product_number',
            'branch_product_source.team_product_number','branch_product_source.is_team_product',
            "branch_product_source.supplier_type_id","branch_product_source.source_id","branch_product_source.source_name",
            "branch_product_source.source_number","branch_product_source.supplier_name",
            'branch_product_source.status',
             "(select nickname  from user where user.user_id = branch_product_source.create_user_id)"=>'create_user_name',
            "(select nickname  from user where user.user_id = branch_product_source.update_user_id)"=>'update_user_name'
        ])->select();

        return $result;


    }
    
    /**
     * 修改分公司产品 根据branch_product_number
     */
    public function updateBranchProductSourceByBranchProductSourceId($params){

        $t = time();

        if(is_numeric($params['status'])){
            $data['status'] = $params['status'];

        }
        $data['update_user_id'] = $params['user_id'];

        $data['update_time'] = $t;




        $this->startTrans();
        try{
            Db::table('branch_product_source')->where(" branch_product_source_id = ".$params['branch_product_source_id'])->update($data);

            $result = 1;
            // 提交事务
            $this->commit();

        } catch (\Exception $e) {
            $result = $e->getMessage();
            // 回滚事务
            $this->rollback();

        }
        return $result;
    }
    /**
     * 获取分公司产品的资源
     */
    public function getBranchProductSource2($params){
    
    	$data = 'status = 1 ';
    	if(!empty($params['branch_product_number'])){
    		$data.= " and branch_product_number ='".$params['branch_product_number']."'";
    	}
    	if(!empty($params['supplier_type_id'])){
    		$data.= " and supplier_type_id = ".$params['supplier_type_id'];
    	}
    	if(!empty($params['team_product_number'])){
    		$data.= " and team_product_number = '".$params['team_product_number']."'";
    	}
    	if(is_numeric($params['is_team_product'])){
    		$data.= " and is_team_product = '".$params['is_team_product']."'";
    	}

    	$result = $this->where($data)->select();

    	//获取货币名字
    	for($i=0;$i<count($result);$i++){
    	    $price_currency_id = $result[$i]['price_currency_id'];
            $source = Db::table('currency')->where(array("currency_id" => $price_currency_id))->select();
            $result[$i]['price_currency_name']=$source[0]['currency_name'];
            $price_currency_id = $result[$i]['cost_currency_id'];
            $source = Db::table('currency')->where(array("currency_id" => $price_currency_id))->select();
            $result[$i]['cost_currency_name']=$source[0]['currency_name'];
    	}

    	return $result;
    }
    //修改报价与货币根据主键
    public function updateBranchProductSourcePriceAndCurrencyIdByBranchProductSourceId($params){
    	$t = time();
    
    
    	$data['price_currency_id'] = $params['price_currency_id'];
    	$data['source_price'] = $params['branch_price'];
    
    
    	$data['update_time'] = $t;
    
    	$data_where['branch_product_source_id'] = $params['branch_product_source_id'];
    
    	$this->startTrans();
    	try{
    		$this->where($data_where)->update($data);
    
    		$result = 1;
    		// 提交事务
    		$this->commit();
    
    	} catch (\Exception $e) {
    		$result = $e->getMessage();
    		// 回滚事务
    		$this->rollback();
    
    	}
    	return $result;
    
    }
}