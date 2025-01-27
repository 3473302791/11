<?php

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {

	exit('Access Denied');

}



if (file_exists(DISCUZ_ROOT.'./source/plugin/ymq_vip/function.php')) {

	@include_once DISCUZ_ROOT.'./source/plugin/ymq_vip/function.php';

}



$act = daddslashes($_GET['act']);

$group = DB::fetch_all("SELECT groupid,grouptitle FROM ".DB::table('common_usergroup')." WHERE type='special'");

if (!$act) {

	$perpage = 20;

	$curpage = empty($_GET['page']) ? 1 : intval($_GET['page']);

	$start = ($curpage-1)*$perpage;

	$count = C::t('#ymq_vip#ymq_product_list')->get_viplist_count();

	$mpurl = ADMINSCRIPT."?action=plugins&operation=config&do=".$pluginid."&identifier=ymq_vip&pmod=admin_product";

	$multipage = multi($count, $perpage,$curpage,$mpurl, 0, 5);

	$vip_list = C::t('#ymq_vip#ymq_product_list')->get_viplist($start,$perpage);

	if (!$count){

		cpmsg(lang('plugin/ymq_vip', 'error_lists'), 'action=plugins&operation=config&do='.$pluginid.'&identifier=ymq_vip&pmod=admin_product&act=add', 'succeed');

	}

	showformheader('plugins&operation=config&do='.$pluginid.'&identifier=ymq_vip&pmod=admin_product&act=del','enctype');

	showtableheader(lang('plugin/ymq_vip', 'vip_compile_list'));

		showtablerow('',array(),array(

			'<input type="checkbox" name="chkall" class="checkbox" onclick="checkall(this.form,\'delete\')" />'.lang('plugin/ymq_vip', 'full'),

			'ID',

			lang('plugin/ymq_vip', 'vip_name'),

			lang('plugin/ymq_vip', 'vip_group'),

			lang('plugin/ymq_vip', 'vip_timep'),

			lang('plugin/ymq_vip', 'vip_prime').'/'.lang('plugin/ymq_vip', 'vip_price'),

			lang('plugin/ymq_vip', 'vip_credits_price').'/'.lang('plugin/ymq_vip', 'credits_type'),

			lang('plugin/ymq_vip', 'vip_credits_presentp').'/'.lang('plugin/ymq_vip', 'credits_type'),

			lang('plugin/ymq_vip', 'vip_present_datep'),

			lang('plugin/ymq_vip', 'vip_restrict_amount'),

			lang('plugin/ymq_vip', 'vip_discount_valuep'),

			lang('plugin/ymq_vip', 'vip_recommendp'),

			lang('plugin/ymq_vip', 'operate')

		));

		foreach ($vip_list as $v) {

			$grouplist = C::t('common_usergroup')->fetch($v['vip_group']);

			showtablerow('', array('class="td25"', 'class="td28"'), array(

				'<input class="checkbox" type="checkbox" name="delete['.$v['vip_id'].']" value="' .$v['vip_id'].'">',

				$v['vip_id'],

				$v['vip_name'],

				$grouplist['grouptitle'],

				$v['vip_time'],

				$v['vip_prime'].'/'.$v['vip_price'],

				$v['vip_credits_price'].'/'.$_G['setting']['extcredits'][$v['vip_credits_type']]['title'],

				$v['vip_credits_present'].'/'.$_G['setting']['extcredits'][$v['vip_credits_present_type']]['title'],

				$v['vip_present_date'],

				$v['vip_restrict_amount'],

				$v['vip_discount_value'],

				$vip_recommend = $v['vip_recommend'] ? lang('plugin/ymq_vip', 'vip_yes') : lang('plugin/ymq_vip', 'vip_no'),

				'<a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do=$pluginid&identifier=ymq_vip&pmod=admin_product&act=edit&vip_id='.$v['vip_id'].'">'.lang('plugin/ymq_vip', 'alter').'</a>'

			));

		}

		$addt = '<input type="button" class="btn" onclick="location.href=\'' . ADMINSCRIPT . '?action=plugins&operation=config&do='.$pluginid. '&identifier=ymq_vip&pmod=admin_product&act=add\'" value="'.lang('plugin/ymq_vip', 'addt').'" />';

		showsubmit('submit', lang('plugin/ymq_vip', 'dels'), '',$addt, $multipage);//??? ->???? ->???

	showtablefooter();

	showformfooter();

}else if($act == 'add'){//????

    if (submitcheck('submit')){

        $vip_data = array();

        $vip_data['vip_name'] = daddslashes($_POST['vip_name']);

        $vip_data['vip_group'] = intval($_POST['vip_group']);

		$vip_data['vip_time'] = intval($_POST['vip_time']);

		$vip_data['vip_prime'] = floatval($_POST['vip_prime']);

		$vip_data['vip_price'] = floatval($_POST['vip_price']);

		$vip_data['vip_credits_price'] = intval($_POST['vip_credits_price']);

		$vip_data['vip_credits_type'] = intval($_POST['vip_credits_type']);

		$vip_data['vip_present_open'] = intval($_POST['vip_present_open']);

		$vip_data['vip_credits_present'] = intval($_POST['vip_credits_present']);

		$vip_data['vip_credits_present_type'] = intval($_POST['vip_credits_present_type']);

		$vip_data['vip_present_date'] = intval($_POST['vip_present_date']);

		$vip_data['vip_restrict_group'] = serialize(daddslashes($_POST['vip_restrict_group']));

		$vip_data['vip_restrict_amount'] = intval($_POST['vip_restrict_amount']);

		$vip_data['vip_discount'] = intval($_POST['vip_discount']);

		$vip_data['vip_discount_value'] = floatval($_POST['vip_discount_value']);

		$vip_data['vip_discount_group'] = serialize(daddslashes($_POST['vip_discount_group']));

		$vip_data['vip_html'] = $_POST['vip_html'];

		$vip_data['vip_recommend'] = intval($_POST['vip_recommend']);

		$vip_data['vip_dateline'] = time();

        C::t('#ymq_vip#ymq_product_list')->insert($vip_data);

        cpmsg(lang('plugin/ymq_vip', 'bcok'), 'action=plugins&operation=config&do='.$pluginid.'&identifier=ymq_vip&pmod=admin_product', 'succeed');

	}else{

		showformheader('plugins&operation=config&do='.$pluginid.'&identifier=ymq_vip&pmod=admin_product&act=add', 'enctype');

        showtableheader(lang('plugin/ymq_vip', 'vip_compile_add'));

			showsetting(lang('plugin/ymq_vip', 'vip_name'), 'vip_name','', 'text', '', '',lang('plugin/ymq_vip', 'vip_names'));

		    showsetting(lang('plugin/ymq_vip', 'vip_group'), array('vip_group',$group), '', 'select','','',lang('plugin/ymq_vip', 'vip_groups'));

			showsetting(lang('plugin/ymq_vip', 'vip_time'), 'vip_time','', 'text', '', '',lang('plugin/ymq_vip', 'vip_times'));

			showsetting(lang('plugin/ymq_vip', 'vip_prime'), 'vip_prime','', 'text', '', '',lang('plugin/ymq_vip', 'vip_primes'));

			showsetting(lang('plugin/ymq_vip', 'vip_price'), 'vip_price','', 'text', '', '',lang('plugin/ymq_vip', 'vip_prices'));

			showsetting(lang('plugin/ymq_vip', 'vip_credits_price'), 'vip_credits_price','', 'text', '', '',lang('plugin/ymq_vip', 'vip_credits_prices'));

			showsetting(lang('plugin/ymq_vip', 'vip_credits_type'), 'vip_credits_type','', '<select name="vip_credits_type" style="width:80px">'.get_extcredits_selected().'</select>', '', '',lang('plugin/ymq_vip', 'vip_credits_types'));

			showsetting(lang('plugin/ymq_vip', 'vip_present_open'), 'vip_present_open','', 'radio', '', '',lang('plugin/ymq_vip', 'vip_present_opens'));

			showsetting(lang('plugin/ymq_vip', 'vip_credits_present'), 'vip_credits_present','', 'text', '', '',lang('plugin/ymq_vip', 'vip_credits_presents'));

			showsetting(lang('plugin/ymq_vip', 'vip_credits_present_type'), 'vip_credits_present_type','', '<select name="vip_credits_present_type" style="width:80px">'.get_extcredits_selected().'</select>', '', '',lang('plugin/ymq_vip', 'vip_credits_present_types'));

			showsetting(lang('plugin/ymq_vip', 'vip_present_date'), 'vip_present_date','', 'text', '', '',lang('plugin/ymq_vip', 'vip_present_dates'));

			showsetting(lang('plugin/ymq_vip', 'vip_restrict_group'), 'vip_restrict_group', '', '<select name="vip_restrict_group[]"  multiple="multiple" size="10">'.get_group_selected().'</select><td class="vtop tips2" s="1">'.lang('plugin/ymq_vip','vip_restrict_groups').'</td>');

			showsetting(lang('plugin/ymq_vip', 'vip_restrict_amount'), 'vip_restrict_amount','', 'text', '', '',lang('plugin/ymq_vip', 'vip_restrict_amounts'));

			showsetting(lang('plugin/ymq_vip', 'vip_discount'), 'vip_discount','', 'radio', '', '',lang('plugin/ymq_vip', 'vip_discounts'));

			showsetting(lang('plugin/ymq_vip', 'vip_discount_value'), 'vip_discount_value','', 'text', '', '',lang('plugin/ymq_vip', 'vip_discount_values'));

			showsetting(lang('plugin/ymq_vip', 'vip_discount_group'), 'vip_discount_group', '', '<select name="vip_discount_group[]"  multiple="multiple" size="10">'.get_group_selected().'</select><td class="vtop tips2" s="1">'.lang('plugin/ymq_vip','vip_discount_groups').'</td>');

			showsetting(lang('plugin/ymq_vip', 'vip_html'), 'vip_html','', 'textarea', '', '',lang('plugin/ymq_vip', 'vip_htmls'));

			showsetting(lang('plugin/ymq_vip', 'vip_recommend'), 'vip_recommend','', 'radio', '', '');

        showsubmit('submit');

        showtablefooter();

        showformfooter();

	}

}else if ($act == 'edit'){//??

    if (submitcheck('submit')){

        $vip_data = array();

        $vip_data['vip_name'] = daddslashes($_POST['vip_name']);

        $vip_data['vip_group'] = intval($_POST['vip_group']);

		$vip_data['vip_time'] = intval($_POST['vip_time']);

		$vip_data['vip_prime'] = floatval($_POST['vip_prime']);

		$vip_data['vip_price'] = floatval($_POST['vip_price']);

		$vip_data['vip_credits_price'] = intval($_POST['vip_credits_price']);

		$vip_data['vip_credits_type'] = intval($_POST['vip_credits_type']);

		$vip_data['vip_present_open'] = intval($_POST['vip_present_open']);

		$vip_data['vip_credits_present'] = intval($_POST['vip_credits_present']);

		$vip_data['vip_credits_present_type'] = intval($_POST['vip_credits_present_type']);

		$vip_data['vip_present_date'] = intval($_POST['vip_present_date']);

		$vip_data['vip_restrict_group'] = serialize(daddslashes($_POST['vip_restrict_group']));

		$vip_data['vip_restrict_amount'] = intval($_POST['vip_restrict_amount']);

		$vip_data['vip_discount'] = intval($_POST['vip_discount']);

		$vip_data['vip_discount_value'] = floatval($_POST['vip_discount_value']);

		$vip_data['vip_discount_group'] = serialize(daddslashes($_POST['vip_discount_group']));

		$vip_data['vip_html'] = $_POST['vip_html'];

		$vip_data['vip_recommend'] = intval($_POST['vip_recommend']);

		$vip_data['vip_dateline'] = time();

		C::t('#ymq_vip#ymq_product_list')->update($vip_data, array('vip_id' => intval($_POST['vip_id'])));

        cpmsg(lang('plugin/ymq_vip', 'editok'), 'action=plugins&operation=config&do='.$pluginid.'&identifier=ymq_vip&pmod=admin_product', 'succeed');

	}else{

		$vip_id = intval($_GET['vip_id']);

        $vip_list = C::t('#ymq_vip#ymq_product_list')->get_viplist_first($vip_id);

		showformheader('plugins&operation=config&do='.$pluginid.'&identifier=ymq_vip&pmod=admin_product&act=edit', 'enctype');

		echo '<input type="hidden" name="vip_id" value="'.$vip_list['vip_id'].'"/>';

        showtableheader(lang('plugin/ymq_vip', 'vip_compile'));

			showsetting(lang('plugin/ymq_vip', 'vip_name'), 'vip_name',$vip_list['vip_name'], 'text', '', '',lang('plugin/ymq_vip', 'vip_names'));

		    showsetting(lang('plugin/ymq_vip', 'vip_group'), array('vip_group',$group), $vip_list['vip_group'], 'select','','',lang('plugin/ymq_vip', 'vip_groups'));

			showsetting(lang('plugin/ymq_vip', 'vip_time'), 'vip_time',$vip_list['vip_time'], 'text', '', '',lang('plugin/ymq_vip', 'vip_times'));

			showsetting(lang('plugin/ymq_vip', 'vip_prime'), 'vip_prime',$vip_list['vip_prime'], 'text', '', '',lang('plugin/ymq_vip', 'vip_primes'));

			showsetting(lang('plugin/ymq_vip', 'vip_price'), 'vip_price',$vip_list['vip_price'], 'text', '', '',lang('plugin/ymq_vip', 'vip_prices'));

			showsetting(lang('plugin/ymq_vip', 'vip_credits_price'), 'vip_credits_price',$vip_list['vip_credits_price'], 'text', '', '',lang('plugin/ymq_vip', 'vip_credits_prices'));

			showsetting(lang('plugin/ymq_vip', 'vip_credits_type'), 'vip_credits_type','', '<select name="vip_credits_type" style="width:80px">'.get_extcredits_selected($vip_list['vip_credits_type']).'</select>', '', '',lang('plugin/ymq_vip', 'vip_credits_types'));

			showsetting(lang('plugin/ymq_vip', 'vip_present_open'), 'vip_present_open',$vip_list['vip_present_open'], 'radio', '', '',lang('plugin/ymq_vip', 'vip_present_opens'));

			showsetting(lang('plugin/ymq_vip', 'vip_credits_present'), 'vip_credits_present',$vip_list['vip_credits_present'], 'text', '', '',lang('plugin/ymq_vip', 'vip_credits_presents'));

			showsetting(lang('plugin/ymq_vip', 'vip_credits_present_type'), 'vip_credits_present_type','', '<select name="vip_credits_present_type" style="width:80px">'.get_extcredits_selected($vip_list['vip_credits_present_type']).'</select>', '', '',lang('plugin/ymq_vip', 'vip_credits_present_types'));

			showsetting(lang('plugin/ymq_vip', 'vip_present_date'), 'vip_present_date',$vip_list['vip_present_date'], 'text', '', '',lang('plugin/ymq_vip', 'vip_present_dates'));

			showsetting(lang('plugin/ymq_vip', 'vip_restrict_group'), 'vip_restrict_group', '', '<select name="vip_restrict_group[]"  multiple="multiple" size="10">'.get_group_selected($vip_list['vip_restrict_group']).'</select><td class="vtop tips2" s="1">'.lang('plugin/ymq_vip','vip_restrict_groups').'</td>');

			showsetting(lang('plugin/ymq_vip', 'vip_restrict_amount'),'vip_restrict_amount',$vip_list['vip_restrict_amount'], 'text', '', '',lang('plugin/ymq_vip', 'vip_restrict_amounts'));

			showsetting(lang('plugin/ymq_vip', 'vip_discount'), 'vip_discount',$vip_list['vip_discount'], 'radio', '', '',lang('plugin/ymq_vip', 'vip_discounts'));

			showsetting(lang('plugin/ymq_vip', 'vip_discount_value'),'vip_discount_value',$vip_list['vip_discount_value'], 'text', '', '',lang('plugin/ymq_vip', 'vip_discount_values'));

			showsetting(lang('plugin/ymq_vip', 'vip_discount_group'), 'vip_discount_group','','<select name="vip_discount_group[]"  multiple="multiple" size="10">'.get_group_selected($vip_list['vip_discount_group']).'</select><td class="vtop tips2" s="1">'.lang('plugin/ymq_vip','vip_discount_groups').'</td>');

			showsetting(lang('plugin/ymq_vip', 'vip_html'), 'vip_html',$vip_list['vip_html'], 'textarea', '', '',lang('plugin/ymq_vip', 'vip_htmls'));

			showsetting(lang('plugin/ymq_vip', 'vip_recommend'), 'vip_recommend',$vip_list['vip_recommend'], 'radio', '', '');

        showsubmit('submit');

        showtablefooter();

        showformfooter();

	}

}else if ($act == 'del'){//???

    if (submitcheck('submit')){

		if (empty($_POST['delete'])){

			cpmsg(lang('plugin/ymq_vip', 'error_list'), 'action=plugins&operation=config&do='.$pluginid.'&identifier=ymq_vip&pmod=admin_product', 'succeed');

		}else{

			foreach ($_POST['delete'] as $del){

				C::t('#ymq_vip#ymq_product_list')->delete(array('vip_id' => $del));

			}

			cpmsg(lang('plugin/ymq_vip','delok'),'action=plugins&operation=config&do='.$pluginid.'&identifier=ymq_vip&pmod=admin_product', 'succeed');

		}

    }

}

?>