<?php



define('IN_API', true);

define('CURSCRIPT', 'api');

define('DISABLEXSSCHECK', true);

require '../../../source/class/class_core.php';

$discuz = C::app();

$discuz->init();



include_once DISCUZ_ROOT.'./source/plugin/ymq_vip/function.php';

if(file_exists($ymq_pay_list = DISCUZ_ROOT.'./data/sysdata/cache_ymq_pay_list.php')){

	@include $ymq_pay_list;

}



if(function_exists('file_get_contents')){

	$xml = file_get_contents("php://input");

}else{

	$xml = $GLOBALS["HTTP_RAW_POST_DATA"];

}



$post = xmlToArray($xml);

$out_trade_no = $post['out_trade_no'];

$trade_no = $post['transaction_id'];

$result = C::t('#ymq_vip#ymq_payrecord_log')->get_payrecord_log_first(array('out_trade_no'=> $out_trade_no));



if($result['pay_pyte'] == 'wx_gzh' || $result['pay_pyte'] == 'wx_pc' || $result['pay_pyte'] == 'wx_h5'){

    $apikey = $pay_list['wx_apikey'];

}else if($result['pay_pyte'] == 'Appbyme_wx'){

	$apikey = $pay_list['wxapp_key'];

}



if(checkSign($post,$apikey)){

	if($post['result_code'] == 'SUCCESS'){

		if ($result['deal_pyte'] == 'buy_credits'){

			update_credits_pay($result, $trade_no);

		}else{

			update_vip_pay($result,$trade_no);

		}

		$return = array();

		$return['return_code'] = 'SUCCESS';

		$return['return_msg'] = 'ok';

	}else{

		$return = array();

		$return['return_code'] = 'FAIL';

		$return['return_msg'] = 'ERROR';

	}

	echo arrayToXml($return);

}



function arrayToXml($arr){

	$xmlstr='';

	foreach($arr as $key=>$val){

		 if(is_numeric($val)){

			$xmlstr.='<'.$key.'>'.$val.'</'.$key.'>'; 

		 }else{

			$xmlstr.='<'.$key.'><![CDATA['.$val.']]></'.$key.'>';  

		 }

	}

	$xmlstr='<xml>'.$xmlstr.'</xml>';

	return $xmlstr; 

}



function xmlToArray($xml){

    libxml_disable_entity_loader(true);

	$xmltmp=simplexml_load_string($xml,'SimpleXMLElement',LIBXML_NOCDATA);

	$arraytmp = json_decode(json_encode($xmltmp),true);

	return $arraytmp;

}



function ymq_getwxsign($arrtmp,$wxkey){

	foreach ($arrtmp as $key => $value){

		$parameters[$key] = $value;

	}

	ksort($parameters);

	$string = formatBizQueryParaMap($parameters,false);

	$string = $string."&key=$wxkey";

	$string = md5($string);

	$resultstr = strtoupper($string);

	return $resultstr;

}



function formatBizQueryParaMap($querystr,$isurlencode){

	$tmpstr = '';

	ksort($querystr);

	foreach($querystr as $key => $value){

		if($isurlencode){

		   $value = urlencode($value);

		}

		$tmpstr .= $key.'='.$value.'&';

	}

	$returnstr='';

	if(strlen($tmpstr) > 0){

		$returnstr = substr($tmpstr,0,strlen($tmpstr)-1);

	}

	return $returnstr;

}



function checkSign($xmlarray,$wxkey){

	$tmpData = $xmlarray;

	unset($tmpData['sign']);

	$sign = ymq_getwxsign($tmpData,$wxkey);

	if ($xmlarray['sign'] == $sign) {

		return TRUE;

	}

	return FALSE;

}

?>