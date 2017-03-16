<?php
    require_once('SendPost.php');
    require_once('ReadJsonFile.php');
	isset($_REQUEST['code']) or die('{"errcode":40029,"errmsg":"invalid code"}');

	$wxAppInfo = read_json_file('../conf/conf_wxdev.json');

    $data = array(
        'appid'      => $wxAppInfo->appid,
        'secret'     => $wxAppInfo->appsecret,
        'js_code'    => $_REQUEST['code'],
        'grant_type' => 'authorization_code'
    );
    echo send_post("https://api.weixin.qq.com/sns/jscode2session", $data);
?>