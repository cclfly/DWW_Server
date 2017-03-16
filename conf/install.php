<?php
$result = null;
$adminConf = "conf_admin.json";
if(file_exists($adminConf)) {
    //重新安装
    //TODO: 验证管理员
}else{
    //安装
}
is_writeable("../") or die("错误！目录不可写！");
?>