<?php
/**
 * 错误码以及错误信息配置定义
 */
return array(
	'errorinfo'=>array(
		'1001'=>'parmas_not_null',
		'1002'=>'parmas_null',
		'1003'=>'traceinfo_not_null',
		'1004'=>'mobile_deviceid_not_match',
		'1005'=>'token_not_null',
		'1006'=>'token_has_expired',
		'1007'=>'sign_error',
		'10000'=>'success',
	    //心跳上报
	    '10004'=>'heart_mac_period_not_null',
	    '10005'=>'heart_clientid_range_err',
	    '10006'=>'heart_mac_invalid',
	    '10007'=>'heart_hotelid_invalid',
	    
		//用户操作报错
		'12001'=>'mobile_illegal',
		'12002'=>'user_not_exist',
		'12003' =>'user_login_err',

		//记录用手首次使用app
	    '20001'=>'first_use_have_data',
	    '20002'=>'first_use_push_err',
		//客户端传类型报错
		'13001'=>'ctype_illegal',
		'13002'=>'cltype_insert_fail',
	    //下载统计类型能够报错
	    '14001'=>'download_source_error',
	    '14002'=>'client_error',
	    '14003'=>'download_data_insert_error',
	    '14004'=>'this_facility_have_download',
	    //抽奖
	    '15001'=>'box_not_set_award',
	    '15002'=>'box_award_record_error',
	    '15003'=>'have_not_this_box',
	    '15004'=>'this_award_have_empty',
	    '15005'=>'this_award_not_have_current',

		//云平台PHP接口
		'16001'=>'down_hotel_infotype_error',
	    
	    '16100'=>'small_platform_hotel_error',
	    '16101'=>'small_platform_report_error',
	    '16102'=>'small_platform_report_type_error',
	    '16103'=>'hotel_not_set_small_plat_upgrade_version',
	    '16104'=>'have_not_this_small_plat_upgrade_war',

	),
);