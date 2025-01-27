<?php


define('IN_API', true);

define('CURSCRIPT', 'api');

define('DISABLEXSSCHECK', true);

require '../../../source/class/class_core.php';

$discuz = C::app();

$discuz->init();


include_once DISCUZ_ROOT . './source/plugin/ymq_vip/function.php';

if (file_exists($ymq_pay_list = DISCUZ_ROOT . './data/sysdata/cache_ymq_pay_list.php')) {

    @include $ymq_pay_list;

}


//��֤md5
$app_id =  addslashes($_POST['app_id']);
$order_sn = addslashes($_POST['order_sn']);
$out_trade_no = addslashes($_POST['out_order_sn']);
$notify_count = addslashes($_POST['notify_count']);
$pay_way = addslashes($_POST['pay_way']);
$price = addslashes($_POST['price']);
$qr_type = addslashes($_POST['qr_type']);
$qr_price = addslashes($_POST['qr_price']);
$pay_price = addslashes($_POST['pay_price']);
$created_at = addslashes($_POST['created_at']);
$paid_at = addslashes($_POST['paid_at']);
$server_time = addslashes($_POST['server_time']);
$sign = addslashes($_POST['sign']);

$temp_sign = md5($app_id . $order_sn . $out_trade_no . $notify_count . $pay_way . $price . $qr_type . $qr_price . $pay_price . $created_at . $paid_at . $server_time . $pay_list['ymq_key']);
if ($temp_sign !== $sign) {
    exit('ǩ������');
}

$result = C::t('#ymq_vip#ymq_payrecord_log')->get_payrecord_log_first(array('out_trade_no' => $out_trade_no));

if ($result['deal_pyte'] == 'buy_credits') {

    update_credits_pay($result, $order_sn);

} else {

    update_vip_pay($result, $order_sn);

}

echo "success";

?>