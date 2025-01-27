<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
if (file_exists(DISCUZ_ROOT.'./source/plugin/ymq_vip/function.php')) {
	@include_once DISCUZ_ROOT.'./source/plugin/ymq_vip/function.php';
}

if (!$_G['uid']){
    showmessage('not_loggedin', 'member.php?mod=logging&action=login&referer='.urlencode($_G['siteurl'].'plugin.php?id=ymq_vip:expired'));
}
$config = vip_config();
$groupidlist = CeckUserGorup($_G['uid']);
$log_vip_id = DB::result_first('SELECT vip_id FROM %t WHERE uid=%d and groupid_new=%d and pay_status=%d',array('ymq_payrecord_log',$_G['uid'],$_G['groupid'],1));

include template('ymq_vip:expired');
?>