<?php
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
if (file_exists(DISCUZ_ROOT . './source/plugin/ymq_vip/function.php')) {
    @include_once DISCUZ_ROOT . './source/plugin/ymq_vip/function.php';
}
if (file_exists($ymq_pay_list = DISCUZ_ROOT . './data/sysdata/cache_ymq_pay_list.php')) {
    @include $ymq_pay_list;
}
if (!function_exists('curl_version')) {
    exit('Please open curl extension');
}

function posturl($charset, $url, $data)
{
    if (stripos($charset, 'gbk') != false) {
        $headerArray = array("content-type: application/x-www-form-urlencoded;charset=GBK", "Accept:application/json");
    } else {
        $headerArray = array("content-type: application/x-www-form-urlencoded;charset=UTF-8", "Accept:application/json");
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArray);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
//    return json_decode($output, true);
}

if (!$_G['uid']) {
    dheader('location: member.php?mod=logging&action=login');
    exit;
}
$out_trade_no = daddslashes($_GET['out_trade_no']);

$order = C::t('#ymq_vip#ymq_payrecord_log')->get_payrecord_log_first(array('out_trade_no' => $out_trade_no));
$viplist = C::t('#ymq_vip#ymq_product_list')->get_viplist_first($order['vip_id']);
$get_name = getuserbyuid($_G['uid']);

if ($order['pay_status'] == 1) {
    showmessage(lang('plugin/ymq_vip', 'pay_oks'));
}
if ($order['deal_pyte'] == 'buy_credits') {
    $subjects = $get_name['username'] . lang('plugin/ymq_vip', 'vip_paylang2') . $order['credits_present'] . $_G['setting']['extcredits'][$order['credits_type']]['title'];
} else {
    $subjects = $get_name['username'] . lang('plugin/ymq_vip', 'vip_paylang') . $viplist['vip_name'];
}
unset($viplist);

$subject = cutstr(trim($subjects), 20);
if ($_G['charset'] == 'gbk') {
    $subject = iconv('gbk', 'utf-8', $subject);
}


if (($order['pay_pyte'] == 'wx_gzh' || $order['pay_pyte'] == 'wx_pc' || $order['pay_pyte'] == 'wx_h5'|| $order['pay_pyte'] == 'zfb_pc' || $order['pay_pyte'] == 'zfb_web') && $_GET['formhash'] == FORMHASH) {
    $app_id = trim($pay_list['ymq_appid']);
    $key = trim($pay_list['ymq_key']);
    $out_order_sn = $order['out_trade_no'];
    $name = $subject;
    switch ($order['pay_pyte']) {
        case 'wx_gzh':
        case 'wx_pc':
        case 'wx_h5':
            $pay_way = 'wechat';
            break;
        case 'zfb_pc':
        case 'zfb_web':
            $pay_way = 'alipay';
            break;
    }
    $price = $order['money'] * 100;
    $notify_url = 'http://zf.xb2022.cn/source/plugin/ymq_vip/notify.php';
//    $notify_url = 'http://www.baidu.com';
    $sign = md5($app_id . $out_order_sn . $name . $pay_way . $price . $notify_url . $key);
    $data = posturl($_G['charset'], "https://service-5izleezr-1255875254.sh.apigw.tencentcs.com/api/pay", array(
        "app_id" => $app_id,
        "out_order_sn" => $out_order_sn,
        "name" => $name,
        "pay_way" => $pay_way,
        "price" => $price,
        "notify_url" => $notify_url,
        "sign" => $sign
    ));
    echo $data;
    exit;
}

//if($order['pay_pyte'] == 'magappx'){
//	$magappxsecret = trim($pay_list['magappxsecret']);
//	$magappdomain = trim($pay_list['magappdomain']);
//	include_once DISCUZ_ROOT.'./source/plugin/ymq_vip/include/wx_pay.class.php';
//	unset($subject);
//    $subject = cutstr(trim($subjects),20);
//	$sign = md5($magappxsecret.$order['out_trade_no']);
//	$mag_notify = $_G['siteurl'].'source/plugin/ymq_vip/mag_notify.php?out_trade_no='.$order['out_trade_no'].'&sign='.$sign;
//    //$http = https() ? 'https://' : 'http://';
//    $res = get('http://'.$magappdomain.'/core/pay/pay/unifiedOrder?trade_no='.$order['out_trade_no'].'&amount='.$order['money'].'&title='.$subject.'&user_id='.$_G['uid'].'&des='.$subject.'&remark='.$subject.'&secret='.$magappxsecret.'&callback='.urlencode($mag_notify));
//	$data = json_decode($res,true);
//	$unionOrderNum = $data["data"]["unionOrderNum"];
//	$magapp ='
//	<html>
//	<head>
//		<meta http-equiv="content-type" content="text/html;charset=utf-8"/>
//		<meta name="viewport" content="width=device-width, initial-scale=1"/>
//		<title>'.$subjects.'</title>
//		<script src="source/plugin/ymq_vip/static/js/magjs-x.js"></script>
//		<script type="text/javascript">
//			var config = {
//				money:"'.$order['money'].'",
//				title:"'.$subject.'",
//				des:"'.$subject.'",
//				payWay:{
//					wallet:1,
//					weixin:1,
//					alipay:1,
//				},
//				orderNum:"'.$order['out_trade_no'].'",
//				unionOrderNum:"'.$unionOrderNum.'",
//				type: "vip-credit "
//			}
//			mag.pay(config, function(){
//			  location.href="'.$order['jump_url'].'";
//			}, function(){
//			  location.href="'.$order['jump_url'].'";
//			});
//		</script>
//	</head>
//	</html>';
//	echo $magapp;exit;
//}

//if($order['pay_pyte'] == 'QianFan' || $_GET['inajax'] == 'finishPay'){
//	$config = vip_config();
//    $qf_pay_type = trim($config['ymq_qfpay_type']);
//	if(!$qf_pay_type){
//		exit('error_qf_pay_type');
//	}
//	if($_GET['inajax'] == 'finishPay' && $_GET['formhash'] == FORMHASH){
//        $out_trade_no = daddslashes($_GET['out_trade_no']);
//        $order_id = daddslashes($_GET['order_id']);
//	    $arrmsg = array();
//		if(!$out_trade_no){
//			$arrmsg['code'] = -1;
//			$arrmsg['msg'] = 'pay_error_out_trade_no';
//		}else{
//			$orders = C::t('#ymq_vip#ymq_payrecord_log')->get_payrecord_log_first(array('out_trade_no'=> $out_trade_no));
//			if($orders['deal_pyte'] == 'buy_credits'){
//				update_credits_pay($orders, $order_id);
//			}else{
//				update_vip_pay($orders,$order_id);
//			}
//			$arrmsg['code'] = 1;
//			$arrmsg['jmurl'] = $orders['jump_url'];
//		}
//		echo json_encode($arrmsg);
//		exit;
//	}
//
//    $orderjson = json_encode($order);
//	include template('ymq_vip:qf_pay');
//	exit;
//}
?>