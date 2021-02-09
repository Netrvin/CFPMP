<?php
include_once("cf.class.php");

$cloudflare->is_login();

function msg($s)
{
    $_SESSION["msg"] = $s;
    header("Location: domains.php");
    exit(0);
}

if (empty($_POST["domain"])) {
    msg("域名不能为空");
}

if (Enable_TXT_Verification){
    if (!$cloudflare->check_txt_record($_POST["domain"])){
        msg("TXT 记录验证失败");
    }
}

$r = $cloudflare->zone_set($_POST["domain"], $_POST["domain"], "www:" . $_POST["domain"]);

if ($r["result"] == "success") {
    msg("添加成功");
} else {
    if (empty($r["msg"])) {
        msg("请刷新本页面以确认域名是否添加成功");
    } else {
        msg("添加失败：" . $r["msg"]);
    }
}
