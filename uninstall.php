<?php
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$plugin_ymq_credits_state = DB::table('ymq_credits_state');
$plugin_ymq_payrecord_log = DB::table('ymq_payrecord_log');
$plugin_ymq_product_list = DB::table('ymq_product_list');

$sql = <<<EOF
DROP TABLE IF EXISTS $plugin_ymq_credits_state;
DROP TABLE IF EXISTS $plugin_ymq_payrecord_log;
DROP TABLE IF EXISTS $plugin_ymq_product_list;
EOF;

runquery($sql);
function ymq_file($directory, $empty = false) {
    if(substr($directory,-1) == "/") {
        $directory = substr($directory,0,-1);
    }
    if(!file_exists($directory) || !is_dir($directory)) {
        return false;
    } elseif(!is_readable($directory)) {
        return false;
    } else {
        @$directoryHandle = opendir($directory);
        while ($contents = @readdir($directoryHandle)) {
            if($contents != '.' && $contents != '..') {
                $path = $directory . "/" . $contents;
                if(is_dir($path)) {
                    @ymq_file($path, $empty);
                } else {
                    @unlink($path);
                }
            }
        }
        @closedir($directoryHandle);
        if($empty == false) {
            if(!@rmdir($directory)) {
                return false;
            }
        }
        return true;
    }
}
ymq_file(DISCUZ_ROOT.'./source/plugin/ymq_vip');
@unlink(DISCUZ_ROOT.'./data/sysdata/cache_ymq_pay_list.php');
$finish = TRUE;
?>