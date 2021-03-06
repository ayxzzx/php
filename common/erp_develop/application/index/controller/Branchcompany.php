<?php
/**
 * Created by PhpStorm.
 * User: godwei
 * Date: 2018/9/20
 * Time: 16:40
 */

namespace app\index\controller;
use think\Cookie;
use \Underscore\Types\Arrays;
use think\Session;
use think\Paginator;
use think\Request;
use think\Controller;
use app\common\help\Help;

class Branchcompany extends Base
{
    /***
     * 分公司产品管理展示页面
     * 王伟
     */
    public function showBranchProductManage()
    {
        Cookie::set("branch_product_manage",$_SERVER['REQUEST_URI']);
        $where=[
            "can_watch_company_id"=>session('user')['company_id'],
            "status"=>1
        ];

        $result = $this->callSoaErp('post', '/branchcompany/getBranchProductType', $where);
        $this->assign('result',$result['data']);


        //搜索
        $branch_product_id = input("branch_product_id");
        $branch_product_number = input("branch_product_number");
        $branch_product_name = input('branch_product_name');
        $status = input('status');
        $create_user_name = input('create_user_name');
        $branch_product_type_id = input('branch_product_type_id');
        if(!empty($branch_product_id)) {
            $data['branch_product_id'] = $branch_product_id;
        }
        if(!empty($branch_product_type_id)) {
            $data['branch_product_type_id'] = $branch_product_type_id;
        }
        if(!empty($branch_product_number)) {
            $data['branch_product_number'] = $branch_product_number;
        }
        if(!empty($branch_product_name)) {
            $data['branch_product_name'] = $branch_product_name;
        }
        if(!empty($create_user_name)) {
            $user_data['nickname'] = $create_user_name;
            $userInfo = $this->callSoaErp('post','/user/getOneUser',$user_data);
            $data['create_user_name'] = $create_user_name;
        }

        //获取分公司团队产品
        $data = [
            'page'=>$this->page(),
            'page_size'=>$this->_page_size,
            'branch_product_number' => $data['branch_product_number'],
            'branch_product_name' => $data['branch_product_name'],
            'status' => $status,
            'create_user_name'=>$create_user_name,
            'search_user_id'=>$userInfo['data']['user_id'],
            'branch_product_type_id'=>$branch_product_type_id,
        ];


        $data['company_id'] =  session('user')['company_id'];


        $branch_product_data_result = $this->callSoaErp('post', '/branchcompany/getBranchProduct', $data);
        // TODO 不懂
//        $branch_product_data_result = $branch_product_data_result['data'];
//        for($i = 0; $i < count($branch_product_data_result); $i++) {
//            $url = '';
//            if(count($branch_product_data_result[$i]['team_product_info']) > 0) {
//
//            }
//        }
        $this->getPageParams($branch_product_data_result);

        return $this->fetch('branch_product_manage');
    }

    /***
     * 分公司产品添加页面
     *
     */
    public function showBranchProductAdd()
    {

        //获取user
        $where['status'] = 1;
        $where['company_id'] =  session('user')['company_id'];
        $where['department_id'] = session('user')['department_id'];
        $UserList = $this->callSoaErp('post','/user/getUser',$where);
        if($UserList['data']){
            $this->assign('UserList',$UserList['data']);
        }

        //获取基础信息
        $branch_product_number = input("branch_product_number");
        if ($branch_product_number)
        {
            $branch_data = [
                'status' => 1,
                'branch_product_number' => $branch_product_number
            ];
            $branch_product_result = $this->callSoaErp('post', '/branchcompany/getBranchProduct', $branch_data);
            $this->assign('branch_product_user', $branch_product_result['data'][0]['update_user_id']);
        }




    	//获取留言版
    	$comment_params = [
    		'branch_product_number'=>input('branch_product_number')
    	];
    	$comment_result  = $this->callSoaErp('post', '/branchcompany/getBranchProductComment', $comment_params);
    	
    	
    	$this->assign('comment_result',$comment_result['data']);

        return $this->fetch('branch_product_add');
    }

    //分公司产品删除
    public function BranchProductDeleteAjax()
    {
        $branch_product_number = input("branch_product_number");

        $data = [
            'branch_product_number' => $branch_product_number,
            'user_id' => session('user')['user_id'],
            'status' => 0
        ];

        $branch_product_delete_result = $this->callSoaErp('post', '/branchcompany/updateBranchProductByBranchProductNumber', $data);

        return $branch_product_delete_result;

    }

    //分公司产品搜索
    public function BranchProductSearchAjax()
    {
        $branchroutetype_search = input("branchroutetype_search");
        $source_name_search = input("source_name_search");
        $source_number_search = input("source_number_search");
        $branch_product_number = input("branch_product_number");
        $check_str = "";

        $source_data = [
            'status' => 1,
            'company_id' => session('user')['company_id']
        ];

        if($branchroutetype_search == 2) {
            if(!empty($source_name_search)) {
                $source_data = [
                    'room_name' => $source_name_search
                ];
            }
            if(!empty($source_number_search)) {
                $source_data = [
                    'source_number' => $source_number_search
                ];
            }

            $source_result = $this->callSoaErp('post', '/source/getHotel', $source_data)['data'];

            //获取分公司酒店资源
            $branch_source_data = [
                'status' => 1,
                'branch_product_number' => $branch_product_number,
                'is_team_product' => 2
            ];
            $branch_source_result = $this->callSoaErp('post', '/branchcompany/getBranchProductSource', $branch_source_data);

            for($j = 0; $j < count($branch_source_result['data']); $j++) {
                $check_str .= $branch_source_result['data'][$j]['source_id'] . ",";
            }

            for($i = 0; $i < count($source_result); $i++) {
                $source_result[$i]['source_name'] = $source_result[$i]['room_name'];
                $source_result[$i]['source_id'] = $source_result[$i]['hotel_id'];
                $source_result[$i]['check_source'] = rtrim($check_str, ",");
            }

        } else if($branchroutetype_search == 3) {
            if(!empty($source_name_search)) {
                $source_data = [
                    'dining_name' => $source_name_search
                ];
            }
            if(!empty($source_number_search)) {
                $source_data = [
                    'source_number' => $source_number_search
                ];
            }

            $source_result = $this->callSoaErp('post', '/source/getDining', $source_data)['data'];

            //获取分公司酒店资源
            $branch_source_data = [
                'status' => 1,
                'branch_product_number' => $branch_product_number,
                'is_team_product' => 2
            ];
            $branch_source_result = $this->callSoaErp('post', '/branchcompany/getBranchProductSource', $branch_source_data);

            for($j = 0; $j < count($branch_source_result['data']); $j++) {
                $check_str .= $branch_source_result['data'][$j]['source_id'] . ",";
            }

            for($i = 0; $i < count($source_result); $i++) {
                $source_result[$i]['source_name'] = $source_result[$i]['dining_name'];
                $source_result[$i]['source_id'] = $source_result[$i]['dining_id'];
                $source_result[$i]['check_source'] = rtrim($check_str, ",");
            }
        }else if($branchroutetype_search == 4) {
            if(!empty($source_name_search)) {
                $source_data = [
                    'flight_number' => $source_name_search
                ];
            }
            if(!empty($source_number_search)) {
                $source_data = [
                    'source_number' => $source_number_search
                ];
            }

            $source_result = $this->callSoaErp('post', '/source/getFlight', $source_data)['data'];

            //获取分公司酒店资源
            $branch_source_data = [
                'status' => 1,
                'branch_product_number' => $branch_product_number,
                'is_team_product' => 2
            ];
            $branch_source_result = $this->callSoaErp('post', '/branchcompany/getBranchProductSource', $branch_source_data);

            for($j = 0; $j < count($branch_source_result['data']); $j++) {
                $check_str .= $branch_source_result['data'][$j]['source_id'] . ",";
            }

            for($i = 0; $i < count($source_result); $i++) {
                $source_result[$i]['source_name'] = $source_result[$i]['flight_number'];
                $source_result[$i]['source_id'] = $source_result[$i]['flight_id'];
                $source_result[$i]['check_source'] = rtrim($check_str, ",");
            }
        }else if($branchroutetype_search == 5) {
            if(!empty($source_name_search)) {
                $source_data = [
                    'cruise_name' => $source_name_search
                ];
            }
            if(!empty($source_number_search)) {
                $source_data = [
                    'source_number' => $source_number_search
                ];
            }

            $source_result = $this->callSoaErp('post', '/source/getCruise', $source_data)['data'];

            //获取分公司酒店资源
            $branch_source_data = [
                'status' => 1,
                'branch_product_number' => $branch_product_number,
                'is_team_product' => 2
            ];
            $branch_source_result = $this->callSoaErp('post', '/branchcompany/getBranchProductSource', $branch_source_data);

            for($j = 0; $j < count($branch_source_result['data']); $j++) {
                $check_str .= $branch_source_result['data'][$j]['source_id'] . ",";
            }

            for($i = 0; $i < count($source_result); $i++) {
                $source_result[$i]['source_name'] = $source_result[$i]['cruise_name'];
                $source_result[$i]['source_id'] = $source_result[$i]['cruise_id'];
                $source_result[$i]['check_source'] = rtrim($check_str, ",");
            }
        }else if($branchroutetype_search == 6) {
            if(!empty($source_name_search)) {
                $source_data = [
                    'visa_name' => $source_name_search
                ];
            }
            if(!empty($source_number_search)) {
                $source_data = [
                    'source_number' => $source_number_search
                ];
            }

            $source_result = $this->callSoaErp('post', '/source/getVisa', $source_data)['data'];

            //获取分公司酒店资源
            $branch_source_data = [
                'status' => 1,
                'branch_product_number' => $branch_product_number,
                'is_team_product' => 2
            ];
            $branch_source_result = $this->callSoaErp('post', '/branchcompany/getBranchProductSource', $branch_source_data);

            for($j = 0; $j < count($branch_source_result['data']); $j++) {
                $check_str .= $branch_source_result['data'][$j]['source_id'] . ",";
            }

            for($i = 0; $i < count($source_result); $i++) {
                $source_result[$i]['source_name'] = $source_result[$i]['visa_name'];
                $source_result[$i]['source_id'] = $source_result[$i]['visa_id'];
                $source_result[$i]['check_source'] = rtrim($check_str, ",");
            }
        }else if ($branchroutetype_search == 7) {
            if(!empty($source_name_search)) {
                $source_data = [
                    'scenic_spot_name' => $source_name_search
                ];
            }
            if(!empty($source_number_search)) {
                $source_data = [
                    'source_number' => $source_number_search
                ];
            }

            $source_result = $this->callSoaErp('post', '/source/getScenicSpot', $source_data)['data'];

            //获取分公司酒店资源
            $branch_source_data = [
                'status' => 1,
                'branch_product_number' => $branch_product_number,
                'is_team_product' => 2
            ];
            $branch_source_result = $this->callSoaErp('post', '/branchcompany/getBranchProductSource', $branch_source_data);

            for($j = 0; $j < count($branch_source_result['data']); $j++) {
                $check_str .= $branch_source_result['data'][$j]['source_id'] . ",";
            }

            for($i = 0; $i < count($source_result); $i++) {
                $source_result[$i]['source_name'] = $source_result[$i]['scenic_spot_name'];
                $source_result[$i]['source_id'] = $source_result[$i]['scenic_spot_id'];
                $source_result[$i]['check_source'] = rtrim($check_str, ",");
            }
        }else if($branchroutetype_search == 8) {
            if(!empty($source_name_search)) {
                $source_data = [
                    'vehicle_name' => $source_name_search
                ];
            }
            if(!empty($source_number_search)) {
                $source_data = [
                    'source_number' => $source_number_search
                ];
            }

            $source_result = $this->callSoaErp('post', '/source/getVehicle', $source_data)['data'];

            //获取分公司酒店资源
            $branch_source_data = [
                'status' => 1,
                'branch_product_number' => $branch_product_number,
                'is_team_product' => 2
            ];
            $branch_source_result = $this->callSoaErp('post', '/branchcompany/getBranchProductSource', $branch_source_data);

            for($j = 0; $j < count($branch_source_result['data']); $j++) {
                $check_str .= $branch_source_result['data'][$j]['source_id'] . ",";
            }

            for($i = 0; $i < count($source_result); $i++) {
                $source_result[$i]['source_name'] = $source_result[$i]['vehicle_name'];
                $source_result[$i]['source_id'] = $source_result[$i]['vehicle_id'];
                $source_result[$i]['check_source'] = rtrim($check_str, ",");
            }
        }else if ($branchroutetype_search == 9) {
            if(!empty($source_name_search)) {
                $source_data = [
                    'tour_guide_name' => $source_name_search
                ];
            }
            if(!empty($source_number_search)) {
                $source_data = [
                    'source_number' => $source_number_search
                ];
            }

            $source_result = $this->callSoaErp('post', '/source/getTourGuide', $source_data)['data'];

            //获取分公司酒店资源
            $branch_source_data = [
                'status' => 1,
                'branch_product_number' => $branch_product_number,
                'is_team_product' => 2
            ];
            $branch_source_result = $this->callSoaErp('post', '/branchcompany/getBranchProductSource', $branch_source_data);

            for($j = 0; $j < count($branch_source_result['data']); $j++) {
                $check_str .= $branch_source_result['data'][$j]['source_id'] . ",";
            }

            for($i = 0; $i < count($source_result); $i++) {
                $source_result[$i]['source_name'] = $source_result[$i]['tour_guide_name'];
                $source_result[$i]['source_id'] = $source_result[$i]['tour_guide_id'];
                $source_result[$i]['check_source'] = rtrim($check_str, ",");
            }
        }else if($branchroutetype_search == 10) {
            if(!empty($source_name_search)) {
                $source_data = [
                    'single_source_name' => $source_name_search
                ];
            }
            if(!empty($source_number_search)) {
                $source_data = [
                    'source_number' => $source_number_search
                ];
            }

            $source_result = $this->callSoaErp('post', '/source/getSingleSource', $source_data)['data'];

            //获取分公司酒店资源
            $branch_source_data = [
                'status' => 1,
                'branch_product_number' => $branch_product_number,
                'is_team_product' => 2
            ];
            $branch_source_result = $this->callSoaErp('post', '/branchcompany/getBranchProductSource', $branch_source_data);

            for($j = 0; $j < count($branch_source_result['data']); $j++) {
                $check_str .= $branch_source_result['data'][$j]['source_id'] . ",";
            }

            for($i = 0; $i < count($source_result); $i++) {
                $source_result[$i]['source_name'] = $source_result[$i]['single_source_name'];
                $source_result[$i]['source_id'] = $source_result[$i]['single_source_id'];
                $source_result[$i]['check_source'] = rtrim($check_str, ",");
            }
        }else if ($branchroutetype_search == 11) {
            if(!empty($source_name_search)) {
                $source_data = [
                    'own_expense_name' => $source_name_search
                ];
            }
            if(!empty($source_number_search)) {
                $source_data = [
                    'source_number' => $source_number_search
                ];
            }

            $source_result = $this->callSoaErp('post', '/source/getOwnExpense', $source_data)['data'];

            //获取分公司酒店资源
            $branch_source_data = [
                'status' => 1,
                'branch_product_number' => $branch_product_number,
                'is_team_product' => 2
            ];
            $branch_source_result = $this->callSoaErp('post', '/branchcompany/getBranchProductSource', $branch_source_data);

            for($j = 0; $j < count($branch_source_result['data']); $j++) {
                $check_str .= $branch_source_result['data'][$j]['source_id'] . ",";
            }

            for($i = 0; $i < count($source_result); $i++) {
                $source_result[$i]['source_name'] = $source_result[$i]['own_expense_name'];
                $source_result[$i]['source_id'] = $source_result[$i]['own_expense_id'];
                $source_result[$i]['check_source'] = rtrim($check_str, ",");
            }
        }
       
        return $source_result;
    }

    /**
     *  分公司产品添加Ajax
     */
    public function addBranchProductAjax()
    {
    	$branch_product_number = input('branch_product_number');
        $branch_product_name = input("branch_product_name");
        $branch_product_begin_time = strtotime(input("branch_product_begin_time"));
        $branch_product_end_time = input("branch_product_end_time");
        $branch_product_user_id = input("branch_product_user_id");
        
        if(!empty($branch_product_end_time)){
        	$branch_product_end_time = strtotime($branch_product_end_time);
        }
        $remark = input("remark");
        $status = input('status');
        $branch_product_type_id = input('branch_product_type_id');
        $team_product_array = json_decode(input('team_product_array'),true);
        $route_template_array = json_decode(input('route_template_array'),true);
        $source_array = json_decode(input('source_array'),true);

        $data = [
        	"branch_product_number" => $branch_product_number,
            "branch_product_name" => $branch_product_name,
            "branch_product_begin_time" => $branch_product_begin_time,
            'branch_product_end_time' => $branch_product_end_time,
        	'branch_product_type_id'=>$branch_product_type_id,
            "remark" => $remark,
        	'status'=>$status,	
        	'team_product_array'=>$team_product_array,
        	'route_template_array'=>$route_template_array,	
        	'source_array'=>$source_array,
            'branch_product_user_id' => $branch_product_user_id

        ];

        $result = $this->callSoaErp('post', '/branchcompany/addBranchProductBigArray', $data);
	
        return $result;//['code' => '400', 'msg' => $data];
    }

    /***
     * 分公司产品修改页面
     *
     */
    public function showBranchProductEdit()
    {

        //获取基础信息
        $branch_product_number = input("branch_product_number");
        $branch_data = [
            'status' => 1,
            'branch_product_number' => $branch_product_number
        ];
        $branch_product_result = $this->callSoaErp('post', '/branchcompany/getBranchProduct', $branch_data);

        $team_product_data = [
            'status' => 1,
            'company_id'=>session('user')['company_id']
        ];
        $team_product_result = $this->callSoaErp('post', '/product/getTeamProductBase', $team_product_data);

        //转换时间戳
        foreach ($team_product_result['data'] as $key => $val) {
            $team_product_result['data'][$key]['begin_time'] = date("Y-m-d", $team_product_result['data'][$key]['begin_time']);
        }

        //获取分公司团队产品数据
        $branch_team_data = [
            'status' => 1,
            'branch_product_number' => $branch_product_number
        ];

        $branch_product_team = $this->callSoaErp('post', '/branchcompany/getBranchProductTeam', $branch_team_data);

        //获取分公司所有资源
        $branch_source_data = [
            'status' => 1,
            'branch_product_number' => $branch_product_number,
            'is_team_product' => 2
        ];
        $branch_source_result = $this->callSoaErp('post', '/branchcompany/getBranchProductSource', $branch_source_data);

        //读取币种
        $currency_result = $this->callSoaErp('post','/system/getCurrency',$data);
        $this->assign('currency_result_data',$currency_result['data']);


//        echo "<pre>";
//        var_dump($team_product_result['data']);exit;
//        echo "</pre>";

        $this->assign('team_product_source_result', $team_product_result['data']);
        $this->assign('branch_product_result', $branch_product_result['data']);
        $this->assign('branch_product_team_result', $branch_product_team['data']);
        $this->assign('branch_source_result', $branch_source_result['data']);
        return $this->fetch('branch_product_edit');
    }

    /***
    * 分公司产品-修改币种和价格
    */
    public function editBranchProductCurrencyAndPriceAjax(){

        $data = json_decode(input("branch_product_arr"), true);
        $data['branch_product_arr'] = $data;
        $currency_and_price_result = $this->callSoaErp('post', '/branchcompany/updateBranchProductPriceAndCurrencyId', $data);

        return $currency_and_price_result;

    }

    /***
     * 分公司产品详情页面
     *
     */
    public function showBranchProductInfo()
    {
    	//获取留言版
    	$comment_params = [
    			'branch_product_number'=>input('branch_product_number')
    	];
    	$comment_result  = $this->callSoaErp('post', '/branchcompany/getBranchProductComment', $comment_params);
    	 
    	 
    	$this->assign('comment_result',$comment_result['data']);
        return $this->fetch('branch_product_info');
    }

    /**
     *  分公司产品基础信息修改Ajax
     */
    public function editBranchProductAjax()
    {
        $branch_product_number = input("branch_product_number");

        $status = input("status");
       

        $data = [
            "branch_product_number" => $branch_product_number,

            "status" => $status,
       
        ];

        $result = $this->callSoaErp('post', '/branchcompany/updateBranchProductByBranchProductNumber', $data);

        return $result;//['code' => '400', 'msg' => $data];
    }

    /***
     * 分公司团队产品修改页面
     */
    public function showBranchProductTeamEdit()
    {
        //获取分公司团队产品
        $branch_product_number = input("branch_product_number");
        $branch_team_data = [
            'status' => 1,
            'branch_product_number' => $branch_product_number
        ];
        $branch_team_data_result = $this->callSoaErp('post', '/branchcompany/getBranchProductTeam', $branch_team_data);
        //转换时间戳
        foreach ($branch_team_data_result['data'] as $key => $val) {
            $branch_team_data_result['data'][$key]['begin_time'] = date("Y-m-d", $branch_team_data_result['data'][$key]['begin_time']);
        }
        $this->assign('branch_team_data_result', $branch_team_data_result['data']);
        return $branch_team_data_result;
    }

    /**
     *  分公司产品团队产品修改Ajax
     */
    public function editBranchProductTeamAjax()
    {
        $branch_product_number = input("branch_product_number");
        $team_product_number = input('team_product_number');
        $status = input("status");
        $user_id = session('user')['user_id'];
        $data = [
            "branch_product_number" => $branch_product_number,
            "status" => $status,
            'user_id' => $user_id,
            'team_product_number' => $team_product_number
        ];

        $result = $this->callSoaErp('post', '/branchcompany/updateBranchProductTeamByBranchProductNumber', $data);
        return $result;//['code' => '400', 'msg' => $data];
    }

    /***
     * 分公司资源修改
     ***/
    public function showBranchProductSourceEdit()
    {

        //获取分公司产品团队产品
        $branch_product_number = input("branch_product_number");
        $branch_source_data = [
            'status' => 1,
            'branch_product_number' => $branch_product_number
        ];
        $branch_source_data_result = $this->callSoaErp('post', '/branchcompany/getBranchProductSource', $branch_source_data);
        $this->assign('branch_source_data_result', $branch_source_data_result['data']);
        return $branch_source_data_result;
    }

    /***
     * 分公司资源修改
     ***/
    public function editBranchProductSourceAjax()
    {
        $branch_product_source_id = input("branch_product_source_id");
        $status = input("status");
        $user_id = session('user')['user_id'];

        $data = [
            "branch_product_source_id" => $branch_product_source_id,
            "status" => $status,
            'user_id' => $user_id
        ];

        $result = $this->callSoaErp('post', '/branchcompany/updateBranchProductSourceByBranchProductSourceId', $data);
        return $result;//['code' => '400', 'msg' => $data];
    }

    /***
     * 分公司团队产品添加页面
     */
    public function showBranchProductTeamAdd()
    {
        //获取分公司产品团队产品
        $branch_team_data = [
            'status' => 1

        ];
        $branch_team_data_result = $this->callSoaErp('post', '/branchcompany/getBranchProductTeam', $branch_team_data);
        $this->assign('branch_team_data_result', $branch_team_data_result['data']);
        return $this->fetch('branch_product_add');
    }

    /***
     * 分公司资源添加
     ***/
    public function showBranchProductSourceAdd()
    {

        $data = json_decode(input("team_product_array"), true);
        $source_data = json_decode(input("branch_product_source_array"), true);
        if (count($data) > 0) {

            foreach ($data as $key => $val) {

                $params['data'] = $data[$key];
                $params['data']['begin_time'] = strtotime($data[$key]['begin_time']);
                $params['data']['user_id'] = session('user')['user_id'];
                $params['data']['status'] = 1;
                $result = $this->callSoaErp('post', '/branchcompany/addBranchProductTeam', $params['data']);


                $source = $params['data']['own_expense_array'];
                if (count($source) > 0) {
                    foreach ($source as $kk => $vv) {
                        $source['data'] = $source[$key];
                        $source['data']['team_product_number'] = $data[$key]['team_product_number'];
                        $source['data']['supplier_type_id'] = 11;
                        $source['data']['is_team_product'] = 1;
                        $source['data']['user_id'] = session('user')['user_id'];
                        $source['data']['status'] = 1;

                        $result = $this->callSoaErp('post', '/branchcompany/addBranchProductSource', $source['data']);

                    }
                }
            }
        } else {

            foreach ($source_data as $kkk => $vvv) {
                $source_all['data'] = $source_data[$kkk];
                $source_all['data']['is_team_product'] = 2;
                $source_all['data']['team_product_number'] = "";
                $source_all['data']['user_id'] = session('user')['user_id'];
                $source_all['data']['status'] = 1;

                $result = $this->callSoaErp('post', '/branchcompany/addBranchProductSource', $source_all['data']);

            }
        }
        return $result;

    }
	/**
	 * 分公司产品类型显示页面
	 */
    public  function showBranchProductTypeManage(){

        Cookie::set("branch_product_type_manage",$_SERVER['REQUEST_URI']);

        $data = [
            'page'=>$this->page(),
            'page_size'=>$this->_page_size,
        ];
        $branch_product_type_name = input('branch_product_type_name');
        $create_user_name = input('create_user_name');
        $company_id = input('company_id');

        if(session('user')['company_id']!=1){
            $data['can_watch_company_id'] = session('user')['company_id'];
        }else{
            if(!empty($company_id)){
                $data['can_watch_company_id'] = input('company_id');
            }
        }
        if(is_numeric(input('status'))){
            $data['status'] = input('status');
        }
        if(!empty($branch_product_type_name)){
            $data['branch_product_type_name'] = input('branch_product_type_name');
        }
        if(!empty($create_user_name)){
            $data['create_user_name'] = input('create_user_name');
        }

        $result = $this->callSoaErp('post', '/branchcompany/getBranchProductType',$data);
        //获取公司页面
        $company_data = [
            'status'=>1,
        ];
        $company_result = $this->callSoaErp('post', '/system/getCompany',$company_data);

        $this->getPageParams($result);
        $this->assign('company_result',$company_result['data']);
        return $this->fetch('branch_product_type_manage');
    }
    
    /**
     * 分公司产品类型添加页面
     */
    public  function showBranchProductTypeAdd(){
    	 
    	 
    	return $this->fetch('branch_product_type_add');
    }
    
    /**
     * 分公司产品类型添加Ajax
     */
    public  function branchProductTypeAddAjax(){
    	$data['branch_product_type_name'] = input('branch_product_type_name');
    	$result = $this->callSoaErp('post', '/branchcompany/addBranchProductType',$data);
    	return $result;
    }
    /**
     * 分公司产品类型修改页面
     */
    public  function showBranchProductTypeEdit(){
    	$data['branch_product_type_id'] = input('branch_product_type_id');
    	$result = $this->callSoaErp('post', '/branchcompany/getBranchProductType',$data);
    	
    	
    	$this->assign('result',$result['data'][0]);
    	return $this->fetch('branch_product_type_edit');
    }    
    /**
     * 分公司产品类型修改Ajax
     */
    public  function branchProductTypeEditAjax(){
    	
    	if(!empty(input('branch_product_type_name'))){
    		$data['branch_product_type_name'] = input('branch_product_type_name');
    	}
    	if(is_numeric(input('status'))){
    		$data['status'] = input('status');
    	}
    
    	$data['branch_product_type_id'] = input('branch_product_type_id');
    	$result = $this->callSoaErp('post', '/branchcompany/updateBranchProductType',$data);
    	return $result;
    }
    /**
     * 客人管理
     * Hugh
     */
    public function showCustomerManage()
    {
        Cookie::set("customer_manage",$_SERVER['REQUEST_URI']);
        $data = Request::instance()->param();
        if ($data['status'] == 2) {
            unset($data['status']);
        }
        $data['company_id'] = session('user')['company_id'];
        $data['page']=$this->page();
        $data['page_size']=$this->_page_size;
        	
        $CustomerData = $this->callSoaErp('post', '/branchcompany/getCustomer', $data);
		$this->getPageParams($CustomerData);
        return $this->fetch('customer_manage');
    }


    /**
     * 搜索客人信息
     * Hugh
     */
    public function setCustomerManage()
    {
        Session::set('setCustomerManage', $_POST);
        $this->redirect('/branchcompany/showCustomerManage');
    }

    public function clearCustomerManage()
    {
        Session::delete('setCustomerManage');
        $this->redirect('/branchcompany/showCustomerManage');
    }

    /**
     * 添加客人
     * Hugh
     */
    public function showCustomerAdd()
    {
        //语言
        $data['status'] = 1;
        $langData = $this->callSoaErp('post', '/system/getLanguage', $data);
        if (!empty($langData['data'])) {
            $this->assign('langList', $langData['data']);
        }

        //国家
        $data1['level'] = 1;
        $country_result = $this->callSoaErp('post', '/system/getCountryCity',$data1);
        if (!empty($country_result['data'])) {
            $this->assign('CountryList', $country_result['data']);
        }

        $data = [
            'status'=>1,
            'company_id'=>session('user')['company_id']
        ];
        $company_result =  $this->callSoaErp('post', '/system/getCompany',$data);
        $this->assign('company_result',$company_result['data']);

        return $this->fetch('customer_add');
    }

    /**
     * 异部添加客人信息
     * Hugh
     */
    public function AddCustomerAjax()
    {
        $data = Request::instance()->param();

        if(!empty(Arrays::get($data, 'term_of_validity'))){
        	$data['term_of_validity'] = strtotime(Arrays::get($data, 'term_of_validity'));
        }
        if(!empty(Arrays::get($data, 'issuing_date'))){
        	$data['issuing_date'] = strtotime(Arrays::get($data, 'issuing_date'));
        }
        if(!empty(Arrays::get($data, 'birthday'))){
        	$data['birthday'] = strtotime(Arrays::get($data, 'birthday'));
        }
      
        
        $result = $this->callSoaErp('post', '/branchcompany/addCustomer', $data);

        return $result;
    }

    /**
     * 修改客人信息
     * Hugh
     */
    public function showCustomerEdit()
    {
        $this->assign('customer_type', $this->customer_type);
        $this->assign('card_type', $this->card_type);
        //语言
        $data['status'] = 1;
        $langData = $this->callSoaErp('post', '/system/getLanguage', $data);
        if (!empty($langData['data'])) {
            $this->assign('langList', $langData['data']);
        }

        //国家
        $data1['level'] = 1;
        $country_result = $this->callSoaErp('post', '/system/getCountryCity',$data1);
        $this->assign('CountryData',$country_result['data']);

        //公司
        $data2 = [
            'status'=>1,
            'company_id'=>session('user')['company_id']
        ];
        $company_result =  $this->callSoaErp('post', '/system/getCompany',$data2);
        $this->assign('company_result',$company_result['data']);


        $data3['customer_id'] = input('get.id');
        $CustomerData = $this->callSoaErp('post', '/branchcompany/getCustomer', $data3);
    
        if (!empty($CustomerData['data'])) {
            $this->assign('CustomerAr', $CustomerData['data'][0]);
        }
        return $this->fetch('customer_edit');
    }


    public function EditCustomerAjax()
    {
        $data = Request::instance()->param();
        $data['user_id'] = session('user')['user_id'];
        $data['company_id'] = session('user')['company_id'];
        $data['customer_id'] = input('get.id');
        if(!empty(Arrays::get($data, 'term_of_validity'))){
        	$data['term_of_validity'] = strtotime(Arrays::get($data, 'term_of_validity'));
        }
        if(!empty(Arrays::get($data, 'issuing_date'))){
        	$data['issuing_date'] = strtotime(Arrays::get($data, 'issuing_date'));
        }
        if(!empty(Arrays::get($data, 'birthday'))){
        	$data['birthday'] = strtotime(Arrays::get($data, 'birthday'));
        }
//        var_dump($data);exit;
        return $this->callSoaErp('post', '/branchcompany/updateCustomerByCustomerId', $data);
    }

    /**
     * 客人详情
     * Hugh
     */
    public function showCustomerInfo()
    {
        $this->assign('customer_type', $this->customer_type);
        $this->assign('card_type', $this->card_type);

        //语言
        $data['status'] = 1;
        $langData = $this->callSoaErp('post', '/system/getLanguage', $data);
        if (!empty($langData['data'])) {
            $this->assign('langList', $langData['data']);
        }
        //国家
        $data['level'] = 1;
        $CountryData = $this->callSoaErp('post', '/system/getCountry', $data);
        if (!empty($CountryData['data'])) {
            $this->assign('CountryList', $CountryData['data']);
        }
        unset($data);
        $data['customer_id'] = input('get.id');
        $CustomerData = $this->callSoaErp('post', '/branchcompany/getCustomer', $data);
        if (!empty($CustomerData['data'])) {
            $this->assign('CustomerAr', $CustomerData['data'][0]);
        }

        return $this->fetch('customer_info');
    }

    /**
     * showCustomerOrder
     *
     * 显示顾客订单列表
     * @author shj
     * @return void
     * Date: 2019/4/17
     * Time: 9:54
     */
    public function showCustomerOrder(){

        $data['customer_id'] = input('get.id');
        $OrderData = $this->callSoaErp('post', '/branchcompany/getCustomerOrder', $data);
        if (!empty($OrderData['data'])) {
            $this->assign('data', $OrderData['data']);
        }

        return $this->fetch('customer_order');
    }


    /***
     * 渠道管理
     * Hugh
     */
    public function showDistributorManage()
    {
        Cookie::set("distributor_manage",$_SERVER['REQUEST_URI']);
        $data = [
            'page'=>$this->page(),
            'page_size'=>$this->_page_size,
        ];
        $data['company_id'] = session('user')['company_id'];
        $setDistributorManage = Session::get('setDistributorManage');

        if (!empty($setDistributorManage['search_Id'])) {
            $data['distributor_id'] = $setDistributorManage['search_Id'];
        }
        if (!empty($setDistributorManage['search_Name'])) {
            $data['distributor_name'] = $setDistributorManage['search_Name'];
        }
        if (!empty($setDistributorManage['search_status'])) {
            $data['status'] = $setDistributorManage['search_status'];
        }

        $distributorData = $this->callSoaErp('post', '/btob/getDistributor', $data);
        $this->getPageParams($distributorData);
        
      
        $this->assign('setDistributorManage', $setDistributorManage);
        return $this->fetch('distributor_manage');
    }

    /**
     * 搜索渠道管理
     * Hugh
     */
    public function setDistributorManage()
    {
        Session::set('setDistributorManage', $_POST);
        $this->redirect('/branchcompany/showDistributorManage');
    }

    public function clearDistributorManage()
    {
        Session::delete('setDistributorManage');
        $this->redirect('/branchcompany/showDistributorManage');
    }

    /**
     * 添加渠道商
     * Hugh
     */
    public function showDistributorAdd()
    {
        //语言
        $data['status'] = 1;
        $langData = $this->callSoaErp('post', '/system/getLanguage', $data);
        $this->assign('langList', $langData['data']);
        
        //城市
        $data1['level'] = 3;
        $country_result = $this->callSoaErp('post', '/system/getCountryCity',$data1);
        $this->assign('cityList', $country_result['data']);
        
        return $this->fetch('distributor_add');
    }

    /**
     * 异部添加渠道商
     * Hugh
     */
    public function addDistributorManageAjax()
    {
        $data = Request::instance()->param();
        $data['user_id'] = session('user')['user_id'];
        $data['company_id'] = session('user')['company_id'];
        $result = $this->callSoaErp('post', '/btob/addDistributor', $data);
        return $result;
    }

    /**
     * 修改渠道商
     * Hugh
     */
    public function showDistributorEdit()
    {
        //语言
        $data['status'] = 1;
        $langData = $this->callSoaErp('post', '/system/getLanguage', $data);
        $this->assign('langList', $langData['data']);

        //城市
        $data1['level'] = 3;
        $country_result = $this->callSoaErp('post', '/system/getCountryCity',$data1);
        $this->assign('cityList', $country_result['data']);
        
        unset($data);
        $data['distributor_id'] = input('get.id');
        $distributorData = $this->callSoaErp('post', '/btob/getDistributor', $data);
        
        if (!empty($distributorData['data'])) {
            $this->assign('distributorList', $distributorData['data'][0]);
        }

        return $this->fetch('distributor_edit');
    }

    /**
     * 异部修改渠道商
     * Hugh
     */
    public function editDistributorManageAjax()
    {
        $data = Request::instance()->param();
        $data['user_id'] = session('user')['user_id'];
        $data['company_id'] = session('user')['company_id'];
        $data['distributor_id'] = input('get.id');
        $result = $this->callSoaErp('post', '/btob/updateDistributorByDistributorId', $data);
        return $result;
    }

    /**
     * 详情渠道商
     * Hugh
     */
    public function showDistributorInfo()
    {
        //语言
        $data['status'] = 1;
        $langData = $this->callSoaErp('post', '/system/getLanguage', $data);
        if (!empty($langData['data'])) {
            $this->assign('langList', $langData['data']);
        }

        //城市
        $data['level'] = 3;
        $cityData = $this->callSoaErp('post', '/system/getCountry', $data);
        if (!empty($cityData['data'])) {
            $this->assign('cityList', $cityData['data']);
        }

        $data['distributor_id'] = input('get.id');
        unset($data['status']);
        $distributorData = $this->callSoaErp('post', '/btob/getDistributor', $data);
        if (!empty($distributorData['data'])) {
            $this->assign('distributorList', $distributorData['data'][0]);
        }

        return $this->fetch('distributor_info');
    }
    /**
    *显示渠道账单
    */
    public function showDistributorTemplateManage(){
		
    	
    $distributor_id = input('id');
    $data['distributor_id'] = $distributor_id;
    $distributor_result = $this->callSoaErp('post', '/btob/getDistributor', $data);
    
   	$this->assign('distributor_result',$distributor_result['data'][0]);
   	
   	//获取账单title
   	$bill_template_params = [
   		'company_id'=>session('user')['company_id'],
   			
   	];
   	$bill_template_result = $this->callSoaErp('post', '/system/getBillTemplate', $bill_template_params);
  	$this->assign('bill_template_result',$bill_template_result['data']);
  	
  	//反查应收获取分公司产品编号
  	$branch_product_params = [
  		'company_id'=>session('user')['company_id'],

  		'status'=>1	
  	];
  	$branch_product_result = $this->callSoaErp('post', '/branchcompany/getBranchProduct', $branch_product_params);
  	$this->assign('branch_product_result',$branch_product_result['data']);
    return $this->fetch('distributor_template');
    }
    /**
    * 调取渠道账单AJAX
    */
    public function getDistributorTemplateAjax(){
		if(!empty(input('company_order_create_from_time'))){
			$data['company_order_create_from_time'] = strtotime(input('company_order_create_from_time'));
		}
		if(!empty(input('company_order_create_to_time'))){
			$data['company_order_create_to_time'] = strtotime(input('company_order_create_to_time'));
		}
		if(!empty(input('company_order_begin_time'))){
			$data['company_order_begin_time'] = strtotime(input('company_order_begin_time'));
		}
		if(!empty(input('company_order_end_time'))){
			$data['company_order_end_time'] = strtotime(input('company_order_end_time'));
		}
		if(!empty(input('hidden_sale_over'))){
			$data['hidden_sale_over'] = input('hidden_sale_over');
		}
		if(!empty(input('branch_product_number'))){
			$data['branch_product_number'] = input('branch_product_number');
		}
	
		$data['distributor_id'] = input('distributor_id');
		$data['bill_template_id'] = input('bill_template_id');
        $distributorData = $this->callSoaErp('post', '/btob/getDistributorBill', $data);
        
        
        return $distributorData;
    }
    /**
     * 分公司订单显示页面
     */
    public function showCompanyOrderManage(){
    
    	//显示所有的数据
    	$data=[
            'page'=>$this->page(),
            'page_size'=>$this->_page_size,
    		"company_id"=>session('user')['company_id'],
    	
    	];

    	//搜索
    	$company_order_number = input("one_id_search");
    	$wr = input("one_wr_search");
    
    	if(!empty($company_order_number)){
    		$data['company_order_number'] = $company_order_number;
    	}
    
    	if(!empty($wr)){
    		$data['wr'] = $wr;
    	}


    	
    	
    	
  
    	$this->assign('data',$order_manage['data']);
      //  $this->getPageParams($order_manage);
    
    	return $this->fetch('company_order_manage');
    }
    /**
     * 删除订单
     */
    public function del_company_order(){
   
    	$data['company_order_number'] = input('company_order_number');
    	$data['status']=input('status');
    	$result = $this->callSoaErp('post', '/branchcompany/updateCompanyOrderByCompanyOrderNumber', $data);
    	
    	return $result;
    }
    
    /**
     * 获取游客行程单 AJAX请求
     */
    public function getCompanyOrderCustomerJouneryMenuAjax(){
    	$company_order_number = input("company_order_number");
    
    	$data=[
    		"company_order_number"=>$company_order_number
    	];
    
    	$guide_receipt_result = $this->callSoaErp('post', '/branchcompany/getCompanyOrderCustomerJouneryMenu', $data);
    
    	return $guide_receipt_result;
    }
    
    /**
     * 获取分公司产品类型
     */
    public function getBranchProductTypeAjax(){
    	$data=[
    		"can_watch_company_id"=>session('user')['company_id']
    	];
    	
    	$result = $this->callSoaErp('post', '/branchcompany/getBranchProductType', $data);

    	return $result;
    	
    }
    
    //获取分公司产品的AJAX
    public function getBranchProductAjax(){
    	$data=[
    		"company_id"=>session('user')['company_id'],
    		'branch_product_name'=>input('branch_product_name'),
    		'branch_product_number'=>input('branch_product_number'),    	
    		'create_user_name'=>input('create_user_name'),
    		'branch_product_type_id'=>input('branch_product_type_id'),
    		'status'=>input('status') ? : 1,
    		'is_like'=>input('is_like'),
          
    	];

    	$result = $this->callSoaErp('post', '/branchcompany/getBranchProduct', $data);
    	
    	return $result;
    	
    }
	/**
	 * 修改分公司状态
	 */
    public function updateBranchProductStatusByBranchProductNumberAjax(){
    	
    	$params = Request::instance()->param();
    	$result = $this->callSoaErp('post', '/branchcompany/updateBranchProductStatusByBranchProductNumber', $params);
    	
    	return $result;
    }
    /**
	 * 添加订单
	 */
    public function companyOrderManage(){
    	
    	$company_order_number = input('company_order_number');
    	if(!empty($company_order_number)){
    		$number_params = [
    				'company_order_number'=>$company_order_number
    		];
    		$result = $this->callSoaErp('post', '/branchcompany/getCompanyOrder', $number_params);
    		 
    		 
    		 
    		$this->assign('company_order',$result['data'][0]);
    	}

    	//获取币种
    	$currency_params = [
    			'status'=>1
    	];
    	$currency_result = $this->callSoaErp('post','/system/getCurrency', $currency_params);
    	 
    	//获取本公司币种
    	//获取所有公司对本公司币种的汇率
    	$proporation_params = [
    			'opposite_currency_id'=>session('user')['company_currency_id'],
    			'proportion_time'=>help::getLastMonthDay()
    	];
    	
    	$proporation_result = $this->callSoaErp('post','/system/getOneCurrencyProportion', $proporation_params);
    	
    	$this->assign('proporation_result',$proporation_result['data']);
    	$this->assign('currency_result',$currency_result['data']);
    	
    	//获取该订单的应付
    	
    	$cope_params = [
    		'company_id'=>session('user')['company_id'],
    		'status'=>1,
    		'order_number'=>$company_order_number,
    		'company_order'=>1	
    	];
    	$cope_result = $this->callSoaErp('post','/finance/getCopeInfo', $cope_params);
    	

    	$this->assign('cope_result',$cope_result['data']);
        return $this->fetch('company_order_manage_add');
    }
    /**
    * 添加订单-->添加游客
    */
    public function companyOrderAddvisitor(){
        return $this->fetch('company_order_addvisitor');
    }
	
	    /**
     * 分公司订单-获取游客行程单
     */
    public function showCompanyOrderCustomerGuideReceipt(){
		$company_order_number = input('company_order_number');
		$this->assign('company_order_number',$company_order_number);
        return $this->fetch('company_order_customer_guide_receipt');

    }
    /**
     * 删除订单的的游客回执单
     */
    public function updateCompanyOrderGuideReceipt(){
    	$company_order_id = input("company_order_id");
    
    	$data=[
    			"company_order_id"=>$company_order_id,
    			'status'=>0
    	];
    
    	$guide_receipt_result = $this->callSoaErp('post', '/branchcompany/updateCompanyOrderGuideReceipt', $data);
    
    	return $guide_receipt_result;
    }
    
    /**
     * 修改分公司产品锁定
     */
    public function updateBranchProductLockedAjax(){
    	$params = Request::instance()->param();
    	 
    	$result = $this->callSoaErp('post', '/branchcompany/updateBranchProductLocked', $params);
    	 
    	return $result;
    }

    /**
     * showCustomerSourceManage
     *
     * 客户来源列表页面
     * @author shj
     * @return mixed
     * Date: 2019/4/10
     * Time: 16:06
     */
    public function showCustomerSourceManage()
    {
        $data['company_id'] = session('user')['company_id'];
        $data['page']=$this->page();
        $data['page_size']=$this->_page_size;

        $status = input('status');
        $customer_source_name = input('customer_source_name');

        $data['status'] = $status;

        if(!empty($customer_source_name)){
            $data['customer_source_name'] = $customer_source_name;
        }

        $CustomerSourceData = $this->callSoaErp('post', '/branchcompany/getCustomerSource', $data);

        $this->getPageParams($CustomerSourceData);

        return $this->fetch('customer_source_manage');
    }

    /**
     * showCustomerSourceAdd
     *
     * 添加客户来源页面
     * @author shj
     * @return void
     * Date: 2019/4/10
     * Time: 17:11
     */
    public function showCustomerSourceAdd(){

        $data = [
            'status'=>1,
            'company_id'=>session('user')['company_id']
        ];
        $company_result =  $this->callSoaErp('post', '/system/getCompany',$data);
        $this->assign('company_result',$company_result['data']);

        return $this->fetch('customer_source_add');
    }

    /**
     * addCustomerSourceAjax
     *
     * 添加客户来源数据
     * @author shj
     * @return array|bool|mixed|string
     * Date: 2019/4/11
     * Time: 15:51
     */
    public function addCustomerSourceAjax(){
        $customer_source_name = input("customer_source_name");
        $status = input("status");
        $remark = input("remark");
        $user_id = session('user')['user_id'];
        $choose_company_id = input('choose_company_id');

        $data = [
            "customer_source_name"=>$customer_source_name,
            "status"=>$status,
            "remark"=>$remark,
            "user_id"=>$user_id,
            'choose_company_id'=>$choose_company_id,
        ];

        $result = $this->callSoaErp('post', '/branchcompany/addCustomerSource',$data);
        return   $result;
    }

    /**
     * showCustomerSourceEdit
     *
     * 修改客户来源页面
     * @author shj
     * @return mixed
     * Date: 2019/4/11
     * Time: 18:00
     */
    public function showCustomerSourceEdit(){

        $where['customer_source_id'] = input('customer_source_id');
        $result = $this->callSoaErp('post', '/branchcompany/getOneCustomerSource',$where);

        $this->assign('result',$result['data']);

        $data = [
            'status'=>1,
            'company_id'=>session('user')['company_id']
        ];
        $company_result =  $this->callSoaErp('post', '/system/getCompany',$data);
        $this->assign('company_result',$company_result['data']);

        return $this->fetch('customer_source_edit');
    }

    /**
     * editCustomerSourceAjax
     *
     * 修改客户来源数据
     * @author shj
     * @return array|bool|mixed|string
     * Date: 2019/4/11
     * Time: 18:01
     */
    public function editCustomerSourceAjax(){
        $customer_source_id = input("customer_source_id");
        $customer_source_name = input("customer_source_name");
        $status = input("status");
        $remark = input("remark");
        $user_id = session('user')['user_id'];
        $choose_company_id = input('choose_company_id');

        $data = [
            "customer_source_id"=>$customer_source_id,
            "customer_source_name"=>$customer_source_name,
            "status"=>$status,
            "remark"=>$remark,
            "user_id"=>$user_id,
            'choose_company_id'=>$choose_company_id,
        ];

        $result = $this->callSoaErp('post', '/branchcompany/editCustomerSource',$data);
        return   $result;
    }

    /**
     * showCustomerSourceInfo
     *
     * 客户来源详情页面
     * @author shj
     * @return mixed
     * Date: 2019/4/12
     * Time: 10:38
     */
    public function showCustomerSourceInfo(){

        $where['customer_source_id'] = input('customer_source_id');
        $result = $this->callSoaErp('post', '/branchcompany/getOneCustomerSource',$where);

        $this->assign('result',$result['data']);

        return $this->fetch('customer_source_info');
    }
    /**
     * 修改分公司产品线路模板
     */
    public function updateBranchProductRouteTemplateAjax(){
    	$params = Request::instance()->param();
    
    	$result = $this->callSoaErp('post', '/branchcompany/updateBranchProductRouteTemplate', $params);
    
    	return $result;
    }

    /**
        * 销售报表-创建人
        */
        public function planTourIframe(){
        	$company_order_number = input('company_order_number');
        	$company_order_id = input('company_order_id');
        	$this->assign('company_order_number',$company_order_number);
       		$this->assign('company_order_id',company_order_id);
            return $this->fetch('planTourIframe');
        }
  
        
        
        
	//获取客户来源
    public function getCustomerSourceAjax(){
    	$params = Request::instance()->param();
    	$params['status'] = 1;
    	$params['company_id'] =  session('user')['company_id'];
    	$result = $this->callSoaErp('post', '/branchcompany/getCustomerSource', $params);
    	
    	return $result;
    }
    /**
     * 获取渠道商AJAX
     */
    public function getDistributorAjax(){
    	$params = Request::instance()->param();
    	$params['status'] = 1;
    	$params['company_id'] =  session('user')['company_id'];
    	$result = $this->callSoaErp('post', '/btob/getDistributor', $params);
    	 
    	return $result;
    }
    /**
     * 添加分公司产品留言板
     * @return Ambigous <mixed, multitype:string Ambigous <string> , multitype:string Ambigous <string, Ambigous <string>, string> >
     */
    public function addBranchProductCommentAjax()
    {
    	$params = Request::instance()->param();
    
    	$params['create_user_id'] = session('user')['user_id'];
    	
    
    	$result = $this->callSoaErp('post', '/branchcompany/addBranchProductComment', $params);
    	$data = [
    	
    			'system_alert_event_id'=>30,
    			'branch_product_number'=>$params['branch_product_number']
    	
    	];
    	
    	return $result;
    }   
    /**
     * 添加付款
     */

    public function companyOrderPayAjax(){
    	$params = Request::instance()->param();

    	if(!empty($params['voucher_time'])){
    		$params['voucher_time'] = strtotime($params['voucher_time']);
    	}
    	if(!empty($params['deposit_payment_time'])){
    		$params['deposit_payment_time'] = strtotime($params['deposit_payment_time']);
    	}
    	 
    	 
    	if(is_numeric($params['result_currency_id'])){
    		$params['result_currency_id'] = $params['result_currency_id'];
    	}else{
    		$params['result_currency_id']=session('user')['company_currency_id'];
    	}
    
    	$result = $this->callSoaErp('post','/finance/addReceiptsPay', $params);
    	//	error_log(print_r($result,1));
    	 
    	$cope_number = $params['cope_number'];
		//获取原始金额和原始货币
		$base_currency_id = $params['base_currency_id'];
		$base_money = $params['base_money'];
		
		//开始 循环cope_id
		
		$cope_arr = explode(',',$cope_number);
		//error_log(print_r($cope_arr,1));
		//开始获取应付数据
		$cope = [];
		for($i=0;$i<count($cope_arr);$i++){
			$cope_params=[
				'cope_number'=>$cope_arr[$i]	
			];
			
			$cope_result = $this->callSoaErp('post','/finance/getCope', $cope_params);
			$cope_result = $cope_result['data'][0];
			//剩余的是
			
			$cope_shengyu = $cope_result['cope_money']-$cope_result['true_receipt'];
			
		
			//再获取币种
			$cope_currency_id = $cope_result['cope_currency_id'];
			//提交审核数据
		
			//判断货币币种和付款币种是否一直
			
			if($cope_result['cope_currency_id'] == $params['base_currency_id']){
				
				$proportion = 1;
			}else{//否贼调取汇率
				$proportion_params = [
					'currency_id'=>$params['base_currency_id'],
					'opposite_currency_id'=>$cope_result['cope_currency_id'],
					'proportion_time'=>help::getLastMonthDay()
				];
				
				$proportion_result = $this->callSoaErp('post','/system/getOneCurrencyProportion',$proportion_params);
				
				$proportion = $proportion_result['data'][0]['currency_proportion'];
			}
			
			$cope_shengyu_proportion = $cope_shengyu*$proportion;
			$base_money = $base_money-$cope_shengyu_proportion;
			//代表可以插入数据
			if($cope_shengyu_proportion<$base_money){
			
				$cope_add_params['cope_number'] =$cope_result['cope_number'];
				$cope_add_params['payment_money'] =  $params['pay_type'];
				$cope_add_params['base_money'] =  $cope_shengyu;
				$cope[] = $cope_add_params;
				
				
			}

			
		}
		$user_id = session('user')['user_id'];
		//开始插入数据
		$data = [
				'voucher_time'=>$params['voucher_time'],
				'voucher_number'=>$params['voucher_number'],
				"payment_currency_id"=>1,
				'cope_info'=>$cope,
				'user_id'=>$user_id,
				'payment_type'=>$params['pay_type'],
				'remark'=>$params['remark'],
				'attachment'=>$params['attachment'],
				'receipts_pay_id'=>$result['data']
		
		];
	
		$result = $this->callSoaErp('post', '/finance/addCopeInfo',$data);
		

    	$data = [
    
    			'system_alert_event_id'=>25,
    
    	];
    	
    	  $this->callSoaErpTest('post','/system_alert_event/addInStationLetterAndEmail',$data);//getSingleSource
    
    	return $result;
    } 

}
