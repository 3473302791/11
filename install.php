<?php
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
$sql = <<<EOF
CREATE TABLE IF NOT EXISTS `pre_ymq_credits_state` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `credits_typeid` int(25) DEFAULT NULL,
  `credits_open` int(25) DEFAULT NULL,
  `credits_max` decimal(25,2) DEFAULT NULL,
  `credits_min` decimal(25,2) DEFAULT NULL,
  `credits_ratio` decimal(25,2) DEFAULT NULL,
  `credits_price` varchar(255) DEFAULT NULL,
  `credits_present_type` int(10) DEFAULT NULL,
  `credits_present_num` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
CREATE TABLE IF NOT EXISTS `pre_ymq_payrecord_log` (
  `log_id` int(50) NOT NULL AUTO_INCREMENT,
  `vip_id` int(10) DEFAULT NULL,
  `out_trade_no` varchar(50) DEFAULT NULL,
  `pay_pyte` varchar(50) DEFAULT NULL,
  `deal_pyte` varchar(50) DEFAULT NULL,
  `uid` int(10) DEFAULT NULL,
  `groupid_new` int(10) DEFAULT NULL,
  `groupid_overdue` int(15) DEFAULT NULL,
  `credits_type` varchar(60) DEFAULT NULL,
  `credits_present` decimal(25,2) DEFAULT NULL,
  `money` decimal(25,2) DEFAULT NULL,
  `pay_status` int(10) DEFAULT NULL,
  `pay_dateline` int(15) DEFAULT NULL,
  `trade_no` varchar(60) DEFAULT NULL,
  `dateline` int(15) DEFAULT NULL,
  `jump_url` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM;
CREATE TABLE IF NOT EXISTS `pre_ymq_product_list` (
  `vip_id` int(10) NOT NULL AUTO_INCREMENT,
  `vip_name` varchar(10) DEFAULT NULL,
  `vip_group` int(10) DEFAULT NULL,
  `vip_time` int(25) DEFAULT NULL,
  `vip_prime` decimal(25,2) DEFAULT NULL,
  `vip_price` decimal(25,2) DEFAULT NULL,
  `vip_credits_price` int(25) DEFAULT NULL,
  `vip_credits_type` int(25) DEFAULT NULL,
  `vip_present_open` int(10) DEFAULT NULL,
  `vip_credits_present` int(25) DEFAULT NULL,
  `vip_credits_present_type` int(25) DEFAULT NULL,
  `vip_present_date` int(15) DEFAULT NULL,
  `vip_restrict_group` text,
  `vip_restrict_amount` int(10) DEFAULT NULL,
  `vip_discount` int(10) DEFAULT NULL,
  `vip_discount_value` decimal(10,2) DEFAULT NULL,
  `vip_discount_group` text,
  `vip_html` text,
  `vip_recommend` int(10) DEFAULT NULL,
  `vip_dateline` int(12) DEFAULT NULL,
  PRIMARY KEY (`vip_id`)
) ENGINE=MyISAM;
EOF;

runquery($sql);
$finish = TRUE;
?>