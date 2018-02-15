<?php
include_once("cf.class.php");

CF::is_login();

function msg($s){
    $_SESSION["msg"]=$s;
    header("Location: domains.php");
    exit(0);
}

if (empty($_POST["domain"])){
    msg("域名不能为空");
}

$r=CF::zone_set($_POST["domain"],$_POST["domain"],"www:".$_POST["domain"]);

if ($r["result"]=="success"){
    msg("添加成功");
}else{
    msg("添加失败：".$r["msg"]);
}