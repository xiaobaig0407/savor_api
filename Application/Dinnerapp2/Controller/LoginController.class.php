<?php
/**
 * @desc 餐厅端2.0-用户登录
 * @author zhang.yingtao
 * @since  20171219
 */
namespace Dinnerapp2\Controller;
use Think\Controller;
use \Common\Controller\BaseController as BaseController;
class LoginController extends BaseController{
    /**
     * 构造函数
     */
    function _init_() {
        switch(ACTION_NAME) {
            case 'doLogin':
                $this->is_verify = 1;
                $this->valid_fields = array('mobile'=>1001,'invite_code'=>1001,'verify_code'=>1000);
                break;
            case 'getHotelInfo';
                $this->is_verify = 1;
                $this->valid_fields = array('mobile'=>1001,'invite_code'=>1001,'verify_code'=>1001);
                break;
        }
        parent::_init_();
    }
    
    public function dologin(){
        $mobile = trim($this->params['mobile']);           //手机号
        $verify_code = trim($this->params['verify_code']); //手机验证码
        $invite_code = trim($this->params['invite_code']); //邀请码
        //验证手机格式
        if(!check_mobile($mobile)){
            $this->to_back(60002);
        }
        if($verify_code){
            $redis  =  \Common\Lib\SavorRedis::getInstance();
            $redis->select(14);
            $cache_key = 'dinner_vcode_'.$mobile;
            $cache_verify_code = $redis->get($cache_key);
            
            if($verify_code != $cache_verify_code){
                $this->to_back('60004');
            }
        }
        $m_hotel_invite_code = new \Common\Model\HotelInviteCodeModel();
        $where = array();
        
        $where['a.bind_mobile'] = $mobile;
        $where['a.flag'] = 0;
        $info = $m_hotel_invite_code->getInfo('a.id invite_id,a.is_import_customer,a.code,b.id hotel_id,b.name hotel_name,c.is_open_customer', $where);
        if(empty($info)){
            $where = array();
            $where['a.code'] = $invite_code;
            $where['a.flag'] = 0;
            $invite_code_info = $m_hotel_invite_code->getInfo('a.id invite_id,a.state,b.id hotel_id,b.name hotel_name,c.is_open_customer',$where);
            if(empty($invite_code_info)){//输入的邀请码不正确
                $this->to_back(60005);
            }
            if($invite_code_info['state'] ==1){
                $this->to_back(60011);
            }
            $where = array();
            $where['code'] = $invite_code;
            $where['flag'] = 0;
            $data = array();
            $data['state'] = 1;
            $data['bind_time'] = date('Y-m-d H:i:s');
            $data ['bind_mobile'] = $mobile;
            $ret = $m_hotel_invite_code->saveInfo($where,$data);
            if($ret){
                if($verify_code){
                    $redis->remove($cache_key);
                }
                unset($invite_code_info['state']);
               
                $this->to_back($invite_code_info);
            }else {
                $this->to_back(60006);
            } 
        }else {
            if($invite_code!=$info['code']){
                $this->to_back(60012);
            }
            unset($info['code']);
            $m_dinner_customer = new \Common\Model\DinnerCustomerModel();
            $oss_path = C('TASK_REPAIR_IMG');
            $fields = "id customer_id,name,mobile,mobile1,`face_url`";
            $where = array();
            $where['invite_id'] = $info['invite_id']; 
            $where['flag']      = 0;
            $customer_list = $m_dinner_customer->getList($fields,$where);
            foreach($customer_list as $key=>$v){
                if(!empty($v['face_url'])){
                    $customer_list[$key]['face_url'] = $oss_path.$v['face_url'];
                }
                
            }
            $info['customer_list'] = $customer_list;
            $this->to_back($info);
        }
    }
    /**
     * @desc 首次登录返回酒楼信息
     */
    public function getHotelInfo(){
        $mobile = $this->params['mobile'];           //手机号
        $verify_code = $this->params['verify_code']; //手机验证码
        $invite_code = $this->params['invite_code']; //邀请码
        //验证手机格式
        if(!check_mobile($mobile)){
            $this->to_back(60002);
        }
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(14);
        $cache_key = 'dinner_vcode_'.$mobile;
        $cache_verify_code = $redis->get($cache_key);
        
        if($verify_code != $cache_verify_code){
            $this->to_back('60004');
        }
        $m_hotel_invite_code = new \Common\Model\HotelInviteCodeModel();
        $where = array();
        
        $where['a.code'] = $invite_code;
        
        //$where['a.state'] = 0;
        
        
        $where['a.flag'] = 0;
        $info = $m_hotel_invite_code->getInfo('a.state,b.id hotel_id,b.name hotel_name', $where);
        if(empty($info)){
            $this->to_back(60005);
        }
        unset($info['state']);
        $this->to_back($info);
    }
}