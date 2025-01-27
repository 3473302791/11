<?php

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {

	exit('Access Denied');

}

	showtableheader();

	showsetting(lang('plugin/ymq_vip', 'applik'),'applik',$_G['siteurl'].'plugin.php?id=ymq_vip', 'text');

	showsetting(lang('plugin/ymq_vip', 'applik2'),'applik',$_G['siteurl'].'plugin.php?id=ymq_vip:buy_credits', 'text');

	showtablefooter();

?>