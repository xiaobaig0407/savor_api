<?php
/**
 * @desc   小程序埋点
 * @author zhang.yingtao
 * @since  2018-09-05
 */
namespace Smallapp21\Controller;
use Think\Controller;
use \Common\Controller\CommonController as CommonController;
use Common\Lib\SavorRedis;
class BuriedPointController extends CommonController{
    /**
     * 构造函数
     */
    function _init_(){
        switch (ACTION_NAME){
            case 'netLogs':
                $this->is_verify = 1;
                $this->valid_fields = array('forscreen_id','resource_id'=>1001,
                                            'box_mac'=>1001,'openid'=>1001,
                                            'used_time'=>1001,'is_exist'=>1001);
            break;  
        }
        parent::_init_();
    }
    
    /**
     * @desc 图片投屏埋点
     */
    public function netLogs(){
        $forscreen_id = $this->params['forscreen_id'];   //一次投屏唯一标识
        $resource_id  = $this->params['resource_id'];    //资源id
        $openid       = $this->params['openid'];         //openid
        $box_mac      = $this->params['box_mac'];         //机顶盒mac
        $used_time    = $this->params['used_time'];       //用时
        $is_exist     = $this->params['is_exist'];         //是否存在
        if($is_exist==1){//资源已存在于机顶盒，不走下载逻辑
            $this->to_back(10000);
        }else {
            $data = array();
            $data['forscreen_id'] = $forscreen_id;
            $data['resource_id']  = $resource_id;
            $data['openid']       = $openid;
            $data['box_mac']      = $box_mac;
            $data['used_time'] =   $used_time;
            $now_time = getMillisecond();
            $data['box_res_sdown_time'] = $now_time - $used_time;
            $data['box_res_edown_time'] = $now_time;
            
            
            $redis = SavorRedis::getInstance();
            $redis->select(5);
            $cache_key = C('SAPP_UPDOWN_FORSCREEN').$openid;
            $redis->rpush($cache_key, json_encode($data));
           
            $this->to_back(10000);
        }
    }
    
}