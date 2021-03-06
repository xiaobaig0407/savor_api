<?php
namespace Common\Model;
use Think\Model;

class DeviceSqlModel extends Model{
	protected $tableName = 'device_sql';
	public function getUpgradeSql($curVersion,$downloadVersion,$type =1){
	    $sql ="select sql_lang,version_name,version_code from savor_device_sql
               where version_code >= $curVersion and version_code <= $downloadVersion and device_type = $type
               ORDER BY create_time ASC";
	    $result = $this->query($sql);
	    return $result;
	}
    public function getUpgradeSqlFf($curVersion,$downloadVersion,$type =1){
	    $sql ="select sql_lang,version_name,version_code from savor_device_sql
               where version_code > $curVersion and version_code <= $downloadVersion and device_type = $type
               ORDER BY create_time ASC";
	    $result = $this->query($sql);
	    return $result;
	}
}