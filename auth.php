<?php

include_once("cf.class.php");

function msg($s)
{
    $_SESSION["login_msg"] = $s;
    header("Location: index.php");
    exit(0);
}

if (Enable_reCAPTCHA) {
    if (!empty($_POST["g-recaptcha-response"])) {
        if (!($cloudflare->reCAPTCHA($_POST["g-recaptcha-response"]))) {
            msg("请完成验证码");
        }
    } else {
        msg("请完成验证码");
    }
}

if ((!empty($_POST["email"])) && (!empty($_POST["password"]))) {
    $r = $cloudflare->login($_POST["email"], $_POST["password"]);
    if ($r["result"] == "success") {
        $_SESSION["user_key"] = $r["response"]["user_key"];
        $_SESSION["email"] = $r["response"]["cloudflare_email"];
        $_SESSION["api_key"] = $r["response"]["user_api_key"];
        //if (Enable_TXT_Verification) $_SESSION["txt_verification"] = password_hash(Random_String.$_SESSION["email"],PASSWORD_BCRYPT );
        header("Location: domains.php");
    } else {
        msg("失败：" . $r["msg"]);
    }
} else {
    msg("用户名和密码不能为空");
}
