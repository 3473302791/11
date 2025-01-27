<?php

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {

	exit('Access Denied');

}

if (file_exists(DISCUZ_ROOT.'./source/plugin/ymq_vip/function.php')) {

	@include_once DISCUZ_ROOT.'./source/plugin/ymq_vip/function.php';

}



	$perpage = 20;

	$curpage = empty($_GET['page']) ? 1 : intval($_GET['page']);

	$start = ($curpage-1)*$perpage;

	$where = array();

	$where['pay_status'] = 1;

	$where['deal_pyte_no'] = 'buy_credits';

	if ($_GET['uid']){

		$uid = ($uids = C::t('common_member')->fetch_uid_by_username($_GET['uid'])) ? $uids : $_GET['uid'];

		$uid = is_numeric($uid) ? $uid : -1;

		$where['uid'] = intval($uid);

	}

	$ORDER = 'ORDER BY dateline DESC,groupid_overdue DESC LIMIT %d,%d';

	$count = C::t('#ymq_vip#ymq_payrecord_log')->get_payrecord_log_count($where);

	$mpurl = ADMINSCRIPT."?action=plugins&operation=config&do=".$pluginid."&identifier=ymq_vip&pmod=admin_member&uid=".$uid;

	$multipage = multi($count, $perpage,$curpage,$mpurl, 0, 5);

	$vip_list = C::t('#ymq_vip#ymq_payrecord_log')->get_payrecord_log_list($start,$perpage,$where,$ORDER);

    $bnt = lang('plugin/ymq_vip','list_find');

    $select_find = lang('plugin/ymq_vip','select_find');

	if(empty($_G['cache']['usergroups'])) {

		loadcache('usergroups');

	}

echo <<<SEARCH

	<form method="post" autocomplete="off" id="tb_search" action="$mpurl">

	<table style="padding:10px 0;">

		<tbody>

			<tr>

				<th>&nbsp;$select_find</th><td><input type="text" class="txt" name="uid" id="uid" value=""></td>

				<th></th><td><input type="submit" class="btn" value="$bnt"></td>

			</tr>

		</tbody>

	</table>

	</form>

SEARCH;



	showtableheader(lang('plugin/ymq_vip','log_member'));

		showtablerow('',array(),array(

			'UID',

			lang('plugin/ymq_vip','member_name'),

			lang('plugin/ymq_vip','member_group'),

			lang('plugin/ymq_vip','member_data'),

			lang('plugin/ymq_vip','member_remark'),

			lang('plugin/ymq_vip','payrecord5')

		));

		foreach ($vip_list as $v){

			$get_name = getuserbyuid($v['uid']);

			showtablerow('', array('class="td26"', 'class="td26"', 'class="td26"'), array(

				$v['uid'],

				$get_name['username'],

				$_G['cache']['usergroups'][$v['groupid_new']]['grouptitle'],

				$groupid_overdue = $v['groupid_overdue'] < TIMESTAMP ? '<span style="color:#f00;">'.lang('plugin/ymq_vip', 'isoverdue').'</span>' : dgmdate($v['groupid_overdue'],'Y-m-d'),

				get_type($v['deal_pyte']),

				dgmdate($v['dateline']),

			));

		}

	showtablefooter();

	echo $multipage;

?>