<?php

namespace app\index\model\system;
use think\Model;
use app\common\help\Help;
use think\config;
use think\Db;
class SupplierType extends Model{
    //protected $connection = ['database' => 'erp'];
    protected $table = 'supplier_type';
    private $_languageList;
    public function initialize()
    {
    	$this->_languageList = config('systom_setting')['language_list'];
    	parent::initialize();
    
    }


    /**
     * 添加资源类型
     * 胡
     */
    public function addSupplierType($params){
    	$t = time();

    	
    
    	$data['supplier_type_name'] = $params['supplier_type_name'];

    	$data['create_time'] = $t;  	
    	$data['create_user_id'] = $params['user_id'];
    	$data['status'] = $params['status'];   		

    
    	Db::startTrans();
    	try{
    		Db::name('supplier_type')->insert($data);
  
    		$result = 1;
    		// 提交事务
    		Db::commit();
    
    	} catch (\Exception $e) {
    		$result = $e->getMessage();
    		// 回滚事务
    		Db::rollback();
    		//\think\Response::create(['code' => '400', 'msg' =>$result], 'json')->send();
    		//exit();
    
    	}
    
    	return $result;
    }
    
    /**
     * 获取资源类型
     * 胡
     */
    public function getSupplierType($params){
    

    	$data = "1=1 ";
    	if(isset($params['supplier_type_name'])){
    		$data.= " and supplier_type.supplier_type_name like '%".$params['supplier_type_name']."%'";
    	}
    	if(isset($params['status'])){
    		$data.= " and supplier_type.status = ".$params['status'];
    	}
    	if(isset($params['supplier_type_id'])){
    		$data.= " and supplier_type.supplier_type_id = '".$params['supplier_type_id']."'";
    	}


            $result = $this->table("supplier_type")->alias('supplier_type')->
           
            
            where($data)->
            
            field(['supplier_type.supplier_type_id',"supplier_type.supplier_type_name",
            		'supplier_type.create_time', 'code_name',
            		"(select nickname  from user where user.user_id = supplier_type.create_user_id)"=>'create_user_name',
            		"(select nickname  from user where user.user_id = supplier_type.update_user_id)"=>'update_user_name',
            		'supplier_type.update_time',"supplier_type.status",
            ])->order("create_time desc")->select();
            
     


        return $result;
    
    }

    
    /**
     * 修改资源类型根据suppliertype_id
     */
    public function updateSupplierTypeBySupplierTypeId($params){
    
    	$t = time();
    	
		
    	if(!empty($params['supplier_type_name'])){
    		$data['supplier_type_name'] = $params['supplier_type_name'];
    		
    	}
	
    	if(!empty($params['supplier_type_id'])){
    		$data['supplier_type_id'] = $params['supplier_type_id'];
    		
    		
    	}   	
    	
	
    	if(!empty($params['status'])){
    		$data['status'] = $params['status'];
    		
    	}


    	$data['update_user_id'] = $params['user_id'];   
    	$data['update_time'] = $t;

    
    
    
    	Db::startTrans();
    	try{
    		Db::name('supplier_type')->where("supplier_type_id = ".$params['supplier_type_id'])->update($data);
    	
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