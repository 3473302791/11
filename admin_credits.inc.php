<?php

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {

	exit('Access Denied');

}

if (file_exists(DISCUZ_ROOT.'./source/plugin/ymq_vip/function.php')) {

	@include_once DISCUZ_ROOT.'./source/plugin/ymq_vip/function.php';

}


if (submitcheck('submit')){

	if (!$_POST['add']){

		cpmsg(lang('plugin/ymq_vip', 'error_list'), 'action=plugins&operation=config&do='.$pluginid.'&identifier=ymq_vip&pmod=admin_credits', 'succeed');

	}else{

		foreach ($_POST['add'] as $id){

			$data = array();

			$data['credits_typeid'] = intval($_POST['credits_typeid'.$id.'']);

			$data['credits_open'] = intval($_POST['credits_open'.$id.'']);

			$data['credits_max'] = floatval($_POST['credits_max'.$id.'']);

			$data['credits_min'] = floatval($_POST['credits_min'.$id.'']);

			$data['credits_ratio'] = floatval($_POST['credits_ratio'.$id.'']);

			$data['credits_price'] = daddslashes($_POST['credits_price'.$id.'']);

			$data['credits_present_type'] = intval($_POST['credits_present_type'.$id.'']);

			$data['credits_present_num'] = daddslashes($_POST['credits_present_num'.$id.'']);

			C::t('#ymq_vip#ymq_credits_state')->update($data, array('id' => $id));

		}

		cpmsg(lang('plugin/ymq_vip','editok'),'action=plugins&operation=config&do='.$pluginid.'&identifier=ymq_vip&pmod=admin_credits', 'succeed');

	}

}



for($i=1;$i<=8;$i++){

	if($_G['setting']['extcredits'][$i]['title']!=''){

		$credits_typeid = C::t('#ymq_vip#ymq_credits_state')->get_creditstype_first($i);

		if(!$credits_typeid['credits_typeid']){

			$data = array();

			$data['credits_typeid'] = intval($i);

			$data['credits_open'] = 0;

			$data['credits_max'] = 0;

			$data['credits_min'] = 0;

			$data['credits_ratio'] = 1;

			$data['credits_price'] = '10|20|30|40|50|60|';

			$data['credits_present_type'] = intval($i);

			$data['credits_present_num'] = '100|200|300|400|500|600|';

			C::t('#ymq_vip#ymq_credits_state')->insert($data);

		}

	}

}



showformheader('plugins&operation=config&do='.$pluginid.'&identifier=ymq_vip&pmod=admin_credits','enctype');



showtips(lang('plugin/ymq_vip', 'tips'));

showtableheader(lang('plugin/ymq_vip', 'credits_buy'));

	 showtablerow('',array(),array(

	    '<input type="checkbox" name="chkall" class="checkbox" onclick="checkall(this.form,\'add\')" />'.lang('plugin/ymq_vip', 'full'),

		'ID',

		lang('plugin/ymq_vip', 'credits_typeid'),

		lang('plugin/ymq_vip', 'deal_pyte_credits').lang('plugin/ymq_vip', 'credits_present_type'),

		lang('plugin/ymq_vip', 'deal_pyte_credits').lang('plugin/ymq_vip', 'credits_present_num'),

		lang('plugin/ymq_vip', 'credits_price'),

		lang('plugin/ymq_vip', 'credits_open'),

		'',

		''

	));

	$credits_type = C::t('#ymq_vip#ymq_credits_state')->get_creditslist(8);

 	foreach($credits_type as $v) {

		if($v['credits_open']==1)$ymq_checked='checked="checked"';else $ymq_checked="";

		$credits_tyid = get_extcredits_selected($v['credits_typeid']);

		$credits_present_type = get_extcredits_selected($v['credits_present_type']);

		showtablerow('', array('class="td25"','class="td25"', ''), array(

	        '<input class="checkbox" type="checkbox" name="add['.$v['id'].']" value="'.$v['id'].'">',

			'<div style="width:40px">'.$v['id'].'</div>',

			'<em style="width:80px;color:red;font-weight:bold;margin-right:5px;">1</em> <select name="credits_typeid'.$v['id'].'" style="width:60px">'.$credits_tyid.'</select> = <input class="txt" type="text" style="width:50px;color:red;font-weight:bold" name="credits_ratio'.$v['id'].'" value="'.$v['credits_ratio'].'" />'.lang('plugin/ymq_vip', 'money').',&nbsp;&nbsp;&nbsp;&nbsp;'.lang('plugin/ymq_vip', 'credits_min').': <input class="txt" type="text" style="width:50px;color:red;font-weight:bold" name="credits_min'.$v['id'].'" value="'.$v['credits_min'].'" />'.lang('plugin/ymq_vip', 'money').',&nbsp;'.lang('plugin/ymq_vip', 'credits_max').': <input class="txt" type="text" style="width:50px;color:red;font-weight:bold" name="credits_max'.$v['id'].'" value="'.$v['credits_max'].'" />'.lang('plugin/ymq_vip', 'money'),

			lang('plugin/ymq_vip', 'credits_present_type').': <select name="credits_present_type'.$v['id'].'" style="width:60px">'.$credits_present_type.'</select>',

			lang('plugin/ymq_vip', 'credits_present_num').': <input class="txt" type="text" style="width:200px;color:red;font-weight:bold" name="credits_present_num'.$v['id'].'" value="'.$v['credits_present_num'].'" />',

			lang('plugin/ymq_vip', 'credits_price').': <input class="txt" type="text" style="width:200px;color:red;font-weight:bold" name="credits_price'.$v['id'].'" value="'.$v['credits_price'].'" />',

			'<input class="checkbox" type="checkbox" name="credits_open'.$v['id'].'" '.$ymq_checked.' value="1" />'

		));

	}

	showsubmit('submit',lang('plugin/ymq_vip', 'addg'));

showtablefooter();

showformfooter();

?>