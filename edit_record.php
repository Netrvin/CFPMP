<?php
include_once("cf.class.php");

$cloudflare->is_login();

function msg($s)
{
    $_SESSION["mng_msg"] = $s;
    header("Location: manage_domain.php?domain=" . $_POST["domain"]);
    exit(0);
}

if (empty($_POST["zone_id"])) {
    $_SESSION["msg"] = "域名不能为空";
    header("Location: domains.php");
    exit(0);
}

if (empty($_POST["action"])) {
    msg("操作不存在");
}

if (empty($_POST["record"])&&empty($_POST["record_id"])) {
    msg("记录不能为空");
}

$re = $cloudflare->zone_lookup($_POST["domain"]);
if ($re["result"] != "success") {
    msg("操作失败：" . $re["msg"]);
}
if ($re["response"]["zone_exists"] != true) {
    msg("该域名未在Cloudflare接入");
}
if ($re["response"]["zone_hosted"] != true) {
    msg("该域名未在" . SITE_NAME . "接入");
}

if ($_POST["action"] == "delete") {
    if (!empty($_POST["record_id"])) {
        $result = $cloudflare->delete_record($_POST["zone_id"], $_POST["record_id"]);
        if ($result["success"]) {
            msg("删除成功");
        } else {
            msg("删除失败");
        }
    } else {
        msg("缺少参数");
    }
} elseif ($_POST["action"] == "edit") {
    if (!empty($_POST["record_id"])&&!empty($_POST["value"])&&!empty($_POST["record"])) {
        $result = $cloudflare->edit_record($_POST["zone_id"], $_POST["record_id"], $_POST["record"], $_POST["value"]);
        if ($result["success"]) {
            msg("更新成功");
        } else {
            msg("更新失败：" . $result["errors"][0]["message"]);
        }
    } else {
        msg("缺少参数");
    }
} elseif ($_POST["action"] == "add") {
    if (!empty($_POST["value"])&&!empty($_POST["record"])) {
        $result = $cloudflare->add_record($_POST["zone_id"], $_POST["record"], $_POST["value"]);
        if ($result["success"]) {
            msg("添加成功");
        } else {
            msg("添加失败：" . $result["errors"][0]["message"]);
        }
    } else {
        msg("缺少参数");
    }
}
