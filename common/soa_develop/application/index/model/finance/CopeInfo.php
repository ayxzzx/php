<?php

namespace app\index\model\finance;
use think\Model;
use app\common\help\Help;
use think\config;
use think\Db;
class CopeInfo extends Model{
    //protected $connection = ['database' => 'erp'];
    protected $table = 'cope_info';
    private $_languageList;
    public function initialize()
    {
        $this->_languageList = config('systom_setting')['language_list'];
        parent::initialize();

    }

    /**
     *添加批量应付
     * 王
     */
    public function addCopeInfo($params){
        $t = time();
		
		$data['cope_voucher'] = $params['cope_voucher'];
		$data['voucher_time'] = $params['voucher_time'];
		$data['cope_number'] = $params['cope_number'];
		$data['receivable_currency_id'] = $params['receivable_currency_id'];
		$data['receivable_type'] = $params['receivable_type'];
		$data['receivable_number'] = $params['receivable_number'];
		$data['receivable_money'] = $params['receivable_money'];
		$data['create_time'] = $params['create_time'];
		$data['update_time'] = $params['update_time'];
		$data['create_user_id'] = $params['create_user_id'];
		$data['update_user_id'] = $params['update_user_id'];
		$data['status'] = 1;
  
		if(!empty($params['receivable_type'])){
			$data['receivable_type'] = $params['receivable_type'];
		}
		if(!empty($params['receivable_number'])){
			$data['receivable_number'] = $params['receivable_number'];
		}
		
		if(!empty($params['attachment'])){
			$data['attachment'] = $params['attachment'];
		}
		if(!empty($params['remark'])){
			$data['remark'] = $params['remark'];
		}
		if(is_numeric($params['receipts_pay_id'])){
			$data['receipts_pay_id'] = $params['receipts_pay_id'];
		}
		if(is_numeric($params['finance_approve_id'])){
			$data['finance_approve_id'] = $params['finance_approve_id'];
		}		
		if(is_numeric($params['base_money'])){
			$data['base_money'] = $params['base_money'];
		}
        $this->startTrans();
        try{
			$result = $this->insert($data);
			
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
     * 修改批量应付根据cope_number
     */
    public function updateCopeInfoByCopeNumber($params){

        $t = time();
        if(!empty($params['receivable_voucher'])){
            $data['receivable_voucher'] = $params['receivable_voucher'];

        }
        if(!empty($params['voucher_time'])){
            $data['voucher_time'] = $params['voucher_time'];

        }
        if(!empty($params['receivable_type'])) {
            $data['receivable_type'] = $params['receivable_type'];

        }
        if(is_numeric($params['status'])){
            $data['status'] = $params['status'];

        }
        $data['update_user_id'] = $params['user_id'];
        $data['update_time'] = $t;

        $data_where['cope_number'] = $params['cope_number'];
        $this->startTrans();
        try{
            Db::table('cope_info')->where($data_where)->update($data);
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
     * 获取应付详情
     * 王
     */
    public function getCopeInfo($params)
    {
    	$data = "1=1 and cope_info.status = 1";
    
    	if(!empty($params['cope_number'])){
    		$data.= " and cope_info.cope_number = '".$params['cope_number']."'";
    	}
    	if(is_numeric($params['cope_info_id'])){
    		$data.= " and cope_info.cope_info_id = ".$params['cope_info_id'];
    	}
    	if(is_numeric($params['status'])){
    		$data.= " and cope_info.status = ".$params['status'];
    	}

    	if(!empty($params['company_order_number'])){
    		 
    		$data.= " and cope.order_number = '".$params['company_order_number']."'";
    	}
    
    	$result = $this->table("cope_info")->alias('cope_info')->
    	join("cope","cope.cope_number = cope_info.cope_number",'left')->
    	where($data)->
    	field(['cope_info.cope_info_id','cope.team_product_id','cope.source_type_id','cope.product_name',
    		   'cope_info.receivable_money',  'cope_info.receivable_currency_id',
				'cope_info.cope_voucher','cope_info.voucher_time','cope_info.receivable_type',
    			'cope_info.create_user_id','cope_info.create_time','cope_info.update_user_id',
    			'cope_info.remark','cope_info.attachment',
    			"(select  unit from currency where  currency.currency_id = cope_info.receivable_currency_id) as unit",
    			"(select nickname  from user where user.user_id = cope_info.create_user_id)"=>'create_user_name',
    			'cope.receivable_object_type','cope.receivable_object_id',
    			'cope_info.update_time','cope_info.status'])->select();
    
    	return $result;
    
    
    }
    /**
     * 通过记账ID 获取 金额
     */
    public function getCopeInfoByReceiptsPayId($params){
    	$data="  1=1 ";
    	if(!empty($params['receipts_pay_id'])){
    		$data.=" and receipts_pay_id = ".$params['receipts_pay_id'];
    	}
    	$sql="select  if(sum(base_money)>0,sum(base_money),0)  as true_cope from cope_info where  $data and status = 1";
  
    	$result = $this->query($sql);
    	 
    	return $result;
    }

}