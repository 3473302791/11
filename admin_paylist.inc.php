<?php

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {

	exit('Access Denied');

}

if(file_exists($ymq_pay_list = DISCUZ_ROOT.'./data/sysdata/cache_ymq_pay_list.php')){

	@include $ymq_pay_list;

}

if($_GET['act'] == 'adds'){

	if(submitcheck('submit')){

		$pay_list = array();

        $pay_list['ymq_appid'] = addslashes($_GET['ymq_appid']);

        $pay_list['ymq_key'] = addslashes($_GET['ymq_key']);

		writetocache('ymq_pay_list',getcachevars(array('pay_list'=>$pay_list)));

		cpmsg(lang('plugin/ymq_vip', 'bcok'),'action=plugins&operation=config&do='.$pluginid.'&identifier=ymq_vip&pmod=admin_paylist','succeed');

	}

}

showformheader('plugins&operation=config&do='.$pluginid.'&identifier=ymq_vip&pmod=admin_paylist&act=adds');

showtableheader('��������');

showsetting('Ӧ��app_id', 'ymq_appid', $pay_list['ymq_appid'], 'text');

showsetting('Ӧ����Կ', 'ymq_key', $pay_list['ymq_key'], 'text');


showsubmit('submit',lang('plugin/ymq_vip', 'adds'));

showtablefooter();

showformfooter();

?>