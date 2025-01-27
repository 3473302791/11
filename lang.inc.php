<?php



if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {

    exit('Access Denied');

}



loadcache(array('pluginlanguage_script','pluginlanguage_template'));



if(submitcheck('submit')){

	$parameteript = array();

	$parametertpl = array();

	if(!empty($_GET['parameteript']) || !empty($_GET['parametertpl'])){

		$parameteript = unserialize(DB::result_first("SELECT DATA FROM ".DB::table('common_syscache')." WHERE cname='pluginlanguage_script'"));

		$parameteript['ymq_vip'] = $_GET['parameteript'];

		C::t('common_syscache')->update('pluginlanguage_script', $parameteript);

		unset($parameteript);

		$parametertpl = unserialize(DB::result_first("SELECT DATA FROM ".DB::table('common_syscache')." WHERE cname='pluginlanguage_template'"));

		$parametertpl['ymq_vip'] = $_GET['parametertpl'];

		C::t('common_syscache')->update('pluginlanguage_template', $parametertpl);

		unset($parametertpl);

		cpmsg(lang('plugin/ymq_vip','editok'), "action=plugins&operation=config&do=$pluginid&identifier=ymq_vip&pmod=lang", 'succeed');

	}

}



showformheader("plugins&operation=config&do=$pluginid&identifier=ymq_vip&pmod=lang");

	showtableheader(lang('plugin/ymq_vip', 'packet'));

		foreach ($_G['cache']['pluginlanguage_script']['ymq_vip'] as $key => $val){

			showsetting($key, 'parameteript['.$key.']', $val, 'text', 0, 0 );

		}

		foreach ($_G['cache']['pluginlanguage_template']['ymq_vip'] as $k => $v){

			showsetting($k, 'parametertpl['.$k.']', $v, 'text', 0, 0 );

		}

	showsubmit('submit');

	showtablefooter();

showformfooter();

?>