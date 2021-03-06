<?php

namespace app\index\model\system;
use think\Model;
use think\Db;
use app\common\help\Help;
class Tag extends Model{
    
    protected $table = 'tag';
    private $_languageList;
    
    public function initialize()
    {
        $this->_languageList = config('systom_setting')['language_list'];
        parent::initialize();
    }

    /**
     * getTag
     *
     * 获取标签列表
     * @author shj
     *
     * @param      $params
     * @param bool $is_count
     *
     * @return false|int|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Date: 2019/3/29
     * Time: 15:59
     */
    public function getTag($params,$is_count=false,$is_page=false,$page=null,$page_size=20){
        
        $data = "1=1 ";
        if(!empty($params['tag_id'])){
            $data.= " and tag.tag_id= ".$params['tag_id'];
        }
        if(!empty($params['status'])){
            $data.= " and tag.status = 1";
        }
        if(!empty($params['tag_name'])){
            $data.= " and tag.tag_name like'%".$params['tag_name']."%'";
        }
        if(!empty($params['language_id'])){
            $data.= " and tag.language_id = '".$params['language_id']."'";
        }
        if($params['type_name']){
            $data.= " and tag.type_name = '".$params['type_name']."'";
        }
        if($params['code_name']){
            $data.= " and tag.code_name = '".$params['code_name']."'";
        }

        if($is_count==true){
            $result = $this->where($data)->count();
        }else{
            if($is_page==true) {
                $result = $this->table("tag")->
                join('user', 'user.user_id = tag.update_user_id', 'left')->
                where($data)->order('create_time desc')->limit($page, $page_size)->
                field(['tag.*', 'user.nickname'])->order("update_time desc")->select();
            }else{
                $result = $this->table("tag")->
                join('user', 'user.user_id = tag.update_user_id', 'left')->
                where($data)->order('create_time desc')->
                field(['tag.*', 'user.nickname'])->order("update_time desc")->select();
            }
        }
        return $result;

    }

    public function getTagByLanguageId($params)
    {
        if ($params['user_company_id'])
        {
            $data = " tag.company_id = ".$params['user_company_id'];
        }
  
        $result = $this->table("tag")->where($data)->order('create_time desc')->field("tag.tag_name")->select();
      

        if (empty($result))
        {
            $data = " tag.company_id = 0 ";
        }
    
        $data .= " and language_id in (1," . $params['language_id'] . ")";
        $result = $this->table("tag")->where($data)->order('create_time desc')->field("tag.tag_name, tag.language_id, tag.code_name")->select();
      
        return $result;

    }

    /**
     * 修改标签 根据tag_name
     */
    public function updateTagByCodeName($params){
        $t = time();
        if(!empty($params['tag_name'])){
            $data['tag_name'] = $params['tag_name'];
        }
        if(!empty($params['type_name'])){
            $data['type_name'] = $params['type_name'];
        }
        if(!empty($params['language_id'])){
            $data['language_id'] = $params['language_id'];
        }
        if(!empty($params['code_name'])){
            $data['code_name'] = $params['code_name'];
        }
        $data['update_user_id'] = $params['user_id'];
        $data['update_time'] = $t;

        Db::startTrans();
        try{
            $where['code_name'] = $params['code_name'];
            $where['language_id'] = $params['language_id'];
            $tag = Db::name('tag')->where($where)->find();
            if($tag){
                Db::name('tag')->where($where)->update($data);
            }else{
                $data['create_user_id'] = $params['user_id'];
                $data['create_time'] = $t;
                $data['status'] = 1;
                if(!empty($params['tag_name'])){
                    Db::name('tag')->insert($data);
                }
            }

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


    public function addTag($params){
        $t = time();
        if(!empty($params['tag_name'])){
            $data['tag_name'] = $params['tag_name'];
        }
        if(!empty($params['type_name'])){
            $data['type_name'] = $params['type_name'];
        }
        if(!empty($params['language_id'])){
            $data['language_id'] = $params['language_id'];
        }
        if(!empty($params['code_name'])){
            $data['code_name'] = $params['code_name'];
        }
        $data['update_user_id'] = $params['user_id'];
        $data['update_time'] = $t;
        $data['create_user_id'] = $params['user_id'];
        $data['create_time'] = $t;
        $data['status'] = 1;
        Db::startTrans();
        try{
            Db::name('tag')->insert($data);
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