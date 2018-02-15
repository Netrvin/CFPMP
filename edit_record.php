<?php
include_once("cf.class.php");

CF::is_login();

function msg($s){
    $_SESSION["mng_msg"]=$s;
    header("Location: manage_domain.php?domain=".$_POST["domain"]);
    exit(0);
}

if (empty($_POST["domain"])){
    $_SESSION["msg"]="域名不能为空";
    header("Location: domains.php");
    exit(0);
}

if (empty($_POST["action"])){
    msg("操作不存在");
}

if (empty($_POST["record"])){
    msg("记录不能为空");
}

$re=CF::zone_lookup($_POST["domain"]);
if ($re["result"]!="success"){
    msg("操作失败：".$re["msg"]);
}
if ($re["response"]["zone_exists"]!=true){
    msg("该域名未在Cloudflare接入");
}
if ($re["response"]["zone_hosted"]!=true){
    msg("该域名未在".SITE_NAME."接入");
}

$r=CF::remove_zone_name($re["response"]["zone_name"],$re["response"]);

if ($_POST["action"]=="delete")
{
    if (!empty($r["hosted_cnames"][$_POST["record"]]))
    {
        unset($r["hosted_cnames"][$_POST["record"]]);
        $result=CF::update_record($r["zone_name"],$r["hosted_cnames"]);
        if ($result["result"]=="success")
        {
            msg("删除成功");
        }else{
            msg("删除失败：".$result["msg"]);
        }
    }else{
        msg("记录不存在");
    }
}elseif($_POST["action"]=="edit")
{
    if (!empty($_POST["value"]))
    {
        $r["hosted_cnames"][$_POST["record"]]=$_POST["value"];
        $result=CF::update_record($r["zone_name"],$r["hosted_cnames"]);
        if ($result["result"]=="success")
        {
            msg("更新成功");
        }else{
            msg("更新失败：".$result["msg"]);
        }
    }else{
        msg("缺少参数");
    }
}
