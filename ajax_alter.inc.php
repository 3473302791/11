<?php

if(!defined('IN_DISCUZ')) {

	exit('Access Denied');

}

if (file_exists(DISCUZ_ROOT.'./source/plugin/ymq_vip/function.php')) {

	@include_once DISCUZ_ROOT.'./source/plugin/ymq_vip/function.php';

}

	$groupid = intval($_GET['group_id']);

	$out_trade_no = daddslashes($_GET['out_trade_no']);

    $arr = array();

	if (!$_G['uid']){

		$arr['log'] = lang('plugin/ymq_vip','log');

		arrjson_iconv($arr);

	}

	if($_GET['formhash'] != FORMHASH){

		$arr['error'] = lang('plugin/ymq_vip','error');

		arrjson_iconv($arr);

	}



 	if(!empty($out_trade_no)){

		$order = C::t('#ymq_vip#ymq_payrecord_log')->get_payrecord_log_first(array('out_trade_no'=> $out_trade_no));

		if($order['pay_status'] == 1){

			echo 'success';

		}else{

			echo 'await';

		}

        exit;

	}else if(!empty($groupid)){



	    $result = GroupSwitch($groupid,$_G['uid']);

		if($result['code'] == -1) {

			$arr['found'] = lang('plugin/ymq_vip','operation1');

			arrjson_iconv($arr);

		}

		if($result['code'] == -2) {

			$arr['period'] = lang('plugin/ymq_vip','operation2');

			arrjson_iconv($arr);

		}

		if($result['code'] == 1) {

			$arr['group_ok'] = lang('plugin/ymq_vip', 'operation3');

			$arr['group_name'] = $result['grouptitle'];

			arrjson_iconv($arr);

		}

	}

?>