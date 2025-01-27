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



$alipay_config = array();

$alipay_config["partner"] = trim($pay_list['zfb_id']);

$alipay_config["seller_email"] = trim($pay_list['zfb_zh']);

$alipay_config["key"] = trim($pay_list['zfb_key']);

$alipay_config["sign_type"] = strtoupper('MD5');

$alipay_config["input_charset"] = strtolower('utf-8');

$alipay_config["cacert"] = DISCUZ_ROOT.'source'.DIRECTORY_SEPARATOR.'plugin'.DIRECTORY_SEPARATOR.'ymq_vip'.DIRECTORY_SEPARATOR.'cacert.pem';

$alipay_config["transport"] = https() ? 'https' : 'http';

require_once("include/alipay_notify.class.php");

$alipayNotify = new AlipayNotify($alipay_config);

$verify_result = $alipayNotify->verifyReturn();



if($verify_result) {

	$out_trade_no = $_GET['out_trade_no'];

	$order = C::t('#ymq_vip#ymq_payrecord_log')->get_payrecord_log_first(array('out_trade_no'=> $out_trade_no));

	$url = $order['jump_url'];

    header("Location:$url");

	exit();

}else{

    echo "verify_result";

}

?>