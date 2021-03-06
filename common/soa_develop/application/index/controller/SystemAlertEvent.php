<?php
namespace app\index\controller;
use app\common\help\Help;
use think\config;
use think\Model;
use think\Controller;
use app\index\model\system\SupplierType;
use app\index\model\finance\FinanceApprove;
use app\index\model\system_alert_event\SystemAlertEvent as m_SystemAlertEvent;
use app\index\model\system_alert_event\Communal;
use app\index\model\system_alert_event\SystemAlertSetting;
use app\index\model\system_alert_event\InStationLetter;
use app\index\model\system_alert_event\SystemMail;
use app\index\model\system\User as UserModel;
use app\index\model\branchcompany\CompanyOrderProductTeam;
use app\index\model\branchcompany\CompanyOrder;
use app\index\model\product\TeamProduct;
use app\index\model\product\RouteTemplate;
use app\index\model\branchcompany\BranchProductRouteTemplate;
use app\index\model\branchcompany\BranchProduct;
use app\index\model\branchcompany\CompanyOrderProduct;
class SystemAlertEvent extends Base
{
	private $mCommunal; //公用
	private $msystemAlertSetting; //系统提醒设置
	private $mSystemAlertEvent; //系统提醒事件
	private $mInStationLetter; //站内信
	private $mSystemMail; //系统邮件

	public function __construct()
    {
    	$this->mCommunal = new Communal();
    	$this->msystemAlertSetting = new systemAlertSetting();
    	$this->mSystemAlertEvent = new m_SystemAlertEvent();
    	$this->mInStationLetter = new InStationLetter();
    	$this->mSystemMail = new SystemMail();
    	parent::__construct();
    }

    /**
    * 获取系统提醒事件
    * Hugh
    */
    public function getSystemAlertEvent(){ 
    	$params = $this->input(); 
		$result = $this->mSystemAlertEvent->getSystemAlertEvent($params);	
		$this->outPut($result);  
    }
 

    /**
	* 添加站内信	和 邮件	
	* Hugh
    */
    public function addInStationLetterAndEmail(){
    
    	$params = $this->input();
    
    	// $params['system_alert_event_id'] = 16;  //系统消息类型  
    	// $params['company_order_number'] = 'BO20181228224490';  
    	// $params['company_id'] = 3;
     
		$finance_approve = new FinanceApprove();
		$team_product = new TeamProduct();
		$company_order = new CompanyOrder();
		$company_order_product = new CompanyOrderProduct();
		$company_order_product_team = new CompanyOrderProductTeam();
		$route_template = new RouteTemplate();
		$branch_product_route_template = new BranchProductRouteTemplate();
		$branch_product = new BranchProduct();
		
    	$where['status'] = 1;
    	$where['system_alert_event_id'] = $params['system_alert_event_id'];
		$result = $this->mSystemAlertEvent->getSystemAlertEvent($where);
		
	
		if(empty($result)){
			$this->outPutError(['msg' => "该事件已被关闭"],$params);
		}	
    	unset($where);

    	//获取系统提醒设置
    	$where['system_alert_event_id'] = $params['system_alert_event_id'];
    	$where['company_id'] = $params['company_id']; 
    		
    	
    	
    	$SystemAlertSettingAry = $this->msystemAlertSetting->getSystemAlertSetting($where); 
    	
    
    	if($SystemAlertSettingAry){
    		if($SystemAlertSettingAry[0]['is_system_reminder']!=1 && $SystemAlertSettingAry[0]['is_email_reminder']!=1){
    			$this->outPutError(['msg' => "系统提醒和邮件提醒都未被开启"],$params);
    		}	
    	}

    	$Supplier_type = [
    		1=>['name'=>'地接',url=>''],
    		2=>['name'=>'酒店资源',url=>'/source/showHotelInfo?hotel_id='],
    		3=>['name'=>'用餐资源',url=>'/source/showDiningInfo?dining_id='],
    		4=>['name'=>'航班资源',url=>'/source/showFlightInfo?flight_id='],
    		5=>['name'=>'邮轮资源',url=>'/source/showCruisesSourceInfo?id='],
    		6=>['name'=>'签证资源',url=>'/source/showCruisesSourceInfo?id='],
    		7=>['name'=>'景点资源',url=>'/source/showScenicSpotSourceInfo?id='],
    		8=>['name'=>'车辆资源',url=>'/source/showVehicleSourceInfo?id='],
    		9=>['name'=>'导游资源',url=>'/source/showTourGuideSourceInfo?id='],
    		10=>['name'=>'单项资源',url=>'/source/showSingleSourceInfo?single_source_id='],
    		11=>['name'=>'自费项目',url=>'/source/showOwnExpenseInfo?own_expense_id='],
    		12=>['name'=>'其他',url=>''],
    	];
    	

 
    	switch ($params['system_alert_event_id']) {
    		case 1:
    		# 资源-编辑资源（参数：资源ID和资源类型）
    		$_supplier_type = $params['supplier_type'];
    		$_resource_id = $params['resource_id'];

    		$team_product_user_id_ar = $this->mCommunal->getTeamProductAllocationForTeamProductUserId($_supplier_type,$_resource_id); 

			$al['in_station_letter_id']=[];
			$al['system_mail_id'] = [];
    		foreach($team_product_user_id_ar as $k=>$v){
    			$add['system_alert_event_id'] = $params['system_alert_event_id'];
	    		$add['user_id'] = $v['team_product_user_id'];
	    		$add['email'] = $v['email'];
	    		
	    		$add['url'] = $Supplier_type[$params['supplier_type']]['url'].$params['resource_id'];
	    		$add['status'] = 1;

	    		if($SystemAlertSettingAry[0]['is_system_reminder']==1){ //是否系统提醒
	    			$add['content'] = $Supplier_type[$params['supplier_type']]['name'].'ID:'.$params['resource_id']."</br>{$SystemAlertSettingAry[0]['system_reminder_content']}";

	    			$al['in_station_letter_id'][] = $this->mInStationLetter->addInStationLetter($add);	
	    		}

	    		if($SystemAlertSettingAry[0]['is_email_reminder']==1){ //是否邮件提醒
					$add['content'] = $Supplier_type[$params['supplier_type']]['name'].'ID:'.$params['resource_id']."</br>{$SystemAlertSettingAry[0]['email_reminder_content']}";

					$al['system_mail_id'][] = $this->mSystemMail->addSystemMail($add);
	    		}
	    		unset($add);
    		}

			$this->outPut($al); 
    		break;
    		case $params['system_alert_event_id']==2 || $params['system_alert_event_id']==3:
    		#线路模板-编辑行程内容 #线路模板-编辑资源配置
    		$url = '/product/showRouteTemplateEdit?route_template_id=';
    		$team_product_user_id_ar = $this->mCommunal->getTeamProductUserIdByRouteTemplateId($params['route_template_id']); 	
			$al['in_station_letter_id']=[];
			$al['system_mail_id'] = [];
    		foreach($team_product_user_id_ar as $k=>$v){
    			$add['system_alert_event_id'] = $params['system_alert_event_id'];
	    		$add['user_id'] = $v['team_product_user_id'];
	    		$add['email'] = $v['email'];
	    		$add['url'] = $url.$params['route_template_id'];
	    		$add['status'] = 1; 

	    		if($SystemAlertSettingAry[0]['is_system_reminder']==1){ //是否系统提醒  (<a href='{$add['url']}'>链接到线路模板详情页面)</a>
	    			$add['content'] = '线路模板ID:'.$params['route_template_id']."</br>{$SystemAlertSettingAry[0]['system_reminder_content']}";

	    			$al['in_station_letter_id'][] = $this->mInStationLetter->addInStationLetter($add);	
	    		}

	    		
	    		if($SystemAlertSettingAry[0]['is_email_reminder']==1){ //是否邮件提醒 (<a href='{$add['url']}'>链接到线路模板详情页面)</a>
					$add['content'] = '线路模板ID:'.$params['route_template_id']."</br>{$SystemAlertSettingAry[0]['email_reminder_content']}";

					$al['system_mail_id'][] = $this->mSystemMail->addSystemMail($add);
	    		}
	    		unset($add);
	    	}
			$this->outPut($al);   
    		break; 
    		case $params['system_alert_event_id']==4||$params['system_alert_event_id']==5||$params['system_alert_event_id']==6||$params['system_alert_event_id']==7: 
    		$url = "/product/showPlanTourUpdate?plan_id=";
    		#团队产品-编辑基本信息 #团队产品-编辑行程内容 #团队产品-编辑资源配置 #团队产品-编辑产品报价
    		$team_product_user_id_ar = $this->mCommunal->getAnOrderOwnerBasedOnTheTeamProductIds($params['team_product_id']);
    		// var_dump($team_product_user_id_ar);exit;
    		$al['in_station_letter_id']=[];
			$al['system_mail_id'] = [];
			$company_id_ar[] = $params['company_id']; 
			$team_product_number = '';
    		foreach($team_product_user_id_ar as $k=>$v){
    			$add['system_alert_event_id'] = $params['system_alert_event_id'];
	    		$add['user_id'] = $v['create_user_id'];
	    		$add['email'] = $v['email'];
	    		$add['url'] = $url.$params['team_product_id'];
	    		$add['status'] = 1; 
				$team_product_number = $v['team_product_number'];
	    		if($SystemAlertSettingAry[0]['is_system_reminder']==1){ //是否系统提醒 (<a href='{$add['url']}'>链接到团队产品详情页面)</a>
	    			$add['content'] = '团队产品编号:'.$v['team_product_number']."</br>{$SystemAlertSettingAry[0]['system_reminder_content']}";

	    			$al['in_station_letter_id'][] = $this->mInStationLetter->addInStationLetter($add);	
	    		}

	    		
	    		if($SystemAlertSettingAry[0]['is_email_reminder']==1){ //是否邮件提醒 (<a href='{$add['url']}'>链接到团队产品详情页面)</a>
					$add['content'] = '团队产品编号:'.$v['team_product_number']."</br>{$SystemAlertSettingAry[0]['email_reminder_content']}";

					$al['system_mail_id'][] = $this->mSystemMail->addSystemMail($add);
	    		}

	    		if(!in_array($v['company_id'],$company_id_ar)){
					$company_id_ar[] = $v['company_id'];
	    		}

	    		unset($add);
	    	}

	    	//相关财务提醒
	    	if($params['system_alert_event_id']==7){
	    		//获取分公司财务
	    		$Uar = $this->mCommunal->getBrachFinance($company_id_ar);	
	    		// var_dump($Uar);
				foreach($Uar as $k=>$v){
    			$add['system_alert_event_id'] = $params['system_alert_event_id'];
	    		$add['user_id'] = $v['user_id'];
	    		$add['email'] = $v['email'];
	    		$add['url'] = $url.$params['team_product_id'];
	    		$add['status'] = 1; 

	    		if($SystemAlertSettingAry[0]['is_system_reminder']==1){ //是否系统提醒 (<a href='{$add['url']}'>链接到团队产品详情页面)</a>
	    			$add['content'] = '团队产品编号:'.$team_product_number."</br>{$SystemAlertSettingAry[0]['system_reminder_content']}";

	    			$al['in_station_letter_id'][] = $this->mInStationLetter->addInStationLetter($add);	
	    		}

	    		if($SystemAlertSettingAry[0]['is_email_reminder']==1){ //是否邮件提醒 (<a href='{$add['url']}'>链接到团队产品详情页面)</a>
					$add['content'] = '团队产品编号:'.$team_product_number."</br>{$SystemAlertSettingAry[0]['email_reminder_content']}";

					$al['system_mail_id'][] = $this->mSystemMail->addSystemMail($add);
	    		} 

	    		unset($add);
	    	}

	    	}

	    	// var_dump($company_id_ar);

			$this->outPut($al); 
    				
    		break;
    		case 8: 
    		#团队产品-确认/编辑游客
    		$url = '/product/showCompanyOrderCustomerEdit?company_order_number=';
    		$company_order_user_ar = $this->mCommunal->getAnOrderOwnerBasedAccordingToTheTouristId($params['company_order_customer_id']);
    		// var_dump($company_order_user_ar);exit;
    		$al['in_station_letter_id']=[];
			$al['system_mail_id'] = [];
    		foreach($company_order_user_ar as $k=>$v){
    			$add['system_alert_event_id'] = $params['system_alert_event_id'];
	    		$add['user_id'] = $v['create_user_id'];
	    		$add['email'] = $v['email'];
	    		$add['url'] = $url.$v['company_order_number'];
	    		$add['status'] = 1; 

	    		if($SystemAlertSettingAry[0]['is_system_reminder']==1){ //是否系统提醒 (<a href='{$add['url']}'>链接到订单详情页面)</a>
	    			$add['content'] = '订单编号:'.$v['company_order_number']."</br>{$SystemAlertSettingAry[0]['system_reminder_content']}";

	    			$al['in_station_letter_id'][] = $this->mInStationLetter->addInStationLetter($add);	
	    		}

	    		
	    		if($SystemAlertSettingAry[0]['is_email_reminder']==1){ //是否邮件提醒 (<a href='{$add['url']}'>链接到订单详情页面)</a>
					$add['content'] = '订单编号:'.$v['company_order_number']."</br>{$SystemAlertSettingAry[0]['email_reminder_content']}";

					$al['system_mail_id'][] = $this->mSystemMail->addSystemMail($add);
	    		}
	    		unset($add);
	    	}

			$this->outPut($al); 
    		break;
    		case 9: 
    		#团队产品-拼团操作
    		break;
    		case 10:
    		#团队产品-分团操作
    		break;
    		case 11:
    		#团队产品-成团操作
			$url = "/product/showCompanyOrderCustomerGuideReceipt?company_order_number=";
    		#团队产品-编辑基本信息 #团队产品-编辑行程内容 #团队产品-编辑资源配置 #团队产品-编辑产品报价
    		$team_product_user_id_ar = $this->mCommunal->getAnOrderOwnerBasedOnTheTeamProductIds($params['team_product_id']);
    		// var_dump($team_product_user_id_ar);exit;
    		 
    		$al['in_station_letter_id']=[];
			$al['system_mail_id'] = [];
    		foreach($team_product_user_id_ar as $k=>$v){
    			$add['system_alert_event_id'] = $params['system_alert_event_id'];
	    		$add['user_id'] = $v['create_user_id'];
	    		$add['email'] = $v['email'];
	    		$add['url'] = $url.$v['company_order_number'];
	    		$add['status'] = 1; 

	    		if($SystemAlertSettingAry[0]['is_system_reminder']==1){ //是否系统提醒 (<a href='{$add['url']}'>链接到订单详情页面)</a>
	    			$add['content'] = '订单编号:'.$v['company_order_number']."</br>{$SystemAlertSettingAry[0]['system_reminder_content']}";

	    			$al['in_station_letter_id'][] = $this->mInStationLetter->addInStationLetter($add);	
	    		}

	    		
	    		if($SystemAlertSettingAry[0]['is_email_reminder']==1){ //是否邮件提醒 (<a href='{$add['url']}'>链接到订单详情页面)</a>
					$add['content'] = '订单编号:'.$v['company_order_number']."</br>{$SystemAlertSettingAry[0]['email_reminder_content']}";

					$al['system_mail_id'][] = $this->mSystemMail->addSystemMail($add);
	    		}
	    		unset($add);
	    	}

			$this->outPut($al);  
	 		break;  
	 		case 12:
	 		#团队产品-编辑应付供应商
	 		//$url = "/product/PlanReceivableBranch?plan_id={$params['team_product_id']}&number={$params['team_product_number']}";
			$url = "/finance/showMustPayManage?team_product_number={$params['team_product_number']}";
			$al['in_station_letter_id']=[];
			$al['system_mail_id'] = [];
			$company_id_ar[] = $params['company_id'];
			$Uar = $this->mCommunal->getBrachFinance($company_id_ar);
			// var_dump($Uar);exit;
			foreach($Uar as $k=>$v){
    			$add['system_alert_event_id'] = $params['system_alert_event_id'];
	    		$add['user_id'] = $v['user_id'];
	    		$add['email'] = $v['email'];
	    		$add['url'] = $url;
	    		$add['status'] = 1; 

	    		if($SystemAlertSettingAry[0]['is_system_reminder']==1){ //是否系统提醒 (<a href='{$add['url']}'>链接到应付财务)</a>
	    			$add['content'] = '团队产品编号:'.$params['team_product_number']."</br>{$SystemAlertSettingAry[0]['system_reminder_content']}";

	    			$al['in_station_letter_id'][] = $this->mInStationLetter->addInStationLetter($add);	
	    		}

	    		if($SystemAlertSettingAry[0]['is_email_reminder']==1){ //是否邮件提醒  (<a href='{$add['url']}'>链接到应付财务)</a>
					$add['content'] = '团队产品编号:'.$params['team_product_number']."</br>{$SystemAlertSettingAry[0]['email_reminder_content']}";

					$al['system_mail_id'][] = $this->mSystemMail->addSystemMail($add);
	    		} 
 
	    		unset($add);
	    	} 

			$this->outPut($al); 

	 		
			break;
	 		case 13:
	 		#团队产品-编辑应收分公司	
	 		$url = "/finance/showMustPayManage?team_product_number={$params['team_product_number']}";
	 		$user_id_ar = $this->mCommunal->getAnOrderOwnerBasedAccordingToCompanyOrderNumbers($params['company_order_number']);
	 		$al['in_station_letter_id']=[];
			$al['system_mail_id'] = [];
			$company_id_ar = []; 
	 		// var_dump($Uar);exit;


			foreach($user_id_ar as $k=>$v){
    			$add['system_alert_event_id'] = $params['system_alert_event_id'];
	    		$add['user_id'] = $v['user_id'];
	    		$add['email'] = $v['email'];
	    		$add['url'] = $url;
	    		$add['status'] = 1; 

	    		if($SystemAlertSettingAry[0]['is_system_reminder']==1){ //是否系统提醒 (<a href='{$add['url']}'>链接到应付财务)</a>
	    			$add['content'] = '团队产品编号:'.$params['team_product_number']."</br>{$SystemAlertSettingAry[0]['system_reminder_content']}";

	    			$al['in_station_letter_id'][] = $this->mInStationLetter->addInStationLetter($add);	
	    		}

	    		if($SystemAlertSettingAry[0]['is_email_reminder']==1){ //是否邮件提醒 (<a href='{$add['url']}'>链接到应付财务)</a>
					$add['content'] = '团队产品编号:'.$params['team_product_number']."</br>{$SystemAlertSettingAry[0]['email_reminder_content']}";

					$al['system_mail_id'][] = $this->mSystemMail->addSystemMail($add);
	    		} 

				if(!in_array($v['company_id'],$company_id_ar)){
					$company_id_ar[] = $v['company_id'];
	    		}
	    		unset($add);
	    	}


			$Uar = $this->mCommunal->getBrachFinance($company_id_ar);	
	    	foreach($Uar as $k=>$v){
    			$add['system_alert_event_id'] = $params['system_alert_event_id'];
	    		$add['user_id'] = $v['user_id'];
	    		$add['email'] = $v['email'];
	    		$add['url'] = $url;
	    		$add['status'] = 1; 

	    		if($SystemAlertSettingAry[0]['is_system_reminder']==1){ //是否系统提醒 (<a href='{$add['url']}'>链接到应付财务)</a>
	    			$add['content'] = '团队产品编号:'.$params['team_product_number']."</br>{$SystemAlertSettingAry[0]['system_reminder_content']}";

	    			$al['in_station_letter_id'][] = $this->mInStationLetter->addInStationLetter($add);	
	    		}

	    		if($SystemAlertSettingAry[0]['is_email_reminder']==1){ //是否邮件提醒 (<a href='{$add['url']}'>链接到应付财务)</a>
					$add['content'] = '团队产品编号:'.$params['team_product_number']."</br>{$SystemAlertSettingAry[0]['email_reminder_content']}";

					$al['system_mail_id'][] = $this->mSystemMail->addSystemMail($add);
	    		}  
	    		unset($add);
	    	}

			$this->outPut($al); 

	 		break;
	 		case 14:
	 		#财务-地接报账
	 		break;
	 		case 15:
	 		#财务-确认实收款
	 		
	 		break;
	 		case $params['system_alert_event_id']==16 || $params['system_alert_event_id']==17 || $params['system_alert_event_id']==18 || $params['system_alert_event_id']==19 || $params['system_alert_event_id']==20 || $params['system_alert_event_id']==21:
	 		#订单-提交订单 / 订单-取消订单 / 订单-订单状态变更 / 订单-编辑游客信息 / 订单-编辑产品 / 订单-编辑报价
	 		$user_ar = $this->mCommunal->getTeamLeaderToCompanyOrderNumber($params['company_order_number']);

	 		$al['in_station_letter_id']=[];
			$al['system_mail_id'] = [];
			 
	 		// var_dump($Uar);exit;
			foreach($user_ar as $k=>$v){
    			$add['system_alert_event_id'] = $params['system_alert_event_id'];
	    		$add['user_id'] = $v['team_product_user_id'];
	    		$add['email'] = $v['email'];
	    		//$add['url'] = "/product/PlanBooking?plan_id={$v['team_product_id']}&number={$v['team_product_number']}&search_booking={$params['company_order_number']}";
	    		$add['url'] = "/product/PlanBookingInfo?bookingId={$params['company_order_number']}&plan_id={$v['team_product_id']}&number={$v['team_product_number']}";
	    		$add['status'] = 1; 

	    		if($SystemAlertSettingAry[0]['is_system_reminder']==1){ //是否系统提醒 (<a href='{$add['url']}'>团队产品-订单-订单详情页面)</a>
	    			$add['content'] = '订单编号:'.$params['company_order_number']."</br>{$SystemAlertSettingAry[0]['system_reminder_content']}";

	    			$al['in_station_letter_id'][] = $this->mInStationLetter->addInStationLetter($add);	
	    		}

	    		if($SystemAlertSettingAry[0]['is_email_reminder']==1){ //是否邮件提醒 (<a href='{$add['url']}'>团队产品-订单-订单详情页面)</a>
					$add['content'] = '订单编号:'.$params['company_order_number']."</br>{$SystemAlertSettingAry[0]['email_reminder_content']}"; 
					$al['system_mail_id'][] = $this->mSystemMail->addSystemMail($add);
	    		}  
	    		unset($add);
	    	}
			$this->outPut($al); 

	 		break;
	 		case 22:
	 			# 订单-编辑发票信息

	 		break;
	 		case 23:
	 			# 订单-编辑代理信息

	 		break;
	 		case 24:
	 			# 订单-编辑销售收款

	 		break;
	 		case 25:
	 			$add['system_alert_event_id'] = $params['system_alert_event_id'];
	 			# 财务审批提交
	 			$user = new UserModel();
	 			$user_params = [
	 				'company_id'=>$params['user_company_id'],
	 				'role_id'=>5	
	 			];
	 			$user_result = $user->getUser($user_params);
	 			
	 			for($i=0;$i<count($user_result);$i++){
	 				
	 				$add['email'] =$user_result[$i]['email'];
	 				$add['url'] = "/examine_and_approve/financeApproveByMe";
	 				$add['status'] = 1;
	 				$add['user_id'] = $user_result[$i]['user_id'];
	 				
	 			
	 				if($SystemAlertSettingAry[0]['is_system_reminder']==1){ //是否系统提醒  (<a href='{$add['url']}'>链接到线路模板详情页面)</a>
	 						
	 					$add['content'] = '财务审批'."</br>{$SystemAlertSettingAry[0]['system_reminder_content']}";
	 			
	 					$this->mInStationLetter->addInStationLetter($add);
	 				}
	 					
	 				
	 				if($SystemAlertSettingAry[0]['is_email_reminder']==1){ //是否邮件提醒 (<a href='{$add['url']}'>链接到线路模板详情页面)</a>
	 					$add['content'] = '财务审批'."</br>{$SystemAlertSettingAry[0]['email_reminder_content']}";
	 					 
	 					$this->mSystemMail->addSystemMail($add);
	 				}
	 			}
	 			$this->outPut(1);
	 			break;
	 		case 26:
	 			$add['system_alert_event_id'] = $params['system_alert_event_id'];
	 			# 财务已审核
	 			//首先查询该数据的创建人
	 			//通过财务审批ID查询创建人信息
	 			$finance_approve_params = [
	 				'finance_approve_id'=>$params['finance_approve_id']
	 			];
	 			
	 			$finance_approve_result = $finance_approve->getFinanceApprove($finance_approve_params);
	 			$to_user_id = $finance_approve_result[0]['create_user_id'];
	 			
	 		
	 			
	 			$user = new UserModel();
	 			$user_params = [
	 				'user_id'=>$to_user_id	
	 			];
	 			$user_result = $user->getUser($user_params);
	 		
	 			$add['email'] =$user_result[0]['email'];
	 			$add['url'] = "/examine_and_approve/financeApproveByMe";
	 			$add['status'] = 1;
	 			$add['user_id'] = $to_user_id;
	 			
	 	
	 			
	 			if($SystemAlertSettingAry[0]['is_system_reminder']==1){ //是否系统提醒  (<a href='{$add['url']}'>链接到线路模板详情页面)</a>
	 			
	 				$add['content'] = '财务审批ID:'.$params['finance_approve_id']."</br>{$SystemAlertSettingAry[0]['system_reminder_content']}";
	 	
	 				$this->mInStationLetter->addInStationLetter($add);
	 			}
	 			
	 			 
	 			if($SystemAlertSettingAry[0]['is_email_reminder']==1){ //是否邮件提醒 (<a href='{$add['url']}'>链接到线路模板详情页面)</a>
	 				$add['content'] = '财务审批ID:'.$params['finance_approve_id']."</br>{$SystemAlertSettingAry[0]['email_reminder_content']}";
	 	
	 				$this->mSystemMail->addSystemMail($add);
	 			}	 			
	 			$this->outPut(1);
	 			break;	
	 		case 27://添加订单留言通知团队负责人
	 				$add['system_alert_event_id'] = $params['system_alert_event_id'];
	 				# 财务已审核
	 				//首先查询该数据的创建人
	 				//通过财务审批ID查询创建人信息
	 				$company_order_product_team_params = [
	 					'company_order_number'=>$params['company_order_number'],
	 					'status'=>1
	 				];
					 			
	 				$company_order_product_team_result  = $company_order_product_team->getCompanyOrderProductTeam($company_order_product_team_params);
	 				
					
	 				
	 				$user = new UserModel();
	 				for($j=0;$j<count($company_order_product_team_result);$j++){
	 					//通过团队产品ID查找团队产品负责人ID
	 					
	 					$team_product_params = [
	 						'team_product_id'=>$company_order_product_team_result[$j]['team_product_id']	
	 					];
	 					$team_product_result = $team_product->getTeamProduct($team_product_params);
	 					
	 				
	 					$to_user_id = $team_product_result[0]['team_product_user_id'];
	 					

	 					$user_params = [
	 							'user_id'=>$to_user_id
	 					];
	 					$user_result = $user->getUser($user_params);
	 						
	 					$add['email'] =$user_result[0]['email'];
	 					$add['url'] = "/product/PlanBookingInfo?bookingId=".$params['company_order_number']."&plan_id=".$company_order_product_team_result[$j]['team_product_id'];
	 					$add['status'] = 1;
	 					$add['user_id'] = $to_user_id;
	 						
	 						
	 						
	 					if($SystemAlertSettingAry[0]['is_system_reminder']==1){ //是否系统提醒  (<a href='{$add['url']}'>链接到线路模板详情页面)</a>
	 					
	 						$add['content'] = '留言板订单编号:'.$params['company_order_number']."</br>{$SystemAlertSettingAry[0]['system_reminder_content']}";
	 					
	 						$this->mInStationLetter->addInStationLetter($add);
	 					}
	 						
	 						
	 					if($SystemAlertSettingAry[0]['is_email_reminder']==1){ //是否邮件提醒 (<a href='{$add['url']}'>链接到线路模板详情页面)</a>
	 						$add['content'] = '留言板订单编号:'.$params['company_order_number']."</br>{$SystemAlertSettingAry[0]['email_reminder_content']}";
	 					
	 						$this->mSystemMail->addSystemMail($add);
	 					}
	 						
	 				}
	 				
	 				$this->outPut(1);
	 					
	 			
	 					

	 				break;	
	 			case 28://团队产品添加留言板通知订单负责人
	 					$add['system_alert_event_id'] = $params['system_alert_event_id'];
	 					# 财务已审核
	 					//首先查询该数据的创建人
	 					//通过财务审批ID查询创建人信息
	 					$company_order_params = [
	 						'company_order_number'=>$params['company_order_number'],
	 				
	 					];
	 						
	 					$company_order_product_team_result  = $company_order->getCompanyOrder($company_order_params);
	 				
	 						
	 				
	 					$user = new UserModel();
	 					for($j=0;$j<count($company_order_product_team_result);$j++){
	 						//通过团队产品ID查找团队产品负责人ID
	 							

	 						$to_user_id = $company_order_product_team_result[0]['create_user_id'];
	 							
	 				
	 						$user_params = [
	 								'user_id'=>$to_user_id
	 						];
	 						$user_result = $user->getUser($user_params);
	 				
	 						$add['email'] =$user_result[0]['email'];
	 						$add['url'] = "/branchcompany/companyOrderManage?company_order_number=".$params['company_order_number'];
	 						$add['status'] = 1;
	 						$add['user_id'] = $to_user_id;
	 				
	 				
	 				
	 						if($SystemAlertSettingAry[0]['is_system_reminder']==1){ //是否系统提醒  (<a href='{$add['url']}'>链接到线路模板详情页面)</a>
	 					 		
	 							$add['content'] = '留言板订单编号:'.$params['company_order_number']."</br>{$SystemAlertSettingAry[0]['system_reminder_content']}";
	 					 		
	 							$this->mInStationLetter->addInStationLetter($add);
	 						}
	 				
	 				
	 						if($SystemAlertSettingAry[0]['is_email_reminder']==1){ //是否邮件提醒 (<a href='{$add['url']}'>链接到线路模板详情页面)</a>
	 							$add['content'] = '留言板订单编号:'.$params['company_order_number']."</br>{$SystemAlertSettingAry[0]['email_reminder_content']}";
	 					 		
	 							$this->mSystemMail->addSystemMail($add);
	 						}
	 				
	 					}
	 				
	 					$this->outPut(1);
	 						
	 						
	 						
	 				
	 		break;	
	 		case 29://线路模板留言板添加 通知 团队 以及 分公司产品 的负责人
	 				$add['system_alert_event_id'] = $params['system_alert_event_id'];
	 				# 财务已审核
	 				//首先查询该数据的创建人
	 				//通过财务审批ID查询创建人信息
	 				$route_template_params = [
	 					'route_template_id'=>$params['route_template_id'],
	 			
	 				];
	 				
	 				//通过线路模板ID获取线路模板编号
	 				
	 				$route_template_result = $route_template->getRouteTemplate($route_template_params);
	 			
	 				//$route_template = new RouteTemplate();
	 				//$branch_product_route_template = new BranchProductRouteTemplate();
	 				
	 				$team_product_result = $team_product->getTeamProduct($route_template_params);
	 		
	 				$route_number = $route_template_result[0]['route_number'];
	 			
	 			
	 				$user = new UserModel();
	 				
	 				//开始查找团队产品
	 				for($j=0;$j<count($team_product_result);$j++){
	 					//通过团队产品ID查找团队产品负责人ID
	 						
	 			
	 					$to_user_id = $team_product_result[$j]['team_product_user_id'];
	 						
	 			
	 					$user_params = [
	 							'user_id'=>$to_user_id
	 					];
	 					$user_result = $user->getUser($user_params);
	 			
	 					$add['email'] =$user_result[0]['email'];
	 					$add['url'] = "/product/showRouteTemplateEdit?route_template_id=".$params['route_template_id'];
	 					$add['status'] = 1;
	 					$add['user_id'] = $to_user_id;
	 			
	 			
	 			
	 					if($SystemAlertSettingAry[0]['is_system_reminder']==1){ //是否系统提醒  (<a href='{$add['url']}'>链接到线路模板详情页面)</a>
	 			
	 						$add['content'] = '线路模板ID:'.$params['route_template_id']."</br>{$SystemAlertSettingAry[0]['system_reminder_content']}";
	 			
	 						$this->mInStationLetter->addInStationLetter($add);
	 					}
	 			
	 			
	 					if($SystemAlertSettingAry[0]['is_email_reminder']==1){ //是否邮件提醒 (<a href='{$add['url']}'>链接到线路模板详情页面)</a>
	 						$add['content'] = '线路模板ID:'.$params['route_template_id']."</br>{$SystemAlertSettingAry[0]['email_reminder_content']}";
	 			
	 						$this->mSystemMail->addSystemMail($add);
	 					}
	 			
	 				}
	 				
	 				//开始查找分公司产品
	 				//首先查询 用线路模板的分公司
	 				$branch_product_route_template_params = [
	 					'route_number'=>$route_number	
	 				];
	 				$branch_product_route_template_result = $branch_product_route_template->getBranchProductRouteTemplate($branch_product_route_template_params);
	 			
	 				for($j=0;$j<count($branch_product_route_template_result);$j++){
	 					$branch_product_params = [
	 							'branch_product_number'=>$branch_product_route_template_result[$j]['branch_product_number']
	 					];
	 					$branch_product_result = $branch_product->getBranchProduct($branch_product_params);
	 						
	 				
	 					$to_user_id = $branch_product_result[$j]['create_user_id'];
	 						
	 					if(is_numeric($to_user_id)){
	 						$user_params = [
	 								'user_id'=>$to_user_id
	 						];
	 						$user_result = $user->getUser($user_params);
	 						
	 						$add['email'] =$user_result[0]['email'];
	 						$add['url'] = "/product/showRouteTemplateEdit?route_template_id=".$params['route_template_id'];
	 						$add['status'] = 1;
	 						$add['user_id'] = $to_user_id;
	 						
	 						
	 						
	 						if($SystemAlertSettingAry[0]['is_system_reminder']==1){ //是否系统提醒  (<a href='{$add['url']}'>链接到线路模板详情页面)</a>
	 								
	 							$add['content'] = '线路模板ID:'.$params['route_template_id']."</br>{$SystemAlertSettingAry[0]['system_reminder_content']}";
	 								
	 							$this->mInStationLetter->addInStationLetter($add);
	 						}
	 						
	 						
	 						if($SystemAlertSettingAry[0]['is_email_reminder']==1){ //是否邮件提醒 (<a href='{$add['url']}'>链接到线路模板详情页面)</a>
	 							$add['content'] = '线路模板ID:'.$params['route_template_id']."</br>{$SystemAlertSettingAry[0]['email_reminder_content']}";
	 								
	 							$this->mSystemMail->addSystemMail($add);
	 						}
	 							 						
	 					}

	 						
	 						
	 				}
	 				
	 				
	 				
	 				
	 				$this->outPut(1);
	 			
	 			
	 			
	 			
	 		break;	
	 		case 30://线路模板留言板添加 通知 团队 以及 分公司产品 的负责人
	 			$add['system_alert_event_id'] = $params['system_alert_event_id'];
	

				$company_order_product_params = [
					'branch_product_number'=>$params['branch_product_number']	
				];
	 		
	 			$company_order_product_result = $company_order_product->getCompanyOrderProduct($company_order_product_params);
	 		
	 
	 				
	 			error_log(print_r(help::modelDataToArr($company_order_product_result),1));
	 			$user = new UserModel();
	 		
	 			//开始查找团队产品
	 			for($j=0;$j<count($company_order_product_result);$j++){
	 				//通过团队产品ID查找团队产品负责人ID
	 				$company_order_params = [
	 					'company_order_number'=>$company_order_product_result[$j]['company_order_number']	
	 				];
	 				$company_order_result = $company_order->getCompanyOrder($company_order_params);
	 				
	 				
	 			 	
	 				$to_user_id = $company_order_result[0]['create_user_id'];
	 		
	 			 	if(is_numeric($to_user_id)){
	 			 		$user_params = [
	 			 				'user_id'=>$to_user_id
	 			 		];
	 			 		$user_result = $user->getUser($user_params);
	 			 			
	 			 		$add['email'] =$user_result[0]['email'];
	 			 		$add['url'] = "/branchcompany/showBranchProductInfo?branch_product_number=".$params['branch_product_number'];
	 			 		$add['status'] = 1;
	 			 		$add['user_id'] = $to_user_id;
	 			 			
	 			 			
	 			 			
	 			 		if($SystemAlertSettingAry[0]['is_system_reminder']==1){ //是否系统提醒  (<a href='{$add['url']}'>链接到线路模板详情页面)</a>
	 			 		
	 			 			$add['content'] = '代售产品编号:'.$params['branch_product_number']."</br>{$SystemAlertSettingAry[0]['system_reminder_content']}";
	 			 		
	 			 			$this->mInStationLetter->addInStationLetter($add);
	 			 		}
	 			 			
	 			 			
	 			 		if($SystemAlertSettingAry[0]['is_email_reminder']==1){ //是否邮件提醒 (<a href='{$add['url']}'>链接到线路模板详情页面)</a>
	 			 			$add['content'] = '代售产品编号:'.$params['branch_product_number']."</br>{$SystemAlertSettingAry[0]['email_reminder_content']}";
	 			 		
	 			 			$this->mSystemMail->addSystemMail($add);
	 			 		}	 			 		
	 			 		
	 			 	}
	 			 	

	 			 	
	 			}
	 		
	 		
	 		
	 		
	 			$this->outPut(1);
	 				
	 				
	 				
	 				
	 			break;	 		
    		default:
    			# code...
    			break;
    	}

    }

    /***
    * 获取系统提醒列表
    */
    public function getSystemReminderList(){
    	$params = $this->input(); 
    	// $params['company_id'] = 3; 
    	$result = $this->msystemAlertSetting->getSystemReminderList($params);
		$this->outPut($result);  

    }

    /***
    * 添加分公司消息提醒（添加分公司信息时执行批处理）
    */
    public function addSystemReminder(){
    	$params = $this->input(); 
    	// $params['company_id'] = 2; 
    	// $params['user_id'] = 394;

    	$result = $this->msystemAlertSetting->AddSystemReminder($params);
    	$this->outPut($result);  	 
    }


    /***
    * 修改分公司消息提醒
    */
    public function updateSystemReminder(){
    	$params = $this->input(); 

    	// $params['system_alert_setting_id']=1;
    	// $params['is_system_reminder']=2;
    	// $params['system_reminder_content']='资源信息已更新2';
    	// $params['is_email_reminder']=2;
    	// $params['email_reminder_content']='资源信息已更新2';
    	// $params['update_time'] = time();
    	// $params['user_id'] = 33;

    	$result = $this->msystemAlertSetting->updateSystemReminder($params);
    	$this->outPut($result);  
    }


    /***
    * 批量修改分公司消息提醒状态
    */
    public function batchEditSystemReminderState(){
    	$params = $this->input(); 

    	$result = $this->msystemAlertSetting->batchEditSystemReminderState($params);
    	$this->outPut($result);  
    
    }


    /*
    * 获取站内信
    */
    public function getInStationLetter(){
    	$params = $this->input(); 
    
    	// $params['user_id'] = 394;
    	// $params['status'] = 1;
    	// $params['sTime'] = time();
    	// $params['eTime'] = time();

    	$result = $this->mInStationLetter->getInStationLetter($params);
    	$this->outPut($result);	
    }

    /*
    * 修改站内信状态为已读
    */
    public function readInStationLetter(){
    	$params = $this->input();

    	$result = $this->mInStationLetter->readInStationLetter($params);
    	$this->outPut($result);
    }
	

}