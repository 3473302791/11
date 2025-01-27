<?php

if(!defined('IN_DISCUZ')) {

	exit('Access Denied');

}
$navtitle = "VIP会员";
$navtitle1 = "VIP会员开通";
	if (file_exists(DISCUZ_ROOT.'./source/plugin/ymq_vip/function.php')) {

		@include_once DISCUZ_ROOT.'./source/plugin/ymq_vip/function.php';

	}

	$config = vip_config();

	$http = https() ? 'https://' : 'http://';

	if($config['isAppbyme'] && !$_G['uid']){

		exit('<script language="javascript" src="mobcent/app/web/js/appbyme/appbyme.js"></script><script>connectAppbymeJavascriptBridge(function(bridge){

			AppbymeJavascriptBridge.login(function(data){

				top.location.href="'.$http.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'";

			});

		});

		</script>');

	}



	if($config['isMAGAPPX'] && !$_G['uid']){

		exit('<script src="source/plugin/ymq_vip/static/js/magjs-x.js"></script><script>

			mag.toLogin(function(){

			   window.location.href="'.$http.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'";

			});

		</script>');

	}



	if($config['isQianFan'] && !$_G['uid']){

		exit('<script>QFH5.jumpLogin(function(state,data){});</script>');

	}



    $tid = intval($_GET['tid']);

	$perpage = 10;

	$curpage = empty($_GET['page']) ? 1 : intval($_GET['page']);

	$start = ($curpage-1)*$perpage;

	$where = array();

	if ($tid){

		$where['vip_id'] = $tid;

	}

	$vip_list = C::t('#ymq_vip#ymq_product_list')->get_viplist($start,$perpage,$where);

	if ($_G['uid']){

        $groupidlist = CeckUserGorup($_G['uid']);

	}

	$vip_html = DB::fetch_all("SELECT vip_id,vip_name,vip_time,vip_prime,vip_price,vip_credits_price,vip_credits_type,vip_recommend,vip_html FROM %t",array('ymq_product_list'));

	$vip_htmlarr = array();

	foreach($vip_html as $k => $v){

		if($v['vip_recommend'] == 1){

			$vip_htmlarr[$v['vip_id']]['vip_recommend'] = $v['vip_recommend'];

		}

		$vip_htmlarr[$v['vip_id']]['vip_id'] = $v['vip_id'];

		$vip_htmlarr[$v['vip_id']]['vip_name'] = $v['vip_name'];

		$vip_htmlarr[$v['vip_id']]['vip_time'] = $v['vip_time'];

		$vip_htmlarr[$v['vip_id']]['vip_prime'] = ($v['vip_prime'] /1);

		$vip_htmlarr[$v['vip_id']]['vip_price'] = ($v['vip_price'] /1);

		$vip_htmlarr[$v['vip_id']]['vip_credits_price'] = $v['vip_credits_price'];

		$vip_htmlarr[$v['vip_id']]['vip_credits_type'] = $v['vip_credits_type'];

		$vip_htmls = array();

		foreach(explode("\r\n",strtolower($v['vip_html'])) as $key => $val){

			if(strstr($val, '{'.lang('plugin/ymq_vip','yes').'}') !== false){

				$vip_htmls[]  = str_replace('{'.lang('plugin/ymq_vip','yes').'}', '<em class="yes">'.lang('plugin/ymq_vip','yes').'</em>', $val);

			}else if(strstr($val, '{'.lang('plugin/ymq_vip','no').'}') !== false){

				$vip_htmls[] = str_replace('{'.lang('plugin/ymq_vip','no').'}', '<em class="no">'.lang('plugin/ymq_vip','no').'</em>', $val);

			}else{

				$vip_htmls[] = $val;

			}

		}

		$vip_htmlarr[$v['vip_id']]['vip_html'] = $vip_htmls;

	}



	if (!$tid){
			$comiis_bg = 1;
			$comiis_head = array(
			'left' => '',
			'center' => $navtitle,
			'right' => '',	
			);
		if($config['ymq_temWidescreen'] == 2 && !checkmobile()){

			

			include template('ymq_vip:ymq_vip_widescreen');

		}else if($config['ymq_temWidescreen'] == 3 && !checkmobile()){

			

			include template('ymq_vip:ymq_vip_widescreen3');

		}else{

			include template('ymq_vip:ymq_vip');

		}

	}else{
		$comiis_bg = 1;
		$comiis_head = array(
		'left' => '',
		'center' => $navtitle1,
		'right' => '',	
		);
		$vip_lists = array();

		foreach($vip_list as $v){

			$vip_lists = $v;

		}

		if ($_G['uid'] && $tid){

			$count_discounts = C::t('#ymq_vip#ymq_payrecord_log')->get_payrecord_log_count(array('uid'=>$_G['uid'],'pay_status'=> 1,'vip_id'=> $tid,'deal_pyte_no'=> 'buy_credits'));

		}

		if(!checkmobile()){

			dheader('location:plugin.php?id=ymq_vip');

			exit;

		}

		include template('ymq_vip:ymq_vip_view');

	}

?>