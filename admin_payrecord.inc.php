<?php

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {

	exit('Access Denied');

}

if (file_exists(DISCUZ_ROOT.'./source/plugin/ymq_vip/function.php')) {

	@include_once DISCUZ_ROOT.'./source/plugin/ymq_vip/function.php';

}



    $act = daddslashes($_GET['act']);

	if ($act == 'empty' && submitcheck('submit')){

		$d = C::t('#ymq_vip#ymq_payrecord_log')->delete(array('pay_status' => -1));

		cpmsg(lang('plugin/ymq_vip','delok'),'action=plugins&operation=config&do='.$pluginid.'&identifier=ymq_vip&pmod=admin_payrecord', 'succeed');



    }else if($act == 'budai'){

		$order = C::t('#ymq_vip#ymq_payrecord_log')->get_payrecord_log_first(array('out_trade_no'=> daddslashes($_GET['out_trade_no'])));

		if ($order['deal_pyte'] == 'buy_credits'){

			update_credits_pay($order, '--');

		}else{

			update_vip_pay($order,'--');

		}

		cpmsg(lang('plugin/ymq_vip','langs_004'),'action=plugins&operation=config&do='.$pluginid.'&identifier=ymq_vip&pmod=admin_payrecord', 'succeed');

	}

	$perpage = 20;

	$curpage = empty($_GET['page']) ? 1 : intval($_GET['page']);

	$start = ($curpage-1)*$perpage;

	$pdeal_pyte = daddslashes($_GET['deal_pyte']);

	$ppay_status = daddslashes($_GET['pay_status']);

	$pay_pytes = daddslashes($_GET['pay_pyte']);

	$dateline = dmktime($_GET['dateline']);

	$dateline2 = dmktime($_GET['dateline2']);

    $where = array();

	if ($pdeal_pyte){

		$where['deal_pyte'] = $pdeal_pyte;

	}

	if($ppay_status){

		$where['pay_status'] = $ppay_status;

	}

	if ($pay_pytes){

		$where['pay_pyte'] = $pay_pytes;

	}

	if ($_GET['uid']){

		$uid = ($uids = C::t('common_member')->fetch_uid_by_username($_GET['uid'])) ? $uids : $_GET['uid'];

		$uid = is_numeric($uid) ? $uid : -1;

		$where['uid'] = intval($uid);

	}

	if($dateline){

		$date = strtotime(date('Y-m-d',$dateline));

        $where['dateline'] = $date;

	}

	if($dateline2){

		$date2 = strtotime(date('Y-m-d',strtotime( "+1 day",$dateline2)));

        $where['dateline2'] = $date2;

	}

	$count = C::t('#ymq_vip#ymq_payrecord_log')->get_payrecord_log_count($where);

	$mpurl = ADMINSCRIPT."?action=plugins&operation=config&do=".$pluginid."&identifier=ymq_vip&pmod=admin_payrecord&deal_pyte=".$pdeal_pyte."&pay_status=".$ppay_status."&pay_pyte=".$pay_pytes."&uid=".$uid."&dateline=".$dateline."&dateline2=".$dateline2;

	$multipage = multi($count, $perpage,$curpage,$mpurl, 0, 5);

	$vip_list = C::t('#ymq_vip#ymq_payrecord_log')->get_payrecord_log_list($start,$perpage,$where);

	$bnt = lang('plugin/ymq_vip','list_find');

    $screen = lang('plugin/ymq_vip','screen');

    $pay_statu = lang('plugin/ymq_vip','pay_statuss');

    $uids = lang('plugin/ymq_vip','select_find');

    $datelines = lang('plugin/ymq_vip','payrecord5');

    $pay_pytemc = lang('plugin/ymq_vip','payrecord1');



	$pay_pyte = array();

	$pay_pyte[] = array('credits_pay',lang('plugin/ymq_vip','type1'));

	$pay_pyte[] = array('zfb_pc',lang('plugin/ymq_vip','type2'));

	$pay_pyte[] = array('zfb_web',lang('plugin/ymq_vip','type8'));

	$pay_pyte[] = array('wx_pc',lang('plugin/ymq_vip','type3'));

	$pay_pyte[] = array('wx_h5',lang('plugin/ymq_vip','type9'));

	$pay_pyte[] = array('wx_gzh',lang('plugin/ymq_vip','type10'));

	$pay_pyte[] = array('magappx',lang('plugin/ymq_vip','type12'));

	$pay_pyte[] = array('Appbyme_wx',lang('plugin/ymq_vip','type11'));

	$pay_pyte[] = array('Appbyme_zfb',lang('plugin/ymq_vip','type13'));

	$pay_pyte[] = array('QianFan','QianFan');



	$deal_pyte = array();

	$deal_pyte[] = array('buy_vip',lang('plugin/ymq_vip','deal_pyte_vip'));

	$deal_pyte[] = array('vip_renew',lang('plugin/ymq_vip','deal_pyte_renew'));

	$deal_pyte[] = array('buy_credits',lang('plugin/ymq_vip','deal_pyte_credits'));

	

	$pay_statuss = array();

	$pay_statuss[] = array(-1, lang('plugin/ymq_vip','pay_isno'));

	$pay_statuss[] = array(1, lang('plugin/ymq_vip','pay_isok'));

	

	$deal_pyte = get_select('deal_pyte', $deal_pyte, $pdeal_pyte, array(0,lang('plugin/ymq_vip','isscreen')));

	$status_pyte = get_select('pay_status', $pay_statuss, $ppay_status, array(0,lang('plugin/ymq_vip','isscreen')));

	$pay_pyte = get_select('pay_pyte', $pay_pyte, $pay_pytes, array(0,lang('plugin/ymq_vip','isscreen')));



echo <<<SEARCH

        <script src="static/js/calendar.js" type="text/javascript"></script>

		<form method="post" autocomplete="off" id="tb_search" action="$mpurl">

		<table style="padding:10px 0;">

			<tbody>

				<tr>

				<td>&nbsp;$screen&nbsp;&nbsp;$deal_pyte</td>

				<td>&nbsp;&nbsp;&nbsp;&nbsp;$pay_statu&nbsp;&nbsp;&nbsp;$status_pyte&nbsp;&nbsp;&nbsp;</td>

				<td>&nbsp;&nbsp;&nbsp;&nbsp;$pay_pytemc&nbsp;&nbsp;&nbsp;$pay_pyte&nbsp;&nbsp;&nbsp;</td>

				<td>&nbsp;&nbsp;&nbsp;&nbsp;$uids&nbsp;&nbsp;&nbsp;<input type="text" class="txt" name="uid" value="" style="width:80px;">&nbsp;&nbsp;&nbsp;</td>

                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$datelines</td><td>&nbsp;<td>

                  <input type="text" class="txt" name="dateline" value="{$_GET['dateline']}" onclick="showcalendar(event, this)">~

                  <input type="text" class="txt" name="dateline2" value="{$_GET['dateline2']}" onclick="showcalendar(event, this)">

				</td>

				<td>&nbsp;&nbsp;<input type="submit" class="btn" value="$bnt"></td>

				</tr>

			</tbody>

		</table>

		</form>

SEARCH;

showformheader('plugins&operation=config&do='.$pluginid.'&identifier=ymq_vip&pmod=admin_payrecord&act=empty','enctype');//

	showtableheader(lang('plugin/ymq_vip','payrecord'));

		showtablerow('',array('class="td25"', 'class="td28"'),array(

			'ID',

			lang('plugin/ymq_vip','payrecord1'),

			lang('plugin/ymq_vip','screen'),

			lang('plugin/ymq_vip','member_name'),

			lang('plugin/ymq_vip','payrecord2'),

			lang('plugin/ymq_vip','pay_statuss'),

			lang('plugin/ymq_vip','payrecord3'),

			lang('plugin/ymq_vip','payrecord4'),

			lang('plugin/ymq_vip','payrecord5'),

			lang('plugin/ymq_vip','langs_005')

		));

		foreach ($vip_list as $v) {

			if($v['pay_status'] == -1){

			    $payment = '<a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do=$pluginid&identifier=ymq_vip&pmod=admin_payrecord&act=budai&out_trade_no='.$v['out_trade_no'].'">'.lang('plugin/ymq_vip','langs_006').'</a>';

			}else{

                $payment = lang('plugin/ymq_vip','langs_007');

			}

			$get_name = getuserbyuid($v['uid']);

			showtablerow('', array('class="td25"', 'class="td28"'), array(

				$v['log_id'],

				get_type($v['pay_pyte']),

				get_type($v['deal_pyte']),

				'<a href="home.php?mod=space&uid='.$v['uid'].'" target ="_blank">'.$get_name['username'].'</a>',

				$money = $v['money'] != 0 ? intval($v['money']).'/'.lang('plugin/ymq_vip','money') : intval($v['credits_present']).'/'.$_G['setting']['extcredits'][$v['credits_type']]['title'],

				$pay_status = $v['pay_status'] == 1 ? '<span style="color:#f00;">'.lang('plugin/ymq_vip','pay_isok').'</span>' : lang('plugin/ymq_vip','pay_isno'),

				$trade_no = $v['trade_no'] ? $v['trade_no'] : '--',

				$pay_dateline = $v['pay_dateline'] ? dgmdate($v['pay_dateline'],'Y-m-d H:i:s') : '--',

				dgmdate($v['dateline'],'Y-m-d H:i:s'),

				$payment

			));

		}

		showsubmit('submit', lang('plugin/ymq_vip', 'empty'),'','',$multipage);

	showtablefooter();

showformfooter();

?>