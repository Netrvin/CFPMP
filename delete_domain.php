<?php
include_once("cf.class.php");

$cloudflare->is_login();

function msg($s){
    $_SESSION["msg"]=$s;
    header("Location: domains.php");
    exit(0);
}

if (empty($_POST["domain"])){
    msg("域名不能为空");
}

$r=$cloudflare->zone_delete($_POST["domain"]);

if ($r["result"]=="success"){
    msg("删除成功");
}else{
    msg("删除失败：".$r["msg"]);
}