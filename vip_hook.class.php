<?php

if(!defined('IN_DISCUZ')) {

	exit('Access Denied');

}

if(file_exists(DISCUZ_ROOT.'./source/plugin/ymq_vip/function.php')){

    @include_once DISCUZ_ROOT.'./source/plugin/ymq_vip/function.php';

}



class plugin_ymq_vip {



	function common(){

		global $_G;

		if($_G['uid'] && CURSCRIPT == 'home' && CURMODULE =='spacecp' && $_GET['ac'] =='usergroup' && $_GET['do'] == 'expiry'){

			$config = vip_config();

			$groupidlists = CeckUserGorup($_G['uid']);

			$new_groupid = '';

			foreach($groupidlists['group_list'] as $grouplist){

				if($grouplist['groupid_time'] && $grouplist['groupid_time'] > TIMESTAMP){

					$new_groupid = $grouplist['groupid'];

					break;

				}

			}

			if($new_groupid == ''){

				if($groupidlists['groupid_main']['groupid']){

					$new_groupid = $groupidlists['groupid_main']['groupid'];

				}else{

					foreach($groupidlists['group_list'] as $grouplist){

						if(!in_array($grouplist['groupid'], $groupidlists['groupidarr'])){

							$new_groupid = $grouplist['groupid'];

							break;

						}

					}

				}

			}



			if($new_groupid != $_G['groupid']){

				$result = GroupSwitch($new_groupid,$_G['uid']);

				if($result['code'] == 1) {

					showmessage(lang('plugin/ymq_vip', 'operation3'), $_G['siteurl']);

				}

			}

		}

	}



	function global_footer(){

		global $_G;

        $config = vip_config();

		if($_G['uid'] && $config['ymq_vipRemind']){

			$remind_expired = getcookie('remind_expired');

			$groupidlist = CeckUserGorup($_G['uid']);

			if(!$remind_expired && $groupidlist['effectiveMsg'] && $groupidlist['effectiveMsg']['effective_days'] < $config['ymq_vipRemind'] && $_GET['do'] != 'expiry'){

				dsetcookie('remind_expired', '1', $config['ymq_vipRemind_time']);

				if(in_array('ymq_notice', $_G['setting']['plugins']['available'])){

					$newnote = lang('plugin/ymq_vip', 'vip_past');

					$url = $_G['siteurl'].'plugin.php?id=ymq_vip';

					vip_MsgWxSend($_G['uid'], 'vip_past', $newnote,$url,false);
					

				}

				return '<script type="text/javascript">showWindow(\'ymq_expired\',\'plugin.php?id=ymq_vip:expired\');</script>';

			}

		}

	}

}



class mobileplugin_ymq_vip extends plugin_ymq_vip {



	function global_footer_mobile(){

		global $_G;

        $config = vip_config();

		if($_G['uid'] && $config['ymq_vipRemind']){

			$remind_expired = getcookie('remind_expired');

			$groupidlist = CeckUserGorup($_G['uid']);

			if(!$remind_expired && $groupidlist['effectiveMsg'] && $groupidlist['effectiveMsg']['effective_days'] < $config['ymq_vipRemind'] && $_GET['do'] != 'expiry'){

				dsetcookie('remind_expired', '1', $config['ymq_vipRemind_time']);

				if(in_array('ymq_notice', $_G['setting']['plugins']['available'])){

					$newnote = lang('plugin/ymq_vip', 'vip_past');

					$url = $_G['siteurl'].'plugin.php?id=ymq_vip';

					vip_MsgWxSend($_G['uid'], 'vip_past', $newnote,$url,false);

				}

				include template('ymq_vip:return');

				return $return;

			}

		}

	}

}