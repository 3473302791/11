<?php

if(!defined('IN_DISCUZ')) {

	exit('Access Denied');

}
$navtitle = "积分充值";
$navtitle1 = "购买记录";
$comiis_bg = 1;
if (file_exists(DISCUZ_ROOT.'./source/plugin/ymq_vip/function.php')) {

	@include_once DISCUZ_ROOT.'./source/plugin/ymq_vip/function.php';

}

	$config = vip_config();

	$http = https() ? 'https://' : 'http://';

	if($config['isAppbyme'] && !$_G['uid']){

		exit('<script language="javascript" src="mobcent/app/web/js/appbyme/appbyme.js"></script><script>connectAppbymeJavascriptBridge(function(bridge){

			AppbymeJavascriptBridge.login(function(data){

				top.location.href="'.$http.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'";

			});

		});

		</script>');

	}

	if($config['isMAGAPPX'] && !$_G['uid']){

		exit('<script src="source/plugin/ymq_vip/static/js/magjs-x.js"></script><script>

			mag.toLogin(function(){

			   window.location.href="'.$http.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'";

			});

		</script>');

	}



	if($config['isQianFan'] && !$_G['uid']){

		exit('<script>QFH5.jumpLogin(function(state,data){});</script>');

	}

	

	$act = daddslashes($_GET['act']);

	if ($_G['uid']){

        $groupidlist = CeckUserGorup($_G['uid']);

	}



if (!$act){
	$comiis_head = array(
		'left' => '',
		'center' => $navtitle,
		'right' => '',	
		);
	$credits_typeid = intval($_GET['credits_typeid']);

	$credits_list = C::t('#ymq_vip#ymq_credits_state')->get_creditslist(8, array('credits_open' => 1));

    $credits_price = array();

	foreach($credits_list as $k => $v){

		$credits_price[$v['credits_typeid']]['title'] = $_G['setting']['extcredits'][$v['credits_typeid']]['title'];

		$credits_price[$v['credits_typeid']]['credits_price'] = $v['credits_price'];

		$credits_price[$v['credits_typeid']]['title2'] = $_G['setting']['extcredits'][$v['credits_present_type']]['title'];

		$credits_price[$v['credits_typeid']]['credits_present_num'] = $v['credits_present_num'];

	}

	if ($credits_typeid){

	    arrjson_iconv($credits_price[$credits_typeid]);

	}

	if(checkmobile()){

		include template('ymq_vip:buy_credits');

		exit;

	}else if($_GET['mod'] !='spacecp'){

		include template('ymq_vip:buy_credits');

		exit;

	}

}else if($act == 'record'){

	$buy_credits = daddslashes($_GET['buy_credits']);

	$uid = intval($_GET['uid']);

	if(!$uid && $_GET['inajax']){

		echo '-1';exit;

	}else if(!$uid){

		showmessage('not_loggedin', 'member.php?mod=logging&action=login&referer='.urlencode($_G['siteurl'].'plugin.php?id=ymq_vip'));

		exit;

	}

	$perpage = 10;

	$curpage = empty($_GET['page']) ? 1 : intval($_GET['page']);

	$start = ($curpage-1)*$perpage;

	$where = array();

	if ($buy_credits){

		$where['uid'] = $uid;

		$where['deal_pyte'] = $buy_credits;

	}else{

		$where['uid'] = $uid;

		$where['deal_pyte_no'] = 'buy_credits';

	}

	$vip_list = C::t('#ymq_vip#ymq_payrecord_log')->get_payrecord_log_list($start,$perpage,$where);

	if(empty($_G['cache']['usergroups'])) {

	    loadcache('usergroups');

	}

	$log_list = array();

	foreach($vip_list as $k => $v){

        $log_list[$k]['vip_id'] = $v['vip_id'];

        $log_list[$k]['out_trade_no'] = $v['out_trade_no'];

		$log_list[$k]['pay_pyte'] = get_type($v['pay_pyte']);

		$log_list[$k]['deal_pyte'] = get_type($v['deal_pyte']);

		$log_list[$k]['deal_pyte1'] = $v['deal_pyte'];

        $log_list[$k]['groupid_new'] = $v['groupid_new'];

		$log_list[$k]['group_name'] = ymq_DeletHtml($_G['cache']['usergroups'][$v['groupid_new']]['grouptitle']);

        $log_list[$k]['groupid_overdue'] = $v['groupid_overdue'] != '0' ? dgmdate($v['groupid_overdue'],'Y-m-d') : '--';

        $log_list[$k]['credits_type'] = $v['credits_type'];

        $log_list[$k]['credits_present'] = $v['credits_present'];

        $log_list[$k]['money'] = $v['money'];

        $log_list[$k]['pay_status'] = $v['pay_status'] == 1 ? '<em class="ymq-label-s" style="color:#8BC34A;border:1px solid #8BC34A;">'.lang('plugin/ymq_vip','pay_isok').'</em>' : '<em class="ymq-label-s">'.lang('plugin/ymq_vip','pay_isno').'</em>';

        $log_list[$k]['dateline'] = dgmdate($v['dateline'],'Y/m/d H:i');

	}

	if(!checkmobile()){

		include template('ymq_vip:vipnotice');

		exit;

	}
	$comiis_head = array(
	'left' => '',
	'center' => $navtitle1,
	'right' => '',	
	);
	include template('ymq_vip:buy_list');

}

?>