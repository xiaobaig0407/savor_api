<?php
/**
 * 微信支付接口类
 */
namespace Payment\Model;
use Think\Model;
class WxpayModel extends Model{
    private $payconfig;//支付账号信息
    private $trade_info;//订单信息
    private $os_type;//1pc 2mobile 3公众号支付
    private $paylog_type;
    private $refund_type = 100;
    private $mmpay_type = 200;
    private $pay_type = 10;//10微信支付
    private $host_name='';
    
    public function __construct($os_type=0){
        $this->baseInc = new \Payment\Model\BaseIncModel();
        if($os_type){
    		$this->os_type = $os_type;
    	}else{
    		$this->os_type = $this->baseInc->getos();
    	}
    	$this->host_name = $this->baseInc->host_name();
    }
    
    public function pay($trade_info=array(),$payconfig=array()){
    	if(empty($trade_info) || empty($payconfig)){
    		die(json_encode(array('error'=>'订单号或支付账号信息为空，请检查相关代码')));
    	}
    	$this->trade_info = $this->baseInc->init_pay_tradeinfo($trade_info);
    	$payinfo = $this->baseInc->init_pay_config($payconfig);
    	$this->payconfig = array('APPID'=>$payinfo['appid'],'MCHID'=>$payinfo['partner'],'KEY'=>$payinfo['key']);
    	$GLOBALS['wxpay_config'] = $this->payconfig;
    	switch ($this->os_type){
    		case 1:
    			$paydata = $this->get_payurl_pc();
    			break;
    		case 2:
    			$paydata = $this->get_payurl_mobile();
    			break;
    		case 3:
    			$paydata = $this->get_pay_jsapi();
    			break;
    		default:
    			$paydata = $this->get_payurl_pc();
    			break;
    	}
    	return $paydata;
    }
    
    public function pay_notify(){
        $fwh_config = C('WX_FWH_CONFIG');
        $appid = $fwh_config['appid'];
        $pay_config = C('PAY_WEIXIN_CONFIG');
    	$this->payconfig = array('APPID'=>$appid,'MCHID'=>$pay_config['partner'],'KEY'=>$pay_config['key']);
    	$GLOBALS['wxpay_config'] = $this->payconfig;
    	if($this->os_type == 1){
    		$this->paylog_type = 1;
    	}elseif($this->os_type == 2){
    		$this->paylog_type = 2;
    	}else{
            $this->paylog_type = 3;
        }
        $this->notify();
    }
    
  
    
    private function get_payurl_pc(){

        require_once "wxpay_lib/WxPay.Api.php";
		$notify_url = $this->host_name.'/payment/wxNotify/pc';
		
		$input = new \WxPayUnifiedOrder();
		$input->SetBody($this->trade_info['subject']);
		$input->SetOut_trade_no($this->trade_info['out_trade_no']);
		$input->SetTotal_fee($this->trade_info['total_fee']*100);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 7200));
		$input->SetGoods_tag($this->trade_info['subject']);
		$input->SetNotify_url($notify_url);
		$input->SetTrade_type("NATIVE");
		$input->SetProduct_id($this->trade_info['out_trade_no']);
		$input->SetAttach('');
		$result = \WxPayApi::unifiedOrder($input);
		$url = $result['code_url'];
		return $url;
    }
    
    private function get_payurl_mobile(){
        require_once "wxpay_lib/WxPay.Api.php";
		$notify_url = $this->host_name.'/payment/wxNotify/pc';
		
		$input = new \WxPayUnifiedOrder();
		$input->SetBody($this->trade_info['subject']);
		$input->SetOut_trade_no($this->trade_info['out_trade_no']);
		$input->SetTotal_fee($this->trade_info['total_fee']*100);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 7200));
		$input->SetGoods_tag($this->trade_info['subject']);
		$input->SetNotify_url($notify_url);
		$input->SetTrade_type("MWEB");
		$input->SetProduct_id($this->trade_info['out_trade_no']);
		$input->SetAttach('');
		$result = \WxPayApi::unifiedOrder($input);
		$redirect_url = urlencode($this->trade_info['redirect_url']);
		$url = $result['mweb_url'].'&redirect_url='.$redirect_url;
		header("Location: $url");
    }
    
    private function get_pay_jsapi(){
        require_once "wxpay_lib/WxPay.Api.php";
		$notify_url = $this->host_name.'/payment/wxNotify/pc';
		
		$input = new \WxPayUnifiedOrder();
		$input->SetBody($this->trade_info['subject']);
		$input->SetOut_trade_no($this->trade_info['out_trade_no']);
		$input->SetTotal_fee($this->trade_info['total_fee']*100);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 7200));
		$input->SetGoods_tag($this->trade_info['subject']);
		$input->SetNotify_url($notify_url);
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($this->trade_info['wx_openid']);
		$order = \WxPayApi::unifiedOrder($input);
		$jsapi = new \WxPayJsApiPay();
		$jsapi->SetAppid($order['appid']);
		$timeStamp = time();
		$jsapi->SetTimeStamp("$timeStamp");
		$jsapi->SetNonceStr(\WxPayApi::getNonceStr());
		$jsapi->SetPackage('prepay_id='.$order['prepay_id']);
		$jsapi->SetSignType('MD5');
		$jsapi->SetPaySign($jsapi->MakeSign());
		$parameters = json_encode($jsapi->GetValues());
		return $parameters;
    }
    
    
    private function notify(){
        $paylog_type = $this->paylog_type;
        require_once "wxpay_lib/WxPay.Api.php";
        //获取通知的数据
        libxml_disable_entity_loader(true);
//        $post_data = $GLOBALS['HTTP_RAW_POST_DATA'];
        $post_data = file_get_contents('php://input');
        $this->baseInc->paynotify_log($paylog_type,'','nofity_data:'.$post_data);

        $result = \WxPayResults::Init($post_data);
        $code = $msg = 'FAIL';
        if($result == false){
            $this->baseInc->paynotify_log($paylog_type,'','验签失败');
        }else{
            $trade_no = $result['transaction_id'];
            $out_trade_no = $result['out_trade_no'];
            $total_fee = $result['total_fee']/100;
            $this->baseInc->paynotify_log($paylog_type,$trade_no,'验签成功');
            
            $input = new \WxPayOrderQuery();
            $input->SetTransaction_id($trade_no);
            $result_order = \WxPayApi::orderQuery($input);
            $this->baseInc->paynotify_log($paylog_type,$trade_no,'微信查询订单接口返回数据'.json_encode($result_order));
            if(array_key_exists("return_code", $result_order) && array_key_exists("result_code", $result_order) && 
                $result_order["return_code"] == "SUCCESS" && $result_order["result_code"] == "SUCCESS"){
                
                $order_extend = array(
                    'trade_no'=>$out_trade_no,
                    'serial_no'=>$trade_no,
                    'pay_fee'=>$total_fee,
                    'paylog_type'=>$paylog_type,
                    'pay_type'=>$this->pay_type
                );
                $is_continue = $this->baseInc->handle_redpacket_notify($order_extend,false);
                if($is_continue){
                    $code = 'SUCCESS';
                    $msg = 'OK';
                    
                    $log = '订单号:'.$out_trade_no.'更新成功，支付完成';
                    $this->baseInc->paynotify_log($paylog_type,$trade_no,$log);
                }else{
                    $log = '订单号:'.$out_trade_no.'更新失败，支付失败';
                    $this->baseInc->paynotify_log($paylog_type,$trade_no,$log);
                }
            }else{
                $this->baseInc->paynotify_log($paylog_type,$trade_no,'获取微信订单支付失败');
            }
        }
        
        //返回数据至微信
        $obj_WxPayNotifyReply = new \WxPayNotifyReply();
        $obj_WxPayNotifyReply->SetReturn_code($code);
        $obj_WxPayNotifyReply->SetReturn_msg($msg);
        $xml = $obj_WxPayNotifyReply->ToXml();
        echo $xml;
    }

    public function wxrefund($trade_info=array(),$payconfig=array()){
    	require_once "wxpay_lib/WxPay.Api.php";
    	$paylog_type = $this->refund_type;
    	if(empty($trade_info) || empty($payconfig)){
    		die(json_encode(array('error'=>'订单号或支付账号信息为空，请检查相关代码')));
    	}
    	$payinfo = $this->baseInc->init_pay_config($payconfig);
    	$this->payconfig = array('APPID'=>$payinfo['appid'],'MCHID'=>$payinfo['partner'],'KEY'=>$payinfo['key']);
    	$GLOBALS['wxpay_config'] = $this->payconfig;
    	//通过微信api进行退款流程
    	$input = new \WxPayRefund();
    	$input->SetOut_trade_no($trade_info['trade_no']);
    	$input->SetOut_refund_no($trade_info['batch_no']);
    	$input->SetTotal_fee($trade_info['pay_fee']*100);
    	$input->SetRefund_fee($trade_info['refund_money']*100);
    	$input->SetOp_user_id($this->payconfig['MCHID']);
    	$order = \WxPayApi::refund($input);

        $log = '订单号:'.$order['out_trade_no'].'微信api返回数据:'.json_encode($order);
        $this->baseInc->paynotify_log($paylog_type,$trade_info['trade_no'],$log);

    	if($order["return_code"]=="SUCCESS"){
    		$log = '订单号:'.$order['out_trade_no'].'退款成功';
    		$this->baseInc->paynotify_log($paylog_type,$order['out_trade_no'],$log);
    	}else if($order["return_code"]=="FAIL"){
    		$log = '订单号:'.$order['out_trade_no'].'退款失败';
    		$this->baseInc->paynotify_log($paylog_type,$order['out_trade_no'],$log);
    	}else{
    		$log = '订单号:'.$order['out_trade_no'].'退款失败';
    		$this->baseInc->paynotify_log($paylog_type,$order['out_trade_no'],$log);
    	}
        return $order;
    }

    public function mmpaymkttransfers($trade_info=array(),$payconfig=array()){
        require_once "wxpay_lib/WxPay.Api.php";
        $paylog_type = $this->mmpay_type;
        if(empty($trade_info) || empty($payconfig)){
            die(json_encode(array('error'=>'订单号或支付账号信息为空，请检查相关代码')));
        }

        $money = $trade_info['money']*100;//单位为分
        $key = $payconfig['key'];
        $params = array();
        $params["mch_appid"]=$payconfig['appid'];
        $params["mchid"] = $payconfig['partner'];
        $params["nonce_str"]= \WxPayApi::getNonceStr();
        $params["partner_trade_no"] = uniqid();
        $params["openid"]= $trade_info['open_id'];
        $params["check_name"]= 'NO_CHECK';
        $params["amount"]= $money;
        $params["desc"]= '红包零钱';
        $params['spbill_create_ip'] = $_SERVER['SERVER_ADDR'];

        //生成签名
        $str = 'amount='.$params["amount"].'&check_name='.$params["check_name"].'&desc='.$params["desc"].'&mch_appid='.$params["mch_appid"].'&mchid='.$params["mchid"].'&nonce_str='.$params["nonce_str"].'&openid='.$params["openid"].'&partner_trade_no='.$params["partner_trade_no"].'&spbill_create_ip='.$params['spbill_create_ip'].'&key='.$key;
        //md5加密 转换成大写
        $sign = strtoupper(md5($str));
        //生成签名
        $params['sign'] = $sign;
        //构造XML数据
        $xmldata = $this->array_to_xml($params); //数组转XML
        $url='https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        //发送post请求
        $res = $this->curl_post_ssl($url, $xmldata); //curl请求
        if(!$res){
            return array('code'=>10001,'msg'=>"服务器连接失败");
        }
        //付款结果分析
        $content = $this->xml_to_array($res); //xml转数组
        $log = '订单号:'.$trade_info['trade_no'].'pay_result'.json_encode($content);
        $this->baseInc->paynotify_log($paylog_type,$trade_info['trade_no'],$log);

        if($content["return_code"]=="SUCCESS"){
            $log = '订单号:'.$trade_info['trade_no'].'success支付零钱'.$trade_info['money'].' openid:'.$trade_info['open_id'];
            $this->baseInc->paynotify_log($paylog_type,$trade_info['trade_no'],$log);
            $info = array('code'=>10000,'msg'=>"支付零钱成功");
        }else if($content["return_code"]=="FAIL"){
            $log = '订单号:'.$trade_info['trade_no'].'fail支付零钱'.$trade_info['money'].' openid:'.$trade_info['open_id'];
            $this->baseInc->paynotify_log($paylog_type,$trade_info['trade_no'],$log);
            $info = array('code'=>10002,'msg'=>"支付零钱成功");
        }else{
            $log = '订单号:'.$trade_info['trade_no'].'fail支付零钱'.$trade_info['money'].' openid:'.$trade_info['open_id'];
            $this->baseInc->paynotify_log($paylog_type,$trade_info['trade_no'],$log);
            $info = array('code'=>10002,'msg'=>"支付零钱成功");
        }
        return $info;
    }



    public function curl_post_ssl($url, $xmldata,  $second=30,$aHeader=array()){
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);

        //默认格式为PEM，可以注释
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLCERT,APP_PATH.'Payment/Model/wxpay_lib/cert/apiclient_cert.pem');
        curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLKEY,APP_PATH.'Payment/Model/wxpay_lib/cert/apiclient_key.pem');
        if( count($aHeader) >= 1 ){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        }
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$xmldata);
        $data = curl_exec($ch);
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            return 'curl error:'.$error;
        }
    }

    public function xml_to_array($xml){
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }

    public function array_to_xml($arr){
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" .$key.">".$val."</".$key.">";
            } else
                $xml .= "<".$key."><![CDATA[".$val."]]></".$key.">";
        }
        $xml .= "</xml>";
        return $xml;
    }



    
    
}