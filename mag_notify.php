<?php



define('IN_API', true);

define('CURSCRIPT', 'api');

define('DISABLEXSSCHECK', true);

require '../../../source/class/class_core.php';

$discuz = C::app();

$discuz->init();



include_once DISCUZ_ROOT.'./source/plugin/ymq_vip/function.php';

if(file_exists($ymq_pay_list = DISCUZ_ROOT.'./data/sysdata/cache_ymq_pay_list.php')){

	@include $ymq_pay_list;

}



$magappxsecret = trim($pay_list['magappxsecret']);

$out_trade_no = $_GET['out_trade_no'];

$sign = $_GET['sign'];



$result = C::t('#ymq_vip#ymq_payrecord_log')->get_payrecord_log_first(array('out_trade_no'=> $out_trade_no));

$MD5sign = md5($magappxsecret.$result['out_trade_no']);



if($sign == $MD5sign && $result['pay_status'] == -1){



	if ($result['deal_pyte'] == 'buy_credits'){

		update_credits_pay($result);

	}else{

		update_vip_pay($result);

	}

}

?>