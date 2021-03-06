<?php
namespace BaseData\Controller;
use Think\Controller;
use \Common\Controller\BaseController as BaseController;
class CategoryController extends BaseController{
 	/**
     * 构造函数
     */
    function _init_() {
        switch(ACTION_NAME) {
            case 'getCategoryList':
                $this->is_verify = 0;
                break;
        }
        parent::_init_();
    }
    /**
     * @desc 获取分类列表
     */
    public function getCategoryList(){

        $m_category = new \Common\Model\Basedata\CategoryModel();
        $data = $m_category->getAllCategory();
        foreach($data as $key=>$v){
           foreach($v as $kk=>$vv){
               if(empty($vv)){
                   unset($data[$key][$kk]);
               }
           }
        }
        $this->to_back($data);
    }
}