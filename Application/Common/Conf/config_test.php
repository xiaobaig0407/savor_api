<?php
$DB_MASTER_HOST = '192.168.168.116';
$DB_SLAVE_HOST  = '192.168.168.116';
$db_name = 'cloud';
$db_user = 'phpweb';
$db_pwd = '123456';

//redis缓存配置
$redis['db1']['0']['host'] = '192.168.168.116';
$redis['db1']['0']['port'] = '6380';
$redis['db1']['0']['password'] = '!1QAZ@2WSX';
$redis['db1']['0']['isMaster'] = '1';
$redis['db1']['1']['host'] = '192.168.168.116'; 
$redis['db1']['1']['port'] = '6380';
$redis['db1']['1']['password'] = '!1QAZ@2WSX';
$redis['db1']['1']['isMaster'] = '0';

$config_db =  array(
	'DB_DEPLOY_TYPE' => 1, //数据库主从支持
    'DB_RW_SEPARATE' => true, //读写分离
    'DB_TYPE' => 'mysql',
    'DB_HOST' => "$DB_MASTER_HOST,$DB_SLAVE_HOST",
    'DB_NAME' => $db_name,
    'DB_USER' => $db_user,
    'DB_PWD' => $db_pwd,
    'DB_PORT' => 3306,
    'DB_CHARSET' => 'UTF8',
    'DB_PREFIX' => 'savor_',
    'DB_DEBUG'  =>  TRUE,
    'DB_PARAMS' => array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL),

    'DB_OSS'=>array(
        'DB_DEPLOY_TYPE' => 1, //数据库主从支持
        'DB_RW_SEPARATE' => true, //读写分离
        'DB_TYPE' => 'mysql',
        'DB_HOST' => "$DB_MASTER_HOST,$DB_SLAVE_HOST",
        'DB_NAME' => 'oss',
        'DB_USER' => $db_user,
        'DB_PWD' => $db_pwd,
        'DB_PORT' => 3306,
        'DB_CHARSET' => 'UTF8',
        'DB_PREFIX' => 'oss_',
        'DB_DEBUG'  =>  TRUE,
        'DB_PARAMS' => array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL)
    ),
    'DB_STATIS'=>array(
        'DB_DEPLOY_TYPE' => 1, //数据库主从支持
        'DB_RW_SEPARATE' => true, //读写分离
        'DB_TYPE' => 'mysql',
        'DB_HOST' => "$DB_MASTER_HOST,$DB_SLAVE_HOST",
        'DB_NAME' => 'statisticses',
        'DB_USER' => $db_user,
        'DB_PWD' => $db_pwd,
        'DB_PORT' => 3306,
        'DB_CHARSET' => 'UTF8',
        'DB_PREFIX' => 'statistics_',
        'DB_DEBUG'  =>  TRUE,
        'DB_PARAMS' => array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL)
    ),

    'REDIS_CONFIG' => $redis,

    
    //OSSS上传配置
	//'OSS_ACCESS_ID'   => 'tnDh4AQqRYbV9mq8',
	//'OSS_ACCESS_KEY'  => 'sv8aZCKEJhQ0nwKHj8uEnw3ADwcM24',
	//'OSS_HOST'    => 'oss-cn-beijing.aliyuncs.com',  //注意不要在前面加 http://
    'OSS_ACCESS_ID' =>'LTAITjXOpRHKflOX',
    'OSS_ACCESS_KEY'=>'Q1t8XSK8q82H3s8jaLq9NqWx7Jsgkt',
    'OSS_HOST'=> 'dev-oss.littlehotspot.com',               //注意不要在前面加 http://
    'OSS_BUCKET' => 'redian-development',                     //资源空间,即桶
	'OSS_SYNC_CALLBACK_URL'=>'alioss/syncNotify', //上传异步回调地址
	'UMENG_PRODUCTION_MODE'=>'false',
    'NETTY_BALANCE_URL'=>'https://dev-api-nzb.littlehotspot.com/netty/position',
    'NETTY_PUSH_BOX_URL'=>'https://dev-netty-push.littlehotspot.com/push/box',
    //end
    //热点投屏小程序配置
    'SMALLAPP_CONFIG'=>array('cache_key'=>'smallapp_token','appid'=>'wxe59b125a3f073901','appsecret'=>'423b1a65b84eacd4c13ab791b3a7edb1'),
     'SMALLAPP_SIMPLE_CONFIG'=>array('cache_key'=>'smallapp_simple_token','appid'=>'wxe59b125a3f073901','appsecret'=>'423b1a65b84eacd4c13ab791b3a7edb1'),
     'SMALLAPP_JIJIAN_CONFIG'=>array('cache_key'=>'smallapp_jijian_token','appid'=>'wx8ab347a4157b133f','appsecret'=>'31f8a73ce84a1b4c1cb0cf8bcbcf432a'),
     'SMALLAPP_DINNER_CONFIG'=>array('cache_key'=>'smallapp_dinner_token','appid'=>'wx329d3de0b91b00a2','appsecret'=>'21c633e1bec94f8fbdd1f73759e6f6ce'),
    
);
$config_api_host = array(
'CONTENT_HOST' => 'http://devp.admin.littlehotspot.com/',
    'IMG_UP_SUBCONTACT' => 'http://devp.oss.littlehotspot.com/log/resource/standalone/mobile',
    'TASK_REPAIR_IMG' => 'http://devp.oss.littlehotspot.com',

);

return array_merge($config_db,$config_api_host);





