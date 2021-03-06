<?php
namespace app\index\model\operations;
use think\Model;
use app\common\help\Help;
use think\config;
use think\Db;

/*
* 待办模板设置 Hugh
*/
class Operations extends Model{ 
	private $_languageList;

    public function initialize()
    {
    	$this->_languageList = config('systom_setting')['language_list'];
    	parent::initialize(); 
    }

    //获取待办模板
    public function selOperations($params){
    	try{
	    	if($params['status']){
				$where['status'] = $params['status'];
	    	}
	    	if($params['company_id']){
	    		$where['company_id'] = $params['company_id'];
	    	}
	    	if($params['id']){
	    		$where['id'] = $params['id'];
	    	}
	    	if($params['has_template']){
	    		$where['has_template'] = $params['has_template'];
	    	}
            if($params['operations_type_id']){
                $where['operations_type_id'] = $params['operations_type_id'];
            }


	    	$return = $this->table('operations')->field(['id','company_id','name','ordering','has_template','email_template_id','remind_policy','remind_day','status','created_at','updated_at','operations_type_id'])->where($where)->order("ordering asc")->select();
	    	return $return;
		}catch (\Exception $e) { 
            return $result = $e->getMessage(); 
        } 
    }

    //添加待办模板
    public function addOperations($params){
    	try{
    		$d['company_id'] = $params['company_id'];	
	    	$d['name'] = $params['name'];	
	    	$d['ordering'] = $params['ordering'];	
	    	$d['has_template'] = $params['has_template'];	
	    	$d['remind_policy'] = $params['remind_policy'];	
	    	$d['remind_day'] = $params['remind_day'];	
	    	$d['status'] = 1;	
            $d['operations_type_id'] = $params['operations_type_id'];
	    	$d['created_at'] = date('Y-m-d H:i:s');
	    	$d['updated_at'] = date('Y-m-d H:i:s');
	    	// $d['create_user_id'] = $params['now_user_id'];
	    	// $d['updated_user_id'] = $params['now_user_id'];
	    	Db::table('operations')->insert($d);
	        $id = Db::name('operations')->getLastInsID(); 
	        return $id;
    	}catch (\Exception $e) { 
            return $result = $e->getMessage(); 
        } 
    	
    }

    //修改待办模板
    public function upOperations($params){
		try{ 
			$d['company_id'] = $params['company_id'];	
	    	$d['name'] = $params['name'];	
	    	$d['ordering'] = $params['ordering'];	
	    	$d['has_template'] = $params['has_template'];	
	    	$d['remind_policy'] = $params['remind_policy'];	
	    	$d['remind_day'] = $params['remind_day'];	
            $d['operations_type_id'] = $params['operations_type_id'];
	    	$d['updated_at'] = date('Y-m-d H:i:s');

	    	$where['id'] = $params['id'];

	    	Db::table('operations')->where($where)->update($d); 
	        return true;
		}catch (\Exception $e) { 
            return $result = $e->getMessage(); 
        } 
		
    }

    public function delOperation($params){
    	$id = $params['id'];
    	$where['id'] = $id;
    	$d['status'] = 2; 
    	$d['updated_at'] = date('Y-m-d H:i:s');
    	Db::table('operations')->where($where)->update($d); 
	    return true;
    }

    //添加待办邮件模板
    public function addOperationsEmail($params){
    	try{
    		$d['company_id'] = $params['company_id'];
	    	$d['name'] = $params['name'];
	    	$d['subject'] = $params['subject'];
	    	$d['content'] = $params['content'];
	    	$d['operation_id'] = $params['operation_id'];
	    	$d['created_at'] = date('Y-m-d H:i:s');
	    	$d['updated_at'] = date('Y-m-d H:i:s');
	    	$d['status'] = 1;
	    	Db::table('operations_email_templates')->insert($d);
	        $id = Db::name('operations_email_templates')->getLastInsID(); 
	        return $id;
    	}catch (\Exception $e) { 
            return $result = $e->getMessage(); 
        }  
    }

    //获取待办邮件模板
    public function selOperationsEmail($params){

    	if($params['status']){ 
    		$where['status'] = $params['status'];
    	}
    	if($params['id']){
    		$where['id'] = $params['id'];
    	}
    	if($params['company_id']){
    		$where['company_id'] = $params['company_id'];
    	}
    	if($params['operation_id']){
    		$where['operation_id'] = $params['operation_id'];
    	}

    	$return = $this->table('operations_email_templates')->field([
    		'operations_email_templates.id','operations_email_templates.company_id','name','subject','content','operation_id','created_at','updated_at'
    		,'(select company_name from company  where company.company_id=operations_email_templates.company_id) as company_name'
    		,'(select operations.name from operations where operations.id=operation_id) as operations_name'
    	])->where($where)->order('operation_id asc')->select();

    	return $return;

    }

    //修改邮件模板
    public function upOperationsEmail($params){
    	try{
    		$d['company_id'] = $params['company_id'];
	    	$d['name'] = $params['name'];
	    	$d['subject'] = $params['subject'];
	    	$d['content'] = $params['content'];
	    	$d['operation_id'] = $params['operation_id']; 
	    	$d['updated_at'] = date('Y-m-d H:i:s');
	    	$d['status'] = 1;
	    	Db::table('operations_email_templates')->where(['id'=>$params['id']])->update($d);
	        return true;
    	}catch (\Exception $e) { 
            return $result = $e->getMessage(); 
        }  
    }

    //删除邮件模板
    public function delOperationsEmail($params){
    	try{
    		$id = $params['id'];
	    	$where['id'] = $id;
	    	$d['status'] = 2; 
	    	$d['updated_at'] = date('Y-m-d H:i:s');
	    	Db::table('operations_email_templates')->where($where)->update($d); 
		    return true;
    	}catch (\Exception $e) { 
            return $result = $e->getMessage(); 
        } 
    	
    }

	//修改待办模板邮件模板选择
    public function ModifyMailTemplateSelection($params){
    	try{
    		$where['id'] = $params['id'];
	    	$u['email_template_id'] = $params['email_template_id'];
	    	Db::table('operations')->where($where)->update($u);
	    	return true;
    	}catch (\Exception $e) { 
            return $result = $e->getMessage(); 
        } 
    }

    //根据分公司订单ID获取订单待办模板 
    public function setCompanyOrderOperationsByCompanyOrderId($params){
        $where['company_order_id'] = $params['company_order_id'];

        if(is_numeric($params['is_visible'])){
            $where['is_visible'] = $params['is_visible'];
        }
        if(is_numeric($params['operations_type_id'])){
             $where['operations_type_id'] = $params['operations_type_id'];
        }

        $return = $this->table('company_order_operations')->alias('coo')->join('operations','operations.id=coo.operation_id')->field([
            'coo.id','coo.company_order_id','coo.operation_id','coo.operation_name','coo.status','coo.remark','coo.email_template_id','coo.is_email_sent','coo.remind_at','coo.remind_to','coo.is_visible','coo.created_at','coo.updated_at'
        ])->where($where)->order('coo.id asc')->select();

        
        foreach ($return as $key => $value) {
            $w['company_order_operations_id'] = $value['id'];
            $w['status'] = 1;
            $ar = $this->table('company_order_operations_attachments')->where($w)->select(); //获取附件
            $return[$key]['attachments'] = $ar;
            unset($w);
        }

        return $return;

    }

    //创建订单待办模板
    public function FoundCompanyOrderOperations($params){

        try{
            $this->startTrans();
            $company_order_id = $params['company_order_id'];
            $company_order_number = $params['company_order_number'];
            $company_id = $params['company_id'];

            //获取订单基本信息
            $w['company_order_number'] = $company_order_number;
            $company_order = $this->table('company_order')->where($w)->find();    
            unset($w);
            if(!empty($company_order)){
               $begin_time = $company_order['begin_time']; //出发
               $end_time = $company_order['end_time']; //结束
               $create_time = $company_order['create_time']; //创建

               //获取分公司待办
               $w['company_id'] = $company_id;
               $operations = $this->table('operations')->where($w)->order('ordering asc')->select();
               unset($w);
               foreach ($operations as $key => $value) {
                   $dd['company_order_id'] =  $company_order_id;
                   $dd['operation_id'] = $value['id'];
                   $dd['operation_name'] = $value['name'] ;
                   $dd['status'] = 1 ;
                   $dd['remark'] = '';
                   $dd['email_template_id'] = $value['email_template_id'] ; //邮件模板
                   $dd['is_email_sent'] = $value['has_template'] ; //是否邮件
                   
                   //[1=>'下单日期后',2=>'出团日期前',3=>'出团日期后'];
                   $remind_policy = $value['remind_policy'] ;
                   $remind_day = $value['remind_day'] ;
                   if($remind_policy==1){
                      $dd['remind_at'] = date('Y-m-d',strtotime("+ {$remind_day} days",$create_time));    
                   }elseif ($remind_policy == 2) {
                      $dd['remind_at'] = date('Y-m-d',strtotime("- {$remind_day} days",$begin_time)); 
                   }else{
                      $dd['remind_at'] = date('Y-m-d',strtotime("+ {$remind_day} days",$begin_time)); 
                   }

                   $dd['remind_to'] = $params['user_id']; //操作人
                   $dd['is_visible'] = 1; //是否可见
                   $dd['created_at'] = date('Y-m-d H:i:s');
                   $dd['updated_at'] = date('Y-m-d H:i:s');
                   Db::table('company_order_operations')->insert($dd); 
                   unset($dd);
               } 
             
             $operations_type = $this->table('operations_type')->where(['company_id'=>$company_id,'status'=>1])->order('id asc')->select(); 
             $operations_type_t = 0;
             if($operations_type[0]['id'])
               $operations_type_t = $operations_type[0]['id'];  
             $up['operations_type_id'] = $operations_type_t;
             Db::table('company_order')->where(['company_order_id'=>$company_order_id])->update($up);
             
             $this->commit();
              
              return $this->table('company_order_operations')->join('operations','operations.id=company_order_operations.operation_id')->where(['company_order_operations.company_order_id'=>$company_order_id,'operations.operations_type_id'=>$operations_type_t])->field('company_order_operations.*')->select();

            }
        }catch (\Exception $e) { 
            $this->rollback();
            return $result = $e->getMessage(); 
        }     
        

    }

    //添加待办类型
    public function addOperationsType($params){
        try{
            $d['company_id'] = $params['company_id'];
            $d['name'] = $params['name'];
            $d['status'] = $params['status'];
            $d['create_time'] = time();
            $d['update_time'] = time();
            $d['create_user_id'] = $params['now_user_id'];
            $d['update_user_id'] =$params['now_user_id'];
            Db::table('operations_type')->insert($d);
            $id = Db::name('operations_type')->getLastInsID(); 
            return $id;
        }catch (\Exception $e) {  
            return $result = $e->getMessage(); 
        } 
    }
 

    //获取待办类型
    public function selOperationsType($params){
        if($params['company_id'])
            $where['company_id'] = $params['company_id'];
        if(is_numeric($params['status'])){
            $where['status'] = $params['status'];
        }
        if($params['id']){
            $where['id'] = $params['id'];
        }

        return $this->table('operations_type')->where($where)->select();
    }

    //修改待办类型
    public function upOperationsType($params){
        try{
            $d['company_id'] = $params['company_id'];
            $d['name'] = $params['name'];
            $d['status'] = $params['status'];
            $d['update_time'] = time();
            $w['id'] = $params['id'];
            Db::table('operations_type')->where($w)->update($d);
            return true;

        }catch (\Exception $e) {  
            return $result = $e->getMessage(); 
        } 
    }

    //添加待办附件
    public function addCompanyOrderOperationsAttachments($params){
        try{
            $d['company_order_operations_id'] = $params['company_order_operations_id'];
            $d['company_order_id'] = $params['company_order_id'];
            $d['name'] = $params['name']; 
            $d['savepath'] = $params['savepath'];
            $d['uploaded_by'] = $params['uploaded_by'];
            $d['created_at'] = date('Y-m-d H:i:s');
            $d['updated_at'] = date('Y-m-d H:i:s'); 
            $d['status'] = 1;
            Db::table('company_order_operations_attachments')->insert($d);    
            $id = Db::name('operations')->getLastInsID(); 
            return $id;
        }catch (\Exception $e) {  
            return $result = $e->getMessage(); 
        }  

    }

    //删除待办附件
    public function delCompanyOrderOperationsAttachments($params){
        try{
            $w['id'] = $params['id'];
            $u['status'] = 2;
            return Db::table('company_order_operations_attachments')->where($w)->update($u);
        }catch (\Exception $e){  
            return $result = $e->getMessage(); 
        } 
    }

    //修改订单待办模板
    public function upCompanyOrderOperations($params){
        try{
            $w['id'] = $params['id'];
            $u['updated_at'] = date('Y-m-d H:i:s');
            if(isset($params['email_template_id'])){
               $u['email_template_id'] = $params['email_template_id'];           
            } 
            if(isset($params['remark'])){
                $u['remark'] = $params['remark'];
            }
            if(isset($params['remind_to'])){
                $u['remind_to'] = $params['remind_to'];
            }
            if(isset($params['status'])){
                $u['status'] = $params['status'];
            }

            return Db::table('company_order_operations')->where($w)->update($u);
        }catch (\Exception $e) {  
            return $result = $e->getMessage(); 
        } 
    }

    //根据订单获取选择的邮件模板信息
    public function selOperationsEmailTemplatesByCompanyOrderOperationsId($params){
        $w['company_order_operations.id'] = $params['id'];
        return $this->table('operations_email_templates')->join('company_order_operations','company_order_operations.email_template_id=operations_email_templates.id')
        ->field(['operations_email_templates.name','operations_email_templates.subject','operations_email_templates.content'])->where($w)->find();   
    }


    /**
     * 获取业务提醒列表
     * Created by PhpStorm.
     * User: Administrator
     * Date: 2019/4/25
     * Time: 15:41
     * @where array 条件数组
     * @filed string 字段
     * @return mixed 返回数据
     */
    public function getOperationsServiceReminderList($where, $filed = '*')
    {
        return $this->table('company_order_operations')->alias('coo')->field($filed)->join('company_order co', 'coo.company_order_id = co.company_order_id', 'INNER')->join('operations o', 'o.operations_type_id = co.operations_type_id and o.id = coo.operation_id', 'INNER')->where($where)->order('coo.remind_at')->select();

    }


}