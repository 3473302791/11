<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function vip_config(){
	global $_G;
	if(empty($_G['cache']['plugin'])){
		loadcache('plugin');
	}
	$config = $_G['cache']['plugin']['ymq_vip'];
	$config['isFromWeixin'] = strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false;
	$config['isAppbyme'] = strpos($_SERVER['HTTP_USER_AGENT'], 'Appbyme') !== false;
	$config['isMAGAPPX'] = strpos($_SERVER['HTTP_USER_AGENT'], 'MAGAPP') !== false;
	$config['isQianFan'] = strpos($_SERVER['HTTP_USER_AGENT'], 'QianFan') !== false;
	$config['isMobile'] = checkmobile();
	return $config;
}

function get_group_selected($groupid = null){
	global $lang;
	$groupselect = array(); 
	$group_list = C::t('common_usergroup')->range();
	foreach($group_list as $group){
		$course = unserialize($groupid);
		$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
		if(in_array($group['groupid'], $course)) {
			$groupselect[$group['type']] .= '<option value="'.$group['groupid'].'" selected >'.$group['grouptitle'].'</option>';
		} else {
			$groupselect[$group['type']] .= '<option value="'.$group['groupid'].'">'.$group['grouptitle'].'</option>';
		}
	}
    $groupselect = '<optgroup label="'.$lang['usergroups_member'].'">'.$groupselect['member'].'</optgroup>'
	.($groupselect['special'] ? '<optgroup label="'.$lang['usergroups_special'].'">'.$groupselect['special'].'</optgroup>' : '')
	.($groupselect['specialadmin'] ? '<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groupselect['specialadmin'].'</optgroup>' : '')
	.'<optgroup label="'.$lang['usergroups_system'].'">'.$groupselect['system'].'</optgroup>';
    return $groupselect;
}

function get_extcredits_selected($extcreditsid = null){
	global $_G;
	for($i=1;$i<=8;$i++){
		if($_G['setting']['extcredits'][$i]['title']!=''){
			if ($i == $extcreditsid){
				$tmpoption.='<option value="'.$i.'" selected >'.$_G['setting']['extcredits'][$i]['title'].'</option>';
			}else{
			    $tmpoption.='<option value="'.$i.'" >'.$_G['setting']['extcredits'][$i]['title'].'</option>';	
			}
		}
	}
	return $tmpoption;
}

function get_Creation_pay($isbuy_vip=true,$pay_type,$vip_id,$jump_url,$moey=null){
	global $_G;
    $config = vip_config();
	$orderno = date('Ymd',$_G['timestamp']).get_randChar(12);
	$vip_list = C::t('#ymq_vip#ymq_product_list')->get_viplist_first($vip_id);

	$data = array();
	$data['vip_id'] = $vip_id;
	$data['out_trade_no'] = $orderno;

	if ($pay_type =='jf'){
		$data['pay_pyte'] = 'credits_pay';
	}else if($pay_type =='zfb' && !$config['isMobile']){
		$data['pay_pyte'] = 'zfb_pc';
	}else if($pay_type =='zfb' && $config['isMobile'] && !$config['isFromWeixin']){
		$data['pay_pyte'] = 'zfb_web';
	}else if($pay_type =='wx' && !$config['isMobile']){
		$data['pay_pyte'] = 'wx_pc';
	}else if($pay_type =='wx' && $config['isMobile'] && !$config['isFromWeixin'] && $config['ymq_wxh5']){
		$data['pay_pyte'] = 'wx_h5';
	}else if($pay_type =='wx' && $config['isMobile'] && $config['isFromWeixin']){
		$data['pay_pyte'] = 'wx_gzh';
	}else if($pay_type =='Appbyme_wx' && $config['isAppbyme']){
		$data['pay_pyte'] = 'Appbyme_wx';
	}else if($pay_type =='Appbyme_zfb' && $config['isAppbyme']){
		$data['pay_pyte'] = 'Appbyme_zfb';
	}else if($pay_type =='magappx' && $config['isMAGAPPX']){
		$data['pay_pyte'] = 'magappx';
	}else if($pay_type =='QianFan' && $config['isQianFan']){
		$data['pay_pyte'] = 'QianFan';
	}

	if($isbuy_vip && !empty($vip_id)){

		$vip_discounts = C::t('#ymq_vip#ymq_payrecord_log')->get_payrecord_log_count(array('uid'=> $_G['uid'],'pay_status'=> 1,'vip_id'=> $vip_id));
		$data['deal_pyte'] = $deal_pyte = $vip_discounts ? 'vip_renew' : 'buy_vip';
		$data['uid'] = $_G['uid'];
		$data['groupid_new'] = $vip_list['vip_group'];
	    $restrict_group = unserialize($vip_list['vip_restrict_group']);
		if (!in_array($_G['groupid'],$restrict_group) && $vip_list['vip_present_open'] && ($vip_discounts <= $vip_list['vip_restrict_amount'])){
			$vip_time = $vip_list['vip_time'] + $vip_list['vip_present_date'];
			$data['groupid_overdue'] = TIMESTAMP + $vip_time*86400;
			if($config['ymq_notify']){
				$note_groupid_overdue = lang('plugin/ymq_vip','vip_discount_value3').$vip_list['vip_present_date'].lang('plugin/ymq_vip', 'vip_discount_value4');
				notification_add($_G['uid'],'system',$note_groupid_overdue,$notevars = array(),1);
			}
		}else{
			$data['groupid_overdue'] = TIMESTAMP + $vip_list['vip_time']*86400;
		}
		$data['credits_type'] = $vip_list['vip_credits_type'];
		$data['credits_present'] = $pay_type =='jf' ? $vip_list['vip_credits_price'] : '';
		$groupid_discount = unserialize($vip_list['vip_discount_group']);
		if ($pay_type !='jf'){
			if (in_array($_G['groupid'],$groupid_discount) && $vip_list['vip_discount'] && $vip_discounts){
//				$data['money'] = round($vip_list['vip_price'] * $vip_list['vip_discount_value']);
                $data['money'] = round($vip_list['vip_price'] * $vip_list['vip_discount_value'],2);
                if($config['ymq_notify']){
					$note_vip_discount = lang('plugin/ymq_vip', 'vip_discount_value1').$vip_list['vip_discount_value'].lang('plugin/ymq_vip', 'vip_discount_value2');
					notification_add($_G['uid'],'system',$note_vip_discount,$notevars = array(),1);
				}
			}else{
//				$data['money'] = round($vip_list['vip_price']);
                $data['money'] = round($vip_list['vip_price'],2);
			}
		}else{
			$data['money'] = '';
		}	
		$data['pay_status'] = -1;
		$data['dateline'] = $_G['timestamp'];
		$data['jump_url'] = $jump_url;
		$result = C::t('#ymq_vip#ymq_payrecord_log')->get_payrecord_log_first(array('uid'=> $_G['uid'],'pay_status'=> -1,'vip_id'=> $vip_id,'deal_pyte_no'=> 'buy_credits'));
		if($vip_discounts){
			$memberfieldforum = C::t('common_member_field_forum')->fetch($_G['uid']);
			$groupterms = dunserialize($memberfieldforum['groupterms']);
			$data['groupid_overdue'] = ($data['groupid_overdue'] - TIMESTAMP) + $groupterms['ext'][$vip_list['vip_group']];
		}
		if($result){
			C::t('#ymq_vip#ymq_payrecord_log')->update($data,array('out_trade_no'=> $result['out_trade_no']));
		   return true;
		}else{
			C::t('#ymq_vip#ymq_payrecord_log')->insert($data);
			return true;
		}

	}else if($moey){
        $credits_type = C::t('#ymq_vip#ymq_credits_state')->get_creditstype_first($vip_id);
		$data['deal_pyte'] = 'buy_credits';
		$data['uid'] = $_G['uid'];
		$data['credits_type'] = $vip_id;
//        $data['credits_present'] = round($moey/$credits_type['credits_ratio']);
        $data['credits_present'] =round($moey/$credits_type['credits_ratio'],2);
//        $data['money'] = round($moey);
        $data['money'] = round($moey,2);
		$data['pay_status'] = -1;
		$data['dateline'] = $_G['timestamp'];
		$data['jump_url'] = $jump_url;
		$result = C::t('#ymq_vip#ymq_payrecord_log')->get_payrecord_log_first(array('uid'=> $_G['uid'],'pay_status'=> -1,'vip_id'=> $vip_id,'deal_pyte'=> 'buy_credits'));
		if($result){
		   C::t('#ymq_vip#ymq_payrecord_log')->update($data,array('out_trade_no'=> $result['out_trade_no']));
		   return true;
		}else{
		   C::t('#ymq_vip#ymq_payrecord_log')->insert($data);
		   return true;
		}
	}
}

function update_vip_pay($order, $trade_no=null){
	global $_G;
	$viplist = C::t('#ymq_vip#ymq_product_list')->get_viplist_first($order['vip_id']);
	$groupid = $order['groupid_new'];
	$groupid_now = getuserbyuid($order['uid']);
	$restrict_group = unserialize($viplist['vip_restrict_group']);
	$vip_discounts = C::t('#ymq_vip#ymq_payrecord_log')->get_payrecord_log_count(array('uid'=>$order['uid'],'pay_status'=>1,'vip_id'=>$order['vip_id'],'deal_pyte_no'=> 'buy_credits'));

	if (!in_array($groupid_now['groupid'],$restrict_group) && $viplist['vip_present_open'] && $vip_discounts <= $viplist['vip_restrict_amount']){

		if ($viplist['vip_credits_present']){
			updatemembercount($order['uid'], array('extcredits'.$viplist['vip_credits_present_type'] => $viplist['vip_credits_present']), true, '',1,1,lang('plugin/ymq_vip','give1'),lang('plugin/ymq_vip','give2').$viplist['vip_name'].lang('plugin/ymq_vip','give3'));
		}

		if($viplist['vip_present_date'] && $vip_discounts){
			$note_xfmsg = lang('plugin/ymq_vip', 'xfmsg').$viplist['vip_name'].lang('plugin/ymq_vip', 'xfmsgok');
			$note_zsvipmsg = lang('plugin/ymq_vip', 'xfmsg').$viplist['vip_name'].lang('plugin/ymq_vip', 'xfzsmsg').$viplist['vip_present_date'].lang('plugin/ymq_vip', 'xfmsgt');
		}else{
			$note_xfmsg = lang('plugin/ymq_vip','gmmsg').$viplist['vip_name'].lang('plugin/ymq_vip','xfmsgok');
			$note_zsvipmsg = lang('plugin/ymq_vip','gmmsg').$viplist['vip_name'].lang('plugin/ymq_vip','xfzsmsg').$viplist['vip_present_date'].lang('plugin/ymq_vip', 'xfmsgt');
		}
		
		$groupdate = $viplist['vip_time'] + $viplist['vip_present_date'];
		notification_add($order['uid'],'system',$note_xfmsg,$notevars = array(),1);
		if($plugin['pluginid'] = vip_GetPluginList('ymq_notice')){
			//vip_MsgWxSend($order['uid'], 'vip_turn', $note_xfmsg);
			//开通会员消息
			//$order['groupid_overdue']有效期
			//$viplist['vip_name']会员名称
			//$viplist['vip_credits_price']支付价格
			//$viplist['vip_credits_present']赠送积分
			//$viplist['vip_time']购买天数
			//$viplist['vip_present_date']增送天数
			$msg = "开通会员消息";
			vip_MsgWxSend($order['uid'], 'vip_turn','','',$viplist['vip_credits_present'],$viplist['vip_credits_price'],$order['groupid_overdue'],$viplist['vip_name'],$viplist['vip_time'],$viplist['vip_present_date'],$msg, $note_xfmsg);
		 
		}
		
		if($viplist['vip_present_date']){
			notification_add($order['uid'],'system',$note_zsvipmsg,$notevars = array(),1);
		    if($plugin['pluginid'] = vip_GetPluginList('ymq_notice')){
				vip_MsgWxSend($order['uid'], 'vip_Presented', $note_zsvipmsg);
			}
		}
	}else{
		if($viplist['vip_present_date'] && $vip_discounts){
			$note_xfmsg = lang('plugin/ymq_vip', 'xfmsg').$viplist['vip_name'].lang('plugin/ymq_vip', 'xfmsgok');
		}else{
			$note_xfmsg = lang('plugin/ymq_vip','gmmsg').$viplist['vip_name'].lang('plugin/ymq_vip','xfmsgok');
		}
		$groupdate = $viplist['vip_time'];
		notification_add($order['uid'],'system',$note_xfmsg,$notevars = array(),1);
		if($plugin['pluginid'] = vip_GetPluginList('ymq_notice')){
			//vip_MsgWxSend($order['uid'], 'vip_turn', $note_xfmsg);
			//会员续费成功提醒
            $msg = "会员续费成功";
            vip_MsgWxSend($order['uid'], 'vip_renew','','',$viplist['vip_credits_present'],$viplist['vip_credits_price'],$order['groupid_overdue'],$viplist['credits_present_type'],$viplist['vip_time'],$viplist['vip_present_date'],$msg, $note_msg);
		}
	}

	$data = array();
	$data['pay_status'] = 1;
	$data['pay_dateline'] = TIMESTAMP;
	$data['trade_no'] = $trade_no ? $trade_no :'--';
	$isok = C::t('#ymq_vip#ymq_payrecord_log')->update($data,array('out_trade_no' => $order['out_trade_no']));
	if($isok){
		$memberlist = C::t('common_member')->fetch($order['uid']);
		$extgroupids = $memberlist['extgroupids'] ? explode("\t", $memberlist['extgroupids']) : array();
		$memberfieldforum = C::t('common_member_field_forum')->fetch($order['uid']);
		$groupterms = dunserialize($memberfieldforum['groupterms']);
		unset($memberfieldforum);
		require_once libfile('function/forum');
		$extgroupidsarray = array();
		foreach(array_unique(array_merge($extgroupids, array($groupid))) as $extgroupid) {
			if($extgroupid) {
				$extgroupidsarray[] = $extgroupid;
			}
		}
		$extgroupidsnew = implode("\t", $extgroupidsarray);
		$groupterms['ext'][$groupid] = ($groupterms['ext'][$groupid] > TIMESTAMP ? $groupterms['ext'][$groupid] : TIMESTAMP) + $groupdate * 86400;
		$groupexpirynew = groupexpiry($groupterms);
		$maingroupid = empty($extgroupids) ? $memberlist['groupid'] : $groupterms['main']['groupid'];
		$groupterms['main'] = array('time' => $groupterms['ext'][$groupid], 'adminid' => $memberlist['adminid'], 'groupid' => $maingroupid);
		C::t('common_member')->update($order['uid'], array('groupexpiry' => $groupexpirynew, 'extgroupids' => $extgroupidsnew));
		C::t('common_member_field_forum')->update($order['uid'], array('groupterms' => serialize($groupterms)));
		GroupSwitch($groupid,$order['uid'],true);
		return true;
	}else{
		return false;
	}
}

function vip_GetPluginList($identifier){
	if(!$identifier){return false;}
	$pluginarr = DB::fetch_first('SELECT * FROM %t WHERE identifier=%s',array('common_plugin',$identifier));
	return $pluginarr;
}

function BuyVipCheck($groupid){
	global $_G;
	$extgroupids = $_G['member']['extgroupids'] ? explode("\t", $_G['member']['extgroupids']) : array();
	if($_G['groupid'] == 4 && $_G['member']['groupexpiry'] > 0 && $_G['member']['groupexpiry'] > TIMESTAMP) {
		return array('code' => -1,'msg' => lang('plugin/ymq_vip', 'langs_001'), 'groupid' => $groupid);
	}
	$group = C::t('common_usergroup')->fetch($groupid);
	if($group['type'] != 'special' || $group['radminid'] != 0) {
		$group = null;
	}
	if(empty($group)) {
		return array('code' => -2,'msg' => lang('plugin/ymq_vip', 'langs_002'), 'groupid' => $groupid);
	}else{
		return array('code' => 1,'msg' => 'ok', 'groupid' => $groupid);
	}
}

function GroupSwitch($groupid,$uid,$first=false){
	global $_G;
	$memberlist = C::t('common_member')->fetch($uid);
	$extgroupids = $memberlist['extgroupids'] ? explode("\t", $memberlist['extgroupids']) : array();
	if(!$first && !in_array($groupid, $extgroupids)) {
		return array('code' => -1,'msg' => lang('plugin/ymq_vip', 'langs_002'), 'groupid' => $groupid);
	}
	if($memberlist['groupid'] == 4 && $memberlist['groupexpiry'] > 0 && $memberlist['groupexpiry'] > TIMESTAMP) {
		return array('code' => -2,'msg' => lang('plugin/ymq_vip', 'langs_001'), 'groupid' => $groupid);
	}
	$group = C::t('common_usergroup')->fetch($groupid);
	$memberfieldforum = C::t('common_member_field_forum')->fetch($uid);
	$groupterms = dunserialize($memberfieldforum['groupterms']);
	unset($memberfieldforum);
	$extgroupidsnew = $memberlist['groupid'];
	$groupexpirynew = $groupterms['ext'][$groupid];
	foreach($extgroupids as $extgroupid) {
		if($extgroupid && $extgroupid != $groupid) {
			$extgroupidsnew .= "\t".$extgroupid;
		}
	}
	if($memberlist['adminid'] > 0 && $group['radminid'] > 0) {
		$newadminid = $memberlist['adminid'] < $group['radminid'] ? $memberlist['adminid'] : $group['radminid'];
	} elseif($memberlist['adminid'] > 0) {
		$newadminid = $memberlist['adminid'];
	} else {
		$newadminid = $group['radminid'];
	}

	C::t('common_member')->update($uid, array('groupid' => $groupid, 'adminid' => $newadminid, 'groupexpiry' => $groupexpirynew, 'extgroupids' => $extgroupidsnew));
	return array('code' => 1,'msg' => lang('plugin/ymq_vip', 'langs_003').$group['grouptitle'], 'groupid' => $groupid, 'grouptitle' => $group['grouptitle']);
}

function update_credits_pay($order, $trade_no=null){
	global $_G;
	$data = array();
	$data['pay_status'] = 1;
	$data['pay_dateline'] = TIMESTAMP;
	$data['trade_no'] = $trade_no ? $trade_no :'--';
	C::t('#ymq_vip#ymq_payrecord_log')->update($data,array('out_trade_no' => $order['out_trade_no']));
	updatemembercount($order['uid'], array('extcredits'.$order['credits_type'] => $order['credits_present']), true, 'AFD',1,1,1,1);
	$credits_list = C::t('#ymq_vip#ymq_credits_state')->get_creditstype_first($order['credits_type']);
	$cs_price = explode("|", $credits_list['credits_price']);
	$cs_present_num = explode("|", $credits_list['credits_present_num']);
	foreach ($cs_price as $k => $v){
		if($v == $order['credits_present']){
			$c_arrkey = $k;
			break;
		}
	}
	if($cs_present_num[$c_arrkey]){
        updatemembercount($order['uid'], array('extcredits'.$credits_list['credits_present_type'] => $cs_present_num[$c_arrkey]), true, '',1,1,lang('plugin/ymq_vip','give1'),lang('plugin/ymq_vip','type4').lang('plugin/ymq_vip','give3'));
		if($plugin['pluginid'] = vip_GetPluginList('ymq_notice')){
			$newnote = lang('plugin/ymq_vip', 'cs_present_num').$cs_present_num[$c_arrkey].$_G['setting']['extcredits'][$credits_list['credits_present_type']]['title'];
			vip_MsgWxSend($order['uid'], 'vip_Presented', $newnote,'',false);
		}
	}
	$note_msg = lang('plugin/ymq_vip','vip_paylang2').$order['credits_present'].$_G['setting']['extcredits'][$order['credits_type']]['title'].lang('plugin/ymq_vip','vip_payok');
	notification_add($order['uid'],'system',$note_msg,$notevars = array(),1);
	if($plugin['pluginid'] = vip_GetPluginList('ymq_notice')){
		//vip_MsgWxSend($order['uid'], 'vip_Pay', $note_msg);
		//会员充值成功提醒
      	$msg = "会员充值成功"; 
      	vip_MsgWxSend($order['uid'], 'vip_Pay','','','','','',$_G['setting']['extcredits'][$credits_list['credits_present_type']]['title'],$order['credits_present'],$present_num,$msg, $note_msg);
	}
	return true;
}

function get_randChar($length){
   $str = null;
   $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
   $max = strlen($strPol)-1;
   for($i=0;$i<$length;$i++){
    $str.=$strPol[rand(0,$max)];
   }
  return $str;
}

function arrjson_iconv($arr){
	if (is_array($arr)) {
		foreach($arr as $k => $v){
			if(strtolower(CHARSET) == 'gbk'){
				if(is_array($v)) {
					foreach($v as $key => $val){
						$ret[$k][$key] = iconv('gbk', 'utf-8',$val);
					}
				}else{
					$ret[$k] = iconv('gbk', 'utf-8',$v);
				}
			}else{
				if(is_array($v)) {
					foreach($v as $key => $val){
						$ret[$k][$key] = $val;
					}
				}else{
					$ret[$k] = $v;
				}
			}
		}
		echo json_encode($ret);exit;
	}else{
		echo json_encode($arr);exit;
	}
}

function get_uscredits($uid) {
    return DB::fetch_first('SELECT extcredits1,extcredits2,extcredits3,extcredits4,extcredits5,extcredits6,extcredits7,extcredits8 FROM '.DB::table('common_member_count').' WHERE uid='.$uid);
}

function get_type($type){
	switch ($type) {
		case 'credits_pay':
			$type = lang('plugin/ymq_vip','type1');
			break;
		case 'zfb_pc':
		    $type = lang('plugin/ymq_vip','type2');
			break;
		case 'zfb_web':
            $type = lang('plugin/ymq_vip','type8');
			break;
		case 'wx_pc':
		    $type = lang('plugin/ymq_vip','type3');
			break;
		case 'wx_h5':
		    $type = lang('plugin/ymq_vip','type9');
			break;
		case 'wx_gzh':
		    $type = lang('plugin/ymq_vip','type10');
			break;
		case 'buy_credits':
		    $type = lang('plugin/ymq_vip','type4');
			break;
		case 'vip_renew':
		    $type = lang('plugin/ymq_vip','type5');
			break;
		case 'buy_vip':
		    $type = lang('plugin/ymq_vip','type6');
			break;
		case 'Appbyme_wx':
		    $type = lang('plugin/ymq_vip','type11');
			break;
		case 'Appbyme_zfb':
		    $type = lang('plugin/ymq_vip','type13');
			break;
		case 'magappx':
		    $type = lang('plugin/ymq_vip','type12');
			break;
		case 'QianFan':
		    $type = 'QF_apppay';
			break;
		default:
			$type = lang('plugin/ymq_vip','type7');
	}
	return $type;
}

function get_select($name, $data, $selected, $initial) {
    $select = "<select name='$name' id='$name'>";
    if ($initial) {
        $select.= "<option value='".$initial[0]."'>".$initial[1]."</option>";
    }
    foreach ($data as $v) {
        $sed = $selected == $v[0] ? 'selected' : '';
        $select.= "<option value='".$v[0]."' $sed>".$v[1]."</option>";
    }
    $select.= "</select>";
    return $select;
}


function https(){
    if(!isset($_SERVER['HTTPS'])) return false;
		if($_SERVER['HTTPS'] === 1){
			return true;
		}else if($_SERVER['HTTPS'] === 'on'){
			return true;
		}else if($_SERVER['SERVER_PORT'] == 443){
			return true;
		}
    return false;
}

function ymq_DeletHtml($str){
	$str = trim($str);
	$str = strip_tags($str,"");
	$str = preg_replace("/\t/","",$str);
	$str = preg_replace("/\r\n/","",$str); 
	$str = preg_replace("/\r/","",$str); 
	$str = preg_replace("/\n/","",$str); 
	$str = preg_replace("/ /","",$str);
	$str = preg_replace("/  /","",$str);
	return trim($str);
}

function CeckUserGorup($uid){
	global $_G;
	if(empty($_G['cache']['usergroups'])) {
		loadcache('usergroups');
	}
	$extgroupids = $_G['member']['extgroupids'] ? explode("\t", $_G['member']['extgroupids']) : array();
	$allgroupids = array_merge($extgroupids, array($_G['groupid']));
	$memberfieldforum = C::t('common_member_field_forum')->fetch($uid);
	$groupterms = dunserialize($memberfieldforum['groupterms']);
	unset($memberfieldforum);
	$groupidarr = array_keys($groupterms['ext']);
	if($groupterms['ext'][$_G['groupid']] && $groupterms['ext'][$_G['groupid']] > TIMESTAMP){
		$exceed_days = intval(ceil(($groupterms['ext'][$_G['groupid']] - TIMESTAMP) / 86400));
		$effectiveMsg = array('effective_groupid'=> $_G['groupid'], 'effective_date'=> $groupterms['ext'][$_G['groupid']], 'effective_time'=> dgmdate($groupterms['ext'][$_G['groupid']]), 'effective_days'=> $exceed_days);
	}
	$group_list = array();
	foreach($allgroupids as $k => $groupid){
		$group_list[$groupid]['groupid'] = $groupid;
		$group_list[$groupid]['groupid_time'] = $groupterms['ext'][$groupid];
		$group_list[$groupid]['group_name'] = ymq_DeletHtml($_G['cache']['usergroups'][$groupid]['grouptitle']);
		$group_list[$groupid]['group_icon'] = $_G['cache']['usergroups'][$groupid]['icon'] ? 'data/attachment/common/'.$_G['cache']['usergroups'][$groupid]['icon'] : '';
		$group_list[$groupid]['group_data'] = $groupterms['ext'][$groupid] ? dgmdate($groupterms['ext'][$groupid],'Y-m-d') : '';
	}
	return array('groupidarr' => $groupidarr, 'effectiveMsg' => $effectiveMsg, 'group_list' => $group_list, 'groupid_main' => $groupterms['main']);
}


function vip_MsgWxSend($uid, $sendtype, $url='',$phone='',$credit='',$money='',$expdate='',$vipname='',$viptime='',$presentdate='',$newnote='',$upnotification=true){
	global $_G;

	if(!class_exists('cknotice')){
		include_once libfile('class/cknotice','plugin/ymq_notice');
	}
	if(!class_exists('cknotice_WeChatClient')){
		include_once libfile('class/wechat','plugin/ymq_notice');
	}
	$cknotice = new cknotice();
	$config = $cknotice->config();
	$WeChatClient = new cknotice_WeChatClient($config['wx_appid'], $config['wx_appsecre']);

	$temtype = $cknotice->getBbsNoteType();
	$wxtemtype = array();
	$wxtemtypename = array();
	foreach ($temtype as $index => $type) {
		if($index == 0 ){
			continue;
		}
		$wxtemtype[] = $type[0];
		$wxtemtypename[$type[0]] = $type[1];
	}

	if(!in_array($sendtype, $wxtemtype)){
		return false;
	}

	$open_type = DB::result_first('select open_type from %t where uid=%d', array('ymq_notice_usset', $uid));
	if($open_type){
		$open_typearr = dunserialize($open_type);
		if($open_typearr && $open_typearr[$sendtype] == 2){
			return false;
		}
	}

	$member = getuserbyuid($uid);
	space_merge($member, 'profile');
	$extcredits = get_uscredits($uid);
	$group_list = CeckUserGorup($uid);
	
	if(!$url){
		$url = $_G['siteurl'];
	}

	$notype = $sendtype;
	$openid = DB::result_first("SELECT openid FROM %t WHERE uid=%d", array('ymq_wxsms_user',$uid));
	if($newnote){//如果指定通知内容
        $reparr = array(
            'vararr'    => array(
                '{time}',//当前时间
                '{sysline}',//系统通知时间
                '{username}',//用户名
                '{uid}',//用户ID
                '{type}',//提醒类型
                '{bbname}',//网站名称
                '{note}',//系统通知提醒内容
                '{phone}',//手机号
              	'{credit}',
              	'{money}',
              	'{expdate}',
                '{vipname}',
             	'{viptime}',
              	'{presentdate}'
              
            ),
            'replacearr' => array(
                dgmdate(TIMESTAMP, 'Y-m-d H:i:s'),
                dgmdate(TIMESTAMP, 'Y-m-d H:i:s'),
                $member['username'],
                $uid,
                $wxtemtypename[$sendtype],
                $_G['setting']['bbname'],
                $newnote,
                $phone,
             	$credit,
             	$money.'元',
                dgmdate($expdate,'Y-m-d'),
              	$vipname,
              	$viptime,
                $presentdate
            )
        );
        $wxsendok = $cknotice->ymq_wxGetTempsend($uid, $url, $notype, $openid, $reparr);
        if($wxsendok){
          	return true;
        }else{
          	return false;
        }
    }else{
		$reparr = array(
			'vararr'    => array(
				'{time}',
				'{sysline}',
				'{username}',
				'{uid}',
				'{type}',
				'{bbname}',
				'{note}',
				'{phone}',
				'{group_data}',
				'{group_name}',
				'{extcredits1}',
				'{extcredits2}',
				'{extcredits3}',
				'{extcredits4}',
				'{extcredits5}',
				'{extcredits6}',
				'{extcredits7}',
				'{extcredits8}'
			),
			'replacearr' => array(
				dgmdate(TIMESTAMP, 'Y-m-d H:i:s'),
				dgmdate(TIMESTAMP, 'Y-m-d H:i:s'),
				$member['username'],
				$uid,
				$wxtemtypename[$sendtype],
				$_G['setting']['bbname'],
				$newnote,
				$member['mobile'],
				$group_list['group_list'][$member['groupid']]['group_data'],
				$group_list['group_list'][$member['groupid']]['group_name'],
				$extcredits['extcredits1'].$_G['setting']['extcredits'][1]['title'],
				$extcredits['extcredits2'].$_G['setting']['extcredits'][2]['title'],
				$extcredits['extcredits3'].$_G['setting']['extcredits'][3]['title'],
				$extcredits['extcredits4'].$_G['setting']['extcredits'][4]['title'],
				$extcredits['extcredits5'].$_G['setting']['extcredits'][5]['title'],
				$extcredits['extcredits6'].$_G['setting']['extcredits'][6]['title'],
				$extcredits['extcredits7'].$_G['setting']['extcredits'][7]['title'],
				$extcredits['extcredits8'].$_G['setting']['extcredits'][8]['title']
			)
		);
	
		$wxsendok = $cknotice->ymq_wxGetTempsend($uid, $url, $notype, $openid, $reparr);
		if($wxsendok){
			if($upnotification){
				$notnews = DB::fetch_all("SELECT * FROM %t WHERE uid=%d and new=1 and uid>0 ORDER BY id DESC LIMIT 0,3", array('home_notification', $uid));
				foreach ($notnews as $key => $val) {
					if(strpos($newnote, $val['note']) !== FALSE){
						DB::query('UPDATE %t SET new=0 WHERE id=%d',array('home_notification', $val['id']));
						break;
					}
				}
			}
			return true;
		}else{
			return false;
		}
	}
}
?>