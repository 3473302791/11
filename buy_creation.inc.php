<?php

if(!defined('IN_DISCUZ')) {

	exit('Access Denied');

}

if (file_exists(DISCUZ_ROOT . './source/plugin/ymq_vip/function.php')) {

	@include_once DISCUZ_ROOT . './source/plugin/ymq_vip/function.php';

}

	$config = vip_config();

	$vip_id = intval($_GET['vip_id']);

	$pay_type = daddslashes($_GET['pay_type']);

	$ismoey = floatval($_GET['moey']);

	$arr = array();

	if (!$_G['uid']){

		$arr['log'] = lang('plugin/ymq_vip','log');

		arrjson_iconv($arr);

	}

	if ($_G['uid'] == 1 && empty($ismoey)){

		$arr['admin'] = lang('plugin/ymq_vip','admin');

		arrjson_iconv($arr);

	}

 	if($_GET['formhash'] != FORMHASH || !$vip_id || !$pay_type){

		$arr['error'] = lang('plugin/ymq_vip', 'error');

		arrjson_iconv($arr);

	}



	if(empty($ismoey)){

		$vip_list = C::t('#ymq_vip#ymq_product_list')->get_viplist_first($vip_id);

		$CheckResult = BuyVipCheck($vip_list['vip_group']);

		if($CheckResult['code'] == -1 || $CheckResult['code'] == -2){

			$arr['error'] = $CheckResult['msg'];

			arrjson_iconv($arr);

		}

	}



	if ($pay_type == 'jf'){

	    $url = $_G['siteurl'].$config['jump_vipurl'];

		$buy_creation = get_Creation_pay(true,$pay_type,$vip_id,$url);

		if ($buy_creation){



			$order = C::t('#ymq_vip#ymq_payrecord_log')->get_payrecord_log_first(array('uid'=> $_G['uid'],'pay_status'=> -1,'vip_id'=> $vip_id,'deal_pyte_no'=> 'buy_credits'));

			$viplist = C::t('#ymq_vip#ymq_product_list')->get_viplist_first($order['vip_id']);

			$extid = get_uscredits($order['uid']);

	

			if ($extid['extcredits'.$viplist['vip_credits_type']] >= $viplist['vip_credits_price']){



//				$vip_credits_price = $viplist['vip_discount'] && $config['credits_discount'] ? round($viplist['vip_credits_price'] * $viplist['vip_discount_value']) : $viplist['vip_credits_price'];
                $vip_credits_price = $viplist['vip_discount'] && $config['credits_discount'] ? round($viplist['vip_credits_price'] * $viplist['vip_discount_value'],2) : $viplist['vip_credits_price'];

				updatemembercount($order['uid'], array('extcredits'.$viplist['vip_credits_type'] => -$vip_credits_price),true,'UGP',1,1,1,1);

				if(in_array('ymq_notice', $_G['setting']['plugins']['available'])){

					$newnote = lang('plugin/ymq_vip', 'vipdeduct').$vip_credits_price.$_G['setting']['extcredits'][$viplist['vip_credits_type']]['title'];

				    vip_MsgWxSend($order['uid'], 'vip_credit_change', $newnote,'',false);

				}

				update_vip_pay($order);

				$arr['vip_name'] = $viplist['vip_name'];

				$arr['groupid_overdue'] = dgmdate($order['groupid_overdue'],'Y-m-d');

				$arr['url'] = $order['jump_url'];



			}else{

				$arr['jfbz'] = lang('plugin/ymq_vip', 'error_jfbz');

			}

		}

		arrjson_iconv($arr);

	}else{



		if($pay_type == 'zfb'){

			$pay_type = 'zfb';

		}else if($pay_type == 'wx'){

			$pay_type = 'wx';

		}else if($pay_type == 'Appbyme_zfb'){

			$pay_type = 'Appbyme_zfb';

		}else if($pay_type == 'Appbyme_wx'){

			$pay_type = 'Appbyme_wx';

		}else if($pay_type == 'magappx'){

			$pay_type = 'magappx';

		}else if($pay_type == 'QianFan'){

			$pay_type = 'QianFan';

		}



		if(!empty($ismoey)){

			$isbuy_vip = false;

			$moey = $ismoey;

			$where = array('uid'=> $_G['uid'],'pay_status'=> -1,'vip_id'=> $vip_id,'deal_pyte'=> 'buy_credits');

			$url = $_G['siteurl'].$config['jump_url'];

		}else {

			$isbuy_vip = true;

			$moey = null;

			$where = array('uid'=> $_G['uid'],'pay_status'=> -1,'vip_id'=> $vip_id,'deal_pyte_no'=> 'buy_credits');

			$url = $_G['siteurl'].$config['jump_vipurl'];

		}

		$buy_creation = get_Creation_pay($isbuy_vip,$pay_type,$vip_id,$url,$moey);

		$order = C::t('#ymq_vip#ymq_payrecord_log')->get_payrecord_log_first($where);

		if ($buy_creation && $order['out_trade_no']){

			$arr['url'] = $_G['siteurl'].'plugin.php?id=ymq_vip:pay_dispose&out_trade_no='.$order['out_trade_no'];

			$arr['wxsm'] = $order['pay_pyte'];

			$arr['out_trade_no'] = $order['out_trade_no'];

			$arr['nurl'] = $order['jump_url'];

			arrjson_iconv($arr);

		}else{

			$arr['error'] = '"out_trade_no" or "buy_creation" no value';

			arrjson_iconv($arr);

		}

	}

?>