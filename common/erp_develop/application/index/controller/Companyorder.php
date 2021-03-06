<?php
/**
 * Created by PhpStorm.
 * User: godwei
 * Date: 2018/9/20
 * Time: 16:40
 */

namespace app\index\controller;
use \Underscore\Types\Arrays;
use think\Session;
use think\Paginator;
use think\Request;
use think\Controller;
use app\common\help\Help;

class CompanyOrder extends Base
{
	/**
	 * 添加订单基础信息AJAX
	 */
    public function addCompanyOrderAjax(){
    	$params = Request::instance()->param();
    	
    	if(!empty($params['end_time'])){
    		$params['end_time'] = strtotime($params['end_time']);
    	}
    	$params['begin_time'] = strtotime($params['begin_time']);
    	
    	$params['buy_order_time'] = strtotime($params['buy_order_time']);



    	$result = $this->callSoaErp('post', '/branchcompany/addCompanyOrder', $params);
  
    	return $result;
    }
    /**
     * 获取公司订单Ajax
     */
    public function getCompanyOrderAjax(){
    	$params = Request::instance()->param();
		
    	$params['company_id'] = session('user')['company_id'];
    
    	$result = $this->callSoaErp('post', '/branchcompany/getCompanyOrder', $params);
    	return $result;
    	
    }
    /**
     * 修改订单AJAX
     */
    public function updateCompanyOrderByCompanyOrderNumberAjax(){
    	$params = Request::instance()->param();
    	$params['begin_time'] = strtotime($params['begin_time']);
    	
    	if(!empty($params['end_time'])){
    		$params['end_time'] = strtotime($params['end_time']);
    	}
    	if(!empty($params['buy_order_time'])){
    		$params['buy_order_time'] = strtotime($params['buy_order_time']);
    	}

        $where['system_alert_event_id'] = 16;
        $where['company_id'] = session('user')['company_id'];
        $where['company_order_number'] = $params['company_order_number'];
        $this->callSoaErp('post','/system_alert_event/addInStationLetterAndEmail',$where);


    	$result = $this->callSoaErp('post', '/branchcompany/updateCompanyOrderByCompanyOrderNumber', $params);
    	 
    	return $result;
    }
    
    /**
     * 修改公司订单状态AJAX
     */
    public function updateCompanyOrderStatusByCompanyOrderNumberAjax(){
    	$params = Request::instance()->param();

    
    	$result = $this->callSoaErp('post', '/branchcompany/updateCompanyOrderByCompanyOrderNumber', $params);
    
    	return $result;
    }    
    
    /**
     * 添加订单游客 的占位
     */
    public function addCompanyOrderCustomerOccupyAjax(){
    	$params = Request::instance()->param();
    
    	$result = $this->callSoaErp('post', '/branchcompany/addCompanyOrderCustomerOccupy', $params);
    	
    	return $result;
    }
    
    /**
     * 添加订单游客
     */
    public function addCompanyOrderCustomerAjax(){
    	
    	$params = Request::instance()->param();
    	
    
    	$customer_flight= json_decode(input('customer_flight'),true);
    	$issuing_date = $params['issuing_date'];
    	$term_of_validity = $params['term_of_validity'];
    	$birthday = $params['birthday'];
    	if(!empty($issuing_date)){
    		$params['issuing_date'] = strtotime($params['issuing_date']);
    	}
    	if(!empty($term_of_validity)){
    		$params['term_of_validity'] = strtotime($params['term_of_validity']);
    	}
    	if(!empty($birthday)){
    		$params['birthday'] = strtotime($params['birthday']);
    	}
    	for($i=0;$i<count($customer_flight);$i++){
    		$begin_time = $customer_flight[$i]['flight_begin_time'];
    		$end_time = $customer_flight[$i]['flight_end_time'];
    		
    		if(!empty($begin_time)){
    			$customer_flight[$i]['flight_begin_time'] = strtotime($begin_time);
    		}
    		
    		if(!empty($end_time)){
    			$customer_flight[$i]['flight_end_time'] = strtotime($end_time);
    		}
    	}
    	$params['customer_flight'] = $customer_flight;
    	$result = $this->callSoaErp('post', '/branchcompany/addCompanyOrderCustomer', $params);
    	 
    	return $result;
    
    }
    /**
     * 获取订单游客
     */
    public function getCompanyOrderCustomerAjax(){
    	
    	$params = Request::instance()->param();
    
    	$result = $this->callSoaErp('post', '/branchcompany/getCompanyOrderCustomer', $params);
    	
    	return $result;
    	
    	
    }
    /**
     * 修改公司订单游客
     */
    public function updateCompanyOrderCustomerByCompanyOrderCustomerIdAjax(){
    	$params = Request::instance()->param();
    	$customer_flight= json_decode(input('customer_flight'),true);
    	$issuing_date = $params['issuing_date'];
    	$term_of_validity = $params['term_of_validity'];
    	$birthday = $params['birthday'];
    	if(!empty($issuing_date)){
    		$params['issuing_date'] = strtotime($params['issuing_date']);
    	}
    	if(!empty($term_of_validity)){
    		$params['term_of_validity'] = strtotime($params['term_of_validity']);
    	}
    	if(!empty($birthday)){
    		$params['birthday'] = strtotime($params['birthday']);
    	}
    	for($i=0;$i<count($customer_flight);$i++){
    		$begin_time = $customer_flight[$i]['flight_begin_time'];
    		$end_time = $customer_flight[$i]['flight_end_time'];
    		if(!empty($begin_time)){
    			$customer_flight[$i]['flight_begin_time'] = strtotime($begin_time);
    		}
    	
    		if(!empty($end_time)){
    			$customer_flight[$i]['flight_end_time'] = strtotime($end_time);
    		}
    	}
    	$params['customer_flight'] = $customer_flight;
    	
    	$result = $this->callSoaErp('post', '/branchcompany/updateCompanyOrderCustomerByCompanyOrderCustomerId', $params);
    	 
    	return $result;
    	
    }
    /**
     * 修改公司订单游客状态
     */
    public function updateCompanyOrderCustomerStatusByCompanyOrderCustomerIdAjax(){
    	$params = Request::instance()->param();
    	 
    	$result = $this->callSoaErp('post', '/branchcompany/updateCompanyOrderCustomerStatusByCompanyOrderCustomerId', $params);
    	
    	return $result;
    	
    	
    }
    /**
     * 添加公司订单产品 不包含自定义
     */
    public function addCompanyOrderProductAjax(){
    	$params = Request::instance()->param();
    	$params['branch_product_array'] = json_decode(input('branch_product_array'),true);
    	
    	$params['team_product_array'] = json_decode(input('team_product_array'),true);
    	
    	
    	$result = $this->callSoaErp('post', '/branchcompany/addCompanyOrderProduct', $params);
    	 
    	return $result;
    }
    
    /**
     * 修改公司订单产品
     */
    public function updateCompanyOrderProductAjax(){
    	$params = Request::instance()->param();
    	
    	$result = $this->callSoaErp('post', '/branchcompany/updateCompanyOrderProduct', $params);
    
    	return $result;
    }
    
    /**
     * 添加公司自定义产品
     */
    public function addCompanyOrderProductDiyAjax(){
    	$params = Request::instance()->param();
    	$result = $this->callSoaErp('post', '/branchcompany/addCompanyOrderProductDiy', $params);
    	
    	return $result;
    	
    }
    
    /**
     * 修改公司订单成本与报价
     */
    public function updateCompanyOrderCostAndPriceAjax(){
 
    	
    	$team_product_array = json_decode(input('team_product_array'),true);
    	$source_array = json_decode(input('source_array'),true);
    	$diy_array = json_decode(input('company_order_product_diy_price'),true);
    	
    	$branch_product_array = json_decode(input('branch_product_array'),true);    
    	$company_order_number = input('company_order_number');
    	
    	for($i=0;$i<count($branch_product_array);$i++){

    		$substribe_time = $branch_product_array[$i]['substribe_time'];
    		if(!empty($substribe_time)){
    			$branch_product_array[$i]['substribe_time'] = strtotime($substribe_time);
    		}
    	}
    	
    	for($i=0;$i<count($team_product_array);$i++){
    		$invoice_time = $team_product_array[$i]['invoice_time'];
    		
    		if(!empty($invoice_time)){
    			$team_product_array[$i]['invoice_time'] = strtotime($invoice_time);
    		}    
    		$substribe_time = $team_product_array[$i]['substribe_time'];
    		if(!empty($substribe_time)){
    			$team_product_array[$i]['substribe_time'] = strtotime($substribe_time);
    		}
    	}
 
    	for($j=0;$j<count($source_array);$j++){
    		$invoice_time = $source_array[$j]['invoice_time'];
    		if(!empty($invoice_time)){
    			$source_array[$j]['invoice_time'] = strtotime($invoice_time);
    		}
    		$substribe_time = $source_array[$j]['substribe_time'];
    											 
    	
    		if(!empty($substribe_time)){
    			$source_array[$j]['substribe_time'] = strtotime($substribe_time);
    		}
    	}
    	for($k=0;$k<count($diy_array);$k++){
    		$invoice_time = $diy_array[$k]['invoice_time'];
    		if(!empty($invoice_time)){
    			$diy_array[$k]['invoice_time'] = strtotime($invoice_time);
    		}
    		$substribe_time = $diy_array[$k]['substribe_time'];
    		if(!empty($substribe_time)){
    			$diy_array[$k]['substribe_time'] = strtotime($substribe_time);
    		}
    	}
    	$data = [
    		"team_product_array" => $team_product_array,
    		"source_array" => $source_array,
    		"company_order_product_diy_price" => $diy_array,
    		'branch_product_array' => $branch_product_array,
    		'company_order_number'=>$company_order_number,

    	
    	];
    
    
    	$result = $this->callSoaErp('post', '/branchcompany/updateCompanyOrderCostAndPrice', $data);
    	 
    	return $result;
    	 
    }
    
    
   /**
    * 获取公司订单产品
    */
    public function getCompanyOrderProductAjax(){
    	$params = Request::instance()->param();

    	$result = $this->callSoaErp('post', '/branchcompany/getCompanyOrderProduct', $params);
    	
    	return $result;
    }
    /**
     * 生成公司订单的应收与应付
     */
    public function updateCompanyOrderReveivableAndCopeAjax(){
    	$params = Request::instance()->param();
    	$result = $this->callSoaErp('post', '/branchcompany/updateCompanyOrderReveivableAndCope', $params);
    	
    	return $result;
    }
    
    /**
     * 订单大提交
     */
    public function addCompanyOrderBigAjax(){
    	$params = Request::instance()->param();
    	$result = $this->callSoaErp('post', '/branchcompany/updateCompanyOrderReveivableAndCope', $params);
    	 
    	return $result;
    }
    
    
    /**
     * 添加销售收款
     */
    public function addCompanyOrderSaleAjax(){
    	$params = Request::instance()->param();
    	$payment_time = $params['payment_time'];
    	if(!empty($payment_time)){
    		$params['payment_time'] = strtotime($payment_time);
    	}
    	$voucher_time = $params['voucher_time'];
    	if(!empty($voucher_time)){
    		$params['voucher_time'] = strtotime($voucher_time);
    	}   	
    	
    	
    	
    	$result = $this->callSoaErp('post', '/branchcompany/addCompanyOrderSale', $params);
    	
    	return $result;
    }
    
    /**
     * 添加产品与游客的关联
     * 
     */
    public function updateCompanyOrderProductCustomerAjax(){
    	$params = Request::instance()->param();
    	$company_order_customer = $params['company_order_customer'];
    	$company_order_customer = trim($company_order_customer,',');
    	$params['company_order_customer'] = $company_order_customer;
    	$result = $this->callSoaErp('post', '/branchcompany/updateCompanyOrderProductCustomer', $params);
    	 
    	return $result;
    }
    /**
     * 获取公司订单小报表
     */
    public function getCompanyOrderReceivableInfoAjax(){
    	$params = Request::instance()->param();
    	
    	$result = $this->callSoaErp('post', '/branchcompany/getCompanyOrderReceivableInfo', $params);
    	
    	return $result;
    }


    /**
     * booking订单ajax接口
     * Created by PhpStorm.
     * User: yyy
     * Date: 2019/4/22
     * Time: 10:47
     */
    public function getCompanyOrderBookingListAjax(){
        $params = Request::instance()->param();
        $params['company_id'] = session('user')['company_id'];         //公司id
        $result = $this->callSoaErp('post', '/branchcompany/getCompanyOrderBookingList', $params);

        return $result;
    }


    /**
     * 付款方式列表ajax接口
     * Created by PhpStorm.
     * User: yyy
     * Date: 2019/4/22
     * Time: 15:05
     */
    public function getClientPaymentListAjax()
    {
        $params = Request::instance()->param();
        $params['company_id'] = session('user')['company_id'];         //公司id
        $result = $this->callSoaErp('post', '/branchcompany/getClientPaymentList', $params);

        return $result;
    }


    /**
     * AccountantPayment列表ajax接口
     * Created by PhpStorm.
     * User: yyy
     * Date: 2019/4/23
     * Time: 11:29
     */
    public function getAccountantPaymentListAjax()
    {
        $params = Request::instance()->param();
        $params['company_id'] = session('user')['company_id'];         //公司id
        $result = $this->callSoaErp('post', '/branchcompany/getAccountantPaymentList', $params);

        return $result;
    }

    /**
     * Cost列表ajax接口
     * Created by PhpStorm.
     * User: yyy
     * Date: 2019/4/23
     * Time: 15:17
     */
    public function getCostListAjax()
    {
        $params['company_id'] = session('user')['company_id'];
        $result = $this->callSoaErp('post', '/branchcompany/getCostList', $params);

        return $result;
    }

    /**
     * 添加财务收款ajax
     * Created by PhpStorm.
     * User: yyy
     * Date: 2019/4/25
     * Time: 16:06
     */
    public function addReceivableInfoAjax()
    {
        $params = Request::instance()->param();
        $params['receivable_info_type'] = 1;

        $result = $this->callSoaErp('post', '/branchcompany/addReceivableInfo', $params);

        return $result;
    }


    /**
     * 添加销售收款ajax
     * Created by PhpStorm.
     * User: Administrator
     * Date: 2019/4/26
     * Time: 16:46
     */
    public function addSaleReceivableInfoAjax()
    {
        $params = Request::instance()->param();
        $params['receivable_info_type'] = 2;
        $result = $this->callSoaErp('post', '/branchcompany/addReceivableInfo', $params);

        return $result;
    }


    public function addCompanyOrderAnnexAjax()
    {
        $params = Request::instance()->param();

        $params['create_user_id'] = session('user')['user_id'];
        $result = $this->callSoaErp('post', '/branchcompany/addCompanyOrderAnnex', $params);

        return $result;
    }


    public function getCompanyOrderAnnexAjax()
    {
        $params = Request::instance()->param();
        $params['status'] = 1;
        $result = $this->callSoaErp('post', '/branchcompany/getCompanyOrderAnnex', $params);
        return $result;
    }

    public function delCompanyOrderAnnexAjax()
    {
        $params = Request::instance()->param();
        $result = $this->callSoaErp('post', '/branchcompany/delCompanyOrderAnnex', $params);

        return $result;
    }
	//添加订单留言板
    public function addCompanyOrderCommentAjax()
    {
        $params = Request::instance()->param();

        $params['create_user_id'] = session('user')['user_id'];
     
        $result = $this->callSoaErp('post', '/branchcompany/addCompanyOrderComment', $params);
   
        
        $data = [
        
        	'system_alert_event_id'=>27,
        	'company_order_number'=>$params['company_order_number']
        
        ];
        
        $result =  $this->callSoaErpTest('post','/system_alert_event/addInStationLetterAndEmail',$data);//getSingleSource
        

        return $result;
    }

    public function getCompanyOrderCommentAjax()
    {
        $params = Request::instance()->param();

        $result = $this->callSoaErp('post', '/branchcompany/getCompanyOrderComment', $params);
        return $result;
    }


    /*
     * 添加季晔的订单
     */
    public function addApiBookingAjax()
    {
        $params = $this->input();

        /*$params['contect_name'] = 'qqqq';
        $params['tel'] = '1';
        $params['email'] = 'QQQ@qq.com';
        $params['persions_count'] = '2';
        $params['contect_language_id'] = '1';
        $params['room'][0]['room_type'] = '1';
        $params['room'][0]['room_code'] = '1';

        //第一个人
        $params['room'][0]['person'][0]['passport_number'] = 'wedd';
        $params['room'][0]['person'][0]['customer_first_name'] = '1';
        $params['room'][0]['person'][0]['customer_last_name'] = '1';
        $params['room'][0]['person'][0]['gender'] = '1';
        $params['room'][0]['person'][0]['term_of_validity'] = '1566316800000';
        $params['room'][0]['person'][0]['check_in'] = '-2';
        $params['room'][0]['person'][0]['check_on'] = '1';
        $params['room'][0]['person'][0]['remark'] = '备注';

        $params['room'][0]['person'][0]['customer_flight'][0]['flight_code'] = 'CK787';
        $params['room'][0]['person'][0]['customer_flight'][0]['flight_begin_time'] = '11';
        $params['room'][0]['person'][0]['customer_flight'][0]['flight_type'] = '1';
        $params['room'][0]['person'][0]['customer_flight'][0]['start_place'] = 'LGA';

        $params['room'][0]['person'][0]['customer_flight'][1]['flight_code'] = 'CK786';
        $params['room'][0]['person'][0]['customer_flight'][1]['flight_begin_time'] = '22';
        $params['room'][0]['person'][0]['customer_flight'][1]['flight_type'] = '2';
        $params['room'][0]['person'][0]['customer_flight'][1]['end_place'] = 'LGA';

        //第二个人
        $params['room'][0]['person'][1]['passport_number'] = 'wedd11111';
        $params['room'][0]['person'][1]['customer_first_name'] = '成人';
        $params['room'][0]['person'][1]['customer_last_name'] = '2';
        $params['room'][0]['person'][1]['gender'] = '1';
        $params['room'][0]['person'][1]['term_of_validity'] = '1566316800000';
        $params['room'][0]['person'][1]['check_in'] = '-2';
        $params['room'][0]['person'][1]['check_on'] = '+1';
        $params['room'][0]['person'][1]['remark'] = '备注';

        $params['room'][0]['person'][1]['customer_flight'][0]['flight_code'] = 'CK789';
        $params['room'][0]['person'][1]['customer_flight'][0]['flight_begin_time'] = '11';
        $params['room'][0]['person'][1]['customer_flight'][0]['flight_type'] = '1';
        $params['room'][0]['person'][1]['customer_flight'][0]['start_place'] = 'LGA';

        $params['room'][0]['person'][1]['customer_flight'][1]['flight_code'] = 'CK788';
        $params['room'][0]['person'][1]['customer_flight'][1]['flight_begin_time'] = '22';
        $params['room'][0]['person'][1]['customer_flight'][1]['flight_type'] = '2';
        $params['room'][0]['person'][1]['customer_flight'][1]['end_place'] = 'LGA';

        $params['begin_time'] = 1577451600;
        $params['company_id'] = 20;
        $params['is_source'] = 1;       //是否勾选了自费资源
        $params['is_tipping_paid'] = 1;   //是否勾选了小费
        $params['branch_product_id'] = 179;       //分公司产品的id*/

         $params['wr'] = 1;              //1retail 2wholesale   默认为1
         $params['channel_type'] = $params['channel_type'] ? $params['channel_type'] : 2;    //默认为 2直客
         $params['buy_order_time'] = time();
         $params['order_type'] = 3;      //订单类型 3为b2b
         $params['begin_time'] = strtotime($params['begin_time']);    //转为时间戳

        //添加订单主表

        $result = $this->callSoaErp('post', '/branchcompany/addCompanyOrder', $params);

        if ($result['code'] == 400) return $result;

        //房间信息
        $room = $params['room'];
        foreach ($room as $room_v)
        {
            foreach ($room_v['person'] as $person_v)
            {
                $customer_arr = [];
                $customer_arr['company_order_number'] = $result['data'];            //订单编号
                $customer_arr['passport_number'] = $person_v['passport_number'];      //护照号
                $customer_arr['term_of_validity'] = $person_v['term_of_validity'];      //有效期
                $customer_arr['customer_first_name'] = $person_v['customer_first_name'];    //客户姓
                $customer_arr['customer_last_name'] = $person_v['customer_last_name'];      //客户名
                $customer_arr['gender'] = $person_v['gender'];              //性别 1男2女
                $customer_arr['check_in'] = $person_v['check_in'];            //特殊要求 check_in
                $customer_arr['check_on'] = $person_v['check_on'];            //特殊要求 check_on
                $customer_arr['remark'] = $person_v['remark'];                //特殊要求 备注
                $customer_arr['special_claim'] = $person_v['special_claim'];     //特殊要求 备注
                $customer_arr['room_type'] = $room_v['room_type'];          //房间 类型
                $customer_arr['room_code'] = $room_v['room_code'];          //房号
                $customer_arr['customer_flight'] = [];      //航班信息

                //航班日期转为时间戳
                foreach ($person_v['customer_flight'] as $customer_flight_v)
                {
                    $customer_flight_v['flight_begin_time'] = strtotime($customer_flight_v['flight_begin_time']);
                    array_push($customer_arr['customer_flight'], $customer_flight_v);
                }

                $result_customer = $this->callSoaErp('post', '/branchcompany/addCompanyOrderCustomer', $customer_arr);

                if ($result_customer['code'] == 400) return $result_customer;
            }
        }


        //添加公司订单产品
        $product_arr = [];
        $product_arr['company_order_number'] = $result['data']; //订单编号

        //获取选择的分公司产品信息
        $branch_product_id = $params['branch_product_id'];
        $branch_product_result = $this->callSoaErp('post','/branchcompany/getBranchProduct',['branch_product_id' => $branch_product_id, 'status'=>1])['data'][0];
        if ($branch_product_result['code'] == 400 ) return $branch_product_result;

        $branch_product = $branch_product_result['data'][0];

        //产品编号
        $product_arr['branch_product_array'][0]['branch_product_number'] = $branch_product['branch_product_number'];
        //产品名称
        $product_arr['branch_product_array'][0]['branch_product_name'] = $branch_product['branch_product_name'];

        //线路模板编号
        $product_arr['branch_product_array'][0]['branch_product_params_array'][0]['route_template_number'] = $branch_product['route_teamplate_array'][0]['route_number'];

        //根据线路模板id和开团日期 获取团队产品
        $team_product_base_result =  $this->callSoaErp('post','/product/getTeamProductBase', ['route_template_id' => $branch_product['route_teamplate_array'][0]['route_template_id'], 'begin_time' => $params['begin_time']]);

        if ($team_product_base_result['code'] ==400 || empty($team_product_base_result['data'])) return ['code' => 400];

        //团队产品id
        $product_arr['branch_product_array'][0]['branch_product_params_array'][0]['team_product_id'] = $team_product_base_result['data'][0]['team_product_id'];
        $product_arr['company_id'] = $params['company_id'];

        $result_product = $this->callSoaErp('post', '/branchcompany/addCompanyOrderProduct', $product_arr);

        if ($result_product['code'] == 400) return $result_product;

        //获取公司订单产品
        $company_order_product = $this->callSoaErp('post', '/branchcompany/getCompanyOrderProduct', ['company_order_number' => $result['data']]);
		 
        //订单成本与报价
        $costAndPrice = [];
        $costAndPrice['company_order_number'] = $result['data'];            //订单编号

        //分公司产品
        $costAndPrice['branch_product_array'][0]['company_order_product_id'] = $company_order_product['data']['company_order_product'][0]['company_order_product_id'];       //分公司ID
        $costAndPrice['branch_product_array'][0]['price_currency_id'] = $company_order_product['data']['company_order_product'][0]['price_currency_id'];       //分公司报价货币ID
        $costAndPrice['branch_product_array'][0]['branch_product_price'] = $company_order_product['data']['company_order_product'][0]['branch_product_price'];       //分公司报价
        $costAndPrice['branch_product_array'][0]['tax_id'] = $company_order_product['data']['company_order_product'][0]['tax_id'];    //税点
        $costAndPrice['branch_product_array'][0]['price_before_tax'] = $company_order_product['data']['company_order_product'][0]['price_before_tax']; //税前价格


        $costAndPrice['source_array'] = [];

        //勾选了自费资源
        if ($params['is_source'] == 1)
        {
            //获取订单的游客
            $result_order_customer =  $this->callSoaErp('post', '/branchcompany/getCompanyOrderCustomer', ['company_order_number' => $result['data']]);
            if ($result_order_customer['code'] == 400)
            {
                return json($result_order_customer);
            }
            $customer_ids = implode(',', array_column($result_order_customer['data'], 'company_order_customer_id'));

            foreach ($company_order_product['data']['company_order_product_source'] as $v)
            {
                //如果是自费
                if ($v['supplier_type_id'] == 11)
                {
                    $arr = [];
                    $arr['company_order_product_source_id'] = $v['company_order_product_source_id'];
                    $arr['price_currency_id'] = $v['price_currency_id'];      //报价货币ID
                    $arr['source_price'] = $v['source_cost_univalence'] * $params['persions_count'];       //报价
                    $arr['price_before_tax'] = $v['source_cost_univalence'] * $params['persions_count'];     //税前价格

                    //给各游客添加自费项目
                    $result_product_customer =  $this->callSoaErp('post', '/branchcompany/updateCompanyOrderProductCustomer', ['company_order_number' => $result['data'], 'company_order_customer' => $customer_ids, 'company_order_product_source_id' => $arr['company_order_product_source_id']]);

                    if ($result_product_customer['code'] == 400)
                    {
                        return json($result_product_customer);
                    }

                    array_push($costAndPrice['source_array'], $arr);
                }

            }
        }

        $result_cost = $this->callSoaErp('post', '/branchcompany/updateCompanyOrderCostAndPrice', $costAndPrice);

        if ($result_cost['code'] == 400)
        {
            return $result_product;
        }

        //生成应收与应付
        $result_cope = $this->callSoaErp('post', '/branchcompany/updateCompanyOrderReveivableAndCope', ['company_order_number' => $result['data']]);
        if ($result_cost['code'] == 400)
        {
            return $result_cope;
        }

        $order_result = $this->callSoaErp('post', '/branchcompany/getCompanyOrder', ['company_order_number' => $result['data']]);

        $order_result['data'] = $order_result['data'][0]['company_order_id'];
		error_log(print_r($order_result,1));
		echo json_encode($order_result);exit;
        return $order_result;

    }
    
    /**添加自定义报价
     * 
     */
    
    public function addCompanyOrderProductDiyPriceAjax(){
    	$params = Request::instance()->param();
    	
    	$result = $this->callSoaErp('post', '/branchcompany/addCompanyOrderProductDiyPrice', $params);
    	 
    	return $result;
    }

}