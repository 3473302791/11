<?php



if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {

	exit('Access Denied');

}



$entire = C::t('#ymq_vip#ymq_credits_state')->fetch_all_field();

$table = DB::table('ymq_credits_state');



if (!$entire['credits_present_type'] && !$entire['credits_present_num']) {

  $sql = " ALTER TABLE $table ADD (`credits_present_type` int(10) DEFAULT NULL, `credits_present_num` varchar(255) DEFAULT NULL) ";

}else{	

  $sql = " ALTER TABLE $table MODIFY `credits_present_num` varchar(255) DEFAULT NULL ";

}



runquery($sql);

$finish = TRUE;

?>